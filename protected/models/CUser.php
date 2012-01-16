<?php

/**
 * модель пользователей
 *
 */
class CUser extends CActiveRecord {
    /**
     * @property $id
     * @property $email
     * @property $name
     * @property $group_id
     * @property $pwd
     * @property $active
     * @property $server_id
     * @property $gtitle;
     * 
     */

    public $gtitle;
    
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
            'alias' => 'u',
        );
    }
/*
    public function relations() {
        return array(
               //'g' => array(self::BELONGS_TO, 'user_groups', 'group_id')
               'g'=>array(self::HAS_ONE, 'CUsergroups', 'group_id'),
        );
    }
*/
    public function tableName() {
        return '{{users}}';
    }

}