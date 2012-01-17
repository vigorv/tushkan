<?php

class GoodsController extends Controller {

    public function actionIndex() {
        $per_page = 10;
        $count = CFilm::model()->count();
        $pages = new CPagination($count);
        $pages->pageSize = $per_page;
        $films = Yii::app()->db->createCommand()
                ->select('f.id, f.y, f.title, p.filename, GROUP_CONCAT(DISTINCT c.title SEPARATOR ", ") AS country')
                ->from('{{films}} f')
                ->join('{{countries_films}} cf', 'cf.film_id=f.id')
                ->join('{{countries}} c', 'cf.country_id=c.id')
                ->leftJoin('{{film_pictures}} p', 'p.film_id=f.id AND p.tp="smallposter"')
                ->group('f.id')
                ->limit($per_page, $pages->getOffset())
                ->queryAll();
        $goods = array(array('name' => 'Films', 'itemtype' => 'V1', 'items' => $films));
        $this->render('view', array('goods' => $goods));
    }

    public function actionAdmin() {
        $this->render('admin');
    }
    
    

}

?>
