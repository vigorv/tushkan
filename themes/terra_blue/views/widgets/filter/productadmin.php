<?php
	echo '<h3>' . Yii::t('common', 'filter') . '</h3>';
	echo $formHead;
		$filterInfo = Utils::getFilterInfo();
		if (!empty($filterInfo['search']))
			$search = $filterInfo['search'];
		else
			$search = Utils::formatFilterParam('search');
		echo 'По названию<br /><input type="text" name="' . $search['uname'] . '" value="' . htmlspecialchars($search['value']) . '">';
		if (!empty($fields['partners']))
		{
			$partners = $fields['partners'];
			if (!empty($filterInfo['partner']))
				$partner = $filterInfo['partner'];
			else
			$partner = Utils::formatFilterParam('partner');
			$sel = '<select name="' . $partner['uname'] . '">';
			$sel .= '<option value="">Выберите партнера</option>';
			foreach($partners as $p)
			{
				$selected = '';
				if (!empty($partner['value']) && ($p['id'] == $partner['value']))
				{
					$selected = 'selected';
				}
				$sel .= '<option ' . $selected . ' value="' . $p["id"] . '">' . $p['title'] . '</option>';
			}
			$sel .= '</select>';
			echo '<br />' . $sel;
		}
	echo '<br /><input type="submit" class="btn" value="' . Yii::t('common', 'filter') . '" />';

	echo $formEnd;
