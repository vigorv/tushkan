<?php

class FileserversController extends Controller {

    public function actionAdmin() {
        $this->layout = '/layouts/admin';   
        $this->render('admin');
    }

}

?>