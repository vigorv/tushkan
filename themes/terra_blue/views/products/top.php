			<a href="#"><div class="left-button"></div></a>
			<a href="#"><div class="right-button"></div></a>
<?php
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
					<img class="small-poster" src="' . $poster . '" alt="' . htmlentities($info['title'], ENT_QUOTES, 'UTF-8') . '" title="' . htmlentities($info['title'], ENT_QUOTES, 'UTF-8') . '">
					<a class="top-film" href="/products/view/' . $info['id'] . '">' . $info['title'] . '</a>
				</div>
		';
    }
}
?>
			<div class="pages">
				<a class="item" href="#"></a>
				<a class="item" href="#"></a>
				<a class="item-active" href="#"></a>
				<a class="item" href="#"></a>
				<a class="item" href="#"></a>
			</div>
