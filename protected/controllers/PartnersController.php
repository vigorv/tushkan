<?php

class PartnersController extends Controller {

    public function actionAdmin() {
        $this->layout = '/layouts/admin';

        $criteria = new CDbCriteria();
        //$criteria->join = '{{products}} pr ON (pr.partner_id = p.id)';
        $count = CPartners::model()->count($criteria);
        $server_count = CPartners::model()->count();

        $per_page = 10;
        $pages = new CPagination($count);
        $pages->pageSize = $per_page;

        $partner_list = CPartners::model()->findAll($criteria);
        $this->render('admin', array('partner_list' => $partner_list));


    }

    public function actionIndex() {
        $this->render('view');
    }

    public function actionPartnerList($id) {
        //get partner script
    //
    }

    public function actionPartnerItemsList() {

    }

    public function actionPartnerItemDetail() {

    }

    public function actionPartnerFilterList() {

    }

    public function actionPartnerItemPurchase() {

    }

}