<?php

class SmsCoinPay
{
	/**
	 * SMSCOIN the function returns an MD5 of parameters passed
	 *
	 * @return string
	 */
	function sign() {
		$params = func_get_args();
		$prehash = implode("::", $params);
		return md5($prehash);
	}

	public function start($payInfo)
	{
		$order_id		= $payInfo['payment_id'];
		$secret_code	= Yii::app()->params['tushkan']['paySystems']['SmsCoinPay']['code'];
		$purse			= Yii::app()->params['tushkan']['paySystems']['SmsCoinPay']['bank_id'];
		$amount			= $payInfo['summa'];
		$clear_amount	= 0; // billing algorithm
		$description	= $payInfo['description']; // описание платежа
		$sign			= $this->sign($purse, $order_id, $amount, $clear_amount, $description, $secret_code);

		$data = 's_purse=' . $purse;

		$data.= '&';
		$data.= 's_order_id=' . $order_id;

		$data.= '&';
		$data.= 's_amount=' . $amount;

		$data.= '&';
		$data.= 's_clear_amount=' . $clear_amount;

		$data.= '&';
		$data.= 's_description=' . $description;

		$data.= '&';
		$data.= 's_sign=' . $sign;

		$host = Yii::app()->params['tushkan']['paySystems']['SmsCoinPay']['url0'];

		$ch = curl_init($host);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 0);
		$response = curl_exec($ch);

		return $response;
	}

	public function process($requestInfo)
	{
		$answerInfo = array();

		foreach($requestInfo as $request_key => $request_value) {
			$requestInfo[$request_key] = substr(strip_tags(trim($request_value)), 0, 250);
		}

		if (!empty($requestInfo['s_order_id']))
		{
			$cmd = Yii::app()->db->createCommand()
				->select('*')
				->from('{{payments}}')
				->where('id=:id AND state <= ' . _PS_CHECK_);
			$cmd->bindParam(':id', $requestInfo['s_order_id'], PDO::PARAM_INT);
			$payInfo = $cmd->queryRow();

			if (!empty($payInfo))
			{
				// service secret code
				$secret_code = Yii::app()->params['tushkan']['paySystems']['SmsCoinPay']['code'];

				// collecting required data
				$purse        = $requestInfo["s_purse"];        // sms:bank id
				$order_id     = $requestInfo["s_order_id"];     // operation id
				$amount       = $requestInfo["s_amount"];       // transaction sum
				$clear_amount = $requestInfo["s_clear_amount"]; // billing algorithm
				$inv          = $requestInfo["s_inv"];          // operation number
				$phone        = $requestInfo["s_phone"];        // phone number
				$sign         = $requestInfo["s_sign_v2"];      // signature
				// making the reference signature
				$reference = $this->sign($secret_code, $purse, $order_id, $amount, $clear_amount, $inv, $phone);

				$success = false;

				// сравниваем сигнатуру
				if($sign == $reference)
				{
					// success, proceeding
					$answerInfo['payment_id'] = $payInfo['id'];
					$answerInfo['result_id'] = _PS_PAYED_;
					$answerInfo['msg'] = '';
				}
			}
		}
		return $answerInfo;
	}

	/**
	 * дополнительные действия перед выводом сообщения пользователю об успешном совершении платежа
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
		$answerInfo = '';
		if (!empty($requestInfo['s_order_id']))
		{
			$answerInfo['payment_id'] = $requestInfo['s_order_id'];
			$answerInfo['result_id'] = _PS_CANCELED_;
			$answerInfo['msg'] = '';
		}
		return $answerInfo;
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