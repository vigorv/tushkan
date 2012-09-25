<?php
/**
 * Author: SnowInG
 * mail: snowcanbe@gmail.com
 *
 */


class AjaxController extends Controller {

    public $layout = '//layouts/ajax';

    public function actionProductsTop(){
       isset($_GET['offset'])? $offset = (int)$_GET['offset'] : $offset = 0;
       if ($offset>20) return;
       if ($offset){
			$userPower = Yii::app()->user->UserPower;
           $pst = CProduct::model()->getProductList(array(10), $userPower, '',$offset, Yii::app()->params['product_top_count']);
           $this->render('/products/top_ajax', array('pst' => $pst));
       }
    }

}