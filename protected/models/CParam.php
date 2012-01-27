<?php

/**
 * модель таблицы параметров типов товаров
 *
 */
class CParam extends CActiveRecord {
    /**
     *
     * @param string $className
     * @return CActiveRecord
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return '{{product_type_params}}';
    }

}