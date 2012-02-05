<?php

class FilesController extends Controller {

    var $user_id;

    public function beforeAction($action) {
	parent::beforeAction($action);
	$this->user_id = Yii::app()->user->id;
	if ($this->user_id)
	    return true;
	else
	    Yii::app()->request->redirect('/register/login');
    }

    /**
     *
     * @param int $pid        Parent id
     * @param str $title      ViewName
     * @param int $is_dir   1|0 is directory
     */
    public function FCreate($pid, $title, $is_dir) {
	$files = new CUserfiles();
	$files->title = $title;
	$files->pid = $pid;
	$files->is_dir = $is_dir;
	$files->user_id = $this->user_id;
	$files->save();
    }

    /**
     * Move to folder $pid
     * @param type $id
     * @param type $pid
     */
    public function FMove($id, $pid) {
//  TO DO :check
	CUserfiles::model()->updateByPk(array('user_id' => $this->user_id, 'id' => $id), 'pid=' . $pid);
	echo "OK";
    }

    /**
     *
     * @param type $id
     * @param type $file
     * @return type
     */
    public function FRemove($id, $file=null) {
	if ($file == null)
	    $file = CUserfiles::model()->findByPk(array('user_id' => $this->user_id, 'id' => $id));
	if ($file == null)
	    die("unknown file");
	if ($file->is_dir) {
	    $filelist = CUserfiles::model()->findAllByAttributes(array('user_id' => $this->user_id, 'pid' => $file->id));
	    foreach ($filelist as $file) {
		$this->fremove($id, $file);
	    }
	}

	$files = CFilelocations::model()->findAllByAttributes(array('user_id' => $this->user_id, 'id' => $file->id));
	foreach ($files as $fileloc) {
	    // CServers::model()->sendComand('delete', $files->server_id, array('fname' => $files->fname, 'folder' => $files->folder))
	    $fileloc->delete();
	    //echo "deleted location\n";
	}
	if ($file <> null) {
	    $file->delete();
	}
	//   echo "deleted meta\n";
	return true;
    }

    /**
     *
     * @param type $id
     */
    public function actionFOpen($id=0) {
	if ($id > 0)
	    $item = CUserfiles::model()->findByPk(array('user_id' => $this->user_id, 'id' => $id));
	if (($id == 0) || ($item->is_dir)) {
	    $flist = CUserfiles::model()->findAllByAttributes(array('user_id' => $this->user_id, 'pid' => $id), array('select' => 'id,pid,title,is_dir'));
	    if (true) {
		echo CFiletypes::ParsePrint($flist, 'FL1');
		exit;
	    } else {
//$this
	    }
	} else {
	    switch (filetype($item->title)) {
		default:
		    echo "Open files support comes later";
	    }
	}
    }

    /**
     * действие добавление в очередь на конвертацию
     * принимает параметры в $_POST
     *
     */
    public function actionQueue() {
	$task_id = 0;
	if (!empty($_POST['id'])) {
	    $fid = (int) $_POST['id'];
	    $cmd = Yii::app()->db->createCommand()
		    ->select('*')
		    ->from('{{convert_queue}}')
		    ->where('id = :id AND user_id = ' . $this->user_id);
	    $cmd->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
	    $queue = $cmd->queryRow();

	    switch ($_POST['subaction']) {
		case "add":
		    if (empty($queue)) {
			//ЕСЛИ В ОЧЕРЕДИ НЕТ ДАННОГО ФАЙЛА
			$task_id = CloudTaskManager::model()->CreateTaskConvert(QUEUE_CONVERTER, $fid, 'x480');
			//$task_id = 1; //ЗАГЛУШКА ВЫЗОВА КОНВЕРТОРА
			//$task_id = file_get_contents('[АДРЕС КОНВЕРТОРА]');//ВЫЗОВ КОНВЕРТОРА
			$sql = 'INSERT INTO {{convert_queue}} (id, user_id, task_id) VALUES (:id, ' . $this->user_id . ', ' . $task_id . ')';
			$cmd = Yii::app()->db->createCommand($sql);
			$cmd->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
			$cmd->execute();
		    }
		    break;
		case "cancel":
		    if (!empty($queue)) {
			$task_id = $queue['task_id'];
			$result = CloudTaskManager::model()->AbortTaskConvert(QUEUE_CONVERTER, $task_id);
			//$canceled_task_id = $task_id; //ЗАГЛУШКА
			//$canceled_task_id = file_get_contents('[АДРЕС КОНВЕРТОРА]');//ВЫЗОВ КОНВЕРТОРА ДЛЯ ОТМЕНЫ ОПЕРАЦИИ
			//if ($task_id == $canceled_task_id) {
			if ($result) {
			    $sql = 'DELETE FROM {{convert_queue}} WHERE id=' . $queue['id'];
			    Yii::app()->db->createCommand($sql)->execute();
			}
		    }
		    break;
	    }
	}
	$this->render('fview', array('task_id' => $task_id));
    }

    /**
     * действие: детальная информация о файле, интерфейс управления
     * @param integer $id - ид файла
     */
    public function actionFview($id = 0) {
	$item = $queue = array();
	if ($id > 0) {
	    $item = CUserfiles::model()->findByPk(array('user_id' => $this->user_id, 'id' => $id));
	    if (!empty($item)) {
		$queue = Yii::app()->db->createCommand()
			->select('*')
			->from('{{convert_queue}}')
			->where('id = ' . $item['id'] . ' AND user_id = ' . $this->user_id)
			->queryRow();
	    }
	}
	if (($id == 0) || ($item->is_dir)) {
	    $flist = CUserfiles::model()->findAllByAttributes(array('user_id' => $this->user_id, 'pid' => $id), array('select' => 'id,pid,title,is_dir'));
	    if (true) {
		echo CFiletypes::ParsePrint($flist, 'FL1');
		exit;
	    } else {
		//$this
	    }
	} else {
	    $this->render('fview', array('item' => $item, 'queue' => $queue));
	}
    }

    /**
     *  Upload page


      public function actionAdd() {
      $UPLOAD_SERVER = 2;
      $DOWNLOAD_SERVER = 2;
      $user_id = Yii::app()->user->id;
      $up_server = $this->GetServer($UPLOAD_SERVER);
      $dl_server = $this->GetServer($DOWNLOAD_SERVER);
      $this->render('add', array('user_id' => $user_id, 'kpt' => $kpt, 'upload_server' => $up_server, 'download_server' => $dl_server));
      }
     */

    /**
     * Handle Ajax tree
     */
    public function actionAjaxFoldersList() {
	if (!Yii::app()->request->isAjaxRequest) {
//exit();
	}
	$parentId = 0;
	if (isset($_GET['root']) && $_GET['root'] !== 'source') {
	    $parentId = (int) $_GET['root'];
	} else {
	    $data = array(array(
		    'text' => 'All files',
		    'id' => "0",
		    ));
	}

	$req = Yii::app()->db->createCommand('SELECT uf.id as id, uf.title AS text, uf2.id is not null  as hasChildren '
		. 'FROM {{userfiles}} AS uf  LEFT JOIN {{userfiles}} as uf2  on uf.id = uf2.pid and uf2.is_dir = 1 and uf.user_id =' . $this->user_id
		. " WHERE uf.pid <=> $parentId and uf.user_id <=> $this->user_id and uf.is_dir = 1 "
		. 'GROUP BY uf.id order by text ASC'
	);
	if (isset($data))
	    $data[0]['children'] = $req->queryAll();
	else
	    $data = $req->queryAll();
	echo str_replace(
		'"hasChildren":"0"', '"hasChildren":false', CTreeView::saveDataAsJson($data)
	);
	exit();
    }

    public function actionIndex() {
	$up_server = CServers::model()->getServer($UPLOAD_SERVER);
	$this->render('view', array('user_id' => $this->user_id, 'up_server' => $up_server));
    }

    public function actionDownload() {
	$fid = (int) $_GET['fid'];
	if ($fid > 0)
	    $item = CUserfiles::model()->findByPk(array('user_id' => $this->user_id, 'id' => $fid));
	if ($item->is_dir == 0) {
	    //        $server = CFilelocations::model()->findAllByAttributes(array('user_id' => $this->user_id, 'id' => $fid));
	    //            echo "it's file aviable on " . $server['id'];
	    $dl_server = Yii::app()->params['tushkan']['dl_server'];
	    //die (CUser::model()->findByPk($this->user_id)->sess_id);
	    $sid = CUser::model()->findByPk($this->user_id)->sess_id;
	    $kpt = md5($this->user_id . $sid . "I am robot");
	    $this->redirect('http://' . $dl_server . '/files/download?fid=' . $fid . '&kpt=' . $kpt . '&user_id=' . $this->user_id);
	    exit();
	} else {
	    echo "It's not aviable to download folder via browser for this moment";
	}
	exit();
    }

    /* Just key for user access to other servers */
    
    public function actionKPT() {
	$sid = CUser::model()->findByPk($this->user_id)->sess_id;
	$kpt = md5($this->user_id . $sid . "I am robot");
	echo $kpt;
	exit();
    }

    public function actionCreate($fid=0) {
	$model = new FilesCreateForm();
	if (isset($_POST['FilesCreateForm'])) {
// collects user input data
	    $model->attributes = $_POST['FilesCreateForm'];
// validates user input and redirect to previous page if validated
	    echo "Validating";
	    if ($model->validate()) {
		$files = new CUserfiles();
		$files->attributes = $model->attributes;
		echo "OK. Let's Create";
	    }
	}
// displays the login form
	$this->render('create', array('model' => $model, 'pid' => (int) $fid));
    }

    public function actionTypes($fid) {
	$this->renders('types');
    }

    public function actionRemove() {
	//TO DO:delete all files
	if (!isset($_POST['id']))
	    die("what?");
	$id = (int) $_POST['id'];
	if ($id < 1)
	    die("unknown file");
	$this->fremove($id);
	echo "OK";
    }

}

?>
