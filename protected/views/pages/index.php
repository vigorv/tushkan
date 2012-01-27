<?php

	if (!empty($info['meta_title']))
		$this->setPageTitle($info['meta_title']);
	if (!empty($info['meta_keywords']))
		Yii::app()->clientScript->registerMetaTag($info['meta_keywords'], 'keywords');
	if (!empty($info['meta_description']))
		Yii::app()->clientScript->registerMetaTag($info['meta_description'], 'description');

	echo '<h2>' . $info['title'] . '</h2>';

	echo $info['txt'];