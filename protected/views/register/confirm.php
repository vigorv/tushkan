<?php
switch($subAction)
{
	case "askpassword":
		echo '<h2>' . Yii::t('users', 'Registration confirmed') . '</h2>';
?>
<div class="form">
<?php
		$clearScript = '
	$( "#passwordId" )
		.click(function(obj) {
			$( "#passwordId" ).val("");
			$( "#rememberId" ).attr("checked", false);
	});
		';
		$checked = true;
    	$pwd = $info['newpassword'];
    	if (!empty($model->pwd))
    	{
    		$pwd = $model->pwd;
    		$clearScript = '';//ЧИСТКА ПАРОЛЯ БОЛЬШЕ НЕ НУЖНА
	    	$checked = $model->rememberMe;
    	}

$form=$this->beginWidget('CActiveForm', array(
	'id'=>'forget-form',
/*
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
*/
)); ?>
	<p class="note">
		Введите новый пароль <u>или воспользуйтесь паролем сгенерированным специально для вас</u>.
	</p>

    <div class="row">
        <?php echo $form->labelEx($model, 'pwd', array('label' => Yii::t('users', 'New password'))); ?>
        <?php echo $form->passwordField($model, 'pwd', array('id' => 'passwordId', 'value' => $pwd, 'class' => 'text ui-widget-content ui-corner-all', 'style' => 'width: 350px;')); ?>
		<?php echo $form->error($model,'pwd'); ?>
    </div>
	<div class="row rememberMe">
		<?php echo $form->checkBox($model,'rememberMe', array('checked' => $checked, 'id' => 'rememberId')); ?>
		<?php echo $form->label($model,'rememberMe', array('label' => Yii::t('users', 'remember me'))); ?>
		<?php echo $form->error($model,'rememberMe'); ?>
	</div>

	<div class="row buttons"><center>
		<button type="submit" id="submitButton"><?php echo Yii::t('common', 'Login');?></button>
<script type="text/javascript">
<?php
	echo $clearScript;
?>
	$( "#submitButton" )
				.button()
				.click(function() {
					$("#forget-form").submit();
	});
</script>
		</center>
    </div>

<?php $this->endWidget(); ?>
</div>
<?php
	break;

default:
?>
<div class="form">
<?php
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'forget-form',
	'action' => '/register/confirm',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
));
	if (!empty($info['error']))
	{
		if (empty($subAction))
		{
			echo '<h2>' . Yii::t('users', 'Confirm registration') . '</h2>';
			echo '<p class="note"><span class="required">Срок действия ссылки истек, сделайте запрос на получение новой ссылки</span></p>';
		}
		else
			echo '<h3>' . $form->error($model,'email') . '</h3>';
	}
	else
	{
		echo '<h1>Вы зарегистрированы</h1>';
		echo '<p class="note"><span class="required">Регистрация не подтверждена.</span></p>';
		echo '<h4>Для подтверждения регистрации на ваш адрес Email отправлено письмо со ссылкой подтверждения.</h4>';
	}
	echo '<p class="note">
	Если вы не получили письмо со ссылкой подтверждения, сделайте запрос.
	Введите адрес Email, указанный вами при регистрации.
	</p>';

?>

    <div class="row">
        <?php echo $form->labelEx($model, 'email'); ?>
        <?php echo $form->textField($model, 'email', array('class' => 'text ui-widget-content ui-corner-all', 'style' => 'width: 350px;')); ?>
		<?php echo $form->error($model,'email'); ?>
    </div>

	<div class="row buttons"><center>
		<button type="submit" id="submitButton"><?php echo Yii::t('common', 'Submit');?></button>
<script type="text/javascript">
	$( "#submitButton" )
				.button()
				.click(function() {
					$("#forget-form").submit();
	});
</script>
	<p class="note">
		<a href="/register/login"><?php echo Yii::t('common', 'Login'); ?></a>
	</p></center>
    </div>

<?php $this->endWidget(); ?>
</div>
<?php
}
