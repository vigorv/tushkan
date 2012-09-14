<?php

class CUserProductUpdates extends CActiveRecord
{
    /**
     *
     * @param string $className
     * @return CUserProductUpdates
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{user_product_updates}}';
    }

}