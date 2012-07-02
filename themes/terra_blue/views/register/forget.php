<?php
switch($subAction)
{
	case "askpassword":
?>
	<div id="floater"></div>
	<div class="span6 offset3 index-bg">
		<div class="span3 index-offset form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'form-horizontal',
	'htmlOptions'=>array(
		'class'=>'form-horizontal',
	),
)); ?>
	<p class="note">
		Введите (<u>или сгенерируйте</u>) новый пароль.
	</p>

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

	echo $form->passwordField($model, 'pwd', array('id' => 'passwordId', 'value' => $pwd, 'class' => 'span3', 'placeholder' => Yii::t('users', 'New password')));
	echo $form->error($model,'pwd');
?>
	<p></p>
	<label class="checkbox"><?php echo $form->checkBox($model,'rememberMe') . Yii::t('users', 'remember me'); ?></label>
	<?php echo $form->error($model,'rememberMe'); ?>

	<center>
		<button class="btn" type="submit" id="submitButton"><?php echo Yii::t('common', 'Login');?></button>
		<button class="btn" type="button" id="generateButton"><?php echo Yii::t('common', 'Generate');?></button>
<script type="text/javascript">
<?php
	echo $clearScript;
?>
	var generated = 0;
	$( "#passwordId" ).focus();
	$( "#submitButton" ).bind("click", function() {
		$("#form-horizontal").submit();
	});

	$( "#generateButton" ).bind("click", function() {
		generated++;
		$("#passwordId").val("");
		$(this).attr("disabled", true );
		hash = new String();
		for (i = 0; i < 15; i++)
		{
			code = Math.round(Math.random() * 100);
			if (code < 50) code = code + 50;
			hash += String.fromCharCode(code);
		}
		window.setTimeout('$( "#rememberId" ).attr("checked", true);$("#passwordId").val("' + hash + '");$( "#generateButton" ).attr("disabled", false );', 1000);

		return false;
	});
</script>
		</center>
<?php $this->endWidget(); ?>
		</div>
	</div>
<?php
	break;

default:
?>
	<div id="floater"></div>
	<div class="span6 offset3 index-bg">
		<div class="span3 index-offset form" style="width:320px;margin-top:-15px; margin-left:-20px;">
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
	'id'=>'form-horizontal',
	'enableClientValidation'=>true,
	'htmlOptions'=>array(
		'class'=>'form-horizontal',
	),
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<?php echo $form->textField($model,'email', array('class' => 'span3', 'style' => 'width:300px;', 'placeholder' => Yii::t('users', 'Email'))) ?>
	<?php echo $form->error($model,'email'); ?>
	<center>
	<p></p><button type="submit" class="btn"><?php echo Yii::t('users', 'Forget password?');?></button>
	<p></p><p class="note">
		<a href="/register/login"><?php echo Yii::t('common', 'Login');?></a>
	</p>
	</center>
<?php $this->endWidget(); ?>
		</div>
	</div>
<?php
}