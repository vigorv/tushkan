<?php

/**
 * @property $id
 * @property $product_id
 * @property $original_id
 * @property $task_id
 * @property $cmd_id
 * @property $info
 * @property $priority
 * @property $state
 * @property $station_id
 * @property $partner_id
 * @property $user_id
 * @property $original_variant_id
 * @property $date_start
 * @property $path
 *
 */

class CConvertQueue extends CActiveRecord {

    /**
     *
     * @param string $className
     * @return CConvertQueue
     */
    public static function model($className = __CLASS__) {
	    return parent::model($className);
    }

    public function tableName() {
		return '{{income_queue}}';
    }

    public static function deleteUserQueue($uid, $qid)
    {
    	$cmd = Yii::app()->db->createCommand()
    		->select('id')
    		->from('{{income_queue}}')
    		->where('id = :qid AND user_id = :uid');
    	$cmd->bindParam(':qid', $qid, PDO::PARAM_INT);
    	$cmd->bindParam(':uid', $uid, PDO::PARAM_INT);
    	$info = $cmd->queryScalar();
    	if ($info)
    	{
    		$sql = 'DELETE FROM {{income_queue}} WHERE id = :qid';
    		$cmd = Yii::app()->db->createCommand($sql);
	    	$cmd->bindParam(':qid', $qid, PDO::PARAM_INT);
	    	$cmd->query();
    	}
    }

    public static function restartUserQueue($uid, $qid)
    {
    	$cmd = Yii::app()->db->createCommand()
    		->select('id')
    		->from('{{income_queue}}')
    		->where('id = :qid AND user_id = :uid AND original_id > 0');
    	$cmd->bindParam(':qid', $qid, PDO::PARAM_INT);
    	$cmd->bindParam(':uid', $uid, PDO::PARAM_INT);
    	$info = $cmd->queryScalar();
    	if ($info)
    	{
    		$sql = 'UPDATE {{income_queue}} SET `cmd_id`=0, `state`=0 WHERE id = :qid';
    		$cmd = Yii::app()->db->createCommand($sql);
	    	$cmd->bindParam(':qid', $qid, PDO::PARAM_INT);
	    	$cmd->query();
    	}
    }
}

?>
