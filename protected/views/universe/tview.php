<h2>Universe</h2>
<?php
if (!empty($info))
{
	//echo'<pre>';
	//print_r($dsc);
	//echo'</pre>';
	$title = $info['title'];
	echo '<h3>' . $title . '</h3>';
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

	function doRedirect(url)
	{
		location.href=url;
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
	$actions[] = '<button class="btn" onclick="return doRemove(' . $info['id'] . ')">удалить из пространства</button>';
	$onlineHref = '';
	if (!empty($params['onlineurl']))
	{
		$onlineLinks[$fk] = $params['onlineurl'];
//$onlineLinks[$fk] = 'http://92.63.192.12:83/d/direktoren_for_det_hele/direktoren_for_det_hele.mp4';
		$actions[] = '<button class="btn" onclick="$.address.value(\'/universe/tview/id/' . $info['id'] . '/do/online\'); return false;">смотреть онлайн</button>';
		$onlineHref = '<p id="autostart" rel="#video' . $fk . '"></p>';
	}
	else
		$onlineLinks[$fk] = '';
	unset($params['onlineurl']);

	if (!empty($params['url']) && !$info['online_only'])
	{
		$links[$fk] = $params['url'];
//$links[$fk] = 'http://92.63.192.12/d/direktoren_for_det_hele/direktoren_for_det_hele.mp4';
		$actions[] = '<button class="btn" onclick="return doRedirect(\'' . $links[$fk] . '\');">скачать</button>';
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
		echo '<p>' . implode(' ', $actions) . ' ' . $rentDsc . '</p>';
	}
	if (!empty($dsc['description']))
		echo '<p>' . $dsc['description'] . '</p>';

	echo'</div>';


//	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.js");
//	Yii::app()->getClientScript()->registerCssFile(Yii::app()->request->baseUrl . "/js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css");

	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/flowplayer/flowplayer-3.2.4.min.js");
//	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/flowplayer326/flowplayer-3.2.6.min.js");

	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/flowplayer/flowplayer.ipad-3.2.1.js");

	$playerCode = '
		' . $onlineHref . '
	<div id="flowplayerdiv" class="modal" style="width:640px; height:580px; display: none">
		<div class="modal-header">
			<a class="close" data-dismiss="modal">×</a>
			' . $title . '
		</div>
		<div class="modal-body" style="width:610px; height:420px; display:block">
		<a href="#" id="ipad"></a>
		</div>
	</div>

		<script type="text/javascript">
			$("#flowplayerdiv").on("show", function () {
				$("#video' . $fk . ' p").trigger("click");
			});
			$("#autostart").click(function(){
			   $("#flowplayerdiv").modal("show");
			   $(".close").click(function(){
			   		$("#flowplayerdiv").modal("hide");
			   });
			});

			function addVideo(num, path) {
//alert(path);
				document.getElementById("ipad"+num).href=path;
				document.getElementById("video" + num).style.display="";
				$f("ipad", "/js/flowplayer/flowplayer-3.2.5.swf",
				//$f("ipad" + num, "/js/flowplayer326/flowplayer-3.2.7.swf",
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
									},
									playlist: [
										{ url: path, scaling: "fit" }
									]
										}

					).ipad();
				return false;
			}
		</script>
	<div style="display: none">
		<div id="video' . $fk . '">
			<p id="ipad' . $fk . '" onclick="return addVideo(' . $fk . ', \'' . $onlineLinks[$fk] . '\');"></p>
		</div>
	</div>
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
