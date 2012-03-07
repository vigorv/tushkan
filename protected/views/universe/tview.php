<h2>Universe</h2>
<?php
if (!empty($info))
{
	//echo'<pre>';
	//print_r($dsc);
	//echo'</pre>';
	echo '<h3>' . $info['title'] . '</h3>';
?>
<script type="text/javascript">
	function doRemove(oid)
	{
		if (confirm('<?php echo Yii::t('common', 'Are you sure?'); ?>'))
		{
			$.post('/universe/remove/' + oid, function(){
				$.address.value('/universe');
			});
		}
		return false;
	}
</script>
<?php
	echo '<div id="productdetail">';
	if (!empty($params['poster']))
	{
		$poster = $params['poster'];
		unset($params['poster']);
	}
	else
	{
		$poster = '/images/films/noposter.jpg';
	}
	echo '<img align="left" src="' . $poster . '" />';

	$fk = 0;
	$actions = array();
	$actions[] = '<a href="#" onclick="return doRemove(' . $info['id'] . ')">удалить из пространства</a>';
	$onlineHref = '';
	if (!empty($params['onlineurl']))
	{
		$onlineLinks[$fk] = $params['onlineurl'];
//$onlineLinks[$fk] = 'http://92.63.192.12:83/d/direktoren_for_det_hele/direktoren_for_det_hele.mp4';
		$actions[] = '<a href="/universe/tview/id/' . $info['id'] . '/do/online">смотреть онлайн</a>';
		$onlineHref = '<a id="autostart" alt="" title="" rel="#video' . $fk . '" style="display:none;">video</a>';
	}
	else
		$onlineLinks[$fk] = '';
	unset($params['onlineurl']);

	if (!empty($params['url']) && !$info['online_only'])
	{
		$links[$fk] = $params['url'];
//$links[$fk] = 'http://92.63.192.12/d/direktoren_for_det_hele/direktoren_for_det_hele.mp4';
		$actions[] = '<a alt="" title="" href="' . $links[$fk] . '">скачать</a>';
	}
	else
		$links[$fk] = '';
	unset($params['url']);
	unset($params['width']);
	unset($params['height']);

	echo '<p>';
	foreach ($params as $param => $value)
	{
		if (empty($value)) continue;
		if ($param == Yii::app()->params['tushkan']['fsizePrmName'])
		{
			$value = Utils::sizeFormat($value);
		}
		echo '<br />' . Yii::t('params', $param) . ': ' . $value . '</li>';
	}
	echo'</p>';
	$rentDsc = '';
	if (!empty($actions))
	{
		if (!empty($info['period']))
		{
			$rentDsc = ' арендовано на ' . Utils::spellPeriod($info['period']);
			$start = strtotime($info['start']);
			if ($start > 0)
			{
				$less = $start + Utils::parsePeriod($info['period'], $info['start']) - time();
				if ($less)
				{
					$rentDsc .= ' до окончания аренды ' . Utils::timeFormat($less);
				}
				else
				{
					$rentDsc .= ' срок аренды истек';
				}
			}
		}
		echo '<p>' . implode(' | ', $actions) . ' ' . $rentDsc . '</p>';
	}
	if (!empty($dsc['description']))
		echo '<p>' . $dsc['description'] . '</p>';

	echo'</div>';



	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.js");
	Yii::app()->getClientScript()->registerCssFile(Yii::app()->request->baseUrl . "/js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css");

//	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/flowplayer/flowplayer-3.2.4.min.js");
	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/flowplayer326/flowplayer-3.2.6.min.js");

	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/flowplayer/flowplayer.ipad-3.2.1.js");

	$playerCode = '
	<div id="flowplayerdiv" style="display: none">
	<h4><a href="#" onclick="document.getElementById(\'flowplayerdiv\').style.display=\'none\'; return false;">выключить проигрыватель</a></h4>
		<a href="#"
			style="display:block;width:95%;height:297px"
			id="ipad0">
		</a>
	</div>

		<script>
			$(document).ready(function() {
				$("#autostart").fancybox({
			        "zoomSpeedIn":  0,
			        "zoomSpeedOut": 0,
			        "overlayShow":  true,
			        "overlayOpacity": 0.8,
			        "showNavArrows": false,
					//"onComplete": function() { $("#video' . $fk . ' p").trigger("click"); return false; }
				});
			});

			function addVideo(num, path) {
alert(num);
				document.getElementById("ipad" + num).href=path;
				document.getElementById("video" + num).style.display="";
				//$f("ipad" + num, "/js/flowplayer/flowplayer-3.2.5.swf",
				$f("ipad" + num, "/js/flowplayer326/flowplayer-3.2.7.swf",
									{plugins: {
										h264streaming: {
											url: "/js/flowplayer/flowplayer.pseudostreaming-3.2.5.swf"
													 }
		                             },
									clip: {
										provider: "h264streaming",
										autoPlay: true,
										scaling: "fit",
										autoBuffering: true,
										scrubber: true
									},
									canvas: {
										// remove default canvas gradient
										backgroundGradient: "none",
										backgroundColor: "#000000"
									}
						}
					).ipad();
				return false;
			}
		</script>
	<div style="display: none">
		<div id="video' . $fk . '">
			<p style="width:640px; height:480px; display:block" id="ipad' . $fk . '" onclick="return addVideo(' . $fk . ', \'' . $onlineLinks[$fk] . '\');"></p>
		</div>
	</div>
		' . $onlineHref . '
	';

	echo $playerCode;
?>
<script type="text/javascript">
<?php
	if ($subAction == 'online')
	{
?>
$(document).ready(function() {
		$('#autostart').trigger('click');
	});
<?php
	}
?>
</script>
<?php
}
