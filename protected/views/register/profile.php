	<script type="text/javascript">
		function setTariff()
		{
			$.post("/register/tariff", { tariff_id: $("#tariffSelectId").val()}, function(data){
				if(data == 'ok');
				{
					location.reload();
				}
			});
			return false;
		}
	</script>
<?php

echo '<h2>Hi, <a href="/register/personal">' . Yii::app()->user->name . '</a>!</h2>';

$upBalance = ' <a href="/pays/do/1">' . Yii::t('users', 'Fill up balance') . '</a>';
$currency = Yii::t('pays', _CURRENCY_);
if (!empty($balance))
{
	echo '<h3>Ваш баланс: ' . $balance['balance'] . ' ' . $currency . $upBalance . '</h3>';
}
else
{
	echo '<h3>У вас на балансе нет средств' . $upBalance . '</h3>';
}

$tLst = array();
if (!empty($tariffs))
{
	foreach($tariffs as $t)
		$tLst[$t['id']] = $t['title'];
}
if (!empty($tariff))
{
	echo '<h3>Ваш тариф: ' . $tariff['title'] . ' (период: ' . Utils::spellPeriod($tariff['period']) . ', стоимость: ' . $tariff['price'] . ' ' . $currency . ')</h3>';

	if (!empty($tLst))
	{
		echo'<p>Сменить тариф: ' .
			CHtml::dropDownList('tariff_id', '', $tLst, array('id' => 'tariffSelectId')) .
			CHtml::button(Yii::t('common', 'Choose'), array('onclick' => 'return setTariff();')). '</p>';
	}
	if (!empty($newTariff))
	{
		echo '<p>Вы собираетесь сменить тариф на "' . $newTariff['title'] . '" (' . $newTariff['price'] . ' ' . $currency . ')</p>';
	}

	echo '<h3>Ваше пространство ' . Utils::sizeFormat($tariff['size_limit'] * _MB_) . ' , свободно ' . Utils::sizeFormat($info['free_limit'] * _MB_) . '</h3>';
}
else
{
	echo '<h3>Тариф не выбран ' . CHtml::dropDownList('tariff_id', '', $tLst, array('id' => 'tariffSelectId')) . ' ' . CHtml::button(Yii::t('common', 'Choose'), array('onclick' => 'return setTariff();')). '</h3>';
}

if (!empty($subscribes))
{
	$hd = '<h3>Подключенные услуги</h3><ul>';
	foreach ($subscribes as $s)
	{
		if ($s['paid_by'] > date('Y-m-d H:i:s') || !empty($s['period']))
		{
			echo $hd; $hd = 0;
			if ($s['paid_by'] > date('Y-m-d H:i:s'))
				$paidByStr = 'оплачено по';
			else
				$paidByStr = 'неоплачен с';

			echo '<li>' . $s['ttitle'] . ' (' . $s['botitle'] . ') ' . $paidByStr . ' ' . $s['paid_by'] . '</li>';
		}
	}
	echo '</ul>';
}

$bans = Yii::app()->user->getState('dmUserBans');
if (!empty($bans))
{
	echo '<h3>' . Yii::t('users', 'Account bans') . '</h3><ul>';
	foreach ($bans as $b)
	{
		$period = '';
		$start = strtotime($b["start"]);
		if (!empty($start))
			$period .= Yii::t('common', 'from') . ' ' . $b["start"] . ' ';
		$finish = strtotime($b["finish"]);
		if (!empty($finish))
			$period .= Yii::t('common', 'to') . ' ' . $b["finish"];
		switch ($b['state'])
		{
			case _BANSTATE_READONLY_:
				$state = Yii::t('users', 'Account in readonly mode');
			break;
			case _BANSTATE_FULL_:
				$state = Yii::t('users', 'Account banned');
			break;
			default:
				$state = '';
		}
		switch ($b['reason'])
		{
			case _BANREASON_ABONENTFEE_:
				$reason = Yii::t('users', 'Overdue abonent fee');
			break;
			case _BANREASON_VIOLATION_:
				$reason = Yii::t('users', 'User violation');
			break;
			default:
				$reason = '';
		}
		echo '<li>' . $period . ' ' . $reason . ' ' . $state . '</li>';
	}
	echo '</ul>';
}