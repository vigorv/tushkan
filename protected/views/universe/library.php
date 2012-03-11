<?php
$uploadServer = CServers::model()->getServer(UPLOAD_SERVER);
$user_id = Yii::app()->user->id;

$nav_active=array(
	'v'=>array('v'=>'class="active"'),
	'a'=>array('a'=>'class="active"'),
	'p'=>array('p'=>'class="active"'),
	'd'=>array('d'=>'class="active"'),
);


?>
<?php if (!isset($mb_content_items)): ?>
	<?php $path_img = '/images/128x128/'; ?>
	<ul id ="lib_menu">
		<li>	
			<a href="/universe/library?lib=v">
				<img src="<?= $path_img; ?>/filesystems/video.png"/>
				<?=Yii::t('users','Video');?></a>
		</li>
		<li>
			<a href="/universe/library?lib=a">
				<img src="<?= $path_img; ?>/filesystems/music.png"/>
				<?=Yii::t('users','Audio');?></a>
		</li>
		<li>

			<a href="/universe/library?lib=p">
				<img src="<?= $path_img; ?>/apps/package_graphics.png"/>
				<?=Yii::t('users','Photo');?></a>
		</li>
		<li>	
			<a href="/universe/library?lib=d">
				<img src="<?= $path_img; ?>/apps/kwrite.png"/>
						<?=Yii::t('users','Documents');?></a>
		</li>
	</ul>
<?php else: ?>
	<?php $path_img = '/images/16x16/'; ?>
	<ul id="content_menu" class="nav nav-tabs">
		<li <?=@$nav_active['v'][$nav_lib];?>>	
			<a href="/universe/library?lib=v"><img src="<?= $path_img; ?>/filesystems/video.png"/>
			<?=Yii::t('users','Video');?></a>
		</li>
		<li <?=@$nav_active['a'][$nav_lib];?>>
			<a href="/universe/library?lib=a"><img src="<?= $path_img; ?>/filesystems/music.png"/>
				<?=Yii::t('users','Audio');?></a>
		</li>
		<li <?=@$nav_active['p'][$nav_lib];?>>

			<a href="/universe/library?lib=p"><img src="<?= $path_img; ?>/apps/package_graphics.png"/>
			<?=Yii::t('users','Photo');?></a>
		</li >	
		<li <?=@$nav_active['d'][$nav_lib];?>>	
			<a href="/universe/library?lib=d"><img src="<?= $path_img; ?>/apps/kwrite.png"/>
				<?=Yii::t('users','Documents');?></a>
		</li>
	</ul>
	<?php if (isset($mb_top_items) && count($mb_top_items>0)): ?>
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
			if (!empty($userProducts) && count($userProducts)) {
				echo '<h4>Добавленное с витрин</h4>';
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
			<h4>Objects</h4>
			<ul>
				<?= CFiletypes::ParsePrint($mb_content_items, 'TL1'); ?>
			</ul>
		</div>
		<div class="clearfix"></div>
		<div id="items_unt" class="well">
			<h4>UntypedItems(Свалка)</h4>
			<ul>
				<?= CFiletypes::ParsePrint($mb_content_items_unt, 'UTL1'); ?>
			</ul>
		</div>

	</div>
<script>
	<?php /*
	<!--
  $('#content_menu a').click(function(){
	this_p = $(this).parent();
	if ($(this_p).hasClass('active')) return false;
	main_p= this_p.parent();
	main_p.children('li.active').removeClass('active');
	this_p.addClass('active');
	return false;	
  });->>*/?>
  </script>
<?php endif; ?>