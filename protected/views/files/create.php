<?php
$f_types = array(
    '1' => 'Directory',
    '0' => 'File'
);
?>
<div class="form">
    <?php $form = $this->beginWidget('CActiveForm'); ?>
    <?php echo $form->errorSummary($model); ?>
    <?php echo $form->hiddenField($model, 'pid', array('value' => $pid)); ?>
    <div class="row">
        <?php echo $form->label($model, 'title'); ?>
        <?php echo $form->textField($model, 'title'); ?>
    </div>
    <div class="row">
        <?php echo $form->label($model, 'is_dir'); ?>
        <?php echo $form->dropDownList($model, 'is_dir', $f_types) ?>
    </div>
    <div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('files', 'Create')); ?>
    </div>

    <?php $this->endWidget(); ?>
</div>
