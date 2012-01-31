<?php
Yii::import('application.controllers.PaysController');
Yii::import('ext.classes.Utils');
Yii::import('application.components.Controller');
/**
 * класс консольных команд
 *
 * пример вызова:
 * protected>yiic cron abonentfee
 *
 * не забудь настроить config/console.php
 *
 */
class CronCommand extends CConsoleCommand
{
	public function actionAbonentfee()
	{
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
			->select('us.id AS usid, us.user_id, us.tariff_id, us.operation_id, us.period, us.paid_by, t.id AS tid, t.price AS tprice, tu.switch_to, b.balance')
			->from('{{user_subscribes}} us')
			->join('{{tariffs_users}} tu', 'tu.tariff_id=us.tariff_id AND tu.user_id=us.user_id')
			->join('{{tariffs}} t', 't.id=tu.tariff_id')
			->join('{{balance}} b', 'b.user_id=us.user_id')
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

					//ЕСЛИ ЭТО ПЕРИОДИЧЕСКАЯ УСЛУГА, ПРОВЕРЯЕМ ВОЗМОЖНОСТЬ СПИСАНИЯ С БАЛАНСА
					if (!empty($usInfo['period']) && ($s['balance'] > $tst[$usInfo['tariff_id']]['price']))
					{
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
				}
			}
		}
	}
}