<?php

switch ($subAction)
{
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

	case "wizardtypeparams":
		if (!empty($result['lst']))
		{
			echo '<form id="wizardParamsFormId" method="post" action="/universe/postuploadparams">';
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
				<input name="paramsForm[params][' . $pid . '][value]" type="text" value="" class="text ui-widget-content ui-corner-all" />
				</div>
				';
			}
			echo $rNote;
			echo '<div class="divider"></div><button class="btn" type="submit">' . Yii::t('common', 'Submit') . '</button></form>';
		}
	break;

	case "addtocloud":
		//ВЫВОД ИКОНКМ СОГЛАСНО СОСТОЯНИЮ ПРОДУКТА
		if (($result == 'ok') || ($result == 'queue') || (intval($result) > 0))
		{
			$state = $result;
			$partnerId = $get['pid'];
			$originalId = $get['oid'];
			if (!empty($get['vid']))
			{
				$originalVariantId = $get['vid'];
			}
			if (intval($result) > 0)
			{
				$state = 'universe';
			}
		}
		else
			$state = 'error';

		switch ($state)
		{
			case "universe":
				$ahref = '<a href="/universe/tview/' . $result . '" title="' . $state . '">';
			break;

			case "error":
				$ahref = '';
			break;
			case "queue":
				$ahref = '';
			break;
			case "ok":
				$ahref = '<a href="' . $href . '" title="' . $state . '">';
			break;
		}

		echo '
		<html>
			<head></head>
			<body style="background-color: white">
				' . $ahref . '<img width="20" src="/images/cloud_' . $state . '.png" alt="' . $state . '" /></a>
			</body>
		</html>
		';
	break;
}
