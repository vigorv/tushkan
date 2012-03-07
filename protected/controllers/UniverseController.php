<?php

class UniverseController extends Controller {

	var $user_id;

	//public $panel;
	//public $goods;

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
		if (!(Yii::app()->request->isAjaxRequest))
			$this->layout = 'concept1';
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
		$this->render('/products/top', array('pst' => $pst), true);		
	}

	

	public function actionSearch($text='') {
		$search = filter_var($text, FILTER_SANITIZE_STRING);
		$lst = array();
		$pst = CProduct::model()->getProductList(CProduct::getShortParamsIds(), $this->userPower, $search);
		$pstContent = $this->renderPartial('/products/top', array('pst' => $pst), true);

		$obj = CUserObjects::model()->getObjectsLike($this->user_id, $search);
		//$objContent = $this->renderPartial('/universe/objects',array('obj'=>$obj));
		$unt = CUserfiles::model()->getFilesLike($this->user_id, $search);
		$untContent = $this->renderPartial('/files/untyped_list', array('unt' => $unt));
		$this->render('search', array('pstContent' => $pstContent));
	}

	public function actionLibrary($lib='') {
		//$this->layout = 'concept1';
		switch ($lib) {
			case 'v':
			case 'a':
			case 'd':
			case 'p':
				$type_id = Utils::getSectionIdByAlias($lib);
				$productsInfo = CProduct::getUserProducts($this->user_id);
				$mb_content_items = CUserObjects::model()->getList($this->user_id, $type_id);
				$mb_content_items_unt = CUserfiles::model()->getFileListUnt($this->user_id);
				$this->render('library', array('mb_content_items' => $mb_content_items,
					'productsInfo' => $productsInfo,
					'mb_content_items_unt' => $mb_content_items_unt));
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
		$this->render('/universe/devices', array('tst' => $tst, 'dst' => $dst));
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
										(id, variant_id, user_id, title, collection_id)
									VALUES
										(null, :id, ' . $this->userInfo['id'] . ', :title, 0)
								';
						$cmd = Yii::app()->db->createCommand($sql);
						$cmd->bindParam(':id', $id, PDO::PARAM_INT);
						$cmd->bindParam(':title', $title, PDO::PARAM_STR);
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
		$dsc = $info = $params = array();
		$subAction = 'view';
		if (!empty($this->userInfo) && !empty($id)) {
			$cmd = Yii::app()->db->createCommand()
					->select('*')
					->from('{{typedfiles}}')
					->where('id = :id AND user_id = ' . $this->userInfo['id']);
			$cmd->bindParam(':id', $id, PDO::PARAM_INT);
			$info = $cmd->queryRow();
			if (!empty($info)) {
				$prms = Yii::app()->db->createCommand()
								->select('pv.id, pv.product_id, pv.online_only, ptp.title, ppv.value, pr.id AS price_id, r.id AS rent_id')
								->from('{{product_variants}} pv')
								->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id')
								->join('{{product_type_params}} ptp', 'ptp.id=ppv.param_id')
								->leftJoin('{{prices}} pr', 'pr.variant_id=pv.id')
								->leftJoin('{{rents}} r', 'r.variant_id=pv.id')
								->where('pv.id = ' . $info['variant_id'] . ' AND ptp.active <= ' . $this->userPower)
								->group('ppv.id')
								->order('pv.id ASC, ptp.srt DESC')->queryAll();
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
					}
				}

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
		$this->render('tview', array('info' => $info, 'params' => $params, 'dsc' => $dsc, 'subAction' => $subAction));
	}

	/**
	 * Отображение подробной информации по типизированному объекту пользователя в ПП (пространстве пользователя)
	 *
	 * @param integer $id - идентификатор объекта в ПП
	 */
	public function actionOview($id = 0) {
		$info = $params = array();
		$subAction = 'view';
		if (!empty($this->userInfo) && !empty($id)) {
			$cmd = Yii::app()->db->createCommand()
					->select('uo.id, uo.title, ptp.title, uopv.value')
					->from('{{userobjects}} uo')
					->join('{{userobjects_param_values}} uopv', 'uopv.object_id=uf.id')
					->join('{{product_type_params}} ptp', 'ptp.id=uopv.param_id')
					->where('uo.id = :id AND uo.user_id = ' . $this->userInfo['id'])
					->group('uopv.id');
			$cmd->bindParam(':id', $id, PDO::PARAM_INT);
			$info = $cmd->queryAll();
			if (!empty($info)) {
				$cmd = Yii::app()->db->createCommand()
						->select('fv.id, fv.file_id, fl.fname')
						->from('{{filevariants}} fv')
						->join('{{filelocations}} fl', 'fl.id=fv.id')
						->where('fv.file_id = :id')
						->group('fl.id');
				$cmd->bindParam(':id', $id, PDO::PARAM_INT);
				$prms = $cmd->queryAll();
				if (!empty($prms)) {
					$params = array();
					foreach ($prms as $p) {
						$params[$p['title']] = $p['value'];
					}
				}

				$subAction = 'view';
				if (!empty($_GET['do']) && !empty($_GET['vid'])) {//ДОЛЖНО БЫТЬ УКАЗАНО ДЕЙСТВИЕ И ВАРИАНТ
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
		$this->render('oview', array('info' => $info, 'params' => $params, 'subAction' => $subAction));
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
