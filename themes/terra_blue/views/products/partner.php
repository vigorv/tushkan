<div class="span12 no-horizontal-margin inside-movie my-catalog">
<h1><?php echo $pInfo['title']; ?></h1>
	<div class="pad-content">
<?php
	echo $warning18plus;

	if (!empty($pstContent))
		echo $pstContent;
	else
		echo Yii::t('common', 'Nothing was found');
?>
	</div>
</div>