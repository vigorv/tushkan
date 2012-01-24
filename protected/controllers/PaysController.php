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
		$balance = Yii::app()->db->createCommand()
			->select('*')
			->from('{{balance}}')
			->where('user_id = ' . Yii::app()->user->getId())
			->queryRow();
		$this->render('/pays/index', array('balance' => $balance));
	}

	/**
	 * действие по оплате
	 *
	 * @param integer $id - идентификатор действия
	 */
	public function actionDo($id)
	{
		$userPower = Yii::app()->user->getState('dmUserPower');
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
		$postInfo = array();
		if (!empty($_POST))
		{
			$postInfo = $_POST;
		}

		$cmd = Yii::app()->db->createCommand()
			->select('*')
			->from('{{balanceoperations}}')
			->where('id = :id')
			;
		$cmd->bindParam(':id', $id, PDO::PARAM_INT);
		$oInfo = $cmd->queryRow();

		$this->render('/pays/do', array('lst' => $lst, 'oInfo' => $oInfo, 'balance' => $balance, 'postInfo' => $postInfo));
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
		$this->layout = '//layouts/ajax';
		if (!empty($_POST))
		{
			$created = date('Y-m-d H:i:s');
			$payInfo = $_POST;
			$payInfo['created'] = $created;
			$sql = '
				INSERT INTO {{payments}}
					(user_id, paysystem_id, created, modified, operation_id, summa, state, hash, info, order_id)
				VALUES
					(:user_id, :paysystem_id, "' . $created . '", "' . $created . '", :operation_id, :summa, ' . _PS_STARTED_ . ', :hash, "", :order_id)
			';
			$cmd = Yii::app()->db->createCommand($sql);
			if (!empty($_POST['user_id']))
				$cmd->bindParam(':user_id', $_POST['user_id'], PDO::PARAM_INT);
			if (!empty($_POST['operation_id']))
				$cmd->bindParam(':operation_id', $_POST['operation_id'], PDO::PARAM_INT);
			if (!empty($_POST['order_id']))
				$order_id = $_POST['order_id'];
			else
				$order_id = 0;
			$cmd->bindParam(':order_id', $order_id, PDO::PARAM_INT);

			$cmd->bindParam(':paysystem_id', $id, PDO::PARAM_INT);
			if (!empty($_POST['summa']))
			{
				$cmd->bindParam(':summa', $_POST['summa'], PDO::PARAM_LOB);
				$hash = $this->createPaymentHash(array(
						'summa' => $_POST['summa'],
						'date' => $created,
						'user_id' => $_POST['user_id'])
				);
				$payInfo['hash'] = $hash;
				$cmd->bindParam(':hash', $hash, PDO::PARAM_STR);
			}
			$res = $cmd->query();
			if ($res)
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
		$this->layout = '//layouts/ajax';
		$this->out($resultMsg);
	}

	/**
	 * Обработчик успешного платежа
	 *
	 * @param integer $id - идентификатор платежной системы
	 */
	public function actionOk($id = 0)
	{
		$this->layout = '/layouts/index';
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
		$this->layout = '/layouts/index';
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
			Yii::app()->db->createCommand($sql)->query();

			//ФИКСИРУЕМ СПИСАНИЕ
			$hash = $this->createPaymentHash(array('user_id' => $payInfo['user_id'], 'date' => $modified, 'summa' => $payInfo['summa']));
			$sql = '
				INSERT INTO {{debits}}
					(id, user_id, created, operation_id, order_id, summa, hash)
				VALUES
					(null, ' . $payInfo['user_id'] . ', "' . $modified . '", ' . $payInfo['operation_id'] . ', ' . $payInfo['order_id'] . ', ' . $payInfo['summa'] . ', "' . $hash . '")
			';
			Yii::app()->db->createCommand($sql)->query();

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
		$items = Yii::app()->db->createCommand()
			->select('*')
			->from('{{order_items}}')
			->where('order_id = ' . $payInfo['order_id'])
			->queryAll();
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
				}

				if (!empty($i['price_id']))
				{
					$period = 0;//ТОВАР КУПЛЕН
				}

				$sql = '
					INSERT INTO {{actual_rents}}
						(id, variant_id, start, period, user_id)
					VALUES
						(null, ' . $i['variant_id'] . ', 0, ' . $period . ', ' . $payInfo['user_id'] . ')
				';
				Yii::app()->db->createCommand($sql)->query();
			}
		}
	}
}