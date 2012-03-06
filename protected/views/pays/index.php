<form action="/pays/index" method="get">
<h3><?php echo Yii::t('common', 'Over a period');?>
	<a href="/pays/index/from/<?php echo date('Y-m-d', time() - 3600*24*7);?>"><?php echo Yii::t('common', 'week');?></a> |
	<a href="/pays/index/from/<?php echo date('Y-m-d', time() - 3600*24*30);?>"><?php echo Yii::t('common', 'month');?></a>
	<?php echo Yii::t('common', 'from');?>: <input id="dpfrom" name="from" value="<?php echo $from;?>" type="text" onchange="return showSubmit()" />
	<?php echo Yii::t('common', 'to');?>: <input id="dpto" name="to" value="<?php echo $to;?>" type="text" onchange="return showSubmit()" />
	<input type="submit" id="periodSubmit" value="<?php echo Yii::t('common', 'Submit');?>" />
</h3>
</form>
<?php
	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/jquery-ui/jquery.ui.datepicker-ru.js");

	$summa = 0;
	if (!empty($balance['balance']))
		$summa = sprintf("%01.2f", $balance['balance']);
	echo '<h2>' . Yii::t('users', 'Account balance') . ' (' . $summa . ' ' . Yii::t('pays', _CURRENCY_) . ')</h2>';

	echo '<h3><a href="/pays/do/1">' . Yii::t('users', 'Fill up balance') . '</a></h3>';

	if (!empty($debits))
	{
		echo '<p>Списания';
		foreach ($debits as $d)
		{
			echo '<br />' . $d['created'] . ' - ' . $operations[$d['operation_id']] . ', ' . Yii::t('orders', 'Sum') . ': ' . sprintf("%01.2f", $d['summa']) . ' ' . Yii::t('pays', _CURRENCY_);
		}
		echo '</p>';
	}
	if (!empty($incs))
	{
		echo '<p>Пополнения';
		foreach ($incs as $i)
		{
			echo '<br />' . $i['created'] . ' - ' . $operations[$i['operation_id']] . ', ' . Yii::t('orders', 'Sum') . ': ' . sprintf("%01.2f", $i['summa']) . ' ' . Yii::t('pays', _CURRENCY_);
		}
		echo '</p>';
	}
?>
<script type="text/javascript">
	function showSubmit()
	{
		if ($('#dpfrom').val() || $('#dpto').val())
		{
			$( "#periodSubmit" ).button({disabled: false});
		}
		else
		{
			$( "#periodSubmit" ).button({disabled: true});
		}
		return true;
	}

	$(function() {
		$( "#dpfrom" ).datepicker({ dateFormat: 'yy-mm-dd'});
		$( "#dpto" ).datepicker({ dateFormat: 'yy-mm-dd'});
		$( "#periodSubmit" ).button();
		showSubmit();
	});
</script>
