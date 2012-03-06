<?php

define('_PS_STARTED_',	0); //статус платежа. начат процесс оплаты
define('_PS_CHECK_',	1); //статус платежа. платеж проверяется
define('_PS_CANCELED_',	2); //статус платежа. платеж отменен
define('_PS_PAYED_',	3); //статус платежа. платеж завершен (оплачен)

class PaysController extends Controller
{
	public $Paysystem;
	public $balancePaymentInfo;

	public function actionIndex()
	{
//print_r($_GET);
//exit;
		$conditions = array(); $from = ''; $to = '';
		if (!empty($_GET['from']))
		{
			$from = date('Y-m-d', strtotime($_GET['from']));
			$fSql = $from . ' 00:00:00';
			$conditions[] = 'created >= :from';
		}
		if (!empty($_GET['to']))
		{
			$to = date('Y-m-d', strtotime($_GET['to']));
			$tSql = $to . ' 23:59:59';
			$conditions[] = 'created <= :to';
		}
		if (!empty($conditions))
		{
			$conditions = ' AND ' . implode(' AND ', $conditions);
		}
		else
			$conditions = '';

		$operations = Yii::app()->db->createCommand()
			->select('id, title')
			->from('{{balanceoperations}}')
			->queryAll();
		$operations = Utils::arrayToKeyValues($operations, "id", "title");

		$balance = Yii::app()->db->createCommand()
			->select('*')
			->from('{{balance}}')
			->where('user_id = ' . Yii::app()->user->getId())
			->queryRow();

		$cmd = Yii::app()->db->createCommand()
			->select('*')
			->from('{{debits}}')
			->where('user_id = ' . $this->userInfo['id'] . $conditions)
			->order('created DESC');
		if (!empty($conditions))
		{
			if (!empty($fSql))
				$cmd->bindParam(':from', $fSql, PDO::PARAM_STR);
			if (!empty($tSql))
				$cmd->bindParam(':to', $tSql, PDO::PARAM_STR);
		}
		$debits = $cmd->queryAll();

		$cmd = Yii::app()->db->createCommand()
			->select('*')
			->from('{{payments}}')
			->where('operation_id = 1 AND user_id = ' . $this->userInfo['id'] . $conditions)
			->order('created DESC');
		if (!empty($conditions))
		{
			if (!empty($fSql))
				$cmd->bindParam(':from', $fSql, PDO::PARAM_STR);
			if (!empty($tSql))
				$cmd->bindParam(':to', $tSql, PDO::PARAM_STR);
		}
		$incs = $cmd->queryAll();

		$this->render('/pays/index', array('balance' => $balance, 'debits' => $debits, 'incs' => $incs, 'from' => $from, 'to' => $to, 'operations' => $operations));
	}

	/**
	 * действие по оплате
	 *
	 * @param integer $id - идентификатор действия
	 */
	public function actionDo($id)
	{
		$userPower = intval(Yii::app()->user->getState('dmUserPower'));
		$lst = Yii::app()->db->createCommand()
			->select('*')
			->from('{{paysystems}}')
			->where('active <= ' . $userPower)
			->order('srt DESC')->queryAll();
		$balance = Yii::app()->db->createCommand()
			->select('*')
			->from('{{balance}}')
			->where('user_id = ' . Yii::app()->user->getId())
			->queryRow();
		$orderInfo = $postInfo = array();
		if (!empty($_POST))
		{
			$postInfo = $_POST;
			if (!empty($postInfo['order_id']))
			{
				$cmd = Yii::app()->db->createCommand()
					->select('o.id, p.title, oi.variant_id, oi.price_id, oi.rent_id, oi.price, oi.cnt')
					->from('{{orders}} o')
					->join('{{order_items}} oi', 'oi.order_id=o.id')
					->leftJoin('{{product_variants}} pv', 'oi.variant_id=pv.id')
					->leftJoin('{{products}} p', 'pv.product_id=p.id')
					->where('o.id = :id AND o.state = 0 AND o.user_id = ' . Yii::app()->user->getId());
				$cmd->bindParam(':id', $postInfo['order_id'], PDO::PARAM_INT);
				$orderInfo = $cmd->queryAll();
			}
		}

		$cmd = Yii::app()->db->createCommand()
			->select('*')
			->from('{{balanceoperations}}')
			->where('id = :id')
			;
		$cmd->bindParam(':id', $id, PDO::PARAM_INT);
		$oInfo = $cmd->queryRow();

		if (empty($oInfo))
		{
			Yii::app()->user->setFlash('error', Yii::t('pays', 'Payment initialisation error.'));
			$this->redirect('/universe/error');
		}
		$this->render('/pays/do', array('lst' => $lst, 'oInfo' => $oInfo, 'balance' => $balance, 'postInfo' => $postInfo, 'orderInfo' => $orderInfo));
	}

	/**
	 * инициализация класса платежной системы
	 * экземпляр класса доступен через свойство $this->Paysystem
	 *
	 * @param integer $psId -  идентификатор в таблице paysystems
	 * @return boolean - результат: инициализировано или нет
	 */
	public function initPaysystem($psId)
	{
		$psInfo = Yii::app()->db->createCommand()
			->select('*')
			->from('{{paysystems}}')
			->where('active > 0 AND id = ' . intval($psId))
			->order('srt DESC')->queryRow();
		if (!empty($psInfo))
		{
			$psName = $psInfo['class'];
			if (!empty($psName))
			{
				Yii::import('ext.paysystems.' . $psName);
				if (class_exists($psName))
				{
					$this->Paysystem = new $psName;
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * сгенерировать хэш суммы
	 *
	 * @param mixed $info (массив должен содержать индексы date, user_id, summa)
	 * @return string
	 */
	public function createPaymentHash($info)
	{
		return md5($info['date'] . $info['summa'] . $info['user_id']);
	}

	/**
	 * Старт процесса оплаты
	 *
	 * @param integer $id - идентификатор платежной системы
	 */
	public function actionPayment($id = 0)
	{
		$this->layout = '/layouts/ajax';
		if (!empty($_POST))
		{
			$userPower = Yii::app()->user->getState('dmUserPower');
			if (!empty($id))
			{
				//ПРОВЕРЯЕМ ДОСТУПНА ЛИ ДАННАЯ ПЛАТЕЖНАЯ СИСТЕМА ЮЗЕРУ
				$cmd = Yii::app()->db->createCommand()
					->select('*')
					->from('{{paysystems}}')
					->where('id = :id AND active <= :power');
				$cmd->bindParam(':id', $id, PDO::PARAM_INT);
				$cmd->bindParam(':power', $userPower, PDO::PARAM_INT);
				$paysystemInfo = $cmd->queryRow();
			}
			else
			{
				//ОПЛАТА С БАЛАНСА
				$paysystemInfo['id'] = 0;
			}

			if (!empty($paysystemInfo))
			{
				$created = date('Y-m-d H:i:s');
				$payInfo = $_POST;
				$payInfo['created'] = $created;
				$sql = '
					INSERT INTO {{payments}}
						(user_id, paysystem_id, created, modified, operation_id, summa, state, hash, info, order_id)
					VALUES
						(:user_id, ' . $paysystemInfo['id'] . ', "' . $created . '", "' . $created . '", :operation_id, :summa, ' . _PS_STARTED_ . ', :hash, "", :order_id)
				';
				$cmd = Yii::app()->db->createCommand($sql);
				if (!empty($payInfo['user_id']))
					$cmd->bindParam(':user_id', $payInfo['user_id'], PDO::PARAM_INT);
				if (!empty($payInfo['operation_id']))
					$cmd->bindParam(':operation_id', $payInfo['operation_id'], PDO::PARAM_INT);
				if (!empty($payInfo['order_id']))
					$order_id = $payInfo['order_id'];
				else
					$order_id = 0;
				$cmd->bindParam(':order_id', $order_id, PDO::PARAM_INT);
				if (!empty($payInfo['summa']))
				{
					$cmd->bindParam(':summa', $payInfo['summa'], PDO::PARAM_LOB);
					$hash = $this->createPaymentHash(array(
							'summa' => $payInfo['summa'],
							'date' => $created,
							'user_id' => $payInfo['user_id'])
					);
					$payInfo['hash'] = $hash;
					$cmd->bindParam(':hash', $hash, PDO::PARAM_STR);
					$res = $cmd->query();
				}

				if (!empty($res))
				{
					$lastId = Yii::app()->db->getLastInsertID('{{payments}}');
					$payInfo['payment_id'] = $lastId;
					$payInfo['description'] = Yii::t('pays', 'Payment') . ' №' . $lastId;
					//ДОБАВИТЬ НАЗВАНИЕ ПЛАТЕЖА
					if (!empty($id))
					{
						$this->initPaysystem($id);
						$resultMsg = $this->Paysystem->start($payInfo);
					}
					else
					{
						$this->balancePaymentInfo = $payInfo;
						$resultMsg = $this->actionProcess(0);
					}
					$this->out($resultMsg);
					return;
				}
			}
			$this->out('saving payment error');
			return;
		}
		Yii::app()->user->setFlash('error', Yii::t('pays', 'Payment initialisation error.'));
		$this->redirect('/pays');
	}

	/**
	 * действие совершения платежа
	 *
	 */
	public function actionProcess($id = 0)
	{
		$resultMsg = 'bad request';
		$result = array();
		if (!empty($id))
		{
			$this->initPaysystem($id);
 			if (!empty($_POST))
 				$requestInfo = $_POST;
 			else
 				$requestInfo = array();
 			$result = $this->Paysystem->process($requestInfo);
		}
		else
		{
			if (!empty($this->balancePaymentInfo))
			{
				$result = $this->balancePayment($this->balancePaymentInfo);
			}
		}

		if (!empty($result))
		{
			//МОЖЕТ БЫТЬ ВОЗВРАЩЕН ОТВЕТ ПО ОДНОМУ ПЛАТЕЖУ ИЛИ ПО СПИСКУ ПЛАТЕЖЕЙ
			if (!empty($result['payment_id']))
			{
				//ЭТО ОТВЕТ ПО ОДНОМУ ПЛАТЕЖУ
				$resultLst = array($result);//СОЗДАЕМ СПИСОК С ОДНИМ ЭЛЕМЕНТОМ
			}
			else
			{
				$resultLst = $result;
			}

			foreach ($resultLst as $result)
			{
				if (!empty($result['payment_id']) && !empty($result['result_id']))
				{
					$cmd = Yii::app()->db->createCommand()
						->select('*')
						->from('{{payments}}')
						->where('id=:id');
					$cmd->bindParam(':id', $result['payment_id'], PDO::PARAM_INT);
					$payInfo = $cmd->queryRow();
					if (!empty($payInfo))
					{
						//ОБНОВЛЯЕМ СТАТУС ПЛАТЕЖА
						$sql = 'UPDATE {{payments}} SET state = ' . $result['result_id'] . ', modified = "' . date('Y-m-d H:i:s') . '" WHERE id = ' . $payInfo['id'];
						Yii::app()->db->createCommand($sql)->query();
						$resultMsg = $result['msg'];

						if ($result['result_id'] == _PS_PAYED_)//ЕСЛИ ОПЛАЧЕНО, ВЫПОЛНЯЕМ ОПЕРАЦИЮ
						{
							$operationInfo = Yii::app()->db->createCommand()
								->select('*')
								->from('{{balanceoperations}}')
								->where('id=' . $payInfo['operation_id'])->queryRow();
							$this->$operationInfo['method']($payInfo);
						}
					}
				}
			}
		}
		$this->layout = '/layouts/ajax';
		$this->out($resultMsg);
	}

	/**
	 * Обработчик успешного платежа
	 *
	 * @param integer $id - идентификатор платежной системы
	 */
	public function actionOk($id = 0)
	{
		//$this->layout = '/layouts/index';
		$resultMsg = Yii::t('pays', 'Payment processed successfully');

		if (!empty($id))
		{
			$this->initPaysystem($id);
 			if (!empty($_POST))
 				$requestInfo = $_POST;
 			else
 				$requestInfo = array();
			$msg = $this->Paysystem->ok($requestInfo);
			if (!empty($msg))
				$resultMsg = $msg;
		}
		$this->out($resultMsg);
	}

	/**
	 * обработчик ошибки при совершении платежа
	 *
	 * @param integer $id - идентификатор платежной системы
	 */
	public function actionFail($id = 0)
	{
		//$this->layout = '/layouts/index';
		$resultMsg = Yii::t('pays', 'Payment failed');
		if (!empty($id))
		{
			$this->initPaysystem($id);
 			if (!empty($_POST))
 				$requestInfo = $_POST;
 			else
 				$requestInfo = array();
			$msg = $this->Paysystem->fail($requestInfo);
			if (!empty($msg))
				$resultMsg = $msg;
		}
		$this->out($resultMsg);
	}

	/**
	 * вывод результата на страницу
	 *
	 */
	public function out($resultMsg)
	{
		$this->render('/pays/out', array('resultMsg' => $resultMsg));
	}

	/**
	 * операция пополнения баланса
	 *
	 * @param mixed $payInfo
	 */
	public function increaseBalance($payInfo)
	{
		$balanceInfo = Yii::app()->db->createCommand()
			->select('*')
			->from('{{balance}}')
			->where('user_id=' . $payInfo['user_id'])->queryRow();

		$modified = date('Y-m-d H:i:s');
		if (empty($balanceInfo))
		{
			$hash = $this->createPaymentHash(array('user_id' => $payInfo['user_id'], 'date' => $modified, 'summa' => $payInfo['summa']));
			$sql = '
				INSERT INTO {{balance}}
					(id, user_id, modified, balance, hash)
				VALUES
					(null, ' . $payInfo['user_id'] . ', "' . $modified . '", ' . $payInfo['summa'] . ', "' . $hash . '")
			';
//echo $sql;
//return;
			Yii::app()->db->createCommand($sql)->query();
		}
		else
		{
			$hash = $this->createPaymentHash(array('user_id' => $payInfo['user_id'], 'date' => $modified, 'summa' => $payInfo['summa'] + $balanceInfo['balance']));
			$sql = 'UPDATE {{balance}} SET balance = balance + ' . $payInfo['summa'] . ', hash = "' . $hash . '" WHERE user_id = ' . $balanceInfo['user_id'];
			Yii::app()->db->createCommand($sql)->query();
		}
	}

	/**
	 * оплата платежа с баланса
	 *
	 * @param mixed $payInfo
	 */
	public function balancePayment($payInfo)
	{
		$answerInfo = array();
		//ПРОВЕРЯЕМ ДОСТАТОЧНОЕ КОЛ-ВО СРЕДСТВ НА БАЛАНСЕ
		$balanceInfo = Yii::app()->db->createCommand()
			->select('*')
			->from('{{balance}}')
			->where('user_id=' . $payInfo['user_id'])->queryRow();

		if ($balanceInfo['balance'] >= $payInfo['summa'])
		{
			//СПИСЫВАЕМ СУММУ
			$modified = date('Y-m-d H:i:s');
			$hash = $this->createPaymentHash(array('user_id' => $payInfo['user_id'], 'date' => $modified, 'summa' => $balanceInfo['balance'] - $payInfo['summa']));
			$sql = 'UPDATE {{balance}} SET balance = balance - ' . $payInfo['summa'] . ', hash = "' . $hash . '" WHERE user_id = ' . $balanceInfo['user_id'];
			Yii::app()->db->createCommand($sql)->execute();

			//ФИКСИРУЕМ СПИСАНИЕ
			$hash = $this->createPaymentHash(array('user_id' => $payInfo['user_id'], 'date' => $modified, 'summa' => $payInfo['summa']));
			$sql = '
				INSERT INTO {{debits}}
					(id, user_id, created, operation_id, order_id, summa, hash)
				VALUES
					(null, ' . $payInfo['user_id'] . ', "' . $modified . '", ' . $payInfo['operation_id'] . ', ' . $payInfo['order_id'] . ', ' . $payInfo['summa'] . ', "' . $hash . '")
			';
			Yii::app()->db->createCommand($sql)->execute();

			//И ВОЗВРАЩАЕМ ОТВЕТ ОБ УСПЕХЕ
			$answerInfo['result_id'] = _PS_PAYED_;
			$answerInfo['payment_id'] = $payInfo['payment_id'];
			$answerInfo['msg'] = '_PS_PAYED_';
		}
		return $answerInfo;
	}

	public function orderPayment($payInfo)
	{
		if (empty($payInfo['order_id']))
			return;
		$Order = new COrder();
		$modified = date('Y-m-d H:i:s');
		$sql = 'UPDATE {{orders}} SET state=' . _ORDER_PAYED_ . ', modified="' . $modified . '" WHERE id=' . $payInfo['order_id'];
		Yii::app()->db->createCommand($sql)->query();

		//ПРОПИСЫВАЕМ ИНФУ О ВСЕХ ТОВАРАХ ЗАКАЗА КАК ПРИОБРЕТЕННЫХ(АРЕНДОВАННЫХ)
		$cmd = Yii::app()->db->createCommand()
			->select('o.id, p.title, oi.variant_id, oi.price_id, oi.rent_id')
			->from('{{orders}} o')
			->join('{{order_items}} oi', 'oi.order_id=o.id')
			->leftJoin('{{product_variants}} pv', 'oi.variant_id=pv.id')
			->leftJoin('{{products}} p', 'pv.product_id=p.id')
			->where('o.id = :id AND o.user_id = ' . Yii::app()->user->getId());
		$cmd->bindParam(':id', $payInfo['order_id'], PDO::PARAM_INT);
		$items = $cmd->queryAll();
		if (!empty($items))
		{
			foreach ($items as $i)
			{
				if (!empty($i['rent_id']))
				{
					$priceInfo = Yii::app()->db->createCommand()
						->select('period')
						->from('{{rents}}')
						->where('id = ' . $i['rent_id'])->queryRow();
					if (!empty($priceInfo))
					{
						$period = $priceInfo['period'];
					}
					$sql = '
						INSERT INTO {{actual_rents}}
							(id, variant_id, start, period, user_id)
						VALUES
							(null, ' . $i['variant_id'] . ', 0, "' . $period . '", ' . Yii::app()->user->getId() . ')
					';
					Yii::app()->db->createCommand($sql)->query();
				}

				//СОХРАНЯЕМ ВСЕ ПОЗИЦИИ В ПП
				$existInfo = Yii::app()->db->createCommand()
					->select('*')
					->from('{{typedfiles}}')
					->where('variant_id = ' . $i['variant_id'] . ' AND user_id = ' . Yii::app()->user->getId())
					->queryRow();
				if (empty($existInfo))
				{
					$sql = '
						INSERT INTO {{typedfiles}}
							(id, variant_id, user_id, title, collection_id)
						VALUES
							(null, ' . $i['variant_id'] . ', ' . Yii::app()->user->getId() . ', "' . $i["title"] . '", 0)
					';
					Yii::app()->db->createCommand($sql)->query();
				}
			}
		}
	}
}