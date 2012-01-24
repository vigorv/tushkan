<?php

//print_r($params);
foreach ($params as $p)
{
	echo '<p>' . $p['title'] . ' = ' . $p['value'];
}

$fk = 0;
$links[$fk] = 'http://92.63.192.12/d/direktoren_for_det_hele/direktoren_for_det_hele.mp4';
$onlineLinks[$fk] = 'http://92.63.192.12:83/d/direktoren_for_det_hele/direktoren_for_det_hele.mp4';

//Yii::app()->getClientScript()->registerCoreScript('jquery');
//Yii::app()->getClientScript()->registerCoreScript('/js/jquery.fancybox-1.3.4/jquery-1.4.3.js');
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
<a rel="video" alt="" title="" href="#video' . $fk . '">смотреть онлайн</a>
';

echo $playerCode;