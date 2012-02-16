<?php

/**
 * модель таблицы параметров типов товаров
 *
 */
class CParam extends CActiveRecord {
    /**
     *
     * @param string $className
     * @return CParam
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return '{{product_type_params}}';
    }

}