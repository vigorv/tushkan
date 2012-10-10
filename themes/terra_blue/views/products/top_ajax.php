<? foreach ($pst as $item): ?><?
	if (empty($item['poster']))
    	$item['poster'] = '/images/films/noposter.jpg';
    else
    	$item['poster'] = Utils::validatePoster($item['poster']);
 ?>
<li>
    <img class="small-poster" src="<?=$item['poster'];?>" alt="<?=$item['ptitle'];?>"
         title="<?=$item['ptitle'];?>">
    <a class="top-film" href="/products/view/<?=$item['id'];?>"
       onClick="return m_goods_carousel_link(this);"><?=$item['ptitle'];?></a>
</li><? endforeach; ?>