<?php
Yii::import('ext.pagefilters.CPageFilter');

/**
 * компонент фильтра продуктов выборки с учетом входных данных
 *
 */
class CPageFilterProduct extends CPageFilter
{
	public function __construct()
	{
		$this->initParams(array('s_title', 's_dir'));
	}
}