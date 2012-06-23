<div class="span12 no-horizontal-margin type">
<?php
	foreach ($lst as $l)
	{
		echo '
			<div class="span3 margin-left-only">
				<a class="type-name ' . $l['alias'] . '" rel="' . $l['id'] . '" href="#">' . $l['title'] . '</a>
			</div>
		';
	}
/*
ОРИГИНАЛЬНАЯ ВЕРСТКА
			<div class="span3 margin-left-only">
				<a class="type-name mobile" href="#">Мобильный телефон</a>
			</div>
			<div class="span3 margin-left-only">
				<a class="type-name type-name-active player" href="#">Плеер/iPod</a>
			</div>
			<div class="span3 no-margin">
				<a class="type-name tv" href="#">Телевизор</a>
			</div>
*/
?>
</div>
<script type="text/javascript">
    $( ".type a" ).click(function() {
	$.post('/devices/add/' + $(this).attr('rel'), function(data){
		data = parseInt(data);
	    if (!data)
	    {
	    	data = 'err';
			$('#m_devices').load('/universe/devices/' + data);
	    }
	    else
			$('#m_devices').load('/devices/view/' + data);
	});
	return false;
    });
</script>