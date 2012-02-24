<div class="form">
<?php
	$form=$this->beginWidget('CActiveForm');
	$aLst = Utils::getActiveStates();
	$media = Utils::getMediaList();
	$mLst = Utils::arrayToKeyValues($media, 'id', 'title');
?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->label($model, 'title'); ?>
        <?php echo $form->textField($model, 'title', array('value' => $type['title'], 'class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'active', array('label' => Yii::t('common', 'Active'))); ?>
        <?php echo $form->dropdownlist($model, 'active', $aLst,
        	array(
       			'options' => array($type['active'] => array('selected' => 'selected')),
        		'class' => 'text ui-widget-content ui-corner-all',
        	));
        ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'media_id', array('label' => Yii::t('common', 'Media'))); ?>
        <?php echo $form->dropdownlist($model, 'media_id', $mLst,
        	array(
       			'options' => array($type['media_id'] => array('selected' => 'selected')),
        		'class' => 'text ui-widget-content ui-corner-all',
        	));
        ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'buy_limit', array('label' => Yii::t('types', 'Buy limit'))); ?>
        <?php echo $form->textField($model, 'buy_limit', array('value' => $type['buy_limit'], 'class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row stolb">
<?php
	if (!empty($params))
	{
    	echo CHtml::checkBoxList('chkParams', $chkParams, $params);
	}
?>
    </div>

    <div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('common', 'Save')); ?>
    </div>

<?php $this->endWidget(); ?>
</div>
