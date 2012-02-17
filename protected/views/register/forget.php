<h2><?php echo Yii::t('users', 'Restoring password');?></h2>
<?php
switch($subAction)
{
	case "askpassword":
?>
<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'forget-form',
/*
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
*/
)); ?>
	<p class="note">
		Введите (<u>или сгенерируйте</u>) новый пароль.
	</p>

    <div class="row">
    <?php
		$clearScript = '
	$( "#passwordId" )
		.focus(function(obj) {
			if (generated)
			{
				$( "#passwordId" ).val("");
				generated = 0;
			}
	});
		';
		$checked = false;
    	//$pwd = $info['newpassword'];
    	$pwd = '';
    	if (!empty($model->pwd))
    	{
    		$pwd = $model->pwd;
    		//$clearScript = '';//ЧИСТКА ПАРОЛЯ БОЛЬШЕ НЕ НУЖНА
	    	$checked = $model->rememberMe;
    	}

    ?>
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
		<button type="button" id="generateButton"><?php echo Yii::t('common', 'Generate');?></button>
<script type="text/javascript">
<?php
	echo $clearScript;
?>
	var generated = 0;
	$( "#passwordId" ).focus();
	$( "#submitButton" )
				.button()
				.click(function() {
					$("#forget-form").submit();
	});

	$( "#generateButton" )
				.button()
				.click(function() {
					generated++;
					$("#passwordId").val("");
					$(this).button( "option", "disabled", true );
					hash = new String();
					for (i = 0; i < 15; i++)
					{
						code = Math.round(Math.random() * 100);
						if (code < 50) code = code + 50;
						hash += String.fromCharCode(code);
					}
					window.setTimeout('$( "#rememberId" ).attr("checked", true);$("#passwordId").val("' + hash + '");$("#generateButton").button( "option", "disabled", false );', 1000);

				return false;
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
	if (!empty($info['error']))
	{
		if (empty($subAction))
		{
			echo '<p class="note"><span class="required">Срок действия ссылки истек, сделайте запрос на получение новой ссылки</span></p>';
		}
		else
			echo '<h3>' . $form->error($model,'email') . '</h3>';
	}
	else
	{
		if (!empty($subAction))
		{
			echo '<h4>На ваш адрес Email отправлено письмо со ссылкой смены пароля.</h4>';
			echo '<p class="note">
			Если вы не получили письмо со ссылкой смены пароля, сделайте запрос.
			Введите адрес Email, указанный вами при регистрации.
			</p>';
		}
		else
			echo '<p class="note">Введите адрес Email, указанный вами при регистрации.</p>';
	}

$form=$this->beginWidget('CActiveForm', array(
	'id'=>'forget-form',
	'action' => '/register/forget',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

    <div class="row">
        <?php echo $form->labelEx($model, 'email'); ?>
        <?php echo $form->textField($model, 'email', array('class' => 'text ui-widget-content ui-corner-all', 'style' => 'width: 350px;')); ?>
		<?php echo $form->error($model,'email'); ?>
    </div>

	<div class="row buttons"><center>
		<button type="submit" id="submitButton"><?php echo Yii::t('users', 'Forget password?');?></button>
<script type="text/javascript">
	$( "input:text" ).focus();
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