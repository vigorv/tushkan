<?php

/**
 * @property $id
 * @property $hkey
 * @property $service_uri
 * @property $service_cat
 * @property $service_items
 * @property $service_itemInfo
 * @property $fields_cat
 * @property $fields_items
 * @property $fields_itemInfo
 */
class CPartners extends CActiveRecord {

    /**
     *
     * @param type $className
     * @return CPartners
     */
    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public static function getPartners()
    {
/*
СТАРЫЙ СТАТИЧЕСКИЙ ВАРИАНТ
    	$partners = array(
    		1	=> array('id' => 1, 'title' => 'vxq', 'url' => 'http://videoxq.com', 'type_id' => 1),//type_id - какого типа контент предоставляет партнер (см. таблицу dm_product_types)
    		2	=> array('id' => 2, 'title' => 'fastlink', 'url' => 'http://fastlink.ws', 'type_id' => 1),
    		3	=> array('id' => 3, 'title' => 'rumedia', 'url' => 'http://rumedia.ws', 'type_id' => 1),
    	);
*/
    	$partners = array();
		$pst = Yii::app()->db->createCommand()
		->select('title,id,url')
		->from('{{partners}}')
		->queryAll();

		if (!empty($pst))
			foreach ($pst as $p)
			{
				$partners[$p['id']] = $p;
				$url = parse_url($p['sprintf_url']);
				if (!empty($url['hostname']))
				{
					$url = 'http://' . $url['hostname'];
				}
				else
				{
					$url = '';
				}
				$partners[$p['id']]['url'] = $url;
				$partners[$p['id']]['type_id'] = 1;
			}
    	return $partners;
    }

    public function tableName() {
	return '{{partners}}';
    }

    /**
     *
     */

    public static function getPartnerList(){
    	$userPower = Yii::app()->user->getState('dmUserPower');

		return Yii::app()->db->createCommand()
		->select('title,id,url')
		->from('{{partners}}')
		->where('active <= ' . $userPower)
		->queryAll();
    }


}

?>
