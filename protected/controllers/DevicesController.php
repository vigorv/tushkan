<?php

class DevicesController extends Controller {

    public function actionAdmin() {

    }

    public function actionAdd() {
        $model = new DeviceForm;
        if (isset($_POST['DeviceForm'])) {
// collects user input data
            $model->attributes = $_POST['DeviceForm'];
// validates user input and redirect to previous page if validated
            if ($model->validate())
                $this->redirect(Yii::app()->devices->returnUrl);
        }
// displays the login form
        $this->render('add', array('model' => $model));
    }

    public function actionView() {
        $device_count = CDevices::model()->count('user_id=' . Yii::app()->user->id);
        $this->render('/devices/view', array('device_count' => $device_count));
    }

    public function actionRemove() {
        echo "remove";
    }
}

?>
