<?php

class DeviceForm extends CFormModel {

    public $dname;
    public $dtype;
    public $desc;

    public function rules() {
        return array(
            //name and type are required
            array('dtype,dname' => 'required'),
            array('dname','length','max'=>16,'min'=>1),
            array('desc','safe')
        );
    }

    public function attributeLabels() {
        return array(
            'dname' => Yii::t('device', 'Device title'),
            'dtype' => Yii::t('device', 'Device type'),
            'desc' => Yii::t('device', 'Description'),
        );
    }

}

?>
