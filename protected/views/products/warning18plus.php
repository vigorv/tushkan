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
		$url = ' &nbsp; | &nbsp; <a onclick="return hideWarning();">Далее</a>';
	}
?>
<style>
#d_100{
	position:absolute;
	background: white;
	z-index:1000;
	top:0;
	left:0;
	width: 100%;
	height:100%;
}
#d_cntr{
	margin: 30% auto;
	width: 250px;
}
</style>
<div id="d_100">
	<div id="d_cntr">
	<h4>Предупреждение 18+</h4>
	<p>Просмотр лицам до 18 лет запрещен.</p>

	<a href="/products">Уходим отсюда</a><?php echo $url; ?>
	</div>
</div>