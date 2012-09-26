<?php

/**
 * @property int(10) $id
 * @property bigint(20) $size
 * @property varchar(32) $md5
 * @property varchar(255) $fname
 * @property int(10) $preset_id
 * @property int(10) $variant_quality_id
 */
class CProductFiles extends CActiveRecord {
    /**
     *
     * @param string $className
     * @return CProductFiles
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return '{{product_files}}';
    }



}