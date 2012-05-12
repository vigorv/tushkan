<?php
function getViewActions($variant, $orders, $actualRents, $qualities, $typedFiles, $presets, $qualityVariantId = 0, $qualityPresetId = 0)
{
	$inOrder = false; $actions = array();
	$isOwned = false; $isRented = false;
	$preOwned = false; $preRented = false;
	$inCloud = false; $cloudId = 0;
	$order = array();

	if (!empty($typedFiles))
	{
		foreach ($typedFiles as $f)
		{
			if ($f['variant_id'] == $variant['id'])
			{
				if (empty($qualityVariantId))
				{
					$inCloud = true;
					$cloudId = $f['id'];
				}
				if (!empty($qualityVariantId))
				{
					if ($f['variant_quality_id'] == $qualityVariantId)
					{
						$inCloud = true;
						$cloudId = $f['id'];
					}
					if ($f['preset_id'] >= $qualityPresetId)
					{
						$inCloud = true;
						$cloudId = $f['id'];
					}
				}
			}
		}
	}

	$rentDsc = '';
	if (!empty($orders))
	{
		foreach ($orders as $order)
		{
			if ($order['variant_id'] == $variant['id'])
			{
				if (!empty($qualityVariantId) && ($order['variant_quality_id'] != $qualityVariantId))
				{
//ЕСЛИ ДОПОЛНИТЕЛЬНАЯ ПРОВЕРКА ПО КАЧЕСТВУ ВАРИАНТА НЕ ПРОШЛА, ЗНАЧИТ ПРОДОЛЖАЕМ ИСКАТЬ ДРУГОЕ КАЧЕСТВО
					if ($qualityPresetId > $order['preset_id'])
						continue;
				}

				$inOrder = true;
				if ($order['state'] == _ORDER_PAYED_)
				{
					//ЕСЛИ ОПЛАТИЛИ
					if (!empty($order['price_id']))
					{
						$isOwned = true; $isRented = false;
						break;
					}
					if (!empty($order['rent_id']))
					{
						//ОПРЕДЕЛЯЕМ ПЕРИОД ПО ТЕКУШЕЙ АРЕНДЕ
						foreach ($actualRents as $a)
						{
							if ($a['variant_id'] == $variant['id'])
							{
								$rentDsc = ' арендовано на ' . Utils::spellPeriod($a['period']);
								$start = strtotime($a['start']);
								if ($start > 0)
								{
									$less = $start + Utils::parsePeriod($a['period'], $a['start']) - time();
									if ($less)
									{
										$isRented = true;
										$rentDsc .= ' до окончания аренды ' . Utils::timeFormat($less);
										break;
									}
									else
									{
										$isRented = false; $inOrder = false;
										$rentDsc .= ' срок аренды истек';
										//ПРОДОЛЖАЕМ ПЕРЕБОР ТК АРЕНДОВАНО МОЖЕТ БЫТЬ ПОВТОРНО
									}
								}
								else
								{
									$isRented = true;
									$break;
								}
							}
						}

						if ($isRented) break;
					}
				}

				if ($order['state'] == _ORDER_CART_)
				{
					//ЕСЛИ В КОРЗИНЕ
					if (!empty($order['price_id']))
						$preOwned = true;
					if (!empty($order['rent_id']))
						$preRented = true;
					break;
				}
			}
		}
	}

	if (!empty($order['oid']))
	{
		$actionPay = '';
		if (empty($qualityVariantId))
			$actionPay = '<a href="/orders/view/' . $order['oid'] . '">оплатить</a>';
		else
		{
			foreach ($orders as $order)
			{
				if (($order['variant_id'] == $variant['id']) && ($order['variant_quality_id'] == $qualityVariantId))
				{
					$actionPay = '<a href="/orders/view/' . $order['oid'] . '">оплатить</a>';
				}
			}
			if (empty($actionPay))
				$inOrder = false;
		}
	}

	if (!empty($variant['price_id']))
		$actionBuy = '<a href="#" onclick="return doBuy(' . $variant['id'] . ', ' . $variant['price_id'] . ', ' . $qualityVariantId . ')">купить</a> за ' . $variant['pprice'] . ' rur';

	if (!empty($variant['rent_id']))
		$actionRent = '<a href="#" onclick="return doRent(' . $variant['id'] . ', ' . $variant['rent_id'] . ', ' . $qualityVariantId . ')">в аренду</a> за ' . $variant['rprice'] . ' rur';

	if ($isOwned || $isRented || (empty($variant['rent_id']) && empty($variant['price_id'])))
	{
		if (!$inCloud)
		{
			$actionTocloud = 'добавить ко мне';
			$actionTocloud = '<a href="#" onclick="return doCloud(' . $variant['id'] . ', ' . $qualityVariantId . ')">' . $actionTocloud . '</a>';
		}
	}
	if (!empty($variant['rent_id']) || !empty($variant['price_id']))
	{
		$actionTocart = '<a href="#" onclick="return doTocart(' . $variant['id'] . ', ' . intval($variant['price_id']) . ', ' . intval($variant['rent_id']) . ', ' . $qualityVariantId . ')">в корзину</a>';
	}

	if ($inCloud)
	{
		//ИЩЕМ МАКСИМАЛЬНОЕ КАЧЕСТВО ДЛЯ ВАРИАНТА ЛИБО ОПРЕДЕЛЕННОЕ КАЧЕСТВО
		$q = '';
		foreach ($qualities as $quality)
		{
			if ($quality['variant_id'] == $variant['id'])
			{
				foreach ($presets as $p)
				{
					if (!empty($qualityVariantId))
					{
						if (($p['id'] == $quality['preset_id']) && ($qualityVariantId == $quality['id']))
						{
							$q = $p['title'];
							break;
						}
					}
					else
					{
						if ($p['id'] == $quality['preset_id'])
						{
							$q = $p['title'];
							break;
						}
					}
				}
			}
		}
		$q = '/quality/' . $q;
		$actionOnline = '<a href="/universe/tview/id/' . $cloudId . '/do/online' . $q . '">смотреть</a>';
		$actionDownload = '<a href="/universe/tview/id/' . $cloudId . '/do/download' . $q . '">скачать</a>';
	}

	if ($isOwned || $isRented || $inCloud)
	{
		if (!$inCloud)
		{
			if (!empty($actionTocloud))
				$actions[] = $actionTocloud;
		}
		else
		{
			if ($variant['online_only'])
				$actions[] = $actionOnline;
			else
			{
				if (!$isRented)
					$actions[] = $actionDownload;
				$actions[] = $actionOnline;
			}
		}
	}
	else
	{
		if ($inOrder && ($preOwned || $preRented))
		{
			$actions[] = $actionPay;
			if (($preOwned)&&(!empty($actionRent)))
				$actions[] = $actionRent;
			if (($preRented)&&(!empty($actionBuy)))
				$actions[] = $actionBuy;
//			if (!empty($actionTocart))
//				$actions[] = $actionTocart;
		}
		else
		{
			if (!empty($actionBuy))
				$actions[] = $actionBuy;
			if (!empty($actionRent))
				$actions[] = $actionRent;
//			if (!empty($actionTocart))
//				$actions[] = $actionTocart;
			if (!empty($actionTocloud))
				$actions[] = $actionTocloud;
		}
	}
	if (!empty($actions))
	{
		$actions =  'Действия: (' . implode(' | ', $actions) . ') ' . $rentDsc;
	}
	return $actions;
}

if(!empty($info))
{
	$presets = CPresets::getPresets();
?>
<form name="quickpayform" method="post" action="/pays/do/3">
	<input type="hidden" name="summa" value="" />
	<input type="hidden" name="order_id" value="" />
</form>
<script type="text/javascript">
	function doBuy(vid, prid, qvid)
	{
		$.post("/orders/buy/" + vid, {prid: prid, qvid: qvid}, function(data){
			oid = parseInt(data);
			if (oid > 0)
			{
				//document.quickpayform.order_id.value = oid;
				//document.quickpayform.submit();
				$.post("/pays/do/3", {order_id: oid}, function(data){
					$("#content").html(data);
				});
			}
		});
		return false;
	}
	function doRent(vid, rid, qvid)
	{
		$.post("/orders/rent/" + vid, {rid: rid, qvid: qvid}, function(data){
			oid = parseInt(data);
			if (oid > 0)
			{
//				document.quickpayform.order_id.value = oid;
//				document.quickpayform.submit();
				$.post("/pays/do/3", {order_id: oid}, function(data){
					$("#content").html(data);
				});
			}
		});
		return false;
	}
	function doTocart(vid, prid, rid, qvid)
	{
		$.post("/orders/tocart/" + vid, {rid: rid, prid: prid, qvid: qvid}, function(data){
			oid = parseInt(data);
			if (oid > 0)
			{
				$.address.value("/orders/view/" + oid);
			}
		});
		return false;
	}
	function doCloud(vid, qvid)
	{
		$.post("/universe/tadd/" + vid, {qvid: qvid}, function(data){
			oid = parseInt(data);
			if (oid > 0)
			{
				$.address.value("/universe/tview/" + oid);
			}
		});
		return false;
	}
</script>
<?php
	echo'<h2>' . $productInfo['title'] . '</h2><ul>';
	$curVariantId = 0;
	$variantsParams = array(); //ЗДЕСЬ СОБИРАЕМ ВСЕ ПАРАМЕТРЫ ВСЕХ ВАРИАНТОВ
	foreach ($info as $variant)
	{
		if ($curVariantId <> $variant['id'])
		{
			//ВЫВОДИМ ПАРАМЕТРЫ ПРЕДЫДУЩЕГО ВАРИАНТА ПРИ ПЕРЕХОДЕ К НОВОМУ
			$curVariantId = $variant['id'];
			$variantsParams[$curVariantId] = array();
			$variantsParams[$curVariantId]['id'] = $curVariantId;
		}

		$variantsParams[$curVariantId]['sub_id'] = $variant['sub_id'];
		$variantsParams[$curVariantId]['vtitle'] = $variant['vtitle'];
		$variantsParams[$curVariantId]['pprice'] = $variant['pprice'];
		$variantsParams[$curVariantId]['price_id'] = $variant['price_id'];
		$variantsParams[$curVariantId]['rprice'] = $variant['rprice'];
		$variantsParams[$curVariantId]['rent_id'] = $variant['rent_id'];
		$variantsParams[$curVariantId]['online_only'] = $variant['online_only'];
		$variantsParams[$curVariantId][$variant['title']] = $variant['value'];
		if (count($variantsParams[$curVariantId]) > 9)
		{
			continue; //ПОЛНУЮ ИТЕРАЦИЮ (С ОПРЕДЕЛЕНИЕМ ДЕЙСТВИЙ) ДЛЯ ВАРИАНТА ДЕЛАЕМ ОДИН РАЗ
		}
	}
/*
	echo'<pre>';
	print_r($variantsParams);
	echo'</pre>';
exit;
//*/
	if (!empty($variantsParams))
	{
		$currentQuality = 0;
		$aContent = '<table cellpadding="5">';
		$num = 1;
		foreach ($variantsParams as $vk => $vps)
		{
			if (!empty($vps['actions']))
			{
				$actions = $vps['actions'];
				unset($vps['actions']);
			}
			else $actions = '';
			$qs = array();
			$variantQualityCnt = 0;//КОЛ_ВО КАЧЕСТВ ТЕКУЩЕГО ВАРИАНТА
			foreach ($qualities as $q)
			{
				if ($q['variant_id'] == $vps['id'])
				{
					foreach ($presets as $p)
					{
						if ($p['id'] == $q['preset_id'])
						{
							$actions = ''; //ДЕЙСТВИЯ ДЛЯ КАЧЕСТВА
							if (!empty($q['pprice']) || !empty($q['rprice']))
							{
								$variantQualityCnt++;
//БЕРЕМ ЦЕНУ ДЛЯ КАЧЕСТВА
								$qs = array(
									'id'		=> $q['variant_id'],
									'title'		=> $p['title'],
									'pprice'	=> $q['pprice'],
									'price_id'	=> $q['price_id'],
									'rprice'	=> $q['rprice'],
									'rent_id'	=> $q['rent_id'],
									'preset_id'	=> $q['preset_id'],
									'online_only'	=> $variant['online_only'],
								);
								$actions = getViewActions($qs, $orders, $actualRents, $qualities, $typedFiles, $presets, $q['id'], $q['preset_id']);
							}
//ВЫВОД ДЕЙСТВИЙ ДЛЯ КАЧЕСТВА
							$aContent .= '<tr><td></td><td></td><td>Качество: ' . $p['title'] . ' ' . $actions . '</td></tr>';
							break;
						}
					}
				}
			}

			if (empty($variantQualityCnt))
			{
//ЕСЛИ В ТЕКУЩЕМ ВАРИАНТЕ НЕТ ЦЕН ДЛЯ КАЧЕСТВ, БЕРЕМ ЦЕНУ ДЛЯ ВАРИАНТА
				$actions = getViewActions($vps, $orders, $actualRents, $qualities, $typedFiles, $presets);
//ВЫВОД ДЕЙСТВИЙ ДЛЯ ВАРИАНТА
				if (count($variantsParams) == 1)
					$vps['vtitle'] = '';
				else
					$vps['vtitle'] .= $num++;
				$aContent .= '<tr><td>' . $vps['vtitle'] . '</td><td></td><td>' . $actions . '</td></tr>';
			}
		}
		$aContent .= '</table>';
		//ВЫВОД КАЧЕСТВ И ДЕЙСТВИЙ
		echo $aContent;

		//ВЫВОД ПАРАМЕТРОВ
		foreach ($variantsParams as $vk => $vps)
		{
			echo '<div id="productdetail">';
			if (!empty($vps['poster']))
			{
				$poster = $vps['poster'];
				unset($vps['poster']);
			}
			else
			{
				$poster = '/images/films/noposter.jpg';
			}
			echo '<img hspace="3" align="left" src="' . $poster . '" />';

			echo '<p>';
			unset($vps['pprice']);
			unset($vps['price_id']);
			unset($vps['rprice']);
			unset($vps['rent_id']);
			unset($vps['online_only']);

			unset($vps['id']);
			unset($vps['url']);
			unset($vps['height']);
			unset($vps['width']);
			unset($vps['onlineurl']);
			unset($vps['sub_id']);
			unset($vps['vtitle']);

			foreach ($vps as $param => $value)
			{
				if (empty($value)) continue;
				if ($param == 'actions') continue;
				if ($param == Yii::app()->params['tushkan']['fsizePrmName'])
				{
					$value = Utils::sizeFormat($value);
				}
				echo '<br />' . Yii::t('params', $param) . ': ' . $value;
			}
			echo'</p>';
			if (!empty($dsc['description']))
				echo '<p>' . $dsc['description'] . '</p>';
			echo'</div>';
			break;//ВЫВОДИМ ОДИН РАЗ
		}
	}
}