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

    /**
     * @static
     * @param $variant_id
     */
    public static function RemoveFileVariantWithLoc($variant_id){
        $loc_list = CFilelocations::getAllLocationsForVariant($variant_id);
        foreach($loc_list as $location){
            CServers::deleteFileOnServerByLocation($location);
        }
    }

}