<?php
$title = Yii::t('common', 'Feedback');
$this->pageTitle=Yii::app()->name . ' - ' . $title;
$this->breadcrumbs=array($title);
?>

<h2><?php echo $title; ?></h2>

<?php if(Yii::app()->user->hasFlash('contact')): ?>

<div class="flash-success">
	<?php echo Yii::app()->user->getFlash('contact'); ?>
</div>

<?php else: ?>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'contact-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<p class="note"><?php echo Yii::t('common', 'Fields with'); ?> <span class="required">*</span> <?php echo Yii::t('common', 'are required'); ?>.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'name', array('label' => Yii::t('users', 'name'))); ?>
		<?php echo $form->textField($model,'name'); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'email'); ?>
		<?php echo $form->textField($model,'email'); ?>
		<?php echo $form->error($model,'email'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'subject', array('label' => Yii::t('common', 'Subject'))); ?>
		<?php echo $form->textField($model,'subject',array('size'=>60,'maxlength'=>128)); ?>
		<?php echo $form->error($model,'subject'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'body', array('label' => Yii::t('common', 'Body'))); ?>
		<?php echo $form->textArea($model,'body',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'body'); ?>
	</div>

	<?php if(CCaptcha::checkRequirements()): ?>
	<div class="row">
		<?php echo $form->label($model,'verifyCode', array('required' => 1, 'label' => Yii::t('common', 'Verification Code'))); ?>
		<div>
		<?php echo $form->textField($model,'verifyCode'); ?>
		<?php $this->widget('CCaptcha', array(
				'clickableImage' => true,
				'imageOptions' => array('height' => 35),
				'buttonOptions' => array('disabled' => true,
				'showRefreshButton' => 'false',
				'buttonLabel' => Yii::t('users', 'new code'),
				)
			));
		?>
		</div>
		<div class="hint"><?php echo Yii::t('common', 'Letters of verify code are not case-sensitive.'); ?></div>
		<?php echo $form->error($model,'verifyCode'); ?>
	</div>
	<?php endif; ?>

	<div class="row buttons">
		<?php echo CHtml::submitButton(Yii::t('common', 'Submit')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->

<?php endif; ?>