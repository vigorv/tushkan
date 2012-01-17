<div class="form">
<?php $form=$this->beginWidget('CActiveForm'); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->label($model, 'title'); ?>
        <?php echo $form->textField($model, 'title') ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'description'); ?>
        <?php echo $form->textArea($model, 'description') ?>
    </div>
  
    <div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('device', 'Add Device')); ?>
    </div>

<?php $this->endWidget(); ?>
</div>
