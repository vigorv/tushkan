<div class="form">
<?php $form=$this->beginWidget('CActiveForm'); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->label($model, 'title', array('label' => Yii::t('common', 'Title'))); ?>
        <?php echo $form->textField($model, 'title', array('class' => 'text ui-widget-content ui-corner-all')) ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'class', array('label' => Yii::t('common', 'Class'))); ?>
        <?php echo $form->textField($model, 'class', array('class' => 'text ui-widget-content ui-corner-all')) ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'active', array('label' => Yii::t('common', 'Active'))); ?>
        <?php echo $form->textField($model, 'active', array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'srt', array('label' => Yii::t('common', 'Srt'))); ?>
        <?php echo $form->textField($model, 'srt', array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('pays', 'Add Paysystem')); ?>
    </div>

<?php $this->endWidget(); ?>
</div>
