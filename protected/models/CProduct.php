<?php

/**
 * модель таблицы товаров
 *
 */
class CProduct extends CActiveRecord {

    /**
     *
     * @param string $className
     * @return CProduct
     */
    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public function behaviors() {
	return array(
	    'params' => array(
		'class' => 'ext.params_products.ParamsProductsBehavior',
	    ),
	    'description' => array(
		'class' => 'ext.product_descriptions.ProductDescriptionsBehavior',
	    ),
	);
    }

    public function tableName() {
	return '{{products}}';
    }

    /**
     *  Short info Id list
     * @return array
     */
    public static function getShortParamsIds() {
	return array(10, 12, 13, 14);
    }

    /**
     *
     * @param type $paramIds
     * @param type $userPower
     * @param string $search 
     */
    public function getProductList($paramIds, $userPower, $search='') {
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
		->order('pv.id ASC');
	return $cmd->queryAll();
    }

}