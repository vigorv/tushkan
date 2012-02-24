<div class="form">
<?php
	$form=$this->beginWidget('CActiveForm');
	$aLst = Utils::getActiveStates();
	$tpLst = Utils::getPersonaldataUItypes();
	$pidLst = Utils::getPersonaldataGroups();
?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->label($model, 'title', array('label' => Yii::t('common', 'Title'))); ?>
        <?php echo $form->textField($model, 'title', array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'srt', array('label' => Yii::t('common', 'Srt'))); ?>
        <?php echo $form->textField($model, 'srt', array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'active', array('label' => Yii::t('common', 'Active'))); ?>
        <?php echo $form->dropdownlist($model, 'active', $aLst, array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'parent_id', array('label' => Yii::t('users', 'group'))); ?>
        <?php echo $form->dropdownlist($model, 'parent_id', $pidLst, array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'tp', array('label' => Yii::t('common', 'Type UI'))); ?>
        <?php echo $form->dropdownlist($model, 'tp', $tpLst, array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

	<div class="row rememberMe">
		<?php echo $form->checkBox($model,'required'); ?>
		<?php echo $form->label($model,'required', array('label' => Yii::t('common', 'required field'))); ?>
		<?php echo $form->error($model,'required'); ?>
	</div>

	<div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('params', 'Add param')); ?>
    </div>

<?php $this->endWidget(); ?>
</div>
