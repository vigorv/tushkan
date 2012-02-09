<div class="form">
<?php
	$form=$this->beginWidget('CActiveForm');
	$aLst = Utils::getActiveStates();
?>

    <?php echo $form->errorSummary($model); ?>
    <?php echo $form->hiddenField($model, 'id', array('value' => $info['id'])) ?>

    <div class="row">
        <?php echo $form->label($model, 'name', array('label' => Yii::t('users', 'name'))); ?>
        <?php echo $form->textField($model, 'name', array('value' => $info['name'], 'class' => 'text ui-widget-content ui-corner-all')) ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'email'); ?>
        <?php echo $form->textField($model, 'email', array('value' => $info['email'], 'class' => 'text ui-widget-content ui-corner-all')) ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'password', array('label' => Yii::t('users', 'password'))); ?>
        <?php echo $form->passwordField($model, 'pwd', array('value' => $info['pwd'], 'class' => 'text ui-widget-content ui-corner-all')) ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'group_id', array('label' => Yii::t('users', 'group'))); ?>
        <?php
        	echo $form->dropdownlist($model, 'group_id', $groups,
        		array(
        			'options' => array($info['group_id'] => array('selected' => 'selected')),
        			'class' => 'text ui-widget-content ui-corner-all'
        	));
        ?>
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

    <div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('common', 'Save')); ?>
    </div>

<?php $this->endWidget(); ?>
</div>
