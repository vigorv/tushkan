<?php

/**
 * модель пользователей
 *
 */

/**
 * @property $id
 * @property $email
 * @property $name
 * @property $group_id
 * @property $pwd
 * @property $active
 * @property $server_id
 * @property $gtitle;
 * @properyy $sess_id;
 * 
 */
class CUser extends CActiveRecord {

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

    public function tableName() {
	return '{{users}}';
    }

    public static function KPT($user_id) {
	$sid = CUser::model()->findByPk($user_id)->sess_id;
	$kpt = md5($user_id . $sid . "I am robot");
	return $kpt;
    }

}