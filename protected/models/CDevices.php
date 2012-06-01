<?php

/**
 * ActiveRecord class for device
 * 
 * @property $id
 * @property $user_id
 * @property $device_type_id
 * @property $title
 * @property $guid
 * @property $active
 * @property $app_hash
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

    public static function generateGUID($info_string=''){
        $guid_str = md5("Device_".$info_string.Yii::app()->user->id);
        return $guid_str;
    }

    public function generateDeviceLoginHash(){
        $this->hash = md5($this->user_id.$this->guid.time());
    }

}

?>
