<?php


    /**
     * модель вариантов товаров
     * @property int(10) $id
     * @property int(10) $param_id
     * @property varchar(255) $value
     * @property int(10) $variant_id
     * @property int(10) $variant_quality_id
     */

class CProductParamValues extends CActiveRecord {
    /**
     *
     * @param string $className
     * @return CProductParamValues
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return '{{product_param_values}}';
    }

}