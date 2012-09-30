<?php


/**
 * модель вариантов товаров
 * @property int(10) $id
 * @property int(10) $variant_id
 * @property int(10) $preset_id
 */

class CProductVariantQualities extends CActiveRecord {
    /**
     *
     * @param string $className
     * @return  CProductVariantQualities
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return '{{variant_qualities}}';
    }

}