<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="language" content="ru" />
        <!-- blueprint CSS framework -->
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
        <!--[if lt IE 8]>
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
        <![endif]-->

        <title><?php echo CHtml::encode($this->pageTitle); ?></title>
	<?php
	Yii::app()->getClientScript()->registerCssFile(Yii::app()->request->baseUrl . "/scss/mycloud.sass");
	Yii::app()->getClientScript()->registerCoreScript('jquery');
	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.hotkeys.js');
	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.cookie.js');
	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jstree/jquery.jstree.js');
	Yii::app()->getClientScript()->registerCssFile(Yii::app()->request->baseUrl . "/js/jquery-ui/css/pepper-grinder/jquery-ui-1.8.16.custom.css");
	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/jquery-ui/js/jquery-ui-1.8.16.custom.min.js");
	?>
        <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    </head>
    <body>
        <div class="container" id="page">
	    <div id="status_panel">
		<div id="mail">

		</div>
		<div id="balance">

		</div>
		<div id="space">

		</div>
	    </div>

	    <div class="notify_area">
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
	    </div>

	    <div id="win_shopers">
		<div id="win_mover">
		</div>
		<div id="win_items">

		</div>

	    </div>

	    <div id="content">
		<?php echo $content; ?>
	    </div>

	    <div id="footer">
		Copyright &copy; <?php echo date('Y'); ?> <?php echo CHtml::encode(Yii::app()->name); ?><br/>
		All Rights Reserved.<br/>
		<?php //echo Yii::powered();   ?>
	    </div><!-- footer -->

        </div><!-- page -->

    </body>
</html>