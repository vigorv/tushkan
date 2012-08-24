<?php
/**
 * класс оплаты через Webmoney
 *
 * в настройках мерчанта:
 * 	- установить метод отправки POST
 *  - разрешить отправку SecurityKey
 *
 */
class WebmoneyPay
{
	/**
	 * отправка запроса к платежной системе
	 *
	 * @param mixed $payInfo
	 */
	public function start($payInfo)
	{
		$ch = curl_init();
		$url = Yii::app()->params['tushkan']['paySystems']['WebmoneyPay']['url'];
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

		$description = $payInfo['description'];//ОПИСАНИЕ ПЛАТЕЖА
		$purse = Yii::app()->params['tushkan']['paySystems']['WebmoneyPay']['purse'];//НОМЕР КОШЕЛЬКА
		$testMode = Yii::app()->params['tushkan']['paySystems']['WebmoneyPay']['testMode'];//1 - ТЕСТОВЫЙ РЕЖИМ, 0 - РАБОЧИЙ РЕЖИМ

		$nvpreq = "LMI_PAYMENT_NO={$payInfo['payment_id']}&LMI_PAYMENT_AMOUNT={$payInfo['summa']}&LMI_PAYMENT_DESC={$description}&LMI_PAYEE_PURSE={$purse}&LMI_SIM_MODE={$testMode}";
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
		$httpResponse = curl_exec($ch);

		return $httpResponse;
	}

	public function process($requestInfo)
	{
		$answerInfo = array();
		if (!empty($requestInfo['LMI_PAYMENT_NO']))
		{
			$cmd = Yii::app()->db->createCommand()
				->select('*')
				->from('{{payments}}')
				->where('id=:id AND state <= ' . _PS_CHECK_);
			$cmd->bindParam(':id', $requestInfo['LMI_PAYMENT_NO'], PDO::PARAM_INT);
			$payInfo = $cmd->queryRow();

			if (!empty($payInfo))
			{
				if ($requestInfo['LMI_PREREQUEST'])//ПРЕДВАРИТЕЛЬНЫЙ ЗАПРОС ОТ СИСТЕМЫ (ПРОВЕРКА)
				{
					if ($payInfo['summa'] == $requestInfo['LMI_PAYMENT_AMOUNT'])//ЕСЛИ СУММА СОВПАЛА СЧИТАЕМ ПРОВЕРКУ ВЫПОЛНЕННОЙ
					{
						$answerInfo = array('payment_id' => $payInfo['id']);
						$answerInfo['result_id'] = _PS_CHECK_;
						$answerInfo['msg'] = 'YES';
					}
				}
				else //ЗАПРОС ПРИ СОВЕРШЕНИИ ПЛАТЕЖА
				{
					$hash = md5(
						$requestInfo['LMI_PAYEE_PURSE'] .
						$requestInfo['LMI_PAYMENT_AMOUNT'] .
						$requestInfo['LMI_PAYMENT_NO'] .
						$requestInfo['LMI_MODE'] .
						$requestInfo['LMI_SYS_INVS_NO'] .
						$requestInfo['LMI_SYS_TRANS_NO'] .
						$requestInfo['LMI_SYS_TRANS_DATE'] .
						$requestInfo['LMI_SECRET_KEY'] .
						$requestInfo['LMI_PAYER_PURSE'] .
						$requestInfo['LMI_PAYER_WM']
					);
					if (
						($payInfo['summa'] == $requestInfo['LMI_PAYMENT_AMOUNT'])
						&& ($hash == $requestInfo['LMI_HASH '])
					)//ЕСЛИ СОВПАЛИ СУММА ХЭШ СЧИТАЕМ, ЧТО ОПЛАЧЕНО
					{
						$answerInfo = array('payment_id' => $payInfo['id']);
						$answerInfo['result_id'] = _PS_PAYED_;
						$answerInfo['msg'] = '';//ПРИ СОВЕРШЕННОМ ПЛАТЕЖЕ МОЖЕМ НИЧЕГО НЕ ОТВЕЧАТЬ
					}
				}
			}
		}
		return $answerInfo;
	}

	/**
	 * дополнительные действия перед выводом сообщения пользователю об успешном совершении платежа
	 *
	 */
	public function ok($requestInfo)
	{
		$answerInfo = '';
		if (!empty($requestInfo['LMI_PAYMENT_NO']))
		{
			$answerInfo['payment_id'] = $requestInfo['LMI_PAYMENT_NO'];
			$answerInfo['result_id'] = _PS_PAYED_;
			$answerInfo['msg'] = '';
		}
		return $answerInfo;
	}

	/**
	 * дополнительные действия перед выводом сообщения пользователю о невыполненом платеже
	 *
	 */
	public function fail($requestInfo)
	{
		$answerInfo = '';
		if (!empty($requestInfo['LMI_PAYMENT_NO']))
		{
			$answerInfo['payment_id'] = $requestInfo['LMI_PAYMENT_NO'];
			$answerInfo['result_id'] = _PS_CANCELED_;
			$answerInfo['msg'] = '';
		}
		return $answerInfo;
		/* КОД ВЫНЕСЕН В PaysController

		$cmd = Yii::app()->db->createCommand()
			->select('*')
			->from('{{payments}}')
			->where('id=:id AND state <= ' . _PS_CHECK_);
		$cmd->bindParam(':id', $requestInfo['LMI_PAYMENT_NO'], PDO::PARAM_INT);
		$payInfo = $cmd->queryRow();
		if (!empty($payInfo))
		{
			$sql = 'UPDATE {{payments}} SET state = ' . _PS_CANCELED_ . ', modified = "' . date('Y-m-d H:i:s') . '" WHERE id = :id';
			$cmd = Yii::app()->db->createCommand($sql);
			$cmd->bindParam(':id', $requestInfo['LMI_PAYMENT_NO'], PDO::PARAM_INT);
			$cmd->query();
		}
		*/
	}

/* DEPRECATED
	public function getOrderId($requestInfo)
	{
		$orderId = 0;
		if (!empty($requestInfo['order_id']))
		{
			$orderId = $requestInfo['order_id'];
		}
		return $orderId;
	}
*/
}