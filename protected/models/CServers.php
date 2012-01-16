<?php

/**
 * 
 */
class CServers extends CActiveRecord {

    /**
     * @property $id
     * @property $ip
     * @property $zone_id    
     * @property $active
     */
    public function tableName() {
        return '{{servers}}';
    }
    

}

?>
