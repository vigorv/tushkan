			<div class="span10 no-horizontal-margin">
<?php
/*
ОРИГИНАЛЬНАЯ ВЕРСТКА
				<div class="span2 margin-left-only">
					<a class="device ipad" href="#">Мой новый iPad 3</a>
				</div>
				<div class="span2 margin-left-only">
					<a class="device ipod-shuffle" href="#">iPod сестры</a>
				</div>
				<div class="span2 margin-left-only">
					<a class="device iphone" href="#">Папина мобилка</a>
				</div>
*/
	foreach ($dst as $d)
	{
	    $title = $d["title"];
	    if (empty($title))
			$title = Yii::t('devices', 'My device');
    ?>
				<div class="span2 margin-left-only">
					<a class="device ipad" href="#" rel="<?php echo $d['id']; ?>"><?php echo $title; ?></a>
				</div>

<?php
	}
?>
			</div>
			<div class="span2 margin-left-only">
				<div class="span2 margin-left-only">
					<a id="linkdevice" class="device new-device" href="#">Добавить новое устройство</a>
				</div>
			</div>
<script type="text/javascript">
    $( ".device" ).click(function(){
    	id = $(this).attr('rel');
    	if (id != null)
    	{
    		$('#m_devices').load('/devices/view/' + id);
    	}
    	return false;
    });

    $( "#linkdevice" )
    .button()
    .click(function() {
		$('#m_devices').load('/devices/select');
		return false;
    });
</script>
