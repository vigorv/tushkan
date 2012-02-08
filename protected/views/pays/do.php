<?php
	$order = ''; $summa = '';
	if (!empty($postInfo))
	{
		if (!empty($postInfo['order_id']))
		{
			$order = ' №' . $postInfo['order_id'];
		}
		if (!empty($postInfo['summa']))
		{
			$summa = ' ' . Yii::t('pays', 'amount') . " " . $postInfo['summa'] . ' ' . Yii::t('pays', _CURRENCY_);
		}
	}
	echo '<h2>' . $oInfo['title'] . $order . $summa . '</h2>';

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
	if (!empty($order))
	{
		echo 'order_id = ' . $postInfo['order_id'] . ';';
	}
	else
	{
		echo 'order_id = 0;';
	}
?>
			$.post("/pays/payment/" + sid, { order_id: order_id, user_id: "<?php echo Yii::app()->user->getId();?>", summa: summa, operation_id: "<?php echo $oInfo['id']; ?>" }, function(data){
				if (data == '_PS_PAYED_')
					location.href = '/pays/ok/' + sid;
				else
					location.href = '/pays/fail/' + sid;
			});
		}
		else
		{
			document.startPayForm.submit();
		}
		return false;
	}
</script>
<div class="form">
<?php echo CHtml::beginForm('/pays/payment/', "post", array('name' => 'startPayForm')); ?>
    <div class="row">
        <?php echo CHtml::label('Выбрать платежную систему', 'paysystem_id'); ?>
        <?php
        	echo CHtml::dropdownlist('paysystem_id', 0, $select);
        ?>
    </div>
<?php
	if (!empty($order))
	{
		echo CHtml::hiddenField('order_id', $postInfo['order_id']);
	}

	if (empty($summa))
	{
?>
    <div class="row">
<?php
		echo CHtml::label(Yii::t('pays', 'Amount'), 'summa', array("required" => 1));
		echo CHtml::textField('summa');
?>
    </div>
<?php
	}
	else
	{
		echo CHtml::hiddenField('summa', $postInfo['summa']);
	}
?>
<?php echo CHtml::button(Yii::t('orders', 'Pay'), array('onclick' => 'return pay();')); ?>
<?php echo CHtml::endForm(); ?>
</div>