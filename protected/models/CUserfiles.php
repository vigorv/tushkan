<?php

/**
 * модель file пользователей



 * @property $id
 * @property $user_id
 * @property $object_id
 * @property $title
 * @property $type_id
 * @method  getFileList($user_id, $pid=0, $page=1, $count=100)
 * @method getFileloc($fid, $user_id, $zone_id, $stype=1)
 * @method getDirTree($user_id)
 */
class CUserfiles extends CActiveRecord {

    /**
     *
     * @param string $className
     * @return CUserfiles class_model
     */
    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public function defaultScope() {
	return array(
	    'alias' => 'f',
	);
    }

    public function tableName() {
	return '{{userfiles}}';
    }

    /**
     *
     * @param type $fid
     * @param type $user_id
     * @param type $zone_id
     * @return type 
     */
    public function getFileloc($fid, $user_id, $zone_id, $stype=1) {
	return Yii::app()->db->createCommand()
			->select('f.server_id,f.fname,fs.ip, f.fsize')
			->from('{{filelocations}} f')
			->join('{{fileservers}} fs', 'fs.id=f.server_id and fs.zone_id=' . $zone_id)
			->join('{{userfiles}} uf', 'uf.fsize = f.fsize and uf.id = f.id and uf.user_id = f.user_id')
			->where('f.id =' . $fid . ' AND f.user_id=' . $user_id . ' AND fs.stype=' . $stype)
			->queryAll();
    }

    public function getFileList($user_id, $pid=0, $page=1, $count=100) {
	$offset = ($page - 1) * $count;
	return Yii::app()->db->createCommand()
			->select('uf.id,uf.title,uf.fsize')
			->from('{{userfiles}} uf')
			->where('uf.user_id =' . $user_id . ' AND uf.pid =' . $pid)
			->limit($count, $offset)
			->queryAll();
    }

    public function getDirTree($user_id) {
	return Yii::app()->db->createCommand()
			->select('uf.id, uf.title')
			->from('{{userfiles}} uf')
			->where('uf.user_id =' . $user_id . ' AND uf.is_dir = 1')
			->queryAll();
    }

    /**
     *
     * @param int $user_id
     * @param int $page
     * @param int $count 
     * @return array
     */
    public function getFileListUnt($user_id, $page=1, $count=100) {
	return Yii::app()->db->createCommand()
		->select('uf.id, uf.title')
		->from('{{userfiles}} uf')
		->where('uf.user_id ='. $user_id.' AND uf.object_id = 0')
		->limit($count, ($page-1) *$count)
		->queryAll();
    }
    
    
    public function getFileInfo($user_id,$fid){
	return Yii::app()->db->createCommand()
		->select('uf.id, uf.title, fv.fsize')
		->from('{{userfiles}} uf')
		->leftJoin('{{files_variants}} fv',' fv.file_id = uf.id and fv.preset_id =0 ')
		->where('uf.object_id = 0 AND uf.id= '.$fid.' AND uf.user_id ='.$user_id)
		->queryRow();
		
    }

}