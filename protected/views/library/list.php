<ul>
<?php foreach ($objects as $object):?>
    <li><a href="/library/view/<?=$object['id'];?>"><?=$object['title'];?></a></li>
<?php endforeach; ?>
</ul>