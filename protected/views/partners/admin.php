<div>
<a href="<?php echo $this->createUrl('partners/form');?>"><?php echo Yii::t('partner', 'Add Partner');?></a>
</div>
<?php if ($partner_list): ?>
<table>
    <thead>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
</thead>
<tbody>
    <?php foreach ($partner_list as $p): ?>
        <tr>
            <td><?= $p['id']; ?></td>
            <td><?= $p['title']; ?></td>
            <td><?= $p['active']; ?></td>
        </tr>
    <?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>