<?php

/**
 * модель таблицы типов товаров
 *
 */
class CType extends CActiveRecord {
    /**
     *
     * @param string $className
     * @return CActiveRecord
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return '{{product_types}}';
    }

}