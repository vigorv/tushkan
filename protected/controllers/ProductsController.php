<?php
/**
 * продукты и витрины
 *
 */
class ProductsController extends Controller
{
	/**
	 * вывод списка витрин
	 *
	 */
	public function actionIndex()
	{
		$lst = Yii::app()->db->createCommand()
			->select('*')
			->from('{{partners}}')
			->where('active <= ' . $this->userPower)
			->queryAll();
		$this->render('/products/index', array('lst' => $lst));
	}

	/**
	 * вывод товаров витрины партнера
	 *
	 * @param integer $id
	 */
	public function actionPartner($id = 0)
	{
		//ПОСТРАНИЧНУЮ НАКЛАДЫВАЕМ НА ЭТУ ВЫБОРКУ
		$cmd = Yii::app()->db->createCommand()
			->select('p.id, p.title AS ptitle, prt.title AS prttitle')
			->from('{{products}} p')
			->join('{{partners}} prt', 'prt.id=p.partner_id')
			->where('p.partner_id = :id AND p.active <= ' . $this->userPower . ' AND prt.active <= ' . $this->userPower);
		$cmd->bindParam(':id', $id, PDO::PARAM_INT);
		$pst = $cmd->queryAll();

		$lst = array();
		if (!empty($pst))
		{
			$pIds = array();
			foreach($pst as $p)
			{
				$pIds[$p['id']] = $p['id'];
			}
		}
		else
		{
			Yii::app()->user->setFlash('error', Yii::t('common', 'Page not found'));
			Yii::app()->request->redirect('/universe/error');
		}
		$this->render('/products/partner', array('pst' => $pst));
	}

	public function actionView($id)
	{
		$Order = new COrder();
		$userId = intval(Yii::app()->user->getId());
		$orders = $actualRents = $typedFiles = array();
		$cmd = Yii::app()->db->createCommand()
			->select('id, title')
			->from('{{products}}')
			->where('id = :id AND active <= ' . $this->userPower);
		$cmd->bindParam(':id', $id, PDO::PARAM_INT);
		$productInfo = $cmd->queryRow();
		if (!empty($productInfo))
		{
			$info = Yii::app()->db->createCommand()
				->select('pv.id, pv.online_only, ptp.title, ppv.value, pr.id AS price_id, pr.price AS pprice, r.id AS rent_id, r.price AS rprice')
				->from('{{product_variants}} pv')
		        ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id')
		        ->join('{{product_type_params}} ptp', 'ptp.id=ppv.param_id')
		        ->leftJoin('{{prices}} pr', 'pr.variant_id=pv.id')
		        ->leftJoin('{{rents}} r', 'r.variant_id=pv.id')
				->where('pv.product_id = ' . $productInfo['id'])
				->group('ppv.id')
				->order('pv.id ASC, ptp.srt DESC')->queryAll();
			if (!empty($userId))
			{
				$actualRents = Yii::app()->db->createCommand()
					->select('*')
					->from('{{actual_rents}}')
					->where('user_id = ' . $userId)
					->order('start DESC')->queryAll();
				$typedFiles = Yii::app()->db->createCommand()
					->select('*')
					->from('{{typedfiles}}')
					->where('variant_id > 0 AND user_id = ' . $userId)
					->queryAll();
				$orders = Yii::app()->db->createCommand()
					->select('o.id AS oid, o.state, oi.id AS iid, oi.variant_id, oi.price_id, oi.rent_id, oi.price')
					->from('{{orders}} o')
			        ->join('{{order_items}} oi', 'o.id=oi.order_id')
					->where('o.user_id = ' . $userId)
					->order('o.state DESC, o.created DESC')->queryAll();
			}
		}
		else
		{
			Yii::app()->user->setFlash('error', Yii::t('common', 'Page not found'));
			Yii::app()->request->redirect('/universe/error');
		}
		$this->render('/products/view', array('info' => $info, 'productInfo' => $productInfo, 'orders' => $orders,
				'actualRents' => $actualRents, 'typedFiles' => $typedFiles, 'userInfo' => $this->userInfo));
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
							Yii::app()->db->createCommand($sql)->execute();
							break;
						}
						else
						{
							if (strtotime($r['start']) + Utils::parsePeriod($r['period'], $r['start']) - time() <= 0)
							{
								$isOwned = false;
								//СРОК АРЕНДЫ ИСТЕК
								$sql = 'DELETE FROM {{actual_rents}} WHERE id=' . $r['id'];
								Yii::app()->db->createCommand($sql)->execute();

								//УДАЛЯЕМ ИЗ ЛИЧНОГО ПРОСТРАНСТВА
								$sql = 'DELETE FROM {{typedfiles}} WHERE variant_id=' . $r['variant_id'] . ' AND user_id = ' . $r['user_id'];
								Yii::app()->db->createCommand($sql)->execute();
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
	public function actionDownload($id = 0)
	{
        $this->layout = '/layouts/testui';
		$this->render('download');
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
        	->select('*')
        	->from('{{products}}')
        	->where('id = :id');
        $cmd->bindParam(':id', $id, PDO::PARAM_INT);
        $info = $cmd->queryRow();

        if (empty($info))
        {
			Yii::app()->user->setFlash('error', Yii::t('common', 'Page not found'));
			$this->redirect('/universe/error');
        }

        $dscInfo = Yii::app()->db->createCommand()
        	->select('description')
        	->from('{{product_descriptions}}')
        	->where('product_id = ' . $info['id'])->queryRow();
        if (!empty($dscInfo))
        	$info['description'] = $dscInfo['description'];
        else
        	$info['description'] = '';

		$variantsInfo = Yii::app()->db->createCommand()
			->select('pv.id, pv.online_only, pv.type_id, pv.active, ptp.id AS pid, ptp.title, ppv.value')
			->from('{{product_variants}} pv')
	        ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id')
	        ->join('{{product_type_params}} ptp', 'ptp.id=ppv.param_id')
			->where('pv.product_id = ' . $info['id'])
			->group('ppv.id')
			->order('pv.id ASC, ptp.srt DESC')->queryAll();
		//ПРИВОДИМ ДАННЫЕ ВАРИАНТОВ И ИХ ПАРМЕТРОВ К СТРУКТУРЕ, ПРИХОДЯЩЕЙ С POST-ФОРМЫ
		$variants = $params = array();
		foreach ($variantsInfo as $vInfo)
		{
			$variants[$vInfo['id']]['id'] = $vInfo['id'];
			$variants[$vInfo['id']]['online_only'] = $vInfo['online_only'];
			$variants[$vInfo['id']]['type_id'] = $vInfo['type_id'];
			$variants[$vInfo['id']]['active'] = $vInfo['active'];

			$params[$vInfo['id']][$vInfo['pid']]['id'] = $vInfo['pid'];
			$params[$vInfo['id']][$vInfo['pid']]['title'] = $vInfo['title'];
			$params[$vInfo['id']][$vInfo['pid']]['value'] = $vInfo['value'];
			$params[$vInfo['id']][$vInfo['pid']]['variant_id'] = $vInfo['pid'];
		}

        $types = Yii::app()->db->createCommand()
                ->select('id, title')
                ->from('{{product_types}}')
                ->queryAll();
		$tLst = Utils::arrayToKeyValues($types, 'id', 'title');

        $partners = Yii::app()->db->createCommand()
                ->select('id, title')
                ->from('{{partners}}')
                ->queryAll();
		$pLst = Utils::arrayToKeyValues($partners, 'id', 'title');

        $productForm = new ProductForm();
        if (isset($_POST['ProductForm'])) {
            $productForm->attributes = $_POST['ProductForm'];

            if ($productForm->validate()) {
                //СОХРАНЕНИЕ ДАННЫХ C УЧЕТОМ ВСЕХ СВЯЗЕЙ
                $products = new Cproduct();

                $attrs = $productForm->getAttributes();
                foreach ($attrs as $k => $v) {
                    $products->{$k} = $v;
                }
                $products->original_id = 0;
                if (empty($products->srt))
                	$products->srt = 0;
                $products->created = date('Y-m-d H:i:s');
                $products->modified = date('Y-m-d H:i:s');

                $products->isNewRecord = false;
                $products->id = $info['id'];
                $products->save();
                Yii::app()->user->setFlash('success', Yii::t('products', 'Product saved'));
                $this->redirect('/products/edit/' . $id);
            }
            else
            {
            	$attrs = $productForm->getAttributes();
            	$info = $attrs;//ДЛЯ ОТОБРАЖЕНИЯ В ФОРМЕ ИЗМЕНЕННЫХ ДАННЫХ
            	$variants = $attrs['variants'];
            	$params = $attrs['params'];
            }

        } else {

        }
        $this->render('/products/edit', array('model' => $productForm,
        	'info' => $info,
        	'tLst' => $tLst, 'pLst' => $pLst,
        	'variants' => $variants, 'params' => $params));
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

        $types = Yii::app()->db->createCommand()
                ->select('id, title')
                ->from('{{product_types}}')
                ->queryAll();
		$tLst = Utils::arrayToKeyValues($types, 'id', 'title');


        $partners = Yii::app()->db->createCommand()
                ->select('id, title')
                ->from('{{partners}}')
                ->queryAll();
		$pLst = Utils::arrayToKeyValues($partners, 'id', 'title');

        $variants = $params = array();

        $productForm = new ProductForm();
        if (isset($_POST['ProductForm'])) {
            $productForm->attributes = $_POST['ProductForm'];

            if ($productForm->validate()) {
                //СОХРАНЕНИЕ ДАННЫХ C УЧЕТОМ ВСЕХ СВЯЗЕЙ
                $products = new Cproduct();

                $attrs = $productForm->getAttributes();
                foreach ($attrs as $k => $v) {
                    $products->{$k} = $v;
                }
                $products->original_id = 0;
                if (empty($products->srt))
                	$products->srt = 0;
                $products->created = date('Y-m-d H:i:s');
                $products->modified = date('Y-m-d H:i:s');

                $products->save();
                Yii::app()->user->setFlash('success', Yii::t('products', 'Product saved'));
                $this->redirect('/films/edit/' . $products->id);
            }
            else
            {
            	$attrs = $productForm->getAttributes();
            	$variants = $attrs['variants'];
            	$params = $attrs['params'];
            }

        } else {

        }
        $this->render('/products/form', array('model' => $productForm,
        	'tLst' => $tLst, 'pLst' => $pLst,
        	'variants' => $variants, 'params' => $params));
    }

	public function actionAjax()
	{
		$subAction = ''; $result = array();
		if (!empty($_POST))
		{
			if (!empty($_POST['action']))
			{
				$subAction = $_POST['action'];
			}
			switch ($subAction)
			{
				case "typeparams":
					$typeId = 0;
					$result['variantId'] = 0;
					if (!empty($_POST['typeId']))
						$typeId = $_POST['typeId'];
					$cmd = Yii::app()->db->createCommand()
						->select('ptp.id, ptp.title, ptp.description')
						->from('{{product_type_params}} ptp')
						->join('{{product_types_type_params}} pttp', 'pttp.param_id = ptp.id')
						->where('pttp.type_id = :id')
						->order('ptp.srt DESC');
					$cmd->bindParam(':id', $typeId, PDO::PARAM_INT);
					$result['lst'] = $cmd->queryAll();
					if (!empty($_POST['variantId']))
					{
						$result['variantId'] = $_POST['variantId'];
					}
				break;

				case "wizardtypeparams":
					$typeId = 0;
					if (!empty($_POST['typeId']))
						$typeId = $_POST['typeId'];
					$userPower = Yii::app()->user->getState('dmUserPower');
					$cmd = Yii::app()->db->createCommand()
						->select('ptp.id, ptp.title, ptp.description')
						->from('{{product_type_params}} ptp')
						->join('{{product_types_type_params}} pttp', 'pttp.param_id = ptp.id')
						->where('pttp.type_id = :id AND ptp.active <= ' . $userPower)
						->order('ptp.srt DESC');
					$cmd->bindParam(':id', $typeId, PDO::PARAM_INT);
					$result['lst'] = $cmd->queryAll();
				break;
			}
		}

        $this->render('ajax', array('subAction' => $subAction, 'result' => $result));
	}
}