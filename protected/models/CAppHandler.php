<?php
/**
 * Author: snow
 * Date: 04.06.12
 */

class CAppHandler
{




    public static function getVtrList($user_id, $type_id = -1, $page = 1, $count = 10)
    {
        $offset = ($page - 1) * $count;
        if ($type_id >= 0) {
            $type_str = ' AND pv.type_id=' . $type_id;
        } else
            $type_str = '';
        return Yii::app()->db->createCommand()
            ->select('tf.title,tf.id, ppv.value as poster')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv', 'pv.id = tf.variant_id')
        //        ->join('{{product_pictures}} pp','pp.product_id = pv.product_id AND pp.tp = "poster" ')
        // Posters somewhere in the ass
            ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
            ->where('tf.user_id =' . $user_id . $type_str)
            ->limit($count, $offset)
            ->queryAll();
    }


    public static function getVtrItemA($item_id = 0, $user_id = 0)
    {
        return Yii::app()->db->createCommand()
            ->select('tf.title,tf.id,pv.product_id as product_id,p.partner_id as partner_id, ppv.value as poster, pf.fname as fname, pd.description')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv', 'pv.id = tf.variant_id')
            ->join('{{products}} p', ' p.id = pv.product_id')
            ->leftJoin('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
        //links in the ass
        // 10 - poster
            ->leftJoin('{{variant_qualities}} vq', ' vq.variant_id = pv.id')
            ->leftJoin('{{product_descriptions}} pd', 'pd.product_id = pv.product_id')
            ->join('{{product_files}} pf', 'pf.variant_quality_id = vq.id and pf.preset_id = 2')
            ->where('tf.user_id =' . $user_id . ' AND tf.id =  ' . $item_id)->limit(1)->query();
    }


    /**
     * @static
     * @param string $search
     * @param int $user_id
     * @param $type_id
     * @param int $page
     * @param int $count
     * @return array
     */
    public static function findUserProducts($search=' ', $user_id =0, $type_id =-1 ,$page = 1, $count = 10){
        $offset = ($page - 1) * $count;
        if ($type_id >= 0) {
            $type_str = ' AND pv.type_id=' . $type_id;
        } else
            $type_str = '';
        return Yii::app()->db->createCommand()
            ->select('tf.title,tf.id, ppv.value as poster')
            ->from('{{typedfiles}} tf')
            ->join('{{product_variants}} pv', 'pv.id = tf.variant_id')
        //        ->join('{{product_pictures}} pp','pp.product_id = pv.product_id AND pp.tp = "poster" ')
        // Posters somewhere in the ass
            ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id = 10')
            ->where('tf.user_id =' . $user_id . $type_str.' AND tf.title LIKE "%'.$search.'%"')
            ->limit($count, $offset)
            ->queryAll();
    }




    public static function getPartnerList()
    {
        return Yii::app()->db->createCommand()
        ->select('title,id')
        ->from('{{partners}}')
        ->where('active=1')
        ->queryAll();
    }

    public static function getProductList($paramIds, $userPower, $search = '')
    {
        $searchCondition = '';
        if (!($search == '')) {
            $searchCondition = ' AND p.title LIKE "%' . $search . '%"';
        }

        $cmd = Yii::app()->db->createCommand()
            ->select('p.id, p.title AS ptitle, prt.id AS prtid, prt.title AS prttitle, pv.id AS pvid, ppv.value, ppv.param_id as ppvid')
            ->from('{{products}} p')
            ->join('{{partners}} prt', 'p.partner_id=prt.id')
            ->join('{{product_variants}} pv', 'pv.product_id=p.id')
            ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id IN (' . implode(',', $paramIds) . ')')
            ->where('p.active <= ' . $userPower . ' AND prt.active <= ' . $userPower . $searchCondition)
            ->order('pv.id ASC')
            ->limit(100);
        return $cmd->queryAll();
    }

    public static function getUserProducts($userId,$type_id=0)
    {
        //ВЫБОРКА КОНТЕНТА ДОБАВЛЕННОГО С ВИТРИН
        $tFiles = Yii::app()->db->createCommand()
            ->select('id, variant_id, title')
            ->from('{{typedfiles}}')
            ->where('variant_id > 0 AND user_id = ' . $userId)
            ->queryAll();
        $fParams = array();
        $types_str='';
        if ($type_id)
            $types_str=' AND pv.type_id ='.$type_id;
        if (!empty($tFiles)) {
            $tfIds = array();
            foreach ($tFiles as $tf) {
                $tfIds[$tf['variant_id']] = $tf['variant_id'];
            }
            $fParams = Yii::app()->db->createCommand()
                ->select('pv.id, ptp.title, ppv.value')
                ->from('{{product_variants}} pv')
                ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id')
                ->join('{{product_type_params}} ptp', 'ptp.id=ppv.param_id')
                ->where('pv.id IN (' . implode(', ', $tfIds) . ')'.$types_str)
                ->group('ppv.id')
                ->order('pv.id ASC, ptp.srt DESC')->queryAll();
        }
        return array("tFiles" => $tFiles, "fParams" => $fParams);
    }

    public static function getProductFullInfo(){

    }

}