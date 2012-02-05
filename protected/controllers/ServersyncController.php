<?php

class ServersyncController extends Controller {

    var $layout = 'ajax';

    public function beforeAction($action) {
	parent::beforeAction($action);
	//    return true;
	$shash = $_GET['shash'];
	$hash_local = md5(date('%h%d') . 'where am i');
	if ($shash <> $hash_local) {
	    return false;
	}
	return true;
    }

    /**
     *
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

    public function actionFiledata($user_id=0, $stype=0, $zone=0) {
	if ($user_id > 0) {
	    $id = (int) $user_id;
	    $fid = (int) $_GET['fid'];
	    $filemeta = Yii::app()->db->createCommand()
		    ->select('uf.*')
		    ->from('{{userfiles}} uf')
		    ->where('uf.user_id=' . $id . ' AND uf.id=' . $fid)
		    ->limit(1)
		    ->queryAll();
	    if (count($filemeta)) {
		$response['fname'] = $filemeta[0]['uf.fname'];
		$response['title'] = $filemeta[0]['uf.title'];
		$fileloc = Yii::app()->db->createCommand('SELECT fl.*,fs.* FROM {{filelocations}} fl'
			. 'INNER JOIN {{fileservers}} fs on fl.server_id = fs.id');
		if ($stype)
		    $fileloc->where('fs.stype=' . $stype);
		if ($zone)
		    $fileloc->where('fs.zone_id=' . $zone);
		$filedata = $fileloc->queryAll();
		foreach ($filedata as $file)
		    $fdata = array();
		$fdata['ip'] = $file['fs.ip'];
		$fdata['port'] = $file['fs.port'];
		$fdata['name'] = $file['fl.fname'];
		$fdata['size'] = $file['fl.fsize'];
		$response['filedata'][] = $fdata;
	    }
	    else {
		$response['error'] = 'unknown file';
	    }
	    echo (serialize($response));
	}
	exit();
    }

    public function actionCreate($user_id=0, $title='', $pid=0, $is_dir=0) {
	if ($user_id > 0) {
	    $cur_file = CUserfiles::model()->findAllByAttributes(array('user_id' => $user_id, 'title' => $title, 'pid' => $pid));
	    if (!$cur_file['id']) {
		$files = new CUserfiles();
		$files->title = $title;
		$files->pid = $pid;
		$files->is_dir = $is_dir;
		$files->user_id = $user_id;
		$files->save();
	    }
	}
    }

    public function actionUpload($user_id=0, $data='') {
	if ($user_id > 0) {
	    //OK 
	    //WHat is server doing this
	    $ip = CServers::convertIpToLong($_SERVER['REMOTE_ADDR']);

	    $server = CServers::model()->findByAttributes(array('ip' => $ip, 'stype' => 2));
	    if ($server === null)
		die('Unknown Server ' . $ip);
	    $input = unserialize($data);
	    $new_title = $input['filename'];
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
	    $fileloc->server_id = $server->id;
	    $fileloc->user_id = $user_id;
	    $fileloc->fsize = $files->fsize;
	    $fileloc->fname = $input['save'];
	    if (isset($input['folder']))
		$fileloc->folder = (int) $input['folder'];
	    $fileloc->save();
	    echo "OK";
	    exit();
	} else
	    die("Bad User");
    }

}