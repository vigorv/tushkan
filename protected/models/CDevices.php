<?php

/**
 * ActiveRecord class for device
 * 
 * @property $id
 * @property $guid
 * @property $user_id
 * @property $device_type_id
 * @property $title
 * @property $active
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
	return '{{userdevices}}';
    }

}

?>
