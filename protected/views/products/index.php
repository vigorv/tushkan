<h2><?php echo Yii::t('common', 'Products'); ?></h2>
<?php
	if (!empty($lst))
	{
		echo '<ul>';
		foreach ($lst as $l)
			echo '<li><a href="/products/view/' . $l['pid'] . '">' . $l['ptitle'] . '</a> (' . $l['ttitle'] . ')</li>';
		echo '</ul>';
	}
?>