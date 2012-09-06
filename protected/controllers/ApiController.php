<?php

/**
 *  ApiController
 * @author Snow
 *
 */
class ApiController extends Controller {

    public $layout = '//layouts/ajax';
    var $user_id;

    public function actionStatusImage(){
        if (isset($_REQUEST['partner_id']) && isset($_REQUEST['partner_item_id'])){
            $partner_id = (int) $_REQUEST['partner_id'];
            $partner_item_id = (int) $_REQUEST['partner_item_id'];
            /**
             * a. Find Partners
             * b. Find Variants
             * ->> State: Already Converted
             * c. Check For Added
             * ->> State: not Converted
             * c. Check for Queue
             */
            $partner = CPartners::model()->find('id = :partner_id',array(':partner_id'=>$partner_id));
            if ($partner && $partner->id && Yii::app()->user->id){
                $variants = CProductVariant::getPartnerVariantData($partner->id,$partner_item_id);
                $variant = current($variants);
                if ($variant['id']){
                    if (CTypedfiles::DidUserHavePartnerVariant(Yii::app()->user->id,$variant['id'])){
                        $image = new Imagick('img/cloud_added.jpg');
                    } else{
                        $image = new Imagick('img/cloud_add_fast.jpg');
                    }
                } else{
                    if (CConvertQueue::model()->find('original_variant_id = :variant_id AND partner_id = :partner_id AND user_id = :user_id',array(':variant_id'=>$partner_item_id,':partner_id'=>$partner_id,':user_id'=>Yii::app()->user->id))){
                        $image = new Imagick('img/cloud_in_process.jpg');
                    } else {
                        $image = new Imagick('img/cloud_add_long.jpg');
                    }
                }
            } else {
                $image = new Imagick('img/cloud_unknown.jpg');
            }
            header("Content-type: image/jpeg");
            echo $image;
        }
    }

    public function actionCloudButton(){
        if (isset($_REQUEST['partner_id']) && isset($_REQUEST['partner_item_id'])){
            $partner_id = (int) $_REQUEST['partner_id'];
            $partner_item_id = (int) $_REQUEST['partner_item_id'];
            /**
             * a. Find Partners
             * b. Find Variants
             * ->> State: Already Converted
             * c. Check For Added
             * ->> State: not Converted
             * c. Check for Queue
             */
            $partner = CPartners::model()->find('id = :partner_id',array(':partner_id'=>$partner_id));
            if ($partner && $partner->id && Yii::app()->user->id){
                $variants = CProductVariant::getPartnerVariantData($partner->id,$partner_item_id);
                $variant = current($variants);
                if ($variant['id']){
                    if (CTypedfiles::DidUserHavePartnerVariant(Yii::app()->user->id,$variant['id'])){
                        echo json_encode(array("msg"=>"Already added","status_code"=>2));
                    } else{
                        $queue = new CConvertQueue();
                        $queue -> variant_id = $variant['id'];
                        $queue -> original_variant_id = $partner_item_id;
                        $queue -> partner_id = $partner_id;
                        $queue -> user_id = Yii::app()->user->id;
                        $queue -> priority = 200;
                        $queue ->save();
                        echo json_encode(array("msg"=>"Added","status_code"=>1));
                    }
                } else{
                    if (CConvertQueue::model()->find('original_variant_id = :variant_id AND partner_id = :partner_id AND user_id = :user_id',array(':variant_id'=>$partner_item_id,':partner_id'=>$partner_id,':user_id'=>Yii::app()->user->id))){
                        echo json_encode(array("msg"=>"In queue","status_code"=>3));
                    } else {
                        $queue = new CConvertQueue();
                        $queue -> original_variant_id = $partner_item_id;
                        $queue -> partner_id = $partner_id;
                        $queue -> user_id = Yii::app()->user->id;
                        $queue -> priority = 100;
                        $queue ->save();
                        echo json_encode(array("msg"=>"Added","status_code"=>1));
                    }
                }
            } else {
                echo json_encode(array("msg"=>"Unknown user or product","status_code"=>0));
            }
        }
    }

    public function actionUpdatePartnerData(){
    	$zFlag = Yii::app()->user->UserInZone;
    	$zSql = '';
    	if (!$zFlag)
    	{
    		$zSql = ' AND p.flag_zone = 0';
    	}

        if(YII_DEBUG){
            $item_id = (int)$_REQUEST['item_id'];
            $partner_id = (int)$_REQUEST['partner_id'];
          $result = Yii::app()->db
              ->createCommand(
        " SELECT tf.user_id as user_id,pv.product_id as product_id FROM {{products}} p"
            ." JOIN {{product_variants}} pv ON pv.product_id = p.id"
            ." JOIN {{typedfiles}} tf ON tf.variant_id = pv.id"
            ." WHERE p.original_id=".$item_id." AND p.partner_id =".$partner_id.$zSql)->queryAll();
        var_dump($result);
        $affected = Yii::app()->db
            ->createCommand("INSERT IGNORE INTO {{user_product_updates}} (user_id,product_id)"
            ." (SELECT tf.user_id as user_id,pv.product_id as product_id FROM {{products}} p"
            ." JOIN {{product_variants}} pv ON pv.product_id = p.id"
            ." JOIN {{typedfiles}} tf ON tf.variant_id = pv.id"
            ." WHERE p.original_id=".$item_id." AND p.partner_id =".$partner_id.")" )->execute();

            echo serialize(array('count'=>$affected));

            return;
        }
        if (isset($_REQUEST['fdata']) && isset($_REQUEST['sdata']) && isset($_REQUEST['partner_id'])) {
            $partner_id = (int) $_REQUEST['partner_id'];
            $partner = CPartners::model()->findByAttributes(array('id'=>$partner_id));
            // CPartners partner
            $sdata = hash('sha512',$_REQUEST['fdata'].$partner->hkey);
            if ($sdata == $_REQUEST['sdata']){
                $fdata = unserialize(base64_encode($_REQUEST['fdata']));
                if (isset($fdata['item_id'])){
                    $item_id = $fdata['item_id'];
                    $affected = Yii::app()->db
                        ->createCommand("INSERT IGNORE INTO {{user_product_updates}} (user_id,product_id)"
                        ." (SELECT tf.user_id as user_id,pv.product_id as product_id FROM {{products}} p"
                        ." JOIN {{product_variants}} pv ON pv.product_id = p.id"
                        ." JOIN {{typedfiles}} tf ON tf.variant_id = pv.id"
                        ." WHERE p.original_id=".$item_id." AND p.partner_id =".$partner_id.")" )->execute();
                    echo serialize(array('count'=>$affected));
                }
            }else{

            }
        }
    }
}

