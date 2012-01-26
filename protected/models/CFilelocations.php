<?php

/**
 * 
 */
class CFilelocations extends CActiveRecord {
    /**
     * @property $id
     * @property $addr
     * @property $desc
     * @property  $active
     */

    /**
     *
     * @param type $className
     * @return type 
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return '{{filelocations}}';
    }

}

?>
