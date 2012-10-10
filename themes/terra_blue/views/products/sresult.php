<?php
/*
$productLimit = 5;
if (!empty($pst)) {
    $curId = 0;
    $infos = array();
   foreach ($pst as $p) {
	if ($p['pvid'] <> $curId) {
	    $curId = $p['pvid'];
	    if ($productLimit-- < 0)
	    	break;
	}
	$infos[$curId]['id'] = $p['id'];
	$infos[$curId]['title'] = $p['ptitle'];
	$infos[$curId][$p['ppvid']] = $p['value'];
	if (!empty($p['prtid'])) {
	    $infos[$curId]['partner'] = '<i><a href="/products/partner/' . $p['prtid'] . '">' . $p['prttitle'] . '</a></i>';
	}
	else
	    $infos[$curId]['partner'] = '';
    }

    foreach ($infos as $info) {
		if (!empty($info['10']))
		    $poster = $info['10'];
		else
		    $poster = Yii::app()->params['tushkan']['postersURL'] . '/noposter.jpg';
		$prms = array($info['12'], $info['13'], $info['14']);
		echo '
				<div class="span2 margin-left-only">
					<img class="small-poster" src="' . $poster . '" alt="' . htmlentities($info['title'], ENT_QUOTES) . '" title="' . htmlentities($info['title'], ENT_QUOTES) . '">
					<a class="top-film" href="/products/view/' . $info['id'] . '">' . $info['title'] . '</a>
				</div>
		';
    }
}*/
?>
<? foreach ($pst as $item):?>
<?
    if (empty($item['poster']))
        $item['poster'] = '/images/films/noposter.jpg';
    else
    	$item['poster'] = Utils::validatePoster($item['poster']);
?>


<div class="chess">
    <a  href="/products/view/<?=$item['id'];?>">
    <img align="left" width="80" height="120" src="<?=$item['poster'];?>" alt="<?=$item['ptitle'];?>" title="<?=$item['ptitle'];?>" />
    <b><?=$item['ptitle'];?></b></a>
</div>
<? endforeach;?>