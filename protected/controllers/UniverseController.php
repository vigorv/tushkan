<?php

class UniverseController extends Controller {

	var $user_id;

	//public $panel;
	//public $goods
	//
	var $layout = 'concept1';

	public function beforeAction($action) {
		parent::beforeAction($action);
		$this->user_id = Yii::app()->user->id;
		if ($this->user_id)
			return true;
		else
			Yii::app()->request->redirect('/register/login');
	}

	public function accessRules() {

	}

	public function actionError() {
		$error = Yii::app()->errorHandler->error;
		if ($error) {
			if (Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				var_dump($error);
//$this->render('error', $error);
		}
	}

	public function actionIndex($section='') {
		$this->render('library');
	}

	public function actionIndexOld() {
//ВЫБОРКА КОНТЕНТА ДОБАВЛЕННОГО С ВИТРИН
		$tFiles = Yii::app()->db->createCommand()
				->select('id, variant_id, title')
				->from('{{typedfiles}}')
				->where('variant_id > 0 AND user_id = ' . $this->userInfo['id'])
				->queryAll();
		$fParams = array();
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
							->where('pv.id IN (' . implode(', ', $tfIds) . ')')
							->group('ppv.id')
							->order('pv.id ASC, ptp.srt DESC')->queryAll();
		}

		$uploadServer = CServers::model()->getServer(UPLOAD_SERVER);
		$quality = Utils::getVideoConverterQuality();

//ВЫБОРКА ТИПОВ ДЛЯ ФОРМЫ ЗАГРУЗКИ
		$userPower = Yii::app()->user->getState('dmUserPower');
		$types = Yii::app()->db->createCommand()
				->select('id, title')
				->from('{{product_types}}')
				->where('active <= ' . $userPower)
				->queryAll();
		$types = Utils::arrayToKeyValues($types, 'id', 'title');

//ВЫБОРКА ТИПИЗИРОВАННОГО КОНТЕНТА
		$tObjects = Yii::app()->db->createCommand()
				->select('uo.id, uo.title, ptp.title, uopv.value')
				->from('{{userobjects}} uo')
				->join('{{userobjects_param_values}} uopv', 'uopv.object_id=uo.id')
				->join('{{product_type_params}} ptp', 'ptp.id=uopv.param_id')
				->where('user_id = ' . $this->userInfo['id'])
				->group('uopv.id')
				->queryAll();
		$oParams = array();
		if (!empty($tObjects)) {
			/*
			  $toIds = array();
			  foreach ($tObjects as $to) {
			  $toIds[$to['id']] = $to['id'];
			  }
			  $oParams = Yii::app()->db->createCommand()
			  ->select('fv.id, fv.file_id')
			  ->from('{{filevariants}} fv')
			  ->join('{{filelocations}} fl', 'fl.id=fv.id')
			  ->where('fv.file_id IN (' . implode(', ', $toIds) . ')')
			  ->group('fpv.id')
			  ->order('fv.id ASC, ptp.srt DESC')->queryAll();
			 */
		}

//ВЫБОРКА НЕТИПИЗИРОВАННЫХ ФАЙЛОВ
		/*
		  $uFiles = Yii::app()->db->createCommand()
		  ->select('uf.id, uf.title, uf.type_id')
		  ->from('{{userfiles}} uf')
		  ->where('uf.object_id = 0 AND uf.user_id = ' . $this->userInfo['id'])
		  ->queryAll();
		 */
		$this->render('index', array('tFiles' => $tFiles, 'fParams' => $fParams,
			'uploadServer' => $uploadServer, 'quality' => $quality,
			'types' => $types, 'tObjects' => $tObjects, 'oParams' => $oParams));
	}

	/**
	 * действие сохранения информации о загруженном файле (параметры)
	 *
	 */
	public function actionPostuploadparams() {
		if (!empty($_POST['paramsForm'])) {
			if (!empty($_POST['paramsForm']['typeId'])) {
				$typeId = intval($_POST['paramsForm']['typeId']);
			}
			if (!empty($_POST['paramsForm']['fileId'])) {
				$fileId = intval($_POST['paramsForm']['fileId']);
			}
			if (!empty($_POST['paramsForm']['params'])) {
				$params = $_POST['paramsForm']['params'];
			}
			if (!empty($fileId) && !empty($params)) {
				$cmd = Yii::app()->db->createCommand()
						->select('*')
						->from('{{userfiles}}')
						->where('id = :id AND user_id = ' . $this->userInfo['id']);
				$cmd->bindParam(':id', $fileId);
				$fileInfo = $cmd->queryRow();
				if (!empty($fileInfo)) {
					//ПРИ ТИПИЗАЦИИ ЗАКРЕПЛЯЕМ ФАЙЛ ЗА ОБЪЕКТОМ
					$sql = 'INSERT INTO {{userobjects}} (id, title, user_id, type_id, active, parent_id)
						VALUES (null, :title, :user_id, :type_id, 0, 0)
					';
					$cmd = Yii::app()->db->createCommand($sql);
					$cmd->bindParam(':title', $fileInfo['title'], PDO::PARAM_STR);
					$cmd->bindParam(':user_id', $this->userInfo['id'], PDO::PARAM_INT);
					$cmd->bindParam(':type_id', $typeId, PDO::PARAM_INT);
					$cmd->execute();
					$objectId = Yii::app()->db->getLastInsertID('{{userobjects}}');

					$sql = 'UPDATE {{userfiles}} SET object_id = ' . $objectId . ' WHERE id = :id';
					$cmd = Yii::app()->db->createCommand($sql);
					$cmd->bindParam(':id', $fileInfo['id'], PDO::PARAM_INT);
					$cmd->execute();

					foreach ($params as $p) {
						if (empty($p['id']))
							continue;

						$sql = 'INSERT INTO {{userobjects_param_values}} (id, param_id, value, object_id)
							VALUES (null, :param_id, :value, ' . $objectId . ')
						';
						$cmd = Yii::app()->db->createCommand($sql);
						$cmd->bindParam(':param_id', $p['id'], PDO::PARAM_INT);
						$cmd->bindParam(':value', $p['value'], PDO::PARAM_STR);
						$cmd->execute();
					}
				}
			}
		}
	}

	/**
	 * действие формы загрузки файла
	 *
	 */
	public function actionUpload() {
//ВЫБОРКА ТИПОВ ДЛЯ ФОРМЫ ЗАГРУЗКИ
		$userPower = Yii::app()->user->getState('dmUserPower');
		$types = Yii::app()->db->createCommand()
				->select('id, title')
				->from('{{product_types}}')
				->where('active <= ' . $userPower)
				->queryAll();
		$types = Utils::arrayToKeyValues($types, 'id', 'title');
//$kpt = file_get_contents(Yii::app()->params['tushkan']['siteURL'] . '/files/KPT');

		$this->render('upload', array('types' => $types, 'user_id' => $this->userInfo['id'], /* 'kpt' => $kpt */));
	}

	public function actionAdd($step=1) {
		$step = (int) $step;
		$this->render('steps');
	}

	public function actionPanel() {
		$userInfo = CUser::model()->getUserInfo($this->user_id);
		$partners = CPartners::model()->getPartnerList();
		$this->render('status_panel', array('userInfo' => $userInfo, 'partners' => $partners));
	}



	public function actionGoodsTop($text='') {
		$search = filter_var($text, FILTER_SANITIZE_STRING);
		$lst = array();
		$pst = CProduct::model()->getProductList(CProduct::getShortParamsIds(), $this->userPower, $search);
		$this->render('/products/top', array('pst' => $pst));
	}

	public function actionProducts(){

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
		$paramIds = CProduct::getShortParamsIds();
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

		$this->render('/universe/products', array('lst' => $lst, 'pstContent' => $pstContent));
	}



	public function actionSearch($text='') {
		$search = filter_var($text, FILTER_SANITIZE_STRING);
		$lst = array();
		$pst = CProduct::model()->getProductList(CProduct::getShortParamsIds(), $this->userPower, $search);
		$pstContent = $this->renderPartial('/products/top', array('pst' => $pst), true);

		$obj = CUserObjects::model()->getObjectsLike($this->user_id, $search);
		$unt = CUserfiles::model()->getFilesLike($this->user_id, $search);

		$this->render('search', array('pstContent' => $pstContent,'unt'=>$unt,'obj'=>$obj));
	}

	public function actionLibrary($lib='') {
		//$this->layout = 'concept1';
		switch ($lib) {
			case 'v':
			case 'a':
			case 'd':
			case 'p':


				$type_id = Utils::getSectionIdByAlias($lib);
				$productsInfo = CProduct::getUserProducts($this->user_id,$type_id);
				$mb_content_items = CUserObjects::model()->getList($this->user_id, $type_id);
				$mb_content_items_unt = CUserfiles::model()->getFileListUnt($this->user_id);
				$this->render('library', array('mb_content_items' => $mb_content_items,
					'productsInfo' => $productsInfo,
					'mb_content_items_unt' => $mb_content_items_unt,'nav_lib'=>$lib));
				break;
			default:
				$this->render('library');
				return;
		}
	}

	public function actionDevices() {
		$tst = Utils::getDeviceTypes();
		$dst = Yii::app()->db->createCommand()
				->select('*')
				->from('{{userdevices}}')
				->where('user_id = ' . Yii::app()->user->getId())
				->queryAll();
		//$this->render('/universe/devices', array('tst' => $tst, 'dst' => $dst));
		$this->render('/devices/index', array('tst' => $tst, 'dst' => $dst));
	}



	/**
	 * добавить в пространство вариант продукта с витрины
	 *
	 * @param integer $id - идентификатор варианта продукта с витрины
	 */
	public function actionTadd($id = 0) {
		$result = 0;
		$this->layout = '/layouts/ajax';
		$orders = new COrder();
		if (!empty($this->userInfo) && !empty($id)) {
			$cmd = Yii::app()->db->createCommand()
					->select('pv.id, pv.product_id, pv.online_only, ptp.title, ppv.value, oi.price_id, oi.rent_id')
					->from('{{product_variants}} pv')
					->join('{{orders}} o', 'o.user_id = ' . $this->userInfo['id'] . ' AND o.state = ' . _ORDER_PAYED_)
					->join('{{order_items}} oi', 'oi.variant_id=pv.id')
					->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id')
					->join('{{product_type_params}} ptp', 'ptp.id=ppv.param_id')
					->where('pv.id = :id')
					->group('ppv.id')
					->order('pv.id ASC, ptp.srt DESC');
			$cmd->bindParam(':id', $id, PDO::PARAM_INT);
			$prms = $cmd->queryAll();
			if (!empty($prms)) {
				$params = array();
				foreach ($prms as $p) {
					$params[$p['title']] = $p['value'];
					if (!empty($p['price_id']))
						$price_id = $p['price_id'];
					if (!empty($p['rent_id']))
						$rent_id = $p['rent_id'];
				}
			}


			$cmd = Yii::app()->db->createCommand()
					->select('id')
					->from('{{typedfiles}}')
					->where('variant_id = :id AND user_id = ' . $this->userInfo['id']);
			$cmd->bindParam(':id', $id, PDO::PARAM_INT);
			$alreadyInCloud = $cmd->queryRow();
			if ($alreadyInCloud) {
				$result = $alreadyInCloud['id'];
			} else {
				$canAdd = false;
				if (!empty($price_id)) {
					$canAdd = true;
				} else {
					if (!empty($rent_id)) {
						$cmd = Yii::app()->db->createCommand()
								->select('*')
								->from('{{actual_rents}}')
								->where('user_id = ' . $this->userInfo['id'] . ' AND variant_id = :id')
								->order('start DESC');
						$cmd->bindParam(':id', $id, PDO::PARAM_INT);
						$actualRents = $cmd->queryAll();
						if (!empty($actualRents))
							foreach ($actualRents as $a) {
								$start = strtotime($a['start']);
								if ($start > 0) {
									$less = $start + Utils::parsePeriod($a['period'], $a['start']) - time();
									if ($less) {
										//АРЕНДА ЕЩЕ НЕ ИСТЕКЛА - ДОБАВИТЬ В ПРОСТРАНСТВО МОЖНО
										$canAdd = true;
										break;
									}
								} else {
									//АРЕНДА ЕЩЕ НЕ НАЧАЛАСЬ - ДОБАВИТЬ В ПРОСТРАНСТВО МОЖНО
									$canAdd = true;
									break;
								}
							}
					}
				}

				if (empty($price_id) && empty($rent_id)) {
					$canAdd = true;
				}

				if ($canAdd) {
					$cmd = Yii::app()->db->createCommand()
							->select('p.title')
							->from('{{products}} p')
							->join('{{product_variants}} pv', 'pv.product_id = p.id')
							->where('pv.id = :id');
					$cmd->bindParam(':id', $id, PDO::PARAM_INT);
					$productInfo = $cmd->queryRow();
					if (!empty($productInfo)) {
						$title = $productInfo['title'];

						$sql = '
									INSERT INTO {{typedfiles}}
										(id, variant_id, user_id, title, collection_id, variant_quality_id)
									VALUES
										(null, :id, ' . $this->userInfo['id'] . ', :title, 0, :qvid)
								';
						$cmd = Yii::app()->db->createCommand($sql);
						$cmd->bindParam(':id', $id, PDO::PARAM_INT);
						$cmd->bindParam(':title', $title, PDO::PARAM_STR);
						$cmd->bindParam(':qvid', $_POST['qvid'], PDO::PARAM_STR);
						$cmd->execute();
						$result = Yii::app()->db->getLastInsertID('{{typedfiles}}');
					}
				}
			}
		}
		$this->render('tadd', array('result' => $result));
	}

	/**
	 * Отображение подробной информации по объекту витрины в ПП (пространстве пользователя)
	 *
	 * @param integer $id - идентификатор объекта в ПП
	 */
	public function actionTview($id = 0) {
		$Order = new COrder();
		$dsc = $info = $params = array();
		$userId = intval(Yii::app()->user->getId());
		$subAction = 'view';
		if (!empty($this->userInfo) && !empty($id)) {
			$cmd = Yii::app()->db->createCommand()
					->select('tf.id, tf.title, tf.variant_id, vq.preset_id')
					->from('{{typedfiles}} tf')
					->leftJoin('{{variant_qualities}} vq', 'tf.variant_quality_id=vq.id')
					->where('tf.id = :id AND tf.user_id = ' . $this->userInfo['id']);
			$cmd->bindParam(':id', $id, PDO::PARAM_INT);
			$info = $cmd->queryRow();
			if (!empty($info)) {
				if (!empty($info['preset_id']))
					$presetCondition = ' AND vq.preset_id <= ' . (intval($info['preset_id']));
				else
					$presetCondition = '';
				$prms = Yii::app()->db->createCommand()
								->select('pv.id, pv.product_id, pv.online_only, ptp.title, ppv.value, pr.id AS price_id, r.id AS rent_id')
								->from('{{product_variants}} pv')
								->join('{{variant_qualities}} vq', 'pv.id=vq.variant_id')
								->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id')
								->join('{{product_type_params}} ptp', 'ptp.id=ppv.param_id')
								->leftJoin('{{prices}} pr', 'pr.variant_id=pv.id')
								->leftJoin('{{rents}} r', 'r.variant_id=pv.id')
								->where('pv.id = ' . $info['variant_id'] . ' AND ptp.active <= ' . $this->userPower . $presetCondition)
								->group('ppv.id')
								->order('pv.id ASC, ptp.srt DESC')->queryAll();
				$vIds = array();
				if (!empty($prms)) {
					$dsc = Yii::app()->db->createCommand()
									->select('*')
									->from('{{product_descriptions}}')
									->where('product_id = ' . $prms[0]['product_id'])->queryRow();
					$params = array();
					foreach ($prms as $p) {
						$params[$p['title']] = $p['value'];
						$info['online_only'] = $p['online_only'];
						$info['price_id'] = $p['price_id'];
						$info['rent_id'] = $p['rent_id'];

						$vIds[$p['id']] = $p['id'];
					}
				}

				$orders = Yii::app()->db->createCommand()
					->select('o.id AS oid, o.state, oi.id AS iid, oi.variant_id, oi.price_id, oi.rent_id, oi.price, oi.variant_quality_id, vq.preset_id')
					->from('{{orders}} o')
			        ->join('{{order_items}} oi', 'o.id=oi.order_id')
			        ->leftJoin('{{variant_qualities}} vq', 'vq.id=oi.variant_quality_id')
					->where('o.user_id = ' . $userId)
					->order('o.state DESC, o.created DESC')->queryAll();

				$qualities = array();
				if (!empty($vIds))
					$qualities = Yii::app()->db->createCommand()
						->select('pf.id AS pfid, vq.variant_id, pf.preset_id, pf.fname')
						->from('{{variant_qualities}} vq')
						->join('{{product_files}} pf', 'pf.variant_quality_id = vq.id')
						->where('vq.variant_id IN (' . implode(',', $vIds) . ')' . $presetCondition)
						->queryAll();

				$subAction = 'view';
				if (!empty($_GET['do'])) {
					$subAction = $_GET['do'];
				}
				if (!empty($info['rent_id'])) {
					$rents = Yii::app()->db->createCommand()
							->select('*')
							->from('{{actual_rents}}')
							->where('variant_id = ' . $info['variant_id'] . ' AND user_id = ' . $this->userInfo['id'])
							->order('start DESC')//СНАЧАЛА ИСПОЛЬЗУЕМ СТАРТОВАВШУЮ АРЕНДУ
							->queryAll();
					if (!empty($rents)) {
						//ПРИ ЛЮБОМ ДЕЙСТВИИ ПРОВЕРЯЕМ ТЕКУЩИЕ АРЕНДЫ
						foreach ($rents as $r) {
							if (strtotime($r['start']) > 0) {
								$info['start'] = $r['start'];
								$info['period'] = $r['period'];
								if (strtotime($r['start']) + Utils::parsePeriod($r['period'], $r['start']) - time() <= 0) {
									//СРОК АРЕНДЫ ИСТЕК
									$sql = 'DELETE FROM {{actual_rents}} WHERE id=' . $r['id'];
									Yii::app()->db->createCommand($sql)->execute();

									//УДАЛЯЕМ ИЗ ЛИЧНОГО ПРОСТРАНСТВА
									$sql = 'DELETE FROM {{typedfiles}} WHERE variant_id=' . $r['variant_id'] . ' AND user_id = ' . $r['user_id'];
									Yii::app()->db->createCommand($sql)->execute();

									$subAction = 'view';
									$info['start'] = '';
									$info['period'] = '';
								}
							}
						}
					}
				}

				//ЕСЛИ НЕТ ЦЕН НИ ПОКУПКИ НИ АРЕНДЫ, ТО ДОСТУПНО И СКАЧКА И ОНЛАЙН
				$neededQuality = ''; $fid = 0;
				if (!empty($_GET['quality']))
				{
					$neededQuality = $_GET['quality'];
				}
				if (!empty($_GET['fid']))
				{
					$fid = $_GET['fid'];
				}
				switch ($subAction) {
					case "download":
						if ($info['online_only']) {
							//СКАЧКА ЗАПРЕЩЕНА
							$subAction = 'view';
							break;
						}

					case "online":
						/*
						  в случае аренды стартуем аренду (поле start таблицы actual_rents)
						  не забыть учесть, что товар может быть арендован многократно
						  в этом случае новую аренду не стартуем до тех пора пока не истечет предыдущая аренда
						 */
						if (!empty($rents)) {
							foreach ($rents as $r) {
								if (strtotime($r['start']) == 0) {
									$start = date('Y-m-d H:i:s');
									$sql = 'UPDATE {{actual_rents}} SET start="' . $start . '" WHERE id=' . $r['id'];
									Yii::app()->db->createCommand($sql)->execute();
									$subAction = 'online'; //ПОДТВЕРЖДАЕМ ДЕЙСТВИЕ ТК МОЖЕТ БЫТЬ СБРОШЕНО ПРЕДЫДУЩЕЙ ИСТЕКШЕЙ АРЕНДОЙ
									$info['start'] = $start;
									$info['period'] = $r['period'];
									break;
								}
							}
						}
						break;
				}
			}
		}
		$this->render('tview', array('info' => $info, 'params' => $params, 'dsc' => $dsc,
			'qualities' => $qualities, 'fid' => $fid, 'orders' => $orders,
			'subAction' => $subAction, 'neededQuality' => $neededQuality));
	}

	/**
	 * Отображение подробной информации по типизированному объекту пользователя в ПП (пространстве пользователя)
	 *
	 * @param integer $id - идентификатор объекта в ПП
	 */
	public function actionOview($id = 0) {
		$prms = $params = $files = array();
		$subAction = 'view';
		if (!empty($this->userInfo) && !empty($id)) {
			$cmd = Yii::app()->db->createCommand()
					->select('uo.id, uo.title AS uotitle, ptp.title AS ptptitle, uopv.value')
					->from('{{userobjects}} uo')
					->join('{{userobjects_param_values}} uopv', 'uopv.object_id=uo.id')
					->join('{{product_type_params}} ptp', 'ptp.id=uopv.param_id')
					->where('uo.id = :id AND uo.user_id = ' . $this->userInfo['id']);
			$cmd->bindParam(':id', $id, PDO::PARAM_INT);
			$prms = $cmd->queryAll();
			if (!empty($prms)) {
				$params = array();
				foreach ($prms as $p) {
					$params[$p['ptptitle']] = $p['value'];
				}

				$cmd = Yii::app()->db->createCommand()
						->select('fv.id, fv.file_id, fl.fname')
						->from('{{userfiles}} uf')
						->join('{{files_variants}} fv', 'uf.id=fv.file_id')
						->join('{{filelocations}} fl', 'fl.id=fv.id')
						->where('uf.object_id = :id')
						->group('fl.id');
				$cmd->bindParam(':id', $id, PDO::PARAM_INT);
				$files = $cmd->queryAll();

				$subAction = 'view';
				if (!empty($_GET['do'])) {//ДОЛЖНО БЫТЬ УКАЗАНО ДЕЙСТВИЕ И ВАРИАНТ
					$subAction = $_GET['do'];
				}

				switch ($subAction) {
					case "download":
						break;

					case "online":
						break;
				}
			}
		}
		$this->render('oview', array('prms' => $prms, 'params' => $params, 'files' => $files, 'subAction' => $subAction));
	}

	/**
	 * УДАЛИТЬ ОБЪЕКТ ИЗ ПП
	 *
	 * @param integer $id - идентификатор объекта в ПП
	 */
	public function actionRemove($id = 0) {
		$result = '';
		$cmd = Yii::app()->db->createCommand()
				->select('id, variant_id')
				->from('{{typedfiles}} tf')
				->where('id = :id AND user_id = ' . Yii::app()->user->getId());
		$cmd->bindParam(':id', $id, PDO::PARAM_INT);
		$tInfo = $cmd->queryRow();
		if (!empty($tInfo)) {
			$sql = 'DELETE FROM {{typedfiles}} WHERE id = ' . $tInfo['id'];
			Yii::app()->db->createCommand($sql)->execute();
			//УДАЛЯЕМ ВОЗМОЖНУЮ ИНФУ ОБ АРЕНДЕ
			$sql = 'DELETE FROM {{actual_rents}} WHERE variant_id = ' . $tInfo['variant_id'] . ' AND user_id = ' . Yii::app()->user->getId();
			Yii::app()->db->createCommand($sql)->execute();
			$result = 'ok';
		}
		$this->render('remove', array('result' => $result));
	}

}
