<?php
class Controller extends CController
{
	public $layout='//layouts/index';

	public $menu = array();

	public $breadcrumbs=array();

	public $identity = null;

	public function filters()
	{
		return array(
            array(
                'application.filters.AccessFilter',
            ),
        );
	}

	public function __construct()
	{
		Yii::app()->setLanguage('ru');
		$this->identity = new UserIdentity('', '');
		$this->identity->authenticate();
	}
}