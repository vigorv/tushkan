<?php
if (!empty($item)) {
    $mediaList = Utils::getMediaList();
    $detectedType = 0; $detectedTypeName = '';
    ?>
    <script type="text/javascript">
    	links = new Array();
    	<?php
    		$info = pathinfo(strtolower($item['title']));
    		foreach ($mediaList as $k => $v)
    		{
    			echo "links[{$k}] = '{$v['link']}';";

    			//ПОКА ТИПИЗИРУЕМ ТОЛЬКО КАК ВИДЕО
    			if (($k == 1) && !empty($info['extension']) && in_array($info['extension'], $v['exts']))
    			{
    				$detectedType = $k;
    				$detectedTypeName = $v['title'];
    			}
    		}
    	?>
        function doType(type_id)
        {
            $.post("/products/ajax", {typeId: type_id, action: "wizardtypeparams"}, function(html){
                $("#content").html(html);
                $("#wizardParamsFormId").append('<input type="hidden" name="paramsForm[fileId]" value="<?php echo $item['id']; ?>" />');
                $("#wizardParamsFormId").append('<input type="hidden" name="paramsForm[typeId]" value="' + type_id + '" />');
            });
        }

        function doDelete()
        {
            if (!confirm("<?php echo Yii::t('common', 'Are you sure?'); ?>"))
            {
                return false;
            }

            $.post('/files/remove', {id: <?php echo $item['id']; ?>}, function(){
                $('#content').load("/universe/library?lib=v");
            });
            return false;
        }

        function ajaxFormSubmit(f)
        {
            return false;
        }

		function startConvert(ufid)
		{
			$.post('/files/startconvert', {id: ufid}, function(data){
				if (data == 'queue')
				{
					$('#content').load('/files/fview/<?php echo $item['id']; ?>');
				}
			});
			return false;
		}

    </script>
<div class="span12 no-horizontal-margin inside-movie my-catalog">
    <?php
    echo '<h1>Файл: ' . $item['title'] . '</h1>';
?>
	<div class="pad-content">
<?php
	if (!empty($qstContent))//ВЫВОДИМ ИНФО ОБ ОЧЕРЕДИ КОНВЕРТИРОВАНИЯ
		echo $qstContent;

	if (strtotime($item['created']) > 0)
    	echo '<p>Дата загрузки: ' . $item['created'] . '</p>';
    echo '<p>Размер: ' . Utils::sizeFormat($item['fsize']) . '</p>';
    $actions = array();
    if (isset($variants) && count($variants)) {
        $actions[] = '<a class="btn"  onclick="window.open(' . "'/files/download?vid=" . $variants[0]['id'] . "'" . ');" >' . Yii::t('files', 'download') . '</a>';
        $actions[] = '<a class="btn" href="#" onclick="return doDelete();">' . Yii::t('files', 'delete') . '</a>';

    	if (!empty($detectedType) && empty($item['object_id']))
    		$actions[] = '<a class="btn" onclick="return doType(' . $detectedType . ');">' . Yii::t('common', 'Typify') . ' ' . Yii::t('common', 'as') . ' "' . $detectedTypeName . '"</a>';
    }


	if (empty($variants[0]['preset_id']) && empty($qstContent) && ($detectedType == 1)) //ПОКА КОНВЕРТИРОВАТЬ МОЖЕМ ТОЛЬКО КАК ВИДЕО
	{
		$actions[] = '<a href="#" class="btn" onclick="return startConvert(' . $item['id'] . ')">' . Yii::t('files', 'convert') . '</a>';
	}

    if (!empty($actions)) {
        echo implode(' ', $actions);
    }
?>
	</div>
</div>
<?php
}