<?php
$device_types = array(
    '1' => 'Desktop',
    '2' => 'Mobile',
        )
?>
<div class="form">
    <?php $form = $this->beginWidget('CActiveForm'); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->label($model, 'dname'); ?>
        <?php echo $form->textField($model,'dname'); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'dtype'); ?>

        <?php echo $form->dropDownList($model, 'dtype', $device_types) ?>
    </div>
    <div class="row">
        <?php echo $form->label($model, 'desc'); ?>
        <?php echo $form->textArea($model, 'desc') ?>
    </div>

    <div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('device', 'Add Device')); ?>
    </div>

    <?php $this->endWidget(); ?>
</div>
