<h2>Universe</h2>
<?php
if (!empty($item)) {
    ?>
    <script type="text/javascript">
        function queue(subaction)
        {
    	$.post('/files/queue', {id: <?php echo $item['id']; ?>, subaction: subaction}, function(data){
    	    tid = parseInt(data);
    	    if (tid > 0)
    	    {

    	    }
    	    location.href = '/files/fview/<?php echo $item['id']; ?>';
    	});
    	return false;
        }
    </script>
    <?php
    echo '<h3>Файл: ' . $item['title'] . '</h3>';
    echo '<p>Размер: ' . Utils::sizeFormat($item['fsize']) . '</p>';
    $actions = array();
    $actions[] = '<a href="/files/download?fid=' . $item['id'] . '" >' . Yii::t('files', 'download') . '</a>';
    if (empty($queue)) {
	$actions[] = '<a href="#" onclick="return queue(\'add\');">типизировать</a>';
    } else {
	echo '<p>Состояние: добавление в пространство<br />';
	echo 'Текущая операция: конвертирование<br />';
	echo 'Процент завершения: ' . rand(0, 100) . '%</p>';
	$actions[] = '<a href="#" onclick="return queue(\'cancel\');">отменить операцию</a>';
    }

    if (!empty($actions)) {
	echo '<p>' . implode(' | ', $actions) . '</p>';
    }
}