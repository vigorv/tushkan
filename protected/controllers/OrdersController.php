<?php
/**
 * Корзина и заказы
 *
 */

class OrdersController extends Controller
{
	public function actionIndex()
	{
		$lst = Yii::app()->db->createCommand()
			->select('*')
			->from('{{orders}}')
			->where('user_id = ' . Yii::app()->user->getId())
			->order('state ASC, created DESC')->queryAll();
		$this->render('/orders/index', array('lst' => $lst));
	}

	/**
	 * просмотр заказа пользователя
	 *
	 * @param integer $id - идентификатор заказа
	 */
	public function actionView($id)
	{
		$Order = new COrder();
		$info = $Order->getUserOrderById($id);

		//ДОБАВИТЬ ПРОВЕРКУ ВСЕХ ПОЗИЦИЙ ЗАКАЗА НА ПРЕДМЕТ ОПЛАЧЕННЫХ РАНЕЕ

		//ТАКЖЕ В СКРИПТЕ ОПЛАТЫ ЗАКАЗА УДАЛИТЬ ДУБЛИ ОПЛАЧЕННЫХ ПОЗИЦИЙ ИЗ КОРЗИНЫ (НЕОПЛАЧЕННЫХ ЗАКАЗОВ)

		$this->render('/orders/view', array('info' => $info));
	}

	/**
	 * купить единичный товар
	 * создается заказ с одним товаром отмеченным как "покупка"
	 * если заказ с таким единичным товаром уже есть, новый не создается,
	 * статус заказу устанавливается "покупка" (мог быть раньше отмечен аренда)
	 *
	 * @param integer $id - идентификатор варианта товара
	 */
	public function actionBuy($id)
	{
		$this->layout = '/layouts/ajax';
		$info = array();
		if (!empty($_POST['prid']))
		{
			$cmd = Yii::app()->db->createCommand()
				->select('id, price')
				->from('{{prices}}')
				->where('id = :id');
			$cmd->bindParam(':id', $_POST['prid'], PDO::PARAM_INT);
			$price = $cmd->queryRow();
		}

		if (!empty($price))
		{
			$Order = new COrder();
			$info = $Order->getUserOrderByProduct($id, ' AND state=' . _ORDER_CART_ . ' AND icnt=1');
			if (!empty($info))
			{
				if (empty($info['price_id']))
				{
				//ПРИНУДИТЕЛЬНО УСТАНАВЛИВАЕМ СТАТУС "ПОКУПКА" (СБРАСЫВАЕМ ПОЛЕ rent_id)
					$sql = 'UPDATE {{order_items}} SET rent_id=0, price_id=' . $price_id . ', price=' . $price['price'] . ' WHERE id=' . $info['oiid'];
					Yii::app()->db->createCommand($sql)->query();
				}
			}
			else
			{
				//СОЗДАЕМ ЗАКАЗ
				$created = date('Y-m-d H:i:s');
				$sql = '
					INSERT INTO {{orders}} (id, user_id, created, state, modified, icnt)
					VALUES (null, ' . Yii::app()->user->getId() . ', "' . $created . '",
					' . _ORDER_CART_ . ', "' . $created . '", 1)
				';
				Yii::app()->db->createCommand($sql)->query();
				$lastId = Yii::app()->db->getLastInsertID('{{orders}}');

				//ДОБАВЛЯЕМ В ЗАКАЗ ЕДИНСТВЕННЫЙ ТОВАР
				$sql = '
					INSERT INTO {{order_items}} (id, variant_id, order_id, rent_id, price_id, price, cnt)
					VALUES (null, :id, "' . $lastId . '", 0, ' . $price['id'] . ', ' . $price['price'] . ', 1)
				';
				$cmd = Yii::app()->db->createCommand($sql);
				$cmd->bindParam(':id', $id, PDO::PARAM_INT);
				$cmd->query();

				$info['oid'] = $lastId;//ДЛЯ ОТВЕТА О СОЗДАНИИ ЗАКАЗА
			}
		}
		$this->render('/orders/buy', array('info' => $info));
	}

	/**
	 * арендовать единичный товар.
	 * создается заказ с одним товаром отмеченным как "аренда"
	 * если заказ с таким единичным товаром уже есть, новый не создается,
	 * статус заказу устанавливается "аренда" (мог быть раньше отмечен покупкой)
	 *
	 * @param integer $id - идентификатор варианта товара
	 */
	public function actionRent($id)
	{
		$this->layout = '/layouts/ajax';
		$info = array();
		if (!empty($_POST['rid']))
		{
			$cmd = Yii::app()->db->createCommand()
				->select('id, period, price')
				->from('{{rents}}')
				->where('id = :id');
			$cmd->bindParam(':id', $_POST['rid'], PDO::PARAM_INT);
			$price = $cmd->queryRow();
		}

		if (!empty($price))
		{
			$Order = new COrder();
			$info = $Order->getUserOrderByProduct($id, ' AND state=' . _ORDER_CART_ . ' AND icnt=1');

			if (!empty($info))
			{
				if ($info['rent_id'] <> $price['id'])
				{
				//ПРИНУДИТЕЛЬНО УСТАНАВЛИВАЕМ СТАТУС "АРЕНДА" (СБРАСЫВАЕМ ПОЛЕ period)
					$sql = 'UPDATE {{order_items}} SET price_id=0, rent_id=' . $price['id'] . ', price=' . $price['price'] . ' WHERE id=' . $info['oiid'];
					Yii::app()->db->createCommand($sql)->query();
				}
			}
			else
			{
				//СОЗДАЕМ ЗАКАЗ
				$created = date('Y-m-d H:i:s');
				$sql = '
					INSERT INTO {{orders}} (id, user_id, created, state, modified, icnt)
					VALUES (null, ' . Yii::app()->user->getId() . ', "' . $created . '",
					' . _ORDER_CART_ . ', "' . $created . '", 1)
				';
				Yii::app()->db->createCommand($sql)->query();
				$lastId = Yii::app()->db->getLastInsertID('{{orders}}');

				//ДОБАВЛЯЕМ В ЗАКАЗ ЕДИНСТВЕННЫЙ ТОВАР
				$sql = '
					INSERT INTO {{order_items}} (id, variant_id, order_id, price_id, rent_id, price, cnt)
					VALUES (null, :id, "' . $lastId . '", 0, ' . $price['id'] . ', ' . $price['price'] . ', 1)
				';
				$cmd = Yii::app()->db->createCommand($sql);
				$cmd->bindParam(':id', $id, PDO::PARAM_INT);
				$cmd->query();

				$info['oid'] = $lastId;//ДЛЯ ОТВЕТА О СОЗДАНИИ ЗАКАЗА
			}
		}
		$this->render('/orders/rent', array('info' => $info));
	}

	/**
	 * добавить товар в корзину
	 *
	 * @param integer $id - идентификатор варианта товара (по таблице product_variants)
	 */
	public function actionTocart($id)
	{
		$this->layout = '/layouts/ajax';
		$info = array();
		if (!empty($_POST['prid']))
		{
			$cmd = Yii::app()->db->createCommand()
				->select('id, price')
				->from('{{prices}}')
				->where('id = :id');
			$cmd->bindParam(':id', $_POST['prid'], PDO::PARAM_INT);
			$priceInfo = $cmd->queryRow();
		}

		if (!empty($_POST['rid']))
		{
			$cmd = Yii::app()->db->createCommand()
				->select('id, period, price')
				->from('{{rents}}')
				->where('id = :id');
			$cmd->bindParam(':id', $_POST['rid'], PDO::PARAM_INT);
			$rPriceInfo = $cmd->queryRow();
		}

		if (!empty($priceInfo) || !empty($rPriceInfo))
		{
			$Order = new COrder();
			$cmd = Yii::app()->db->createCommand()
				->select('o.id oid')
				->from('{{orders}} o')
				->where('user_id = ' . Yii::app()->user->getId() . ' AND state=' . _ORDER_CART_ . ' AND icnt>=1')
				->order('o.icnt DESC');
			$info = $cmd->queryRow();

			if (empty($info))
			{
				//СОЗДАЕМ ЗАКАЗ
				$created = date('Y-m-d H:i:s');
				$sql = '
					INSERT INTO {{orders}} (id, user_id, created, state, modified, icnt)
					VALUES (null, ' . Yii::app()->user->getId() . ', "' . $created . '",
					' . _ORDER_CART_ . ', "' . $created . '", 0)
				';
				Yii::app()->db->createCommand($sql)->query();
				$lastId = Yii::app()->db->getLastInsertID('{{orders}}');
				$info['oid'] = $lastId;
			}

			//ПРОВЕРЯЕМ БЫЛ ЛИ УЖЕ ТОВАР В ЗАКАЗЕ
			$cmd = Yii::app()->db->createCommand()
				->select('*')
				->from('{{order_items}}')
				->where('order_id = ' . $info['oid'] . ' AND variant_id = :id');
			$cmd->bindParam(':id', $id, PDO::PARAM_STR);
			$itemInfo = $cmd->queryRow();

			if (empty($itemInfo))
			{
				//ДОБАВЛЯЕМ ТОВАР В ЗАКАЗ
				$sql = '';
				if (!empty($rPriceInfo))
				{
					$sql = '
						INSERT INTO {{order_items}} (id, variant_id, order_id, price_id, rent_id, price, cnt)
						VALUES (null, :id, "' . $info['oid'] . '", 0, ' . $rPriceInfo['id'] . ', ' . $rPriceInfo['price'] . ', 1)
					';
				}
				if (!empty($priceInfo))
				{
					$sql = '
						INSERT INTO {{order_items}} (id, variant_id, order_id, price_id, rent_id, price, cnt)
						VALUES (null, :id, "' . $info['oid'] . '", ' . $priceInfo['id'] . ', 0, ' . $priceInfo['price'] . ', 1)
					';
				}
				if (!empty($sql))
				{
					$cmd = Yii::app()->db->createCommand($sql);
					$cmd->bindParam(':id', $id, PDO::PARAM_INT);
					$cmd->query();
					$sql = 'UPDATE {{orders}} SET icnt=icnt+1 WHERE id=' . $itemInfo['order_id'];
					Yii::app()->db->createCommand($sql)->query();
				}
			}
			else
			{
				if (!empty($rPriceInfo))
				{
					$sql = 'UPDATE {{order_items}} SET price_id=0, rent_id=' . $rPriceInfo['id'] . ', price=' . $rPriceInfo['price'] . ' WHERE id=' . $itemInfo['id'];
				}
				if (!empty($priceInfo))
				{
					$sql = 'UPDATE {{order_items}} SET price_id=' . $priceInfo['id'] . ', rent_id=0, price=' . $priceInfo['price'] . ' WHERE id=' . $itemInfo['id'];
				}
				if (
					(!empty($priceInfo) && !empty($itemInfo['price_id']))
					||
					(!empty($rPriceInfo) && !empty($itemInfo['rent_id']))
				)
				{
				//ИНКРЕМЕНТИРУЕМ КОЛ-ВО ТОВАРА В КОРЗИНЕ
					$sql = 'UPDATE {{order_items}} SET cnt=cnt+1 WHERE id=' . $itemInfo['id'];
					Yii::app()->db->createCommand($sql)->query();
				}
			}
		}
		$this->render('/orders/rent', array('info' => $info));
	}
}