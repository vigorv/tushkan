<?php
$balance = 0;
if (!empty($balanceInfo))
	$balance = sprintf("%01.2f", $balanceInfo['balance']);
if (!empty($balance))
{
	$balance = $balance . ' ' . Yii::t('pays', _CURRENCY_);
	echo $balance;
}
