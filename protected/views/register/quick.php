<?php
$this->pageTitle=Yii::app()->name . ' - ' . Yii::t('common', 'Registration');
?>
<center><h1><?php echo Yii::t('common', 'Registration'); ?></h1></center>
<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'quick-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'email'); ?>
		<?php echo $form->textField($model,'email', array('class' => 'text ui-widget-content ui-corner-all', 'style' => 'width: 350px')) ?>
		<?php echo $form->error($model,'email'); ?>
	</div>
	<br />
	<div class="row buttons"><center>
		<button type="submit" id="submitButton"><?php echo Yii::t('common', 'Register');?></button>
<script type="text/javascript">
	$( "#submitButton" )
				.button()
				.click(function() {
					$("#quick-form").submit();
	});
</script>
	<br /><br /><p class="note">
		<a href="/register/login"><?php echo Yii::t('common', 'Login'); ?></a>
		|
		<a href="/register/forget"><?php echo Yii::t('users', 'Forget password?'); ?></a>
	</p></center>
	</div>

<?php $this->endWidget(); ?>
</div>