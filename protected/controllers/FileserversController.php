<?php

class FileserversController extends Controller {

    var $layout = 'admin';

    public function actionAdmin() {
        $this->render('admin');
    }

    public function actionAdd() {
        $this->render('form');
    }

}

?>