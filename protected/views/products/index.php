<h2><?php echo Yii::t('common', 'Partners'); ?></h2>
<?php
	if (!empty($lst))
	{
		echo '<ul>';
		foreach ($lst as $l)
			echo '<li><a href="/products/partner/' . $l['id'] . '">' . $l['title'] . '</a></li>';
		echo '</ul>';
	}
?>