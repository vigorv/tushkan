<?php

/**
 * @property $id
 * @property $product_id
 * @property $original_id
 * @property $task_id
 * @property $cmd_id
 * @property $info
 * @property $priority
 * @property $state
 * @property $station_id
 * @property $partner_id
 * @property $user_id
 * @property $original_variant_id
 * @property $date_start
 * @property $path
 *
 */

class CConvertQueue extends CActiveRecord {

    /**
     *
     * @param string $className
     * @return CConvertQueue
     */
    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public function tableName() {
		return '{{income_queue}}';
    }

}

?>
