<?php

class AssistPay
{
	public function start($payInfo)
	{
		$Order_IDP		= $payInfo['id'];
		$Shop_IDP		= Yii::app()->params['tushkan']['paySystems']['AssistPay']['Shop_IDP'];
		$Subtotal_P		= $payInfo['summa'];
		$Comment		= $payInfo['description'];
		$Delay			= 0; //НЕМЕДЛЕННОЕ СПИСАНИЕ

		$DemoResult		= 'AS000'; //ОЖИДАЕМ ЧТО ОПЛАТА ПРОЙДЕТ УСПЕШНО
		//$DemoResult		= 'AS100'; //ОЖИДАЕМ ЧТО ОПЛАТА НЕ ПРОЙДЕТ

		$siteURL = Yii::app()->params['tushkan']['siteURL'];
		$paySystemId = Yii::app()->params['tushkan']['paySystems']['AssistPay']['id'];
		$URL_RETURN		= $siteURL . '/pays'; //ВОЗВРАТ НА СТРАНИЦУ ОПЛАТЫ
		$URL_RETURN_OK	= $siteURL . '/' . $paySystemId . '/process'; //ПРИ УСПЕШНОЙ ОПЕРАЦИИ ОПЛАТЫ
		$URL_RETURN_NO	= $siteURL . '/' . $paySystemId . '/fail'; //ПРИ НЕОПЛАТЕ

		$data = 'Order_IDP=' . $Order_IDP;

		$data.= '&';
		$data.= 'Shop_IDP=' . $Shop_IDP;

		$data.= '&';
		$data.= 'Subtotal_P=' . $Subtotal_P;

		$data.= '&';
		$data.= 'Comment=' . iconv('utf-8', 'windows-1251', $Comment);

		$data.= '&';
		$data.= 'Delay=' . $Delay;

		$data.= '&';
		$data.= 'URL_RETURN=' . $URL_RETURN;

		$data.= '&';
		$data.= 'URL_RETURN_NO=' . $URL_RETURN_NO;

		$data.= '&';
		$data.= 'URL_RETURN_OK=' . $URL_RETURN_OK;

		$data.= '&';
		$data.= 'WebMoneyPayment=1';

		$data.= '&';
		$data.= 'PayCashPayment=1';

		$data.= '&';
		$data.= 'EPBeelinePayment=1';

		$data.= '&';
		$data.= 'CardPayment=1';

		$data.= '&';
		$data.= 'AssistIDCCPayment=1';

		if (Yii::app()->params['tushkan']['paySystems']['AssistPay']['testMode'])
		{
			$data.= '&';
			$data.= 'DemoResult=' . $DemoResult;
			$host = Yii::app()->params['tushkan']['paySystems']['AssistPay']['url1'];
		}
		else
		{
			$host = Yii::app()->params['tushkan']['paySystems']['AssistPay']['url0'];
		}

		//$this->set('host', $host);
		//$this->set('data', $data);

		$url = $host . "?" . $data;

		header('location: ' . $url);
	}

	/**
	 *
	 * @param unknown_type $requestInfo
	 */
	public function process($requestInfo)
	{
		$ShopOrderNumber= '%';//ВСЕ
		$Shop_ID		= Yii::app()->params['tushkan']['paySystems']['AssistPay']['Shop_IDP'];
		$Login			= Yii::app()->params['tushkan']['paySystems']['AssistPay']['login'];
		$Password		= Yii::app()->params['tushkan']['paySystems']['AssistPay']['pwd'];
		$Success		= 2; //КАКИЕ ВОЗВРАЩАТЬ (0 - неуспешные, 1 - успешные, 2 - все)
		$Format			= 4; //XML
		$ZipFlag		= 0; //Режим выдачи результата (0 – браузер, 1 – файл, 2 – архивированный файл)

		//ДАТУ-ВРЕМЯ НЕ УКАЗЫВАЕМ, БУДЕМ ВЫБИРАТЬ ПО УМОЛЧАНИЮ (ЗА ТРИ ДНЯ) СЕРВИС ОТВЕЧАЕТ ОДИН РАЗ ЗА ДЕСЯТЬ МИНУТ

		$data = 'ShopOrderNumber=' . $ShopOrderNumber;

		$data.= '&';
		$data.= 'Shop_ID=' . $Shop_ID;

		$data.= '&';
		$data.= 'Login=' . $Login;

		$data.= '&';
		$data.= 'Password=' . $Password;

		$data.= '&';
		$data.= 'Success=' . $Success;

		$data.= '&';
		$data.= 'Format=' . $Format;

		$data.= '&';
		$data.= 'ZipFlag=' . $ZipFlag;

		$testMode = Yii::app()->params['tushkan']['paySystems']['AssistPay']['testMode'];
		$host = Yii::app()->params['tushkan']['paySystems']['AssistPay']['url' . $testMode];
		$paySystemId = Yii::app()->params['tushkan']['paySystems']['AssistPay']['id'];

		//ВЫБИРАЕМ ВСЕ НЕОБРАБОТАННЫЕ ПЛАТЕЖИ
		$cmd = Yii::app()->db->createCommand()
			->select('*')
			->from('{{payments}}')
			->where('paysystem_id = ' . $paySystemId . ' AND created > "' . date('Y-m-d H:i:s', time() - 3600 * 24 * 3) . '" AND state <= ' . _PS_CHECK_);
		$payInfoLst = $cmd->queryAll();

		if (!empty($payInfoLst) && count($payInfoLst) > 0)
		{
			$xml = file_get_contents($host . "?" . $data);
			if ($xml)
			{
				global $orders;
				global $curOrder;
				global $tag;

				$orders = array();

				function startElement($parser, $name, $attrs)
				{
					global $orders;
					global $curOrder;
					global $tag;

					$tag = $name;
					switch ($tag)
					{
						case "ORDER":
							$curOrder = array();
					}
				    //$depth[$parser]++;
				}

				function characterData($parser, $data)
				{
					global $orders;
					global $curOrder;
					global $tag;

					if (!trim($data))
						return;

					switch ($tag)
					{
				    	case "ORDERNUMBER":
							$curOrder['id'] = intval($data);
						break;

				    	case "RESPONSE_CODE":
							$curOrder['code'] = $data;
				    	break;

				    	case "DATE":
							$date = sscanf($data, "%02s.%02s.%04s %02s:%02s:%02s");
							$curOrder['date'] = sprintf("%04s-%02s-%02s %02s:%02s:%02s", $date[2], $date[1], $date[0], $date[3], $date[4], $date[5]);
				    	break;

				    	case "TOTAL":
							$curOrder['total'] = $data;
				    	break;
					}
				}

				function endElement($parser, $name)
				{
					global $orders;
					global $curOrder;

				    //$depth[$parser]--;

				    switch ($name)
				    {
				    	case "ORDER":
				    		if (!empty($curOrder['id']))
				    		{
				    			$orders[$curOrder['id']] = $curOrder;
				    		}
				    	break;
				    }
				}

				$xml_parser = xml_parser_create();
				xml_set_element_handler($xml_parser, "startElement", "endElement");
				xml_set_character_data_handler($xml_parser, "characterData");

			    if (!xml_parse($xml_parser, $xml, true))
			    {

			        die(sprintf("XML error: %s at line %d",
			                    xml_error_string(xml_get_error_code($xml_parser)),
			                    xml_get_current_line_number($xml_parser)));
			    }
				xml_parser_free($xml_parser);

				$answerInfo = array();
				foreach ($orders as $o)
				{
					if (!empty($o['id']))
					{
						foreach ($payInfoLst as $payInfo)
						{
							if ($payInfo['id'] == $o['id'])
							{
								if ($o['code'] == 'AS000')
								{
									$result_id = _PS_PAYED_;
 								}
								else
								{
									$result_id = _PS_CANCELED_;
								}
								$answerInfo[] = array(
									'payment_id'	=> $payInfo['id'],
									'result_id'		=> $result_id,
									'msg'			=> ''
								);
							}
						}
					}
				}
				return $answerInfo;
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

	/**
	 * получить номер заказа по данным о платеже
	 *
	 * @param mixed $requestInfo - параметры ответа от платежной системы
	 * @return integer
	 */
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