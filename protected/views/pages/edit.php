<div class="form">
<?php $form=$this->beginWidget('CActiveForm'); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->label($model, 'title', array('label' => Yii::t('common', 'Title'))); ?>
        <?php echo $form->textField($model, 'title', array('value' => $pageInfo['title'], 'class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'alias', array('label' => 'URL alias')); ?>
        <?php echo $form->textField($model, 'alias', array('value' => $pageInfo['alias'], 'class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'meta_title'); ?>
        <?php echo $form->textField($model, 'meta_title', array('value' => $pageInfo['meta_title'], 'class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'meta_description'); ?>
        <?php
        	$model->meta_description = $pageInfo['meta_description'];
        	echo $form->textArea($model, 'meta_description', array('class' => 'text ui-widget-content ui-corner-all'));
        ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'meta_keywords'); ?>
        <?php
        	$model->meta_keywords = $pageInfo['meta_keywords'];
        	echo $form->textArea($model, 'meta_keywords', array('class' => 'text ui-widget-content ui-corner-all'));
        ?>
    </div>


    <div class="row">
        <?php echo $form->label($model, 'txt', array('label' => Yii::t('common', 'Текст'))); ?>

<?php
	$this->widget('application.extensions.tinymce.ETinyMce',
		array('name'=>'PageForm[txt]', 'editorTemplate' => 'full', 'value' => $pageInfo['txt'], ));
?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'active'); ?>
        <?php echo $form->textField($model, 'active', array('value' => $pageInfo['active'], )) ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'parent_id'); ?>
        <?php echo $form->textField($model, 'parent_id', array('value' => $pageInfo['parent_id'], )) ?>
    </div>

   <div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('common', 'Save')); ?>
    </div>

<?php $this->endWidget(); ?>
</div>
