<h2><?php echo Yii::t('common', 'Pages'); ?></h2>
<div>
<a href="<?php echo $this->createUrl('/pages/form');?>"><?php echo Yii::t('pages', 'Add page');?></a>
</div>
<?php
	if (!empty($lst))
	{
		echo '<ul>';
		foreach ($lst as $l)
			echo '<li><a href="/pages/edit/' . $l['id'] . '">' . $l['title'] . '</a> (<a href="/pages/index/' . $l['id'] . '">смотреть</a>)</li>';
		echo '</ul>';
	}
?>