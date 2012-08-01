<div>
<a href="<?php echo $this->createUrl('/products/form');?>"><?php echo Yii::t('products', 'Add product');?></a>
|
<a href="<?php echo $this->createUrl('/products');?>"><?php echo Yii::t('products', 'Upload products list');?></a>
</div>
<?php
	$this->widget('ext.filterwidget.EFilterWidget', array('method' => 'POST', 'filterName' => 'productadmin', 'fields' => array('partners' => CPartners::getPartnerList())));
	if (!empty($products))
	{
		foreach ($products as $p)
		{
			$href = Yii::app()->createUrl('/products/edit/' . $p['id']);
			echo '<div class="chess"><a href="' . $href . '">';
			if (!empty($p['filename']))
				$poster = Yii::app()->params['tushkan']['postersURL'] . '/smallposter/' . $p['filename'];
			else
				$poster = Yii::app()->params['tushkan']['postersURL'] . '/noposter.jpg';
			echo '<img width="80" align="left" src="' . $poster . '" />';

			echo $p['title'] . '</a></div>';
		}
		$this->widget('ext.pagination.EPaginationWidget', array('params' => $paginationParams));
	}
