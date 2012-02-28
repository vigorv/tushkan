<?php

/**
 * ActiveRecord class for UserObjects
 * 
 * @property $id
 * @property $title
 * @property $user_id
 * @property $type_id
 * @property $active
 * @property $parent_id
 */
class CUserObjects extends CActiveRecord {
    
    /**
     *
     * @param string $className
     * @return CUserObjects
     */
    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public function tableName() {
	return '{{userobjects}}';
    }

}