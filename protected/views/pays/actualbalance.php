<?php
$balance = 0;
if (!empty($balanceInfo))
	$balance = sprintf("%01.2f", $balanceInfo['balance']);
if (!empty($balance))
{
	$balance = Yii::t('users', 'Account balance') . ': ' . $balance . ' руб';
	echo $balance;
}
