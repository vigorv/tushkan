<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="language" content="ru" />
    <!-- blueprint CSS framework -->
    <!--  <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" /> -->
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
    <!--[if lt IE 8]>
    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
    <![endif]-->

    <link rel="stylesheet" type="text/css"  href="/css/bootstrap.min.css"/>
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <script src="/js/bootstrap.min.js"></script>
    <?php
    Yii::app()->getClientScript()->registerScriptFile("http://yui.yahooapis.com/3.5.1/build/yui/yui-min.js");
    ?>
    <script src="/js/bootstrap.min.js"></script>
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>
<body>
<div id="content">
    <img src="/images/cloud.png"/><br/>
    <?php echo $content; ?>
</div>
</body>
</html>