<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="language" content="ru" />

        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
    <!--[if lt IE 8]><link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" type="text/css" media="screen, projection"><![endif]-->

        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" />

        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/tushkan.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/admin.css" />

        <?php
        Yii::app()->getClientScript()->registerCoreScript('jquery');
        Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/jquery-ui/js/jquery-ui-1.8.16.custom.min.js");
        Yii::app()->getClientScript()->registerCssFile(Yii::app()->request->baseUrl . "/js/jquery-ui/css/pepper-grinder/jquery-ui-1.8.16.custom.css");
        ?>
    </head>
    <body>
        <?php
        $this->widget('zii.widgets.CBreadcrumbs', array(
            'links' => $this->breadcrumbs,
        ));
        $this->pageTitle = Yii::app()->name;
        ?>
        <div class="container">
            <div id="adminleft">
                <ul>Administrator resources
                    <li><a href="<?php echo $this->createUrl('users/admin'); ?>">Users</a></li>
                    <li>Groups</li>
                    <li><a href="<?php echo $this->createUrl('films/admin'); ?>">Films</a></li>
                </ul>
            </div>
            <div id="admincontent">
                <?php if (Yii::app()->user->hasFlash('success')): ?>
                    <div class="flash-notice">
                        <?php echo Yii::app()->user->getFlash('success') ?>
                    </div>
                <?php endif ?>
                <?php if (Yii::app()->user->hasFlash('error')): ?>
                    <div class="flash-error">
                        <?php echo Yii::app()->user->getFlash('error') ?>
                    </div>
                <?php endif ?>

                <?php echo $content; ?>
            </div>
        </div>

    </body>
</html>