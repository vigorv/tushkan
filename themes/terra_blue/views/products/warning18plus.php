<?php
	if (!empty($url))
	{
?>
<script type="text/javascript">
function hideWarning()
{
	d = document.getElementById("d_100");
	d.style.display = 'none';
	return false;
}
</script>
<?php
		$url = ' &nbsp; &nbsp; &nbsp; <a class="btn" onclick="return hideWarning();">Далее</a>';
	}
?>
<style>
#d_100{
	z-index:10000;
	width: 98%;
	height:98%;
	margin: 0;
	left:1%;
	top:1%;
	overflow: hidden;
}
#d_cntr{
	margin: 30% auto;
	width: 250px;
}
</style>
<div class="modal static" id="d_100">
	<div id="d_cntr">
	<h4>Предупреждение 18+</h4>
	<p>Просмотр лицам до 18 лет запрещен.</p>

	<a class="btn" href="/products">Уходим отсюда</a><?php echo $url; ?>
	</div>
</div>