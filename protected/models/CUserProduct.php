<?php

/***
 * Product that user add to self
 * @property $id
 * @property $variant_id
 * @property $user_id
 * @property $title
 * @property $collection_id
 * @property $variant_quality_id
 */

class CUserProduct extends CActiveRecord
{
    /**
     *
     * @param string $className
     * @return CUserProduct
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{typedfiles}}';
    }

    public static function findUserProducts($search = '', $user_id = 0, $type_id = -1, $page = 1, $count = 10)
    {
        $offset = ($page - 1) * $count;
        if ($type_id >= 0) {
            $type_str = ' AND pv.type_id=' . $type_id;
        } else
            $type_str = '';
        return Yii::app()->db->createCommand()
            ->select('tf.title,tf.id, ppv.value as image,pv.id as variant_id, COALESCE(ppvT.value,"-")  as original_title')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv', 'pv.id = tf.variant_id')
            ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
            ->leftJoin('{{product_param_values}} ppvT', 'pv.id=ppvT.variant_id AND ppvT.param_id = 12')//original_title
            ->where('pv.online_only = 0  and tf.user_id =' . $user_id . $type_str . ' AND (tf.title LIKE "%' . $search . '%" OR ppvT.value LIKE "%' . $search . '%")')
            ->limit($count, $offset)
            ->queryAll();
    }

    public static function countFoundProducts($search = ' ', $user_id = 0, $type_id = -1)
    {
        if ($type_id >= 0) {
            $type_str = ' AND pv.type_id=' . $type_id;
        } else
            $type_str = '';
        return Yii::app()->db->cache(50)->createCommand()
            ->select('count(tf.id)as count')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv', 'pv.id = tf.variant_id')
        //        ->join('{{product_pictures}} pp','pp.product_id = pv.product_id AND pp.tp = "poster" ')
        // Posters somewhere in the ass
            ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
            ->leftJoin('{{product_param_values}} ppvT', 'pv.id=ppvT.variant_id AND ppvT.param_id = 12')//original_title
            ->where('pv.online_only = 0  and tf.user_id =' . $user_id . $type_str . ' AND (tf.title LIKE "%' . $search . '%" OR ppvT.value LIKE "%' . $search . '%")')
            ->queryScalar();
    }

    public static function getUserProduct($user_product_id = 0, $user_id = 0)
    {
        $data = Yii::app()->db->createCommand()
            ->select('tf.title,tf.id, pv.id as variant_id, pv.product_id as product_id,p.partner_id as partner_id, ppv.value as image,COALESCE(ppvY.value,0) as year,  COALESCE(ppvC.value,"-") as  country, COALESCE(ppvG.value,"-")  as genre, COALESCE(ppvT.value,"-")  as original_title,  pd.description')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv', 'pv.id = tf.variant_id')
            ->join('{{products}} p', ' p.id = pv.product_id')
            ->leftJoin('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10') //poster
            ->leftJoin('{{product_param_values}} ppvY', 'pv.id=ppvY.variant_id AND ppvY.param_id = 13')//year
            ->leftJoin('{{product_param_values}} ppvC', 'pv.id=ppvC.variant_id AND ppvC.param_id = 14')//country
            ->leftJoin('{{product_param_values}} ppvG', 'pv.id=ppvG.variant_id AND ppvG.param_id = 18')//genre
            ->leftJoin('{{product_param_values}} ppvT', 'pv.id=ppvT.variant_id AND ppvT.param_id = 12')//original_title
        //   ->leftJoin('{{variant_qualities}} vq', ' vq.variant_id = pv.id')
            ->leftJoin('{{product_descriptions}} pd', 'pd.product_id = pv.product_id')
            ->where('tf.user_id =' . $user_id . ' AND tf.id =  ' . $user_product_id)->limit(1)->queryAll();
        $data[0]['files']= Yii::app()->db->createCommand()
            ->select('pf.fname as fname,pf.id as fid , pf.preset_id as preset_id')
            ->from('{{variant_qualities}} vq')
            ->join('{{product_files}} pf', 'pf.variant_quality_id = vq.id')
            ->where('vq.variant_id = '. $data[0]['variant_id'])
            ->queryAll();
        return $data;
    }


    public static function DidUserHavePartnerVariant($user_id, $partner_variant_id)
    {
        return Yii::app()->db->createCommand()
            ->select('Count(tf.id)')
            ->from('{{typedfiles}} tf')
            ->where('tf.user_id = :user_id AND tf.variant_id = :variant_id', array(':user_id' => $user_id, ':variant_id' => $partner_variant_id))
            ->queryScalar();
    }

    public static function GetPartnerFileData($file_id)
    {
        return Yii::app()->db->createCommand()
            ->select('p.partner_id as partner_id,pf.fname as fname')
            ->from('{{product_files}} pf')
            ->join('{{variant_qualities}} vq', 'vq.id = pf.variant_quality_id')
            ->join('{{product_variants}} pv', 'pv.id = vq.variant_id')
            ->join('{{products}} p', 'pv.product_id = p.id')
            ->join('{{typedfiles}} tf', 'tf.variant_id = pv.id')
            ->where('pf.id =:file_id', array('file_id' => $file_id))
            ->queryAll();
    }
/*
    public static function getPartnerFileLinkForUser($item_id, $preset)
    {
        $list = Yii::app()->db->createCommand()
            ->select('tf.id, pv.id as variant_id, pv.product_id as product_id,p.partner_id as partner_id, pf.fname as fname')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv', 'pv.id = tf.variant_id')
            ->join('{{products}} p', ' p.id = pv.product_id')
            ->leftJoin('{{variant_qualities}} vq', ' vq.variant_id = pv.id')
            ->join('{{product_files}} pf', 'pf.variant_quality_id = vq.id and pf.preset_id >= ' . $preset)
            ->where('tf.user_id =' . Yii::app()->user->id . ' AND tf.id =  ' . $item_id)->order('pf.preset_id')->limit(1)->query();
        $zFlag = Yii::app()->user->UserInZone;
        $zSql = '';
        if (!$zFlag) {
          //  $zSql = ' AND p.flag_zone = 0';
        }

        if ($res = $list->read()) {
            if ($res['fname']) {
                $partnerInfo = Yii::app()->db->createCommand()
                    ->select('prt.id, prt.title, prt.sprintf_url, p.original_id')
                    ->from('{{products}} p')
                    ->join('{{partners}} prt', 'prt.id = p.partner_id')
                    ->where('p.id = ' . $res['product_id'] . $zSql)->queryRow();
                $fn = pathinfo($res['fname'], PATHINFO_FILENAME) . '.mp4';
                switch ($res['partner_id']) {
                    default:
                        if (!empty($partnerInfo['sprintf_url'])){
                            $link = sprintf($partnerInfo['sprintf_url'], $partnerInfo['original_id'], 'low', $fn, 0);
                        } else{
                            //$link = Yii::app()->params['tushkan']['safelib_video'] . $res['fname'][0] . '/' . $res['fname'];
                            $link =Yii::app()->createAbsoluteUrl('/files/download?vid=' . 'variant_id');
                        }
                        break;
                    case 0:
                        echo json_encode(array('cmd' => "FilmData", 'error' => 1, 'error_msg' => 'unknown partner'));
                        Yii::app()->end();

                }

                return $link;
            }
        }
        return NULL;
    }
*/
}
