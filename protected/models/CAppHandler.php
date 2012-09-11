<?php
/**
 * Author: snow
 * Date: 04.06.12
 */

class CAppHandler
{
    /*
     *  UserProducts
     */

    public static function getVtrList($user_id, $type_id = -1, $page = 1, $count = 10)
    {
        $offset = ($page - 1) * $count;
        if ($type_id >= 0) {
            $type_str = ' AND pv.type_id=' . $type_id;
        } else
            $type_str = '';
        return Yii::app()->db->createCommand()
            ->select('tf.title,tf.id, ppv.value as poster,pv.id as variant_id, COALESCE(ppvT.value,"-")  as original_title')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv', 'pv.id = tf.variant_id')
        //        ->join('{{product_pictures}} pp','pp.product_id = pv.product_id AND pp.tp = "poster" ')
        // Posters somewhere in the ass
            ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
            ->leftJoin('{{product_param_values}} ppvT', 'pv.id=ppvT.variant_id AND ppvT.param_id = 12')//original_title
            ->where('tf.user_id =' . $user_id . $type_str)
            ->limit($count, $offset)
            ->queryAll();
    }

    public static function countVtrList($user_id, $type_id = -1)
    {

        if ($type_id >= 0) {
            $type_str = ' AND pv.type_id=' . $type_id;
        } else
            $type_str = '';
        return Yii::app()->db->createCommand()
            ->select('count(tf.id) as count')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv', 'pv.id = tf.variant_id')
            ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
            ->where('tf.user_id =' . $user_id . $type_str)
            ->queryScalar();
    }


    public static function getVtrItemA($item_id = 0, $user_id = 0, $preset = 2)
    {
        switch ($preset){
            case 3:
                break;
            default:
                $preset=2;
                break;
        }



        return Yii::app()->db->createCommand()
            ->select('tf.title,tf.id, pv.id as variant_id, pv.product_id as product_id,p.partner_id as partner_id, ppv.value as poster,COALESCE(ppvY.value,0) as year,  COALESCE(ppvC.value,"-") as  country, COALESCE(ppvG.value,"-")  as genre, COALESCE(ppvT.value,"-")  as original_title, pf.fname as fname, pd.description')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv', 'pv.id = tf.variant_id')
            ->join('{{products}} p', ' p.id = pv.product_id')
            ->leftJoin('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')   //poster
            ->leftJoin('{{product_param_values}} ppvY', 'pv.id=ppvY.variant_id AND ppvY.param_id = 13')//year
            ->leftJoin('{{product_param_values}} ppvC', 'pv.id=ppvC.variant_id AND ppvC.param_id = 14')//country
            ->leftJoin('{{product_param_values}} ppvG', 'pv.id=ppvG.variant_id AND ppvG.param_id = 18')//genre
            ->leftJoin('{{product_param_values}} ppvT', 'pv.id=ppvT.variant_id AND ppvT.param_id = 12')//original_title
        //links in the ass
        // 10 - poster
            ->leftJoin('{{variant_qualities}} vq', ' vq.variant_id = pv.id')
            ->leftJoin('{{product_descriptions}} pd', 'pd.product_id = pv.product_id')
            ->join('{{product_files}} pf', 'pf.variant_quality_id = vq.id and pf.preset_id = '.$preset)
            ->where('tf.user_id =' . $user_id . ' AND tf.id =  ' . $item_id )->limit(1)->query();
    }


    /**
     * @static
     * @param string $search
     * @param int $user_id
     * @param $type_id
     * @param int $page
     * @param int $count
     * @return array
     */
    public static function findUserProducts($search=' ', $user_id =0, $type_id =-1 ,$page = 1, $count = 10){
        $offset = ($page - 1) * $count;
        if ($type_id >= 0) {
            $type_str = ' AND pv.type_id=' . $type_id;
        } else
            $type_str = '';
        return Yii::app()->db->createCommand()
            ->select('tf.title,tf.id, ppv.value as poster,pv.id as variant_id, COALESCE(ppvT.value,"-")  as original_title')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv', 'pv.id = tf.variant_id')
        //        ->join('{{product_pictures}} pp','pp.product_id = pv.product_id AND pp.tp = "poster" ')
        // Posters somewhere in the ass
            ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
            ->leftJoin('{{product_param_values}} ppvT', 'pv.id=ppvT.variant_id AND ppvT.param_id = 12')//original_title
            ->where('tf.user_id =' . $user_id . $type_str.' AND (tf.title LIKE "%'.$search.'%" OR ppvT.value LIKE "%'.$search.'%")')
            ->limit($count, $offset)
            ->queryAll();
    }

    public static function countFoundProducts($search=' ', $user_id =0, $type_id =-1 ){
        if ($type_id >= 0) {
            $type_str = ' AND pv.type_id=' . $type_id;
        } else
            $type_str = '';
        return Yii::app()->db->cache(50)->createCommand()
            ->select('count(tf.id)as count')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv', 'pv.id = tf.variant_id')
        //        ->join('{{product_pictures}} pp','pp.product_id = pv.product_id AND pp.tp = "poster" ')
        // Posters somewhere in the ass
            ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
            ->leftJoin('{{product_param_values}} ppvT', 'pv.id=ppvT.variant_id AND ppvT.param_id = 12')//original_title
            ->where('tf.user_id =' . $user_id . $type_str.' AND (tf.title LIKE "%'.$search.'%" OR ppvT.value LIKE "%'.$search.'%")')
            ->queryScalar();
    }

    public static function RemoveFromMyProducts($item_id=0){
        return Yii::app()->db->createCommand()
            ->delete('{{typedfiles}}','id = :item_id and user_id = :user_id',array(':item_id'=>$item_id,':user_id'=>Yii::app()->user->id));
    }

    /*
     *  Partners
     */

    public static function getPartnerList($userPower)
    {
        return Yii::app()->db->createCommand()
        ->select('p.title,p.id')
        ->from('{{partners}} p')
        ->leftJoin('{{partners_tariffs}} pt','pt.partner_id = p.id')
        ->where('pt.partner_id is NULL && p.active <='.Yii::app()->user->userPower)
        ->queryAll();
    }

    public static function getPartnerProductsForUser($search='',$partner_id=0, $page = 1, $count = 10){
        $offset = ($page - 1) * $count;
        $searchCondition = '';
        if (!($search == '')) {
            $searchCondition = ' AND (p.title LIKE "%' . $search . '%" OR ppvT.value LIKE "%' . $search . '%")';
        }
        $partnerCondition='';
        if ($partner_id){
            $partnerCondition =' AND prt.id = '.$partner_id;
        }

        $zFlag = Yii::app()->user->UserInZone;
    	$zSql = '';
    	if (!$zFlag)
    	{
    		$zSql = ' AND p.flag_zone = 0';
    	}

        $cmd = Yii::app()->db->createCommand()
            ->select('p.id, p.title AS ptitle,pv.title as pvtitle, prt.id AS prtid, prt.title AS prttitle, pv.id AS variant_id, ppv.value as image, COALESCE(ppvT.value,"-")  as original_title, COALESCE(tf.id,0) as cloud_id')
            ->from('{{products}} p')
            ->join('{{partners}} prt', 'p.partner_id=prt.id AND prt.active<='.Yii::app()->user->userPower.$partnerCondition)
            ->leftJoin('{{partners_tariffs}} pt','pt.partner_id = prt.id')
            ->join('{{product_variants}} pv', 'pv.product_id=p.id')
            ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
            ->leftJoin('{{product_param_values}} ppvT', 'pv.id=ppvT.variant_id AND ppvT.param_id = 12')
            ->leftJoin('{{typedfiles}} tf', 'tf.variant_id = pv.id and tf.variant_quality_id = (select max(tf.variant_quality_id) from {{typedfiles}} tf WHERE tf.variant_id = pv.id Limit 1) AND tf.user_id = '.Yii::app()->user->id )
            ->leftJoin('{{prices}} pr','pr.variant_id = pv.id and pr.variant_quality_id = 2')
            ->where('pt.partner_id is NULL AND pr.price is NULL AND pv.childs = "" AND p.active <= ' . Yii::app()->user->userPower . $searchCondition . $zSql)
            ->order('pv.id ASC')
            //->group('p.id')
            ->limit($count,$offset);
        return $cmd->queryAll();
    }

    public static function countPartnerProductsForUser($search='',$partner_id=0){
        $searchCondition = '';
        if (!($search == '')) {
            $searchCondition = ' AND (p.title LIKE "%' . $search . '%" OR ppvT.value LIKE "%' . $search . '%")';
        }
        $partnerCondition='';
        if ($partner_id){
            $partnerCondition = ' AND prt.id = '.$partner_id;
        }

    	$zFlag = Yii::app()->user->UserInZone;
    	$zSql = '';
    	if (!$zFlag)
    	{
    		$zSql = ' AND p.flag_zone = 0';
    	}

        $cmd = Yii::app()->db->createCommand()
            ->select('Count(p.id)')
            ->from('{{products}} p')
            ->join('{{partners}} prt', 'p.partner_id=prt.id AND prt.active<='.Yii::app()->user->userPower.$partnerCondition)
            ->leftJoin('{{partners_tariffs}} pt','pt.partner_id = prt.id')
            ->join('{{product_variants}} pv', 'pv.product_id=p.id')
            ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
            ->leftJoin('{{product_param_values}} ppvT', 'pv.id=ppvT.variant_id AND ppvT.param_id = 12')
            ->leftJoin('{{prices}} pr','pr.variant_id = pv.id and pr.variant_quality_id = 2')
            ->where('pt.partner_id is NULL AND pr.price is NULL AND pv.childs = "" AND p.active <= ' . Yii::app()->user->userPower . $searchCondition . $zSql);
            //->group('p.id');
        return $cmd->queryScalar();
    }


    public static function searchPartnerProductsForUser($partner_id,$search=''){

    }



    public static function searchAllProductsForUser($search){

    }

    public static function getProductFullInfo($variant_id){
        $zFlag = Yii::app()->user->UserInZone;
        $zSql = '';
        if (!$zFlag)
        {
            $zSql = ' AND p.flag_zone = 0';
        }
            return Yii::app()->db->createCommand()
            //->select('pv.product_id')
                //->select('*')
                ->select('pv.title as pvtitle, pv.product_id as product_id,pv.id as variant_id, p.partner_id as partner_id, ppv.value as poster, COALESCE(ppvY.value,0) as year,  COALESCE(ppvC.value,"-") as  country, COALESCE(ppvG.value,"-")  as genre, COALESCE(ppvT.value,"-")  as original_title,pf.fname as fname, pd.description,COALESCE(tf.id,0) as cloud_id')
                ->from('{{product_variants}} pv')
                ->join('{{products}} p','product_id = p.id')
                ->leftJoin('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10') //poster
                ->leftJoin('{{product_param_values}} ppvY', 'pv.id=ppvY.variant_id AND ppvY.param_id = 13')//year
                ->leftJoin('{{product_param_values}} ppvC', 'pv.id=ppvC.variant_id AND ppvC.param_id = 14')//country
                ->leftJoin('{{product_param_values}} ppvG', 'pv.id=ppvG.variant_id AND ppvG.param_id = 18')//genre
                ->leftJoin('{{product_param_values}} ppvT', 'pv.id=ppvT.variant_id AND ppvT.param_id = 12')//original_title
            //links in the ass
            // 10 - poster
                ->leftJoin('{{variant_qualities}} vq', ' vq.variant_id = pv.id')
                ->leftJoin('{{product_descriptions}} pd', 'pd.product_id = pv.product_id')
                ->leftJoin('{{typedfiles}} tf', 'tf.variant_id = pv.id and tf.variant_quality_id = (select max(tf.variant_quality_id) from {{typedfiles}} tf WHERE tf.variant_id = pv.id Limit 1)  AND tf.user_id = '.Yii::app()->user->id )
                ->join('{{product_files}} pf', 'pf.variant_quality_id = vq.id and pf.preset_id = 2')
                ->where('pv.id = :variant_id'.$zSql , array(':variant_id'=>$variant_id))
                ->limit(1)->query();
    }

    public static function addProductToUser($variant_id=0){
        $found = Yii::app()->db->createCommand()
            ->select('count(id)')->from('{{typedfiles}}')
            ->where('variant_id = :variant_id AND user_id = :user_id',array(':variant_id'=>$variant_id,':user_id'=>Yii::app()->user->id))->queryScalar();
        if(!$found){
            $variant = Yii::app()->db->createCommand()
                ->select("pv.title, COALESCE(pr.price,0) as price")
                ->from('{{product_variants}} pv')
                ->leftjoin('{{prices}} pr','pr.variant_id = '.(int)$variant_id.' and pr.variant_quality_id = 2')
                ->where('pv.id = :variant_id and pv.online_only = 0',array(':variant_id'=>$variant_id))->queryRow();
            if ($variant && !$variant['price']){
              $rows = Yii::app()->db->createCommand()
                    ->insert('{{typedfiles}}',array('variant_id'=>$variant_id,'user_id'=>Yii::app()->user->id,'title'=>$variant['title'],'variant_quality_id'=>2));
                if ($rows)
                    return  Yii::app()->db->getLastInsertID();
            }
        }
        return false;
    }

    public  static function removeFromUser($item_id=0){
        Yii::app()->db->createCommand()
            ->delete('{{typedfiles}}','id = :item_id AND user_id = :user_id',array(':item_id'=>$item_id,':user_id'=>Yii::app()->user->id));
    }


}