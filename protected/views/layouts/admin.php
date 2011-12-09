<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="ru" />

	<!-- blueprint CSS framework -->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/admin.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/tushkan.css" />
</head>
<body>
<?php
	$this->widget('zii.widgets.CBreadcrumbs', array(
			'links'=>$this->breadcrumbs,
		));
	$this->pageTitle=Yii::app()->name;
?>
<div class="container">
	<div id="adminleft">
		<ul>Administrator resources
			<li>Users</li>
			<li>Groups</li>
			<li><a href="<?php echo $this->createUrl('films/admin');?>">Films</a></li>
		</ul>
	</div>
	<div id="admincontent">
		<?php echo $content; ?>
	</div>
</div>

</body>
</html>