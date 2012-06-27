<?php

/**
 * @property $id
 * @property $addr
 * @property $dsc
 * @property  $active
 */
class CFileservers extends CActiveRecord {

    /**
     *
     * @param string $className
     * @return CFileservers
     */
    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public function tableName() {
	    return '{{fileservers}}';
    }

    /**
     * @static
     * @param int $location_id
     * @return mixed
     */
    public static function getDownloadServerForUserFile($location_id){
        return Yii::app()->db->createCommand()
            ->select('fs.*')
            ->from('{{fileservers}} fs')
            ->join('{{filelocations}} fl','fl.server_id = fs.id && fl.id = :location_id',array(':location_id'=>$location_id))
            ->where('fs.downloads = 1')
            ->limit(1)
            ->queryRow();
    }

}

?>
