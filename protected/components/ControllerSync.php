<?php
Yii::import('ext.classes.Utils');
/**
 * @property Utils $_utils
 */
class ControllerSync extends CController {
    var $_utils;
    public function init() {
        parent::init();
        $this->_utils = new Utils();
    }
}