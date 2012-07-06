<?php

/**
 * модель таблицы товаров
 * @property int(10) $id
 * @property varchar(255) $title
 * @property int(10) $partner_id
 * @property int(10) $active
 * @property int(10) $original_id
 * @property int(11) $srt
 * @property datetime $created
 * @property datetime $modified
 */

class CProduct extends CActiveRecord
{

    /**
     * @param string $className
     * @return CProduct
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function behaviors()
    {
        return array(
            'params' => array(
                'class' => 'ext.params_products.ParamsProductsBehavior',
            ),
            'description' => array(
                'class' => 'ext.product_descriptions.ProductDescriptionsBehavior',
            ),
        );
    }

    public function tableName()
    {
        return '{{products}}';
    }

    /**
     *  Short info Id list
     * @return array
     */
    public static function getShortParamsIds()
    {
        return array(10, 12, 13, 14);
    }

    /**
     *
     * @param array $paramIds
     * @param int $userPower
     * @param string $search
     * @param int $offset
     * @param int $count
     * @return array
     */
    public function getProductList($paramIds, $userPower, $search = '', $offset = 0, $count = 10)
    {
        $searchCondition = '';
        if (!($search == '')) {
            $searchCondition = ' AND p.title LIKE "%' . $search . '%"';
        }
        $select_str ='p.id, p.title AS ptitle, prt.id AS prtid, prt.title AS prttitle, pv.id AS pvid';


        $cmd = Yii::app()->db->createCommand()
            ->from('{{products}} p')
            ->join('{{partners}} prt', 'p.partner_id=prt.id')
            ->join('{{product_variants}} pv', 'pv.product_id=p.id');

        if (in_array(10, $paramIds)){
            $cmd->leftJoin('{{product_param_values}} ppvP', 'pv.id=ppvP.variant_id AND ppvP.param_id = 10');
            $select_str.=', ppvP.value as poster ';
        }
        if (in_array(12, $paramIds)){
            $cmd->leftJoin('{{product_param_values}} ppvT', 'pv.id=ppvT.variant_id AND ppvT.param_id = 12');
            $select_str.=', ppvT.value as engTitle ';
        }
        if (in_array(13, $paramIds)){
            $cmd->leftJoin('{{product_param_values}} ppvY', 'pv.id=ppvY.variant_id AND ppvY.param_id = 13');
            $select_str.=', ppvY.value as year ';
        }
        if (in_array(14, $paramIds)){
            $cmd->leftJoin('{{product_param_values}} ppvC', 'pv.id=ppvC.variant_id AND ppvC.param_id = 14');
            $select_str.=', ppvC.value as country';
        }
        $cmd->select($select_str);
        $cmd->where('p.active <= ' . $userPower . ' AND prt.active <= ' . $userPower . $searchCondition)
            ->order('pv.id ASC')
            ->limit($count, $offset);
        return $cmd->queryAll();
    }

    /**
     * @param int $userId
     * @param int $type_id
     * @param int $offset
     * @param int $count
     * @return array
     */
    public function getUserProducts($userId, $type_id = 0, $offset = 0, $count = 8)
    {
        //ВЫБОРКА КОНТЕНТА ДОБАВЛЕННОГО С ВИТРИН
        $tFiles = Yii::app()->db->createCommand()
            ->select('id, variant_id, title')
            ->from('{{typedfiles}}')
            ->where('variant_id > 0 AND user_id = ' . $userId)
            ->limit($count, $offset)
            ->queryAll();
        $fParams = array();
        $types_str = '';
        if ($type_id)
            $types_str = ' AND pv.type_id =' . $type_id;
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
                ->where('pv.id IN (' . implode(', ', $tfIds) . ')' . $types_str)
                ->group('ppv.id')
                ->order('pv.id ASC, ptp.srt DESC')
                ->queryAll();
        }
        return array("tFiles" => $tFiles, "fParams" => $fParams);
    }
}