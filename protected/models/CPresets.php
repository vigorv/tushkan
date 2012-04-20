<?php

/**
 *
 */
class CPresets {

    private static $_models = array();
# array with the options to create stream context

    /**
     *
     * @param type $className
     * @return CPresets
     */
    public static function model($className=__CLASS__) {
	if (isset(self::$_models[$className]))
	    return self::$_models[$className];
	else {
	    $model = self::$_models[$className] = new $className(null);
	    return $model;
	}
    }

    public function getPresets() {
    	$presets = array(
    		'unknown'	=> array('id' => 1, 'title' => 'unknown'),
    		'low'		=> array('id' => 2, 'title' => 'low'),
    		'medium'	=> array('id' => 3, 'title' => 'medium'),
    		'high'		=> array('id' => 4, 'title' => 'high'),
    		'ultra'		=> array('id' => 5, 'title' => 'ultra'),
    	);
    	return $presets;
    }

    public function getPresetID($name) {
    	$presets = $this->getPresets();

    	if (!empty($presets[$name]))
    	{
    		return $presets[$name]['id'];
    	}
    	else
    		return 0;
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