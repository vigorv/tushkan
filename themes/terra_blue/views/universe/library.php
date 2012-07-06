<?php if (!isset($mb_content_items)): ?>

<?php
	$mediaList = Utils::getMediaList();
?>
		<div class="span12 no-horizontal-margin category">
			<div class="span3 margin-left-only">
				<a class="cat-name cat-name-active video" href="<?php echo $mediaList[1]['link']; ?>"><?php echo $mediaList[1]['title'];?></a>
			</div>
			<div class="span3 margin-left-only">
				<a class="cat-name audio" href="<?php echo $mediaList[2]['link']; ?>"><?php echo $mediaList[2]['title'];?></a>
			</div>
			<div class="span3 margin-left-only">
				<a class="cat-name photo" href="<?php echo $mediaList[5]['link']; ?>"><?php echo $mediaList[5]['title'];?></a>
			</div>
			<div class="span3 no-margin">
				<a class="cat-name document" href="<?php echo $mediaList[4]['link']; ?>"><?php echo $mediaList[4]['title'];?></a>
			</div>
		</div>
<?php else: ?>
		<div class="tabbable"> <!-- Only required for left/right tabs -->
			<ul class="nav inside-nav nav-pills inside-nav-pills">
			<?php
				foreach ($mediaList as $ml)
				{
					if ($ml['hidden']) continue;
					$active = '';
					if ($ml['id'] == $type_id)
						$active = 'class="active"';
					echo '<li ' . $active . '><a href="' . $ml['link']. '">' . $ml['title'] . '</a></li>';
				}
			?>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active">
					<div class="span12 no-horizontal-margin my-catalog">
						<?php
							$userProducts = $productsInfo['tFiles'];
							$productParams = $productsInfo['fParams'];
							$pHeader1 ='
								<div class="span12 no-horizontal-margin more-link"><a href="#">' . Yii::t('common', 'From partners') . '</a></div>
								<div id="userproductsdiv" class="span12 no-horizontal-margin type">
							';
							$pHeader2 = '</div>';
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
										echo $pHeader1;
										$pHeader1 = '';

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
								$this->widget('ext.pagination.EPaginationWidget', array('params' => $productsPagination));
							}
							if (empty($pHeader1)) echo $pHeader2;

							if (!empty($qstContent)) {
								?>
						<div class="span12 no-horizontal-margin more-link"><a href="#"><?php echo Yii::t('common', 'Processing');?></a></div>
						<div class="span12 no-horizontal-margin type">
								<?php
								echo $qstContent;
								?>
						</div>
								<?php
							}
							if (!empty($mb_content_items)) {
								?>
						<div class="span12 no-horizontal-margin more-link"><a href="#"><?php echo Yii::t('common', 'Typed');?></a></div>
						<div class="span12 no-horizontal-margin type">

							<ul class="nav inside-nav nav-pills">
							<?php
								echo CFiletypes::ParsePrint($mb_content_items, 'TL1');
							?>
							</ul>
						</div>
						<?php
							}

							if (!empty($mb_content_items_unt)) {
							?>
						<div class="span12 no-horizontal-margin more-link"><a href="#"><?php echo Yii::t('common', 'Untyped');?><?php //(echo Yii::t('common', 'Garbage') );?></a></div>
						<div class="span12 no-horizontal-margin type">
							<ul class="nav inside-nav nav-pills ">
								<?= CFiletypes::ParsePrint($mb_content_items_unt, 'UTL1'); ?>
							</ul>
						</div>
						<?php
							}
						?>
					</div>
				</div>
			</div>
		</div>
<?php endif; ?>