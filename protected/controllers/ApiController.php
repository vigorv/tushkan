<?php

class ApiController extends Controller {
    public $layout = '//layouts/ajax';
    
    public function BeforeAction() {
	return true;
    }

    public function actionLogin() {
	if (isset($_SERVER['HTTPS']) && !strcasecmp($_SERVER['HTTPS'], 'on')) {
	    $username = $_POST['username'];
	    $password = $_POST['password'];
	} else {
	    $this->redirect('https://' . getenv('HTTP_HOST') . '/api/login');
	}
    }

    public function actionLogout() {
	
    }

    public function actionGetUserInfo() {
	
    }

    public function actionGetDirTree() {
	
    }

    public function actionGetFileList() {
	
    }

    public function actionCreate() {
	$pid = (int) $_POST['pid'];
	$title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
	$flag_dir = (int) $_POST['flag_dir'];
	$files = new CUserfiles();
	$files->title = $title;
	$files->pid = $pid;
	$files->is_dir = $is_dir;
	$files->user_id = $this->user_id;
	$files->save();
    }

    public function actionMove() {
	$id = (int) $_POST['id'];
	$new_pid = (int) $_POST['new_pid'];
	$category = (int) $_POST['category'];
//Check is directory exists
	$place = CUserfiles::model()->findByPk(array('id' => $pid, 'user_id' => $this->user_id));
	if (($place) && ($place->is_dir)) {
	    $files = CUserfiles::model()->findByPk(array('id' => $id, 'user_id' => $this->user_id));
	    if ($files) {
		$files->pid = $new_pid;
		$files->save();
	    }
	}
    }

    public function actionRename() {
	$id = (int) $_POST['id'];
	$title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
	$files = CUserfiles::model()->findByPk(array('id' => $id, 'user_id' => $this->user_id));
	if ($files) {
	    $files->title = $title;
	    $files->save();
	}
    }

    public function actionDelete() {
	$id = (int) $_POST['id'];
	$files = CUserfiles::model()->findByPk(array('id' => $id, 'user_id' => $this->user_id));
	if ($files) {
	    $files->delete();
	}
    }

    public function actionGetUpdatesCmdList() {
	
    }

    public function actionGetSettings() {
	
    }

    public function actionSetSyncSettings() {
	
    }

}

