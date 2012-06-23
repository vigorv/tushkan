<div class="span12 no-horizontal-margin inside-movie my-catalog">
<?php
	$order = ''; $summa = '';
	if (!empty($postInfo))
	{
		if (!empty($postInfo['order_id']))
		{
			$order = ' №' . $postInfo['order_id'];
			if (!empty($orderInfo))
			{
				//СЧИТАЕМ СУММУ ИЗ ЗАКАЗА
				$orderSumma = 0;
				foreach ($orderInfo as $o)
				{
					$orderSumma += $o['price'] * $o['cnt'];
				}
				$postInfo['summa'] = $orderSumma;
				if (count($orderInfo) == 1)
				{
					//ЕСЛИ ОДНА ПОЗИЦИЯ В ЗАКАЗЕ ВЫВОДИМ ИНФУ О НЕЙ
					$doing = Yii::t('pays', 'Buying');
					if (!empty($orderInfo[0]['rent_id']))
						$doing = Yii::t('pays', 'Renting');
					$order = '';
					$oInfo['title'] = $doing . ' "' . $orderInfo[0]['title'] . '"';
				}
			}
		}
		if (!empty($postInfo['summa']))
		{
			$summa = ' - ' . Yii::t('pays', 'amount') . " " . sprintf("%01.2f", $postInfo['summa']) . ' ' . Yii::t('pays', _CURRENCY_);
		}
	}
	echo '<h1>' . $oInfo['title'] . $order . $summa . '</h1>';
?>
	<div class="pad-content">
<?php
	if (!empty($lst))
	{
		$select = array();
		if ($oInfo['id'] <> 1)
			$select[] = 'Списать с баланса';
		$jsCondition = '(sid == 0)';
		foreach($lst as $l)
		{
			$select[$l['id']] = $l['title'];
			if ($l['is_ajax'])
			{
				$jsCondition .= ' || (sid == ' . $l['id'] . ')';
			}
		}
	}
?>
<script type="text/javascript">
	function pay()
	{
		sid = document.startPayForm.paysystem_id.value;
		summa = document.startPayForm.summa.value;
		if (<?php echo $jsCondition; ?>)
		{
<?php
	if (!empty($orderInfo[0]['id']))
	{
		echo 'order_id = ' . $orderInfo[0]['id'] . ';';
	}
	else
	{
		echo 'order_id = 0;';
	}
?>
			$.post("/pays/payment/" + sid, { order_id: order_id, user_id: "<?php echo Yii::app()->user->getId();?>", summa: summa, operation_id: "<?php echo $oInfo['id']; ?>" }, function(data){
				if (data == '_PS_PAYED_')
					$("#content").load('/pays/ok/' + sid, {order_id: order_id});
				else
					$("#content").load('/pays/fail/' + sid, {order_id: order_id});
				updateActualBalance();
			});
		}
		else
		{
			return true;
		}
		return false;
	}
</script>
<div>

<?php echo CHtml::beginForm('/pays/payment/', "post", array('name' => 'startPayForm', 'onsubmit' => 'return pay();' ,'class'=>'form-horizontal')); ?>
    <fieldset>
        <?php echo CHtml::label('Выбрать платежную систему', 'paysystem_id'); ?>
        <?php
        	echo CHtml::dropdownlist('paysystem_id', 0, $select);
        ?>

<?php
	if (!empty($orderInfo[0]['id']))
	{
		echo CHtml::hiddenField('order_id', $orderInfo[0]['id']);
	}

	if (empty($summa))
	{
?>
<p></p>
<?php
		//echo CHtml::label(Yii::t('pays', 'Amount'), 'summa', array("required" => 1));
		echo CHtml::textField('summa', '', array('placeholder' => Yii::t('pays', 'Amount')));
?>

<?php
	}
	else
	{
		echo CHtml::hiddenField('summa', $postInfo['summa']);
	}
?>
   </fieldset>
<?php echo CHtml::endForm(); ?>
</div>
<?php
		$options = array();
		$options[] = '<button class="btn" id="dopayid">' . Yii::t('orders', 'Pay') . '</button>';
		if (!empty($orderInfo[0]['id']))
		{
			$options[] = '<button class="btn" id="dodiscardid">' . Yii::t('orders', 'Discard') . '</button>';
		}
		if (!empty($options))
		{
			echo implode(' ', $options);
?>
<script type="text/javascript">
	$( "#dopayid" )
				.button()
				.click(function() {
					document.startPayForm.onsubmit();
					return false;
	});
<?php
		if (!empty($orderInfo[0]['id']))
		{
?>
	$( "#dodiscardid" )
				.button()
				.click(function() {
					if (confirm('<?php echo Yii::t('common', 'Are you sure?');?>'))
					{
						oid = <?php echo $orderInfo[0]['id'];?>;
						//$.post('/orders/discard/' + oid, function(){
							$('#content').load('/orders/discard/' + oid);
						//});
					}
					return false;
	});
<?php
		}
?>
</script>
<?php
		}
?>
	</div>
</div>