<?php

class DevicesController extends Controller {
    /**
     * привязать устройство указанного типа
     *
     * @param integer $id - идентификатор типа устройства
     */
    public function actionAdd($id = 0) {
    	$result = '';
        $device = new CDevices();
        $device -> user_id = Yii::app()->user->id;
        $device -> active = 10;
        $device -> device_type_id = (int) $id;
        if ($device->save()){
            $result = $device->id;
        }
    	$this->render('/devices/add', array('result' => $result));
    }

    public function actionView($id = 0) {
        $info = CDevices::model()->findByPk(array('id' => $id, 'user_id' => Yii::app()->user->getId(), 'device_type_id' => '?0'));
        $this->render('/devices/view', array('info' => $info));
    }

    /**
     * отвязать устройство
     *
     * @param integer $id - идентификатор устройства пользователя
     */
    public function actionRemove($id) {
    	$result = '';
    	if ( CDevices::model()->delete('id = :id and user_id = :user_id',array(':id'=>(int)$id,':user_id'=>Yii::app()->user->id)))
    		$result = 'ok';
    	$this->render('/devices/remove', array('result' => $result));
    }

    public function actionSelect()
    {
    	$lst =  CDevices::getDeviceTypes();
    	$this->render('/devices/select', array('lst' => $lst));
    }

    public function actionIndex()
    {
		$tst = Utils::getDeviceTypes();
		$dst = Yii::app()->db->createCommand()
			->select('*')
			->from('{{userdevices}}')
			->where('user_id = :user_id', array(':user_id'=>Yii::app()->user->id))
			->queryAll();
		$this->render('/devices/index', array('tst' => $tst, 'dst' => $dst));
    }
}
