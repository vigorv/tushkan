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
			->queryAll();
		foreach ($tariffs as $t)
		{
			$tst[$t['id']] = $t;
		}

		$now = date('Y-m-d H:i:s');
//echo $now . "\r\n";
		$subscribes = Yii::app()->db->createCommand()
			->select('us.id AS usid, us.user_id, us.tariff_id, us.operation_id, us.period, us.paid_by, t.id AS tid, t.price AS tprice, tu.switch_to, b.balance, t.is_option, us.eof_period')
			->from('{{user_subscribes}} us')
			->join('{{tariffs_users}} tu', 'tu.tariff_id=us.tariff_id AND tu.user_id=us.user_id')
			->join('{{tariffs}} t', 't.id=tu.tariff_id')
			->leftJoin('{{balance}} b', 'b.user_id=us.user_id')
			->where('us.paid_by <= "' . $now . '"')
			->order('t.is_option ASC')
			->queryAll();

		if (!empty($subscribes))
		{
			foreach ($subscribes as $s)
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
				}

				//ИЩЕМ БАН ЗА НЕУПЛАТУ АБОНЕНТКИ
				$doBan = true;
				$banInfo = Yii::app()->db->createCommand()
					->select('*')
					->from('{{bannedusers}}')
					->where('user_id = ' . $s['user_id'] . ' AND reason = ' . _BANREASON_ABONENTFEE_)
					->queryRow();

				//ВЫЧИСЛЯЕМ ДОЛГ (СКОЛЬКО ПЛАТЕЖНЫХ ПЕРИОДОВ НЕОПЛАЧЕНО) ПО ТАРИФУ ДО ПЕРЕКЛЮЧЕНИЯ
				$periodLength = Utils::parsePeriod($tst[$s['tid']]['period']);
				$payPeriodLength = Utils::parsePeriod($tst[$s['tid']]['pay_period']);//ДЛИНА ПЕРИОДА ОПЛАТЫ
				$periods = intval((strtotime($now) - strtotime($s['paid_by'])) / $payPeriodLength);//КОЛ-ВО НЕОПЛАЧЕННЫХ ПЕРИОДОВ
				//ВЫЧИСЛЯЕМ СТОИМОСТЬ ДОЛГА ЗА ПРОПУЩЕННЫЕ ПЕРИОДЫ
				$k = intval($periodLength/$payPeriodLength);//ВО СКОЛЬКО РАЗ ПЕРИОД ДЕЙСТВИЯ БОЛЬШЕ ПЕРИОДА ОПЛАТЫ
				if ($k < 1) $k = 1;//КОСТЫЛЬ. ПЕРИОД ОПЛАТЫ Д.Б. МЕНЬШЕ ЛИБО РАВЕН ПЕРИОДУ ДЕЙСТВИЯ ТАРИФА
				$dolgCost = $tst[$s['tid']]['price'] / $k * $periods;//СУММА ДОЛГА
				$usInfo["paid_by"] = date("Y-m-d H:i:s", strtotime($usInfo["paid_by"]) + $payPeriodLength * $periods);

				//ВЫЧИСЛЯЕМ СУММУ ОПЛАТЫ ПО ТЕКУЩЕМУ ПЕРИОДУ ($usInfo['tariff_id']) ПО ТЕКУЩЕМУ ТАРИФУ
				$periodLength = Utils::parsePeriod($tst[$usInfo['tariff_id']]['period']);
				$payPeriodLength = Utils::parsePeriod($tst[$usInfo['tariff_id']]['pay_period']);
				$k = intval($periodLength/$payPeriodLength);//ВО СКОЛЬКО РАЗ ПЕРИОД ДЕЙСТВИЯ БОЛЬШЕ ПЕРИОДА ОПЛАТЫ
				if ($k < 1) $k = 1;//КОСТЫЛЬ. ПЕРИОД ОПЛАТЫ Д.Б. МЕНЬШЕ ЛИБО РАВЕН ПЕРИОДУ ДЕЙСТВИЯ ТАРИФА
				$periodCost = $tst[$usInfo['tariff_id']]['price'] / $k;//СУММА ДОЛГА

				$cost = $dolgCost + $periodCost; //СУММА К ОПЛАТЕ
/*
if ($s['user_id'] == 2)
{
echo 'user_id = ' . $s['user_id'] . "\r\n";
echo 'periods = ' . $periods . "\r\n";
echo 'dolgCost = ' . $dolgCost . "\r\n";
echo 'dolg paid_by= ' . $usInfo["paid_by"] . "\r\n";
echo "\r\n";
echo 'periodCost= ' . $periodCost . "\r\n";
echo "\r\n";
}
continue;
*/
				//ЕСЛИ ЭТО ПЕРИОДИЧЕСКАЯ УСЛУГА, ПРОВЕРЯЕМ ВОЗМОЖНОСТЬ СПИСАНИЯ С БАЛАНСА
				if (!empty($usInfo['period']) && ($s['balance'] > $cost))
				{
					//ПЕРЕКЛЮЧАЕМ ТАРИФ ПРИ ДОСТАТОЧНОМ КОЛ-ВЕ СРЕДСТВ НА СЧЕТЕ
					if ($s['switch_to'] && !empty($tst[$s['switch_to']]))
					{
						//ОБНОВЛЯЕМ СВЯЗЬ ПОЛЬЗОВАТЕЛЬ-ТАРИФ
						$sql = 'UPDATE {{tariffs_users}}
							SET switch_to=0, tariff_id=' . $tst[$s['switch_to']]['id'] . '
							WHERE user_id=' . $s['user_id'] . ' AND tariff_id=' . $s['tariff_id'];
						Yii::app()->db->createCommand($sql)->execute();

						//КОРРЕКТИРУЕМ ОБЪЕМ СВОБОДНОГО МЕСТА ПП
						$userInfo = Yii::app()->db->createCommand()
						->select('*')
						->from('{{users}}')
						->where('id = ' . $s['user_id'])
						->queryRow();

						$freeLimit = $tst[$s['switch_to']]['size_limit'] - ($tst[$s['tid']]['size_limit'] - $userInfo['free_limit']);
						if ($freeLimit < 0) $freeLimit = 0;
						$sql = 'UPDATE {{users}} SET free_limit=' . $freeLimit . ' WHERE id=' . $s['user_id'];
						Yii::app()->db->createCommand($sql)->execute();
					}
					$usInfo["paid_by"] = date("Y-m-d H:i:s", strtotime($usInfo["paid_by"]) + $payPeriodLength);
/*
if ($s['user_id'] == 2)
{
echo 'user_id = ' . $s['user_id'] . "\r\n";
echo 'paid_by= ' . $usInfo["paid_by"] . "\r\n";
echo "\r\n";
}
*/
					if ($dolgCost > 0)//СПИСЫВАЕМ ДОЛГ
					{
						$hash = PaysController::createPaymentHash(array('user_id' => $s['user_id'], 'date' => $now, 'summa' => $s['balance'] - $dolgCost));
						$sql = 'UPDATE {{balance}} SET balance = balance - ' . $dolgCost . ', hash = "' . $hash . '" WHERE user_id = ' . $s['user_id'];
						Yii::app()->db->createCommand($sql)->execute();
						//ФИКСИРУЕМ СПИСАНИЕ ПО ТЕКУЩЕМУ ТАРИФУ
						$hash = PaysController::createPaymentHash(array('user_id' => $s['user_id'], 'date' => $now, 'summa' => $dolgCost));
						$sql = '
							INSERT INTO {{debits}}
								(id, user_id, created, operation_id, order_id, summa, hash)
							VALUES
								(null, ' . $s['user_id'] . ', "' . $now . '", ' . $s['operation_id'] . ', 0, ' . $dolgCost . ', "' . $hash . '")
						';
						Yii::app()->db->createCommand($sql)->execute();
					}

					if ($periodCost > 0)
					{
						//СПИСЫВАЕМ СУММУ ПО ТЕКУЩЕМУ ТАРИФУ
						$hash = PaysController::createPaymentHash(array('user_id' => $s['user_id'], 'date' => $now, 'summa' => $s['balance'] - $periodCost));
						$sql = 'UPDATE {{balance}} SET balance = balance - ' . $periodCost . ', hash = "' . $hash . '" WHERE user_id = ' . $s['user_id'];
						Yii::app()->db->createCommand($sql)->execute();
						//ФИКСИРУЕМ СПИСАНИЕ ПО ТЕКУЩЕМУ ТАРИФУ
						$hash = PaysController::createPaymentHash(array('user_id' => $s['user_id'], 'date' => $now, 'summa' => $periodCost));
						$sql = '
							INSERT INTO {{debits}}
								(id, user_id, created, operation_id, order_id, summa, hash)
							VALUES
								(null, ' . $s['user_id'] . ', "' . $now . '", ' . $s['operation_id'] . ', 0, ' . $periodCost . ', "' . $hash . '")
						';
						Yii::app()->db->createCommand($sql)->execute();
					}

					//ОБНОВЛЯЕМ ИНФ О ПЕРИОДИЧЕСКОЙ УСЛУГЕ
					$eofSql = '';
					if ($usInfo['paid_by'] >= $s["eof_period"])
					{
						$eofSql = ', eof_period = "' . date("Y-m-d H:i:s", strtotime($usInfo['paid_by']) + Utils::parsePeriod($usInfo['period'])) . '"';
					}
					$sql = 'UPDATE {{user_subscribes}}
						SET period = "' . $usInfo['period'] . '"' . $eofSql . ', tariff_id = ' . $usInfo['tariff_id'] . ', paid_by="' . $usInfo['paid_by'] . '" WHERE id = ' . $usInfo['id'];
					Yii::app()->db->createCommand($sql)->execute();

					$doBan = false;
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
					$sql = 'DELETE FROM {{bannedusers}} WHERE user_id = ' . $s['user_id'] . ' AND reason = ' . _BANREASON_ABONENTFEE_;
					Yii::app()->db->createCommand($sql)->execute();
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

					//УДАЛЯЕМ ИЗ ЛИЧНОГО ПРОСТРАНСТВА
					if (!empty($l['variant_id']))
					{
						$sql = 'DELETE FROM {{typedfiles}} WHERE variant_id=' . $l['variant_id'] . ' AND user_id = ' . $l['user_id'];
						Yii::app()->db->createCommand($sql)->execute();
					}
					if (!empty($l['variant_quality_id']))
					{
						$vid = Yii::app()->db->createCommand()
							->select('variant_id')
							->from('{{variant_qualities}}')
							->where('quality_id = ' . $l['variant_quality_id'])
							->queryRow();
						if (!empty($vid))
						{
							$sql = 'DELETE FROM {{typedfiles}} WHERE variant_id=' . $vid['variant_id'] . ' AND user_id = ' . $l['user_id'];
							Yii::app()->db->createCommand($sql)->execute();
						}
					}
				}
			}
		}
	}
}