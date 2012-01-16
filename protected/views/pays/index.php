<?php
	$summa = 0;
	if (!empty($balance['balance']))
		$summa = sprintf("%01.2f", $balance['balance']);
	if (!empty($lst))
	{
		echo '<ul>Пополнить баланс счета';
		echo ' (' . $summa . ')';
		foreach($lst as $l)
		{
			echo '<li>' . $l['class'];
			$href = 'href=""';
			if ($l['id'] == 1)
			{
?>
	<script type="text/javascript">
		function diamondPay()
		{
			$.post("/pays/payment/1", { user_id: "<?php echo Yii::app()->user->getId();?>", summa: "100.5" , operation_id: "1" }, function(data){
				//alert(data);
				if (data == '_PS_PAYED_')
					location.href = '/pays/ok/1';
				else
					location.href = '/pays/fail/1';
			});
			return false;
		}
	</script>
<?php
				$href = 'href="#" onclick="return diamondPay();"';
			}
			echo ' <a ' . $href . '>' . Yii::t('users', 'Fill up balance') . '</a></li>';
		}
		echo '</ul>';
	}