<?php

/**
 * @property $id
 * @property $addr
 * @property $dsc
 * @property  $active
 */
class CFileservers extends CActiveRecord {

    /**
     *
     * @param string $className
     * @return CFileservers
     */
    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public function tableName() {
	return '{{fileservers}}';
    }

}

?>
