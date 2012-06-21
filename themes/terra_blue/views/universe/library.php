<?php if (!isset($mb_content_items)): ?>
		<div class="span12 no-horizontal-margin category">
			<div class="span3 margin-left-only">
				<a class="cat-name cat-name-active video" href="/universe/library?lib=v"><?php echo Yii::t('users','Video');?></a>
			</div>
			<div class="span3 margin-left-only">
				<a class="cat-name audio" href="/universe/library?lib=a"><?php echo Yii::t('users','Audio');?></a>
			</div>
			<div class="span3 margin-left-only">
				<a class="cat-name photo" href="/universe/library?lib=p"><?php echo Yii::t('users','Photo');?></a>
			</div>
			<div class="span3 no-margin">
				<a class="cat-name document" href="/universe/library?lib=d"><?php echo Yii::t('users','Documents');?></a>
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
					echo '<li ' . $active . '><a href="' . $ml['link']. '" data-toggle="tab">' . $ml['title'] . '</a></li>';
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
								<div class="span12 no-horizontal-margin more-link"><a href="#">Добавлено с витрин</a></div>
								<div class="span12 no-horizontal-margin type">
							';
							$pHeader2 = '</div>';
							if (!empty($userProducts) && count($userProducts)) {
								?>
								<?php
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
							}
							if (empty($pHeader1)) echo $pHeader2;

							if (!empty($qstContent)) {
								?>
						<div class="span12 no-horizontal-margin more-link"><a href="#">В процессе добавления</a></div>
						<div class="span12 no-horizontal-margin type">
								<?php
								echo $qstContent;
								?>
						</div>
								<?php
							}
							if (!empty($mb_content_items)) {
								?>
						<div class="span12 no-horizontal-margin more-link"><a href="#">Objects</a></div>
						<div class="span12 no-horizontal-margin type">

							<ul>
							<?php
								echo CFiletypes::ParsePrint($mb_content_items, 'TL1');
							?>
							</ul>
						</div>
						<?php
							}

							if (!empty($mb_content_items_unt)) {
							?>
						<div class="span12 no-horizontal-margin more-link"><a href="#">UntypedItems(Свалка)</a></div>
						<div class="span12 no-horizontal-margin type">
							<ul>
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

		<div class="span12 no-horizontal-margin type">
			<div class="span3 margin-left-only">
				<a class="type-name netbook" href="#">Нетбук/планшет</a>
			</div>
			<div class="span3 margin-left-only">
				<a class="type-name mobile" href="#">Мобильный телефон</a>
			</div>
			<div class="span3 margin-left-only">
				<a class="type-name type-name-active player" href="#">Плеер/iPod</a>
			</div>
			<div class="span3 no-margin">
				<a class="type-name tv" href="#">Телевизор</a>
			</div>
		</div>
