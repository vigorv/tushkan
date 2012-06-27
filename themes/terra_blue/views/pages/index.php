<div class="span12 no-horizontal-margin inside-movie my-catalog">
<?php

	if (!empty($info['meta_title']))
		$this->setPageTitle($info['meta_title']);
	if (!empty($info['meta_keywords']))
		Yii::app()->clientScript->registerMetaTag($info['meta_keywords'], 'keywords');
	if (!empty($info['meta_description']))
		Yii::app()->clientScript->registerMetaTag($info['meta_description'], 'description');

	echo '<h1>' . $info['title'] . '</h1>';
?>
	<div class="pad-content">
<?php
	echo $info['txt'];
?>
	</div>
</div>
