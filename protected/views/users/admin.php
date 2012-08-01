<div>
<a href="<?php echo $this->createUrl('users/form');?>"><?php echo Yii::t('users', 'Add User');?></a>
</div>
<script type="text/javascript">
	function sendConfirm(hash, a)
	{
		$(a).attr("disabled", "disabled");
		$.get("/register/confirm/" + hash);
		return false;
	}
</script>
<?php
	$this->widget('ext.filterwidget.EFilterWidget', array('method' => 'POST', 'filterName' => 'useradmin'));
	if (!empty($users))
	{
		echo '<table>';
		echo '<tr>';
		echo '<td>' . Yii::t('common', 'action') . '</td>';
		echo '<td>id</td>';
		echo '<td><a href="' . Utils::preparePageSortUrl('/users/admin', 'name', false) . '">' . Yii::t('users', 'name') . '</a></td>';
		echo '<td><a href="' . Utils::preparePageSortUrl('/users/admin', 'email') . '">email</a></td>';
		echo '<td><a href="' . Utils::preparePageSortUrl('/users/admin', 'created') . '">' . Yii::t('users', 'registered date') . '</a></td>';
		echo '<td>' . Yii::t('users', 'last visit') . '</td>';
		echo '</tr>';
		foreach ($users as $u)
		{
			$banned = false;
			if (!empty($banInfo))
			{
				$banDetail = array();
				foreach ($banInfo as $bi)
					if ($bi['user_id'] == $u['id'])
					{
						$banned = true;
						$period = Yii::t('common', 'from') . ' ' . $bi['start'];
						if (strtotime($bi['finish']) > 0)
							$period .= ' ' . Yii::t('common', 'to') . ' ' . $bi['finish'];
						$banDetail[] = array(
							'id' => $bi['id'],
							'period' => $period,
							'reason' => Yii::t('common', 'banreason_' . $bi['reason']),
							'state' => Yii::t('common', 'banstate_' . $bi['state']),
							);
					}
			}
			echo '<tr>';
			(empty($u['gtitle'])) ? $g = '' : $g = ' (' . $u['gtitle'] . ')';
			$href = Yii::app()->createUrl('/users/edit/' . $u['id']);
			$actions = array();
			if (empty($u['confirmed']))
			{
				//$actions[] = '<a class="btn" href="' . Yii::app()->createUrl('/users/confirm/' . $u['id']) . '">' . Yii::t('common', 'confirm') . '</a>';
				$actions[] = '<a class="btn" href="#" onclick="return sendConfirm(\'' . $u['sess_id'] . '\', this);">' . Yii::t('common', 'send confirm') . '</a>';
			}
			if ($u['active'] == _IS_ADMIN_)
				$actions[] = '<a class="btn" href="' . Yii::app()->createUrl('/users/restore/' . $u['id']) . '">' . Yii::t('common', 'restore') . '</a>';
			else
				$actions[] = '<a class="btn" href="' . Yii::app()->createUrl('/users/hide/' . $u['id']) . '">' . Yii::t('common', 'hide') . '</a>';
			$actions[] = '<a class="btn" href="' . Yii::app()->createUrl('/users/delete/' . $u['id']) . '">' . Yii::t('common', 'delete') . '</a>';
			if (!$banned)
				$actions[] = '<a class="btn" href="' . Yii::app()->createUrl('/users/ban/' . $u['id']) . '">' . Yii::t('common', 'ban') . ' | ' . Yii::t('common', 'banstate_0') . ' | ' . Yii::t('common', 'banreason_1') . '</a>';
			else
			{
				foreach ($banDetail as $bd)
					$actions[] = '<a class="btn" href="' . Yii::app()->createUrl('/users/unban/' . $bd['id']) . '">' . Yii::t('common', 'unban') . ' | ' . $bd['state'] . ' | ' . $bd['reason'] . '</a>';
			}
			echo '<td><input type="checkbox" name="massIds[' . $u['id'] . ']" />
				 ' . implode(' ', $actions) . '
			</td>';
			if (empty($u['name']))
				$u['name'] = '[ ' . Yii::t('users', 'name') . ' ]';
			echo '<td>' . $u['id'] . '</td>';
			echo '<td><a href="' . $href . '">' . $u['name'] . '</a>' . $g . '</td>';
			echo '<td>' . $u['email'] . '</td>';
			echo '<td>' . date('Y-m-d', strtotime($u['created'])) . '</td>';
			echo '<td>' . date('Y-m-d', strtotime($u['lastvisit'])) . '</td>';
			echo '</tr>';
		}
		echo '</table>';
		$this->widget('ext.pagination.EPaginationWidget', array('params' => $paginationParams));
	}
