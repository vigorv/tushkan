<?php

/**
 * @property $id
 * @property $addr
 * @property  $active
 */
class CFilelocations extends CActiveRecord {

    /**
     *
     * @param string $className
     * @return CFilelocations
     */
    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public function tableName() {
	    return '{{filelocations}}';
    }

     /**
     * @static
     * @param int $fl_id
     * @param int $zone_id
     */
    public static function getLocationByZone($fl_id,$zone_id){
       return Yii::app()->db->createCommand()
            ->select('fl.*,fs.*')
            ->from('{{filelocations}} fl')
            ->leftJoin('{{fileservers}} fs','fl.server_id = fs.id AND zone_id=:zone_id',array(':zone_id'=>$zone_id))
            ->where('fl.id = :fl_id',array(':fl_id'=>$fl_id))
            ->queryALL();
    }



}

?>
