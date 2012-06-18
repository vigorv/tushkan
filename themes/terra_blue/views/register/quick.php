<?php
$this->pageTitle=Yii::app()->name . ' - ' . Yii::t('common', 'Registration');
?>
	<div id="floater"></div>
	<div class="span6 offset3 index-bg">
		<div class="span3 index-offset form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'form-horizontal',
	'enableClientValidation'=>true,
	'htmlOptions'=>array(
		'class'=>'form-horizontal',
	),
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<?php echo $form->textField($model,'email', array('class' => 'span3', 'placeholder' => Yii::t('users', 'Email'))) ?>
	<?php echo $form->error($model,'email'); ?>
	<p></p>
	<center>
	<button type="submit" class="btn"><?php echo Yii::t('common', 'Registration'); ?></button>
	<p></p><p class="note">
		<a href="/register/login"><?php echo Yii::t('common', 'Login');?></a>
		|
		<a href="/register/forget"><?php echo Yii::t('users', 'Forget password?'); ?></a>
	</p>
	</center>
<?php $this->endWidget(); ?>
		</div>
	</div>
