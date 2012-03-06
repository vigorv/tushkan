<?php

/**
 * Class CloudTaskManager
 * 
 */
class CloudTaskManager {

    private static $_models = array();
# array with the options to create stream context

    /**
     *
     * @param string $className
     * @return CloudTaskManager
     */
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
     * CreateFileTask
     * 
     * Adds operation with file to queue
     * 
     * @param int $queue_id
     * @param int $fid
     * @param int $user_id
     * @param string $preset_name
     * @return 
     */
    public function CreateFileTask($queue_id, $fid, $user_id, $preset_name) {
	$zone = 0;
	$server = CServers::model()->getServerFull(TASK_SERVER, $zone);
	$server_addr = Cservers::model()->convertIpToString($server['ip']) . ':' . $server['port'];
	$file = CUserfiles::model()->getFileloc($fid, $user_id, $zone,0);

	$preset_id = CPresets::model()->getPresetID($preset_name);

	if (count($file) && ($server)) {
	    $task_id = (int) CServers::model()->sendCommandAddr('/tasks/addtask', $server_addr, array(
			'queue' => $queue_id,
			'user_id' => $user_id,
			'fid' => $fid,
			'preset' => $preset_name,
			'fpath' => '',
			'fname' => $file[0]['fname'],
			'fsize' => $file[0]['fsize'],
			'ip' => Cservers::model()->convertIpToString($file[0]['ip'])));
	    if ($task_id > 0) {
		$queue = new CConvertQueue();
		$queue->id=$fid;
		$queue->task_id=$task_id;
		$queue->server_id=$server['id'];
		$queue->preset_id=$preset_id;
		//$sql = 'INSERT INTO {{convert_queue}} (id,  task_id,server_id,preset_id) VALUES (' . $fid . ', ' . $user_id . ', ' . $task_id . ',' . $server['id'] . ',' . $preset_id . ')';
		//return Yii::app()->db->createCommand($sql)->execute();
		return $queue->save();
	    }
	}
	return false;
    }

    /**
     * AbortFileTaskQueue
     * 
     * Remove operation with file from queue
     * 
     * @param type $queue
     * @param type $queue_id
     * @return boolean 
     */
    public function AbortFileTaskQueue($queue, $queue_id=1) {
	$server = CServers::model()->findByPk($queue['server_id']);
	if ($server) {
	    $ip = CServers::model()->convertIpToString($server['ip']);
	    $result = CServers::model()->sendCommandAddr('/tasks/abort', $ip . ':' . $server['port'], array('task_id' => $queue['task_id'],
		'queue' => $queue_id, 'user_id' => $queue['user_id']));
	    if ($result == 1) {
		$sql = 'DELETE FROM {{convert_queue}} WHERE task_id=' . $queue['task_id'] . ' AND server_id=' . $queue['server_id'];
		Yii::app()->db->createCommand($sql)->execute();
		return true;
	    } else {
		echo "Fail to sync";
		return false;
	    }
	} else {
	    echo "unknown server";
	    return false;
	}
    }

    /**
     *
     * GetTaskForFile
     * 
     * Find queued task's for file 
     *
     * @param int $fid 
     * @param int $user_id
     * @return row
     */
    public function GetTaskForFile($fid, $user_id) {
	$cmd = Yii::app()->db->createCommand()
		->select('cq.*,uf.user_id')
		->from('{{convert_queue}} cq')
		->join('{{userfiles}} uf', 'cq.id = uf.id AND uf.user_id = ' . $user_id)
		->where(' cq.id = ' . $fid );
	return $cmd->queryRow();
    }

}
?>



