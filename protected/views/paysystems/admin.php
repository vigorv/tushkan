<div>
<a href="<?php echo $this->createUrl('paysystems/form');?>"><?php echo Yii::t('pays', 'Add Paysystem');?></a>
</div>
<?php
	if (!empty($paysystems))
	{
		echo '<table>';
		echo '<tr>';
		echo '<td>' . Yii::t('common', 'action') . '</td>';
		echo '<td>id</td>';
		echo '<td>' . Yii::t('common', 'Title') . '</td>';
		echo '<td>' . Yii::t('common', 'Class') . '</td>';
		echo '</tr>';
		foreach ($paysystems as $p)
		{
			echo '<tr>';
			$href = Yii::app()->createUrl('/paysystems/edit/' . $p['id']);
			if (empty($p['active']))
				$action = '<a href="' . Yii::app()->createUrl('/paysystems/restore/' . $p['id']) . '">' . Yii::t('common', 'restore') . '</a>';
			else
				$action = '<a href="' . Yii::app()->createUrl('/paysystems/delete/' . $p['id']) . '">' . Yii::t('common', 'delete') . '</a>';
			echo '<td><input type="checkbox" name="massIds[' . $p['id'] . ']" />
				 ' . $action . '
			</td>';
			echo '<td>' . $p['id'] . '</td>';
			echo '<td><a href="' . $href . '">' . $p['title'] . '</a></td>';
			echo '<td>' . $p['class'] . '</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
