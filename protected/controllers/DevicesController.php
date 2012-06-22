<?php

class DevicesController extends Controller {
    /**
     * привязать устройство указанного типа
     *
     * @param integer $id - идентификатор типа устройства
     */
    public function actionAdd($id = 0) {
    	$result = '';
    	$sql = 'INSERT INTO {{userdevices}} (id, user_id, title, device_type_id, guid, active, hash)
    		VALUES (null, ' . Yii::app()->user->getId() . ', "", :type, "", 10, "")
    	';
    	$cmd = Yii::app()->db->createCommand($sql);
    	$cmd->bindParam(':type', $id, PDO::PARAM_INT);
    	if ($cmd->execute())
    		$result = 'ok';
    	$this->render('/devices/add', array('result' => $result));
    }

    public function actionView($id = 0) {
        //$device_count = CDevices::model()->count('user_id=' . Yii::app()->user->id);
        $info = CDevices::model()->findByPk(array('user_id' => Yii::app()->user->getId(), 'id' => $id));
        $this->render('/devices/view', array('info' => $info));
    }

    /**
     * отвязать устройство
     *
     * @param integer $id - идентификатор устройства пользователя
     */
    public function actionRemove($id) {
    	$result = '';
        $sql = 'DELETE FROM {{userdevices}} WHERE id = :id AND user_id = ' . Yii::app()->user->getId();
    	$cmd = Yii::app()->db->createCommand($sql);
    	$cmd->bindParam(':id', $id, PDO::PARAM_INT);
    	if ($cmd->execute())
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
			->where('user_id = ' . Yii::app()->user->getId())
			->queryAll();
		$this->render('/devices/index', array('tst' => $tst, 'dst' => $dst));
    }
}
