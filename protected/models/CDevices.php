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
define("_DT_PAD_", 1);
define("_DT_MOBILE_", 2);
define("_DT_PLAYER_", 3);
define("_DT_TVSET_", 4);

class CDevices extends CActiveRecord {

    /**
     *
     * @param string $className
     * @return CDevices
     */
    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public static function getDeviceTypes()
    {
    	return array(
    		_DT_PAD_ => array(
    			'id' => _DT_PAD_,
    			'title' => Yii::t('common', 'Pad'),
    			'alias' => "netbook",
    		),
    		_DT_MOBILE_ => array(
    			'id' => _DT_MOBILE_,
    			'title' => Yii::t('common', 'Mobile'),
    			'alias' => "mobile",
    		),
    		_DT_PLAYER_ => array(
    			'id' => _DT_PLAYER_,
    			'title' => Yii::t('common', 'Player'),
    			'alias' => "player",
    		),
    		_DT_TVSET_ => array(
    			'id' => _DT_TVSET_,
    			'title' => Yii::t('common', 'TVset'),
    			'alias' => "tv",
    		),
    	);
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
