<?php
if(!empty($info))
{
	$title = '';
	foreach ($info as $variant)
	{
		if (empty($title))
		{
			$title = $variant['ptitle'];
			echo'<h2>' . $title . '</h2><ul>';
		}
		echo '<li>Вариант исполнения №' . $variant['pvid'];
		$inOrder = false; $actions = array();
		$isOwned = false; $isRented = false;
		$preOwned = false; $preRented = false;

		if (!empty($variant['period']))//ЗНАЧИТ ПРИСУТСТВУЕТ В ТЕКУЩЕЙ АРЕНДЕ
		{
			$inOrder = true;
			if (empty($variant['start']) || (!empty($variant['start']) && ($variant['start'] + $variant['period'] < time())))
			{
				$isRented = true;
			}
			else
			{
				$inOrder = false;
			}
		}

		if (!$inOrder && !empty($orders))
		{
			foreach ($orders as $order)
			{
				if ($order['variant_id'] == $variant['pvid'])
				{
					$inOrder = true;
					if ($order['state'] == _ORDER_PAYED_)
					{
						//ЕСЛИ ОПЛАТИЛИ
						if (!empty($variant['prid']))
							$isOwned = true;
						if (!empty($variant['rid']))
							$isRented = true;
						break;
					}
					if ($order['state'] == _ORDER_CART_)
					{
						//ЕСЛИ В КОРЗИНЕ
						if (!empty($variant['prid']))
							$preOwned = true;
						if (!empty($variant['rid']))
							$preRented = true;
						break;
					}
				}
			}
		}
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
</script>
<?php
		if (!empty($order['oid']))
			$actionPay = '<a href="/orders/view/' . $order['oid'] . '">оплатить</a>';

		if (!empty($variant['prid']))
			$actionBuy = '<a href="#" onclick="return doBuy(' . $variant['pvid'] . ', ' . $variant['prid'] . ')">купить</a> (' . $variant['pprice'] . ' rur)';

		if (!empty($variant['rid']))
			$actionRent = '<a href="#" onclick="return doRent(' . $variant['pvid'] . ', ' . $variant['rid'] . ')">в аренду</a> (' . $variant['rprice'] . ' rur)';

		if (!empty($variant['rid']) || !empty($variant['prid']))
			$actionTocart = '<a href="#" onclick="return doTocart(' . $variant['pvid'] . ', ' . intval($variant['prid']) . ', ' . intval($variant['rid']) . ')">в корзину</a>';

		//$actionOnline = '<a href="#" onclick="return doOnline(' . $variant['pvid'] . ')">смотреть</a>';
		$actionOnline = '<a href="/products/online/' . $variant['pvid'] . '">смотреть</a>';
		if (!empty($variant['period']))
		{
			$actionOnline .= ' арендовано на ' . $variant['period'] . ' сек.';
		}
		if (strtotime($variant['start']) > 0)
		{
			$actionOnline .= ' до окончания аренды ' . (strtotime($variant['start']) + $variant['period'] - time()) . ' сек.';
		}

		$actionDownload = '<a href="#" onclick="return doDownload(' . $variant['pvid'] . ')">скачать</a>';

		if ($isOwned || $isRented)
		{
			if ($variant['online_only'])
				$actions[] = $actionOnline;
			else
			{
				$actions[] = $actionDownload;
				$actions[] = $actionOnline;
			}
		}
		else
		{
			if ($inOrder)
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
			echo ' (' . implode(' | ', $actions) . ')';
		}
	}
	echo'</ul>';
}