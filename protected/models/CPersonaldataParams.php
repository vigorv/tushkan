<?php

/**
 * модель таблицы параметров персональных данных пользователей
 *
 */
class CPersonaldataParam extends CActiveRecord {
    /**
     *
     * @param string $className
     * @return CActiveRecord
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return '{{personaldata_params}}';
    }

}