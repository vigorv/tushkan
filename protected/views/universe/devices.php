<h4><?= Yii::t('common', 'Devices'); ?></h4>

<?php foreach ($dst as $d) : ?>
    <?
    $title = $d["title"];
    if (empty($title))
	$title = 'Мое устройство';
    ?>
    <p><img src="#"  height="40px" width="30px"/><br/>
        <a><?= $title; ?></a> (<?= $tst[$d["device_type_id"]]['title']; ?>)
        <a class="syncdevice" rel="' . $d['id'] . '"><?= Yii::t('devices', 'synchronize'); ?></a>
        <a class="unlinkdevice" rel="' . $d['id'] . '"><?= Yii::t('devices', 'unlink'); ?></a>
    </p>
<?php endforeach; ?>
<p><a>
	<img src="#" height="40px" width="30px"/><br/>
	<?= Yii::t('devices', 'Add device'); ?></a>

</p>


<script type="text/javascript">
    $( ".syncdevice" )
    .button()
    .click(function() {
    });
    $( ".unlinkdevice" )
    .button()
    .click(function() {
	if (confirm('<?php echo Yii::t('common', 'Are you sure?'); ?>'))
	{
	    $.post('/devices/remove/' + $(this).attr('rel'), function(data){
		if (data == 'ok')
		    $('#m_devices').load('/universe/devices');
	    });
	}
    });
    $( ".linkdevice" )
    .button()
    .click(function() {
	$.post('/devices/add/' + $(this).attr('rel'), function(data){
	    if (data == 'ok')
		$('#m_devices').load('/universe/devices');
	});
    });
</script>