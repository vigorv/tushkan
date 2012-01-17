<div>
    <a href="<?php echo $this->createUrl('fileservers/form'); ?>"><?php echo Yii::t('fileservers', 'Add Fileserver'); ?></a>
</div>
<?php if (!empty($FileServers)): ?>
    <table>
        <thead>
        <th></th>
        </thead>
        <tbody>
            <?php foreach ($FileServers as $f): ?>


            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

