<?php
	if (!empty($qst))
	{
		echo '<ul class="nav inside-nav">';
		foreach ($qst as $q)
		{
			$info = array();
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
                if (isset($info['tags']))
				    $title .= ' (от ' . $q['title'] . ') ';
			}
			echo '<li><img src="/images/64x64/mimetypes/' . $img . '">' . $title . $start . ' <i>' . $state . '</i></li>';
		}
		echo '</ul>';
	}