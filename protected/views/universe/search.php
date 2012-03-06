
<h2>Search results...</h2>
<div id="search_result_goods">
    <h3>Goods</h3>
    <?php if (isset($pstContent)): ?>
	<?= $pstContent; ?>
    <?php else: ?>
        <span>Nothing founded in Goods</span>
    <?php endif; ?>
</div>
<div id="search_result_objects">
    <h3>Library</h3>   
    <?php if (isset($objContent)): ?>
	<?= $objContent; ?>
    <?php else: ?>
        <span>Nothing founded in Library</span>
    <?php endif; ?>
</div>
<div id="search_result_unt">
    <h3>Untyped</h3>   
    <?php if (isset($untContent)): ?>
	<?= $untContent; ?>
    <?php else: ?>
        <span>Nothing founded in Untyped</span>
    <?php endif; ?>

</div>



<script langauge="javascript">
    $('a').click(function(){
	cont.load(this.href);
	return false;	 
    });
</script>