<?php

/**
 * модель таблицы товаров
 *
 */
class CProduct extends CActiveRecord {
    /**
     *
     * @param string $className
     * @return CActiveRecord
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function behaviors() {
        return array(
            'params' => array(
                'class' => 'ext.params_products.ParamsProductsBehavior',
            ),
            'description' => array(
                'class' => 'ext.product_descriptions.ProductDescriptionsBehavior',
            ),
        );
    }

    public function tableName() {
        return '{{products}}';
    }

}