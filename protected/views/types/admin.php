<h2><?php echo Yii::t('types', 'Types of products'); ?></h2>
<div>
<a href="<?php echo $this->createUrl('/types/form');?>"><?php echo Yii::t('types', 'Add type');?></a>
</div>
<?php
	if (!empty($types))
	{
		echo '<ul>';
		foreach ($types as $t)
			echo '<li><a href="/types/edit/' . $t['id'] . '">' . $t['title'] . '</a> (' . Yii::t('types', 'Buy limit') . ' - <b>' . $t['buy_limit'] . '</b>)</li>';
		echo '</ul>';
	}
?>