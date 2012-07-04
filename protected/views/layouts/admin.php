<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="language" content="ru" />

		<link href="/css/bootstrap.min.css" rel="stylesheet">
		<link href="/css/tushkan.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="/css/carousel/skin.css" />
		<style>body {padding-top: 25px; background: #ededed url('/img/body-bg.png') top left repeat-x;}</style>
		<!--[if lt IE 9]>
			<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<?php
//	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery-1.7.1.min.js');

	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/jquery-ui-1.7.3.custom/js/jquery-ui-1.7.3.custom.min.js");
	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.hotkeys.js');
	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.cookie.js');
	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.form.js');
    //Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.caret.js');

?>
		<script src="/js/bootstrap.min.js"></script>
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
                <ul><?php echo Yii::t('common', 'Administrator resources');?>
                    <li><a href="<?php echo $this->createUrl('users/admin'); ?>"><?php echo Yii::t('users', 'Users');?></a></li>
                    <li>Groups</li>
                    <li><a href="<?php echo $this->createUrl('paysystems/admin'); ?>"><?php echo Yii::t('pays', 'Paysystems');?></a></li>
                    <li><a href="<?php echo $this->createUrl('types/admin'); ?>"><?php echo Yii::t('types', 'Types of products');?></a></li>
                    <li><a href="<?php echo $this->createUrl('params/admin'); ?>"><?php echo Yii::t('params', 'Type params');?></a></li>
                    <li><a href="<?php echo $this->createUrl('products/admin'); ?>"><?php echo Yii::t('common', 'Products');?></a></li>
                    <li><a href="<?php echo $this->createUrl('pages/admin'); ?>"><?php echo Yii::t('common', 'Pages');?></a></li>
                    <li><a href="<?php echo $this->createUrl('personaldataparams/admin'); ?>"><?php echo Yii::t('params', 'Personal data params');?></a></li>
                    <li><a href="<?php echo $this->createUrl('partners/admin'); ?>"><?php echo Yii::t('admin', 'Partners');?></a></li>
                    <li><a href="<?php echo $this->createUrl('fileservers/admin'); ?>"><?php echo Yii::t('admin', 'FileServers');?></a></li>
                    <li><a href="<?php echo $this->createUrl('zones/admin'); ?>"><?php echo Yii::t('admin', 'Zones');?></a></li>
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