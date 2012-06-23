<html lang="ru">
	<head>
		<meta charset="utf-8">
		<meta name="description" content="">
		<meta name="author" content="">

		<link href="/css/bootstrap.min.css" rel="stylesheet">
		<link href="/css/tushkan.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="/css/carousel/skin.css" />
		<style>body {padding-top: 25px; background: #ededed url('/img/body-bg.png') top left repeat-x;}</style>
		<!--[if lt IE 9]>
			<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>

<?php
	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/jquery-ui-1.7.3.custom/js/jquery-ui-1.7.3.custom.min.js");
	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.hotkeys.js');
	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.cookie.js');
	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.form.js');
	Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.address-1.4.min.js');
    Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . '/js/jquery.jcarousel.min.js');
?>

		<script src="/js/bootstrap.min.js"></script>

        <title><?php echo CHtml::encode($this->pageTitle); ?></title>
	</head>
<body>
	<div class="container">
		<!--Навигация-->
		<div id="m_panel" class="navbar">
		</div>

		<!--Строка поиска-->
		<form class="form-search">
			<div class="control-group">
				<div class="controls">
					<div class="input-prepend">
						<a href="#search" onClick="SearchEverywhere($('#i_search').val());"><span class="add-on"><i class="icon-search"></i></span></a><input id="i_search" type="text" class="span-search search-query" placeholder="Поиск по всем файлам...">
<script type="text/javascript">
	var cont = $("#content");

	$('#i_search').keypress(function(e){
		if(e.which == 13){
			$.address.value('/universe/search?text='+this.value);
		}
	});

	function SearchEverywhere(text){
		$.address.value('/universe/search?text='+text);
		return false;
	}
</script>
					</div>
				</div>
			</div>
		</form>
		<!--Список фильмов-->
		<div id="m_goods" class="span12 no-horizontal-margin top-movies-list">

		</div>

	<div id="content">
	</div>

		<div id="m_devices" class="span12 no-horizontal-margin connected">
		</div>
	</div>
			<script langauge="javascript">
				$.address.change(function(event) {
					console.log(event.value);
					$('#content').load(event.value, function(){
						$('#content a').click(function(){
							lnk= $(this).attr('href');
							if (lnk.substr(0, 1) == "#")return true;
							if (lnk=="") return false;

							$.address.value(lnk);
							return false;
						});
					});
					return false;
				});

				$('#m_panel').load('/universe/panel', function(){
					$('#m_panel .dropdown-menu a').click(function(){
						lnk= $(this).attr('href');
						if (lnk.substr(0, 1) == "#")return true;
						if (lnk=="") return false;
						$.address.value(lnk);
						return false;
					});
				});

				$('#m_uploads a').click(function() {
					lnk= $(this).attr('href');
					if (lnk.substr(0, 1) == "#") return true;
					if (lnk=="") return false;
					$.address.value(lnk);
					return false;
				});


                $('#m_goods').load('/universe/goodsTop');

				$('#m_devices').load('/universe/devices');

			</script>
</body>
</html>