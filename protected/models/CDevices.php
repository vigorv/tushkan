<?php

/**
 * 
 */
class CDevices extends CActiveRecord {
    /**
     * @property $id
     * @property $guid
     * @property  $lastactive
     * @property $user_id
     * @property $dtype
     * @property $title
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
        return '{{devices}}';
    }

}

?>
