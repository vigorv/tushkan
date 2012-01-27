<h2><?php echo Yii::t('params', 'Type params'); ?></h2>
<div>
<a href="<?php echo $this->createUrl('/params/form');?>"><?php echo Yii::t('params', 'Add param');?></a>
</div>
<?php
	if (!empty($params))
	{
		echo '<ul>';
		foreach ($params as $p)
			echo '<li><a href="/params/edit/' . $p['id'] . '">' . $p['title'] . '</a> (' . Yii::t('params', $p['title']) . ')</li>';
		echo '</ul>';
	}
?>