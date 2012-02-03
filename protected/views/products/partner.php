<h2><?php echo Yii::t('common', 'Products'); ?></h2>
<?php
	if (!empty($pst))
	{
		echo '<h3>' . $pst[0]['prttitle'] . '</h3>';
		echo '<ul>';
		foreach ($pst as $p)
		{
			echo '<li><a href="/products/view/' . $p['id'] . '">' . $p['ptitle'] . '</a></li>';
		}
		echo '</ul>';
	}
?>