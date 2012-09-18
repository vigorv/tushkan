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
    public function actionFOpen($id = 0) {
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
     * добавление в очередь на конвертацию
     * принимает параметры в $_POST
     *
     */
    public function actionQueue() {
        $task_id = 0;
        if (!empty($_POST['id'])) {
            $fid = (int) $_POST['id'];
            $preset = filter_var($_POST['preset']);
            $fileinfo = CUserfiles::model()->getFileInfonfo($this->user_id, $fid);
            // Is supported convert
            if ($type_id = Utils::IsConvertingCorrect($fileinfo['title'], $preset)) {
                // is converted before
                if (CFilesvariants::model()->findAllByAttributes(array('file_id' => fid, 'preset_id' => $preset))) {
                    $answer = array('operation' => 'ADD', result => 'Variant exists', "err_code" => 3);
                } else {
                    $queue = CloudTaskManager::model()->getTaskForFile($fid, $this->user_id);
                    switch ($_POST['subaction']) {
                        case "add":
                            if (empty($queue)) {
                                $task_id = CloudTaskManager::model()->CreateFileTask(1, $fid, $this->user_id, 'x480');
                                if ($task_id) {
                                    $answer = array('operation' => 'ADD', 'result' => 'ok');
                                } else
                                    $answer = array('operation' => 'ADD', 'result' => 'bad', "err_code" => 1);
                            } else {
                                $answer = array('operation' => 'ADD', 'result' => 'Task Exists', "err_code" => 2);
                            }
                            break;
                        case "cancel":
                            if (!empty($queue)) {
                                $result = CloudTaskManager::model()->AbortFileTaskQueue($queue);
                                $answer = array('operation' => 'CANCEL', 'result' => 'ok');
                            }
                            break;
                    }
                }
                if (!empty($answer)) {
                    echo json_encode($answer);
                    return;
                }
            }
        }
        $this->render('fview', array('task_id' => $task_id));
    }

    public function actionAdd() {

        $this->render('add');
    }

    /**
     * Handle Ajax tree
     *
     * Deprecated
     */
    /*
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
      } */

    public function actionIndex() {
        $items = CUserfiles::model()->getFileListUnt($this->user_id);

        $this->render('view', array('files' => $items));
    }

    public function actionDownload() {
        if (isset($_GET['vid']) && ((int)$_GET['vid']>0)){
        $variant_id = (int) $_GET['vid'];
            $allowed_download = CUserfiles::DidUserHaveVariant(Yii::app()->user->id,$variant_id);
            if ($allowed_download){
                $server = CFileservers::getDownloadServerForUserFile($variant_id);
                $sign = CUser::getDownloadSign($variant_id.$this->user_id);
                if ($server){
                    if ($server['alias'] == '')
                        $this->redirect('http://' .$server['ip'].':'.$server['port'].'/files/download?vid=' . $variant_id. '&user_id=' . $this->user_id .'&key='.$sign);
                    else
                        $this->redirect('http://' .$server['alias'].':'.$server['port'].'/files/download?vid=' . $variant_id . '&user_id=' . $this->user_id .'&key='.$sign);
                }
            exit();
            } else {
                throw new CHttpException(403);
            }
        } else {
            throw new CHttpException(404, 'The specified file cannot be found.');
        }
    }

    /* Just key for user access to other servers */

    public function actionKPT() {
//$sid = CUser::model()->findByPk($this->user_id)->sess_id;
//$kpt = md5($this->user_id . $sid . "I am robot");
        echo CUser::kpt($this->user_id);
        exit();
    }

    /* AJAX LISTS */

    public function actionAjaxUntypedList($page = 1, $per_page = 100) {
        if (!Yii::app()->request->isAjaxRequest) {
            echo '<form method="post" action="/files/removeAll">
		<input type="submit" name="removeAll" value="delete ALL"/>
		</form>
';
        }
        $page = abs((int) $page);
        $per_page = abs((int) $per_page);
        $mb_content_items_unt = CUserfiles::model()->getFileListUnt($this->user_id, $page, $per_page);
        $this->render('items_unt', array('mb_content_items_unt' => $mb_content_items_unt));
//}
    }

    /**
     * действие: детальная информация о файле, интерфейс управления
     * @param integer $id - ид файла
     */
    public function actionFview($id) {
        //if (Yii::app()->request->isAjaxRequest) {
        $variants = $item = $queue = array();

        if ($id > 0) {
            $item = CUserfiles::model()->getFileInfo($this->user_id, $id);
            // TO DO: make zones
            $zone = 0;
            if (!empty($item)) {
	            $variants = CUserfiles::model()->GetVarWithLoc($item['id'], $zone);
                $queue = Yii::app()->db->createCommand()
                	->select('*')
                	->from('{{income_queue}}')
                	->where('cmd_id < 50 AND original_id = ' . $item['id'] . ' AND partner_id = 0')
                	->queryAll();

				$qstContent = $this->renderPartial('/universe/queue', array('qst' => $queue), true);
            }
        }
        $this->render('fview', array('item' => $item, 'queue' => $queue, 'qstContent' => $qstContent, 'variants' => $variants));
    }

    /* Deprecated
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
     */

    public function actionTypes($fid) {
        $this->renders('types');
    }

    public function actionStartconvert()
    {
        if (!empty($_POST['id']))
        {
        	$userId = Yii::app()->user->getId();
        	//ВЫБИРАЕМ ИНФУ О ФАЙЛЕ И ЕГО ВАРИАНТАХ, КОТОРЫЕ ЕЩЕ НЕСКОНВЕРТИРОВАНЫ (preset_id=0)
        	$cmd = Yii::app()->db->createCommand()
        		->select('uf.id, fv.preset_id, fl.fname')
        		->from('{{userfiles}} uf')
        		->join('{{files_variants}} fv', 'fv.file_id=uf.id')
        		->join('{{filelocations}} fl', 'fl.id=fv.id')
        		->where('uf.id = :id AND fv.preset_id=0');
        	$cmd->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
        	$fInfo = $cmd->queryRow();

        	//ПРОВЕРЯЕМ ВОЗМОЖНО ЛИ КОНВЕРТИРОВАИНЕ
        	if (!empty($fInfo))
        	{
                $mediaList = Utils::getMediaList();
                $fi = pathinfo(strtolower($fInfo['fname']));
				if (!empty($fi['extension']) && !empty($mediaList[1]['exts']) && in_array($fInfo['extension'], $mediaList[1]['exts']))
				{
					$partnerId = 0;
					$queue = array(
						'id'			=> null,
						'product_id'	=> 0,
						'original_id'	=> $fInfo['id'],
						'task_id'		=> 0,
						'cmd_id'		=> 0,
						'info'			=> "",
						'priority'		=> 200,
						'state'			=> 0,
						'station_id'	=> 0,
						'partner_id'	=> $partnerId,
						'user_id'		=> $userId,
						'original_variant_id'	=> 0,
					);
					$cmd = Yii::app()->db->createCommand()->insert('{{income_queue}}', $queue);
					$result = 'queue';
					echo $result;
				}
			}
        }
    }

    public function actionCancelconvert() {
//TO DO:delete all files
        if (!empty($_POST['id']))
        {
	        CConvertQueue::model()->deleteUserQueue($this->user_id, $_POST['id']);
        }
    }

    public function actionRestartqueue() {
//TO DO:delete all files
        if (!empty($_POST['id']))
        {
	        CConvertQueue::model()->restartUserQueue($this->user_id, $_POST['id']);
        }
    }

    public function actionRemove() {
//TO DO:delete all files
        if (!isset($_POST['id']))
            die("what?");
        $id = (int) $_POST['id'];
        if ($id < 1)
            die("unknown file");
        CUserfiles::model()->RemoveFile($this->user_id, $id);

        echo "OK";
    }

    /**
     * remove All untyped files
     */
    public function actionRemoveAll() {
        if (!isset($_POST['removeAll']))
            die('what?');
        CUserfiles::model()->RemoveAllFiles($this->user_id);
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
