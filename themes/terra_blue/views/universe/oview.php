<?php
if (!empty($prms))
{
	$presets = CPresets::getPresets();
	$qs = array();
	foreach ($qualities as $q)
	{
		foreach ($presets as $p)
		{
			if ($p['id'] == $q['preset_id'])
			{
				//ПОРЯДОК ЭЛЕМЕНТОВ МАССИВА НЕ МЕНЯТЬ
				$qs[$p['title']][] = array($q['fname'], $q['ufid'], $q['fvid'], $q['preset_id']);
				break;
			}
		}
	}
	$aContent = '';
	$tabsContent = '<ul class="nav nav-tabs">';
	$btnsContent = '<div class="tab-content">';
	$autoActionLink = '';
	$currentQuality = '';
/*
	echo'<pre>';
	print_r($qs);
	echo'</pre>';
exit;
//*/
	$commonActions = array('<a href="#" onclick="return doRemoveAll(' . $id . ')">' . Yii::t('files', 'delete all qualities') . '</a>');

	$playList = '';
	foreach ($qs as $k => $val)
	{
		$qualityVariantId = $val[0][2];
//ВНИМАНИЕ НУЖНО ОПРЕДЕЛЯТЬ КАКОЙ ВАИАНТ ЗАПРОСИЛИ СМОТРЕТЬ ПО КАЧЕСТВУ ИЗ УРЛА
		$qualityPresetId = $val[0][3];
		$actions = array();
		if ($k <> $currentQuality)
		{
			$num = 1;//ПОРЯДКОВЫЙ НОМЕР ФАЙЛА ДАННОГО КАЧКСТВА
			$tabsContent .= '<li id="linkQuality' . $k . '"><a href="#tabQuality' . $k . '" data-toggle="tab">Качество "' . $k . '"</a></li>';;
			$currentQuality = $activateTab = $k;
		}
		if (!empty($_GET['quality']))
		{
			$activateTab = $_GET['quality'];
			if ($_GET['quality'] == $k)
				$currentVariantId = $qualityVariantId;
		}
		else
		{
			$currentVariantId = $qualityVariantId;
		}

		//$actions[] = '<a href="#" onclick="return doRemove(' . $val[0][1] . ')">' . Yii::t('files', 'delete') . '</a>';
		if (empty($queue)) {
			if (!empty($files[0]['preset_id']))
				$actions[] = '<a href="#" onclick="$.address.value(\'/universe/oview/id/' . $id . '/do/online/quality/' . $k . '\'); return false;">смотреть онлайн</a>';
		}
		else
		{
		    echo '<p>Состояние: добавление в пространство<br />';
		    echo 'Текущая операция: конвертирование<br /><p>';
		}

		$dLink = '/files/download?vid=' . $qualityVariantId;

		$aContent .= '<p id="autostart" rel="#video' . $qualityVariantId . '"></p>';
		$playList .= '
		<div id="video' . $qualityVariantId . '">
			<p id="ipad' . $qualityVariantId . '" onclick="return addVideo(' . $qualityVariantId . ', \'' . $dLink . '\');"></p>
		</div>';

		unset($params['onlineurl']);

		$actions[] = '<a href="#" onclick="return doRedirect(\'' . $dLink . '\');">' . Yii::t('files', 'download') . '</a>';
		unset($params['url']);

		if (!empty($actions))
		{
			$actions = '<li>' . implode('</li><li>', $actions) . '</li>';
			$actions = '<li class="active"><a noref style="text-decoration: none">' . Yii::t('files', 'delete') . '</a></li>'
				. $actions;

			$btnsContent .= '<div class="tab-pane" id="tabQuality' . $currentQuality . '"><ul class="nav nav-pills movie-buttons">' . $actions . '</ul></div>';
		}
	}
	$tabsContent .= '</ul>';
	$btnsContent .= '</div>';

	foreach ($prms as $key => $value)
	{
		if ($value['ptptitle'] == 'title')
			$title = $value['value'];
	}
	if (empty($title))
		$title = $prms[0]['uotitle'];
?>
<script type="text/javascript">
	function doRemoveAll(ufid)
	{
		if (confirm('<?php echo Yii::t('common', 'Are you sure?'); ?>'))
		{

		}
		return false;
	}

	function doRemove(vid)
	{
		if (confirm('<?php echo Yii::t('common', 'Are you sure?'); ?>'))
		{

		}
		return false;
	}

	function doRedirect(url)
	{
		location.href=url;
		return false;
	}
</script>
<div class="span12 no-horizontal-margin inside-movie my-catalog">
<?php
	echo '<h1>' . $title . '</h1>';
?>
	<div class="pad-content">
<?php
	if (!empty($params['poster']))
	{
		$poster = $params['poster'];
		unset($params['poster']);
	}
	else
	{
		$poster = '/images/films/noposter.jpg';
	}
?>
			<div class="span12 no-horizontal-margin some-space"></div>
			<div class="span2">
				<ul class="thumbnails">
					<li class="span2">
						<a href="#" class="thumbnail">
							<img src="<?php echo $poster; ?>" alt="">
						</a>
					</li>
				</ul>
			</div>
<?php
	if (!empty($neededQuality))
		$activateTab = $neededQuality;
	$tabsContent = str_replace(	'<li id="linkQuality' . $activateTab . '"><a',
								'<li class="active" id="linkQuality' . $activateTab . '"><a',
								$tabsContent);
	$btnsContent = str_replace(	'<div class="tab-pane" id="tabQuality' . $activateTab . '"',
								'<div class="tab-pane active" id="tabQuality' . $activateTab . '"',
								$btnsContent);
	if (!empty($commonActions))
	{
		$actions = '<li>' . implode('</li><li>', $commonActions) . '</li>';
		$aContent .= '<div class="span11"><ul class="nav nav-pills movie-buttons">' . $actions . '</ul></div>';
	}

	$aContent .= '

	<div class="span11">

	' . $tabsContent . $btnsContent . '

	</div>';// . $activate;
?>
	<div class="span9 movie-text">
<?php
	foreach ($params as $param => $value)
	{
		if (empty($value)) continue;
		if ($param == Yii::app()->params['tushkan']['fsizePrmName'])
		{
			$value = Utils::sizeFormat($value);
		}
		echo '<p><strong>' . Yii::t('params', $param) . ':</strong> ' . $value . '</p>';
	}
?>
	</div>
<?php
	//ВЫВОД КАЧЕСТВ И ДЕЙСТВИЙ
	echo $aContent;
?>
	</div>
</div>
<?php
//	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.js");
//	Yii::app()->getClientScript()->registerCssFile(Yii::app()->request->baseUrl . "/js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css");

	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/flowplayer/flowplayer-3.2.4.min.js");
//	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/flowplayer326/flowplayer-3.2.6.min.js");

	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/flowplayer/flowplayer.ipad-3.2.1.js");

	if (empty($currentVariantId))
		$currentVariantId = $qualityVariantId;

	$playerCode = '
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
				$("#video' . $currentVariantId . ' p").trigger("click");
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
	' . $playList . '
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