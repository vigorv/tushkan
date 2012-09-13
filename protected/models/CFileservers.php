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

    /**
     * получить адрес сервера по зоне
     *
     * @param integer $zoneId - идентификатор зоны. по умолчанию действует автоопределение зоны (либо указать "-1")
     * @return string
     */
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
			$i = 0;
			if (count($info) > 1)
			{
				//ВЫБИРАЕМ СЛУЧАЙНЫЙ ИЗ СПИСКА
				$i = rand(0, count($info) - 1);
			}
			$addr = 'http://' . $info[$i]['ip'];
			if (!empty($info[$i]['port']))
			{
				$addr .= ':' . $info[$i]['port'];
			}
		}

    	return $addr;
    }

    /**
     * определить сервер для партнера с учетом зоны пользователя
     *
     * @param integer $partnerId
     * @param integer $zoneId - идентификатор зоны. по умолчанию действует автоопределение зоны (либо указать "-1")
     */
    public static function getServerByPartnerZone($partnerId, $zoneId = -1)
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
				->from('{{fileservers}} fs')
				->join('{{partners_zones}} pz', 'pz.server_id = fs.id')
				->where('active = 1 AND pz.partner_id = :partner_id ' . $ids)
				->queryAll();

		if (!empty($info))
		{
			$i = 0;
			if (count($info) > 1)
			{
				//ВЫБИРАЕМ СЛУЧАЙНЫЙ ИЗ СПИСКА
				$i = rand(0, count($info) - 1);
			}
			$addr = 'http://' . $info[$i]['ip'];
			if (!empty($info[$i]['port']))
			{
				$addr .= ':' . $info[$i]['port'];
			}
		}

    	return $addr;
    }
}

?>
