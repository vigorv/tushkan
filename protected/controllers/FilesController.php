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
	    $queue = CloudTaskManager::model()->getTaskForFile($fid, $this->user_id);
	    switch ($_POST['subaction']) {
		case "add":
		    if (empty($queue)) {
			$task_id = CloudTaskManager::model()->CreateFileTask(1, $fid, $this->user_id, 'x480');
		    }
		    break;
		case "cancel":
		    if (!empty($queue)) {
			$result = CloudTaskManager::model()->AbortFileTaskQueue($queue);
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
	    $item = CUserfiles::model()->getFileInfo($this->user_id, $id);
	    if (!empty($item)) {

		$queue = Yii::app()->db->createCommand()
			->select('*')
			->from('{{convert_queue}}')
			->where('id = ' . $item['id'])
			->queryRow();
	    }
	}


	$this->render('fview', array('item' => $item, 'queue' => $queue));
    }

    public function actionAdd() {

	$this->render('add');
    }

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
	$items = CUserfiles::model()->getFileListUnt($this->user_id);

	$this->render('view', array('files' => $items));
    }

    public function actionDownload() {
	$fid = (int) $_GET['fid'];
	if ($fid > 0) {
	    $item = CUserfiles::model()->findByPk(array('user_id' => $this->user_id, 'id' => $fid));
	    //        $server = CFilelocations::model()->findAllByAttributes(array('user_id' => $this->user_id, 'id' => $fid));
	    //            echo "it's file aviable on " . $server['id'];
	    $dl_server = CServers::model()->getServer(DOWNLOAD_SERVER);
	    $kpt = CUser::kpt($this->user_id);
	    $this->redirect('http://' . $dl_server . '/files/download?fid=' . $fid . '&kpt=' . $kpt . '&user_id=' . $this->user_id);
	    exit();
	} else throw new CHttpException(404,'The specified file cannot be found.');
	exit();
    }

    /* Just key for user access to other servers */

    public function actionKPT() {
	//$sid = CUser::model()->findByPk($this->user_id)->sess_id;
	//$kpt = md5($this->user_id . $sid . "I am robot");
	echo CUser::kpt($this->user_id);
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

    /**
     * Действие обработчика мультифайловой загрузки
     *
     * входные параметры передаются методом GET
     * например: /universe/receivefile?q=2
     *
     * в ответ ожидается строка в виде пары [результат] [ид файла][разделитель]
     * например: ok 101/error 102/ok 103
     *
     * ИЛИ возвращаем JSON
     *
     */
    public function actionReceivefile() {
	// e.g. url:"page.php?upload=true" as handler property
	$headers = getallheaders();
	if (
	// basic checks
		isset(
			$headers['Content-Type'], $headers['Content-Length'], $headers['X-File-Size'], $headers['X-File-Name']
		) &&
		$headers['Content-Type'] === 'multipart/form-data' &&
		$headers['Content-Length'] === $headers['X-File-Size']
	) {
	    // create the object and assign property
	    $file = new stdClass;
	    $file->name = basename($headers['X-File-Name']);
	    $file->size = $headers['X-File-Size'];
	    $file->content = file_get_contents("php://input");

	    // if everything is ok, save the file somewhere
	    if (file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/protected/runtime/' . $file->name, $file->content))
		exit('{"success" : "true", "id" : "' . $file->name . '"}');
	}

	// if there is an error this will be the output instead of "OK"
	exit('{"error" : "true"}');
    }

}
