<?php

/**
 * @property $id     
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
