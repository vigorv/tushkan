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

		<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/tushkan.css" />

		<link rel="stylesheet/less" type="text/css" href="/less/mycloud.less"/>
        <title><?php echo CHtml::encode($this->pageTitle); ?></title>
		<?php
		Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery-1.7.1.min.js');
		Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.hotkeys.js');
		Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.cookie.js');
		Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.form.js');
		Yii::app()->getClientScript()->registerCssFile(Yii::app()->request->baseUrl . "/js/jquery-ui/css/pepper-grinder/jquery-ui-1.8.16.custom.css");
		Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/jquery-ui/js/jquery-ui-1.8.16.custom.min.js");
		Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.address-1.4.min.js');
		?>
		<script src="/js/bootstrap.min.js"></script>
		<script src="/js/less-1.2.2.min.js"></script>

        <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    </head>
    <body>
        <div class="container" id="page">

			<div id="m_panel">

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

			<div id="m_uploads" class="clearblockfix">
				<? $this->renderPartial('/universe/uploads'); ?>
			</div>
			<div id="m_goods" class="clearblockfix">

			</div>

			<div id="content">
				<?php echo $content; ?>
			</div>

			<div id="m_devices" class="well">

			</div>


			<script langauge="javascript">
				$.address.change(function(event) {  
					$('#content').load(event.value, function(){
						$('#content a').click(function(){
							$.address.value($(this).attr('href'));
							return false;
						});
					});  
					return false;
				});  
					
				$('#m_panel').load('/universe/panel',function(){
					$('#m_panel a').click(function() {  
						lnk= $(this).attr('href');
						if (lnk=="#") return false;
						$.address.value(lnk);  
						return false;
					});
				});
				
				$('#m_uploads a').click(function() {  
					lnk= $(this).attr('href');
					if (lnk=="#") return false;
					$.address.value(lnk);  
					return false;
				});
				
				
				
				$('#m_goods').load('/universe/goodsTop',function(){
					$('#m_goods  a').click(function() {
						$.address.value($(this).attr('href'));
						return false;
					});
				});
				$('#m_devices').load('/universe/devices');
			</script>



			<div id="footer">
				Copyright &copy; <?php echo date('Y'); ?> <?php echo CHtml::encode(Yii::app()->name); ?><br/>
				All Rights Reserved.<br/>
				<?php //echo Yii::powered();     ?>
			</div><!-- footer -->

		</div><!-- page -->


    </body>
</html>