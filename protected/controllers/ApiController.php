<?php

/**
 *  ApiController
 * @author Snow
 *
 */
class ApiController extends Controller {

    public $layout = '//layouts/ajax';
    var $user_id;

    public function beforeAction($action) {
	parent::beforeAction($action);
	$this->user_id = Yii::app()->user->id;
	if ($this->user_id)
	    return true;
	else
	    Yii::app()->request->redirect('/register/login');
    }

    private function XmlRender() {
	
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
	//$user= CUser::model()->getU
	echo "No info";
    }

    public function actionGetDirTree() {
	$dirs = CUserfiles::model()->getDirTree($user_id);
	echo CXmlHandler::arrayToXml($dirs);
    }

    public function actionGetFileList() {
	$pid = 0;
	$files = CUserfiles::model()->getFileList($this->user_id, $pid);
	echo CXmlHandler::arrayToXml($files);
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
	$xml_data->save();
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
		echo "OK: Moved";
	    }else
		echo "ERROR: Unknown file";
	}else
	    echo "ERROR: Unknown place";
    }

    public function actionRename() {
	$id = (int) $_POST['id'];
	$title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
	$files = CUserfiles::model()->findByPk(array('id' => $id, 'user_id' => $this->user_id));
	if ($files) {
	    $files->title = $title;
	    $files->save();
	    echo "OK: Renamed";
	}
	else
	    echo "ERROR: unknown file";
    }

    public function actionDelete() {
	$id = (int) $_POST['id'];
	$files = CUserfiles::model()->findByPk(array('id' => $id, 'user_id' => $this->user_id));
	if ($files) {
	    $files->delete();
	    echo "OK: Deleted";
	}
	else
	    echo "ERROR: unknown file";
    }

    public function actionGetUpdatesCmdList() {
	echo "No Updates";
    }

    public function actionGetSettings() {
	echo "No Settings";
    }

    public function actionSetSyncSettings() {
	if (isset($_POST['data'])) {
	    $data = $_POST['data'];
	}else
	    echo "ERROR: no data";
    }

}

