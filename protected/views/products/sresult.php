<?php
$productLimit = 7;
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
	echo '<div class="chess">
				<img width="80px" height="120" src="' . $poster . '" />
				    <div style="clear:left">
				<a href="/products/view/' . $info['id'] . '">' . $info['title'] . '</a>

				    </div>
			</div>';
    }
}