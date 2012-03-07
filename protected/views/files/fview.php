<?php
if (!empty($item)) {
	$mediaList = Utils::getMediaList();
    ?>
    <script type="text/javascript">
        function queue(subaction)
        {
	    	$.post('/files/queue', {id: <?php echo $item['id']; ?>, subaction: subaction}, function(data){
	    	    tid = parseInt(data);
	    	    if (tid > 0)
	    	    {
					$.post("/products/ajax", {typeId: <?php echo $item['type_id']; ?>, action: "wizardtypeparams"}, function(html){
						$("#content").html(html);
						$("#wizardParamsFormId").append('<input type="hidden" name="paramsForm[fileId]" value="<?php echo $item['id']; ?>" />');
						$("#wizardParamsFormId").append('<input type="hidden" name="paramsForm[typeId]" value="<?php echo $item['type_id']; ?>" />');
						$("#wizardParamsFormId").ajaxForm(function() {
							$("#content").load("<?php echo $mediaList[$item['type_id']]['link']; ?>");
						});
					});
	    	    }
	    	    else
	    	    {
	    	    	alert('Ошибка создания очереди конвертирования: ' + data);
	    	    }
	    	});
	    	return false;
        }

        function doDelete()
        {
        	if (!confirm("<?php echo Yii::t('common', 'Are you sure?'); ?>"))
        	{
        		return false;
        	}
	    	$.post('/files/remove', {id: <?php echo $item['id']; ?>}, function(){
	    		$("#content").load("/files/fview/<?php echo $item['id']; ?>");
	    	});
	    	return false;
        }

        function ajaxFormSubmit(f)
        {
        	url = f.action;
        	inputs = $("#" + f.id + " input");
        	if (inputs)
        	{
	        	for (i = 0; i < inputs.length; i++)
	        	{
	        		alert(inputs[i].name);
	        	}
        	}
			return false;
        }
    </script>
    <?php
    echo '<h3>Файл: ' . $item['title'] . '</h3>';
    echo '<p>Размер: ' . Utils::sizeFormat($item['fsize']) . '</p>';
    $actions = array();
    $d_link=true;
    if ($d_link) {
		$actions[] = '<button class="btn"  onclick="document.location='."'/files/download?fid=" . $item['id'] . "'". '" >' . Yii::t('files', 'download') . '</button>';
		$actions[]='<button class="btn" href="#" onclick="return doDelete();">' . Yii::t('files', 'delete') . '</button>';
    }
    if (empty($queue)) {
		$actions[] = '<button class="btn" href="#" onclick="return queue(\'add\');">типизировать</button>';
    } else {
		echo '<p>Состояние: добавление в пространство<br />';
		echo 'Текущая операция: конвертирование<br />';
		//echo 'Процент завершения: ' . rand(0, 100) . '%</p>';
		$actions[] = '<button class="btn" href="#" onclick="return queue(\'cancel\');">отменить операцию</button>';
    }

    if (!empty($actions)) {
		echo implode(' ', $actions);
    }
}