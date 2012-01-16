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

    <div class="row">
        <?php echo $form->label($model, 'name', array('label' => Yii::t('users', 'name'))); ?>
        <?php echo $form->textField($model, 'name', array('class' => 'text ui-widget-content ui-corner-all')) ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'email'); ?>
        <?php echo $form->textField($model, 'email', array('class' => 'text ui-widget-content ui-corner-all')) ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'pwd', array('label' => Yii::t('users', 'password'))); ?>
        <?php echo $form->passwordField($model, 'pwd', array('class' => 'text ui-widget-content ui-corner-all')) ?>
    </div>

	<?php
		if(CCaptcha::checkRequirements()): ?>
	<div class="row">
		<?php echo $form->labelEx($model,'verifyCode'); ?>
		<table callspacing="0" cellpadding="0" border="0">
			<tr valign="middle"><td width="20">
		<?php
			echo $form->textField($model,'verifyCode');
		echo'</td><td align="center">';
			$this->widget('CCaptcha', array(
				'clickableImage' => true,
				'imageOptions' => array('height' => 35),
				'buttonOptions' => array('disabled' => true,
				'href' => Yii::app()->createUrl('/register/captcha')),
				'showRefreshButton' => 'false',
				'buttonLabel' => Yii::t('users', 'new code'),
				'captchaAction' => Yii::app()->createUrl('/register/captcha'))
			);
		?>
			</td></tr>
		</table>
		<?php //echo $form->error($model,'verifyCode'); ?>
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