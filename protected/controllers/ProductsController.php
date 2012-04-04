<?php
/**
 * продукты и витрины
 *
 */
class ProductsController extends Controller
{
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
		$lst = Yii::app()->db->createCommand()
			->select('*')
			->from('{{partners}}')
			->where('active <= ' . $this->userPower)
			->queryAll();

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
			->where('p.active <= ' . $this->userPower . ' AND prt.active <= ' . $this->userPower . $searchCondition)
			->order('pv.id ASC');
		if (!empty($searchCondition))
		{
			$cmd->bindParam(':search', $search, PDO::PARAM_STR);
		}
		$pst = $cmd->queryAll();

		$pstContent = $this->renderPartial('/products/list', array('pst' => $pst), true);

		$this->render('/products/index', array('lst' => $lst, 'pstContent' => $pstContent));
	}

	/**
	 * вывод товаров витрины партнера
	 *
	 * @param integer $id
	 */
	public function actionPartner($id = 0)
	{
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
			Yii::app()->request->redirect('/universe/error');
		}

		$pstContent = $this->renderPartial('/products/list', array('pst' => $pst), true);

		$this->render('/products/partner', array('pInfo' => $pInfo, 'pstContent' => $pstContent));
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
				->where('pv.product_id = ' . $productInfo['id'] . ' AND pv.active <= ' . $this->userPower . ' AND ptp.active <= ' . $this->userPower)
				->group('ppv.id')
				->order('pv.id ASC, ptp.srt DESC')->queryAll();

			$dsc = Yii::app()->db->createCommand()
					->select('*')
					->from('{{product_descriptions}}')
					->where('product_id = ' . $productInfo['id'])->queryRow();

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
		$this->render('/products/view', array('info' => $info, 'dsc' => $dsc, 'productInfo' => $productInfo, 'orders' => $orders,
				'actualRents' => $actualRents, 'typedFiles' => $typedFiles, 'userInfo' => $this->userInfo));
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
	 * выдать картинку-статус на основе исходных данных GET запроса
	 *
		$_GET['pid'] - id партнера
		$_GET['oid'] - id продукта
		$_GET['vid'] - id варианта продукта
	 */
	public function actionCloudimg()
	{
		header ("Content-type: image/png");
		$im = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'] . "/images/cloud.png");
		imagepng($im);
		imagedestroy($im);

		Yii::app()->end();
	}

	public function actionCloudaction()
	{
		$result = 'bad original ID';
		$subAction = 'cloudaction';
		$info = array('originalId' => 0, 'originalVariantId' => 0, 'userId' => 0);
		if (!empty($_POST['original_id']))
		{
			$info['originalId'] = $_POST['original_id'];
			if (!empty($_POST['original_variant_id']))
			{
				$info['originalVariantId'] = $_POST['original_variant_id'];
			}
			$result = 'user not registered';
			if (!empty($this->userInfo['id']))
			{
				$result = 'ok';
				$info['userId'] = $this->userInfo['id'];
			}
		}
        $this->render('ajax', array('subAction' => $subAction, 'result' => $result, $info => 'info'));
	}

	/**
	 * действие добавления в очередь конвертера задания по действию пользователя
	 *
	 * выполняется методом POST c параметрами
	 * original_id, partner_id, original_variant_id
	 *
	 */
	public function actionAddtoqueue()
	{
		$result = 'bad original ID';
		$subAction = 'addtoqueue';
		$variantExists = 0;
		if (!empty($_POST['original_id']))
		{
			$originalId = $_POST['original_id'];
			$result = 'user not registered';
			if (!empty($this->userInfo['id']))
			{
				$userId = $this->userInfo['id'];
				$hash = $_POST['hash'];
				$cmd = Yii::app()->db->createCommand()
					->select('id')
					->from('{{users}}')
					->where('id = :id');
				$cmd->bindParam(':id', $userId, PDO::PARAM_INT);
				$userExists = $cmd->queryRow();
				if ($userExists)
				{
					if (!empty($_POST['original_variant_id']))
					{
						$originalVariantId = $_POST['original_variant_id'];
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
					if (empty($originalVariantId) || !$variantExists)
					{
						//ПРОВЕРКУ ДУБЛЕЙ В ОЧЕРЕДИ ДЕЛАЕМ ЧЕРЕЗ УНИКАЛЬНЫЙ ИНДЕКС ПО ПОЛЯМ
						//original_id, partner_id, user_id, original_variant_id
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
						$cmd = Yii::app()->db->createCommand()
							->insert('{{income_queue}}', $queue);
					}
				}
			}
		}
        $this->render('ajax', array('subAction' => $subAction, 'result' => $result, 'variantExists' => $variantExists));
	}

	/**
	 * метод вызывается конвертером для добавления продукта в витрины и в ПП пользователя
	 *
	 * @param integer $id - идентификатор очереди
	 */
	public function actionAddfromqueue($id = 0)
	{
		$this->layout = '/layouts/ajax';
		if (empty($id))
		{
			$sql = 'SELECT * FROM {{income_queue}} WHERE id=:id AND state>7'; // ИЩЕМ ОБЪЕКТ В ОЧЕРЕДИ ГОТОВЫХ К ДОБАВЛЕНИЮ (_CMD_ADD_) ОБЪЕКТОВ
			$cmd = Yii::app()->db->createCommand($sql);
			$cmd->bindParam(':id', $id, PDO::PARAM_INT);
			$cmdInfo = $cmd->queryAll();
			if (!empty($cmdInfo) && !empty($cmdInfo[0]['user_id']))
			{

			}
		}
		Yii::app()->end();
	}
}