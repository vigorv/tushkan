<?php

class LibraryController extends Controller {

    var $user_id;

    public function actionIndex() {
    	$objects = CUserObjects::model()->getList($this->user_id);
	    $this->render('list', array('objects' => $objects));
    }

}

?>
