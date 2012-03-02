
<div id="Universe">
    <h1>Universe</h1>
    <div id="Universe_options">
        <div class="fleft">
            <a href="/universe/add"><img src="" width="25px" height="25px" />Add</a>
        </div>
        <div class="fright">
            <a href=""><img src="" width="25px" height="25px" />Delete </a>
        </div>
    </div>
    <div class="clearfix"></div>

    <div id="user_content" class="block_content">
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
				echo '<img align="left" width="80" src="' . $poster . '" />';
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
    <div id="section_content" class="block_content">

    </div>
    <div id="section_files" class="block_content">

    </div>
    <div id="device_content" class="block_content">

    </div>
    <div id="upload_block" class="block_content">

    </div>
</div>
<script langauge="javascript">
    //$('#user_content').load('users/view');
  //$('#device_content').load('devices/view');
    //$('#section_content').load('sections/view')
//   $('#section_files').load('files')
   $('#upload_block').load('/universe/upload')
</script>
