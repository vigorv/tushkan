
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

    <ul class="form">
        <li class="controls">
            <?php echo $form->textField($model, 'email', array('class' => 'email','placeholder'=>'Email')); ?>
            <?php echo $form->error($model, 'email'); ?>
        </li>
        <li class="controls">
            <?php echo $form->passwordField($model, 'password', array('class' => 'password','placeholder'=>Yii::t('app','Password'))); ?>
            <?php echo $form->error($model, 'password'); ?>
        </li>

    </ul>
    <p>
       <a href="javascript:submitform();" class="button" style="background-image: url('/images/registerButton.png'); background-repeat: no-repeat; width:219px; height:57px; display:block;"><?=Yii::t('app','Sign up');?></a>
    </p>

    </ul>
    <?php $this->endWidget(); ?>
    <script language="javascript">
        function submitform()
        {
            document.forms['register-form'].submit();
        }
    </script>

