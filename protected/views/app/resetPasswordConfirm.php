<div class="form">
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
    ?>
    <fieldset>
        <div class="control-group">
            <?php echo $form->labelEx($model, 'password', array('class' => 'control-label')); ?>
            <div class="controls">
                <?php echo $form->passwordField($model, 'password',array('class' => 'password')); ?>
                <?php echo $form->error($model, 'password'); ?>
            </div>
        </div>
    </fieldset>
    <p align="center">
        <a href="javascript:submitform();" class="button" style="background-image: url('/images/registerButton.png'); background-repeat: no-repeat; width:219px; height:57px; display:block;"><?=Yii::t('app','Set password');?></a>
    </p>


    <?php $this->endWidget(); ?>
</div><!-- form -->
