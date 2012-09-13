<?php

/**
 * @property $id
 * @property $title
 * @property $active
 * @property $sprintf_url
 * @property $approved
 * @property $hkey
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
//СТАРЫЙ СТАТИЧЕСКИЙ ВАРИАНТ
    	$partners = array(
    		1	=> array('id' => 1, 'title' => 'vxq', 'url' => 'http://videoxq.com', 'type_id' => 1),//type_id - какого типа контент предоставляет партнер (см. таблицу dm_product_types)
    		2	=> array('id' => 2, 'title' => 'fastlink', 'url' => 'http://fastlink.ws', 'type_id' => 1),
    		3	=> array('id' => 3, 'title' => 'rumedia', 'url' => 'http://rumedia.ws', 'type_id' => 1),
    	);
    	return $partners;
//*/
    	$partners = array();
		$pst = Yii::app()->db->createCommand()
		->select('title, id, sprintf_url, flag_zone')
		->from('{{partners}}')
		->queryAll();
		if (!empty($pst))
			foreach ($pst as $p)
			{
				$partners[$p['id']] = $p;
				$url = parse_url($p['sprintf_url']);
				if (!empty($url['host']))
				{
					$url = 'http://' . $url['host'];
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
    	//$userPower = Yii::app()->user->getState('dmUserPower');
		$result = Yii::app()->db->createCommand()
		->select('title,id,sprintf_url AS url')
		->from('{{partners}}')
         ->where('active <= :userpower',array(':userpower'=>Yii::app()->user->userPower))
		->queryAll();
        $partners = array();
        foreach ($result as $partner){
            $product_count = CPartners::countPartnerProductForUser($partner['id']);
            if ($product_count){
                $partners[] = $partner;
            }
        }
        return $partners;
    }

    public static function countPartnerProductForUser($partner_id = 0, $zone=0){
       // $userPower = Yii::app()->user->getState('dmUserPower');
        $cmd = Yii::app()->db->createCommand()
            ->select('count(id)')
            ->from('{{products}}')
            ->where('active <= :userpower AND partner_id=:partner_id ',array(':userpower'=>Yii::app()->user->userPower,':partner_id'=>$partner_id));
        if ($zone=0){
            $cmd->where('flag_zone=0');
        } else {
            // TODO: zones checks
            // For now if user has zone<>0 he get all
        }
        return $cmd -> queryScalar();
    }

    /**
     * @param int $product_id
     * @param int $partner_id
     * @return int
     */

    public static function setPartnerItemUpdate($partner_item_id=0,$partner_id=0){
        return Yii::app()->db
            ->createCommand("INSERT IGNORE INTO {{user_product_updates}} (user_id,product_id)"
            ." (SELECT tf.user_id as user_id,pv.product_id as product_id FROM {{products}} p"
            ." JOIN {{product_variants}} pv ON pv.product_id = p.id"
            ." JOIN {{typedfiles}} tf ON tf.variant_id = pv.id"
            ." WHERE p.original_id=".$partner_item_id." AND p.partner_id =".$partner_id.")" )->execute();

    }

}