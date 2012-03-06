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

    public function getList($user_id, $type_id=-1, $page=1, $count=100) {
	$offset = ($page - 1) * $count;
	if ($type_id >= 0) {
	    $type_str = ' AND uo.type_id=' . $type_id;
	} else
	    $type_str = '';
	return Yii::app()->db->createCommand()
			->select('uo.title,uo.id')
			->from('{{userobjects}} uo')
			->where('uo.user_id =' . $user_id . $type_str)
			->limit($count, $offset)
			->queryAll();
    }

    public function getObjectsLike($user_id, $like, $page=1, $per_page=10,$type_id=-1) {
	$offset = ($page - 1) * $per_page;
	if ($type_id >= 0) {
	    $type_str = ' AND uo.type_id=' . $type_id;
	} else
	    $type_str = '';
	return Yii::app()->db->createCommand()
			->select('uo.title,uo.id')
			->from('{{userobjects}} uo')
			->where('uo.user_id =' . $user_id .' AND uo.title LIKE "%'.$like.'%"' . $type_str)
			->limit($per_page, $offset)
			->queryAll();
    }

}