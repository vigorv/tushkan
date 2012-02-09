<div class="form">
<?php

	$form=$this->beginWidget('CActiveForm');
	$aLst = Utils::getActiveStates();
?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->label($model, 'title', array('label' => Yii::t('common', 'Title'))); ?>
        <?php echo $form->textField($model, 'title', array('value' => $info['title'], 'class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'partner_id', array('label' => Yii::t('common', 'Partner'))); ?>
        <?php echo $form->dropdownlist($model, 'partner_id', $pLst, array('class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'description', array('label' => Yii::t('common', 'Description'))); ?>
        <?php
        	$model->description = $info['description'];
        	echo $form->textArea($model, 'description', array('class' => 'text ui-widget-content ui-corner-all'));
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

    <div class="row">
        <?php echo $form->label($model, 'srt', array('label' => Yii::t('common', 'Srt'))); ?>
        <?php echo $form->textField($model, 'srt', array('value' => $info['srt'], 'class' => 'text ui-widget-content ui-corner-all')); ?>
    </div>

    <?php echo '<h4>' . Yii::t('common', 'Variants') . '</h4>'; ?>
    <div id="variants_params" class="row stolb">
<script type="text/javascript">
	variantNum = 1;
	newVariantId = <?php echo (count($variants)*(-1)-1); ?>;
	function variantParams(vId, selObj)
	{
		$('#variant'+ vId +'_params').load('/products/ajax', {typeId: selObj.value, action: "typeparams", variantId: vId});
		return true;
	}
	function newVariant()
	{
		$('#variants_params').append('<div class="formvariantblock" id="variantId' + newVariantId + '"><h5><?php echo Yii::t('common', 'Variant'); ?> №' + (variantNum++) + '</h5></div>');
		$('#variantId' + newVariantId).append('<input type="hidden" name="ProductForm[variants][' + newVariantId + '][id]" value="' + newVariantId + '" />');
		$('#variantId' + newVariantId).append('<input type="checkbox" name="ProductForm[variants][' + newVariantId + '][online_only]" class="text ui-widget-content ui-corner-all" /> <?php echo Yii::t('common', 'online only');?><br />');
		$('#variantId' + newVariantId).append('<select name="ProductForm[variants][' + newVariantId + '][active]" id="variants' + newVariantId + 'active" class="text ui-widget-content ui-corner-all"></select><br />');
		$('#variantId' + newVariantId).append('<select name="ProductForm[variants][' + newVariantId + '][type_id]"onchange="return variantParams(' + newVariantId + ', this);" id="variants' + newVariantId + 'type_id" class="text ui-widget-content ui-corner-all"></select><br />');
		$('#variants' + newVariantId + 'type_id').append($('<option value="0">Выберите тип</option>'));
<?php
	foreach ($aLst as $k => $v)
	{
?>
		$('#variants' + newVariantId + 'active').append($('<option value="<?php echo $k;?>"><?php echo $v;?></option>'));
<?php
	}

	foreach ($tLst as $k => $v)
	{
?>
		$('#variants' + newVariantId + 'type_id').append($('<option value="<?php echo $k;?>"><?php echo $v;?></option>'));
<?php
	}
?>
		$('#variants_params').append('<div class="formvariantparamsblock" id="variant' + newVariantId + '_params"></div>');
		newVariantId--;
		return false;
	}
</script>
    <?php
    	if (!empty($variants))
    	{
    		foreach ($variants as $vk => $variant)
    		{
    			$checked = '';
    			if (empty($variant['online_only']))
    				$variant['online_only'] = '';
    			if (($variant['online_only'] == 'on') || ($variant['online_only'] == '1'))
    				$checked = 'checked';
//НАЧАЛО ПАРАМЕТРОВ ВАРИАНТА
				echo'
    			<div id="variantId' . $vk . '">
					<input type="hidden" name="ProductForm[variants][' . $vk . '][id]" value="' . $variant['id'] .  '" />
				';
//ТИП ВАРИАНТА
				if ($vk > 0)
				{
					//У СОХРАНЕННОГО ВАРИАНТА ТИП СМЕНИТЬ НЕЛЬЗЯ
					echo '
						<h3>' . $tLst[$variant['type_id']] . '</h3>
						<input type="hidden" name="ProductForm[variants][' . $vk . '][type_id]" value="' . $variant['type_id'] .  '" />
					';
				}
//ВИДИМОСТЬ
				echo'
					<input type="checkbox" ' . $checked . ' name="ProductForm[variants][' . $vk . '][online_only]" class="text ui-widget-content ui-corner-all" /> ' .  Yii::t('common', 'online only') . '<br />
					<select name="ProductForm[variants][' . $vk . '][active]" class="text ui-widget-content ui-corner-all">
				';
				foreach ($aLst as $k => $v)
				{
					$selected = '';
					if ($k == $variant['active'])
						$selected = 'selected';
					echo '<option ' . $selected . ' value="' . $k . '">' . $v . '</option>';
				}
				echo '
					</select><br />
				';
				if ($vk < 0)
				{
					echo'
						<select onchange="return variantParams(' . $vk .  ', this);" id="variants' . $vk .  'type_id" name="ProductForm[variants][' . $vk . '][type_id]" class="text ui-widget-content ui-corner-all">
					';
					foreach ($tLst as $k => $v)
					{
						$selected = '';
						if ($k == $variant['type_id'])
							$selected = 'selected';
						echo '<option ' . $selected . ' value="' . $k . '">' . $v . '</option>';
					}
					echo '
						</select>
					';
				}
				echo '
					<div class="variantformdiv" id="variant' . $vk . '_params">
				';

				foreach ($params[$vk] as $pid => $param)
				{
					echo'
					<input name="ProductForm[params][' . $vk . '][' . $pid . '][id]" type="hidden" value="-1" />
					<input name="ProductForm[params][' . $vk . '][' . $pid . '][title]" type="hidden" value="' . $param['title'] . '" />
					' . Yii::t('params', $param['title']) . ':<br />
					<input name="ProductForm[params][' . $vk . '][' . $pid . '][value]" type="text" value="' . $param['value'] . '" class="text ui-widget-content ui-corner-all" />
					<input name="ProductForm[params][' . $vk . '][' . $pid . '][variant_id]" type="hidden" value="' . $param['variant_id'] . '" />
					<br />
					';
				}
				echo'
					</div>
    			</div>
    			';
    		}
    	}
    ?>
    </div>
	<a href="" onclick="return newVariant();">Новый вариант</a>

    <div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('common', 'Save')); ?>
    </div>

<?php $this->endWidget(); ?>
</div>
