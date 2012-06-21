
		<div class="span12 no-horizontal-margin category">
			<div class="span3 margin-left-only">
				<a class="cat-name cat-name-active video" href="#">Ваши видеофайлы</a>
			</div>
			<div class="span3 margin-left-only">
				<a class="cat-name audio" href="#">Ваши аудиофайлы</a>
			</div>
			<div class="span3 margin-left-only">
				<a class="cat-name photo" href="#">Ваши фотографии</a>
			</div>
			<div class="span3 no-margin">
				<a class="cat-name document" href="#">Ваши документы</a>
			</div>
		</div>

    <div id="user_content" class="span12 no-horizontal-margin type">
<?php
	if (!empty($tFiles))
	{
		echo '<h4>Видео с витрин</h4>';
		foreach ($tFiles as $f)
		{
			$curVariantId = $f['variant_id'];
			$params = array();
			foreach($fParams as $p)
			{
				if ($p['id'] == $curVariantId)
				{
					$params[$p['title']] = $p['value'];
				}
			}

			if (!empty($params))
			{
				echo '<div class="chess"><a href="/universe/tview/' . $f['id'] . '">';
				if (!empty($params['poster']))
				{
					$poster = $params['poster'];
					unset($params['poster']);
				}
				else
				{
					$poster = '/images/films/noposter.jpg';
				}
				echo '<img align="left" width="80" height="120" src="' . $poster . '" />';
				echo '<b>' . $f['title'] . '</b>';
				echo '</a></div>';
			}
		}
	}

	if (!empty($tObjects))
	{
		echo '<h4>Мое видео</h4>';
		foreach ($tObjects as $o)
		{
			$params = array();
			foreach($oParams as $p)
			{
				$params[$p['title']] = $p['value'];
			}
			echo '<div class="shortfilm"><a href="/universe/oview/' . $o['id'] . '">';
			if (!empty($params['poster']))
			{
				$poster = $params['poster'];
				unset($params['poster']);
			}
			else
			{
				$poster = '/images/films/noposter.jpg';
			}
			echo '<img width="50" src="' . $poster . '" />';
			echo '<b>' . $o['title'] . '</b>';
			echo '</a></div>';
		}
	}
?>
    </div>

		<div class="span12 no-horizontal-margin type">
			<div class="span3 margin-left-only">
				<a class="type-name netbook" href="#">Нетбук/планшет</a>
			</div>
			<div class="span3 margin-left-only">
				<a class="type-name mobile" href="#">Мобильный телефон</a>
			</div>
			<div class="span3 margin-left-only">
				<a class="type-name type-name-active player" href="#">Плеер/iPod</a>
			</div>
			<div class="span3 no-margin">
				<a class="type-name tv" href="#">Телевизор</a>
			</div>
		</div>
