<div class="P_section_p50 Panel_V" >
    HELLO
</div>
<div class="P_section_p50 Panel_V">
<div class="form_t1">
<h3>TAKE ME IN</h3>
<h4>Enter your email Address and Password.<br/>
    Have fun!
</h4>
<?php
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'login-form',
    'enableClientValidation' => true,
    'clientOptions' => array(
        'validateOnSubmit' => true,
    ),
        ));
?>

<div class="row">
    <?php echo $form->labelEx($model, 'username'); ?>:<br/>
    <?php echo $form->textField($model, 'username'); ?>
    <?php echo $form->error($model, 'username'); ?>
</div>

<div class="row">
    <?php echo $form->labelEx($model, 'password'); ?>:<br/>
    <?php echo $form->passwordField($model, 'password'); ?>
    <?php echo $form->error($model, 'password'); ?>
</div>

<div class="row rememberMe">
    <?php echo $form->checkBox($model, 'rememberMe'); ?>
    <?php echo $form->label($model, 'rememberMe', array('label' => Yii::t('users', 'remember me'))); ?>
    <?php echo $form->error($model, 'rememberMe'); ?>
</div>

<div class="row buttons">
    <?php echo CHtml::submitButton(Yii::t('common', 'Login')); ?>
</div>

<?php $this->endWidget(); ?>
<p>Don't have an account? 
    <a href="/register/">Create now!</a> | 
    <a href="/register/restore" >Forgot password?</a></p>

</div>
    </div>