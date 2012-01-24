<h2><?php echo Yii::t('orders', 'Orders'); ?></h2>
<?php
if (!empty($lst))
{
	echo '<ul>';
	foreach ($lst as $l)
		echo'<li><a href="/orders/view/' . $l['id'] . '">' . Yii::t('orders', 'Order') . ' №' . $l['id']. '</a> от ' . date('Y-m-d', strtotime($l['created'])) . ' (состояние: ' . $l["state"] .  '), товаров - ' . $l['icnt'] . '</li>';
	echo '</ul>';
}
else
	echo Yii::t('orders', 'Orders not found');