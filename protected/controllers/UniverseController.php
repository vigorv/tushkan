<?php

class UniverseController extends Controller {

    public function accessRules() {
        
    }

    public function actionError() {
        $error = Yii::app()->errorHandler->error;
        if ($error) {
            if (Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                var_dump($error);
                //$this->render('error', $error);
        }
    }

    public function actionIndex() {
        //if (!Yii::app()->user->isGuest) {
        $this->render('index');
    }

    public function actionAdd($step=1) {
        $step = (int) $step;
        $this->render('steps');
    }

    public function actionExt() {
        if(isset($_GET['goods_add'])){
            
            
        }
        
        $this->render('steps');
    }

}

?>
