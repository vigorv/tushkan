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
    public function tableName() {
        return '{{devices}}';
    }

}

?>
