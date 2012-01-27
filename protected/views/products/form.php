<div class="form">
<?php $form=$this->beginWidget('CActiveForm'); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->label($model, 'title'); ?>
        <?php echo $form->textField($model, 'title') ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'y'); ?>
        <?php
        	$yLst = range(date('Y'), 1900, -1);
        	$yLst = array_combine($yLst, $yLst);
        	echo $form->dropdownlist($model, 'y', $yLst);
        ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'description'); ?>
        <?php echo $form->textArea($model, 'description') ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'active'); ?>
        <?php echo $form->textField($model, 'active') ?>
    </div>

    <?php echo $form->label($model, 'countries'); ?>
    <div class="row stolb">
    <?php
    	echo CHtml::checkBoxList('ProductForm[countries]', $chkCountries, $countries);
    ?>
    </div>

    <div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('products', 'Add product')); ?>
    </div>

<?php $this->endWidget(); ?>
</div>
