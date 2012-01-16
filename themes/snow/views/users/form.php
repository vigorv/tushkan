<div class="form">
<?php $form=$this->beginWidget('CActiveForm'); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->label($model, 'name', array('label' => Yii::t('users', 'name'))); ?>
        <?php echo $form->textField($model, 'name', array('class' => 'text ui-widget-content ui-corner-all')) ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'email'); ?>
        <?php echo $form->textField($model, 'email', array('class' => 'text ui-widget-content ui-corner-all')) ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'password', array('label' => Yii::t('users', 'password'))); ?>
        <?php echo $form->passwordField($model, 'pwd', array('class' => 'text ui-widget-content ui-corner-all')) ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'group_id', array('label' => Yii::t('users', 'group'))); ?>
        <?php
        	echo $form->dropdownlist($model, 'group_id', $groups, array('class' => 'text ui-widget-content ui-corner-all'));
        ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'active'); ?>
        <?php echo $form->textField($model, 'active', array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('users', 'Add User')); ?>
    </div>

<?php $this->endWidget(); ?>
</div>
