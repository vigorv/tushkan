<?php

class ServersyncController extends Controller {

    var $layout = 'ajax';
    var $server = null;

    public function beforeAction($action) {
	parent::beforeAction($action);
//    return true;
	$shash = $_GET['shash'];
	$hash_local = md5(date('%h%d') . 'where am i');
	if ($shash <> $hash_local) {
	    //var_dump($shash);
	    echo 'bye';
	    return false;
	}
	$ip = CServers::convertIpToLong($_SERVER['REMOTE_ADDR']);

	//zaglushka
	//$ip = CServers::convertIpToLong('192.168.201.163');

	$this->server = CServers::model()->findByAttributes(array('ip' => $ip));
	if ($this->server === null)
	    die('Unknown Server ' . $_SERVER['REMOTE_ADDR']);
		Yii::log(print_r($_GET,true),CLOGGER::LEVEL_TRACE,'snow');
	return true;
    }

    /**
     * GET  USERINFO
     * @param int $user_id 
     */
    public function actionUserdata($user_id=0) {
	$uid = (int) $user_id;
	if ($uid > 0) {
	    $response = array();
	    $response['sid'] = CUser::model()->findByPk($uid)->getAttribute('sess_id');
	    echo serialize($response);
	}
	exit();
    }

    public function actionFiledata($user_id=0) {
	$user_id = (int) $user_id;
	if ($user_id > 0) {
	    if (!isset($_GET['data']))
		die("Data is not enough");
	    $data = @unserialize($_GET['data']);
	    if ($data) {
		$fid = (int) $data['fid'];
		$stype = (int) $data['stype'];
		$user_ip = (int) $data['user_ip'];
		$zone = CZones::model()->GetZoneByIp($user_ip);
		$filemeta = CUserfiles::model()->getFileMeta($user_id, $fid);
		if ($filemeta) {
		    $response['title'] = $filemeta['title'];
		    $variant = CUserfiles::model()->getFileVariantUser($fid);
		    if ($variant) {
			$filedata = CUserfiles::model()->getFileLocUser($variant['id'], $zone);

			foreach ($filedata as $file) {
			    $fdata = array();
			    $fdata['ip'] = $file['ip'];
			    $fdata['port'] = $file['port'];
			    $fdata['name'] = $file['fname'];
			    $fdata['size'] = $file['fsize'];
			    $response['filedata'][] = $fdata;
			}
		    } else 
			$response['error'] = 'no variants';
		} else {
		    $response['error'] = 'unknown file';
		}
	    } else {
		$response['error'] = 'bad data';
	    }
	    echo (serialize($response));
	} else
	    die('bye bye');
	exit;
    }

    /**
     * actionCreateMetaFile
     * @param int $user_id
     * @param string$data 
     */
    public function actionCreateMetaFile($user_id=0, $data='') {
	if ($user_id > 0) {
//OK 
	    $input = @unserialize($data);
	    if (!($input === false)) {
		$files = new CUserfiles();
		$files->title = base64_decode($input['title']);
		$files->user_id = $user_id;
		$ext = pathinfo($files->title, PATHINFO_EXTENSION);
		$files->type_id = Utils::getSectionIdByExt($ext);
		if ($files->type_id > 0) {
		    if ($files->save())
			$result = array('fid' => $files->id);
		    else
			$result = array('error' => "Can't save record");
		} else
		    $result = array('error' => "Unsupported filetype " . $ext, 'error_code' => 1);
	    } else
		$result = array('error' => 'Bad input data');
	    echo serialize($result);
	    exit;
	}
	exit;
    }

    /**
     * actionCreateFileVariant
     * @param int $user_id
     * @param string $data 
     */
    public function actionCreateFileVariant($data='') {
	$input = @unserialize($data);
	if (!($input === false)) {
	    $file_variant = new CFilesvariants();
	    $file_variant->file_id = $input['fid'];
	    $preset_id = 0; //CPresets::model()->findPresetByName($data['preset']);
	    $file_variant->preset_id = $preset_id;
	    $file_variant->fmd5 = $input['fmd5'];
	    $file_variant->fsize = $input['fsize'];
	    if ($file_variant->save())
		$result = array('variant_id' => $file_variant->id);
	    else
		$result = array('error' => "Can't save record");
	} else
	    $result = array('error' => 'Bad input data');
	echo serialize($result);
	exit;
    }

    /**
     * actionCreateFileLocation
     * @param int $user_id
     * @param string $data 
     */
    public function actionCreateFileLocation($data='') {
	$input = @unserialize($data);
	if (!($input === false)) {
	    $file_location = new CFilelocations();
	    $file_location->id = $input['variant_id'];
	    $file_location->fname = $input['sname'];
	    $file_location->fsize = $input['fsize'];
	    if (isset($input['modified']))
		$file_location->modified = $input['modified'];
	    if (isset($input['folder']))
		$file_location->folder = $input['folder'];
	    $file_location->server_id = $this->server->id;
	    if ($file_location->save())
		$result = array('file_location_id' => $file_location->id);
	    else
		$result = array('error' => "Can't save record");
	} else
	    $result = array('error' => 'Bad input data');
	echo serialize($result);
	exit;
    }

    public function actionCreateConvertTask($data='') {
	$input = @unserialize($data);
	if (!($input === false)) {
	    $cqueue = new CConvertQueue();
	    $cqueue->id = $data['fid'];
	    $preset_id = 0; //CPresets::model()->findPresetByName($data['preset']);
	    $cqueue->preset_id = $preset_id;
	    $cqueue->server_id = $this->server->id;
	    $cqueue->task_id = $data['job_id'];
	    if ($cqueue->save())
		$result = array('queue_id' => $cqueue->id);
	    else
		$result = array('error' => "Can't save record");
	} else
	    $result = array('error' => 'Bad input data');
	echo serialize($result);
	exit;
    }

    public function actionCompleteConvertTask($data='') {
	$input = @unserialize($data);
	if (!($input === false)) {
	    $cqueue = CConvertQueue::model()->findByAttributes(array('task_id' => (int) $data['job_id']));
	    if ($cqueue) {

		//CompleteConverTask
		// 1. For What file is
		if ($cqueue->file_id > 0) {
		    $file_id = $cqueue->file_id;
		    //Create variant
		    $file_variant = new CFilesvariants();
		    $file_variant->fsize = $input['fsize'];
		    $file_variant->fmd5 = $input['fmd5'];
		    $file_variant->preset_id = $cqueue->preset_id;
		    $file_variant->file_id = $file_id;

		    if ($file_variant->save()) {
			$file_loc = new CFilelocations();
			$file_loc->id = $file_variant->id;
			$file_loc->server_id = $this->server->id;
			$file_loc->fsize = $input['fsize'];
			$file_loc->fname = $input['fname'];
			//$file_loc->modified=now();
			if ($file_loc->save()) {
			    $result = array('success' => 'Location created');
			    $cqueue->delete();
			} else
			    $result = array('error' => 'Location not created');
		    } else
			$result = array('error' => 'file_variant not created');
		} else
		    $result = array('error' => 'unknown file');
	    } else
		$result = array('error' => 'Unknown task');
	} else
	    $result = array('error' => 'Bad input data');
	echo serialize($result);
	exit;
    }

    ///Deprecated Upload

    public function actionUpload($user_id=0, $data='') {
	if ($user_id > 0) {
//OK 


	    $input = @unserialize($data);
	    if (!($input === false)) {

		$new_title = base64_decode($input['filename']);
		$cur_file = CUserfiles::model()->findAllByAttributes(array('user_id' => $user_id, 'title' => $input['filename'], 'pid' => $input['pid']));
		$i = 1;
		while (count($cur_file)) {
		    $new_title = pathinfo($input['filename'], PATHINFO_FILENAME) . $i . '.' . pathinfo($input['filename'], PATHINFO_EXTENSION);
		    $cur_file = CUserfiles::model()->findAllByAttributes(array('user_id' => $user_id, 'title' => $new_title, 'pid' => $input['pid']));
		    $i++;
		}
		$files = new CUserfiles();
		$files->title = $new_title;
		$files->pid = $input['pid'];
		$files->fsize = $input['fsize'];
		$files->user_id = $user_id;
		$files->save();
		$fileloc = new CFilelocations();
		$fileloc->id = $files->id;
		$fileloc->server_id = $this->server->id;
		$fileloc->user_id = $user_id;
		$fileloc->fsize = $files->fsize;
		$fileloc->fname = $input['save'];
		if (isset($input['folder']))
		    $fileloc->folder = (int) $input['folder'];
		$fileloc->save();
		$result = array();
		$result['fid'] = $files->id;
		echo serialize($result);
		exit();
	    } else {
		$result = array('error' => 'bad data format');
		echo serialize($result);
		exit();
	    }
	} else
	    die("Bad User");
    }

    public function actionDownload($user_id=0) {
	if ($user_id > 0) {
//OK 
//WHat is server doing this

	    if (!isset($_GET['data']))
		die('not enough data');
	    $input = @unserialize($_GET['data']);
	    if ($input) {
		$fileloc = new CFilelocations();
		$fileloc->id = $input['fid'];
		$fileloc->server_id = $this->server['id'];
		$fileloc->user_id = $user_id;
		$fileloc->fsize = $input['fsize'];
		$fileloc->fname = $input['save'];
		if (isset($input['folder']))
		    $fileloc->folder = (int) $input['folder'];
		$fileloc->save();
		echo "OK";
		exit();
	    } else {
		echo "Bad data";
		exit();
	    }
	} else
	    die("Bad User");
    }

    public function actionTypify($user_id=0, $data='') {
	if ($user_id > 0) {
	    $input = @unserialize($data);
	    if (!($input === false)) {
		$result = 1;
//$folder = $convertInfo['folder'];
		$server_id = $this->server->id;
		$fid = (int) $input['file_id'];
		$filename = $input['save'];
		$fsize = $input['fsize'];
		$task_id = (int) $input['task_id'];
		if ($task_id > 0) {
		    $queue = CConvertQueue::model()->findByAttributes(array('task_id' => $task_id));
		    if (!(queue == null)) {//ЕСЛИ ЕСТЬ ИНФО О ЗАДАНИИ
//ПРОВЕРКА РЕЗУЛЬТАТА ТИПИЗАЦИИ
			if (!empty($result)) {
//ОБРАБОТКА ОШИБКИ ТИПИЗАЦИИ
			} else {
//ЧТЕНИЕ ИНФО О ФАЙЛЕ
			    $cmd = Yii::app()->db->createCommand()
				    ->select('*')
				    ->from('{{userfiles}}')
				    ->where('id = ' . $queue['id']);
			    $fileInfo = $cmd->queryRow();
//ЧТЕНИЕ ИНФО О ЛОКАЦИИ ФАЙЛА
			    $cmd = Yii::app()->db->createCommand()
				    ->select('*')
				    ->from('{{filelocations}}')
				    ->where('id = ' . $queue['id']);
			    $locInfo = $cmd->queryRow();
//ЧТО ДЕЛАТЬ С ЗАПИСЯМИ О ФАЙЛЕ? УТОЧНИТЬ
//СОЗДАНИЕ ЗАПИСИ ТИПИЗИРОВАННОГО ОБЪЕКТА
			    $objInfo = array(
				'id' => $fid,
				'user_id' => $user_id,
				'title' => $filename,
				'type_id' => $queue->preset_id,
			    );

//CREATE METAFILE
			    $sql = 'INSERT INTO {{typedfiles}} (id, variant_id, user_id, fsize, title, userobject_id)
			    		VALUES (null, 0, ' . $objInfo['user_id'] . ', :fsize, "' . $objInfo['title'] . '", ' . $objInfo['id'] . ')';
			    $cmd = Yii::app()->db->createCommand($sql);
			    $cmd->bindParam(':fsize', $fsize, PDO::PARAM_LOB);
			    $cmd->execute();

//CREATE FILELOC
			    $sql = 'INSERT INTO {{usertobjects}} (id, user_id, title, type_id)
			    		VALUES (' . $objInfo['id'] . ', ' . $objInfo['user_id'] . ', "' . $objInfo['title'] . '", ' . $objInfo['type_id'] . ')';
			    Yii::app()->db->createCommand($sql)->execute();

//ВЫБИРАЕМ ПЕРЕЧЕНЬ ПАРАМЕТРОВ ДЛЯ ОБЪЕКТОВ ДАННОГО ТИПА
			    $cmd = Yii::app()->db->createCommand()
				    ->select('ptp.id, ptp.title')
				    ->from('{{product_type_params}} ptp')
				    ->join('{{product_types_type_params}} pttp', 'ptp.id = pttp.param_id')
				    ->where('pttp.type_id = :id');
			    $cmd->bindParam(':id', $type_id, PDO::PARAM_INT);
			    $params = $cmd->queryAll();

			    $height = 200;
			    $width = 400; //ПАРАМЕТРЫ ДЛЯ ТЕСТА
//ВООБЩЕ ПАРАМЕТРЫ ДОЛЖНЫ ПРИХОДИТЬ ОТДЕЛЬНО. К ОБСУЖДЕНИЮ: ОТКУДА?
			    if (!empty($params)) {
//СОХРАНЯЕМ ЗНАЧЕНИЯ ПАРАМЕТРОВ ДЛЯ ОБЪЕКОВ ДАННОГО ТИПА
				foreach ($params as $p) {
				    if (!empty($$p['title'])) {
					$p_id = $p['id'];
					$p_vl = $$p['title'];
					$sql = 'INSERT INTO {{tobjects_param_values}} (id, param_id, value, userobject_id)
						    		VALUES (null, ' . $p_id . ',
						    		"' . $p_vl . '", ' . $objInfo['id'] . ')';
					Yii::app()->db->createCommand($sql)->execute();
				    }
				}
			    }

//СОЗДАНИЕ ЛОКАЦИИ ОБЪЕКТА
			    $objLocInfo = array(
				'id' => $locInfo['id'],
				'user_id' => $locInfo['user_id'],
				'server_id' => intval($server_id),
				'state' => 0, // ?? ЧТО СЮДА ПРОПИСАТЬ ??
				'fsize' => $fsize,
				'fname' => $filename,
				'folder' => $folder,
			    );
			    $sql = 'INSERT INTO {{userobjectlocations}} (id, user_id, server_id, state, fsize, fname, folder)
			    		VALUES (' . $locInfo['id'] . ', ' . $locInfo['user_id'] . ', ' . $locInfo['server_id'] . ',
			    		' . $locInfo['state'] . ', ' . $locInfo['fsize'] . ', "' . $locInfo['fname'] . '", ' . $locInfo['folder'] . ')';
			    Yii::app()->db->createCommand($sql)->execute();

//ЧИСТКА ОЧЕРЕДИ КОНВЕРТИРОВАНИЯ
			    $sql = 'DELETE FROM {{convert_queue}} WHERE id=' . $queue['id'];
			    Yii::app()->db->createCommand($sql)->execute();
			}
		    }
		}
	    }
	}
    }

}