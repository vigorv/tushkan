<?php

/**
 * 
 */
class CServers extends CActiveRecord {

    /**
     *
     * @param string $className
     * @return CActiveRecord
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public static  function convertIpToString($ip)
    {
        $long = 4294967295 - ($ip - 1);
        return long2ip(-$long);
    }
    
    public function sendCommand($action, $sid, $data) {
        $server = $this->findByPk($sid);
        if ($server == null) {
            
        } else {
            $sdata = serialize($data);
            $hash = '';
            $link='http://'.$server->ip.'/'.$action.'?hash='.$hash.'&data='.$sdata;
            file_get_contents($link);
        }
    }

    public function tableName() {
        return '{{fileservers}}';
    }

}

?>
