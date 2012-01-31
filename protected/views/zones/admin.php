<div>
    <a href="<?php echo $this->createUrl('fileservers/add'); ?>"><?php echo Yii::t('fileservers', 'Add Fileserver'); ?></a>
</div>
<?php if ($zones): ?>
    <table  class="tablesorter">
        <thead>
        <th>ID</th>
        <th>IP</th>
        <th>MASK</th>
        <th>ACTIVE</th>
    </thead>
    <tbody>
        <?php foreach ($zones as $f): ?>
            <tr>
                <td><?= $f['id']; ?></td>
                <td><?= long2ip($f['ip']); ?></td>
                <td><?= $f['mask']; ?></td>
                <td><?= $f['active']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    </table>
<?php endif; ?>

