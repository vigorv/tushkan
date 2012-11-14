<?php

//date_default_timezone_set('Europe/Moscow');
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
// change the following paths if necessary
$yii = dirname(__FILE__) . '/../framework/yii.php';

$config = dirname(__FILE__) . '/protected/config/stable/main.php';
if ((isset($_COOKIE['DEBUG']) && ($_COOKIE['DEBUG'] == 'kolpman')) || (file_exists(dirname(__FILE__) . '/protected/config/develop'))) {
// remove the following lines when in production mode
    defined('YII_DEBUG') or define('YII_DEBUG', true);
// specify how many levels of call stack should be shown in each log message
    defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 3);
    if (isset($_COOKIE['DEBUG_CONFIG'])){
        $config = dirname(__FILE__) . '/protected/config/dev/main.php';
    }
}


require_once($yii);
Yii::createWebApplication($config)->run();
