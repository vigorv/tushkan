<ul>
<?php foreach ($files as $file):?>
    <li><a href="/files/fview/<?=$file['id'];?>"><?=$file['title'];?></a></li>
<?php endforeach; ?>
</ul>