<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />        
        <title><?php echo CHtml::encode($this->pageTitle); ?></title>
        <?php
        Yii::app()->getClientScript()->registerCoreScript('jquery');
        Yii::app()->getClientScript()->registerCssFile(Yii::app()->theme->baseUrl . '/css/main.css');
        ?>
    </head>
    <body>
        <div  id="header" class="Panel_H" > 
            <b>Menu</b>
        </div>
        <div id="container" style="background-color:#FFF6BF">




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
        <div id="footer" class="Panel_H">
            Copyright &copy; <?php echo date('Y'); ?> <?php echo CHtml::encode(Yii::app()->name); ?><br/>
            All Rights Reserved.<br/>
            <?php //echo Yii::powered();    ?>
        </div><!-- footer -->
    </body>
</html>