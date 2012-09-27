<?php

Yii::import('ext.classes.Utils');
//Yii::import('ext.yii-detectmobilebrowser.XDetectMobileBrowser');

function DebugEcho($data){
    if (YII_DEBUG === TRUE){
        echo"<pre>";
        var_dump($data);
        echo "</pre>";
    }
}


class Controller extends CController {

	public $layout = '//layouts/concept1';
	public $menu = array();
	public $breadcrumbs = array();
	public $identity = null;
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
		$this->userInfo = Yii::app()->user->getState('dmUserInfo');
		if (!empty($this->userInfo)) {
			$this->userInfo = unserialize($this->userInfo);
		}

        if (isset($_GET['mini'])){
            $this->layout='mini';
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
		if (!empty($this->active)) {
			if (Yii::app()->user->UserPower < $this->active) {
				//Yii::app()->request->redirect('access_denied');
				//return false;
			}
		}
		return true;
	}

	/**
	 * общее действие для всех контроллеров
	 * принимает параметры фильтрации из POST запроса и перенаправляет их на конечное действие GET запросом
	 *
	 */
	public function actionPostfilter()
	{
		$action = '/';
		if (!empty($_POST['action']))
		{
			$action = '/' . Yii::app()->getController()->getId() . '/' . $_POST['action'];

			$url = Utils::preparePageSortUrl($action, '', false);
			//параметр false дает нам возможность передать(не сбрасывать) параметры сортировки,
			//переданные с данными формы

			$this->redirect($url);
		}
	}


}