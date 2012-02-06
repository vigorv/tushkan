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

    public function actionIndex() {
    	//ВЫБОРКА КОНТЕНТА ДОБАВЛЕННОГО С ВИТРИН
		$tFiles = Yii::app()->db->createCommand()
			->select('id, variant_id, title')
			->from('{{typedfiles}}')
			->where('variant_id > 0 AND user_id = ' . $this->userInfo['id'])
			->queryAll();
    	$fParams = array();
    	if (!empty($tFiles))
    	{
    		$tfIds = array();
    		foreach ($tFiles as $tf)
    		{
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

    	//ВЫБОРКА ТИПИЗИРОВАНННОГО КОНТЕНТА
		$tObjects = Yii::app()->db->createCommand()
			->select('id, userobject_id, title')
			->from('{{typedfiles}}')
			->where('userobject_id > 0 AND user_id = ' . $this->userInfo['id'])
			->queryAll();
		$oParams = array();
    	if (!empty($tObjects))
    	{
    		$toIds = array();
    		foreach ($tObjects as $to)
    		{
    			$toIds[$to['userobject_id']] = $to['userobject_id'];
    		}
			$oParams = Yii::app()->db->createCommand()
				->select('uo.id, ptp.title, opv.value')
				->from('{{usertobjects}} uo')
		        ->join('{{tobjects_param_values}} opv', 'uo.id=opv.userobject_id')
		        ->join('{{product_type_params}} ptp', 'ptp.id=opv.param_id')
				->where('uo.id IN (' . implode(', ', $toIds) . ')')
				->group('ptp.id')
				->order('uo.id ASC, ptp.srt DESC')->queryAll();
    	}
        $this->render('index', array('tFiles' => $tFiles, 'fParams' => $fParams,
        	'tObjects' => $tObjects, 'oParams' => $oParams));
    }

    public function actionAdd($step=1) {
        $step = (int) $step;
        $this->render('steps');
    }

    public function actionExt() {
        if(isset($_GET['goods_add'])){


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
    public function actionTypify()
    {
//*ЗАГЛУШКА
		$result		= 0;
		$task_id	= 1;
		$server_id	= 1;
		$folder		= 1;
		$filename	= 'generated_filename.avi';
		$fsize		= 1000000000;
		$type_id	= 1;
//*///КОНЕЦ ЗАГЛУШКИ
    	if (!empty($_POST))
    	{
//*
    		$result		= $_POST['result'];
    		$task_id	= $_POST['task_id'];
    		$server_id	= $_POST['server_id'];
    		$folder		= $_POST['folder'];
    		$filename	= $_POST['filename'];
    		$fsize		= $_POST['fsize'];
    		$type_id	= $_POST['type_id'];
//*/
    	}
    	if (!empty($task_id))
    	{
	    	$cmd = Yii::app()->db->createCommand()
	    		->select('*')
	    		->from('{{convert_queue}}')
	    		->where('task_id = :id');
	    	$cmd->bindParam(':id', $task_id, PDO::PARAM_INT);
	    	$queue = $cmd->queryRow();
	    	if (!empty($queue))//ЕСЛИ ЕСТЬ ИНФО О ЗАДАНИИ
	    	{
		    	//ПРОВЕРКА РЕЗУЛЬТАТА ТИПИЗАЦИИ
		    	if (!empty($result))
		    	{
		    		//ОБРАБОТКА ОШИБКИ ТИПИЗАЦИИ
		    	}
		    	else
		    	{
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
			    		'id'		=> $fileInfo['id'],
			    		'user_id'	=> $fileInfo['user_id'],
			    		'title'		=> $fileInfo['title'],
			    		'type_id'	=> intval($type_id),
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

$height = 200;	$width = 400; //ПАРАМЕТРЫ ДЛЯ ТЕСТА
//ВООБЩЕ ПАРАМЕТРЫ ДОЛЖНЫ ПРИХОДИТЬ ОТДЕЛЬНО. К ОБСУЖДЕНИЮ: ОТКУДА?
		    		if (!empty($params))
		    		{
		    		//СОХРАНЯЕМ ЗНАЧЕНИЯ ПАРАМЕТРОВ ДЛЯ ОБЪЕКОВ ДАННОГО ТИПА
		    			foreach ($params as $p)
		    			{
		    				if (!empty($$p['title']))
		    				{
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
			    		'id'		=> $locInfo['id'],
			    		'user_id'	=> $locInfo['user_id'],
			    		'server_id'	=> intval($server_id),
			    		'state'		=> 0,// ?? ЧТО СЮДА ПРОПИСАТЬ ??
			    		'fsize'		=> $fsize,
			    		'fname'		=> $filename,
			    		'folder'	=> $folder,
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
    public function actionTadd($id = 0)
    {
    	$result = 0;
    	$this->layout = '/layouts/ajax';
    	$orders = new COrder();
    	if (!empty($this->userInfo) && !empty($id))
    	{
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
	    	if (!empty($prms))
	    	{
	    		$params = array();
				foreach ($prms as $p)
				{
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
				if ($alreadyInCloud)
				{
					$result = $alreadyInCloud['id'];
				}
				else
				{
					$canAdd = false;
					if (!empty($price_id))
					{
						$canAdd = true;
					}
					else
					{
						if(!empty($rent_id))
						{
							$cmd = Yii::app()->db->createCommand()
								->select('*')
								->from('{{actual_rents}}')
								->where('user_id = ' . $this->userInfo['id'] . ' AND variant_id = :id')
								->order('start DESC');
							$cmd->bindParam(':id', $id, PDO::PARAM_INT);
							$actualRents = $cmd->queryAll();
							if (!empty($actualRents))
								foreach ($actualRents as $a)
								{
									$start = strtotime($a['start']);
									if ($start > 0)
									{
										$less = $start + Utils::parsePeriod($a['period'], $a['start']) - time();
										if ($less)
										{
											//АРЕНДА ЕЩЕ НЕ ИСТЕКЛА - ДОБАВИТЬ В ПРОСТРАНСТВО МОЖНО
											$canAdd = true;
											break;
										}
									}
									else
									{
										//АРЕНДА ЕЩЕ НЕ НАЧАЛАСЬ - ДОБАВИТЬ В ПРОСТРАНСТВО МОЖНО
										$canAdd = true;
										break;
									}
								}
						}
					}
					if (empty($price_id) && empty($rent_id))
					{
						$canAdd = true;
					}

					$fSize = 0;
					if (!empty($params[Yii::app()->params['tushkan']['fsizePrmName']]))
					{
						$fSize = $params[Yii::app()->params['tushkan']['fsizePrmName']];
					}
					$mB = 1024;
					if ($canAdd && ($this->userInfo['free_limit'] * $mB > $fSize))
					{
						//КОРРЕКТИРУЕМ ОБЪЕМ СВОБОДНОГО ПРОСТРАНСТВА
						$freeLimit = $this->userInfo['free_limit'] * $mB - $fSize;
						if ($freeLimit < 0) $freeLimit = 0;
						$freeLimit = intval(round($freeLimit / $mB));
						$this->userInfo['free_limit'] = $freeLimit;
						Yii::app()->user->setState('dmUserInfo', serialize($this->userInfo));

						$productInfo = Yii::app()->db->createCommand()
							->select('title')
							->from('{{products}}')
							->where('id = ' . $prms[0]['product_id'])
							->queryRow();
						$title = '';
						if (!empty($productInfo['title']))
							$title = $productInfo['title'];

						$sql = 'UPDATE {{users}} SET free_limit=' . $freeLimit . ' WHERE id=' . $this->userInfo['id'];
						Yii::app()->db->createCommand($sql)->execute();

						$sql = '
							INSERT INTO {{typedfiles}}
								(id, variant_id, user_id, fsize, title, userobject_id)
							VALUES
								(null, :id, ' . $this->userInfo['id'] . ', ' . $fSize . ', "' . $title . '", 0)
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
    public function actionTview($id = 0)
    {
    	$info = $params = array();
    	$subAction = 'view';
    	if (!empty($this->userInfo) && !empty($id))
    	{
    		$cmd = Yii::app()->db->createCommand()
    			->select('*')
    			->from('{{typedfiles}}')
    			->where('id = :id AND user_id = ' . $this->userInfo['id']);
    		$cmd->bindParam(':id', $id, PDO::PARAM_INT);
    		$info = $cmd->queryRow();
    		if (!empty($info))
    		{
				$prms = Yii::app()->db->createCommand()
					->select('pv.id, pv.online_only, ptp.title, ppv.value, pr.id AS price_id, r.id AS rent_id')
					->from('{{product_variants}} pv')
			        ->join('{{product_param_values}} ppv', 'pv.id=ppv.variant_id')
			        ->join('{{product_type_params}} ptp', 'ptp.id=ppv.param_id')
			        ->leftJoin('{{prices}} pr', 'pr.variant_id=pv.id')
			        ->leftJoin('{{rents}} r', 'r.variant_id=pv.id')
					->where('pv.id = ' . $info['variant_id'])
					->group('ppv.id')
					->order('pv.id ASC, ptp.srt DESC')->queryAll();
		    	if (!empty($prms))
		    	{
		    		$params = array();
					foreach ($prms as $p)
					{
						$params[$p['title']] = $p['value'];
						$info['online_only'] = $p['online_only'];
						$info['price_id'] = $p['price_id'];
						$info['rent_id'] = $p['rent_id'];
					}
		    	}

		    	$subAction = 'view';
		    	if (!empty($_GET['do']))
		    	{
		    		$subAction = $_GET['do'];
		    	}
				if (!empty($info['rent_id']))
				{
					$rents = Yii::app()->db->createCommand()
						->select('*')
						->from('{{actual_rents}}')
						->where('variant_id = ' . $info['variant_id'] . ' AND user_id = ' . $this->userInfo['id'])
						->order('start DESC')//СНАЧАЛА ИСПОЛЬЗУЕМ СТАРТОВАВШУЮ АРЕНДУ
						->queryAll();
					if (!empty($rents))
					{
						//ПРИ ЛЮБОМ ДЕЙСТВИИ ПРОВЕРЯЕМ ТЕКУЩИЕ АРЕНДЫ
						foreach($rents as $r)
						{
							if (strtotime($r['start']) > 0)
							{
								$info['start'] = $r['start'];
								$info['period'] = $r['period'];
								if (strtotime($r['start']) + Utils::parsePeriod($r['period'], $r['start']) - time() <= 0)
								{
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

	    		switch ($subAction)
	    		{
	    			case "download":
	    				if ($info['online_only'])
	    				{
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
						if (!empty($rents))
						{
							foreach($rents as $r)
							{
								if (strtotime($r['start']) == 0)
								{
									$start = date('Y-m-d H:i:s');
									$sql = 'UPDATE {{actual_rents}} SET start="' . $start . '" WHERE id=' . $r['id'];
									Yii::app()->db->createCommand($sql)->execute();
									$subAction = 'online';//ПОДТВЕРЖДАЕМ ДЕЙСТВИЕ ТК МОЖЕТ БЫТЬ СБРОШЕНО ПРЕДЫДУЩЕЙ ИСТЕКШЕЙ АРЕНДОЙ
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
        $this->render('tview', array('info' => $info, 'params' => $params, 'subAction' => $subAction));
    }


    /**
     * Отображение подробной информации по типизированному объекту пользователя в ПП (пространстве пользователя)
     *
     * @param integer $id - идентификатор объекта в ПП
     */
    public function actionOview($id = 0)
    {
    	$info = $params = array();
    	$subAction = 'view';
    	if (!empty($this->userInfo) && !empty($id))
    	{
    		$cmd = Yii::app()->db->createCommand()
    			->select('*')
    			->from('{{typedfiles}}')
    			->where('id = :id AND user_id = ' . $this->userInfo['id']);
    		$cmd->bindParam(':id', $id, PDO::PARAM_INT);
    		$info = $cmd->queryRow();
    		if (!empty($info))
    		{
	    		$locInfo = Yii::app()->db->createCommand()
	    			->select('*')
	    			->from('{{userobjectlocations}}')
	    			->where('id = ' . $info['id'])
	    			->queryRow();

				$prms = Yii::app()->db->createCommand()
					->select('uo.id, ptp.title, opv.value')
					->from('{{usertobjects}} uo')
			        ->join('{{tobjects_param_values}} opv', 'uo.id=opv.userobject_id')
			        ->join('{{product_type_params}} ptp', 'ptp.id=opv.param_id')
					->where('uo.id = ' . $info['userobject_id'])
					->group('opv.id')
					->order('uo.id ASC, ptp.srt DESC')->queryAll();
		    	if (!empty($prms))
		    	{
		    		$params = array();
					foreach ($prms as $p)
					{
						$params[$p['title']] = $p['value'];
					}
		    	}

		    	$subAction = 'view';
		    	if (!empty($_GET['do']))
		    	{
		    		$subAction = $_GET['do'];
		    	}

				//ЕСЛИ НЕТ ЦЕН НИ ПОКУПКИ НИ АРЕНДЫ, ТО ДОСТУПНО И СКАЧКА И ОНЛАЙН

	    		switch ($subAction)
	    		{
	    			case "download":
					break;

	    			case "online":
	    			break;
	    		}
    		}
    	}
        $this->render('oview', array('info' => $info, 'params' => $params, 'subAction' => $subAction, 'locInfo' => $locInfo));
    }
}
