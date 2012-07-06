<?php
	if (!empty($productsInfo['tFiles']))
		$userProducts = $productsInfo['tFiles'];
	if (!empty($productsInfo['fParams']))
		$productParams = $productsInfo['fParams'];
	if (!empty($userProducts) && count($userProducts)) {
		foreach ($userProducts as $f) {
			$curVariantId = $f['variant_id'];
			$params = array();
			foreach ($productParams as $p) {
				if ($p['id'] == $curVariantId) {
					$params[$p['title']] = $p['value'];
				}
			}

			if (!empty($params)) {
				echo '<div class="chess"><a href="/universe/tview/' . $f['id'] . '">';
				if (!empty($params['poster'])) {
					$poster = $params['poster'];
					unset($params['poster']);
				} else {
					$poster = '/images/films/noposter.jpg';
				}
				echo '<img align="left" width="80" height="120" src="' . $poster . '" />';
				echo '<b>' . $f['title'] . '</b>';
				echo '</a></div>';
			}
		}
	}
	$this->widget('ext.pagination.EPaginationWidget', array('params' => $paginationParams));
