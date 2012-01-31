<div>
    <a href="<?php echo $this->createUrl('films/form'); ?>"><?php echo Yii::t('films', 'Add Film'); ?></a>
    <a href="<?php echo $this->createUrl('/films');?>"><?php echo Yii::t('films', 'Upload FilmList');?></a>
</div>
<?php if (!empty($films)): ?>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Year</th>
                <th>Country</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($films as $f): ?>
            <tr>
                <?php
                $href = Yii::app()->createUrl('/films/edit/' . $f['id']);
                //'<div class="shortfilm"><a href="' . $href . '">';
                if (!empty($f['filename']))
                    $poster = Yii::app()->params['tushkan']['postersURL'] . '/smallposter/' . $f['filename'];
                else
                    $poster = Yii::app()->params['tushkan']['postersURL'] . '/noposter.jpg';
                // '<img src="' . $poster . '" />';
                //echo $f['title'] . '</a>, ' . $f['y'] . ', ' . $f['country'];
                ?>
                <td><a href="<?=$href;?>"><?=$f['title'];?></a></td>
                <td><?=$f['y'];?></td>
                <td><?=$f['country'];?></td>
                <td><?=$poster;?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php
$this->widget('CLinkPager', array(
    'pages' => $pages,
))
?>