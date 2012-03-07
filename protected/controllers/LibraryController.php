<?php

class LibraryController extends Controller {

    var $user_id;

    public function beforeAction($action) {
	parent::beforeAction($action);
	$this->user_id = Yii::app()->user->id;
	if ($this->user_id)
	    return true;
	else
	    Yii::app()->request->redirect('/register/login');
    }

    public function actionIndex() {
	$objects = CUserObjects::model()->getList($this->user_id);
	$this->render('list', array('objects' => $objects));
    }

}

?>
