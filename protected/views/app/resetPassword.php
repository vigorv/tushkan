    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'reset-form',
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
        'htmlOptions' => array(
            'class' => 'form-horizontal',
        )
    ));
    ?>
    <ul class="form">
        <li class="controls">
            <?php echo $form->textField($model, 'email', array('class' => 'email','placeholder'=>'Email')); ?>
            <?php echo $form->error($model, 'email'); ?>
        </li>
    </ul>
    <p align="center">
        <a href="javascript:submitform();" class="button" style="background-image: url('/images/registerButton.png'); background-repeat: no-repeat; width:219px; height:57px; display:block;"><?=Yii::t('app','Reset password');?></a>
    </p>

    </ul>
    <?php $this->endWidget(); ?>
    <script language="javascript">
        function submitform()
        {
            document.forms['reset-form'].submit();
        }
    </script>