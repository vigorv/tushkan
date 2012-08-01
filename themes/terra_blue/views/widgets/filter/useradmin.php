<?php
	echo '<h3>' . Yii::t('common', 'filter') . '</h3>';
	echo $formHead;
	$filterInfo = Utils::getFilterInfo();

	if (!empty($filterInfo['search']))
		$search = $filterInfo['search'];
	else
		$search = Utils::formatFilterParam('search');
	echo 'По имени или email<br /><input type="text" name="' . $search['uname'] . '" value="' . htmlspecialchars($search['value']) . '">';

	// В ФОРМЕ ФИЛЬТРА МОЖНО ТАКЖЕ ИСПОЛЬЗОВАТЬ И СОРТИРОВКУ (КАК ВИЗУАЛЬНЫЕ ТАК СКРЫТЫЕ ЭЛЕМЕНТЫ ИНТЕРФЕЙСА)
	$sortInfo = Utils::getSortInfo();
	if (!empty($sortInfo['name']))
		$nameSort = $sortInfo['name'];
	else
		$nameSort = Utils::formatSortParam('name');
	echo '<input type="hidden" name="' . $nameSort['uname'] . '" value="' . $nameSort['dir'] . '" />';

	echo '<br /><input type="submit" class="btn" value="' . Yii::t('common', 'filter') . '" />';

	echo $formEnd;
