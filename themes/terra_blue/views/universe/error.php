<div class="span12 no-horizontal-margin inside-movie my-catalog">
<?php
	echo '<h1>' . $code . '</h1>';
?>
	<div class="pad-content">

<div class="error">
<?php echo CHtml::encode($message); ?>
</div>
<?php
	if (!empty($warning18plus))
		echo $warning18plus;
?>
</div>
</div>