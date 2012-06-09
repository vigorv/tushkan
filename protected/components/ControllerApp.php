<?php

//Yii::import('ext.classes.Utils');
//Yii::import('ext.yii-detectmobilebrowser.XDetectMobileBrowser');

/**
 *
 *
 */

class ControllerApp extends CController {

    public $layout = '//layouts/ajax';
    public $menu = array();
    public $breadcrumbs = array();
    public $identity = null;
    public $userInfo;
    public $active = 0; //���������� ���� active �������� �������

    public function init() {
        parent::init();
        Yii::app()->errorHandler->errorAction='app/error';
    //        $this->identity = new UserIdentity('', '');
    //    $this->identity->authenticate();
        if (isset($_GET['_lang'])) {
            Yii::app()->language = $_GET['_lang'];
            Yii::app()->session['_lang'] = Yii::app()->language;
        } else if (isset(Yii::app()->session['_lang'])) {
            Yii::app()->language = Yii::app()->session['_lang'];
        }




    }

    public function beforeAction($action) {
        $this->userInfo = Yii::app()->user->getState('dmUserInfo');
        if (!empty($this->userInfo)) {
            $this->userInfo = unserialize($this->userInfo);
        }

        return true;
    }


}