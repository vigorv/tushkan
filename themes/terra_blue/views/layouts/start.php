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
</body>
</html>