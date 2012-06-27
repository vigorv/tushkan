<?php

/**
 * 
 * @property $id
 * @property $zone_lvl
 * @property $zone_parent
 */
class CZones extends CActiveRecord {
    /**
     * @static
     * @param string $className
     * @return CZones
     */
    public static function model($className = __CLASS__) {
	    return parent::model($className);
    }

    public function tableName() {
	return '{{zones}}';
    }

    /**
     * @param $client_ip
     * @return int
     */
    public function GetZoneByIp($client_ip) {
	    return 0;
    }

}

?>
