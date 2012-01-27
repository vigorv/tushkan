<?php

/**
 * ContactForm class.
 * ContactForm is the data structure for keeping
 * contact form data. It is used by the 'contact' action of 'SiteController'.
 */
class FileServersForm extends CFormModel {

    public $title;
    public $ip;
    public $desc;
    public $active;
    public $zone_id;
    public $stype;

    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
                // name, email, subject and body are required
                //	array('name, email, subject, body', 'required'),
                // email has to be a valid email address
                //	array('email', 'email'),
                // verifyCode needs to be entered correctly
                //	array('verifyCode', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()),
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'title' => Yii::t('Files', 'Title'),
            'desc' => Yii::t('Files', 'Description'),
            'zone_id' => Yii::t('Files', 'Zone'),
            'stype' => Yii::t('Files', 'ServerType')
        );
    }

}