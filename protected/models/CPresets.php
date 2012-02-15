<?php

/**
 * 
 */
class CPresets {

    private static $_models = array();
# array with the options to create stream context

    public static function model($className=__CLASS__) {
	if (isset(self::$_models[$className]))
	    return self::$_models[$className];
	else {
	    $model = self::$_models[$className] = new $className(null);
	    return $model;
	}
    }

    public function getPresetID($name) {
	switch ($name) {
	    case 'high':
	    case 'good':
	    case 'normal':
		return 0;
	    case 'x480': return 1;
	    default: return false;
	}
    }

    /*
     * Presets
     * 
     * Video
     *  High, Good,Normal
     * 
     * Audio
     *  Flac, Mp3
     * 
     * Photo
     *  png, jpg
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     */
}