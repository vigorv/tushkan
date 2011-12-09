<?php
class Controller extends CController
{
	public $layout='//layouts/index';

	public $menu = array();

	public $breadcrumbs=array();

	public function __construct()
	{
		Yii::app()->setLanguage('ru');
	}
}