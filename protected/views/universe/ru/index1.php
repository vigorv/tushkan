div>
    <h4>Пользователь</h4>
    <div id="user_content"></div>
</div>
<div>
    <h4>Разделы</h4> 
    <div id="section_content"></div>
</div>
<div>
    <h4>Устройства</h4> 
    <div id="device_content">
    </div>
</div>

<script langauge="javascript">
    $('#user_content').load('user/view');
    $('#device_content').load('devices/view');
    $('#section_content').load('sections/view')
</script>