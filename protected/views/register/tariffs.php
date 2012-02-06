<?php
echo '
	<h2>' . Yii::t('common', 'Tariffs') . '!</h2>
	<table>
		<tr>
			<td>' . Yii::t('common', 'Title') . '</td>
			<td>' . Yii::t('common', 'Space') . '</td>
			<td>' . Yii::t('common', 'Device count') . '</td>
			<td>' . Yii::t('common', 'Period') . '</td>
			<td>' . Yii::t('common', 'Price') . '</td>
		</tr>
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
			<td>' . Utils::sizeFormat($l['size_limit'] * 1024). '</td>
			<td>' . $l['device_cnt'] . '</td>
			<td>' . Utils::spellPeriod($l['period']) . '</td>
			<td>' . $l['price'] . ', ' . Yii::t('pays', _CURRENCY_) . '</td>
		</tr>
	';
}
echo'</table>';