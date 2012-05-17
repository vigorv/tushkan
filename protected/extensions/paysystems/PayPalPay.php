<?php
/**
 * класс оплаты через PayPal
 *
 */
class PayPalPay
{
	/**
	 * в зависимости от режима (тестовый/рабочий) возвращает название сервиса PayPal
	 *
	 * @return string
	 */
	function getPaypalEnvironment()
	{
		if (Yii::app()->params['tushkan']['paySystems']['PayPalPay']['testMode'])
			return 'sandbox';
		else
			return '';
	}

	/**
	 * Send HTTP POST Request
	 *
	 * @param	string	The API method name
	 * @param	string	The POST Message fields in &name=value pair format
	 * @return	array	Parsed HTTP Response body
	 */
	function paypalRequest($methodName_, $nvpStr_)
	{
		$environment = $this->getPaypalEnvironment();	// or 'beta-sandbox' or 'live'

		// Set up your API credentials, PayPal end point, and API version.
		$testMode = Yii::app()->params['tushkan']['paySystems']['PayPalPay']['testMode'];
		$API_UserName = urlencode(Yii::app()->params['tushkan']['paySystems']['PayPalPay']['username' . $testMode]);
		$API_Password = urlencode(Yii::app()->params['tushkan']['paySystems']['PayPalPay']['password' . $testMode]);
		$API_Signature = urlencode(Yii::app()->params['tushkan']['paySystems']['PayPalPay']['signature' . $testMode]);
		$API_Endpoint = "https://api-3t.paypal.com/nvp";
		if("sandbox" === $environment || "beta-sandbox" === $environment) {
			$API_Endpoint = "https://api-3t.$environment.paypal.com/nvp";
		}
		$version = urlencode('61.0');

		// Set the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		// Turn off the server and peer verification (TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

		// Set the API operation, version, and API signature in the request.
		$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

		// Set the request as a POST FIELD for curl.
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
//echo $nvpreq;
//exit;

		// Get response from the server.
		$httpResponse = curl_exec($ch);

		if(!$httpResponse) {
			exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
		}
//echo $httpResponse;
//exit;
		// Extract the response details.
		$httpResponseAr = explode("&", $httpResponse);

		$httpParsedResponseAr = array();
		foreach ($httpResponseAr as $i => $value) {
			$tmpAr = explode("=", $value);
			if(sizeof($tmpAr) > 1) {
				$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
			}
		}

		if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
			return false;
			//exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
		}

		return $httpParsedResponseAr;
	}

	/**
	 * инициализация платежа

/you can test your credentials with the simple form below

<form method=post action= https://api-3t.paypal.com/nvp>
<input type=hidden name=USER value= api_user>
<input type=hidden name=PWD value= api_password>
<input type=hidden name=SIGNATURE value= api_signature>
<input type=hidden name=VERSION value= 61.0>
<input type=hidden name=PAYMENTACTION value=Sale>
<input type=hidden name=AMT value=1.00>
<input type=hidden name=RETURNURL value=https://www.paypal.com>
<input type=hidden name=CANCELURL value=https://www.paypal.com>
<input type=submit name=METHOD value=SetExpressCheckout>
</form>

	 * @param mixed $payInfo
	 * @return string
 	*/
	public function start($payInfo)
	{
		//ОБЯЗАТЕЛЬНО ФОРМАТИРУЕМ СУММУ ОПЛАТЫ
		$summa = sprintf("%01.2f", (float)($PayInfo['summa']));

		$siteURL = Yii::app()->params['tushkan']['siteURL'];
		$paySystemId = Yii::app()->params['tushkan']['paySystems']['PayPalPay']['id'];
		$fields = array(
			"AMT"			=> $out_summ,
			//"PAYMENTACTION"	=> "Authorization",
			"PAYMENTACTION"	=> "Sale",
			"CURRENCYCODE"	=> Yii::app()->params['tushkan']['paySystems']['PayPalPay']['currency'],
			"RETURNURL"		=> $siteURL . "/pays/process/{$paySystemId}",
			"CANCELURL"		=> $siteURL . "/pays/fail/{$paySystemId}",
		);

		$data = ''; $amp = '&';
		foreach ($fields as $key => $value)
		{
			$data .= $amp . $key . '=' . urlencode($value);
		}

		// Execute the API operation; see the PPHttpPost function above.
		$httpParsedResponseAr = $this->paypalRequest('SetExpressCheckout', $data);

		$payPalURL = '';

		if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
			// Redirect to paypal.com.
			$environment = $this->getPaypalEnvironment();
			$token = urldecode($httpParsedResponseAr["TOKEN"]);
			$payPalURL = "https://www.paypal.com/webscr&cmd=_express-checkout&token=$token";
			if("sandbox" === $environment || "beta-sandbox" === $environment) {
				$payPalURL = "https://www.$environment.paypal.com/webscr&cmd=_express-checkout&token=$token";
			}

			$sql = 'UPDATE {{payments}} SET state = ' . _PS_CHECK_ . '
				,modified = "' . date('Y-m-d H:i:s') . '"
				,token = "' . $token . '"
				WHERE id = ' . $payInfo['id'];
			Yii::app()->db->createCommand($sql)->query();

		} else {
			Yii::app()->user->setFlash('error', 'SetExpressCheckout failed: ' . print_r($httpParsedResponseAr));
			$sql = 'UPDATE {{payments}} SET state = ' . _PS_CANCELED_ . ', modified = "' . date('Y-m-d H:i:s') . '" WHERE id = ' . $payInfo['id'];
			Yii::app()->db->createCommand($sql)->query();
			header('location: /pays');
		}
		return;
	}

	public function process($requestInfo)
	{
		$answerInfo = array();
		$paySystemId = Yii::app()->params['tushkan']['paySystems']['PayPalPay']['id'];
		if (empty($requestInfo['token']))
		{
			return;
		}
		$token = urldecode($requestInfo['token']);
		$payerID = urldecode($requestInfo['PayerID']);

		// success, proceeding
		$cmd = Yii::app()->db->createCommand()
			->select('*')
			->from('{{payments}}')
			->where('state <= ' . _PS_CHECK_ . ' AND info = :info');
		$cmd->bindParam(':info', $token);
		$payInfo = $cmd->queryRow();
		if (!empty($payInfo))
		{
			$amount = $payInfo['summa'];

			$token = urlencode(htmlspecialchars($token));
			$payerID = urlencode(htmlspecialchars($payerID));

			$paymentType = urlencode("Sale");			// 'Authorization' or 'Sale' or 'Order'
			$paymentAmount = urlencode($amount);
			$currencyID = urlencode(Configure::read('paypal.currency'));	// or other currency code ('USD', 'GBP', 'EUR', 'JPY', 'CAD', 'AUD')

			// Add request-specific fields to the request string.
			$nvpStr = "&TOKEN=$token&PAYERID=$payerID&PAYMENTACTION=$paymentType&AMT=$paymentAmount&CURRENCYCODE=$currencyID";

			// Execute the API operation; see the PPHttpPost function above.
			$httpParsedResponseAr = $this->paypalRequest('DoExpressCheckoutPayment', $nvpStr);

			if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
				$answerInfo['payment_id'] = $payInfo['id'];
				$answerInfo['result_id'] = _PS_PAYED_;
				$answerInfo['msg'] = '';

				return $answerInfo;
			} else  {
				return;
			}
		}
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