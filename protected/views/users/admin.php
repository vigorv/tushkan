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
	if (!empty($users))
	{
		echo '<table>';
		echo '<tr>';
		echo '<td>' . Yii::t('common', 'action') . '</td>';
		echo '<td>id</td>';
		echo '<td>' . Yii::t('users', 'name') . '</td>';
		echo '<td>email</td>';
		echo '<td>' . Yii::t('users', 'registered date') . '</td>';
		echo '<td>' . Yii::t('users', 'last visit') . '</td>';
		echo '</tr>';
		foreach ($users as $u)
		{
			echo '<tr>';
			(empty($u['gtitle'])) ? $g = '' : $g = ' (' . $u['gtitle'] . ')';
			$href = Yii::app()->createUrl('/users/edit/' . $u['id']);
			$actions = array();
			if (empty($u['confirmed']))
			{
				//$actions[] = '<a class="btn" href="' . Yii::app()->createUrl('/users/confirm/' . $u['id']) . '">' . Yii::t('common', 'confirm') . '</a>';
				$actions[] = '<a class="btn" href="#" onclick="return sendConfirm(\'' . $u['sess_id'] . '\', this);">' . Yii::t('common', 'send confirm') . '</a>';
			}
			if (empty($u['active']))
				$actions[] = '<a class="btn" href="' . Yii::app()->createUrl('/users/restore/' . $u['id']) . '">' . Yii::t('common', 'restore') . '</a>';
			else
				$actions[] = '<a class="btn" href="' . Yii::app()->createUrl('/users/delete/' . $u['id']) . '">' . Yii::t('common', 'delete') . '</a>';
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
	}
	$this->widget('ext.pagination.EPaginationWidget', array('params' => $paginationParams));
