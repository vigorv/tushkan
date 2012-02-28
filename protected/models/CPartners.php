<?php

/**
 * @property $id     
 * @property $hkey
 * @property $service_uri
 * @property $service_cat
 * @property $service_items
 * @property $service_itemInfo
 * @property $fields_cat
 * @property $fields_items
 * @property $fields_itemInfo
 */
class CPartners extends CActiveRecord {

    /**
     *
     * @param type $className
     * @return CPartners
     */
    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public function tableName() {
	return '{{partners}}';
    }

}

?>
