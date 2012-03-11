
<h2>Search results...</h2>
<div id="search_result_goods" class="well clearblockfix">
    <h3>Goods</h3>
	<?php if (isset($pstContent)): ?>
		<?= $pstContent; ?>
	<?php else: ?>
		<span>Nothing founded in Goods</span>
	<?php endif; ?>
</div>
<div id="items_t" class="well">
    <h3>Library</h3>   
	<?php if (isset($obj)): ?>
		<ul>
			<?= CFiletypes::ParsePrint($obj, 'TL1'); ?>
		</ul>
	<?php else: ?>
		<span>Nothing founded in Library</span>
	<?php endif; ?>
</div>
<div id="items_unt" class="well">
    <h3>Untyped</h3>   
	<?php if (isset($unt)): ?>
		<ul>
			<?= CFiletypes::ParsePrint($unt, 'UTL1'); ?>
		</ul>
	<?php else: ?>
		<span>Nothing founded in Untyped</span>
	<?php endif; ?>

</div>

