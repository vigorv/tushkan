<?php

class AdminController extends Controller
{
	public $layout = '/layouts/admin';

	public function actionIndex()
	{
		$this->breadcrumbs = array(
			Yii::t('common', 'Admin index'),
		);
		$this->render('/admin/index');
	}
}