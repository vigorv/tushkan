<?php

/**
 * @property $server_id
 * @property $preset_id
 * @property $task_id
 * @property $id
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
	return '{{convert_queue}}';
    }

}

?>
