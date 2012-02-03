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
        //if (!Yii::app()->user->isGuest) {
        $this->render('index');
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
				->group('ptp.id')
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

					$fSize = 0;
					if (!empty($params[Yii::app()->params['tushkan']['fsizePrmName']]))
					{
						$fSize = $params[Yii::app()->params['tushkan']['fsizePrmName']];
					}
					if ($canAdd && ($this->userInfo['free_limit'] > $fSize))
					{
						$result = 'ok'; $mB = 1024 * 1024;
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
					->group('ptp.id')
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
}
