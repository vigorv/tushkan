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
				if (empty($p['ppvid']))
				{
					$curParamId = -1;//ДЛЯ НОВЫХ ПАРАМЕТРОВ
				}
				else
				{
					$curParamId = $p['ppvid'];
				}
				$title = Yii::t('params', $p['title']);
				echo'
				<input name="ProductForm[params][' . $curVariantId . '][' . $pid . '][id]" type="hidden" value="' . $curParamId . '" />
				<input name="ProductForm[params][' . $curVariantId . '][' . $pid . '][title]" type="hidden" value="' . $title . '" />
				' . $title . ':<br />
				<input name="ProductForm[params][' . $curVariantId . '][' . $pid . '][value]" type="text" value="" class="text ui-widget-content ui-corner-all" />
				<input name="ProductForm[params][' . $curVariantId . '][' . $pid . '][variant_id]" type="hidden" value="' . $curVariantId . '" />
				<br />
				';
			}
		}
	break;

	case "wizardtypeparams":
		if (!empty($result['lst']))
		{
			foreach($result['lst'] as $p)
			{
				$pid = $p['id'];
				$title = Yii::t('params', $p['title']);
				echo'<div class="chess">
				<input name="wizardForm[params][' . $pid . '][id]" type="hidden" value="' . $pid . '" />
				' . $title . ':<br />
				<input name="wizardForm[params][' . $pid . '][value]" type="text" value="" class="text ui-widget-content ui-corner-all" />
				</div>
				';
			}
		}
	break;
}