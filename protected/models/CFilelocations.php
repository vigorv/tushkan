<?php

/**
 * @property $id
 * @property $addr
 * @property  $active
 */
class CFilelocations extends CActiveRecord {

    /**
     *
     * @param string $className
     * @return CFilelocations
     */
    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public function tableName() {
	return '{{filelocations}}';
    }

}

?>
