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

    public static function getServerByZone($zoneId = -1)
    {
    	$addr = $ids = '';
    	if ($zoneId < 0)
    	{
    		//АВТОМАТИЧЕСКОЕ ОПРЕДЕЛЕНИЕ ПО IP
    		$zones = Yii::app()->user->UserZones;
    		if (!empty($zones[0]['zone_id']))
    		{
    			$ids = Utils::pushIndexToKey('zone_id', $zones);
    			$ids = ' AND zone_id IN (' . implode(',', array_keys($ids)) . ')';
    		}
    	}
		else
			$ids = ' AND zone_id = ' . $zoneId;

		if (!empty($ids))
			$info = Yii::app()->db->createCommand()
				->select('ip, port')
				->from('{{fileservers}}')
				->where('active = 1 ' . $ids)
				->queryAll();

		if (!empty($info))
		{
			$addr = 'http://' . $info[0]['ip'];
			if (!empty($info[0]['port']))
			{
				$addr .= ':' . $info[0]['port'];
			}
		}

    	return $addr;
    }

}

?>
