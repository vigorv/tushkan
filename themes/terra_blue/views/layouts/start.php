<html lang="ru">
<head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="language" content="ru" />

		<link href="/css/bootstrap.min.css" rel="stylesheet">
		<link href="/css/form.css" rel="stylesheet">
		<style>body {padding-top: 25px; background: #ededed url('/img/body-bg.png') top left repeat-x;}</style>
		<!--[if IE]>
			<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<script src="/js/bootstrap.min.js"></script>
        <title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>
<body>
	<div class="container">
<?php echo $content; ?>
	</div>
    <div id="bottom" class="span12 no-horizontal-margin"><center>
        <!--LiveInternet counter--><script type="text/javascript"><!--
    document.write("<a href='http://www.liveinternet.ru/click' "+
        "target=_blank><img src='//counter.yadro.ru/hit?t14.6;r"+
        escape(document.referrer)+((typeof(screen)=="undefined")?"":
        ";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?
            screen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+
        ";"+Math.random()+
        "' alt='' title='LiveInternet: показано число просмотров за 24"+
        " часа, посетителей за 24 часа и за сегодня' "+
        "border='0' width='88' height='31'><\/a>")
    //--></script><!--/LiveInternet-->
        </center>
        </div>
</body>
</html>