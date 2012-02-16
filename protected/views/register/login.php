<?php
$this->pageTitle=Yii::app()->name . ' - ' . Yii::t('common', 'Login');
?>

<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'email'); ?>
		<?php echo $form->textField($model,'email', array('class' => 'text ui-widget-content ui-corner-all', 'style' => 'width: 300px')) ?>
		<?php echo $form->error($model,'email'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'password', array('label' => Yii::t('users', 'password'))); ?>
		<?php echo $form->passwordField($model,'password', array('class' => 'text ui-widget-content ui-corner-all', 'style' => 'width: 300px')) ?>
		<?php echo $form->error($model,'password'); ?>
	</div>

	<p class="note"><span class="required">*</span> <?php echo Yii::t('common', 'are required'); ?>.</p>

	<div class="row rememberMe">
		<?php echo $form->checkBox($model,'rememberMe'); ?>
		<?php echo $form->label($model,'rememberMe', array('label' => Yii::t('users', 'remember me'))); ?>
		<?php echo $form->error($model,'rememberMe'); ?>
	</div>

	<div class="row buttons"><center>
		<button type="submit" id="submitButton"><?php echo Yii::t('common', 'Login');?></button>
<script type="text/javascript">
	$( "#submitButton" )
				.button()
				.click(function() {
					$("#login-form").submit();
	});
</script>
	<br /><br /><p class="note">
		<a href="/register/quick"><?php echo Yii::t('common', 'Registration'); ?></a>
		|
		<a href="/register/forget"><?php echo Yii::t('users', 'Forget password?'); ?></a>
	</p></center>
	</div>
<?php $this->endWidget(); ?>
</div>