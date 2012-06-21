	<div class="tabbable"> <!-- Only required for left/right tabs -->
		<ul class="nav inside-nav nav-pills inside-nav-pills">
		<?php
			foreach ($mediaList as $ml)
			{
				if ($ml['hidden']) continue;
				$active = '';
				if ($ml['id'] == $type_id)
					$active = 'class="active"';
				echo '<li ' . $active . '><a href="' . $ml['link']. '">' . $ml['title'] . '</a></li>';
			}
		?>
		</ul>
	</div>
	<div class="tab-content">
		<div class="tab-pane active">
			<div class="span12 no-horizontal-margin inside-movie my-catalog">
<?php
if (!empty($info))
{
/*
	echo'<pre>';
	print_r($partnerInfo);
	echo'</pre>';
//*/
	$presets = CPresets::getPresets();

	$title = $info['title'];
	echo '<h1>' . $title . '</h1>';
?>
	<div class="back-button">
		<ul class="nav nav-pills movie-back-button">
			<li><a href="<?php echo $mediaList[$type_id]['link']; ?>"><?php echo Yii::t('common', 'Back to list')?></a></li>
		</ul>
	</div>
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
	$fk = 0;
/*
//СТАРЫЙ ВАРИАНТ
	$onlineHref = '';
	if (!empty($params['onlineurl']))
	{
		$onlineLinks[$fk] = $params['onlineurl'];
//$onlineLinks[$fk] = 'http://92.63.192.12:83/d/direktoren_for_det_hele/direktoren_for_det_hele.mp4';
		//$actions[] = '<button class="btn" onclick="$.address.value(\'/universe/tview/id/' . $info['id'] . '/do/online\'); return false;">смотреть онлайн</button>';
		$onlineHref = '<p id="autostart" rel="#video' . $fk . '"></p>';
	}
	else
		$onlineLinks[$fk] = '';

	if (!empty($params['url']) && !$info['online_only'])
	{
		$links[$fk] = $params['url'];
//$links[$fk] = 'http://92.63.192.12/d/direktoren_for_det_hele/direktoren_for_det_hele.mp4';
		//$actions[] = '<button class="btn" onclick="return doRedirect(\'' . $links[$fk] . '\');">скачать</button>';
	}
	else
		$links[$fk] = '';
//*/

	unset($params['url']);
	unset($params['width']);
	unset($params['height']);
	unset($params['onlineurl']);

	$qs = array();
	foreach ($qualities as $q)
	{
		foreach ($presets as $p)
		{
			if ($p['id'] == $q['preset_id'])
			{
				//ПОРЯДОК ЭЛЕМЕНТОВ МАССИВА НЕ МЕНЯТЬ
				$qs[$p['title']][] = array($q['fname'], $q['pfid'], $q['variant_id'], $q['preset_id']);
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

	$fids = array();
	$onlineLinks = array(0 => '');
	foreach ($qs as $k => $val)
	{
		$isRented = false;
		$isOwned = false;
		$qualityVariantId = $val[0][2];
		$qualityPresetId = $val[0][3];
		$actions = array();
		$actions[] = '<a onclick="return doRemove(' . $info['id'] . ')">удалить из пространства</a>';
		if (!empty($orders))
		{
			foreach ($orders as $order)
			{
				if ($order['variant_id'] == $info['variant_id'])
				{
/*
	echo'<pre>';
	print_r($order);
	echo'</pre>';
//exit;
//*/
					if (!empty($order['variant_quality_id']) && ($order['variant_quality_id'] != $qualityVariantId))
					{
	//ЕСЛИ ДОПОЛНИТЕЛЬНАЯ ПРОВЕРКА ПО КАЧЕСТВУ ВАРИАНТА НЕ ПРОШЛА, ЗНАЧИТ ПРОДОЛЖАЕМ ИСКАТЬ ДРУГОЕ КАЧЕСТВО
						if ($qualityPresetId > $order['preset_id'])
							continue;
					}

					$inOrder = true;
					if ($order['state'] == _ORDER_PAYED_)
					{
						//ЕСЛИ ОПЛАТИЛИ
						if (!empty($order['price_id']))
						{
							$isOwned = true; $isRented = false;
							break;
						}
						if (!empty($order['rent_id']))
						{
							$isRented = true;
							break;
						}
					}
				}
			}
		}

		if ($k <> $currentQuality)
		{
			$num = 1;//ПОРЯДКОВЫЙ НОМЕР ФАЙЛА ДАННОГО КАЧКСТВА
			$tabsContent .= '<li id="linkQuality' . $k . '"><a href="#tabQuality' . $k . '" data-toggle="tab">Качество "' . $k . '"</a></li>';;
			$currentQuality = $activateTab = $k;
		}

		foreach ($val as $v)
		{
//$v[0] = 'http://92.63.192.12:83/l/little_caesar/270/little_caesar.mp4';//ОТЛАДКА
//$v[0] = 'http://92.63.192.12:83' . $v[0];
			$numF = '';
			if (count($val) > 1)
			{
				$numF = 'файл ' . $num;
			}

			if (empty($partnerInfo['sprintf_url']))
			{
				$v[0] = 'http://212.20.62.34:82' . $v[0];
				$online = '<a href="#" onclick="$.address.value(\'/universe/tview/id/' . $info['id'] . '/do/online/quality/' . $k . '/fid/' . $v[1] . '\'); return false;">смотреть онлайн ' . $numF . '</a>';
				$download = '<a href="#" onclick="return doRedirect(\'' . $v[0] . '\');">скачать ' . $numF . '</a>';
			}
			else
			{
				//порядок параметров original_id, quality, fileName (без расширения), доп. параметр 1-online, 0-download
				$pathInfo = pathinfo($v[0]);
				$fn = str_replace('.' . $pathInfo['extension'], '', $pathInfo['basename']);
				$v[0] = sprintf($partnerInfo['sprintf_url'], $partnerInfo['original_id'], $k, $fn, 1);
				$online = '<a href="#" onclick="$.address.value(\'/universe/tview/id/' . $info['id'] . '/do/online/quality/' . $k . '/fid/' . $v[1] . '\'); return false;">смотреть онлайн ' . $numF . '</a>';
				$v[0] = sprintf($partnerInfo['sprintf_url'], $partnerInfo['original_id'], $k, $fn, 0);
				$download = '<a href="#" onclick="return doRedirect(\'' . $v[0] . '\');">скачать ' . $numF . '</a>';
			}

			if (empty($fid)) $fid = $v[1];

			$fids[] = $v[1];
			$onlineLinks[$v[1]] = $v[0];
			if ($fid == $v[1])
			{
				$aContent .= '<p id="autostart" rel="#video' . $fid . '"></p>';
			}

			if ($info['online_only'])
				$actions[] = $online;
			else
			{
				$actions[] = $online;
				if (!$isRented || $isOwned)
					$actions[] = $download;
			}
			$num++;
		}
		$rentDsc = '';
		if (!empty($actions))
		{
			if ($isRented && !empty($info['period']))
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
			$actions = '<li>' . implode('</li><li>', $actions) . '</li>';
			if (!empty($rentDsc))
				$actions .= '<li class="active"><a noref style="text-decoration: none">' . $rentDsc . '</a></li>';
			$btnsContent .= '<div class="tab-pane" id="tabQuality' . $currentQuality . '"><ul class="nav nav-pills movie-buttons">' . $actions . '</ul></div>';
		}
	}

	//echo $aContent;
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
	if (!empty($dsc['description']))
		echo '<p>' . $dsc['description'] . '</p>';
?>
	</div>
<?php
	$tabsContent .= '</ul>';
	$btnsContent .= '</div>';

	if (!empty($neededQuality))
		$activateTab = $neededQuality;
	$tabsContent = str_replace(	'<li id="linkQuality' . $activateTab . '"><a',
								'<li class="active" id="linkQuality' . $activateTab . '"><a',
								$tabsContent);
	$btnsContent = str_replace(	'<div class="tab-pane" id="tabQuality' . $activateTab . '"',
								'<div class="tab-pane active" id="tabQuality' . $activateTab . '"',
								$btnsContent);

	$aContent .= '


	<div class="span11">





	' . $tabsContent . $btnsContent . '





	</div>';// . $activate;
	//ВЫВОД КАЧЕСТВ И ДЕЙСТВИЙ
	echo $aContent;

//	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.js");
//	Yii::app()->getClientScript()->registerCssFile(Yii::app()->request->baseUrl . "/js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.css");

	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/flowplayer/flowplayer-3.2.4.min.js");
//	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/flowplayer326/flowplayer-3.2.6.min.js");

	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/flowplayer/flowplayer.ipad-3.2.1.js");

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
				$("#video' . $fid . ' p").trigger("click");
			});
			$("#autostart").click(function(){
			   $("#flowplayerdiv").modal("show");
			   $(".close").click(function(){
			   		$("#flowplayerdiv").modal("hide");
			   });
			});

			function addVideo(num, path) {
//alert(num);
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
		<div id="video' . $fid . '">
			<p id="ipad' . $fid . '" onclick="return addVideo(' . $fid . ', \'' . $onlineLinks[$fid] . '\');"></p>
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
?>
		</div>
	</div>
</div>