<script type="text/javascript">
	var globalValue;

	function cancelConvert(qid)
	{
        if (!confirm("<?php echo Yii::t('common', 'Are you sure?'); ?>"))
        {
            return false;
        }
        globalValue = qid;
        $.post('/files/cancelconvert', {id: qid}, function(){
        	$('#queue' + globalValue + 'li').remove();
        });


        return false;
	}

	function restartQueue(qid, oid)
	{
		globalValue = oid;
        $.post('/files/restartqueue', {id: qid}, function(){
        	$('#content').load('/files/fview/' + globalValue);
        });
	}
</script>

<?php
	if (!empty($qst))
	{
		echo '<ul class="nav inside-nav">';
		foreach ($qst as $q)
		{
			$info = array();
			if (empty($q['title']))
				$q['title'] = '';
			$title= 'от партнера ' . $q['title'];
			if (empty($q['title']))
			{
				$title = 'конвертирование';
			}

			$img = 'video_mp4.png';
			$start = strtotime($q['date_start']);
			if ($start <= 0)
				$start = ' <i>в ожидании очереди на добавление</i>';
			else
				$start = ' запущено ' . date('Y-m-d в H:i', $start);
			$state = '';
			if ($q['state'] > 3)
				$state = 'ошибка конвертирования операция будет перезапущена';
			else
			{
				if (!empty($q['cmd_id']))
					$state = 'прогресс ' . ($q['cmd_id'] * 10 + $q['state'] * 3) . '%';
			}
			if (!empty($q['info']))
			{
				$info = unserialize($q['info']);
				if (!empty($info['files']) && !empty($info['files'][0]))
				{
					$title .= ' ' . basename($info['files'][0]);
				}
                if ((isset($info['tags'])) && !empty($q['title']))
				    $title .= ' (от ' . $q['title'] . ') ';
			}
			$btn = '<a id="queue' . $q['id'] . '" class="btn" onclick="return cancelConvert(' . $q['id'] . ')">' . Yii::t('common', 'cancel'). '</a>';
			if ($q['state'] == 10)
				$btn .= ' <a class="btn">' . Yii::t('common', 'restart'). '</a>';
			echo '<li id="queue' . $q['id'] . 'li"><img src="/images/64x64/mimetypes/' . $img . '">' . $title . $start . ' <i>' . $state . '</i> <nobr>' . $btn . '</nobr></li>';
		}
		echo '</ul>';
	}