<?php
	if (!empty($pst))
	{
		$curId = 0; $infos = array();
		foreach ($pst as $p)
		{
			$variantExists = false;
			foreach ($infos as $k => $v)
			{
				if (($k <> $p['pvid']) && ($v['id'] == $p['id']))
				{
					$variantExists = true;
					break;
				}

			}
			if ($variantExists)
			{
				continue;//ОГРАНИЧИВАЕМ ВЫВОД ОДНИМ ВАРАНТОМ НА ПРОДУКТ
			}

			if ($p['pvid'] <> $curId)
			{
				$curId = $p['pvid'];
			}
			$infos[$curId]['id'] = $p['id'];
			$infos[$curId]['title'] = $p['ptitle'];
			$infos[$curId][$p['ppvid']] = $p['value'];
			if (!empty($p['prtid']))
			{
				$infos[$curId]['partner'] = '<i><a href="/products/partner/' . $p['prtid'] . '">' . $p['prttitle'] . '</a></i>';
			}
			else
				$infos[$curId]['partner'] = '';
		}

		foreach ($infos as $info)
		{
			if (!empty($info['10']))
				$poster = Utils::validatePoster($info['10']);
			else
				$poster = '/images/films/noposter.jpg';

			$prms = array();
			if (!empty($info['12']))
				$prms[] = $info['12'];
			if (!empty($info['13']))
				$prms[] = $info['13'];
			if (!empty($info['14']))
				$prms[] = $info['14'];
			echo '<div class="chess">
				<img width="80" height="120" align="left" src="' . $poster . '" />
				' . $info['partner'] . '
				<a href="/products/view/' . $info['id'] . '">' . $info['title'] . '</a>
				<br />' . implode(', ', $prms) . '
			</div>';
		}
	}
