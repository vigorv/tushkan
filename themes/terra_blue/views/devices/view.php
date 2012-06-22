<?php
	if (empty($info))
	{
?>
		<div class="span12 no-horizontal-margin type">
		<?php echo Yii::t('common', 'Nothing was found'); ?>
		</div>
<?php
	}
	else
	{
	$lst = CDevices::getDeviceTypes();
?>
<div class="span12 no-horizontal-margin type">
	<div class="span3 margin-left-only">
		<a class="type-name <?php echo $lst[$info["device_type_id"]]['alias']; ?>" nohref></a>
	</div>
	<p>
    <?php
	    $title = $info["title"];
	    if (empty($title))
			$title = Yii::t('devices', 'My device');

    	echo $title;
    ?> (<?php echo $lst[$info["device_type_id"]]['title']; ?>)
    </p>
    <p>
    <a class="btn" id="syncdevice" rel="<?php echo $info['id']; ?>"><?php echo Yii::t('devices', 'synchronize'); ?></a>
    <a class="btn" id="unlinkdevice" rel="<?php echo $info['id']; ?>"><?php echo Yii::t('devices', 'unlink'); ?></a>
    </p>
    <p>
    <a class="btn" id="devicelist"><?php echo Yii::t('common', 'Back to list'); ?></a>
	</p>
<script type="text/javascript">
    $( "#syncdevice" ).click(function() {
    });

    $( "#devicelist" ).click(function() {
    	$('#m_devices').load('/universe/devices');
    });

    $( "#unlinkdevice" ).click(function() {
	if (confirm('<?php echo Yii::t('common', 'Are you sure?'); ?>'))
	{
	    $.post('/devices/remove/' + $(this).attr('rel'), function(data){
		if (data == 'ok')
		    $('#m_devices').load('/universe/devices');
	    });
	}
    });
</script>
<?php
	}