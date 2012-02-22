<div class="form">
<?php
	$form=$this->beginWidget('CActiveForm');
	$aLst = Utils::getActiveStates();
	$media = Utils::getMediaList();
	$mLst = Utils::arrayToKeyValues($media, 'id', 'title');
?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->label($model, 'title', array('label' => Yii::t('common', 'Title'))); ?>
        <?php echo $form->textField($model, 'title', array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'active', array('label' => Yii::t('common', 'Active'))); ?>
        <?php echo $form->dropdownlist($model, 'active', $aLst, array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'media_id', array('label' => Yii::t('common', 'Media'))); ?>
        <?php echo $form->dropdownlist($model, 'media_id', $mLst, array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

	<div class="row">
        <?php echo $form->label($model, 'buy_limit', array('label' => Yii::t('types', 'Buy limit'))); ?>
        <?php echo $form->textField($model, 'buy_limit', array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('types', 'Add type')); ?>
    </div>

<?php $this->endWidget(); ?>
</div>
