<?php

class DevicesController extends Controller {
    
    
    public function actionAdmin(){
        
    }

    public function actionAdd() {
        $this->render('add');
    }

    public function actionView() {
        $id = Yii::app()->user->id;
        if ($id) {
            $devices = new CDevices;
            $device_count = $devices->count('user_id='.$id);
        } else $device_count=null;
        $this->render('/devices/view',array('device_count'=>$device_count));
    }

    public function actionRemove() {
        echo "remove";
    }

}

?>
