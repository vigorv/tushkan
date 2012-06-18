<?php
if ($registered)
{
	echo '<h3>Поздравляем! Теперь вы можете <a href="' . Yii::app()->createUrl('/register/login') . '">войти</a>.</h3>';
}
else
{
?>
<div class="form">
<?php $form=$this->beginWidget('CActiveForm'); ?>

    <?php echo $form->errorSummary($model); ?>

	<p class="note"><?php echo Yii::t('common', 'Fields with'); ?> <span class="required">*</span> <?php echo Yii::t('common', 'are required'); ?>.</p>

	<div class="row">
        <?php echo $form->label($model, 'name', array('label' => Yii::t('users', 'name'))); ?>
        <?php echo $form->textField($model, 'name', array('class' => 'text ui-widget-content ui-corner-all')) ?>
    </div>

    <div class="row">
		<?php echo $form->labelEx($model,'email'); ?>
        <?php echo $form->textField($model, 'email', array('class' => 'text ui-widget-content ui-corner-all')) ?>
		<?php echo $form->error($model,'email'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model, 'pwd', array('label' => Yii::t('users', 'password'))); ?>
        <?php echo $form->passwordField($model, 'pwd', array('class' => 'text ui-widget-content ui-corner-all')) ?>
		<?php echo $form->error($model,'pwd'); ?>
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

    <div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('common', 'Register')); ?>
    </div>

<?php $this->endWidget(); ?>
</div>
<?php
}
?>