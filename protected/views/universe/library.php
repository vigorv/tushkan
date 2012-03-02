<?php if (!isset($mb_content_items)): ?>
    <ul>
        <li><a href="/universe/library?lib=v">Video</a></li>
        <li><a href="">Audio</a></li>
        <li><a href="">Photo</a></li>
        <li><a href="">Docs</a></li>
    </ul>
<?php else: ?>
    <?php if (isset($mb_top_items)): ?>
	<div class="lib_top">
	    <ul>
		<?php foreach ($mb_top_items as $mb_top_item): ?>
	    	<li><a href="<?= $mb_top_item['link']; ?>"><?= $mb_top_item['caption']; ?></a></li>
		<?php endforeach; ?>
	    </ul>
	</div>
    <?php endif; ?>
    <div class="lib_content">
        <div class="top_menu">
    	<a href="Back">Back</a>
    	<h4></h4>
        </div>
        <div class="filters">   

        </div>
        <div class="items">
	   TypedItems
    	<ul>	
		<?= CFiletypes::ParsePrint($mb_content_items, 'TL1'); ?>
    	</ul>
        </div>
        <div class="ext">
    	<div class="items_unt">
	    UntypedItems
    	    <ul>
		    <?= CFiletypes::ParsePrint($mb_content_items_unt, 'UTL1'); ?>
    	    </ul>
    	</div>
    	<div class="items_add">
	    
    	</div>
        </div>
    </div>
<?php endif; ?>