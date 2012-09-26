<div class="span12 no-horizontal-margin inside-movie my-catalog">
<div class="pad-content">
<?php
echo '
	<h2>' . Yii::t('common', 'Tariffs') . '</h2>
	<table class="table table-hover">
	<thead>
		<tr>
			<th>' . Yii::t('common', 'Title') . '</th>
			<th>' . Yii::t('common', 'Space') . '</th>
			<th>' . Yii::t('common', 'Device count') . '</th>
			<th>' . Yii::t('common', 'Period') . '</th>
			<th>' . Yii::t('common', 'Price') . '</th>
		</tr>
	</thead>
	<tbody>
';

foreach ($lst as $l)
{
	if ($l['is_archive'])
		$arc = ' (в архиве)';
	else
		$arc = '';
	echo '
		<tr>
			<td>' . $l['title'] . $arc . '</td>
			<td>' . Utils::sizeFormat($l['size_limit'] * _MB_). '</td>
			<td>' . $l['device_cnt'] . '</td>
			<td>' . Utils::spellPeriod($l['period']) . '</td>
			<td>' . $l['price'] . ', ' . Yii::t('pays', _CURRENCY_) . '</td>
		</tr>
	';
}
echo'</tbody></table>';
?>
</div>
</div>