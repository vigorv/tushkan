<?php
$title = Yii::t('common', 'Feedback');
//$this->pageTitle=Yii::app()->name . ' - ' . $title;
?>

<div class="span12 no-horizontal-margin inside-movie my-catalog">
<h1><?php echo $title; ?></h1>
	<div class="pad-content">

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
<?php
//<p></p>
//	echo $form->textField($model,'name', array('placeholder' => Yii::t('users', 'name')));
//	echo $form->error($model,'name');
//<p></p>
//	echo $form->textField($model,'email', array('class' => 'wideform', 'placeholder' => Yii::t('users', 'Email')));
//	echo $form->error($model,'email');
?>
<p></p>
	<?php echo $form->textField($model,'subject',array('class' => 'wideform', 'size'=>60,'maxlength'=>128, 'placeholder' => Yii::t('users', 'Subject'))); ?>
	<?php echo $form->error($model,'subject'); ?>
<p></p>
	<?php echo $form->textArea($model,'body',array('class' => 'wideform', 'rows'=>6, 'cols'=>50, 'placeholder' => Yii::t('users', 'Body'))); ?> <?php echo _REQUIRED_; ?>
	<?php echo $form->error($model,'body'); ?>
<p></p>
<?php if(CCaptcha::checkRequirements()): ?>
	<?php echo $form->label($model,'verifyCode', array('class' => 'wideform', 'required' => 1, 'label' => Yii::t('common', 'Verification Code'))); ?>
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
<?php endif; ?>

	<?php echo CHtml::submitButton(Yii::t('common', 'Submit'), array('class' => 'btn')); ?>

<?php $this->endWidget(); ?>

</div>

<?php endif; ?>
	</div>
</div>
<script type="text/javascript">
<!--
//	$("#FeedbackForm_body").bind('click', function () {
//		$("#FeedbackForm_body").caretTo(1000);
//	});
	$("#FeedbackForm_body").focus();
-->
</script>