<?php
/**
 * продукты и витрины
 *
 */
class ProductsController extends Controller
{
	/**
	 * HTML код страницы предупреждения ограничения просмотра по возрасту
	 *
	 * заполняется методами после прверки
	 *
	 * @var string
	 */
	protected $warning18plus;

	/**
	 * ПОЛУЧИТЬ МАССИВ ПАРАМЕТРОВ, НЕОБХОДИМЫХ ДЛЯ КРАТКОЙ ИНФЫ О ПРОДУКТЕ
	 *
	 * @return mixed
	 */
	public function getShortParamsIds()
	{
		return array(10, 12, 13, 14);
	}
	/**
	 * вывод списка витрин
	 *
	 */
	public function actionIndex()
	{
		//ПОЛУЧАЕМ СПИСОК ПАРТНЕРОВ
		$lst = Yii::app()->db->createCommand()
			->select('p.id, p.title, pt.tariff_id')
			->from('{{partners}} p')
			->leftJoin('{{partners_tariffs}} pt', 'p.id = pt.partner_id')
			->where('active <= ' . $this->userPower)
			->queryAll();

		$userTariffs = Yii::app()->user->getState('dmUserTariffs');
		//ОГРАНИЧИВАЕМ СПИСОК ПАРТНЕРОВ СОГЛАСНО ТАРИФАМ ПОЛЬЗОВАТЕЛЯ
		$filteredLst = array();
		foreach($lst as $l)
		{
			$allow = true;
			if ($l['tariff_id'])
			{
				$allow = false;
				//ЕСТЬ ОГРАНИЧЕНИЯ НА ТАРИФ У ЭТОГО ПАРТНЕРА
				if (!empty($userTariffs))
				{
					foreach ($userTariffs as $ut)
					{
						if ($ut['tariff_id'] == $l['tariff_id'])
						{
							$allow = true;
							break;
						}
					}
				}
			}
			if ($allow)
			{
				if (empty($filteredLst[$l['id']]))
					$filteredLst[$l['id']] = $l;
			}
		}
		$lst = $filteredLst;
		$pst = array();

		if (!empty($lst))
		{
			$searchCondition = '';
			if (!empty($_GET['search']))
			{
				$searchCondition = ' AND p.title LIKE :search';
				$search = '%' . $_GET['search'] . '%';
			}
			$paramIds = $this->getShortParamsIds();
			$cmd = Yii::app()->db->createCommand()
				->select('p.id, p.title AS ptitle, prt.id AS prtid, prt.title AS prttitle, pv.id AS pvid, ppv.value, ppv.param_id as ppvid')
				->from('{{products}} p')
				->join('{{partners}} prt', 'p.partner_id=prt.id')
				->join('{{product_variants}} pv', 'pv.product_id=p.id')
				->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id IN (' . implode(',', $paramIds) . ')')
				->where('prt.id IN (' . implode(',', array_keys($lst)) . ') AND p.active <= ' . $this->userPower . ' AND prt.active <= ' . $this->userPower . $searchCondition)
				->order('pv.id ASC');
			if (!empty($searchCondition))
			{
				$cmd->bindParam(':search', $search, PDO::PARAM_STR);
			}
			$pst = $cmd->queryAll();
		}
		$pstContent = $this->renderPartial('/products/list', array('pst' => $pst), true);

		$this->render('/products/index', array('lst' => $lst, 'pstContent' => $pstContent));
	}

	public function checkPartnerAllow($id = 0, $url = '')
	{
		$partnerAllowed = true;
		$cmd = Yii::app()->db->createCommand()
			->select('*')
			->from('{{partners_tariffs}}')
			->where('partner_id = :id');
		$cmd->bindParam(':id', $id, PDO::PARAM_INT);
		$partnerTariffs = $cmd->queryAll();
		$userTariffs = Yii::app()->user->getState('dmUserTariffs');

		$this->warning18plus = '';
		if (!empty($partnerTariffs))
		{
			$partnerAllowed = false;
			if (!empty($userTariffs))
			{
				foreach ($userTariffs as $ut)
				{
					foreach ($partnerTariffs as $pt)
						if (($ut['tariff_id'] == $pt['tariff_id']))
						{
							$partnerAllowed = true;
							break;
						}
					if ($partnerAllowed) break;
				}
			}

			if (!$partnerAllowed) $url ='';
			$this->warning18plus = $this->renderPartial('/products/warning18plus', array('url' => $url), true);
		}
		return $partnerAllowed;
	}

	/**
	 * вывод товаров витрины партнера
	 *
	 * @param integer $id
	 */
	public function actionPartner($id = 0)
	{
		$partnerAllowed = $this->checkPartnerAllow($id, '/products/partner/' . $id);
		if (!$partnerAllowed && !empty($this->warning18plus))
		{
			$id = 0;
		}

		$cmd = Yii::app()->db->createCommand()
			->select('*')
			->from('{{partners}}')
			->where('id = :id AND active <= ' . $this->userPower);
		$cmd->bindParam(':id', $id, PDO::PARAM_INT);
		$pInfo = $cmd->queryRow();
		$pst = array();

		if (!empty($pInfo))
		{
			$paramIds = $this->getShortParamsIds();
			$cmd = Yii::app()->db->createCommand()
				->select('p.id, p.title AS ptitle, pv.id AS pvid, ppv.value, ppv.param_id as ppvid')
				->from('{{products}} p')
				->join('{{product_variants}} pv', 'pv.product_id=p.id')
				->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id IN (' . implode(',', $paramIds) . ')')
				->where('p.partner_id = ' . $pInfo['id'] . ' AND p.active <= ' . $this->userPower)
				->order('pv.id ASC');
			$pst = $cmd->queryAll();
		}

		if (empty($pst) && empty($pInfo))
		{
			Yii::app()->user->setFlash('error', Yii::t('common', 'Page not found'));
			//Yii::app()->request->redirect('/universe/error');
		}

		$pstContent = $this->renderPartial('/products/list', array('pst' => $pst), true);

		$this->render('/products/partner', array('pInfo' => $pInfo, 'pstContent' => $pstContent, 'warning18plus' => $this->warning18plus));
	}

	public function actionView($id = 0)
	{
		$Order = new COrder();
		$userId = intval(Yii::app()->user->getId());
		$orders = $actualRents = $typedFiles = array();
		$cmd = Yii::app()->db->createCommand()
			->select('id, title, partner_id')
			->from('{{products}}')
			->where('id = :id AND active <= ' . $this->userPower);
		$cmd->bindParam(':id', $id, PDO::PARAM_INT);
		$productInfo = $cmd->queryRow();

		if (!empty($productInfo))
		{
			$partnerAllowed = $this->checkPartnerAllow($productInfo['partner_id'], '/products/view/' . $id);
			if (!$partnerAllowed && !empty($this->warning18plus))
			{
				$productInfo = array();
			}
		}

		if (!empty($productInfo))
		{
			$info = Yii::app()->db->createCommand()
				->select('pv.id, pv.online_only, ptp.title, ppv.value, pv.sub_id, vs.title AS vtitle, pr.id AS price_id, pr.price AS pprice, r.id AS rent_id, r.price AS rprice')
				->from('{{product_variants}} pv')
		        ->join('{{variant_subs}} vs', 'pv.sub_id=vs.id')
		        ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id')
		        ->join('{{product_type_params}} ptp', 'ptp.id=ppv.param_id')
		        ->leftJoin('{{prices}} pr', 'pr.variant_id=pv.id')
		        ->leftJoin('{{rents}} r', 'r.variant_id=pv.id')
				->where('pv.product_id = ' . $productInfo['id'] . ' AND pv.active <= ' . $this->userPower . ' AND ptp.active <= ' . $this->userPower)
				->group('ppv.id')
				->order('pv.id ASC, ptp.srt DESC')->queryAll();

			$dsc = Yii::app()->db->createCommand()
					->select('*')
					->from('{{product_descriptions}}')
					->where('product_id = ' . $productInfo['id'])->queryRow();

			$vIds = array();
			foreach($info as $i)
			{
				$vIds[$i['id']] = $i['id'];
			}
			$qualities = array();
			if (!empty($vIds))
				$qualities = Yii::app()->db->createCommand()
					->select('vq.id, vq.variant_id, vq.preset_id, pr.id AS price_id, pr.price AS pprice, r.price AS rprice, r.id AS rent_id')
					->from('{{variant_qualities}} vq')
			        ->leftJoin('{{prices}} pr', 'pr.variant_quality_id=vq.id')
			        ->leftJoin('{{rents}} r', 'r.variant_quality_id=vq.id')
					->where('vq.variant_id IN (' . implode(',', $vIds) . ')')
					->queryAll();

			if (!empty($userId))
			{
				$actualRents = Yii::app()->db->createCommand()
					->select('*')
					->from('{{actual_rents}}')
					->where('user_id = ' . $userId)
					->order('start DESC')->queryAll();
				$typedFiles = Yii::app()->db->createCommand()
					->select('tf.id, tf.variant_id, tf.user_id, tf.title, tf.variant_quality_id, vq.preset_id')
					->from('{{typedfiles}} tf')
			        ->leftJoin('{{variant_qualities}} vq', 'vq.id=tf.variant_quality_id')
					->where('tf.variant_id > 0 AND tf.user_id = ' . $userId)
					->queryAll();
				$orders = Yii::app()->db->createCommand()
					->select('o.id AS oid, o.state, oi.id AS iid, oi.variant_id, oi.price_id, oi.rent_id, oi.price, oi.variant_quality_id, vq.preset_id')
					->from('{{orders}} o')
			        ->join('{{order_items}} oi', 'o.id=oi.order_id')
			        ->leftJoin('{{variant_qualities}} vq', 'vq.id=oi.variant_quality_id')
					->where('o.user_id = ' . $userId)
					->order('o.state DESC, o.created DESC')->queryAll();
			}
		}
		else
		{
			//Yii::app()->user->setFlash('error', Yii::t('common', 'Page not found'));
			//Yii::app()->request->redirect('/universe/error');
			$this->render('/products/view', array('code' => '', 'message' => '', 'warning18plus' => $this->warning18plus));
			return;
		}
		$this->render('/products/view', array('info' => $info, 'dsc' => $dsc, 'productInfo' => $productInfo,
				'orders' => $orders, 'qualities' => $qualities,
				'actualRents' => $actualRents, 'typedFiles' => $typedFiles, 'userInfo' => $this->userInfo,
				'warning18plus' => $this->warning18plus)
		);
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

        $perPage = 20;
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
			->select('pv.id, pv.online_only, pv.type_id, pv.active, ptp.id AS pid, ptp.title, ppv.id AS vlid, ppv.value')
			->from('{{product_variants}} pv')
	        ->join('{{product_types_type_params}} pttp', 'pttp.type_id=pv.type_id')
	        ->join('{{product_type_params}} ptp', 'ptp.id=pttp.param_id')
			->leftJoin('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id=ptp.id')
			->where('pv.product_id = ' . $info['id'])
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
			$params[$vInfo['id']][$vInfo['pid']]['variant_id'] = $vInfo['id'];
			$params[$vInfo['id']][$vInfo['pid']]['vlid'] = $vInfo['vlid'];
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
                $this->redirect('/products/edit/' . $products->id);
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

	/**
	 * проверяем состояние очереди по параметрам запроса на добавление
	 *
	 * выполняется методом POST c параметрами
		$_GET['pid'] - id партнера
		$_GET['oid'] - id продукта
		$_GET['vid'] - id варианта продукта
	 *
	 */
	public function checkQueue()
	{
		$result = 'bad original ID or bad partner ID';
		if (!empty($_GET['oid']) && !empty($_GET['pid']))
		{
			$partnerId = $_GET['pid'];
			$originalId = $_GET['oid'];
			$result = 'user not registered';
			if (!empty($this->userInfo['id']))
			{
				$userId = $this->userInfo['id'];
				$cmd = Yii::app()->db->createCommand()
					->select('id')
					->from('{{users}}')
					->where('id = :id');
				$cmd->bindParam(':id', $userId, PDO::PARAM_INT);
				$userExists = $cmd->queryRow();
				if ($userExists)
				{
/*
					if (!empty($_GET['vid']))
					{
						$originalVariantId = $_GET['vid'];
						//ПРОВЕРЯЕМ, ЕСТЬ ЛИ УЖЕ ВАРИАНТ В ПП
						$cmd = Yii::app()->db->createCommand()
							->select('tf.id')
							->from('{{products}} p')
							->join('{{product_variants}} pv', 'p.id = pv.id')
							->join('{{typedfiles}} tf', 'tf.variant_id = pv.id')
							->where('pv.original_id = :originalId AND tf.user_id = :userId');
						$cmd->bindParam(':originalId', $originalVariantId, PDO::PARAM_INT);
						$cmd->bindParam(':userId', $userId, PDO::PARAM_INT);
						$variantExists = $cmd->queryRow();
					}
					else
*/
					{
						$originalVariantId = 0;//ПО УМОЛЧАНИЮ ДОБАВИМ ВСЕ ВАРИАНТЫ ПРОДУКТА В ПП
						//ПРОВЕРЯЕМ КОМПРЕССОРОМ ЕСТЬ ЛИ НЕДОБАВЛЕННЫЕ ВАРИАНТЫ ПРОДУКТА

						//ВЫДАЕМ ОДИН ИЗ ВАРИАНТОВ ПРОДУКТА В ПП
						$cmd = Yii::app()->db->createCommand()
							->select('tf.id')
							->from('{{products}} p')
							->join('{{product_variants}} pv', 'p.id = pv.id')
							->join('{{typedfiles}} tf', 'tf.variant_id = pv.id')
							->where('p.id = :originalId AND tf.user_id = :userId');
						$cmd->bindParam(':originalId', $originalId, PDO::PARAM_INT);
						$cmd->bindParam(':userId', $userId, PDO::PARAM_INT);
						$variantExists = $cmd->queryRow();
					}

					$result = 'ok';
					if (!empty($variantExists))
						$result = $variantExists;
					else
					{
						//ПРОВЕРЯЕМ НАЛИЧИЕ В ВИТРИНАХ
						$cmd = Yii::app()->db->createCommand()
							->select('p.id , pv.id AS pvid, p.title')
							->from('{{products}} p')
							->join('{{product_variants}} pv', 'p.id = pv.id')
							->where('p.id = :originalId');
						$cmd->bindParam(':originalId', $originalId, PDO::PARAM_INT);
						$productExists = $cmd->queryRow();
						if ($productExists)
						{
							$result = $productExists['id'];
							if (!empty($_GET['do']) && ($_GET['do'] == 'add'))
							{
								//ЕСЛИ ЕСТЬ ВИТРИНАХ, ДОБАВЛЯЕМ В ПП
								$tfInfo = array(
									'variant_id'	=> $productExists['pvid'],
									'user_id'		=> $userExists['id'],
									'title'			=> $productExists['title'],
									'collection_id'	=> 0,
								);
								$cmd = Yii::app()->db->createCommand()->insert('{{typedfiles}}', $tfInfo);
								$result = Yii::app()->db->getLastInsertID('{{typedfiles}}');
								return $result;
							}
						}

						//ПОВЕРЯЕМ НАЛИЧИЕ В ОЧЕРЕДИ КОНВЕРТЕРА
						$cmd = Yii::app()->db->createCommand()
							->select('id')
							->from('{{income_queue}}')
							->where('user_id = :id AND partner_id=:pid AND original_id=:oid AND original_variant_id=:vid');
						$cmd->bindParam(':id', $userId, PDO::PARAM_INT);
						$cmd->bindParam(':pid', $partnerId, PDO::PARAM_INT);
						$cmd->bindParam(':oid', $originalId, PDO::PARAM_INT);
						$cmd->bindParam(':vid', $originalVariantId, PDO::PARAM_INT);
						$queueExists = $cmd->queryRow();
						if ($queueExists)
							$result = 'queue';
					}
				}
			}
		}
		return $result;
	}

	/**
	 * действие добавления в очередь конвертера задания по действию пользователя
	 *
	 * выполняется методом POST c параметрами
		$_GET['pid'] - id партнера
		$_GET['oid'] - id продукта
		$_GET['vid'] - id варианта продукта
	 *
	 */
	public function actionAddtocloud()
	{
		$this->layout = '/layouts/ajax';
		$result = $this->checkQueue();
		$subAction = 'addtocloud';
		$variantExists = 0;

        $this->render('ajax', array('subAction' => $subAction, 'result' => $result, 'get' => $_REQUEST));
	}

	/**
	 * действие добавления в очередь конвертера задания по действию пользователя
	 *
	 * выполняется методом POST c параметрами
		$_GET['pid'] - id партнера
		$_GET['oid'] - id продукта
		$_GET['vid'] - id варианта продукта
	 *
	 */
	public function actionAddtoqueue()
	{
		$this->layout = '/layouts/ajax';
		$result = $this->checkQueue();
		$subAction = 'addtocloud';

		if (($result == 'ok') || ($result == 'queue') || (intval($result) > 0))
		{
			$state = $result;
			$partnerId = intval($_GET['pid']);
			$originalId = intval($_GET['oid']);
			if (!empty($_GET['vid']))
			{
				$originalVariantId = intval($_GET['vid']);
			}
		}

		if ((($result == 'ok') || empty($originalVariantId) || (intval($result) > 0)) && ($result <> 'queue'))
		{
			//ПРОВЕРКУ ДУБЛЕЙ В ОЧЕРЕДИ ДЕЛАЕМ ЧЕРЕЗ УНИКАЛЬНЫЙ ИНДЕКС ПО ПОЛЯМ
			//original_id, partner_id, user_id, original_variant_id
			$userId = $this->userInfo['id'];
			$queue = array(
				'id'			=> null,
				'product_id'	=> 0,
				'original_id'	=> $originalId,
				'task_id'		=> 0,
				'cmd_id'		=> 0,
				'info'			=> "",
				'priority'		=> 100,
				'state'			=> 0,
				'station_id'	=> 0,
				'partner_id'	=> $partnerId,
				'user_id'		=> $userId,
				'original_variant_id'	=> $originalVariantId,
			);
			$cmd = Yii::app()->db->createCommand()->insert('{{income_queue}}', $queue);
			$result = 'queue';
		}

       $this->render('ajax', array('subAction' => 'addtocloud', 'result' => $result, 'get' => array('pid' => $partnerId, 'oid' => $originalId, 'vid' => $originalVariantId)));
	}

	/**
	 * эмуляция добавления готового объекта в очередь (исключается операция конвертирования)
	 * используется для тестирования действия добавления в витрины
	 *
	 * @param integer $id
	 */
	public function actionQueuemulate($id = 0)
	{
		$this->layout = '/layouts/ajax';
		$cmd = Yii::app()->dbvxq->createCommand()
			->select('f.id, f.title, f.title_en, f.description, f.year, f.dir, fv.id AS ovid, ff.md5, ff.file_name')
			->from('{{films}} f')
			->join('{{film_variants}} fv', 'fv.film_id = f.id')
			->join('{{film_files}} ff', 'ff.film_variant_id = fv.id')
			->where('f.id = :id');
		$cmd->bindParam(':id', $id, PDO::PARAM_INT);
		$filmInfo = $cmd->queryAll();
		if (!empty($filmInfo))
		{
			$info = array(
				'product_id'	=> 0,	//ДОБАВЛЯЕМ ФАЙЛЫ ПРОСТО НА КОНВЕРТИРОВАНИЕ (В ВИТРИНЫ ДОБАВЛЕНИЯ НЕ БУДЕТ)
				'task_id'		=> 0,	//идентификатор задания в очереди заданий данного компрессора
				'cmd_id'		=> 7,	//добавление объекта в витрины
				'priority'		=> 0,
				'state'			=> 0,
				'station_id'	=> 0,
				'partner_id'	=> 1,	//videoxq.com (`mycloud`.`dm_partners`.`id`)
				'user_id'				=> 0,
				'original_variant_id'	=> 0,
				'date_start'	=> date('Y-m-d H:i:s')
			);
			$info['original_id'] = $filmInfo[0]['id'];

			$sql = 'SELECT file_name, type FROM film_pictures WHERE film_id = ' . $info['original_id'];
			$genres = $countries = $smallPosters = $bigPosters = $posters = array();
			$pictures = Yii::app()->dbvxq->createCommand($sql)->query();
			define('_SL_', '/');
			foreach ($pictures as $p)
			{
				switch ($p['type'])
				{
					case "poster":
						$dir = _SL_ . 'posters';
						$posters[] = $dir . _SL_ . basename($p['file_name']);
					break;
					case "smallposter":
						$dir = _SL_ . 'smallposters';
						$smallPosters[] = $dir . _SL_ . basename($p['file_name']);
					break;
					case "bigposter":
						$dir = _SL_ . 'bigposters';
						$bigPosters[] = $dir . _SL_ . basename($p['file_name']);
					break;
				}
			}
			if (empty($posters))
			{
				$posters = $smallPosters;
			}
			if (empty($posters))
			{
				$posters = $bigPosters;
			}
			$poster = '';
			if (!empty($posters))
			{
				foreach ($posters as $p)
				{
					$poster = $p;
					break;
				}
			}

		//ОПРЕДЕЛЯЕМ СПИСОК ЖАНРОВ
			$sql = '
				SELECT g.title FROM genres AS g
					INNER JOIN films_genres AS fg ON (fg.genre_id = g.id)
				WHERE fg.film_id = ' . $info['original_id'] . '
			';
			$gst = Yii::app()->dbvxq->createCommand($sql)->query();
			$genres = array();
			foreach ($gst as $g)
			{
				$genres[] = $g['title'];
			}
			$genre = implode(', ', $genres);

		//ОПРЕДЕЛЯЕМ СПИСОК СТРАН
			$sql = '
				SELECT c.title FROM countries AS c
					INNER JOIN countries_films AS cf ON (cf.country_id = c.id)
				WHERE cf.film_id = ' . $info['original_id'] . '
			';
			$cst = Yii::app()->dbvxq->createCommand($sql)->query();
			$countries = array();
			foreach ($cst as $c)
			{
				$countries[] = $c['title'];
			}
			$country = implode(', ', $countries);

			$inf = array();
			$inf['tags'] = array(
				"title"				=> $filmInfo[0]['title'],
				"title_original"	=> $filmInfo[0]['title_en'],
				"genres"			=> $genre,
				"countries"			=> $country,
				"description"		=> $filmInfo[0]['description'],
				"year"				=> $filmInfo[0]['year'],
				"poster"			=> "/img/catalog" . $poster,
			);
			$inf['md5s'] = array();
			$inf['files'] = array();
			$inf['ovids'] = array();
			$inf['newfiles'] = array();
			$inf["filepresets"] = array();
			foreach ($filmInfo as $f)
			{
				if (strpos($f['file_name'], '270/') !== false)//ВЕРСИЮ ДЛЯ МОБИЛЬНЫХ ПРОПУСКАЕМ
				{
					continue;
				}

				$inf['md5s'][] = $f['md5'];
				$inf['ovids'][] = $f['ovid'];

				$letter = strtolower(substr($f['dir'], 0, 1));
				if (($letter >= '0') && ($letter <= '9'))
					$letter = '0-999';
				$inf['files'][] = "/" . $letter . "/" . $f['dir'] . "/" . $f['file_name'];
				$inf['newfiles'][] = "/" . $letter . "/" . $f['dir'] . "/" . $f['file_name'];;
				$inf["filepresets"][] = array('low', 'medium');
			}

			$info['info'] = serialize($inf);

			$cmd = Yii::app()->db->createCommand()->insert('{{income_queue}}', $info);
		}
	}

	/**
	 * метод вызывается конвертером для добавления продукта в витрины и в ПП пользователей
	 *
	 * @param integer $id - идентификатор очереди
	 */
	public function actionAddfromqueue($id = 0)
	{
		$this->layout = '/layouts/ajax';
		if (!empty($id))
		{
			//ВЫБИРАЕМ ОЧЕРЕДЬ
			$sql = 'SELECT id, user_id, partner_id, original_id, original_variant_id, info FROM {{income_queue}} WHERE id=:id';
			$cmd = Yii::app()->db->createCommand($sql);
			$cmd->bindParam(':id', $id, PDO::PARAM_INT);
			$cmdInfo = $cmd->queryRow();
			if (!empty($cmdInfo))
			{
				//ПРОВЕРЯЕМ НАЛИЧИЕ ГОТОВОГО ОБЪЕКТА В ВИТРИНАХ И ПОЛУЧАЕМ ВСЕ ЕГО ВАРИАНТЫ
				$productInfo = Yii::app()->db->createCommand()
					->select('p.id, pv.id AS pvid, p.original_id, pv.original_id AS pvoriginal_id')
					->from('{{products}} p')
					->join('{{product_variants}} pv', 'pv.product_id = p.id')
					->where('p.partner_id = ' . $cmdInfo['partner_id'] . ' AND p.original_id = ' . $cmdInfo['original_id'])
					->queryAll();

				if (empty($productInfo))
				{
					$presets = CPresets::getPresets();
					$partners = CPartners::getPartners();
					$info = unserialize($cmdInfo['info']);
					if (!empty($info['tags']) && !empty($info['newfiles']))
					{
						$productInfo = array();//ЗДЕСЬ СОБИРАЕМ ИНФУ ПО ПРОДУКТУ С ЕГО ВАРИАНТАМИ
						//(КАК ЕСЛИ БЫ ЭТО БЫЛ РЕЗУЛЬТАТ ВЫБОРКИ С ПОЛЯМИ id, pvid, original_id, pvoriginal_id)
						$productInfoIndex = 0;
						//ДОБАВЛЯЕМ ПРОДУКТ
						$pInfo = array(
							'title' 			=> strip_tags($info['tags']['title']),
							'partner_id'		=> $cmdInfo['partner_id'],
							'active'			=> 0, //ВИДИМ ВСЕМ
							'srt'				=> 0,
							'original_id'		=> $cmdInfo['original_id'],
							'created'			=> date('Y-m-d H:i:s'),
							'modified'			=> date('Y-m-d H:i:s'),
						);
						$pInfoTags = array(
							'title_original'	=> strip_tags($info['tags']['title_original']),
							'description'		=> '',
							'genres'			=> $info['tags']['genres'],
							'countries'			=> $info['tags']['countries'],
							'year'				=> $info['tags']['year'],
							'poster'			=> $partners[$cmdInfo['partner_id']]['url'] . $info['tags']['poster'],
						);
						$cmd = Yii::app()->db->createCommand()->insert('{{products}}', $pInfo);
						$pInfo['id'] = Yii::app()->db->getLastInsertID('{{products}}');

						//ДОБАВЛЯЕМ ВАРИАНТЫ: ОДИН ВАРИАНТ -> ОДНА СЕРИЯ -> СОДЕРЖИТ НЕСКОЛЬКО КАЧЕСТВ
						for ($nfj = 0; $nfj < count($info['newfiles']); $nfj++)
						{
							if (empty($info['ovids'][$nfj]))
								$info['ovids'][$nfj] = 0;
							$vInfo = array(
								'product_id'	=> $pInfo['id'],
								'online_only'	=> 0,
								//'type_id'		=> $partners[$cmdInfo['partner_id']]['type'],//ТИП КОНТЕНТА см. dm_product_types
								'type_id'		=> 1,//ПОКА РАБОТАЕМ ТОЛЬКО С ВИДЕО
								'active'		=> 0,
								'title'			=> $pInfo['title'],
								'description'	=> $pInfoTags['description'],
								'original_id'	=> $info['ovids'][$nfj],
								'childs'		=> '', //ИДЕНТИФИКАТОРЫ ВАРИАНТОВ ПОТОМКОВ
								'sub_id'		=> 1 //СУБТИП ПО УМОЛЧАНИЮ "Фильм"
							);
							$cmd = Yii::app()->db->createCommand()->insert('{{product_variants}}', $vInfo);
							$vInfo['id'] = Yii::app()->db->getLastInsertID('{{product_variants}}');
							$newVariants[$info['ovids'][$nfj]] = $vInfo;

							$productInfo[$productInfoIndex++] = array(
								'id'			=> $pInfo['id'],
								'pvid'			=> $vInfo['id'],
								'original_id'	=> $pInfo['original_id'],
								'pvoriginal_id'	=> $vInfo['original_id'],
							);

							//СОХРАНЯЕМ ВСЕ КАЧЕСТВА ЭТОГО ВАРИАНТА
							foreach ($info['filepresets'][$nfj] as $fp)
							{
								$qInfo = array(
									'variant_id'	=> $vInfo['id'],
									'preset_id'		=> $presets[$fp]['id'],
								);
								$cmd = Yii::app()->db->createCommand()->insert('{{variant_qualities}}', $qInfo);
								$qInfo['id'] = Yii::app()->db->getLastInsertID('{{variant_qualities}}');

								//СОХРАНЯЕМ ВСЕ ФАЙЛЫ ДАННОГО КАЧЕСТВА
								$pathInfo = pathinfo($info['newfiles'][$nfj]);
								$presetName = $presets[$fp]['title'];

								$sz = 0;
								if (!empty($info['newfilesizes'][$nfj]))
									$sz = $info['newfilesizes'][$nfj];
								$fInfo = array(
									'size'					=> $sz,
									'md5'					=> "",
									'fname'					=> $pathInfo['dirname'] . '/' . $presetName . '/' . $pathInfo['basename'],
									'preset_id'				=> $presets[$fp]['id'],
									'variant_quality_id'	=> $qInfo['id'],
								);
								$cmd = Yii::app()->db->createCommand()->insert('{{product_files}}', $fInfo);
								$fInfo['id'] = Yii::app()->db->getLastInsertID('{{product_files}}');
							}

							//ДОБАВЛЯЕМ ПАРАМЕТРЫ ВАРИАНТА
							$paramInfo = array(
								'param_id'	=> 18,//18 - genres
								'value'		=> $pInfoTags['genres'],
								'variant_id'=> $vInfo['id'],
							);
							$cmd = Yii::app()->db->createCommand()->insert('{{product_param_values}}', $paramInfo);

							$paramInfo = array(
								'param_id'	=> 10,//10 - poster
								'value'		=> $pInfoTags['poster'],
								'variant_id'=> $vInfo['id'],
							);
							$cmd = Yii::app()->db->createCommand()->insert('{{product_param_values}}', $paramInfo);

							$paramInfo = array(
								'param_id'	=> 12,//12 - original name
								'value'		=> $pInfoTags['title_original'],
								'variant_id'=> $vInfo['id'],
							);
							$cmd = Yii::app()->db->createCommand()->insert('{{product_param_values}}', $paramInfo);

							$paramInfo = array(
								'param_id'	=> 19,//19 - description
								'value'		=> $pInfoTags['description'],
								'variant_id'=> $vInfo['id'],
							);
							$cmd = Yii::app()->db->createCommand()->insert('{{product_param_values}}', $paramInfo);

							$paramInfo = array(
								'param_id'	=> 13,//13 - year
								'value'		=> $pInfoTags['year'],
								'variant_id'=> $vInfo['id'],
							);
							$cmd = Yii::app()->db->createCommand()->insert('{{product_param_values}}', $paramInfo);

							$paramInfo = array(
								'param_id'	=> 14,//14 - countries
								'value'		=> $pInfoTags['countries'],
								'variant_id'=> $vInfo['id'],
							);
							$cmd = Yii::app()->db->createCommand()->insert('{{product_param_values}}', $paramInfo);
						}
						$dInfo = array(
							'product_id'	=> $pInfo['id'],
							'description'	=> strip_tags($info['tags']['description']),
						);
						$cmd = Yii::app()->db->createCommand()->insert('{{product_descriptions}}', $dInfo);
					}
				}

				//ДОБАВЛЕНИЕ В ПП ПОЛЬЗОВАТЕЛЕЙ
				if (!empty($productInfo))
				{
					$oldIds = array();
					$oldVariantIds = array();
					foreach ($productInfo as $pInfo)
					{
						$oldIds[$pInfo['original_id']] = $pInfo['original_id'];
						if (!empty($pInfo['pvoriginal_id']))
						{
							$oldVariantIds[$pInfo['pvoriginal_id']] = $pInfo['pvoriginal_id'];
						}
					}
					$orCondition = array();
					if (!empty($oldIds))
					{
						$orCondition[] = 'original_id IN (' . implode(',', $oldIds) . ')';
					}
					if (!empty($oldVariantIds))
					{
						$orCondition[] = 'original_variant_id IN (' . implode(',', $oldVariantIds) . ')';
					}
					$orCondition = implode(' OR ', $orCondition);

					//ПРОДУКТ ДОБАВЛЕН, ИЩЕМ ВСЕХ ПОЛЬЗОВАТЕЛЕЙ, СДЕЛАВШИХ ЗАЯВКУ НА ПРОДУКТ ИЛИ ВАРИАНТЫ ПРОДУКТА
					$users = Yii::app()->db->createCommand()
						->select('id, user_id, original_id, original_variant_id')
						->from('{{income_queue}}')
						->where('user_id > 0 AND partner_id = ' . $cmdInfo['partner_id'] . ' AND (' . $orCondition . ')')
						->queryAll();
					if (!empty($users))
					{
						$queueToDelete = array();
						foreach ($users as $u)
						{
							$queueToDelete[$u['id']] = $u['id'];
							//ПОВЕРЯЕМ ДОБАВЛЕНО В ПП ИЛИ НЕТ?
//							if (empty($u['original_variant_id']))
//ПОКА ВСЕГДА ДОБАВЛЯЕМ ВСЕ ВАРИАНТЫ ПРОДУКТА
							{
								//ЗНАЧИТ В ПП ДОЛЖНЫ БЫТЬ ДОБАВЛЕНЫ ВСЕ ВАРИАНТЫ ПРОДУКТА
								//ВЫБИРАЕМ УЖЕ ДОБАВЛЕННЫЕ В ПП ВАРИАНТЫ ПРОДУКТА
								$addedVariants = Yii::app()->db->createCommand()
									->select('tp.id, pv.id AS pvid, pv.original_id')
									->from('{{typedfiles}} tp')
									->join('{{product_variants}} pv', 'tp.variant_id=pv.id')
									->join('{{products}} p', 'pv.product_id=p.id')
									->where('tp.user_id=' . $u['user_id'] . ' AND p.original_id = ' . $u['original_id'])
									->queryAll();
								//ПРОВЕРЯЕМ ЕСТЬ ЛИ НЕ ДОБАВЛЕННЫЕ ВАРИАНТЫ (ВДРУГ ПОЯВИЛИСЬ НОВЫЕ)
								foreach ($productInfo as $pInfo)
								{
									$already = false;
									if (!empty($addedVariants))
									{
										foreach ($addedVariants as $av)
										{
											if ($av['original_id'] == $pInfo['pvoriginal_id'])
											{
												$already = true;
												break;
											}
										}
									}
									if (!$already)
									{
										$tfInfo = array(
											'variant_id'	=> $pInfo['pvid'],
											'user_id'		=> $u['user_id'],
											'title'			=> $info['tags']['title'],
											'collection_id'	=> 0,
										);
										$cmd = Yii::app()->db->createCommand()->insert('{{typedfiles}}', $tfInfo);
									}
								}
							}
/*
ПОКА НЕ РЕАЛИЗОВАНО ДОБАВЛЕНИЕ ОТДЕЛЬНЫХ ВАРИАНТОВ
							else
							{
								//ЗНАЧИТ В ПП ДОЛЖНЫ БЫТЬ ДОБАВЛЕН ДАННЫЙ ВАРИАНТ ПРОДУКТА
								$variantExists = Yii::app()->db->createCommand()
									->select('tp.id')
									->from('{{typedfiles}} tp')
									->join('{{product_variants}} pv', 'pv.id = tp.variant_id')
									->where('tp.user_id = ' . $u['user_id'] . ' AND pv.original_id = ' . $u['original_variant_id'])
									->queryRow();
								if (!$variantExists)
								{
									$vId = 0;
									foreach ($productInfo as $pInfo)
									{
										if ($pInfo['pvoriginal_id'] == $u['original_variant_id'])
										{
											$vId = $pInfo['pvid'];
											break;
										}
									}
									if (!empty($vId))
									{
										$tfInfo = array(
											'variant_id'	=> $vId,
											'user_id'		=> $u['user_id'],
											'title'			=> $info['tags']['title'],
											'collection_id'	=> 0,
										);
										$cmd = Yii::app()->db->createCommand()->insert('{{typedfiles}}', $tfInfo);
									}
								}
							}
*/
						}
						if (!empty($queueToDelete))
						{
							//ОБНОВЛЯЕМ ОБРАБОТАННЫЕ ЗАДАНИЯ
							$sql = 'UPDATE {{income_queue}} SET cmd_id=50 WHERE id IN (' . implode(',', $queueToDelete) . ')';
							Yii::app()->db->createCommand($sql)->execute();
							//УДАЛЯЕМ ОБРАБОТАННЫЕ ЗАДАНИЯ
							//$sql = 'DELETE FROM {{income_queue}} WHERE id IN (' . implode(',', $queueToDelete) . ')';
							//Yii::app()->db->createCommand($sql)->execute();
						}
					}
					//ОБНОВЛЯЕМ ТЕКУЩЕЕ ЗАДАНИЕ
					$sql = 'UPDATE {{income_queue}} SET cmd_id=50 WHERE id=' . $cmdInfo['id'];
					Yii::app()->db->createCommand($sql)->execute();
					//УДАЛЯЕМ ТЕКУЩЕЕ ЗАДАНИЕ
					//$sql = 'DELETE FROM {{income_queue}} WHERE id=' . $cmdInfo['id'];
					//Yii::app()->db->createCommand($sql)->execute();
				}
			}
		}
		Yii::app()->end();
	}
}