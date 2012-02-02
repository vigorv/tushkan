<div>
    <a href="<?php echo $this->createUrl('fileservers/add'); ?>"><?php echo Yii::t('fileservers', 'Add Fileserver'); ?></a>
</div>
<?php if ($file_servers): ?>
    <table  class="tablesorter">
        <thead>
        <th>id</th>
        <th>ip</th>
        <th>alias</th>
        <th>stype</th>
        <th>desc</th>
        <th>active</th>
        <th>zone</th>
        
    </thead>
    <tbody>
        <?php foreach ($file_servers as $f): ?>
            <tr>
                <td><?= $f['id']; ?></td>
                <td><?= long2ip($f['ip']); ?></td>
                <td><?= $f['alias']; ?></td>
                <td><?= $f['stype']; ?></td>
                <td><?= $f['dsc']; ?></td>
                <td><?= $f['active']; ?></td>
                <td><?= $f['zone_id']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    </table>
<?php endif; ?>

