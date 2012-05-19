<?php
	if (!empty($qst))
	{
		echo '<h4>В процессе добавления:</h4><ul>';
		foreach ($qst as $q)
		{
			$info = array();
			$info['tags']['title'] = 'Без названия';
			$start = strtotime($q['date_start']);
			if (empty($start))
				$start = 'в ожидании очереди';
			else
				$start = 'запущено ' . date('Y-m-d в H:i');
			if ($q['state'] > 3)
				$state = 'ошибка конвертирования операция будет перезапущена';
			else
				$state = 'прогресс ' . ($q['cmd_id'] * 10 + $q['state'] * 3) . '%';
			if (!empty($q['info']))
			{
				$info = unserialize($q['info']);
			}
			echo '<li>' . $info['tags']['title'] . ' (от ' . $q['title'] . ') ' . $start . ' ' . $state . '</li>';
		}
		echo '</ul>';
	}