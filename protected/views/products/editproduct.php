<div class="form">
<?php

	$form=$this->beginWidget('CActiveForm');
	$aLst = Utils::getActiveStates();
/*
echo '<pre>';
print_r($pLst);
echo '</pre>';
//exit;
//*/
?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <?php echo $form->label($model, 'title', array('label' => Yii::t('common', 'Title'))); ?>
        <?php echo $form->textField($model, 'title', array('value' => $info['title'], 'class' => 'text')); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'partner_id', array('label' => Yii::t('common', 'Partner'))); ?>
        <?php echo $form->dropdownlist($model, 'partner_id', $pLst, array(
        	'options' => array($info['partner_id'] => array('selected' => 'selected')),
        	'class' => 'text'
        	)); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'description', array('label' => Yii::t('common', 'Description'))); ?>
        <?php
        	$model->description = $info['description'];
        	echo $form->textArea($model, 'description', array('class' => 'text'));
        ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'active', array('label' => Yii::t('common', 'Active'))); ?>
        <?php echo $form->dropdownlist($model, 'active', $aLst,
        	array(
       			'options' => array($info['active'] => array('selected' => 'selected')),
        		'class' => 'text',
        	));
        ?>
    </div>

    <div class="row">
        <?php echo $form->label($model, 'srt', array('label' => Yii::t('common', 'Srt'))); ?>
        <?php echo $form->textField($model, 'srt', array('value' => $info['srt'], 'class' => 'text')); ?>
    </div>

    <div class="row submit">
        <?php echo CHtml::submitButton(Yii::t('common', 'Save')); ?>
    </div>
<?php $this->endWidget(); ?>
</div>
	<script type="text/javascript">
		function groupFormSubmit(f)
		{
			var str='';
			element = '#actionSelectId';
			str = str + $(element).attr('name') +'='+$(element).val()+'&';
			element = '#parentSelectId';
			str = str + $(element).attr('name') +'='+$(element).val()+'&';

			str = str + getFormParamsToString(f.id);
			$.post('/products/editajax/<?php echo $info['id']; ?>', str, function(redirect){
				//if (redirect == '')
					redirect = '/products/editproduct/<?php echo $info['id']; ?>';
				window.location.href = redirect;
			});
			return false;
		}

		function getFormParamsToString(fid)
		{
			var amp = '';
			var str = '';
			$('#' + fid + ' input').each(function(n,element){
				if($(element).attr('type')=='button')
					return;
				if($(element).attr('type')=='submit')
					return;
				if (($(element).attr('type') == 'checkbox') && ($(element).attr('checked') != 'checked'))
					return;

				str = str + amp + $(element).attr('name') +'=' + $(element).val();
				amp = '&';
			});
			$('#' + fid + ' select').each(function(n,element){
				str = str + amp + $(element).attr('name') +'=' + $(element).val();
				amp = '&';
			});
			$('#' + fid + ' textarea').each(function(n,element){
				str = str + amp + $(element).attr('name') +'=' + $(element).val();
				amp = '&';
			});
//alert(str);
			return str;
		}

		function openModalForm(vid, tid)
		{
			$('#editVariantFormContent').html('');
			$('#editVariantFormDiv').modal();
			$('#editVariantFormContent').load('/products/ajax', {action: "variantparams", variantId: vid});
			return false;
		}

		function variantFormSubmit(f)
		{
			var str='';
			str = str + getFormParamsToString("editVariantForm");
			$.post('/products/editvariant/' + $('#variantId').val(), str, function(state){
				$('#editVariantFormContent').html(state);
				//$('#editVariantFormDiv').modal('hide');
			});
			return false;
		}

	</script>
<?php
	if (!empty($variantsTree))
	{
		echo '
			<form id="variantsTreeForm" method="post" onsubmit="return groupFormSubmit(this);">
		';
	    echo '<h4>' . Yii::t('common', 'Variants') . '</h4>';
		echo '<ul>';
		foreach ($variantsTree as $vt)
		{
			$actions = array('/products/editajax');
			if (empty($vt['title']))
				$vt['title'] = 'no title';
			echo '<li><input type="checkbox" name="group_ids[' . $vt['id'] . ']" value="1" /> <a href="#" onclick="return openModalForm(' . $vt['id'] . ');">' . $vt['title'] . '</a> (id = ' . $vt['id'] . ')';
			if (!empty($vt['childsInfo']))
			{
				echo '<ul>';
				foreach ($vt['childsInfo'] as $ci)
				{
					if (empty($ci['title']))
						$ci['title'] = 'no title';
					echo '<li><input type="checkbox" name="group_ids[' . $ci['id'] . ']" value="1" /> <a href="#" onclick="return openModalForm(' . $ci['id'] . ');">' . $ci['title'] . '</a> (id = ' . $ci['id'] . ')</li>';
				}
				echo '</ul>';//ЗАКРЫВАЕМ СПИСОК ПОТОМКОВ
			}
			echo '</li>';
		}
		echo '</ul>';
		//ВЫВОДИМ СЕЛЕКТ ВЫБОРА ОПЕРАЦИИ
		$actionCommands = array('group' => 'Объединить', 'ungroup' => 'Разгруппировать', 'toparent' => 'Закрепить за вариантом', 'del' => 'Удалить');
		$sel = '<select id="actionSelectId" name="action"><option value="">Действие с отмеченными</option>';
		foreach ($actionCommands as $ak => $av)
		{
			$sel .= '<option value="' . $ak . '">' . $av . '</option>';
		}
		$sel .= '</select><br />';
		echo $sel;

		//ВЫВОДИМ СЕЛЕКТ ВЫБОРА ПРЕДКА
		$sel = '<select id="parentSelectId" name="parentId"><option value="">Выбор родительского варианта</option>';
		foreach ($variantsTree as $vt)
		{
			if (!empty($vt['childsInfo']) || (($vt['childs'] != '') && ($vt['childs'] != ',,')))
				$sel .= '<option value="' . $vt['id'] . '">' . $vt['title'] . ' (id = ' . $vt['id'] . ')</option>';
		}
		$sel .= '</select><br />';
		echo $sel;

		echo '
			<button type="submit">Выполнить</button>
			</form>
		';
	}
?>
    <div class="modal hide" id="editVariantFormDiv">
	    <div class="modal-header">
		    <button type="button" class="close" data-dismiss="modal">×</button>
		    <h3><?php echo Yii::t('common', 'Edit variant'); ?></h3>
	    </div>
	    <div class="modal-body">
	    	<form id="editVariantForm111" method="post" onsubmit="return variantFormSubmit(this)">
	    	</form>
	    	<div id="editVariantFormContent">
	    	</div>
	    </div>
    </div>
