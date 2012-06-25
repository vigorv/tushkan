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
	 * проверяем состояние очереди по параметрам запроса на типизацию(конвертирование)
	 *
		$id - id файла пользователя (original_id)
	 *
	 */
	public function checkQueue($id)
	{
		$isTypified = false;
		$result = 'bad original ID';
		if (!empty($id))
		{
			$originalId = $id;
			$result = 'user not registered';
			if (!empty($this->userInfo['id']))
			{
				//ВЫБОРКА ВАРИАНТА (У НЕТИПИЗИРОВАННОГО ВАРИАНТ ОДИН И ЛОКАЦИЯ ОДНА)
				$cmd = Yii::app()->db->createCommand()
					->select('fv.id, fv.preset_id')
					->from('{{files_variants}} fv')
					->where('fv.id = :originalId');
				$cmd->bindParam(':originalId', $originalId, PDO::PARAM_INT);
				$variantExists = $cmd->queryRow();

				if (!empty($variantExists) && !empty($variantExists['preset_id']))
				{
					$isTypified = true;
					$result = $variantExists['id'];
				}
				else
				{
					$result = 'ok';
					//ПОВЕРЯЕМ НАЛИЧИЕ В ОЧЕРЕДИ КОНВЕРТЕРА
					$cmd = Yii::app()->db->createCommand()
						->select('id, cmd_id, state')
						->from('{{income_queue}}')
						->where('cmd_id < 50 AND user_id = :id AND partner_id=0 AND original_id=:oid');
					$cmd->bindParam(':id', $userId, PDO::PARAM_INT);
					$cmd->bindParam(':oid', $originalId, PDO::PARAM_INT);
					$queueExists = $cmd->queryRow();
					if ($queueExists)
					{
						$result = 'queue|' . $queueExists['cmd_id'] . '|' . $queueExists['state'];
					}
				}
			}
		}
		return $result;
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

				$result = $this->checkQueue($fileId);
				if (($result == 'ok'))
				{
					$state = $result;
					$partnerId = 0;
					$originalId = $fileId;
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
						'priority'		=> 200,
						'state'			=> 0,
						'station_id'	=> 0,
						'partner_id'	=> $partnerId,
						'user_id'		=> $this->userInfo['id'],
						'original_variant_id'	=> 0,
					);
					$cmd = Yii::app()->db->createCommand()->insert('{{income_queue}}', $queue);
					$result = 'queue';
				}

				$cmd = Yii::app()->db->createCommand()
						->select('id, title, object_id')
						->from('{{userfiles}}')
						->where('id = :id AND user_id = ' . $this->userInfo['id']);
				$cmd->bindParam(':id', $fileId);
				$fileInfo = $cmd->queryRow();
				if (!empty($fileInfo) && empty($fileInfo['object_id'])) {
					//ПРИ ТИПИЗАЦИИ ЗАКРЕПЛЯЕМ ФАЙЛ ЗА ОБЪЕКТОМ
					$sql = 'INSERT INTO {{userobjects}} (id, title, user_id, type_id, active, parent_id, modified)
						VALUES (null, :title, :user_id, :type_id, 0, 0, "' . date("Y-m-d H:i:s") . '")
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

					$fileInfo['object_id'] = $objectId;

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
		if (!empty($objectId))
		{
			Yii::app()->request->redirect('/universe/oview/' . $objectId);
			return;
		}
		if (!empty($fileId))
		{
			Yii::app()->request->redirect('/files/fview/' . $fileId);
			return;
		}
		Yii::app()->request->redirect('/universe');
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

		$this->render('uploadFile', array('types' => $types, 'user_id' => $this->userInfo['id'], /* 'kpt' => $kpt */));
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
		$pstContent = $this->renderPartial('/products/sresult', array('pst' => $pst), true);

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

				$qstContent = '';
				$uid = Yii::app()->user->getId();
				if (!empty($uid))
				{
					$qst = Yii::app()->db->createCommand()
						->select('iq.info, p.title, iq.cmd_id, iq.state, iq.date_start')
						->from('{{income_queue}} iq')
						->leftJoin('{{partners}} p', 'p.id=iq.partner_id')
						->where('iq.cmd_id < 50 AND iq.user_id = ' . $uid)
						->queryAll();
					if ($qst)
					{
						$qstContent = $this->renderPartial('/universe/queue', array('qst' => $qst), true);
					}
				}

				$type_id = Utils::getSectionIdByAlias($lib);
				$mediaList = Utils::getMediaList();
				$productsInfo = CProduct::model()->getUserProducts($this->user_id,$type_id);
				$mb_content_items = CUserObjects::model()->getList($this->user_id, $type_id);
				$mb_content_items_unt = CUserfiles::model()->getFileListUnt($this->user_id);
				$this->render('library', array('mb_content_items' => $mb_content_items,
					'productsInfo' => $productsInfo,
					'qstContent' => $qstContent,
					'type_id' => $type_id,
					'mediaList' => $mediaList,
					'mb_content_items_unt' => $mb_content_items_unt,'nav_lib'=>$lib));
				break;
			default:
				$this->render('library');
				return;
		}
	}

	/**
	 * показать список привязанных устройств
	 *
	 * @param mixed $id - код состояния вызвавшего действия
	 */
	public function actionDevices($id = 0) {
		$tst = CDevices::getDeviceTypes();
		$dst = Yii::app()->db->createCommand()
				->select('*')
				->from('{{userdevices}}')
				->where('user_id = ' . Yii::app()->user->getId())
				->queryAll();
		$this->render('/universe/devices', array('tst' => $tst, 'dst' => $dst));
		//$this->render('/devices/index', array('tst' => $tst, 'dst' => $dst));
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
					->leftJoin('{{orders}} o', 'o.user_id = ' . $this->userInfo['id'] . ' AND o.state = ' . _ORDER_PAYED_)
					->leftJoin('{{order_items}} oi', 'oi.variant_id=pv.id')
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
								->select('pv.id, pv.product_id, pv.online_only, pv.type_id, ptp.title, ppv.value, pr.id AS price_id, r.id AS rent_id')
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

					$partnerInfo = Yii::app()->db->createCommand()
						->select('prt.id, prt.title, prt.sprintf_url, p.original_id')
						->from('{{products}} p')
						->join('{{partners}} prt', 'prt.id = p.partner_id')
						->where('p.id = ' . $prms[0]['product_id'])->queryRow();

					$params = array();
					foreach ($prms as $p) {
						$params[$p['title']] = $p['value'];
						$info['online_only'] = $p['online_only'];
						if (!empty($p['price_id']))
							$info['price_id'] = $p['price_id'];
						if (!empty($p['rent_id']))
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
								if (strtotime($r['start']) <= 0) {
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
		$type_id = $prms[0]['type_id'];
		$mediaList = Utils::getMediaList();
		$this->render('tview', array('info' => $info, 'params' => $params, 'dsc' => $dsc,
			'qualities' => $qualities, 'fid' => $fid, 'orders' => $orders,
			'subAction' => $subAction, 'neededQuality' => $neededQuality,
			'type_id' => $type_id, 'mediaList' => $mediaList,
			'partnerInfo' => $partnerInfo
		));
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

	public function actionUploadui()
	{
		$this->layout = 'uploadui';
		$fishKey = CUser::getfishkey($this->userInfo['id'], date('Y-m-d'));
		$uploadServer = CServers::model()->getServer(UPLOAD_SERVER);
//$uploadServer = 'upload';//ДЛЯ ОТЛАДКИ НА ЛОКАЛЬНОЙ МАШИНЕ
		$this->render('uploadui', array('fishKey' => $fishKey, 'uploadServer' => $uploadServer, 'userId' => $this->userInfo['id']));
	}

	/**
	 * ДЕЙСТВИЕ СОХРАНЕНИЯ ФАЙЛА. ЕСЛИ ЗАГРУЗКА ИДЕТ НА ОТДЕЛЬНЫЙ ФАЙЛОВЫЙ СЕРВЕР.
	 * ОФОРМИТЬ ДЕЙСТВИЕ ОТДЕЛЬНЫМ СКРИПТОМ И РАЗМЕСТИТЬ НА ЭТОМ ОТДЕЛЬНОМ ФАЙЛОВОМ СЕРВЕРЕ
	 *
	 */
	public function actionUploaduido()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			header('Access-Control-Allow-Origin: *');
			exit;
		}
		$this->layout = 'ajax';
		$this->render('uploaduido');

		if (!empty($_FILES))
		{
			/**
			 * приходит массив с информацией о файле array(1) {
			 * ["Filedata"]=>
  					array(5) {
						["name"]=>string(43) "design.mycloud.ver1_4_enter_more-clouds.jpg"
						["type"]=>string(10) "image/jpeg"
						["tmp_name"]=>string(14) "/tmp/phpikgVJm"
						["error"]=>int(0)
						["size"]=>int(0)
  					}
  				}
			 */

			//СОХРАНЯЕМ ФАЙЛЫ
				$filePath = '';//ПУТЬ СОХРАНЕНИЯ ФАЙЛА НА СЕРВЕРЕ
				$fileName = '';//ИМЯ ФАЙЛА НА СЕРВЕРЕ
				$fileMD5 = '';//MD5 ФАЙЛА
				$serverIp = ''; //IP ДАННОГО ФАЙЛОВОГО СЕРВЕРА
				$serverId = ''; //ИДЕНТИФИКАТОР ДАННОГО ФАЙЛОВОГО СЕРВЕРА

			//ЗАПОЛНЯЕМ СТРУКТУРУ, ОПИСЫВАЮЩУЮ ФАЙЛ
			if (!empty($saveSuccess))
			{
				$fileInfo = array(
					"file_original" => $_FILES["Filedata"]["name"],
					"file_name" => $fileName,
					"file_path" => $filePath,//ЦЕЛОЕ ЧИСЛО
					"file_MD5" => $fileMD5,
					"file_size" => $fileSize,
					"server_ip" => $serverIp,
				);
			}

			if (!empty($_POST))
			{
				/**
				 * ДОП. ПАРАМЕТРЫ
				 * array(
				 * 		"key"	=>string,
				 * 		"userid"=>int
				 * 		"params"=> array - доп. параметры для типизации файла
				 * )
				 */
				$result = '';
				if (!empty($_POST["key"]) && !empty($_POST['userid']) && !empty($fileInfo))
				{

					$key = $_POST["key"];
					$uid = $_POST["userid"];
					$sfile = serialize($fileInfo);
					$sparams = serialize(array());
					if (!empty($_POST['params']))
						$sparams = serialize($_POST['params']);
					$sum = sha1($sfile . $serverId);

					//ЗАПРОС ЧЕРЕЗ CURL
					$ch = curl_init("http://myicloud.ws/serversync/upload");
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));//ОБХОДИМ ПРОБЛЕМУ С NGINX
					$data = 'key=' . $key . '&uid=' . $uid . '&sum=' . $sum . '&sid=' . $serverId . '&sfile=' . $sfile . '&sparams=' . $sparams;
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
					curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 0);
					$result = curl_exec($ch);
					curl_close($ch);
				}

				if ($result <> 'ok')
				{
					//ДАННЫЕ НЕ ПРОШЛИ ПРОВЕРКУ, ФАЙЛЫ НУЖНО УДАЛИТЬ
				}
			}
		}
	}
}
