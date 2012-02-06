<h2>Universe</h2>
<?php
if (!empty($info))
{
	echo '<h3>' . $info['title'] . '</h3>';
?>
<script type="text/javascript">
	function doRemove(oid)
	{
		if (confirm('<?php echo Yii::t('common', 'Are you sure?'); ?>'))
		{

		}
		return false;
	}
</script>
<?php
	echo '<div class="shortfilm">';
	if (!empty($params['poster']))
	{
		$poster = $params['poster'];
		unset($params['poster']);
	}
	else
	{
		$poster = '/images/films/noposter.jpg';
	}
	echo '<img src="' . $poster . '" />';

	$fk = 0;
	$actions = array();
	$actions[] = '<a href="#" onclick="return doRemove(' . $info['id'] . ')">удалить из пространства</a>';
	$onlineHref = '';
		$onlineLinks[$fk] = $locInfo['filename'];
//$onlineLinks[$fk] = 'http://92.63.192.12:83/d/direktoren_for_det_hele/direktoren_for_det_hele.mp4';
		$actions[] = '<a href="/universe/oview/id/' . $info['id'] . '/do/online">смотреть онлайн</a>';
		$onlineHref = '<a id="autostart" rel="video" alt="" title="" href="#video' . $fk . '"></a>';
	unset($params['onlineurl']);

	$links[$fk] = $locInfo['filename'];
//$links[$fk] = 'http://92.63.192.12/d/direktoren_for_det_hele/direktoren_for_det_hele.mp4';
		$actions[] = '<a rel="video" alt="" title="" href="' . $links[$fk] . '">скачать</a>';
	unset($params['url']);

	echo '<ul>';
	foreach ($params as $param => $value)
	{
		if ($param == Yii::app()->params['tushkan']['fsizePrmName'])
		{
			$value = Utils::sizeFormat($value);
		}
		echo '<li>' . Yii::t('params', $param) . ': ' . $value . '</li>';
	}
	echo'</ul>';
	if (!empty($actions))
	{
		echo '<p>' . implode(' | ', $actions) . '</p>';
	}
	echo'</div>';



	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.js");
	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/flowplayer/flowplayer-3.2.4.min.js");
	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/flowplayer/flowplayer.ipad-3.2.1.js");
	Yii::app()->getClientScript()->registerCssFile(Yii::app()->request->baseUrl . "/js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css");

	$playerCode = '
	<div id="flowplayerdiv" style="display: none">
	<h4><a href="#" onclick="document.getElementById(\'flowplayerdiv\').style.display=\'none\'; return false;">выключить проигрыватель</a></h4>
		<a href="#"
			style="display:block;width:95%;height:297px"
			id="ipad">
		</a>
	</div>

		<script>
			$(document).ready(function() {
				$("a[rel=video]").fancybox({
			        "zoomSpeedIn":  0,
			        "zoomSpeedOut": 0,
			        "overlayShow":  true,
			        "overlayOpacity": 0.8,
			        "showNavArrows": false,
					"onComplete": function() { $(this.href + " a").trigger("click"); return false; }
				});
			});

			function addVideo(num, path) {
				document.getElementById("ipad" + num).href=path;
				document.getElementById("video" + num).style.display="";
				$f("ipad" + num, "/js/flowplayer/flowplayer-3.2.5.swf",
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
			<a style="width:640px; height:480px; display:block" id="ipad' . $fk . '" onclick="return addVideo(' . $fk . ', \'' . $onlineLinks[$fk] . '\');"></a>
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
