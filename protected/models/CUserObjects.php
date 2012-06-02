<?php

/**
 * ActiveRecord class for UserObjects
 *
 * @property $id
 * @property $title
 * @property $user_id
 * @property $type_id
 * @property $active
 * @property $parent_id
 */
class CUserObjects extends CActiveRecord
{

    /**
     *
     * @param string $className
     * @return CUserObjects
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{userobjects}}';
    }

    public function getList($user_id, $type_id = -1, $page = 1, $count = 100)
    {
        $offset = ($page - 1) * $count;
        if ($type_id >= 0) {
            $type_str = ' AND uo.type_id=' . $type_id;
        } else
            $type_str = '';
        return Yii::app()->db->createCommand()
            ->select('uo.title,uo.id')
            ->from('{{userobjects}} uo')
            ->where('uo.user_id =' . $user_id . $type_str)
            ->limit($count, $offset)
            ->queryAll();
    }

    public function getObjectsLike($user_id, $like, $page = 1, $per_page = 10, $type_id = -1)
    {
        $offset = ($page - 1) * $per_page;
        if ($type_id >= 0) {
            $type_str = ' AND uo.type_id=' . $type_id;
        } else
            $type_str = '';
        return Yii::app()->db->createCommand()
            ->select('uo.title,uo.id')
            ->from('{{userobjects}} uo')
            ->where('uo.user_id =' . $user_id . ' AND uo.title LIKE "%' . $like . '%"' . $type_str)
            ->limit($per_page, $offset)
            ->queryAll();
    }

    public function getVtrList($user_id, $type_id = -1, $page = 1, $count = 100){
        $offset = ($page - 1) * $count;
        if ($type_id >= 0) {
            $type_str = ' AND pv.type_id=' . $type_id;
        } else
            $type_str = '';
        return Yii::app()->db->createCommand()
            ->select('tf.title,tf.id, ppv.value as poster')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv','pv.id = tf.variant_id')
    //        ->join('{{product_pictures}} pp','pp.product_id = pv.product_id AND pp.tp = "poster" ')
            // Posters somewhere in the ass
            ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
            ->where('tf.user_id =' . $user_id . $type_str)
            ->limit($count, $offset)
            ->queryAll();
    }


    public function getVtrItemA($item_id=0,$user_id=0){
      return Yii::app()->db->createCommand()
          ->select('tf.title,tf.id,pv.product_id as product_id,p.partner_id as partner_id, ppv.value as poster, pf.fname as fname')
          ->from('{{typedfiles}} tf')
          ->join('{{product_variants}} pv','pv.id = tf.variant_id')
          ->join('{{products}} p', ' p.id = pv.product_id')
          ->leftJoin('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
            //links in the ass
            // 10 - poster
          ->leftJoin('{{variant_qualities}} vq',' vq.variant_id = pv.id')
          ->join('{{product_files}} pf', 'pf.variant_quality_id = vq.id and pf.preset_id = 2' )
          ->where('tf.user_id =' . $user_id .' AND tf.id =  '.$item_id)->limit(1)->query();
    }

}