<?php
	echo '<h3>' . Yii::t('common', 'filter') . '</h3>';
	echo $formHead;

	$filterInfo = Utils::getFilterInfo();
	if (!empty($filterInfo['search']))
		$search = $filterInfo['search'];
	else
		$search = Utils::formatFilterParam('search');
	echo 'По названию<br /><input type="text" name="' . $search['uname'] . '" value="' . htmlspecialchars($search['value']) . '">';

	Yii::app()->getClientScript()->registerCssFile(Yii::app()->request->baseUrl . "/js/jquery-ui-1.7.3.custom/css/custom-theme/jquery-ui-1.7.3.custom.css");
	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/jquery-ui/jquery.ui.datepicker-ru.js");

	if (!empty($filterInfo['from']))
		$from = $filterInfo['from'];
	else
		$from = Utils::formatFilterParam('from');

	if (!empty($filterInfo['to']))
		$to = $filterInfo['to'];
	else
		$to = Utils::formatFilterParam('to');

	echo '<br />' . Yii::t('common', 'Over a period') . ' ';
?>
<script type="text/javascript">
	$(function() {
		$( "#dpfrom" ).datepicker({ dateFormat: 'yy-mm-dd'});
		$( "#dpto" ).datepicker({ dateFormat: 'yy-mm-dd'});
	});
</script>
	<?php echo Yii::t('common', 'from');?>:<br /><input id="dpfrom" name="<?php echo $from['uname']; ?>" value="<?php echo $from['value']; ?>" type="text" />
	<?php echo Yii::t('common', 'to');?>: <input id="dpto" name="<?php echo $to['uname']; ?>" value="<?php echo $to['value']; ?>" type="text" /><br />
<?php
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
