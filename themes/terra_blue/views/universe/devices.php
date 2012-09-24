			<div class="span10 no-horizontal-margin">
<?php
//*
//ОРИГИНАЛЬНАЯ ВЕРСТКА
	echo '
				<div class="span2 margin-left-only">
					<a class="device ipad" href="/pages/3/#devices">iPad 3</a>
				</div>
				<div class="span2 margin-left-only">
					<a class="device ipod-shuffle" href="/pages/3/#devices">iPod</a>
				</div>
				<div class="span2 margin-left-only">
					<a class="device iphone" href="/pages/3/#devices">Мобильный</a>
				</div>
	';
//*/
/*
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
в блон JS
    $( ".device" ).click(function(){
    	id = $(this).attr('rel');
    	if (id != null)
    	{
    		$('#m_devices').load('/devices/view/' + id);
    	}
    	return false;
    });
в блон JS

//*/
?>
			</div>
<?php
/*
			<div class="span2 margin-left-only">
				<div class="span2 margin-left-only">
					<a id="linkdevice" class="device new-device" href="#">Добавить новое устройство</a>
				</div>
			</div>
*/
?>
<script type="text/javascript">

    $( "#linkdevice" )
    .button()
    .click(function() {
		$('#m_devices').load('/devices/select');
		return false;
    });
</script>
