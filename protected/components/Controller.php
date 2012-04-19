<?php

Yii::import('ext.classes.Utils');
//Yii::import('ext.yii-detectmobilebrowser.XDetectMobileBrowser');

class Controller extends CController {

	public $layout = '//layouts/index';
	public $menu = array();
	public $breadcrumbs = array();
	public $identity = null;
	public $userPower;
	public $userGroupId;
	public $userInfo;
	public $active = 0; //СОДЕРЖИМОЕ ПОЛЯ active ТЕКУЩЕГО ОБЪЕКТА

	public function filters() {
		return array(
			array(
				'application.filters.AccessFilter',
			),
		);
	}

	public function init() {
		parent::init();
		$app = Yii::app();
		$utils = new Utils(); //ПРИНУДИТЕЛЬНО ПОДГРУЖАЕМ
		$this->identity = new UserIdentity('', '');
		$this->identity->authenticate();

		if (isset($_GET['_lang'])) {
			$app->language = $_GET['_lang'];
			$app->session['_lang'] = $app->language;
		} else if (isset($app->session['_lang'])) {
			$app->language = $app->session['_lang'];
		}
	}

	public function beforeAction($action) {
		$this->userGroupId = intval(Yii::app()->user->getState('dmUserGroupId'));
		$this->userPower = intval(Yii::app()->user->getState('dmUserPower'));
		$this->userInfo = Yii::app()->user->getState('dmUserInfo');
		if (!empty($this->userInfo)) {
			$this->userInfo = unserialize($this->userInfo);
		}

		if (Yii::app()->detectMobileBrowser->showMobile) {
			$this->layout = 'mobile';
		}
		if (Yii::app()->request->isAjaxRequest) {
			//    $this->renderPartial('_ajaxContent', $data);
			$this->layout = 'ajax';
		} else {
			//$this->layout='index';
			// $this->render('index', $data);
		}
		return true;
	}

	public function beforeRender($view) {
		$userPower = Yii::app()->user->getState('dmUserPower');
		if (!empty($this->active)) {
			if ($userPower < $this->active) {
				//Yii::app()->request->redirect('access_denied');
				//return false;
			}
		}
		return true;
	}

}