<?php

/**
 * ActiveRecord class for device
 * 
 * @property $id
 * @property $guid
 * @property  $lastactive
 * @property $user_id
 * @property $dtype
 * @property $title
 */
class CDevices extends CActiveRecord {

    /**
     *
     * @param string $className
     * @return CDevices
     */
    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public function tableName() {
	return '{{devices}}';
    }

}

?>
