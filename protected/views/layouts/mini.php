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

    <link rel="stylesheet/less" type="text/css" href="/less/mini.less"/>
    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    <?php
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery-1.7.1.min.js');
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.hotkeys.js');
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.cookie.js');
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.form.js');
    Yii::app()->getClientScript()->registerCssFile(Yii::app()->request->baseUrl . "/js/jquery-ui/css/pepper-grinder/jquery-ui-1.8.16.custom.css");
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/jquery-ui/js/jquery-ui-1.8.16.custom.min.js");
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.address-1.4.min.js');
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/multiuploader.js");
    ?>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/less-1.2.2.min.js"></script>

    <title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>
<body>
<div id="content">
    <img src="/images/cloud.png"/><br/>
    <?php echo $content; ?>
</div>
</body>
</html>