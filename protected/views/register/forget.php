<h2>Восстановление пароля</h2>
<?php
switch($subAction)
{
	case "post":
		if (empty($info['error']))
			echo '<h3>На ваш электронный адрес отправлена ссылка для восстановления пароля.</h3>';
	break;

	case "newpassword":
		if (!empty($info['error']))
			echo '<h3>Ключ для восстановления пароля устарел. Начните процедуру восстановления пароля <a href="/register/forget">заново</a>.</h3>';
		else
			echo '<h3>На ваш электронный адрес отправлен новый пароль.</h3>';
	break;

default:
?>
<p class="note"><?php echo Yii::t('common', 'Fields with'); ?> <span class="required">*</span> <?php echo Yii::t('common', 'are required'); ?>.</p>
<div class="form">
<?php $form=$this->beginWidget('CActiveForm');?>
<?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->label($model, 'email', array('required' => 1)); ?>
        <?php echo $form->textField($model, 'email', array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('users', 'Forget password?')); ?>
    </div>

<?php $this->endWidget(); ?>
</div>
<?php
}
print_r($info);