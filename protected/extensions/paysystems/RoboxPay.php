<?php
/**
 * класс оплаты через Робокассу
 *
 */
class RoboxPay
{
	/**
	 * отправка запроса к платежной системе
	 *
	 * @param mixed $payInfo
	 */
	public function start($payInfo)
	{
		$fields = array(
			"MrchLogin"			=> Yii::app()->params['tushkan']['paySystems']['RoboxPay']['login'],
			"OutSum"			=> $payInfo['summa'],
			"InvId"				=> $payInfo['id'],
			"Desc"				=> $payInfo['description'],
//					"IncCurrLabel"		=> 'RUR',
		);

		$code		= Yii::app()->params['tushkan']['paySystems']['RoboxPay']['pwd1'];
		$signature 	= md5($fields["MrchLogin"] . ':' . $fields["OutSum"] . ':' . $fields["InvId"] . ':' . $code);
		$fields["SignatureValue"] = $signature;

		$data = ''; $amp = '';
		foreach ($fields as $key => $value)
		{
			$data .= $amp . $key . '=' . $value;
			$amp = '&';
		}

		$testMode = Yii::app()->params['tushkan']['paySystems']['RoboxPay']['testMode'];
		$host = Yii::app()->params['tushkan']['paySystems']['RoboxPay']['url' . $testMode];

		header('location: ' . $host . '?' . $data);

		return;
	}

	public function process($requestInfo)
	{
		$answerInfo = array();
		if (!empty($requestInfo['InvId']))
		{
			$cmd = Yii::app()->db->createCommand()
				->select('*')
				->from('{{payments}}')
				->where('id=:id AND state <= ' . _PS_CHECK_);
			$cmd->bindParam(':id', $requestInfo['InvId'], PDO::PARAM_INT);
			$payInfo = $cmd->queryRow();
		}
		if (!empty($payInfo))
		{
			$field["OutSum"] = $requestInfo['OutSum'];
			$field["InvId"] = intval($requestInfo['InvId']);
			$field["Sign"] = strtoupper($requestInfo['SignatureValue']);

			$code		= Yii::app()->params['tushkan']['paySystems']['RoboxPay']['pwd2'];
			$signature	= strtoupper(md5($field["OutSum"] . ':' . $field["InvId"] . ':' . $code));

			if (($payInfo['summa'] == $field["OutSum"]) && ($field["Sign"] == $signature))
			{
				$answerInfo = array('payment_id' => $payInfo['id']);
				$answerInfo['result_id'] = _PS_PAYED_;
				$answerInfo['msg'] = "OK" . $payInfo['id'] . "\n";
			}
			else
			{
				$answerInfo = array('payment_id' => $payInfo['id']);
				$answerInfo['result_id'] = _PS_CANCELED_;
				$answerInfo['msg'] = "";
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

	}

	/**
	 * дополнительные действия перед выводом сообщения пользователю о невыполненом платеже
	 *
	 */
	public function fail($requestInfo)
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