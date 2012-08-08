
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'register-form',
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
        'htmlOptions' => array(
            'class' => 'form-horizontal',
        )
    ));
    /**
     *@var CActiveForm $form
     * */
    ?>
    <h1>SafeLib</h1>
    <ul class="form">
        <li class="controls">
            <?php echo $form->textField($model, 'email', array('class' => 'email','placeholder'=>'Email')); ?>
            <?php echo $form->error($model, 'email'); ?>
        </li>
        <li class="controls">
            <?php echo $form->passwordField($model, 'password', array('class' => 'password','placeholder'=>Yii::t('app','Password'))); ?>
            <?php echo $form->error($model, 'password'); ?>
        </li>


        <?php if (CCaptcha::checkRequirements()): ?>
        <li class="controls">
            <?php $this->widget('CCaptcha'); ?>
            <?php echo $form->textField($model, 'verifyCode', array('class' => 'text')); ?>
        </li>
        <?php echo $form->error($model, 'verifyCode'); ?>
        <?php endif; ?>
    </ul>
    <p>
       <a href="javascript:submitform();" class="button white"><?=Yii::t('app','Sign up');?></a>
    </p>

    </ul>
    <?php $this->endWidget(); ?>
    <script language="javascript">
        function submitform()
        {
            document.forms['register-form'].submit();
        }
    </script>

