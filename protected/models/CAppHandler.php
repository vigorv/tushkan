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
            ->select('tf.title,tf.id, ppv.value as poster')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv', 'pv.id = tf.variant_id')
        //        ->join('{{product_pictures}} pp','pp.product_id = pv.product_id AND pp.tp = "poster" ')
        // Posters somewhere in the ass
            ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
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


    public static function getVtrItemA($item_id = 0, $user_id = 0)
    {
        return Yii::app()->db->createCommand()
            ->select('tf.title,tf.id,pv.product_id as product_id,p.partner_id as partner_id, ppv.value as poster, pf.fname as fname, pd.description')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv', 'pv.id = tf.variant_id')
            ->join('{{products}} p', ' p.id = pv.product_id')
            ->leftJoin('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
        //links in the ass
        // 10 - poster
            ->leftJoin('{{variant_qualities}} vq', ' vq.variant_id = pv.id')
            ->leftJoin('{{product_descriptions}} pd', 'pd.product_id = pv.product_id')
            ->join('{{product_files}} pf', 'pf.variant_quality_id = vq.id and pf.preset_id = 2')
            ->where('tf.user_id =' . $user_id . ' AND tf.id =  ' . $item_id)->limit(1)->query();
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
            ->select('tf.title,tf.id, ppv.value as poster')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv', 'pv.id = tf.variant_id')
        //        ->join('{{product_pictures}} pp','pp.product_id = pv.product_id AND pp.tp = "poster" ')
        // Posters somewhere in the ass
            ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
            ->where('tf.user_id =' . $user_id . $type_str.' AND tf.title LIKE "%'.$search.'%"')
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
            ->where('tf.user_id =' . $user_id . $type_str.' AND tf.title LIKE "%'.$search.'%"')
            ->queryScalar();
    }

    public static function RemoveFromMyProducts($item_id=0){
        return Yii::app()->db->createCommand()
            ->delete('{{typedfiles}}','id = :item_id',array(':item_id'=>$item_id));
    }

    /*
     *  Partners
     */

    public static function getPartnerList($userPower)
    {
        return Yii::app()->db->createCommand()
        ->select('title,id')
        ->from('{{partners}}')
        ->where('active <= '.$userPower )
        ->queryAll();
    }

    public static function getPartnerProductsForUser($search='',$partner_id=0, $page = 1, $count = 10){
        $offset = ($page - 1) * $count;
        $searchCondition = '';
        if (!($search == '')) {
            $searchCondition = ' AND p.title LIKE "%' . $search . '%"';
        }
        $partnerCondition='';
        if ($partner_id){
            $partnerCondition = 'AND prt.id = '.$partner_id;
        }

        $cmd = Yii::app()->db->createCommand()
            ->select('p.id, p.title AS ptitle, prt.id AS prtid, prt.title AS prttitle, pv.id AS pvid, ppv.value as image, COALESCE(tf.id,0) as cloud')
            ->from('{{products}} p')
            ->join('{{partners}} prt', 'p.partner_id=prt.id '.$partnerCondition)
            ->join('{{product_variants}} pv', 'pv.product_id=p.id')
            ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
            ->leftJoin('{{typedfiles}} tf', 'tf.variant_id = pv.id and tf.variant_quality_id = (select max(tf.variant_quality_id) from {{typedfiles}} tf WHERE tf.variant_id = pv.id Limit 1)' )
            ->leftJoin('{{prices}} pr','pr.variant_id = pv.id and pr.variant_quality_id = 2')
            ->where('pr.price is NULL AND p.active <= ' . Yii::app()->user->userPower . ' AND prt.active <= ' . Yii::app()->user->userPower . $searchCondition)
            ->order('pv.id ASC')
            ->group('p.id')
            ->limit($count,$offset);
        return $cmd->queryAll();
    }

    public static function countPartnerProductsForUser($search='',$partner_id=0){
        $searchCondition = '';
        if (!($search == '')) {
            $searchCondition = ' AND p.title LIKE "%' . $search . '%"';
        }
        $partnerCondition='';
        if ($partner_id){
            $partnerCondition = 'AND prt.id = '.$partner_id;
        }

        $cmd = Yii::app()->db->createCommand()
            ->select('Count(p.id)')
            ->from('{{products}} p')
            ->join('{{partners}} prt', 'p.partner_id=prt.id '.$partnerCondition)
            ->join('{{product_variants}} pv', 'pv.product_id=p.id')
            ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
            ->where('p.active <= ' . Yii::app()->user->userPower. ' AND prt.active <= ' . Yii::app()->user->userPower . $searchCondition);
        return $cmd->queryScalar();
    }


    public static function searchPartnerProductsForUser($partner_id,$search=''){

    }



    public static function searchAllProductsForUser($search){

    }

    public static function getProductFullInfo($item_id){
            return Yii::app()->db->createCommand()
            //->select('pv.product_id')
                //->select('*')
                ->select('pv.product_id as product_id,p.partner_id as partner_id, ppv.value as poster, pf.fname as fname, pd.description,COALESCE(tf.id,0) as cloud')
                ->from('{{product_variants}} pv')
                ->join('{{products}} p','product_id = p.id')
                ->leftJoin('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
            //links in the ass
            // 10 - poster
                ->leftJoin('{{variant_qualities}} vq', ' vq.variant_id = pv.id')
                ->leftJoin('{{product_descriptions}} pd', 'pd.product_id = pv.product_id')
                ->leftJoin('{{typedfiles}} tf', 'tf.variant_id = pv.id and tf.variant_quality_id = (select max(tf.variant_quality_id) from {{typedfiles}} tf WHERE tf.variant_id = pv.id Limit 1)' )
                ->join('{{product_files}} pf', 'pf.variant_quality_id = vq.id and pf.preset_id = 2')
                ->where('pv.id = :variant_id', array(':variant_id'=>$item_id))
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
                ->where('id = :variant_id and online_only = 0',array(':variant_id'=>$variant_id))->queryRow();
            if ($variant && !$variant['price'])
                return Yii::app()->db->createCommand()
                    ->insert('{{typedfiles}}',array('variant_id'=>$variant_id,'user_id'=>Yii::app()->user->id,'title'=>$variant['title'],'variant_quality_id'=>2));
        }
        return false;
    }


}