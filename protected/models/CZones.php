<?php

/**
 * 
 * @property $id
 * @property $zone_lvl
 * @property $zone_parent
 * @method  GetZoneByIp($client_ip
 */
class CZones extends CActiveRecord {

    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public function tableName() {
	return '{{zones}}';
    }

    public function GetZoneByIp($client_ip) {
	return 0;
    }

}

?>
