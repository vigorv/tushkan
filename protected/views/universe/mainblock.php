<div id="mainblock">
    <div class="mb_top">
	<ul>
	    <?php foreach ($mb_top_items as $mb_top_item): ?>
    	    <li><a href="<?=$mb_top_item['link']; ?>"><?=$mb_top_item['caption']; ?></a></li>
	    <?php endforeach; ?>
	</ul>
    </div>
    <div class="mb_content">
	<div class="top_menu">
	    <a href="Back">Back</a>
	    <h4><?= $title; ?></h4>
	</div>
	<div class="filters">   

	</div>
	<div class="items">
	    <ul>
		<?php foreach ($mb_content_items as $mb_content_item): ?>
    		<li><?= CFiletypes::ParsePrint($mb_content_item, 'FL1'); ?></li>
		<? endforeach; ?>
	    </ul>
	</div>
	<div class="ext">
	    <div class="items_unt">

	    </div>
	    <div class="items_add">

	    </div>
	</div>
    </div>
</div>