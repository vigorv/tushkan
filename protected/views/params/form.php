<div class="form">
<?php $form=$this->beginWidget('CActiveForm'); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->label($model, 'title', array('label' => Yii::t('common', 'Title'))); ?>
        <?php echo $form->textField($model, 'title', array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'description', array('label' => Yii::t('common', 'Description'))); ?>
        <?php echo $form->textArea($model, 'description', array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'srt', array('label' => Yii::t('common', 'Srt'))); ?>
        <?php echo $form->textField($model, 'srt', array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

   <div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('params', 'Add param')); ?>
    </div>

<?php $this->endWidget(); ?>
</div>
