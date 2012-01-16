<?php

class PartnersController extends Controller {
    
    public function actionAdmin(){
        $this->layout = '/layouts/admin';
        $this->render('admin');
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