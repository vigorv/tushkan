<?php

class Controller extends CController {

    public $layout = '//layouts/index';
    public $menu = array();
    public $breadcrumbs = array();

    public function init() {
        parent::init();
        $app = Yii::app();
        if (isset($_GET['_lang'])) {
            $app->language = $_GET['_lang'];
            $app->session['_lang'] = $app->language;
        } else if (isset($app->session['_lang'])) {
            $app->language = $app->session['_lang'];
        }
    }

    public function beforeAction($action) {       
        if (Yii::app()->request->isAjaxRequest) {
            //    $this->renderPartial('_ajaxContent', $data);
            $this->layout = 'ajax';
        } else {
            //$this->layout='index';
            // $this->render('index', $data);
        }
        return true;
    }

}