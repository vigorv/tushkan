<?php
	echo '<h4>' . Yii::t('common', 'Devices'). '</h4>';
	foreach($dst as $d)
	{
		$title = $d["title"];
		if (empty($title))
			$title = 'Мое устройство';
		echo '<div class="chess"><center><a>' . $title . '</a> (' . $tst[$d["device_type_id"]]['title'] . ')<br /><a class="unlinkdevice" rel="' . $d['id'] . '">' . Yii::t('devices', 'unlink') . '</a></center></div>';
	}
	echo'<br /><div class="divider"></div><br />';
	echo'<h5>' . Yii::t('common', 'Device types'). '</h5>';
	foreach($tst as $t)
	{
		echo '<div class="chess"><center>' . $t['title'] . '<br /><a class="linkdevice" rel="' . $t['id'] . '">' . Yii::t('devices', 'link') . '</a></center></div>';
	}
	echo'<div class="divider"></div>';
?>
<script type="text/javascript">
	$( ".unlinkdevice" )
		.button()
		.click(function() {
			if (confirm('<?php echo Yii::t('common', 'Are you sure?');?>'))
			{
				$.post('/devices/remove/' + $(this).attr('rel'), function(data){
					if (data == 'ok')
						$('#device_content').load('/devices/index');
				});
			}
	});
	$( ".linkdevice" )
		.button()
		.click(function() {
			$.post('/devices/add/' + $(this).attr('rel'), function(data){
				if (data == 'ok')
					$('#device_content').load('/devices/index');
			});
	});
</script>