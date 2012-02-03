<?php
Yii::import('application.controllers.PaysController');
Yii::import('ext.classes.Utils');
Yii::import('application.components.Controller');

/**
 * класс консольных команд
 *
 * списание абонентской платы
 *		yiic cron abonentfee
 *
 * проверка текущей аренды продуктов
 * 		yiic cron checkactualrent
 *
 * не забудь настроить config/console.php
 *
 */
class CronCommand extends CConsoleCommand
{
	/**
	 * Автоматическое списание абонентской платы со счетов пользователей
	 * с одновременной обработкой заявок на смену тарифа
	 *
	 */
	public function actionAbonentfee()
	{
		$utils = new Utils();

		$tariffs = Yii::app()->db->createCommand()
			->select('*')
			->from('{{tariffs}}')
			->where('is_archive=0')
			->queryAll();
		foreach ($tariffs as $t)
		{
			$tst[$t['id']] = $t;
		}

		$subscribes = Yii::app()->db->createCommand()
			->select('us.id AS usid, us.user_id, us.tariff_id, us.operation_id, us.period, us.paid_by, t.id AS tid, t.price AS tprice, tu.switch_to, b.balance, t.is_option')
			->from('{{user_subscribes}} us')
			->join('{{tariffs_users}} tu', 'tu.tariff_id=us.tariff_id AND tu.user_id=us.user_id')
			->join('{{tariffs}} t', 't.id=tu.tariff_id')
			->leftJoin('{{balance}} b', 'b.user_id=us.user_id')
			->order('t.is_option ASC')
			->queryAll();

		if (!empty($subscribes))
		{
			$now = date('Y-m-d H:i:s');
			foreach ($subscribes as $s)
			{
				if ($s['paid_by'] <= $now)
				{
					$usInfo = array(
						'id'		=> $s['usid'],
						'period'	=> $s['period'],
						'tariff_id'	=> $s['tid'],
						'paid_by'	=> $s['paid_by'],
					);
					//ИСТЕК СРОК ОПЛАТЫ, ПРОВЕРЯЕМ ЗАЯВКУ НА СМЕНУ ТАРИФА
					if ($s['switch_to'] && !empty($tst[$s['switch_to']]))
					{
						//ЕСЛИ ТАРИФ СУЩЕСТВУЕТ, ПЕРЕКЛЮЧАЕМ
						$usInfo['tariff_id'] = $tst[$s['switch_to']]['id'];
						$usInfo['period'] = $tst[$s['switch_to']]['period'];

						//ОБНОВЛЯЕМ СВЯЗЬ ПОЛЬЗОВАТЕЛЬ-ТАРИФ
						$sql = 'UPDATE {{tariffs_users}}
							SET switch_to=0, tariff_id=' . $tst[$s['switch_to']]['id'] . '
							WHERE user_id=' . $s['user_id'] . ' AND tariff_id=' . $s['tariff_id'];
						Yii::app()->db->createCommand($sql)->execute();
					}

					//ИЩЕМ БАН ЗА НЕУПЛАТУ АБОНЕНТКИ
					$doBan = true;
					$banInfo = Yii::app()->db->createCommand()
						->select('*')
						->from('{{bannedusers}}')
						->where('user_id = ' . $s['user_id'] . ' AND reason = ' . _BANREASON_ABONENTFEE_)
						->queryRow();
					//ЕСЛИ ЭТО ПЕРИОДИЧЕСКАЯ УСЛУГА, ПРОВЕРЯЕМ ВОЗМОЖНОСТЬ СПИСАНИЯ С БАЛАНСА
					if (!empty($usInfo['period']) && ($s['balance'] > $tst[$usInfo['tariff_id']]['price']))
					{
						$doBan = false;
						$usInfo['paid_by'] = date('Y-m-d H:i:s', (time() + Utils::parsePeriod($tst[$usInfo['tariff_id']]['period'])));

						//СПИСЫВАЕМ СУММУ
						$hash = PaysController::createPaymentHash(array('user_id' => $s['user_id'], 'date' => $now, 'summa' => $s['balance'] - $tst[$usInfo['tariff_id']]['price']));
						$sql = 'UPDATE {{balance}} SET balance = balance - ' . $tst[$usInfo['tariff_id']]['price'] . ', hash = "' . $hash . '" WHERE user_id = ' . $s['user_id'];
						Yii::app()->db->createCommand($sql)->execute();

						//ФИКСИРУЕМ СПИСАНИЕ
						$hash = PaysController::createPaymentHash(array('user_id' => $s['user_id'], 'date' => $now, 'summa' => $tst[$usInfo['tariff_id']]['price']));
						$sql = '
							INSERT INTO {{debits}}
								(id, user_id, created, operation_id, order_id, summa, hash)
							VALUES
								(null, ' . $s['user_id'] . ', "' . $now . '", ' . $s['operation_id'] . ', 0, ' . $tst[$usInfo['tariff_id']]['price'] . ', "' . $hash . '")
						';
						Yii::app()->db->createCommand($sql)->execute();
					}

					//ОБНОВЛЯЕМ ИНФ О ПЕРИОДИЧЕСКОЙ УСЛУГЕ
					$sql = 'UPDATE {{user_subscribes}}
						SET period = "' . $usInfo['period'] . '", tariff_id = ' . $usInfo['tariff_id'] . ', paid_by="' . $usInfo['paid_by'] . '" WHERE id = ' . $usInfo['id'];
					Yii::app()->db->createCommand($sql)->execute();

					if ($s['is_option'])
					{
						if (empty($usInfo['period']))
						{
							//ЗА РАЗОВЫЕ ОПЦИИ НЕ БАНИМ
						}
						else
						{
							/**
							 * ДОДЕЛАТЬ!
							 * ПЕРИОДИЧЕСКИЕ ОПЦИИ ОТКЛЮЧАТЬ ИЛИ БАНИТЬ АККАУНТ - К ОБСУЖДЕНИЮ
							 */
						}
						continue;
					}

					if ($doBan)
					{
						if (empty($banInfo))
						{
							$sql = 'INSERT INTO {{bannedusers}} (id, user_id, start, finish, state, reason)
								VALUES (NULL, ' . $s['user_id'] . ', "' . $now . '", "0000-00-00 00:00:00", ' . _BANSTATE_READONLY_ . ', ' . _BANREASON_ABONENTFEE_ . ')';
							Yii::app()->db->createCommand($sql)->execute();
						}
					}
					else
					{
						if (!empty($banInfo))
						{
							$sql = 'DELETE FROM {{bannedusers}} WHERE id = ' . $banInfo['id'];
							Yii::app()->db->createCommand($sql)->execute();
						}
					}
				}
			}
		}
	}

	/**
	 * проверка текущей аренды продуктов
	 *
	 */
	public function actionCheckactualrent()
	{
		$utils = new Utils();
		$lst = Yii::app()->db->createCommand()
			->select('*')
			->from('{{actual_rents}}')
			->where('start > "0000-00-00 00:00:00"')
			->queryAll();
		if (!empty($lst))
		{
			foreach($lst as $l)
			{
				if (strtotime($l['start']) + Utils::parsePeriod($l['period'], $l['start']) - time() <= 0)
				{
					$sql = 'DELETE FROM {{actual_rents}} WHERE id=' . $l['id'];
					Yii::app()->db->createCommand($sql)->execute();
				}
			}
		}
	}
}