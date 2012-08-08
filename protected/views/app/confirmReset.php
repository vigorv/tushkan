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
        <?php if (CCaptcha::checkRequirements()): ?>
        <div class="control-group">
            <?php echo $form->labelEx($model, 'verifyCode', array('class' => 'control-label')); ?>
            <div class="controls">
                <?php $this->widget('CCaptcha'); ?>
                <?php echo $form->textField($model, 'verifyCode',array('class' => 'text')); ?>
            </div>
            <?php echo $form->error($model, 'verifyCode'); ?>
        </div>
        <?php endif; ?>
    </fieldset>
    <div class="form-actions">
        <?php echo CHtml::submitButton(Yii::t('user','Reset Password'),array('class'=>'btn btn-primary')); ?>
    </div>

    <?php $this->endWidget(); ?>
</div><!-- form -->
