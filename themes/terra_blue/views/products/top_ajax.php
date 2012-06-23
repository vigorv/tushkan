<? foreach ($pst as $item): ?><? if (empty($item['poster']))
    $item['poster'] = Yii::app()->params['tushkan']['postersURL'] . '/noposter.jpg';   ?>
<li>
    <img class="small-poster" src="<?=$item['poster'];?>" alt="<?=$item['ptitle'];?>"
         title="<?=$item['ptitle'];?>">
    <a class="top-film" href="/products/view/<?=$item['id'];?>"
       onClick="return m_goods_carousel_link(this);"><?=$item['ptitle'];?></a>
</li><? endforeach; ?>