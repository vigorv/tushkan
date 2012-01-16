<?php

class GoodsController extends Controller {

    public function actionIndex() {
        $filmlist = CFilm::model()->findAll();
        $goods = array(array('name' => 'Films', 'itemtype' => 'V1', 'items' => $filmlist));
        $this->render('view', array('goods' => $goods));
    }

    public function actionAdmin() {
        $this->render('admin');
    }

}

?>
