<?php

/**
 * 
 */
class CXmlHandler {

    /**
     * Converts an array to Xml
     *
     * @param mixed $arData The array to convert
     * @param mixed $sRootNodeName The name of the root node in the returned Xml
     * @param string $sXml The converted Xml
     */
    public static function arrayToXml($arData, $sRootNodeName = 'data', $sXml = null) {
	// turn off compatibility mode as simple xml doesn't like it
	if (1 == ini_get('zend.ze1_compatibility_mode'))
	    ini_set('zend.ze1_compatibility_mode', 0);

	if (null == $sXml)
	    $sXml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><{$sRootNodeName} />");

	// loop through the data passed in.
	foreach ($arData as $_sKey => $_oValue) {
	    // no numeric keys in our xml please!
	    if (is_numeric($_sKey))
		$_sKey = "unknownNode_" . (string) $_sKey;

	    // replace anything not alpha numeric
	    $_sKey = preg_replace('/[^a-z]/i', '', $_sKey);

	    // if there is another array found recrusively call this function
	    if (is_array($_oValue)) {
		$_oNode = $sXml->addChild($_sKey);
		self::arrayToXml($_oValue, $sRootNodeName, $_oNode);
	    } else {
		// add single node.
		$_oValue = htmlentities($_oValue);
		$sXml->addChild($_sKey, $_oValue);
	    }
	}

	return( $sXml->asXML() );
    }

}