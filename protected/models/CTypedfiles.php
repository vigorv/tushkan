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

class CTypedfiles extends CActiveRecord{
    /**
     *
     * @param string $className
     * @return CTypedfiles
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{typedfiles}}';
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
