<h2><?php echo Yii::t('common', 'Partners'); ?></h2>
<table><tr valign="top"><td width="100%">
<?php
	if (!empty($lst))
	{
		echo '<ul>';
		foreach ($lst as $l)
			echo '<li><a href="/products/partner/' . $l['id'] . '">' . $l['title'] . '</a></li>';
		echo '</ul>';
	}
?></td><td>
<form method="get" action="/products/index">
<input type="text" name="search" />
<input type="submit" value="<?php echo Yii::t('common', 'Search');?>" />
</form></tr></table>
<?php
	echo $pstContent;