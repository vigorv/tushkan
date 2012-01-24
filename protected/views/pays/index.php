<?php
	$summa = 0;
	if (!empty($balance['balance']))
		$summa = sprintf("%01.2f", $balance['balance']);
	echo '<h2>' . Yii::t('users', 'Account balance') . ' (' . $summa . ' ' . Yii::t('pays', _CURRENCY_) . ')</h2>';

	echo '<h3><a href="/pays/do/1">' . Yii::t('users', 'Fill up balance') . '</h3>';