<h2>Error <?php echo $code; ?></h2>

<div class="error">
<?php echo CHtml::encode($message); ?>
</div>
<?php
	if (!empty($warning18plus))
		echo $warning18plus;
