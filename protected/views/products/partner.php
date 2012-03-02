<h2><?php echo Yii::t('common', 'Products'); ?></h2>
<?php
	if (!empty($pst))
	{
		echo '<h3>' . $pst[0]['prttitle'] . '</h3>';
		$curId = 0; $infos = array();
		foreach ($pst as $p)
		{
			if ($p['pvid'] <> $curId)
			{
				$curId = $p['pvid'];
			}
			$infos[$curId]['id'] = $p['id'];
			$infos[$curId]['title'] = $p['ptitle'];
			$infos[$curId][$p['ppvid']] = $p['value'];
		}

		foreach ($infos as $info)
		{
			if (!empty($info['10']))
				$poster = $info['10'];
			else
				$poster = Yii::app()->params['tushkan']['postersURL'] . '/noposter.jpg';
			$prms = array($info['12'], $info['13'], $info['14']);
			echo '<div class="chess">
				<img width="80" align="left" src="' . $poster . '" />
				<a href="/products/view/' . $info['id'] . '">' . $info['title'] . '</a>
				<br />' . implode(', ', $prms) . '
			</div>';
		}
	}
?>