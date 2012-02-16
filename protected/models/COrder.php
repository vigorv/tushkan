<?php

/**
 * модель таблицы заказов
 *
 */
DEFINE('_ORDER_CART_', 0);
DEFINE('_ORDER_INIT_', 1);
DEFINE('_ORDER_CANCEL_', 2);
DEFINE('_ORDER_PAYED_', 3);
DEFINE('_ORDER_PERIOD_1_', 3600 * 24);

/*
 *  ActiveRecord for Orders
 * 
 * @method  getUserOrderById($id, $sqlCond = '')
 * @method getUserOrderByProduct($id, $sqlCond = '')
 */

class COrder extends CActiveRecord {

    /**
     *
     * @param string $className
     * @return COrder
     */
    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public function tableName() {
	return '{{orders}}';
    }

    /**
     * 
     * getUserOrderById
     * 
     * получить данные заказа пользователя со всеми позициями
     *
     * @param integer $id		- идентификатор заказа
     * @param string $sqlCond	- доп. sql-условия
     * @return mixed
     */
    public function getUserOrderById($id, $sqlCond = '') {
	$cmd = Yii::app()->db->createCommand()
		->select('o.id AS oid, o.state, p.id AS pid, p.title AS ptitle, pv.id AS pvid, oi.price_id, oi.rent_id, oi.price, oi.cnt')
		->from('{{orders}} o')
		->join('{{order_items}} oi', 'oi.order_id=o.id')
		->join('{{product_variants}} pv', 'oi.variant_id=pv.id')
		->join('{{products}} p', 'pv.product_id=p.id')
		->where('o.id = :id ' . $sqlCond . ' AND o.user_id = ' . Yii::app()->user->getId())
		->order('p.id ASC, pv.id ASC');
	$cmd->bindParam(':id', $id, PDO::PARAM_INT);
	$info = $cmd->queryAll();

	return $info;
    }

    /**
     * получить данные заказа пользователя по идентификатору продукта
     *
     * @param integer $id		- идентификатор продукта
     * @param string $sqlCond	- доп. sql-условия
     * @return mixed
     */
    public function getUserOrderByProduct($id, $sqlCond = '') {
	$cmd = Yii::app()->db->createCommand()
		->select('o.id oid, oi.price_id, oi.rent_id, oi.price, oi.id AS oiid')
		->from('{{orders}} o')
		->join('{{order_items}} oi', 'oi.variant_id = :id')
		->where('user_id = ' . Yii::app()->user->getId() . $sqlCond)
		->order('o.icnt DESC');
	$cmd->bindParam(':id', $id, PDO::PARAM_INT);
	$info = $cmd->queryRow();

	return $info;
    }

}