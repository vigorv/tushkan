<?php

/**
 * модель file пользователей
 *
 */
class CUserfiles extends CActiveRecord {
    /**
     * @property $id
     * @property $user_id
     * @property $is_dir
     * @property $fsize
     * @property $curent_fsize
     * @property $pid
     * @property $title
     * @property $fname
     */
    
    
    /**
     *
     * @param type $className
     * @return type 
     */
    
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function defaultScope() {
        return array(
            'alias' => 'f',
        );
    }

    public function tableName() {
        return '{{userfiles}}';
    }
    
    public function CreateFile(){
        
    }
    
    public function MoveFile(){
        
    }
    
    public function OpenFile(){
        
    }
    
    public function DeleteFile(){
        
    }
    
    
    

}