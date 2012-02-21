<?php

/**
 * @property $id
 * @property $file_id
 * @property $preset_id
 * @property $fsize
 * @property $fmd5
 */
class CFilesvariants extends CActiveRecord {

    /**
     *
     * @param string $className
     * @return CFileVariants
     */
    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public function tableName() {
	return '{{files_variants}}';
    }

}