<?php

/**
 * 
 */
class CZones extends CActiveRecord {

    /**
     * @property $id
     * @property $zone_lvl
     * @property $zone_parent
     */
    public function tableName() {
        return '{{zones}}';
    }

}

?>
