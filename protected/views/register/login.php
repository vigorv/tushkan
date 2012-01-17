<?php
$this->pageTitle=Yii::app()->name . ' - ' . Yii::t('common', 'Login');
?>

<h1><?php echo Yii::t('common', 'Login'); ?></h1>

<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<p class="note"><?php echo Yii::t('common', 'Fields with'); ?> <span class="required">*</span> <?php echo Yii::t('common', 'are required'); ?>.</p>

	<div class="row">
		<?php echo $form->labelEx($model,'username'); ?>
		<?php echo $form->textField($model,'username', array('class' => 'text ui-widget-content ui-corner-all')) ?>
		<?php echo $form->error($model,'username'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'password'); ?>
		<?php echo $form->passwordField($model,'password', array('class' => 'text ui-widget-content ui-corner-all')) ?>
		<?php echo $form->error($model,'password'); ?>
	</div>

	<div class="row rememberMe">
		<?php echo $form->checkBox($model,'rememberMe'); ?>
		<?php echo $form->label($model,'rememberMe', array('label' => Yii::t('users', 'remember me'))); ?>
		<?php echo $form->error($model,'rememberMe'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton(Yii::t('common', 'Login')); ?>
	</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
	<p class="note"><a href="/register/forget"><?php echo Yii::t('users', 'Forget password?'); ?></a>.</p>
