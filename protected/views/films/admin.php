<?php
	if (!empty($films))
	{
		foreach ($films as $f)
		{
			$href = Yii::app()->createUrl('/films/edit/' . $f['id']);
			echo '<div class="shortfilm"><a href="' . $href . '">';
			if (!empty($f['filename']))
				$poster = Yii::app()->params['tushkan']['postersURL'] . '/smallposter/' . $f['filename'];
			else
				$poster = Yii::app()->params['tushkan']['postersURL'] . '/noposter.jpg';
			echo '<img src="' . $poster . '" />';

			echo $f['title'] . '</a>, ' . $f['y'] . ', ' . $f['country'];
		}
	}
