<?php

/**
 * 
 */
class CloudTaskManager {

    private static $_models = array();
# array with the options to create stream context

    public static function model($className=__CLASS__) {
	if (isset(self::$_models[$className]))
	    return self::$_models[$className];
	else {
	    $model = self::$_models[$className] = new $className(null);
	    return $model;
	}
    }

    /**
     *
     * @param type $queue_id
     * @param type $fid
     * @param type $user_id
     * @param type $preset_name
     * @return type 
     */
    public function CreateFileTask($queue_id, $fid, $user_id, $preset_name) {
	$zone = 0;
	$server = CServers::model()->getServerFull(TASK_SERVER, $zone);
	$server_addr = Cservers::model()->convertIpToString($server['ip']) . ':' . $server['port'];
	$file = CUserfiles::model()->getFileloc($fid, $user_id, $zone);
	if (count($file) && ($server)) {
	    $task_id = (int) CServers::model()->sendCommandAddr('/tasks/addtask', $server_addr, array(
			'queue' => $queue_id,
			'fid' => $fid,
			'preset' => $preset_name,
			'fpath' => '',
			'fname' => $file[0]['fname'],
			'fsize' => $file[0]['fsize'],
			'ip' => Cservers::model()->convertIpToString($file[0]['ip'])));
	    if ($task_id > 0) {
		$sql = 'INSERT INTO {{convert_queue}} (id, user_id, task_id,server_id) VALUES (' . $fid . ', ' . $user_id . ', ' . $task_id . ',' . $server['id'] . ')';
		return Yii::app()->db->createCommand($sql)->execute();
	    }
	}
	return false;
    }

    public function AbortFileTaskQueue($queue) {
	$server = CServers::model()->findByPk($queue['server_id']);
	if ($server) {
	    $ip = CServers::model()->convertIpToString($server['ip']);
	    $result = (int) CServers::model()->sendCommandAddr('/tasks/abort', $ip . ':' . $server['port'], array('task_id' => $queue['task_id']));
	    if ($result) {
		$sql = 'DELETE FROM {{convert_queue}} WHERE task_id=' . $queue['task_id'] . ' AND server_id=' . $queue['server_id'];
		Yii::app()->db->createCommand($sql)->execute();
	    }
	} else
	    return false;
    }

    public function GetTaskForFile($fid, $user_id) {
	$cmd = Yii::app()->db->createCommand()
		->select('*')
		->from('{{convert_queue}}')
		->where('id = ' . $fid . ' AND user_id = ' . $user_id);
	return $cmd->queryRow();
    }

}
?>



