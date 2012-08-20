<div>
<a href="<?php echo $this->createUrl('/products/form');?>"><?php echo Yii::t('products', 'Add product');?></a>
|
<a href="<?php echo $this->createUrl('/products');?>"><?php echo Yii::t('products', 'Upload products list');?></a>
</div>
<?php
	$flashes = Yii::app()->user->getFlashes();
	if (!empty($flashes['success']))
	{
		$msg = '<div id="flashDiv" class="alert alert-success">
			<a class="close" data-dismiss="alert" href="#">×</a>
			<h4 class="alert-heading">Ок!</h4>
			' . $flashes['success'] . '
		</div>';
	}
	if (!empty($flashes['error']))
	{
		$msg = '<div id="flashDiv" class="alert alert-error">
			<a class="close" data-dismiss="alert" href="#">×</a>
			<h4 class="alert-heading">Error!</h4>
			' . $flashes['error'] . '
		</div>';
	}
if (!empty($msg))
{
	echo $msg;
}

	$this->widget('ext.filterwidget.EFilterWidget', array('method' => 'POST', 'filterName' => 'productadmin', 'fields' => array('partners' => CPartners::getPartnerList())));
	if (!empty($products))
	{
?>
	<form name="massForm" action="/products/group" method="POST">
<?php
		foreach ($products as $p)
		{
			$href = Yii::app()->createUrl('/products/edit/' . $p['id']);
			echo '<div class="chess">';
			echo '<input type="checkbox" name="group_ids[' . $p['id']. ']" />';
			echo '<a href="' . $href . '">';
			if (!empty($p['filename']))
				$poster = Yii::app()->params['tushkan']['postersURL'] . '/smallposter/' . $p['filename'];
			else
				$poster = Yii::app()->params['tushkan']['postersURL'] . '/noposter.jpg';
			echo '<img width="80" align="left" src="' . $poster . '" />';

			echo $p['title'] . '</a></div>';
		}
		$this->widget('ext.pagination.EPaginationWidget', array('params' => $paginationParams));
?>
	<select name="operation">
		<option value="0">действие с отмеченными</option>
		<option value="1">объединить</option>
		<option value="2">скрыть</option>
		<option value="3">удалить</option>
	</select>
	<br /><input class="btn" type="submit" value="Выполнить" />
	</form>
<?php
	}
