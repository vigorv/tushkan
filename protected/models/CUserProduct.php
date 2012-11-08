<?php

/***
 * Product that user add to self
 * @property $id
 * @property $variant_id
 * @property $user_id
 * @property $title
 * @property $collection_id
 * @property $variant_quality_id
 */

class CUserProduct extends CActiveRecord{
    /**
     *
     * @param string $className
     * @return CUserProduct
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{typedfiles}}';
    }

    public static function findUserProducts($search='', $user_id =0, $type_id =-1 ,$page = 1, $count = 10){
        $offset = ($page - 1) * $count;
        if ($type_id >= 0) {
            $type_str = ' AND pv.type_id=' . $type_id;
        } else
            $type_str = '';
        return Yii::app()->db->createCommand()
            ->select('tf.title,tf.id, ppv.value as poster,pv.id as variant_id, COALESCE(ppvT.value,"-")  as original_title')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv', 'pv.id = tf.variant_id')
            ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
            ->leftJoin('{{product_param_values}} ppvT', 'pv.id=ppvT.variant_id AND ppvT.param_id = 12')//original_title
            ->where('pv.online_only = 0  and tf.user_id =' . $user_id . $type_str.' AND (tf.title LIKE "%'.$search.'%" OR ppvT.value LIKE "%'.$search.'%")')
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
            ->where('pv.online_only = 0  and tf.user_id =' . $user_id . $type_str.' AND (tf.title LIKE "%'.$search.'%" OR ppvT.value LIKE "%'.$search.'%")')
            ->queryScalar();
    }

    public static function getUserProduct($user_product_id=0,$user_id=0){
        return Yii::app()->db->createCommand()
            ->select('tf.title,tf.id, pv.id as variant_id, pv.product_id as product_id,p.partner_id as partner_id, ppv.value as poster,COALESCE(ppvY.value,0) as year,  COALESCE(ppvC.value,"-") as  country, COALESCE(ppvG.value,"-")  as genre, COALESCE(ppvT.value,"-")  as original_title,  pd.description')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv', 'pv.id = tf.variant_id')
            ->join('{{products}} p', ' p.id = pv.product_id')
            ->leftJoin('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')   //poster
            ->leftJoin('{{product_param_values}} ppvY', 'pv.id=ppvY.variant_id AND ppvY.param_id = 13')//year
            ->leftJoin('{{product_param_values}} ppvC', 'pv.id=ppvC.variant_id AND ppvC.param_id = 14')//country
            ->leftJoin('{{product_param_values}} ppvG', 'pv.id=ppvG.variant_id AND ppvG.param_id = 18')//genre
            ->leftJoin('{{product_param_values}} ppvT', 'pv.id=ppvT.variant_id AND ppvT.param_id = 12')//original_title
         //   ->leftJoin('{{variant_qualities}} vq', ' vq.variant_id = pv.id')
            ->leftJoin('{{product_descriptions}} pd', 'pd.product_id = pv.product_id')
            ->where('tf.user_id =' . $user_id . ' AND tf.id =  ' . $user_product_id )->limit(1)->queryAll();
    }


    public static function DidUserHavePartnerVariant($user_id,$partner_variant_id){
        return Yii::app()->db->createCommand()
            ->select('Count(tf.id)')
            ->from('{{typedfiles}} tf')
            ->where('tf.user_id = :user_id AND tf.variant_id = :variant_id',array(':user_id'=>$user_id,':variant_id'=>$partner_variant_id))
            ->queryScalar();
    }

    public static function GetPartnerFileData($file_id){
        return Yii::app()->db->createCommand()
            ->select('p.partner_id as partner_id,pf.fname as fname')
            ->from('{{product_files}} pf')
            ->join('{{variant_qualities}} vq','vq.id = pf.variant_quality_id')
            ->join('{{product_variants}} pv', 'pv.id = vq.variant_id')
            ->join('{{products}} p', 'pv.product_id = p.id')
            ->join('{{typedfiles}} tf', 'tf.variant_id = pv.id')
            ->where('pf.id =:file_id',array('file_id'=>$file_id))
            ->queryAll();
    }

}