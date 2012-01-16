<div>
    <a href="<?php echo $this->createUrl('users/form'); ?>"><?php echo Yii::t('users', 'Add User'); ?></a>
</div>
<?php if (!empty($users)): ?>
    <table id="UsersTable" class="tablesorter">
        <thead>
            <tr>
                <th><?= Yii::t('common', 'action'); ?></th>
                <th>id</th>
                <th><?= Yii::t('users', 'name'); ?></th>
                <th>UserGroup</th>
                <th>email</th>
                <th><?= Yii::t('users', 'registered date'); ?></th>
                <th><?= Yii::t('users', 'last visit'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <?php
                    ($u['gtitle']=='') ? $g = 'Unknown group' : $g =  $u['gtitle'] ;
                    $href = Yii::app()->createUrl('/users/edit/' . $u['id']);
                    if (empty($u['active']))
                        $action = '<a href="' . Yii::app()->createUrl('/users/restore/' . $u['id']) . '">' . Yii::t('common', 'restore') . '</a>';
                    else
                        $action = '<a href="' . Yii::app()->createUrl('/users/delete/' . $u['id']) . '">' . Yii::t('common', 'delete') . '</a>';
                    ?>
                    <td><input type="checkbox" name="massIds[<?= $u['id']; ?>]" />
                        <?= $action; ?>
                    </td>
                    <td><?= $u['id']; ?></td>
                    <td><a href="<?= $href; ?>"><?= $u['name']; ?></a></td>
                    <td><?= $g; ?></td>
                    <td><?= $u['email']; ?></td>
                    <td><?= date('Y-m-d', strtotime($u['created'])); ?> </td>
                    <td><?= date('Y-m-d', strtotime($u['lastvisit'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?
    $this->widget('CLinkPager', array(
        'pages' => $pages,
    ))
    ?>

<?php endif; ?>
<script>
    $('#UsersTable')
    .tablesorter({widthFixed:true, widgets:['zebra']});     
</script>