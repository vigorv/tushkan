<?php

CONST QUEUE_CONVERTER = 1;

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

    public function CreateFileTask($queue_id, $fid, $preset_name) {
	$zone=0;
	$server=CServers::model()->getServer($TASK_SERVER,$zone);
	$id = CServers::model()->sendCommandAddr('/task/create',$server,array($queue_id,$fid,$preset_name));
	return $id;
    }

    public function AbortFileTask($queue_id, $task_id) {
	$zone=0;
	$server=CServers::model()->getServer($TASK_SERVER,$zone);
	$result = CServers::model()->sendCommandAddr('/task/abort',$server,array($queue_id,$fid,$preset_name));
	return $result;
    }

}
?>



