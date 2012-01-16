<?php

/**
 * 
 */
class CUsershares extends CActiveRecord {

    /**
     * @property $id
     * @property $master_user_id
     * @property $slave_user_id
     * @property $path
     */
    public function tableName() {
        return '{{user_shares}}';
    }

}

?>
