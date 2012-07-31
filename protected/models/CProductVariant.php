<?php

/**
 * модель вариантов товаров
 *
 */
class CProductVariant extends CActiveRecord {
    /**
     *
     * @param string $className
     * @return CActiveRecord
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return '{{product_variants}}';
    }

    public static function getPartnerVariantData($partner_id,$original_id){
        return Yii::app()->db->createCommand()
            ->select('pv.*,p.*')
            ->from('{{product_variants}} pv')
            ->leftJoin('{{products}} p','p.partner_id = :partner_id AND  pv.product_id = p.id',array(':partner_id'=>$partner_id))
            ->where('pv.id = :variant_id',array(':variant_id'=>$original_id))
            ->queryAll();
    }
}