<?php

/**
 * модель file пользователей
 * @property $id
 * @property $user_id
 * @property $object_id
 * @property $title
 * @property $type_id
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
     *  Serversync getFileloc for $file_id , $user_id ,$zone_id
     * @param int $fid
     * @param int $user_id
     * @param int $zone_id
     * @return type
     */
    public function getFileloc($fid, $user_id, $zone_id, $preset_id, $stype=1) {
	return Yii::app()->db->createCommand()
			->select('fl.server_id,fl.fname,fs.ip, fl.fsize')
			->from('{{filelocations}} fl')
			->join('{{fileservers}} fs', 'fs.id=fl.server_id and fs.zone_id=' . $zone_id . ' AND fs.stype=' . $stype)
			->join('{{files_variants}} fv', 'fv.id = fl.id AND fv.preset_id=' . $preset_id . ' AND fl.fsize = fv.fsize AND fv.file_id=' . $fid)
			->join('{{userfiles}} uf', ' uf.id = fv.file_id and uf.user_id =' . $user_id)
			->queryAll();
    }

    /**
     * Get  variants wich has locations
     * @param int $fid
     * @param int $zone_id
     */
    public function GetVarWithLoc($fid, $zone_id) {
	return Yii::app()->db->createCommand()
			->select('fv.id, fv.fsize , fv.preset_id')
			->from('{{filelocations}} fl')
			->join('{{fileservers}} fs', 'fs.id=fl.server_id and fs.zone_id=' . $zone_id . ' AND fs.downloads=1')
			->join('{{files_variants}} fv', 'fv.id = fl.id AND fl.fsize = fv.fsize AND fv.file_id=' . $fid)
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
			->where('uf.user_id =' . $user_id . ' AND uf.object_id = 0')
			->limit($count, ($page - 1) * $count)
			->queryAll();
    }

    /**
     *
     * @param int $user_id
     * @param int $fid
     * @return mixed
     */
    public function getFileInfo($user_id, $fid) {
	return Yii::app()->db->createCommand()
			->select('uf.id, uf.title, uf.created, uf.object_id, fv.fsize, uf.type_id, fv.preset_id')
			->from('{{userfiles}} uf')
			->leftJoin('{{files_variants}} fv', ' fv.file_id = uf.id')
			->where('uf.id= ' . $fid . ' AND uf.user_id =' . $user_id)
			->queryRow();
    }

    /**
     *
     * @param int $user_id
     * @param int $fid
     * @return mixed
     */
    public static function getFileMeta($user_id, $fid) {
	return Yii::app()->db->createCommand()
			->select('uf.*')
			->from('{{userfiles}} uf')
			->where('uf.user_id=' . $user_id . ' AND uf.id=' . $fid)
			->queryRow();
    }

    public function getFileVariantUser($fid) {
	return Yii::app()->db->createCommand()
			->select('fv.*')
			->from('{{files_variants}} fv')
			->where('fv.file_id=' . $fid . ' AND fv.preset_id = 0')
			->queryRow();
    }

    public function getFileLocUser($variant_id, $zone=null, $stype=1) {
	$fileloc = Yii::app()->db->createCommand()
		->select('fl.*,fs.*')
		->from('{{filelocations}} fl')
		->join('{{fileservers}} fs', 'fl.server_id = fs.id');
	$where = array('and');
	if ($stype)
	    $where[] = 'fs.stype=' . $stype;
	if ($zone)
	    $where[] = 'fs.zone_id=' . $zone;
	$where[] = 'fl.id=' . $variant_id;

	$fileloc->where($where);
	return $fileloc->queryAll();
    }

    /**
     * Remove only if file| not object
     * @param int $user_id
     * @param int $id
     */
    public static function RemoveFile($user_id, $fid) {
	    $file = CUserfiles::getFileMeta($user_id, $fid);
	    if (($file) && ($file['object_id'] == 0)) {
	         $file_variants = CFilesvariants::model()->findAllByAttributes(array('file_id' => $file['id']));

	    // TODO: DELETE BY mysql onDELETE
	    foreach ($file_variants as $file_variant){
            $locations = CFilelocations::getAllLocationsForVariant($file_variant['id']);
            foreach ($locations as $location)
                CServers::deleteFileOnServerByLocation($location);
		    CFilelocations::model()->deleteAllByAttributes(array('id' => $file_variant['id']));
        }
	        CFilesvariants::model()->deleteAllByAttributes(array('file_id' => $file['id']));
	        CUserfiles::model()->deleteByPk($fid);
	} else
	    return false;
    }


    /**
     *Remove all untypes files
     * @param type $user_id
     * @return type
     */
    public function RemoveAllFiles($user_id) {
	$file_variants = Yii::app()->db->createCommand()
		->select('fv.id')
		->from('{{files_variants}} fv')
		->join('{{userfiles}} uf', 'uf.user_id =' . $user_id . ' AND uf.object_id = 0')
		->queryAll();
	foreach ($file_variants as $file_variant) {
	    CFilelocations::model()->deleteAllByAttributes(array('id' => $file_variant['id']));
	    CFilesvariants::model()->deleteAllByAttributes(array('id' => $file_variant['id']));
	}
	Yii::app()->db->createCommand()
			->delete('{{userfiles}}','user_id =' . $user_id . ' AND object_id = 0');

	return;
    }

    /**
     *
     */

    public function getFilesLike($user_id,$like, $page=1, $per_page=10){
		$offset = ($page - 1) * $per_page;
	return Yii::app()->db->createCommand()
			->select('uf.id, uf.title, fv.fsize')
			->from('{{userfiles}} uf')
			->leftJoin('{{files_variants}} fv', ' fv.file_id = uf.id and fv.preset_id =0 ')
			->where('uf.object_id = 0 AND uf.title LIKE "%' . $like . '%" AND uf.user_id =' . $user_id)
			->limit($per_page, $offset)
			->queryAll();
    }

    /**
     * @static
     * @param $user_id
     * @param $variant_id
     * @return mixed
     */

    public static function DidUserHaveVariant($user_id,$variant_id){
        return Yii::app()->db->createCommand()
            ->select('Count(uf.id)')
            ->from('{{userfiles}} uf')
            ->join('{{files_variants}} fv','fv.file_id = uf.id and fv.id = :variant_id',array(':variant_id'=>$variant_id))
            ->where('uf.user_id = :user_id',array(':user_id'=>$user_id))
            ->queryScalar();
    }



    public static function findObjects($search = '', $user_id = 0, $page = 1, $per_page = 10)
    {
        $offset = ($page - 1) * $per_page;
        return Yii::app()->db->createCommand()
            ->select('*')
            ->from('{{userfiles}}')
            ->where('user_id = :user_id', array(':user_id' => $user_id))
            ->offset($offset)
            ->limit($per_page)
            ->queryAll();
    }

    public static function countFoundObjects($search = '', $user_id = 0)
    {
        return Yii::app()->db->createCommand()
            ->select('Count(*)')
            ->from('{{userfiles}}')
            ->where('user_id = :user_id', array(':user_id' => $user_id))
            ->queryScalar();
    }


    public static function getUserObject($item_id,$user_id)
    {
        return Yii::app()->db->createCommand()
            ->select('*')
            ->from('{{userfiles}}')
            ->where('user_id = :user_id AND id=:item_id', array(':user_id' => $user_id,':item_id'=>$item_id))
            ->limit(1)
            ->queryAll();
    }


}