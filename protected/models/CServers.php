<?php

define('DOWNLOAD_SERVER', 1);
define('UPLOAD_SERVER', 2);
define('CONTENT_SERVER', 3);
define('CONVERT_SERVER', 4);
define('TASK_SERVER', 5);

/**
 * @property id
 * @property ip
 * @property desc
 * @property active
 * @property zone_id
 * @property stype
 * @property alias
 * @property port
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

    public static function convertIpToString($ip) {
	$long = 4294967295 - ($ip - 1);
	return long2ip(-$long);
    }

    public static function convertIpToLong($ip) {
	return sprintf("%u", ip2long($ip));
    }

    public function sendCommand($action, $sid, $data) {
	$server = $this->findByPk($sid);
	if ($server == null) {
	    
	} else {
	    $sdata = serialize($data);
	    $hash = '';
	    $link = 'http://' . $server->ip . ':' . $server->port . '/' . $action . '?hash=' . $hash . '&data=' . $sdata;
	    file_get_contents($link);
	}
    }

    public function sendCommandAddr($action, $addr, $data) {
	$sdata = serialize($data);
	$hash = '';
	$link = 'http://' . $addr . '/' . $action . '?hash=' . $hash . '&data=' . $sdata;
	file_get_contents($link);
    }

    public function getServer($stype=0, $zone = 0) {
	$cond = array();
	if ($stype)
	    $cond['stype'] = $stype;
	if ($zone)
	    $cond['zone'] = $zone;
	$cond['active'] = 1;
	$server = CServers::model()->findByAttributes($cond);
	if ($server) {
	    if ($server['alias'] == '')
		return CServers::convertIpToString($server['ip']) . ':' . $server['port'];
	    else
		return $server['alias'] . ':' . $server['port'];
	} else
	    return false;
    }

    public function tableName() {
	return '{{fileservers}}';
    }

}

?>
