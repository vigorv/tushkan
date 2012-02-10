<?php

class ServersyncController extends Controller {

    var $layout = 'ajax';

    public function beforeAction($action) {
	parent::beforeAction($action);
	//    return true;
	$shash = $_GET['shash'];
	$hash_local = md5(date('%h%d') . 'where am i');
	if ($shash <> $hash_local) {
	    echo 'bye';
	    return false;
	}
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
	    $data = unserialize($_GET['data']);
	    $fid = (int) $data['fid'];
	    $stype = (int) $data['stype'];
	    $user_ip = (int) $data['user_ip'];
	    $zone = CZones::model()->GetZoneByIp($user_ip);
	    $filemeta = Yii::app()->db->createCommand()
		    ->select('uf.*')
		    ->from('{{userfiles}} uf')
		    ->where('uf.user_id=' . $user_id . ' AND uf.id=' . $fid)
		    ->limit(1)
		    ->queryAll();
	    if (count($filemeta)) {
		$response['title'] = $filemeta[0]['title'];
		$fileloc = Yii::app()->db->createCommand()
			->select('fl.*,fs.*')
			->from('{{filelocations}} fl')
			->join('{{fileservers}} fs', 'fl.server_id = fs.id');
		$where = array('and');

		if ($stype)
		    $where[] = 'fs.stype=' . $stype;
		if ($zone)
		    $where[] = 'fs.zone_id=' . $zone;
		$where[] = 'fl.id=' . $fid;
		$where[] = 'fl.user_id=' . $user_id;
		$fileloc->where($where);
		$filedata = $fileloc->queryAll();
		foreach ($filedata as $file) {
		    $fdata = array();
		    $fdata['ip'] = $file['ip'];
		    $fdata['port'] = $file['port'];
		    $fdata['name'] = $file['fname'];
		    $fdata['size'] = $file['fsize'];
		    $response['filedata'][] = $fdata;
		}
	    } else {
		$response['error'] = 'unknown file';
	    }
	    echo (serialize($response));
	} else
	    die('bye bye');
	exit;
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
	    if (!$input)
		die('Bad data');
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
	    $result = array();
	    $result['fid'] = $files->id;
	    echo serialize($result);
	    exit();
	} else
	    die("Bad User");
    }

    public function actionDownload($user_id=0) {
	if ($user_id > 0) {
	    //OK 
	    //WHat is server doing this
	    $ip = CServers::convertIpToLong($_SERVER['REMOTE_ADDR']);
	    $server = CServers::model()->findByAttributes(array('ip' => $ip, 'stype' => 1));
	    if ($server === null)
		die('Unknown Server ' . $_SERVER['REMOTE_ADDR']);
	    if (!isset($_GET['data']))
		die('not enough data');
	    $input = unserialize($_GET['data']);
	    $fileloc = new CFilelocations();
	    $fileloc->id = $input['fid'];
	    $fileloc->server_id = $server['id'];
	    $fileloc->user_id = $user_id;
	    $fileloc->fsize = $input['fsize'];
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