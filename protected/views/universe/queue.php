<?php
	if (!empty($qst))
	{
		echo '<ul>В процессе добавления в мое пространство:';
		foreach ($qst as $q)
		{
			echo '<li>' . serialize($q) . '</li>';
		}
		echo '</ul>';
	}