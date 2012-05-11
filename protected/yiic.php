<?php

// change the following paths if necessary
if (file_exists(dirname(__FILE__).'/../framework'))
	$yiic=dirname(__FILE__).'/../framework/yiic.php';
else
	$yiic=dirname(__FILE__).'/../../framework/yiic.php';
$config=dirname(__FILE__).'/config/console.php';

require_once($yiic);
