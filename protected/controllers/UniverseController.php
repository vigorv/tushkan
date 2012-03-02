<?php

class UniverseController extends Controller {

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

    public function actionIndex2($section='') {
	$this->layout = 'concept1';


	$type_id = Utils::getSectionIdByName($section);

	if ($type_id) {
	    $mb_content_items = Yii::app()->db->createCommand()
		    ->select('id,  title')
		    ->from('{{userfiles}}')
		    ->where('type_id=' . $type_id)
		    ->queryAll();
	} else
	    $mb_content_items = Yii::app()->db->createCommand()
		    ->select('id, title')
		    ->from('{{userlocks}} ul')
		    ->join('{{userfiles}} uf', ' ((uf.id =ul.lock_id) AND (ul.type=1))');

	$media = Utils::getMediaList();
	$this->render('mainblock', array(
	    'mb_top_items' => array(
		array('caption' => $media[1]['title'], 'link' => $media[1]['link']),
		array('caption' => 'Музыка', 'link' => '/universe/index?section=music'),
		array('caption' => 'Фото', 'link' => '/universe/index?section=music'),
		array('caption' => 'Документы', 'link' => '/universe/index?section=music')
	    ),
	    'section' => $section, 'mb_content_items' => $mb_content_items));
    }

    public function actionIndex() {
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

    public function actionExt() {
	if (isset($_GET['goods_add'])) {

	}

	$this->render('steps');
    }

    /**
     * обработчик ответа от конвертора
     * переносит сконвертированный файл в типизированные объекты
     * входные параметры передаются в $_POST
     * 		result
     * 		task_id
     * 		server_id
     * 		folder
     * 		filename
     * 		fsize
     * 		type_id
     *
     */
    public function actionTypify() {
//*ЗАГЛУШКА
	$result = 0;
	$task_id = 1;
	$server_id = 1;
	$folder = 1;
	$filename = 'generated_filename.avi';
	$fsize = 1000000000;
	$type_id = 1;
//*///КОНЕЦ ЗАГЛУШКИ
	/*
	  if (!empty($_POST['']))
	  {
	  $convertInfo = unserialize($_POST['']);

	  $result		= $convertInfo['result'];
	  $task_id	= $convertInfo['task_id'];
	  $server_id	= $convertInfo['server_id'];
	  $folder		= $convertInfo['folder'];
	  $filename	= $convertInfo['filename'];
	  $fsize		= $convertInfo['fsize'];
	  $type_id	= $convertInfo['type_id'];
	  }
	  // */
	if (!empty($task_id)) {
	    $cmd = Yii::app()->db->createCommand()
		    ->select('*')
		    ->from('{{convert_queue}}')
		    ->where('task_id = :id');
	    $cmd->bindParam(':id', $task_id, PDO::PARAM_INT);
	    $queue = $cmd->queryRow();
	    if (!empty($queue)) {//ЕСЛИ ЕСТЬ ИНФО О ЗАДАНИИ
//ПРОВЕРКА РЕЗУЛЬТАТА ТИПИЗАЦИИ
		if (!empty($result)) {
//ОБРАБОТКА ОШИБКИ ТИПИЗАЦИИ
		} else {
//ЧТЕНИЕ ИНФО О ФАЙЛЕ
		    $cmd = Yii::app()->db->createCommand()
			    ->select('*')
			    ->from('{{userfiles}}')
			    ->where('id = ' . $queue['id']);
		    $fileInfo = $cmd->queryRow();
//ЧТЕНИЕ ИНФО О ЛОКАЦИИ ФАЙЛА
		    $cmd = Yii::app()->db->createCommand()
			    ->select('*')
			    ->from('{{filelocations}}')
			    ->where('id = ' . $queue['id']);
		    $locInfo = $cmd->queryRow();
//ЧТО ДЕЛАТЬ С ЗАПИСЯМИ О ФАЙЛЕ? УТОЧНИТЬ
//СОЗДАНИЕ ЗАПИСИ ТИПИЗИРОВАННОГО ОБЪЕКТА
		    $objInfo = array(
			'id' => $fileInfo['id'],
			'user_id' => $fileInfo['user_id'],
			'title' => $fileInfo['title'],
			'type_id' => intval($type_id),
		    );
		    $sql = 'INSERT INTO {{typedfiles}} (id, variant_id, user_id, fsize, title, userobject_id)
			    		VALUES (null, 0, ' . $objInfo['user_id'] . ', :fsize, "' . $objInfo['title'] . '", ' . $objInfo['id'] . ')';
		    $cmd = Yii::app()->db->createCommand($sql);
		    $cmd->bindParam(':fsize', $fsize, PDO::PARAM_LOB);
		    $cmd->execute();

		    $sql = 'INSERT INTO {{usertobjects}} (id, user_id, title, type_id)
			    		VALUES (' . $objInfo['id'] . ', ' . $objInfo['user_id'] . ', "' . $objInfo['title'] . '", ' . $objInfo['type_id'] . ')';
		    Yii::app()->db->createCommand($sql)->execute();

//ВЫБИРАЕМ ПЕРЕЧЕНЬ ПАРАМЕТРОВ ДЛЯ ОБЪЕКТОВ ДАННОГО ТИПА
		    $cmd = Yii::app()->db->createCommand()
			    ->select('ptp.id, ptp.title')
			    ->from('{{product_type_params}} ptp')
			    ->join('{{product_types_type_params}} pttp', 'ptp.id = pttp.param_id')
			    ->where('pttp.type_id = :id');
		    $cmd->bindParam(':id', $type_id, PDO::PARAM_INT);
		    $params = $cmd->queryAll();

		    $height = 200;
		    $width = 400; //ПАРАМЕТРЫ ДЛЯ ТЕСТА
//ВООБЩЕ ПАРАМЕТРЫ ДОЛЖНЫ ПРИХОДИТЬ ОТДЕЛЬНО. К ОБСУЖДЕНИЮ: ОТКУДА?
		    if (!empty($params)) {
//СОХРАНЯЕМ ЗНАЧЕНИЯ ПАРАМЕТРОВ ДЛЯ ОБЪЕКОВ ДАННОГО ТИПА
			foreach ($params as $p) {
			    if (!empty($$p['title'])) {
				$p_id = $p['id'];
				$p_vl = $$p['title'];
				$sql = 'INSERT INTO {{tobjects_param_values}} (id, param_id, value, userobject_id)
						    		VALUES (null, ' . $p_id . ',
						    		"' . $p_vl . '", ' . $objInfo['id'] . ')';
				Yii::app()->db->createCommand($sql)->execute();
			    }
			}
		    }

//СОЗДАНИЕ ЛОКАЦИИ ОБЪЕКТА
		    $objLocInfo = array(
			'id' => $locInfo['id'],
			'user_id' => $locInfo['user_id'],
			'server_id' => intval($server_id),
			'state' => 0, // ?? ЧТО СЮДА ПРОПИСАТЬ ??
			'fsize' => $fsize,
			'fname' => $filename,
			'folder' => $folder,
		    );
		    $sql = 'INSERT INTO {{userobjectlocations}} (id, user_id, server_id, state, fsize, fname, folder)
			    		VALUES (' . $locInfo['id'] . ', ' . $locInfo['user_id'] . ', ' . $locInfo['server_id'] . ',
			    		' . $locInfo['state'] . ', ' . $locInfo['fsize'] . ', "' . $locInfo['fname'] . '", ' . $locInfo['folder'] . ')';
		    Yii::app()->db->createCommand($sql)->execute();

//ЧИСТКА ОЧЕРЕДИ КОНВЕРТИРОВАНИЯ
		    $sql = 'DELETE FROM {{convert_queue}} WHERE id=' . $queue['id'];
		    Yii::app()->db->createCommand($sql)->execute();
		}
	    }
	}
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
		    ->join('{{order_items}} oi', 'oi.variant_id')
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
			$productInfo = Yii::app()->db->createCommand()
				->select('title')
				->from('{{products}}')
				->where('id = ' . $prms[0]['product_id'])
				->queryRow();
			$title = '';
			if (!empty($productInfo['title']))
			    $title = $productInfo['title'];

			$sql = '
							INSERT INTO {{typedfiles}}
								(id, variant_id, user_id, title, collection_id)
							VALUES
								(null, :id, ' . $this->userInfo['id'] . ', "' . $title . '", 0)
						';
			$cmd = Yii::app()->db->createCommand($sql);
			$cmd->bindParam(':id', $id, PDO::PARAM_INT);
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
				->where('pv.id = ' . $info['variant_id'])
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
    public function actionRemove($id = 0)
    {
    	$result = '';
    	$cmd = Yii::app()->db->createCommand()
    		->select('id, variant_id')
    		->from('{{typedfiles}} tf')
    		->where('id = :id AND user_id = ' . Yii::app()->user->getId());
    	$cmd->bindParam(':id', $id, PDO::PARAM_INT);
    	$tInfo = $cmd->queryRow();
    	if (!empty($tInfo))
    	{
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