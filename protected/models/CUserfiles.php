<?php

/**
 * модель file пользователей
 *
 */

/**
 * @property $id
 * @property $user_id
 * @property $is_dir
 * @property $fsize
 * @property $curent_fsize
 * @property $pid
 * @property $title
 * @property $fname
 */
class CUserfiles extends CActiveRecord {

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
			->where('f.id =' . $fid . ' AND f.user_id=' . $user_id . ' AND fs.stype='.$stype)
			->queryAll();
    }

}