<?php
$uploadServer = CServers::model()->getServer(UPLOAD_SERVER);
$user_id = Yii::app()->user->id;
Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/multiuploader.js");
?>
<?php if (!isset($mb_content_items)): ?>
	<ul id ="lib_menu">
		<li><a href="/universe/library?lib=v">Video</a></li>
		<li><a href="/universe/library?lib=a">Audio</a></li>
		<li><a href="/universe/library?lib=p">Photo</a></li>
		<li><a href="/universe/library?lib=d">Docs</a></li>
	</ul>
<?php else: ?>
	<?php if (isset($mb_top_items)): ?>
		<div class="lib_top well">
			<ul>
				<?php foreach ($mb_top_items as $mb_top_item): ?>
					<li><a href="<?= $mb_top_item['link']; ?>"><?= $mb_top_item['caption']; ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
	<div class="lib_content ">
		<div class="top_menu well">			
			<?php
			$userProducts = $productsInfo['tFiles'];
			$productParams = $productsInfo['fParams'];
			if (!empty($userProducts)) {
				echo '<h4>Видео с витрин</h4>';
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
						echo '<img align="left" width="80" src="' . $poster . '" />';
						echo '<b>' . $f['title'] . '</b>';
						echo '</a></div>';
					}
				}
				echo '<div class="divider"></div>';
			}
			?>
		</div>
		<div class="filters">

		</div>
		<div id="items_t" class="well">
			<h4>TypedItems</h4>
			<ul>
				<?= CFiletypes::ParsePrint($mb_content_items, 'TL1'); ?>
			</ul>
		</div>
		<div class="clearfix"></div>
		<div id="items_unt" class="well">
			<h4>UntypedItems</h4>
			<ul>
				<?= CFiletypes::ParsePrint($mb_content_items_unt, 'UTL1'); ?>
			</ul>
		</div>

	</div>
<?php endif; ?>