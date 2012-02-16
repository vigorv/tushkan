<?php

/**
 * 
 */
class CConvertQueue extends CActiveRecord {
    /*
     *
     * @param type $className
     * @return type 
     */

    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public function tableName() {
	return '{{convert_queue}}';
    }

}

?>
