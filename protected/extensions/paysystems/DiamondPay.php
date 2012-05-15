<?php

/**
 * класс, реализующий оплату через внутренню систему платежей
 * выдача бонусов, предоплата обещанных платежей итд
 *
 */
class DiamondPay
{
	/**
	 * отправка запроса к платежной системе
	 *
	 * @param mixed $payInfo
	 */
	public function start($payInfo)
	{
		$ch = curl_init();
		$url = Yii::app()->params['tushkan']['siteURL'] . '/pays/process/1';
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		$nvpreq = "order_id={$payInfo['payment_id']}&user_id={$payInfo['user_id']}&sum={$payInfo['summa']}&hash={$payInfo['hash']}";
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
		$httpResponse = curl_exec($ch);

		return $httpResponse;
	}

	public function process($payInfo)
	{
		$answerInfo = array('payment_id' => $payInfo['order_id']);
		$answerInfo['result_id'] = _PS_PAYED_;
		$answerInfo['msg'] = '_PS_PAYED_';
		return $answerInfo;
	}

	public function ok()
	{

	}

	public function fail()
	{

	}

	public function getOrderId($requestInfo)
	{
		$orderId = 0;
		if (!empty($requestInfo['order_id']))
		{
			$orderId = $requestInfo['order_id'];
		}
		return $orderId;
	}
}