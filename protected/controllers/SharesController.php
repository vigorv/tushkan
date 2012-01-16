<?php

class SharesController extends Controller {
       
    public function actionView() {
        $section = new Section();
        $this->render('view');
    }

}

?>
