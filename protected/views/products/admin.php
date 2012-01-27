<div>
<a href="<?php echo $this->createUrl('/products/form');?>"><?php echo Yii::t('products', 'Add product');?></a>
|
<a href="<?php echo $this->createUrl('/products');?>"><?php echo Yii::t('products', 'Upload products list');?></a>
</div>
<?php
	if (!empty($products))
	{
		foreach ($products as $p)
		{
			$href = Yii::app()->createUrl('/products/edit/' . $p['id']);
			echo '<div class="shortproduct"><a href="' . $href . '">';
			if (!empty($p['filename']))
				$poster = Yii::app()->params['tushkan']['postersURL'] . '/smallposter/' . $p['filename'];
			else
				$poster = Yii::app()->params['tushkan']['postersURL'] . '/noposter.jpg';
			echo '<img src="' . $poster . '" />';

			echo $p['title'] . '</a>';
		}
	}

$pager = $this->beginWidget('CLinkPager');
$pages->pageVar = 'page';
$pages->params = array('srt' => 'title', 'dir' => 'asc');
$pager->pages = $pages;
$this->endWidget();