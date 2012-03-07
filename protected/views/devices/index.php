<?php
	echo '<h4>' . Yii::t('common', 'Devices'). '</h4>';
	foreach($dst as $d)
	{
		$title = $d["title"];
		if (empty($title))
			$title = 'Мое устройство';
		echo '<p><a>' . $title . '</a> (' . $tst[$d["device_type_id"]]['title'] . ')
		<button class="btn syncdevice" rel="' . $d['id'] . '">' . Yii::t('devices', 'synchronize') . '</button>
		<button class="btn unlinkdevice" rel="' . $d['id'] . '">' . Yii::t('devices', 'unlink') . '</button>
		</p>';
	}
	echo'<br /><div class="divider"></div><br />
	<button class="btn" onclick="return switchTypes();">' . Yii::t('devices', 'link') . '</button></center></div>
	<div id="deviceTypeDiv" style="display:none">';
	echo'<h5>' . Yii::t('common', 'Device types'). '</h5>';
	foreach($tst as $t)
	{
		echo '<div class="glue"><center>' . $t['title'] . '<br /><button class="btn linkdevice" rel="' . $t['id'] . '">' . Yii::t('devices', 'link') . '</button></center></div>';
	}
	echo'<div class="divider"></div>
	</div>
	';
?>
<script type="text/javascript">
	function switchTypes()
	{
		$("#deviceTypeDiv").toggle();
	}

	$( ".syncdevice" )
		.click(function() {
	});

	$( ".unlinkdevice" )
		.click(function() {
			if (confirm('<?php echo Yii::t('common', 'Are you sure?');?>'))
			{
				$.post('/devices/remove/' + $(this).attr('rel'), function(data){
					if (data == 'ok')
						$('#m_devices').load('/universe/devices');
				});
			}
	});
	$( ".linkdevice" )
		.click(function() {
			$.post('/devices/add/' + $(this).attr('rel'), function(data){
				if (data == 'ok')
					$('#m_devices').load('/universe/devices');
			});
	});
</script>