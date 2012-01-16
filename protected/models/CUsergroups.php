<?php

/**
 * модель пользователей
 *
 */
class CUsergroups extends CActiveRecord {
    /**
     * @property $id
     * 
     */

    /**
     *
     * @param type $className
     * @return type 
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function defaultScope() {
        return array(
            'alias' => 'g',
        );
    }


    public function tableName() {
        return '{{user_groups}}';
    }

}