<div>
    <a href="<?php echo $this->createUrl('zones/add'); ?>"><?php echo Yii::t('Zones', 'Add Zone'); ?></a>
</div>


<table  class="tablesorter">
    <thead>
    <th>Zone_ID</th>
    <th>IP</th>
    <th>MASK</th>
    <th>ACTIVE</th>
</thead>
<tbody>
    <?php if ($zones): ?>
	<?php foreach ($zones as $f): ?>
	    <tr>
		<td><?= $f['id']; ?></td>
		<td><?= long2ip($f['ip']); ?></td>
		<td><?= $f['mask']; ?></td>
		<td><?= $f['active']; ?></td>
	    </tr>
	<?php endforeach; ?>
    <?php endif; ?>
</tbody>
</table>


