<?php
	if (!empty($qst))
	{
		echo '<h4>В процессе добавления:</h4><ul>';
		foreach ($qst as $q)
		{
			$info = array();
			$info['tags']['title'] = 'от партнера ' . $q['title'];
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
				$info['tags']['title'] .= ' (от ' . $q['title'] . ') ';
			}
			echo '<li>' . $info['tags']['title'] . $start . ' <i>' . $state . '</i></li>';
		}
		echo '</ul>';
	}