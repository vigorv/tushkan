<?php

class ZonesController extends Controller {

    var $layout = 'admin';

    public function actionAdmin() {
        $criteria = new CDbCriteria();
        $count = CZones::model()->count($criteria);
        $server_count = CZones::model()->count();
        $per_page = 10;
        $pages = new CPagination($count);
        $pages->pageSize = $per_page;
        $server_list = CZones::model()->findAll($criteria);
        $this->render('admin', array('file_servers' => $server_list));
    }

}