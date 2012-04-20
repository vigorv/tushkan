<?php

/**
 * ActiveRecord class for User Uploads
 * 
 * @property $id
 * @property $user_id
 * @property $filename
 * @property $kpt
 * @property $started
 */
class CUploads extends CActiveRecord {

	/**
	 *
	 * @param string $className
	 * @return CUploads
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return '{{uploads}}';
	}

}