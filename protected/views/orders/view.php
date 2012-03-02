<h2><?php echo Yii::t('orders', 'Cart'); ?></h2>
<?php
	if (!empty($info[0]))
	{
		echo '<h3>' . Yii::t('orders', 'Order') . ' №' . $info[0]['oid'] . '</h3>';
		echo '<table border="1" cellpadding="1" cellspasing="1">';
		$summa = 0;
		foreach ($info as $inf)
		{
			$state = Yii::t('pays', 'Renting');
			if (empty($inf['rent_id']))
				$state = Yii::t('pays', 'Buying');
			echo'<tr>
				<td><a href="/products/view/' . $inf['pid'] . '">' . $inf['ptitle'] . '</a> (Вариант исполнения №' . $inf['pvid'] . ')</td>
				<td>' . $inf['cnt'] . '</td>
				<td>' . $state . '</td>
				<td>' . sprintf("%01.2f", $inf['price']) . ' ' . Yii::t('pays', _CURRENCY_) . '</td>
			</tr>';
			$summa = $summa + $inf['price'] * $inf['cnt'];
		}
		echo '</table>
		<form name="payOrderForm" action="/pays/do/3" method="post">
			<input type="hidden" name="order_id" value="' . $info[0]['oid'] . '"/>
			<input type="hidden" name="summa" value="' . $summa . '"/>
		</form>
		';

		$options = array();
		if ($info[0]['state'] == _ORDER_CART_)
		{
			$options[] = '<a id="dopayid">' . Yii::t('orders', 'Pay') . '</a>';
			$options[] = '<a id="dodiscardid">' . Yii::t('orders', 'Discard') . '</a>';
		}
		if (!empty($options))
		{
			echo implode(' ', $options);
?>
<script type="text/javascript">
	$( "#dopayid" )
				.button()
				.click(function() {
					document.payOrderForm.submit();
					return false;
	});
	$( "#dodiscardid" )
				.button()
				.click(function() {
					if (confirm('<?php echo Yii::t('common', 'Are you sure?');?>'))
					{
						oid = document.payOrderForm.order_id.value;
						$.post('/orders/discard/' + oid, function(){
							location.href = '/orders/view/' + oid;
						});
					}
					return false;
	});
</script>
<?php
		}
	}
	else
	{
		echo Yii::t('orders', 'Order deleted');
	}
