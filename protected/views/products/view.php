<?php
if(!empty($info))
{
?>
<script type="text/javascript">
	function doBuy(vid, prid)
	{
		$.post("/orders/buy/" + vid, {prid: prid}, function(data){
			oid = parseInt(data);
			if (oid > 0)
			{
				location.href="/orders/view/" + oid;
			}
		});
		return false;
	}
	function doRent(vid, rid)
	{
		$.post("/orders/rent/" + vid, {rid: rid}, function(data){
			oid = parseInt(data);
			if (oid > 0)
			{
				location.href="/orders/view/" + oid;
			}
		});
		return false;
	}
	function doTocart(vid, prid, rid)
	{
		$.post("/orders/tocart/" + vid, {rid: rid, prid: prid}, function(data){
			oid = parseInt(data);
			if (oid > 0)
			{
				location.href="/orders/view/" + oid;
			}
		});
		return false;
	}
	function doCloud(vid)
	{
		$.post("/universe/tadd/" + vid, function(data){
			oid = parseInt(data);
			if (oid > 0)
			{
				location.href="/universe/tview/" + oid;
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

		$variantsParams[$curVariantId][$variant['title']] = $variant['value'];
		if (count($variantsParams[$curVariantId]) > 2)
		{
			continue; //ПОЛНУЮ ИТЕРАЦИЮ (С ОПРЕДЕЛЕНИЕМ ДЕЙСТВИЙ) ДЛЯ ВАРИАНТА ДЕЛАЕМ ОДИН РАЗ
		}

		$inOrder = false; $actions = array();
		$isOwned = false; $isRented = false;
		$preOwned = false; $preRented = false;
		$inCloud = false;
		$order = array();

		if (!empty($typedFiles))
		{
			foreach ($typedFiles as $f)
			{
				if ($f['variant_id'] == $variant['id'])
				{
					$inCloud = true;
					$cloudId = $f['id'];
					break;
				}
			}
		}

		if (!empty($orders))
		{
			foreach ($orders as $order)
			{
				if ($order['variant_id'] == $variant['id'])
				{
					$inOrder = true;
					if ($order['state'] == _ORDER_PAYED_)
					{
						//ЕСЛИ ОПЛАТИЛИ
						if (!empty($order['price_id']))
							$isOwned = true;
						if (!empty($order['rent_id']))
						{
							$isRented = true;
						}
						break;
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
			$actionPay = '<a href="/orders/view/' . $order['oid'] . '">оплатить</a>';

		if (!empty($variant['price_id']))
			$actionBuy = '<a href="#" onclick="return doBuy(' . $variant['id'] . ', ' . $variant['price_id'] . ')">купить</a> за ' . $variant['pprice'] . ' rur';

		if (!empty($variant['rent_id']))
			$actionRent = '<a href="#" onclick="return doRent(' . $variant['id'] . ', ' . $variant['rent_id'] . ')">в аренду</a> за ' . $variant['rprice'] . ' rur';

		if (!empty($order['rent_id']) || !empty($order['price_id']))
		{
			$actionTocloud = 'добавить в пространство';
			if ($userInfo['free_limit'] > 0)
				$actionTocloud = '<a href="#" onclick="return doCloud(' . $variant['id'] . ')">' . $actionTocloud . '</a>';
			else
				$actionTocloud = '<s>' . $actionTocloud . '</s>';
			$actionTocloud .= ' <b>свободно ' . Utils::sizeFormat($userInfo['free_limit'] * 1024 * 1024) . '</b>';
		}
		if (!empty($variant['rent_id']) || !empty($variant['price_id']))
		{
			$actionTocart = '<a href="#" onclick="return doTocart(' . $variant['id'] . ', ' . intval($variant['price_id']) . ', ' . intval($variant['rent_id']) . ')">в корзину</a>';
		}

		if ($inCloud)
		{
			$actionOnline = '<a href="/universe/tview/id/' . $cloudId . '/do/online">смотреть</a>';
			$actionDownload = '<a href="/universe/tview/id/' . $cloudId . '/do/download">скачать</a>';
		}
		$rentDsc = '';
		if ($isRented)
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
		}

		if ($isOwned || $isRented)
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
				if (!empty($actionTocart))
					$actions[] = $actionTocart;
			}
			else
			{
				if (!empty($actionBuy))
					$actions[] = $actionBuy;
				if (!empty($actionRent))
					$actions[] = $actionRent;
				if (!empty($actionTocart))
					$actions[] = $actionTocart;
			}
		}

		if (!empty($actions))
		{
			$variantsParams[$curVariantId]['actions'] =  ' (' . implode(' | ', $actions) . ') ' . $rentDsc;
		}
	}

	if (!empty($variantsParams))
	{
		foreach ($variantsParams as $vps)
		{
			echo '<div class="shortfilm">';
			if (!empty($variantsParams['poster']))
			{
				$poster = $variantsParams['poster'];
				unset($variantsParams['poster']);
			}
			else
			{
				$poster = '/images/films/noposter.jpg';
			}
			echo '<img src="' . $poster . '" />';

			echo '<ul>';
			if (!empty($vps['actions']))
			{
				$actions = '<p>' . $vps['actions'] . '</p>';
				unset($vps['actions']);
			}
			else $actions = '';
			unset($vps['id']);
			unset($vps['url']);
			unset($vps['onlineurl']);

			foreach ($vps as $param => $value)
			{
				if ($param == Yii::app()->params['tushkan']['fsizePrmName'])
				{
					$value = Utils::sizeFormat($value);
				}
				echo '<li>' . Yii::t('params', $param) . ': ' . $value . '</li>';
			}
			echo'</ul>';
			echo $actions;
			echo'</div>';
		}
	}
}