<?php
/**
 * продукты и витрины
 *
 */
class ProductsController extends Controller
{
	public function actionIndex()
	{
		$lst = Yii::app()->db->createCommand()
			->select('p.id as pid, p.title as ptitle, pt.title as ttitle, pv.id as vid, pt.id as tid')
			->from('{{products}} p')
	        ->join('{{product_variants}} pv', 'pv.product_id=p.id')
	        ->join('{{product_types}} pt', 'pt.id=pv.type_id')
	        ->group('p.id')
			->where('p.active > 0')
			->order('p.srt DESC, p.id ASC')->queryAll();
		$actualRents = Yii::app()->db->createCommand()
			->select('*')
			->from('{{actual_rents}}')
			->where('user_id = ' . Yii::app()->user->getId())
			->order('start DESC')->queryAll();
		$this->render('/products/index', array('lst' => $lst));
	}

	public function actionView($id)
	{
		$Order = new COrder();
		$cmd = Yii::app()->db->createCommand()
			->select('p.id as pid, p.title as ptitle, pv.online_only, pv.id as pvid, ar.start, ar.period, pr.id AS prid, pr.price AS pprice, r.id AS rid, r.price AS rprice')
			->from('{{products}} p')
	        ->join('{{product_variants}} pv', 'pv.product_id=p.id')
	        ->leftJoin('{{prices}} pr', 'pr.variant_id=pv.id')
	        ->leftJoin('{{rents}} r', 'r.variant_id=pv.id')
	        ->leftJoin('{{actual_rents}} ar', 'ar.variant_id=pv.id')
			->where('p.id = :id AND p.active > 0')
			->order('p.id ASC, pv.id ASC');
		$cmd->bindParam(':id', $id, PDO::PARAM_INT);
		$info = $cmd->queryAll();
		$orders = Yii::app()->db->createCommand()
			->select('o.id AS oid, o.state, oi.id AS iid, oi.variant_id, oi.price_id, oi.rent_id, oi.price')
			->from('{{orders}} o')
	        ->join('{{order_items}} oi', 'o.id=oi.order_id')
			->where('o.user_id = ' . Yii::app()->user->getId())
			->order('o.created DESC')->queryAll();
		$this->render('/products/view', array('info' => $info, 'orders' => $orders));
	}

	/**
	 * действие показа онлайн для варианта исполнения продукта
	 *
	 * @param integer $id - идентификатор варианта продукта
	 */
	public function actionOnline($id)
	{
		$cmd = Yii::app()->db->createCommand()
			->select('*')
			->from('{{product_variants}}')
			->where('id = :id');
		$cmd->bindParam(':id', $id, PDO::PARAM_INT);
		$variantInfo = $cmd->queryRow();
		$params = array();
		if (!empty($variantInfo))
		{
			/*
				в случае аренды стартуем аренду (поле start таблицы actual_rents)
				не забыть учесть, что товар может быть арендован многократно
				в этом случае новую аренду не стартуем до тех пора пока не истечет предыдущая аренда
			*/
			$rents = Yii::app()->db->createCommand()
				->select('*')
				->from('{{actual_rents}}')
				->where('variant_id = ' . $variantInfo['id'])
				->order('start DESC')//СНАЧАЛА ИСПОЛЬЗУЕМ СТАРТОВАВШУЮ АРЕНДУ
				->queryAll();
			$isOwned = false;
			foreach($rents as $r)
			{
				if (empty($r['period']))
				{
					//БЕЗВРЕМЕННАЯ АРЕНДА (КУПЛЕНО)
					$isOwned = true;
					break;
				}
				else
				{
					$isOwned = true;
					if (strtotime($r['start']) == 0)
					{
						$sql = 'UPDATE {{actual_rents}} SET start="' . date('Y-m-d H:i:s') . '" WHERE id=' . $r['id'];
						Yii::app()->db->createCommand($sql)->query();
						break;
					}
					else
					{
						if (strtotime($r['start']) + $r['period'] - time() <= 0)
						{
							$isOwned = false;
							//СРОК АРЕНДЫ ИСТЕК
							$sql = 'DELETE FROM {{actual_rents}} WHERE id=' . $r['id'];
							Yii::app()->db->createCommand($sql)->query();
						}
					}
				}
			}

			if($isOwned)
			{
				//ВЫБОРКА ЗНАЧЕНИЙ ПАРАМЕТРОВ ВАРИАНТА
				$params = Yii::app()->db->createCommand()
					->select('ppv.param_id, ppv.value, ptp.title')
					->from('{{product_param_values}} ppv')
			        ->join('{{product_type_params}} ptp', 'ppv.param_id=ptp.id')
					->where('ppv.variant_id = ' . $variantInfo['id'])->queryAll();
			}
		}
		$this->render('/products/online', array('params' => $params));
	}

	/**
	 * действие скачивания для варианта исполнения продукта
	 *
	 * @param integer $id - идентификатор варианта продукта
	 */
	public function actionDownload($id)
	{
	}
}