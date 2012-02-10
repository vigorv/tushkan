<div class="form">
<?php
	$form=$this->beginWidget('CActiveForm');
	$aLst = Utils::getActiveStates();
?>

    <?php echo $form->errorSummary($model); ?>
    <?php echo $form->hiddenField($model, 'id', array('value' => $info['id'])) ?>

    <div class="row">
        <?php echo $form->label($model, 'title', array('label' => Yii::t('common', 'Title'))); ?>
        <?php echo $form->textField($model, 'title', array('value' => $info['title'], 'class' => 'text ui-widget-content ui-corner-all')) ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'class', array('label' => Yii::t('common', 'Class'))); ?>
        <?php echo $form->textField($model, 'class', array('value' => $info['class'], 'class' => 'text ui-widget-content ui-corner-all')) ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'active', array('label' => Yii::t('common', 'Active'))); ?>
        <?php echo $form->dropdownlist($model, 'active', $aLst,
        	array(
       			'options' => array($info['active'] => array('selected' => 'selected')),
        		'class' => 'text ui-widget-content ui-corner-all',
        	));
        ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'srt', array('label' => Yii::t('common', 'Srt'))); ?>
        <?php echo $form->textField($model, 'srt', array('value' => $info['srt'], 'class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('common', 'Save')); ?>
    </div>

<?php $this->endWidget(); ?>
</div>
