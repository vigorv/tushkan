<?php
$this->pageTitle=Yii::app()->name . ' - ' . Yii::t('common', 'Login');
?>
	<div id="floater"></div>
	<div class="span6 offset3 index-bg">
		<div class="span3 index-offset form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'form-horizontal',
	'enableClientValidation'=>true,
	'htmlOptions'=>array(
		'class'=>'form-horizontal',
		'style'=>'margin-top:-20px;',
	),
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<?php echo $form->textField($model,'email', array('class' => 'span3', 'placeholder' => Yii::t('users', 'Email'))) ?>
	<?php echo $form->error($model,'email'); ?>
	<label></label>
	<?php echo $form->passwordField($model,'password', array('class' => 'span3', 'placeholder' => Yii::t('users', 'password'))) ?>
	<?php echo $form->error($model,'password'); ?>
	<p></p>
	<label class="checkbox"><?php echo $form->checkBox($model,'rememberMe') . Yii::t('users', 'remember me'); ?></label>
	<?php echo $form->error($model,'rememberMe'); ?>

	<center>
	<button type="submit" class="btn"><?php echo Yii::t('common', 'Login');?></button>
	<p></p><p class="note">
		<a href="/register/quick"><?php echo Yii::t('common', 'Registration'); ?></a>
		|
		<a href="/register/forget"><?php echo Yii::t('users', 'Forget password?'); ?></a>
	</p>
	</center>
<?php $this->endWidget(); ?>
		</div>
	</div>
