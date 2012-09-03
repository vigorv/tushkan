<?php

switch ($subAction)
{
	case "contentinbox":
		function dirwalk($dir)
		{
			echo '<ul>' . basename($dir);
			$dh = opendir($dir);
			while (($file = readdir($dh)) !== false)
			{
				if (($file == '.') || ($file == '..'))
					continue;
				echo '<li>';
				if (is_dir($dir . '/' . $file))
					dirwalk($dir . '/' . $file);
				else
				{
					$baseDir = Yii::app()->params['tushkan']['content_inbox'];
					$file = str_replace($baseDir, '', $dir . '/' . $file);
					echo '<input type="checkbox" value="' . ($file) . '" />' . basename($file);
				}
				echo '</li>';
        	}
        	echo '</ul>';
			closedir($dh);
		}

		$dir = Yii::app()->params['tushkan']['content_inbox'];
		echo '<h3>Входящий контент ' . $dir . '</h3>';
		if (file_exists($dir) && is_dir($dir))
		{
			dirwalk($dir);
			echo'<button class="btn" onclick="return generateVariants();">Ok</button>';
		}
		else
		{
			echo 'Ошибка доступа к директории входящего контента';
		}
	break;

	case "typeparams":
		if (!empty($result['lst']))
		{
			$curVariantId = $result['variantId'];
			foreach($result['lst'] as $p)
			{
				$pid = $p['id'];
				if (empty($p['vlid']))
				{
					$curValueId = 0;//ДЛЯ ЗНАЧЕНИЙ НОВЫХ ПАРАМЕТРОВ
				}
				else
				{
					$curValueId = $p['vlid'];
				}
				$title = Yii::t('params', $p['title']);
				echo'
				<input name="ProductForm[params][' . $curVariantId . '][' . $pid . '][id]" type="hidden" value="' . $pid . '" />
				<input name="ProductForm[params][' . $curVariantId . '][' . $pid . '][title]" type="hidden" value="' . $title . '" />
				' . $title . ':<br />
				<input name="ProductForm[params][' . $curVariantId . '][' . $pid . '][value]" type="text" value="" class="text ui-widget-content ui-corner-all" />
				<input name="ProductForm[params][' . $curVariantId . '][' . $pid . '][variant_id]" type="hidden" value="' . $curVariantId . '" />
				<input name="ProductForm[params][' . $curVariantId . '][' . $pid . '][vlid]" type="hidden" value="' . $curValueId . '" />
				<br />
				';
			}
		}
	break;

	case "variantparams":
		if (!empty($result['lst']))
		{
	$form=$this->beginWidget('CActiveForm', array('htmlOptions' => array('id' => 'editVariantForm', 'onsubmit' => 'return variantFormSubmit(this)')));
	$aLst = Utils::getActiveStates();
	$model = $result['variantForm'];
	$info = $result['info'];
	$sLst = $result['sLst'];
?>
		<input type="hidden" id="variantId" name="id" value="<?php echo $result['variantId']; ?>" />
        <?php echo $form->label($model, 'title', array('label' => Yii::t('common', 'Title'))); ?>
        <?php echo $form->textField($model, 'title', array('value' => $info['title'], 'class' => 'text')); ?>
<br />
        <?php echo $form->label($model, 'description', array('label' => Yii::t('common', 'Description'))); ?>
        <?php
        	$model->description = $info['description'];
        	echo $form->textArea($model, 'description', array('class' => 'text'));
        ?>
<br />
        <?php echo $form->label($model, 'active', array('label' => Yii::t('common', 'Active'))); ?>
        <?php echo $form->dropdownlist($model, 'active', $aLst,
        	array(
       			'options' => array($info['active'] => array('selected' => 'selected')),
        		'class' => 'text',
        	));
        ?>
<br />
        <?php echo $form->label($model, 'original_id', array('label' => Yii::t('common', 'original_id'))); ?>
        <?php echo $form->textField($model, 'original_id', array('value' => $info['original_id'], 'class' => 'text')); ?>
<br />
        <?php echo $form->label($model, 'sub_id', array('label' => Yii::t('common', 'SubId'))); ?>
        <?php echo $form->dropdownlist($model, 'sub_id', $sLst,
        	array(
       			'options' => array($info['sub_id'] => array('selected' => 'selected')),
        		'class' => 'text',
        	));
        ?>
<br />
<?php
		$checked = '';
		if ($info['online_only']) $checked = 'checked';
		echo'
			<input type="checkbox" ' . $checked . ' name="VariantForm[online_only]" class="text" /> ' .  Yii::t('common', 'online only') . '<br />
		';
			$presets = Utils::pushIndexToKey('id', CPresets::getPresets());
			foreach($result['lst'] as $p)
			{
				$pid = $p['id']; //ИДЕНТИФИКАТОР ПАРАМЕТРА
				$vid = $p['vlid']; //ИДЕНТИФИКАТОР ЗНАЧЕНИЯ ПАРАМЕТРА
				$title = Yii::t('params', $p['title']);
				$preset = '';
				if (!empty($p['preset_id']))
					$preset = ' (' . $presets[$p['preset_id']]['title'] . ')';
				echo'
				' . $title . $preset . ':<br />
				<input name="VariantForm[params][' . $vid . ']" type="text" value="' . $p['value'] . '" class="text" />
				<br />
				';
			}
?>
		    <input type="submit" class="btn btn-primary" value="<?php echo Yii::t('common', 'Submit'); ?>" />
		    <a href="" class="btn" data-dismiss="modal"><?php echo Yii::t('common', 'Cancel'); ?></a>
		    <br />
<?php
		}
$this->endWidget();
?>
	</div>
<?php
	break;

	case "wizardtypeparams":
		if (!empty($result['lst']))
		{
		    $mediaList = Utils::getMediaList();
    		$detectedType = 0; $detectedTypeName = '';
    		foreach ($mediaList as $k => $v)
    		{
    			if ($k == $typeId)
    			{
    				$detectedType = $k;
    				$detectedTypeName = $v['title'];
    			}
    		}
?>
<div class="span12 no-horizontal-margin inside-movie my-catalog">
	<h1><?php echo Yii::t('common', 'Typify') . ' ' . Yii::t('common', 'as') . ' "' . $detectedTypeName . '"'; ?></h1>
	<div class="span9 movie-text">
	<div class="span12 no-horizontal-margin some-space"></div>
	<script type="text/javascript">
		function ajaxSubmit(f)
		{
			var str='ajax=1&';
			$('#' + f.id + ' input').each(function(n,element){
			if($(element).attr('type')!='button'){
				str = str + $(element).attr('name') +'='+$(element).val()+'&';
			}
			});
			//alert(str);
				$.post('/universe/postuploadparams', str, function(redirect){
					if (redirect == '') redirect = '/universe';
					$('#content').load(redirect);
				});
			return false;
		}
	</script>
	<form id="wizardParamsFormId" method="post" onsubmit="return ajaxSubmit(this);">
<?php
			$rNote = '';
			foreach($result['lst'] as $p)
			{
				$pid = $p['id'];
				$r = '';
				if (!empty($p['required']))
				{
					$r = ' <span class="required">*</span>';
					$rNote = '<div class="block_content"><p class="note">' . $r . ' - ' . Yii::t('common', 'required field') . '</p></div>';
				}
				$title = Yii::t('params', $p['title']);
				echo'<div class="chess">
				<input name="paramsForm[params][' . $pid . '][id]" type="hidden" value="' . $pid . '" />
				' . $title . ':' . $r . '<br />
				<input name="paramsForm[params][' . $pid . '][value]" type="text" value="" />
				</div>
				';
			}
			echo $rNote;
			echo '
				<div class="divider"></div>
				<button class="btn" type="submit">' . Yii::t('common', 'Typify') . '</button>
				';
?>
	</form>
	</div>
</div>
<?php
		}
	break;

	case "addtocloud":
		//ВЫВОД ИКОНКМ СОГЛАСНО СОСТОЯНИЮ ПРОДУКТА
		if (($result == 'ok') || (substr($result, 0, 5) == 'queue') || (intval($result) > 0))
		{
			$state = $result;
			$partnerId = intval($get['pid']);
			$originalId = intval($get['oid']);
			if (!empty($get['vid']))
			{
				$originalVariantId = intval($get['vid']);
			}
			if (intval($result) > 0)
			{
				if (!$inCloud)
					$state = 'universe_add';
				else
					$state = 'universe';
			}
		}
		else
			$state = 'error';

		if ((substr($result, 0, 5) == 'queue'))
		{
			$progress = explode('|', $result);
			$state = 'queue';
			if (count($progress) >= 3)
			{
				$result = '. конвертирование ' . ($progress[1]*10+$progress[2]*3) . '%';
			}
		}
		$alt = $state;
		if (empty($get['pid']))
			$get['pid'] = 0;
		if (empty($get['oid']))
			$get['oid'] = 0;
		if (empty($get['vid']))
			$get['vid'] = 0;
		switch ($state)
		{
			case "universe_add":
				$alt = 'добавить в пространство';
				$ahref = '<a href="/products/addtocloud/pid/' . $get['pid'] . '/oid/' . $get['oid'] . '/vid/' . $get['vid'] . '/do/add" title="' . $alt . '">';
			break;
			case "universe":
				$alt = 'в пространстве';
				$ahref = '<a target="_parent" href="/#/universe/tview/' . $result . '" title="' . $alt . '">';
			break;
			case "queue":
				$alt = 'в очереди на добавление в пространство';
				$alt .= ' ' . $result; $ahref = '<a title="' . $alt . '">';
			break;
			case "ok":
				$alt = 'добавить в пространство';
				$ahref = '<a href="/products/addtoqueue/pid/' . $partnerId . '/oid/' . $originalId . '/vid/' . $originalVariantId . '" title="' . $alt . '">';
			break;
			default:
				$alt = 'ошибка';
				$alt .= ' ' . $result; $ahref = '<a title="' . $alt . '">';
			break;
		}

		echo '
		<html>
			<head></head>
			<body style="background-color: white">
				' . $ahref . '<img width="28" src="/images/cloud_' . $state . '.png" alt="' . $alt . '" /></a>
			</body>
		</html>
		';
	break;
}
