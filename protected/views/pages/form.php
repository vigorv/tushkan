<div class="form">
<?php $form=$this->beginWidget('CActiveForm'); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->label($model, 'title', array('label' => Yii::t('common', 'Title'))); ?>
        <?php echo $form->textField($model, 'title', array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'alias', array('label' => 'URL alias')); ?>
        <?php echo $form->textField($model, 'alias', array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'meta_title'); ?>
        <?php echo $form->textField($model, 'meta_title', array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'meta_description'); ?>
        <?php echo $form->textArea($model, 'meta_description', array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'meta_keywords'); ?>
        <?php echo $form->textArea($model, 'meta_keywords', array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>


    <div class="row">
        <?php echo $form->label($model, 'txt', array('label' => Yii::t('common', 'Текст'))); ?>

<?php
	$this->widget('application.extensions.tinymce.ETinyMce',
		array('name'=>'PageForm[txt]', 'editorTemplate' => 'full'));
?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'active'); ?>
        <?php echo $form->textField($model, 'active', array('value' => 0)) ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'parent_id'); ?>
        <?php echo $form->textField($model, 'parent_id', array('value' => 0)) ?>
    </div>

   <div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('pages', 'Add page')); ?>
    </div>

<?php $this->endWidget(); ?>
</div>
