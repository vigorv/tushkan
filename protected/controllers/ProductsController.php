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
	 * используется в разных методах для показа результата: в облаке объект или нет?
	 *
	 * @var boolean
	 */
	protected $inCloud;

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
	    	$zFlag = Yii::app()->user->UserInZone;
	    	$zSql = '';
	    	if (!$zFlag)
	    	{
	    		$zSql = ' AND p.flag_zone = 0';
	    	}

			$cmd = Yii::app()->db->createCommand()
				->select('p.id, p.title AS ptitle, prt.id AS prtid, prt.title AS prttitle, pv.id AS pvid, ppv.value, ppv.param_id as ppvid')
				->from('{{products}} p')
				->join('{{partners}} prt', 'p.partner_id=prt.id')
				->join('{{product_variants}} pv', 'pv.product_id=p.id')
				->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id IN (' . implode(',', $paramIds) . ')')
				->where('prt.id IN (' . implode(',', array_keys($lst)) . ') AND p.active <= ' . $this->userPower . ' AND prt.active <= ' . $this->userPower . $searchCondition . $zSql)
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

		$paginationParams = array();
		if (!empty($pInfo))
		{
	    	$zFlag = Yii::app()->user->UserInZone;
	    	$zSql = '';
	    	if (!$zFlag)
	    	{
	    		$zSql = ' AND p.flag_zone = 0';
	    	}

			$cmd = Yii::app()->db->createCommand()
				->select('count(p.id)')
				->from('{{products}} p')
				->where('p.partner_id = ' . $pInfo['id'] . ' AND p.active <= ' . $this->userPower . $zSql);
			$count = $cmd->queryScalar();
			$paginationParams = Utils::preparePagination('/products/partner/id/' . $id, $count);

			if ($count)
			{
				$cmd = Yii::app()->db->createCommand()
					->select('p.id')
					->from('{{products}} p')
					->where('p.partner_id = ' . $pInfo['id'] . ' AND p.active <= ' . $this->userPower . $zSql)
					->limit($paginationParams['limit'], $paginationParams['offset']);
				$pst = $cmd->queryAll();
				if (!empty($pst))
				{
					$pst = implode(',', Utils::arrayToKeyValues($pst, 'id', 'id'));
				}
				else
					$pst = 0;

				$paramIds = $this->getShortParamsIds();
				$cmd = Yii::app()->db->createCommand()
					->select('p.id, p.title AS ptitle, pv.id AS pvid, ppv.value, ppv.param_id as ppvid')
					->from('{{products}} p')
					->join('{{product_variants}} pv', 'pv.product_id=p.id')
					->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id AND ppv.param_id IN (' . implode(',', $paramIds) . ')')
					->where('p.id IN (' . $pst . ')')
					->order('pv.id ASC');
				$pst = $cmd->queryAll();
			}
		}

		if (empty($pst) && empty($pInfo))
		{
			Yii::app()->user->setFlash('error', Yii::t('common', 'Page not found'));
			//Yii::app()->request->redirect('/universe/error');
		}

		$pstContent = $this->renderPartial('/products/list', array('pst' => $pst), true);

		$this->render(	'/products/partner', array('pInfo' => $pInfo, 'pstContent' => $pstContent,
						'warning18plus' => $this->warning18plus, 'paginationParams' => $paginationParams));
	}

	public function actionView($id = 0)
	{
		$Order = new COrder();
		$userId = intval(Yii::app()->user->getId());
		$orders = $actualRents = $typedFiles = array();
    	$zFlag = Yii::app()->user->UserInZone;
    	$zSql = '';
    	if (!$zFlag)
    	{
    		$zSql = ' AND p.flag_zone = 0';
    	}

		$cmd = Yii::app()->db->createCommand()
			->select('id, title, partner_id')
			->from('{{products}}')
			->where('id = :id AND active <= ' . $this->userPower . $zSql);
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

	public function actionGroup()
	{
		$errorResult = 'Нечего выполнять. Небходимо выбрать несколько продуктов';
		if (!empty($_POST['group_ids']) && !empty($_POST['operation']))
		{
			$errorResult = '';
			$ids = array();
			$group_ids = array_keys($_POST['group_ids']);
			foreach ($group_ids as $id)
			{
				$id = intval($id);
				if (empty($productId)) $productId = $id;//ЭТО ДЛЯ ОПЕРАЦИИ ОБЪЕДИНЕНИЯ
				$ids[$id] = $id;
			}
			switch ($_POST['operation'])
			{
				case 1://объединить
					if (count($ids) > 1)
					{
						//ВЫБИРАЕМ ВАРИАНТЫ УКАЗАННЫХ ПРОДУКТОВ
						$variantsToUnite = Yii::app()->db->createCommand()
							->select('*')
							->from('{{product_variants}}')
							->where('product_id IN (' . implode(',', $ids) . ')')
							->queryAll();
						//ИЩЕМ СРЕДИ НИХ РОДИТЕЛЬСКИТЙ ВАРИАНТ
						//НАПОМИНАНИЕ: если вариант является потомком другого, то поле childs = "", по умолчанию childs = ",,"
						if (!empty($variantsToUnite))
						{
							$parentVariant = array();
							foreach ($variantsToUnite as $vu)
							{
								if (($vu['childs'] != '') && ($vu['childs'] != ',,'))
								{
									$parentVariant = $vu;
									break;
								}
							}

							if (empty($parentVariant))
							{
								//СОЗДАЕМ РОДИТЕЛЬСКИЙ ВАРИАНТ
								$parentVariant = $variantsToUnite[0]; //РОДИТЕЛЬСКИЙ ВАРИАНТ ДЕЛАЕМ ПО ШАБЛОНУ ПЕРВОГО ВАРИАНТА
								unset($parentVariant['id']);
								$parentVariant['childs'] = ',,';
/*
echo '<pre>';
var_dump($parentVariant);
echo '</pre>';
exit;
*/
								if (!Yii::app()->db->createCommand()->insert('{{product_variants}}', $parentVariant))
								{
									$errorResult = 'Невозможно создать родительский вариант';
									break;
								}
								$parentVariant['id'] = Yii::app()->db->getLastInsertID('{{product_variants}}');
							}

							//ПЕРЕЗАКРЕПЛЯЕМ ВСЕ ВАРИАНТЫ НА ОДИН ПРОДУКТ
							$childIds = array();
							foreach ($variantsToUnite as $vu)
							{
								if ($vu['id'] == $parentVariant['id']) continue;

								if (($vu['childs'] != '') && ($vu['childs'] != ',,'))
								{
									//ЕЩЕ ОДИН РОДИТЕЛЬСКИЙ ВАРИАНТ. УДАЛЯЕМ
									$sql = 'DELETE FROM {{product_variants}} WHERE id = ' . $vu['id'];
									Yii::app()->db->createCommand($sql)->execute();
									continue;
								}

								$childIds[] = $vu['id'];
								$sql = 'UPDATE {{product_variants}} SET childs = "", product_id = ' . $productId . ' WHERE id = ' . $vu['id'];
								Yii::app()->db->createCommand($sql)->execute();
							}

							//ПРОПИСЫВАЕМ childs У РОДИТЕЛЯ
							$childs = ',' . implode(',', $childIds) . ',';
							$sql = 'UPDATE {{product_variants}} SET childs = "' . $childs . '" WHERE id = ' . $parentVariant['id'];
							Yii::app()->db->createCommand($sql)->execute();

							//УДАЛЯЕМ ЛИШНИЕ ПРОДУКТЫ
							$ids = array_keys($ids);
							unset($ids[0]);
							$sql = 'DELETE FROM {{products}} WHERE id IN (' . implode(',', $ids) . ')';
							Yii::app()->db->createCommand($sql)->execute();

							$operationResult = 'Продукты объединены';
							break;
						}
						$errorResult = 'Ошибка структуры данных. Продукты не содержат варианты.';
					}
				break;
				case 2://скрыть
				break;
				case 3://удалить
					if (empty($ids)) break;

					foreach ($ids as $id)
					{
						CProduct::deleteProduct($id);
					}
					$operationResult = 'Продукт удален';

				break;
			}
			if (!empty($operationResult))
				Yii::app()->user->setFlash('success', $operationResult);
		}

		if (empty($operationResult) && empty($errorResult))
			$errorResult = 'Операция на стадии разработки';
		if (!empty($errorResult))
			Yii::app()->user->setFlash('error', $errorResult);
		$this->redirect('/products/admin');
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

		$filterCondition = array();
		$filterInfo = Utils::getFilterInfo();
		$filterInfo['active'] = $userPower;
		$filterCondition['active'] = 'p.active <= :active';
		if (!empty($filterInfo['search']))
		{
			$filterCondition['search'] = 'p.title LIKE :title';
		}
		if (!empty($filterInfo['partner']))
		{
			$filterCondition['partner'] = 'p.partner_id = :partner';
		}

		if (!empty($filterInfo['from']))
		{
			$filterCondition['from'] = 'p.created >= :ffrom';
		}

		if (!empty($filterInfo['to']))
		{
			$filterCondition['to'] = 'p.created < :fto';
		}

		$cmd = Yii::app()->db->createCommand()
			->select('count(p.id) AS cnt')
			->from('{{products}} p');
		if (!empty($filterCondition))
		{
			$cmd->where(implode(' AND ', $filterCondition));

			$cmd->bindParam(':active', $filterInfo['active'], PDO::PARAM_INT);
			if (!empty($filterInfo['search']))
			{
				$searchValue = '%' . $filterInfo['search']['value'] . '%';
				$cmd->bindParam(':title', $searchValue, PDO::PARAM_STR);
			}
			if (!empty($filterInfo['partner']))
			{
				$cmd->bindParam(':partner', $filterInfo['partner']['value'], PDO::PARAM_INT);
			}
			if (!empty($filterInfo['from']))
			{
				$cmd->bindParam(':ffrom', $filterInfo['from']['value'], PDO::PARAM_STR);
			}
			if (!empty($filterInfo['to']))
			{
				$to = date('Y-m-d', strtotime($filterInfo['to']['value']) + 3600*24);
				$cmd->bindParam(':fto', $to, PDO::PARAM_STR);
			}
		}
		$count = $cmd->queryScalar();
/*
echo $to;
echo $cmd->getText();
exit;
//*/
		$paginationParams = Utils::preparePagination('/products/admin', $count);
		$products = array();

		if ($count)
		{
			$cmd = Yii::app()->db->createCommand()
	          ->select('p.id')
	          ->from('{{products}} p');

			if (!empty($filterCondition))
			{
				$cmd->where(implode(' AND ', $filterCondition));
			}
			$sortInfo = Utils::getSortInfo();
			if (!empty($sortInfo))
			{
				$sortCondition = array();
				foreach (array('title') as $srt)
					if (!empty($sortInfo[$srt]))
						$sortCondition[$srt] = $sortInfo[$srt]['name'] . ' ' . $sortInfo[$srt]['direction'];
			}
			if (!empty($sortCondition))
			{
				$cmd->order(implode(',', $sortCondition));
			}
			$cmd->limit($paginationParams['limit']);
			$cmd->offset($paginationParams['offset']);

			$cmd->bindParam(':active', $filterInfo['active'], PDO::PARAM_INT);
			if (!empty($filterInfo['search']))
			{
				$searchValue = '%' . $filterInfo['search']['value'] . '%';
				$cmd->bindParam(':title', $searchValue, PDO::PARAM_STR);
			}
			if (!empty($filterInfo['partner']))
			{
				$cmd->bindParam(':partner', $filterInfo['partner']['value'], PDO::PARAM_INT);
			}
			if (!empty($filterInfo['from']))
			{
				$cmd->bindParam(':ffrom', $filterInfo['from']['value'], PDO::PARAM_STR);
			}
			if (!empty($filterInfo['to']))
			{
				$cmd->bindParam(':fto', $filterInfo['to']['value'], PDO::PARAM_STR);
			}
			$pst = $cmd->queryAll();

			if (!empty($pst))
			{
				$pst = implode(',', Utils::arrayToKeyValues($pst, 'id', 'id'));
			}
			else
				$pst = 0;

			$cmd = Yii::app()->db->createCommand()
				->select('p.id, p.title')
				->from('{{products}} p')
				->where('p.id IN (' . $pst . ')')
				->group('p.id');
			if (!empty($sortCondition))
			{
				$cmd = $cmd->order(implode(',', $sortCondition));
			}
			$products = $cmd->queryAll();
		}


        $this->render('admin', array('products' => $products, 'paginationParams' => $paginationParams));
    }

    /**
     * действие редактирования/сохранения данных продукта и опциями управления структурой вариантов:
     * - группировка/разгруппировка с родительским вариантом
     * - удаление варианта
     *
     * @param integer $id - идентификатор продукта
     */
	public function actionEditproduct($id = 0)
	{
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

		$variantsTree = CProductVariant::getProductVariantsTree($id);
/*
echo '<pre>';
var_dump($variantsTree);
echo '</pre>';
exit;
//*/
        $productForm = new ProductForm();
        if (isset($_POST['ProductForm'])) {
            $productForm->attributes = $_POST['ProductForm'];

            if ($productForm->validate()) {
                //СОХРАНЕНИЕ ДАННЫХ C УЧЕТОМ ВСЕХ СВЯЗЕЙ
                $products = new CProduct();

                $attrs = $productForm->getAttributes();
                foreach ($attrs as $k => $v) {
                    $products->{$k} = $v;
                }
                if (empty($products->flag_zone))
                	$products->flag_zone = 0;
                else
                	$products->flag_zone = 1;
                $products->original_id = 0;
                if (empty($products->srt))
                	$products->srt = 0;
                $products->created = date('Y-m-d H:i:s');
                $products->modified = date('Y-m-d H:i:s');

                $products->isNewRecord = false;
                $products->id = $info['id'];
                $products->save();
                Yii::app()->user->setFlash('success', Yii::t('products', 'Product saved'));
                $this->redirect('/products/editproduct/' . $id);
            }
            else
            {
            	$attrs = $productForm->getAttributes();
            	$info = $attrs;//ДЛЯ ОТОБРАЖЕНИЯ В ФОРМЕ ИЗМЕНЕННЫХ ДАННЫХ
            }

        }

		$pLst = Utils::arrayToKeyValues(CPartners::getPartnerList(), 'id', 'title');
/*
echo '<pre>';
var_dump($pLst);
echo '</pre>';
exit;
//*/

        $this->render('/products/editproduct', array('model' => $productForm,
        	'info' => $info,
        	'pLst' => $pLst,
        	'variantsTree' => $variantsTree));
	}

	/**
	 * действие обработки операций над вариантами продукта
	 *
	 * @param integer $id
	 */
	public function actionEditajax($id = 0)
	{
		if (!empty($_POST))
		{
			if (!empty($_POST['group_ids']))
			{
				$ids = array();
				$group_ids = array_keys($_POST['group_ids']);
				foreach ($group_ids as $gid)
				{
					$gid = intval($gid);
					$ids[$gid] = $gid;
				}
				if (!empty($ids))
				{
					//ВЫБИРАЕМ ОТМЕЧЕННЫЕ
					$cmd = Yii::app()->db->createCommand()
						->select('*')
						->from('{{product_variants}}')
						->where('product_id = :id AND id IN (' . implode(',', $ids) . ')');
					$cmd->bindParam(':id', $id, PDO::PARAM_INT);
					$vst = $cmd->queryAll();
				}
			}

			switch ($_POST['action'])
			{
				case "group":
					//СГРУППИРОВАТЬ ВАРИАНТЫ В ГРУППУ (С СОЗДАНИЕМ РОДИТЕЛЬСКОГО ВАРИАНТА)
					if (!empty($vst))
					{
						$fst = array();//СЮДА ОТФИЛЬТРУЕМ ВСЕ ВАРИАНТЫ ГОДНЫЕ ДЛЯ ГРУППИРОВКИ
						$fIds = array(); //СЮДА АККУМУЛИРУЕМ ИДЕНТИФИКАТОРЫ ГОДНЫХ ВАРИАНТОВ
						foreach($vst as $v)
						{
							if (($v['childs'] == ',,') || empty($v['childs']))//ЕСЛИ НЕ ЯВЛЯЕТСЯ ПРЕДКОМ ДРУГИХ ВАРИАНТОВ
							{
								$fst[] = $v;
								$fIds[] = $v['id'];
							}
						}
						if (!empty($fIds))
						{
							//СОЗДАЕМ РОДИТЕЛЬСКИЙ ВАРИАНТ
							$pInfo = $fst[0];//ЗА ОСНОВУ БЕРЕМ ПЕРВЫЙ ВАРИАНТ ИЗ СПИСКА
							unset($pInfo['id']);
							$pInfo['childs'] = ',' . implode(',', $fIds) . ',';

							if (!Yii::app()->db->createCommand()->insert('{{product_variants}}', $pInfo))
							{
								$errorResult = 'Невозможно создать родительский вариант';
								break;
							}
							$pInfo['id'] = Yii::app()->db->getLastInsertID('{{product_variants}}');
							foreach ($fst as $f)
							{
								$sql = 'UPDATE {{product_variants}} SET childs="" WHERE id = ' . $f['id'];
								Yii::app()->db->createCommand($sql)->execute();
							}
						}
					}
				break;
				case "ungroup":
				case "toparent":
					//РАЗГРУППИРОВАТЬ (ВЫВЕСТИ ИЗ ГРУППЫ В КОРЕНЬ) (ПРЕДКА ПРИ ЭТОМ СКРЫВАЕМ (ACTIVE=_IS_ADMIN_)
					//И ПРОПИСЫВАЕМ ПУСТОЙ СПИСОК ПОТОМКОВ В ВИДЕ ,0,)
					$tree = CProductVariant::getProductVariantsTree($id);
					foreach ($vst as $v)
					{
						if (($v['childs'] != ',,') && !empty($v['childs']))//ЕСЛИ ЯВЛЯЕТСЯ ПРЕДКОМ ДРУГИХ ВАРИАНТОВ
							continue; //ПРОПУСКАЕМ

						//ИЩЕМ СТАРОГО ПРЕДКА ОТМЕЧЕННЫХ ВАРИАНТОВ
						foreach ($tree as $vk => $vv)
						{
							if (!empty($vv['childsInfo']))
							{
								foreach ($vv['childsInfo'] as $ck => $cv)
								{
									if ($cv['id'] == $v['id'])
									{
										//КОРРЕКТИРУЕМ СТРУКТУРУ ДЕРЕВА ВАРИАНТОВ
										unset($tree[$vk]['childsInfo'][$ck]);

										//ОБНОВЛЯЕМ ПРЕДКА
										if (empty($tree[$vk]['childsInfo']))
										{
											$childs = ',0,';
											$active = 'active = ' . _IS_ADMIN_ . ',';
										}
										else
										{
											$childs = Utils::pushIndexToKey('id', $tree[$vk]['childsInfo']);
											$childs = ',' . implode(',', array_keys($childs)) . ',';
											$active = '';
										}
										$sql = 'UPDATE {{product_variants}} SET ' . $active. 'childs="' . $childs . '" WHERE id = ' . $tree[$vk]['id'];
										Yii::app()->db->createCommand($sql)->execute();
										$tree[$vk]['childs'] = $childs;

										if (empty($_POST['parentId']))
										{
											//ОБНОВЛЯЕМ ПОТОМКА
											$sql = 'UPDATE {{product_variants}} SET childs=",," WHERE id = ' . $cv['id'];
											Yii::app()->db->createCommand($sql)->execute();
										}
									}
								}
							}

							//ЗАКРЕПИТЬ В ВИДЕ ПОТОМКА ЗА НОВЫМ ПРЕДКОМ
							if (!empty($_POST['parentId']))
							{
								$parentId = intval($_POST['parentId']);
								if ($vv['id'] == $parentId)
								{
									//КОРРЕКТИРУЕМ ДЕРЕВО
									$tree[$vk]['childsInfo'][$v['id']] = $v;

									//ОБНОВЛЯЕМ ПРЕДКА
									$childs = Utils::pushIndexToKey('id', $tree[$vk]['childsInfo']);
									$childs = ',' . implode(',', array_keys($childs)) . ',';
									$active = 'active = 0, ';

									$sql = 'UPDATE {{product_variants}} SET ' . $active. 'childs="' . $childs . '" WHERE id = ' . $tree[$vk]['id'];
									Yii::app()->db->createCommand($sql)->execute();
									$tree[$vk]['childs'] = $childs;

									//ОБНОВЛЯЕМ ПОТОМКА
									$sql = 'UPDATE {{product_variants}} SET childs="" WHERE id = ' . $v['id'];
									Yii::app()->db->createCommand($sql)->execute();
								}
							}
						}

//print_r($tree);
//break;
					}
				break;

				case "del":
					//УДАЛЕНИЕ ВАРИАНТА (ЕСЛИ ПРЕДОК, ТО ДОЛЖЕН БЫТЬ БЕЗ ПОТОМКОВ)
					//УДАЛЯЕМ ТОЛЬКО НЕ СВЯЗАННЫЕ С ПОЛЬЗОВАТЕЛЯМИ ВАРИАНТЫ
					$tree = CProductVariant::getProductVariantsTree($id);
					foreach ($vst as $v)
					{
//						if (($v['childs'] != ',,') && !empty($v['childs']))
						if (!empty($tree[$v['id']]['childsInfo']))
						{
							//ЭТО ВАРИАНТ-ПРЕДОК, ПРОПУСКАЕМ
							continue;
						}
						$relations = CProductVariant::getVariantRelations($v['id']);
						if (empty($relations['typedfiles']))
						{
							//ЕСЛИ НЕТ СВЯЗИ ВАРИАНТА С ПОЛЬЗОВАТЕЛЯМИ, ГРОХАЕМ ВАРИАНТ И ВСЕ СВЯЗИ
							foreach ($relations as $rk => $rv)
							{
								$sql = 'DELETE FROM {{' . $rk . '}} WHERE variant_id = ' . $v['id'];
								Yii::app()->db->createCommand($sql)->execute();
							}
							$sql = 'DELETE FROM {{product_variants}} WHERE id = ' . $v['id'];
							Yii::app()->db->createCommand($sql)->execute();
						}
						else
						{
							//СКРЫВАЕМ ВАРИАНТ ДЛЯ ПОЛЬЗОВАТЕЛЕЙ
						}
					}

				break;
			}
		}
	}

	/**
	 * действие сохранения данных модальной формы редактирования варианта
	 *
	 * @param integer $id
	 */
	public function actionEditvariant($id = 0)
	{
		$result = 0;
		if (!empty($_POST['VariantForm']))
		{
	        $variantForm = new VariantForm();
            $variantForm->attributes = $_POST['VariantForm'];

            if ($variantForm->validate()) {
                //СОХРАНЕНИЕ ДАННЫХ C УЧЕТОМ ВСЕХ СВЯЗЕЙ
                //$variants = new CProductVariant();

				$variants = CProductVariant::model()->findByPk($id);

				if (empty($variants['child']))
				{
					//ЗНАЧИТ - ЭТО ПОТОМОК, ПРОВЕРЯЕМ НАЛИЧИЕ ПРЕДКА
					$parent = Yii::app()->db->createCommand()
						->select('id')
						->from('{{product_variants}}')
						->where('childs LIKE ",' . $variants['id'] . ',"')
						->queryRow();
					if (empty($parent))
					{
						//ПРЕДКА НЕ НАШЛИ. ПРАВИМ СТРУКТУРУ
						$variants['childs'] = ',,';
					}
				}

                $attrs = $variantForm->getAttributes();
                foreach ($attrs as $k => $v) {
                    $variants->{$k} = $v;
                }

                $variants->isNewRecord = false;
                $variants->id = $id;
                if (empty($attrs['online_only']))
                	$variants->online_only = 0;
                else
                	$variants->online_only = 1;

                $variants->save();
                Yii::app()->user->setFlash('success', Yii::t('products', 'Variant saved'));
            }
            else
            {
            	$result = 1;//ВЗВОДИМ ОШИБКУ
            }

			foreach ($_POST['VariantForm']['params'] as $id => $value)
			{
				$sql = 'UPDATE {{product_param_values}} SET value=:value WHERE id=:id';
				$cmd = Yii::app()->db->createCommand($sql);
				$cmd->bindParam(':id', $id, PDO::PARAM_INT);
				$cmd->bindParam(':value', $value, PDO::PARAM_STR);
				$result += $cmd->execute();
			}
		}
		$this->render('/products/editvariant', array('result' => $result));
	}

    /**
     * действие редактирования продукта (одновременное редактирование данных продукта и всех его вариантов)
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

		/*
        $partners = Yii::app()->db->createCommand()
                ->select('id, title')
                ->from('{{partners}}')
                ->queryAll();
		$pLst = Utils::arrayToKeyValues($partners, 'id', 'title');
		*/
		$pLst = Utils::arrayToKeyValues(CPartners::getPartnerList(), 'id', 'title');

        $productForm = new ProductForm();
        if (isset($_POST['ProductForm'])) {
            $productForm->attributes = $_POST['ProductForm'];

            if ($productForm->validate()) {
                //СОХРАНЕНИЕ ДАННЫХ C УЧЕТОМ ВСЕХ СВЯЗЕЙ
                $products = new CProduct();

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
                $products = new CProduct();

                $attrs = $productForm->getAttributes();
                foreach ($attrs as $k => $v) {
                    $products->{$k} = $v;
                }
                if (empty($products->flag_zone))
                	$products->flag_zone = 0;
                else
                	$products->flag_zone = 1;
                $products->original_id = 0;
                $products->active = _IS_ADMIN_;//ДОБАВЛЕННЫЕ С АДМИНКИ СКРЫВАЕМ, ПОКА НЕ БУДЕТ СКОНВЕРТИРОВАНО
                if (empty($products->srt))
                	$products->srt = 0;
                $products->created = date('Y-m-d H:i:s');
                $products->modified = date('Y-m-d H:i:s');

                $products->save();
                Yii::app()->user->setFlash('success', Yii::t('products', 'Product saved'));
                $this->redirect('/products/editproduct/' . $products->id);
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
		$typeId = 0;
		if (!empty($_POST))
		{
			if (!empty($_POST['action']))
			{
				$subAction = $_POST['action'];
			}
			switch ($subAction)
			{
				case "contentinbox":
					//ВСЕ ДЕЛАЕМ ВО ВЬЮХЕ
				break;

				case "typeparams":
					$result['variantId'] = 0;
					if (!empty($_POST['variantId']))
					{
						$result['variantId'] = $_POST['variantId'];
					}
					if (!empty($_POST['typeId']))
						$typeId = $_POST['typeId'];
					$cmd = Yii::app()->db->createCommand()
						->select('ptp.id, ptp.title, ptp.description')
						->from('{{product_type_params}} ptp')
						->join('{{product_types_type_params}} pttp', 'pttp.param_id = ptp.id')
						->where('pttp.type_id = :id')
						->order('ptp.srt DESC');
//echo $cmd->getText();
					$cmd->bindParam(':id', $typeId, PDO::PARAM_INT);
					$result['lst'] = $cmd->queryAll();
				break;

				case "variantparams":
					$result['variantId'] = 0;
					$info = array();
					if (!empty($_POST['variantId']))
					{
						$result['variantId'] = intval($_POST['variantId']);
						$info = Yii::app()->db->createCommand()
							->select('*')
							->from('{{product_variants}}')
							->where('id = ' . $result['variantId'])
							->queryRow();
					}

			        $variantForm = new VariantForm();
			        $result['variantForm'] = $variantForm;
			        $result['info'] = $info;

					$cmd = Yii::app()->db->createCommand()
						->select('ptp.id, ptp.title, ptp.description, ppv.id AS vlid, ppv.value, vq.preset_id')
						->from('{{product_type_params}} ptp')
						->join('{{product_types_type_params}} pttp', 'pttp.param_id = ptp.id')
						->leftJoin('{{product_param_values}} ppv', 'ppv.param_id = ptp.id')
						->leftJoin('{{variant_qualities}} vq', 'vq.id = ppv.variant_quality_id')
						->where('ppv.variant_id = :vid')
						->group('ppv.id')
						->order('ptp.srt DESC');
//echo $cmd->getText();
					$cmd->bindParam(':vid', $result['variantId'], PDO::PARAM_INT);
					$result['lst'] = $cmd->queryAll();
					$sLst = Yii::app()->db->createCommand()
						->select('id, title')
						->from('{{variant_subs}}')
						->queryAll();
					$result['sLst'] = Utils::arrayToKeyValues($sLst, 'id', 'title');
				break;

				case "wizardtypeparams":
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

		if (($subAction == 'contentinbox') || ($subAction == 'variantparams') || (!empty($typeId) && ($typeId == 1)))//ПОКА ПОДДЕРЖКА ТОЛЬКО ВИДЕО
		{
	        $this->render('/products/ajax', array('subAction' => $subAction, 'result' => $result, 'typeId' => $typeId));
		}
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
		$this->inCloud = false;
		$result = 'bad original ID or bad partner ID';
		if (!empty($_GET['oid']) && !empty($_GET['pid']))
		{
			$partnerId = intval($_GET['pid']);
			$originalId = intval($_GET['oid']);
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
							->join('{{product_variants}} pv', 'p.id = pv.product_id')
							->join('{{typedfiles}} tf', 'tf.variant_id = pv.id')
							->where('p.original_id = :originalId AND tf.user_id = :userId');
						$cmd->bindParam(':originalId', $originalId, PDO::PARAM_INT);
						$cmd->bindParam(':userId', $userId, PDO::PARAM_INT);
						$variantExists = $cmd->queryRow();
					}

					if (!empty($variantExists))
					{
						$this->inCloud = true;
						$result = $variantExists['id'];
					}
					else
					{
						$result = 'ok';
						//ПРОВЕРЯЕМ НАЛИЧИЕ В ВИТРИНАХ
						$cmd = Yii::app()->db->createCommand()
							->select('p.id , pv.id AS pvid, p.title')
							->from('{{products}} p')
							->join('{{product_variants}} pv', 'p.id = pv.product_id')
							->where('p.original_id = :originalId');
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
								$this->inCloud = true;
								return $result;
							}
						}

						//ПОВЕРЯЕМ НАЛИЧИЕ В ОЧЕРЕДИ КОНВЕРТЕРА
						if ($originalVariantId)
							$variantCondition = ' AND original_variant_id=:vid';
						else
							$variantCondition = '';
						$cmd = Yii::app()->db->createCommand()
							->select('id, cmd_id, state')
							->from('{{income_queue}}')
							->where('cmd_id < 50 AND user_id = :id AND partner_id=:pid AND original_id=:oid' . $variantCondition);
						$cmd->bindParam(':id', $userId, PDO::PARAM_INT);
						$cmd->bindParam(':pid', $partnerId, PDO::PARAM_INT);
						$cmd->bindParam(':oid', $originalId, PDO::PARAM_INT);
						if ($originalVariantId)
							$cmd->bindParam(':vid', $originalVariantId, PDO::PARAM_INT);
						$queueExists = $cmd->queryRow();
						if ($queueExists)
						{
							$result = 'queue|' . $queueExists['cmd_id'] . '|' . $queueExists['state'];
						}
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

        $this->render('ajax', array('subAction' => $subAction, 'result' => $result, 'inCloud' => $this->inCloud, 'get' => $_REQUEST));
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

		if (($result == 'ok') || ($result == 'queue') || (intval($result) > 0))
		{
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

       $this->render('ajax', array('subAction' => 'addtocloud', 'result' => $result, 'inCloud' => $this->inCloud,
       		'get' => array('pid' => $partnerId, 'oid' => $originalId, 'vid' => $originalVariantId)));
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
			$partnerFlagZone = 0;
			if (!empty($cmdInfo))
			{
/**
 * ДОБАВЛЕНИЕ В ВИТРИНЫ. начало
 */
				if ($cmdInfo['partner_id'] <= 0)
				{
					//ОТ ЭТИХ ПАРТНЕРОВ ДОБАВЛЯТЬ В ВИТРИНЫ НЕ НАДО
					//ТАКОЙ КОНТЕНТ ОБРАБАТЫВАЕТСЯ ОСОБЫМ ПОРЯДКОМ (ЧЕРЕЗ ТРАНСПОРТ КОНВЕРТЕРА, ПП и т.д.)
					exit;
				}

				$info = unserialize($cmdInfo['info']);
				$onlineOnly = (!empty($info['just_online']));
				$presets = CPresets::getPresets();
				$partners = CPartners::getPartners();
				$pInfoTags = array(
					'title'				=> (empty($info['tags']['title'])) ? '' : strip_tags($info['tags']['title']),
					'title_original'	=> (empty($info['tags']['title_original'])) ? '' : strip_tags($info['tags']['title_original']),
					'description'		=> (empty($info['tags']['description'])) ? '' : strip_tags($info['tags']['description']),
					'genres'			=> (empty($info['tags']['genres'])) ? '' : $info['tags']['genres'],
					'countries'			=> (empty($info['tags']['countries'])) ? '' : $info['tags']['countries'],
					'year'				=> (empty($info['tags']['year'])) ? '' : $info['tags']['year'],
					'poster'			=> (empty($info['tags']['poster'])) ? '' : $partners[$cmdInfo['partner_id']]['url'] . $info['tags']['poster'],
				);
				//ПРОВЕРЯЕМ НАЛИЧИЕ ГОТОВОГО ОБЪЕКТА В ВИТРИНАХ И ПОЛУЧАЕМ ВСЕ ЕГО ВАРИАНТЫ

				$originalId = $cmdInfo['original_id'];
				//ЕСЛИ УКАЗАНА ГРУППИРОВКА ОБЪЕКТ ДОЛЖЕН БЫТЬ ПОМЕЩЕН В ПРОДУКТ С ЭТИМ original_id
				if (empty($info['group_id']))
				{
					$info['group_id'] = 0;
				}
				$info['group_id'] = intval($info['group_id']);
				if (!empty($info['group_id']))
					$originalId = $info['group_id'];

				$productInfo = Yii::app()->db->createCommand()
					->select('p.id, pv.id AS pvid, p.original_id, pv.original_id AS pvoriginal_id, pv.childs')
					->from('{{products}} p')
					->join('{{product_variants}} pv', 'pv.product_id = p.id')
					->where('p.partner_id = ' . $cmdInfo['partner_id'] . ' AND p.original_id = ' . $originalId)
					->queryAll();
				$childIds = array();//ЗДЕСЬ БУДЕМ АККУМУЛИРОВАТЬ ИДЕНТИФИКАТОРЫ НОВЫХ ПОДВАРИАНТОВ
				$newVariants = array();//ЗДЕСЬ БУДЕМ АККУМУЛИРОВАТЬ ДАННЫЕ НОВЫХ ВАРИАНТОВ
				$childsDefValue = ',,';
				//если вариант является потомком другого, то поле childs должно быть ="", по умолчанию = ",,"
				if (!empty($productInfo))
				{
					if ($productInfo[0]['original_id'] == $cmdInfo['original_id'])
					{
						//ПРОДУКТ ДОБАВЛЕН
						exit;
					}
					$productInfo = Utils::pushIndexToKey('pvid', $productInfo);
					//ПЕРЕБИРАЕМ ВСЕ ВАРИАНТЫ ПРОДУКТА ИЩЕМ РОДИТЕЛЬСКИЙ ВАРИАНТ ДЛЯ ОБЪЕКТА ИЗ ОЧЕРДИ

					foreach ($productInfo as $pvInfo)
					{
						$alreadyAdded =  ($pvInfo['pvoriginal_id'] == $cmdInfo['original_id']);
						if (!empty($info['group_id']) && !empty($pvInfo['childs']) && ($pvInfo['childs'] <> ',,') && ($pvInfo['original_id'] == $info['group_id']))
						{
							//НАШЛИ РОДИТЕЛЬСКИЙ ВАРИАНТ
							$parentVariant = $pvInfo;
						}
						if ($alreadyAdded) exit;
					}
					if (!empty($parentVariant))
					{
						$childsDefValue = '';//ЗНАЧЕНИЕ ПОЛЕ childs ДЛЯ ПОДВАРИАНТОВ
						$childs = explode(',', $parentVariant['childs']);
						$childIds = CProductVariant::getChildsIds($parentVariant['childs']);
						if (!empty($childIds))
						{
							$podVariants = Yii::app()->db->createCommand()
								->select('*')
								->from('{{product_variants}}')
								->where('id IN (' . implode(',', $childIds) . ')')
								->queryAll();
							$podVariants = Utils::pushIndexToKey('id', $podVariants);
						}
					}

					//ЗАПОЛНЯЕМ ИНФО О УЖЕ ДОБАВЛЕННОМ ПРОДУКТЕ
					$pInfo = array(
						'id'			=> $pvInfo['id'],
						'partner_id'	=> $cmdInfo['partner_id'],
						'original_id'	=> $originalId,
					);
				}
				//ПРИЗНАК ДАННЫЙ ОБЪЕКТ БУДЕТ СОХРАНЕН КАК ПОДВАРИАНТ РОДИТЕЛЬСКОГО ВАРИАНТА
				$parented = (!empty($info['group_id']) || (count($info['files']) > 1));
				$productInfoIndex = 0;
				if (empty($productInfo))
				{
					$partnerInfo = CPartners::model()->findByPk($cmdInfo['partner_id']);
					if (!empty($partnerInfo['flag_zone']))
					{
						$partnerFlagZone = $partnerInfo['flag_zone'];
					}
					//ПРОДУКТ ЕЩЕ НЕ СОЗДАВАЛИ (БЕЗ ВАРИАНТОВ ПРОДУКТА НЕ МОЖЕТ БЫТЬ)
					$productInfo = array();//ЗДЕСЬ СОБИРАЕМ ИНФУ ПО ПРОДУКТУ С ЕГО ВАРИАНТАМИ
					//(КАК ЕСЛИ БЫ ЭТО БЫЛ РЕЗУЛЬТАТ ВЫБОРКИ С ПОЛЯМИ id, pvid, original_id, pvoriginal_id)
					//ДОБАВЛЯЕМ ПРОДУКТ
					$pInfo = array(
						'title' 			=> $pInfoTags['title'],
						'partner_id'		=> $cmdInfo['partner_id'],
						'active'			=> 0, //ВИДИМ ВСЕМ
						'srt'				=> 0,
						'original_id'		=> $originalId,
						'created'			=> date('Y-m-d H:i:s'),
						'modified'			=> date('Y-m-d H:i:s'),
						'flag_zone'			=> $partnerFlagZone,
					);
					$cmd = Yii::app()->db->createCommand()->insert('{{products}}', $pInfo);
					$pInfo['id'] = Yii::app()->db->getLastInsertID('{{products}}');

					//ОПИСАНИЕ К ПРОДУКТУ ДОБАВЛЯЕМ, (ОБНОВЛЕНИЕ ОПИСАНИЯ ПОКА НЕ РЕАЛИЗУЕМ)
					$dInfo = array(
						'product_id'	=> $pInfo['id'],
						'description'	=> $pInfoTags['description'],
					);
					$cmd = Yii::app()->db->createCommand()->insert('{{product_descriptions}}', $dInfo);

					if ($parented)
					{
						//ЭТО ГРУППА ОБЪЕКТОВ. СОЗДАЕМ РОДИТЕЛЬСКИЙ ВАРИАНТ (С ЗАПОЛНЕНИЕМ ПОЛЯ childs)
						$parentVariant = array(
							'product_id'	=> $pInfo['id'],
							'online_only'	=> intval($onlineOnly),
							//'type_id'		=> $partners[$cmdInfo['partner_id']]['type'],//ТИП КОНТЕНТА см. dm_product_types
							'type_id'		=> 1,//ПОКА РАБОТАЕМ ТОЛЬКО С ВИДЕО
							'active'		=> 0,
							'title'			=> $pInfo['title'],
							'description'	=> '',//ОПИСАНИЕ ВСЕ РАВНО БУДЕТ ВСТАВЛЕНО В {{variant_param_values}}
							'original_id'	=> $info['group_id'],
							'childs'		=> $childsDefValue, //ИДЕНТИФИКАТОРЫ ВАРИАНТОВ ПОТОМКОВ
							'sub_id'		=> 1 //СУБТИП ПО УМОЛЧАНИЮ "Фильм"
						);
						$cmd = Yii::app()->db->createCommand()->insert('{{product_variants}}', $parentVariant);
						$parentVariant['pvid'] = Yii::app()->db->getLastInsertID('{{product_variants}}');

						$childsDefValue = '';//ПОСЛЕДУЮЩИЕ ВАРИАНТЫ ДОБАВЛЯЕМ КАК ПОТОМКОВ

						$productInfo[$productInfoIndex++] = array(
							'id'			=> $pInfo['id'],
							'pvid'			=> $parentVariant['pvid'],
							'original_id'	=> $pInfo['original_id'],
							'pvoriginal_id'	=> $parentVariant['original_id'],
						);
					}
				}

				/*
				НАПОМИНАНИЕ СТРУКТУРЫ ПОЛЯ info
				$info['files'][n]		- оригинальное имя обрабатываемого файла
				$info['ovids'][n]		- оригинальные variant_id. если = 0 продукт невозможно будет обновить частично
											(все файлы продукта должны будут обновляться вместе)
				$info['newfiles'][n]	- содержит новое имя (уже пережатого) файла
				$info['filepresets'][n]	- массив. содержит список пресетов с которыми данный файл был пережат
											для каждого пресета в папке фильма заводится подпапка

					* - во всех массивах должно быть одинаковое кол-во элементов
				*/

				if (!empty($info['newfiles']))
				{
					//ДОБАВЛЯЕМ ВАРИАНТЫ: ОДИН ВАРИАНТ -> ОДНА СЕРИЯ -> СОДЕРЖИТ НЕСКОЛЬКО КАЧЕСТВ
					for ($nfj = 0; $nfj < count($info['newfiles']); $nfj++)
					{
						if (empty($info['ovids'][$nfj]))
							$info['ovids'][$nfj] = 0;
						$vInfo = array(
							'product_id'	=> $pInfo['id'],
							'online_only'	=> intval($onlineOnly),
							//'type_id'		=> $partners[$cmdInfo['partner_id']]['type'],//ТИП КОНТЕНТА см. dm_product_types
							'type_id'		=> 1,//ПОКА РАБОТАЕМ ТОЛЬКО С ВИДЕО
							'active'		=> 0,
							'title'			=> $pInfoTags['title'],
							'description'	=> '',//ОПИСАНИЕ ВСЕ РАВНО БУДЕТ ВСТАВЛЕНО В {{variant_param_values}}
							'original_id'	=> $info['ovids'][$nfj],
							'childs'		=> $childsDefValue, //ИДЕНТИФИКАТОРЫ ВАРИАНТОВ ПОТОМКОВ
							'sub_id'		=> 1 //СУБТИП ПО УМОЛЧАНИЮ "Фильм"
						);
						if ($parented)
						{
							//ЕСЛИ ЭТОТ ФАЙЛ ИЗ ГРУППЫ, ТО ДАННЫЙ ПОДВАРИАНТ БУДЕТ ХРАНИТЬ ОРИГ ИД = ИД ФАЙЛА В БД ПАРТНЕРА
							$vInfo['original_id'] = $cmdInfo['original_id'];
						}

						$cmd = Yii::app()->db->createCommand()->insert('{{product_variants}}', $vInfo);
						$vInfo['id'] = Yii::app()->db->getLastInsertID('{{product_variants}}');
						$newVariants[$info['ovids'][$nfj]] = $vInfo;
						$childIds[$vInfo['id']] = $vInfo['id'];

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
							'variant_quality_id'	=> 0,//ПАРАМЕТР ИМЕЕТ ОДНО ЗНАЧЕНИЕ ДЛЯ ВСЕХ КАЧЕСТВ ВАРИАНТА
						);
						$cmd = Yii::app()->db->createCommand()->insert('{{product_param_values}}', $paramInfo);

						$paramInfo = array(
							'param_id'	=> 10,//10 - poster
							'value'		=> $pInfoTags['poster'],
							'variant_id'=> $vInfo['id'],
							'variant_quality_id'	=> 0,//ПАРАМЕТР ИМЕЕТ ОДНО ЗНАЧЕНИЕ ДЛЯ ВСЕХ КАЧЕСТВ ВАРИАНТА
						);
						$cmd = Yii::app()->db->createCommand()->insert('{{product_param_values}}', $paramInfo);

						$paramInfo = array(
							'param_id'	=> 12,//12 - original name
							'value'		=> $pInfoTags['title_original'],
							'variant_id'=> $vInfo['id'],
							'variant_quality_id'	=> 0,//ПАРАМЕТР ИМЕЕТ ОДНО ЗНАЧЕНИЕ ДЛЯ ВСЕХ КАЧЕСТВ ВАРИАНТА
						);
						$cmd = Yii::app()->db->createCommand()->insert('{{product_param_values}}', $paramInfo);

						$paramInfo = array(
							'param_id'	=> 19,//19 - description
							'value'		=> mb_substr($pInfoTags['description'], 0, 250, 'UTF-8'),
							'variant_id'=> $vInfo['id'],
							'variant_quality_id'	=> 0,//ПАРАМЕТР ИМЕЕТ ОДНО ЗНАЧЕНИЕ ДЛЯ ВСЕХ КАЧЕСТВ ВАРИАНТА
						);
						$cmd = Yii::app()->db->createCommand()->insert('{{product_param_values}}', $paramInfo);

						$paramInfo = array(
							'param_id'	=> 13,//13 - year
							'value'		=> $pInfoTags['year'],
							'variant_id'=> $vInfo['id'],
							'variant_quality_id'	=> 0,//ПАРАМЕТР ИМЕЕТ ОДНО ЗНАЧЕНИЕ ДЛЯ ВСЕХ КАЧЕСТВ ВАРИАНТА
						);
						$cmd = Yii::app()->db->createCommand()->insert('{{product_param_values}}', $paramInfo);

						$paramInfo = array(
							'param_id'	=> 14,//14 - countries
							'value'		=> $pInfoTags['countries'],
							'variant_id'=> $vInfo['id'],
							'variant_quality_id'	=> 0,//ПАРАМЕТР ИМЕЕТ ОДНО ЗНАЧЕНИЕ ДЛЯ ВСЕХ КАЧЕСТВ ВАРИАНТА
						);
						$cmd = Yii::app()->db->createCommand()->insert('{{product_param_values}}', $paramInfo);
					}

					if (!empty($parentVariant))
					{
						//ЗАПОЛНЯЕМ ИДЕНТИФИКАТОРЫ ПОТОМКОВ В ЗАПИСИ ПРЕДКА
						$childs = ',' . implode(',', $childIds) . ',';
						$sql = 'UPDATE {{product_variants}} SET childs = "' . $childs . '" WHERE id = ' . $parentVariant['pvid'];
						Yii::app()->db->createCommand($sql)->execute();
					}
				}
/**
 * ДОБАВЛЕНИЕ В ВИТРИНЫ. конец
 */





/**
 * ДОБАВЛЕНИЕ В ПП. начало
 */
				if (!empty($productInfo))
				{
					$oldIds = array();//СОБЕРЕМ ИДЕНТИФИКАТОРЫ ПРОДУКТОВ
					$oldVariantIds = array();//СОБЕРЕМ ИДЕНТИФИКАТОРЫ ВАРИАНТОВ ПРОДУКТОВ
					foreach ($productInfo as $pvInfo)
					{
						$oldIds[$pvInfo['original_id']] = $pvInfo['original_id'];
						if (!empty($pvInfo['pvoriginal_id']))
						{
							$oldVariantIds[$pvInfo['pvoriginal_id']] = $pvInfo['pvoriginal_id'];
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

					//ИЩЕМ ВСЕХ ПОЛЬЗОВАТЕЛЕЙ, СДЕЛАВШИХ ЗАЯВКУ НА ПРОДУКТ ИЛИ ВАРИАНТЫ ПРОДУКТА
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
											'title'			=> $pInfoTags['title'],
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
											'title'			=> $pInfoTags['title'],
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
		exit;
	}
}