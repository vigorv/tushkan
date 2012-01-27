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
		$userId = Yii::app()->user->getId();
		if (!empty($userId))
		{
			$actualRents = Yii::app()->db->createCommand()
				->select('*')
				->from('{{actual_rents}}')
				->where('user_id = ' . $userId)
				->order('start DESC')->queryAll();
		}
		$this->render('/products/index', array('lst' => $lst));
	}

	public function actionView($id)
	{
		$Order = new COrder();
		$userId = intval(Yii::app()->user->getId());
		$cmd = Yii::app()->db->createCommand()
			->select('p.id as pid, p.title as ptitle, pv.online_only, pv.id as pvid, ar.start, ar.period, pr.id AS prid, pr.price AS pprice, r.id AS rid, r.price AS rprice')
			->from('{{products}} p')
	        ->join('{{product_variants}} pv', 'pv.product_id=p.id')
	        ->leftJoin('{{prices}} pr', 'pr.variant_id=pv.id')
	        ->leftJoin('{{rents}} r', 'r.variant_id=pv.id')
	        ->leftJoin('{{actual_rents}} ar', 'ar.variant_id=pv.id AND ar.user_id = ' . $userId)
			->where('p.id = :id AND p.active > 0')
			->order('p.id ASC, pv.id ASC');
		$cmd->bindParam(':id', $id, PDO::PARAM_INT);
		$info = $cmd->queryAll();
		$orders = array();
		if(!empty($userId))
		{
			$orders = Yii::app()->db->createCommand()
				->select('o.id AS oid, o.state, oi.id AS iid, oi.variant_id, oi.price_id, oi.rent_id, oi.price')
				->from('{{orders}} o')
		        ->join('{{order_items}} oi', 'o.id=oi.order_id')
				->where('o.user_id = ' . $userId)
				->order('o.created DESC')->queryAll();
		}
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
			$isOwned = false;
			/**
			 * проверяем возможность бесплатного просмотра
			 * у варианта должна быть цена аренды или покупки
			 */
			$priceInfo = Yii::app()->db->createCommand()
				->select('id')
				->from('{{prices}}')
				->where('variant_id = ' . $variantInfo['id'])
				->queryRow();
			$rentInfo = Yii::app()->db->createCommand()
				->select('id')
				->from('{{rents}}')
				->where('variant_id = ' . $variantInfo['id'])
				->queryRow();
			$isOwned = (empty($priceInfo) && empty($rentInfo));//ЕСЛИ НЕТ НИ ЦЕНЫ АРЕНДЫ, НИ ПОКУПКИ - ДОСТУПЕН БЕСПЛАТНО

			$userId = Yii::app()->user->getId();
			if (!$isOwned && !empty($userId))
			{
				/*
					в случае аренды стартуем аренду (поле start таблицы actual_rents)
					не забыть учесть, что товар может быть арендован многократно
					в этом случае новую аренду не стартуем до тех пора пока не истечет предыдущая аренда
				*/
				$rents = Yii::app()->db->createCommand()
					->select('*')
					->from('{{actual_rents}}')
					->where('variant_id = ' . $variantInfo['id'] . ' AND user_id = ' . $userId)
					->order('start DESC')//СНАЧАЛА ИСПОЛЬЗУЕМ СТАРТОВАВШУЮ АРЕНДУ
					->queryAll();
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

/**
 * методы админских скриптов
 */
    public function actionAdmin() {
        $this->layout = '/layouts/admin';
        $this->breadcrumbs = array(
            Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
            Yii::t('products', 'Administrate products'),
        );
		$userPower = Yii::app()->user->getState('dmUserPower');
		$cmd = Yii::app()->db->createCommand()
          ->select('count(id)')
          ->from('{{products}}')
          ->where('active <= :power');
        $cmd->bindParam(':power', $userPower, PDO::PARAM_INT);
        $count = $cmd->queryScalar();
        $pages = new CPagination($count);

        $perPage = 2;
        if (!empty($_GET['perpage']))
          $perPage = $_GET['perpage'];
        $pages->pageSize = $perPage;

		Yii::import('ext.pagefilters.CPageFilterProduct');
        $pageFilter = new CPageFilterProduct();
		$cmd = Yii::app()->db->createCommand()
          ->select('*')
          ->from('{{products}}')
          ->where('active <= :power');

        $sqlOrder = '';
        $orderFilter = $pageFilter->getFilterSort($sqlOrder);
		if (!empty($sqlOrder))
		{
        	$cmd = $cmd->order($sqlOrder);
		}

        $cmd = $cmd->limit($perPage, $pages->getCurrentPage() * $perPage);
        $cmd->bindParam(':power', $userPower, PDO::PARAM_INT);
        $products = $cmd->queryAll();

        $this->render('admin', array('products' => $products, 'pages' => $pages));
    }

    /**
     * действие инлайн редактирования
     *
     */
    public function actionEdit($id = 0) {
        $this->layout = '/layouts/admin';
        $this->breadcrumbs = array(
            Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
            Yii::t('products', 'Administrate products') => array($this->createUrl('products/admin')),
            Yii::t('common', 'Edit'),
        );

        $cmd = Yii::app()->db->createCommand()
                ->select('p.id, p.title, d.description, pp.filename, GROUP_CONCAT(DISTINCT c.title SEPARATOR ", ") AS country')
                ->from('{{products}} p')
                ->join('{{product_descriptions}} d', 'd.product_id=p.id')
                ->join('{{countries_products}} cp', 'cp.product_id=p.id')
                ->join('{{countries}} c', 'cp.country_id=c.id')
                ->leftJoin('{{product_pictures}} pp', 'pp.product_id=p.id AND pp.tp="smallposter"')
                ->where('p.id=:id')
                ->group('p.id');

        $cmd->bindParam(':id', $id, PDO::PARAM_INT);
        $product = $cmd->queryAll();
        print_r($_GET);

        $this->render('/products/edit', array('product' => $product));
    }

    /**
     * действие формы добавления
     *
     */
    public function actionForm() {
        $this->layout = '/layouts/admin';
        $this->breadcrumbs = array(
            Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
            Yii::t('products', 'Administrate products') => array($this->createUrl('product/admin')),
            Yii::t('products', 'Add product'),
        );

        $cLst = Yii::app()->db->createCommand()
                ->select('id, title')
                ->from('{{countries}}')
                ->queryAll();

        $countries = $chkCountries = array();

        $productForm = new ProductForm();
        if (isset($_POST['ProductForm'])) {
            $productForm->attributes = $_POST['ProductForm'];

            if ($productForm->validate()) {
                //СОХРАНЕНИЕ ДАННЫХ C УЧЕТОМ ВСЕХ СВЯЗЕЙ
                $products = new Cproduct();
                $attrs = $filmForm->getAttributes();
                foreach ($attrs as $k => $v) {
                    $products->{$k} = $v;
                }
                $products->created = date('Y-m-d H:i:s');
                $products->modified = date('Y-m-d H:i:s');
                $products->save();
                Yii::app()->user->setFlash('success', Yii::t('products', 'Product saved'));
                //$this->redirect('/films/admin');
            }

            if (!empty($_POST['ProductForm']['countries'])) {
                $chkCountries = $_POST['ProductForm']['countries'];
            }
            $countries = array();
            foreach ($cLst as $country) {
                $countries[$country['id']] = $country['title'];
            }
        } else {
            foreach ($cLst as $country) {
                $countries[$country['id']] = $country['title'];
            }
        }
        $this->render('/products/form', array('model' => $productForm, 'countries' => $countries, 'chkCountries' => $chkCountries));
    }
}