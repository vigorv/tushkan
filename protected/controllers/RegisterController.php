<?php
Yii::import('ext.classes.SimpleMail');
Yii::import('application.controllers.PaysController');

class RegisterController extends Controller {

    private $_crumbs = array();

    public function actions() {
        return array(
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xffffff,
            ),
        );
    }

    public function actionConfirm($hash = '') {
    	$info = array(); $subAction = '';
        if (!(Yii::app()->request->isAjaxRequest))
		$this->layout = '/layouts/start';
		if (!empty($hash))
		{
        	$cmd = Yii::app()->db->createCommand()
        		->select('*')
        		->from('{{users}}')
        		->where('sess_id = :sess_id');
        	$cmd->bindParam(':sess_id', $hash, PDO::PARAM_STR);
        	$userInfo = $cmd->queryRow();

        	if (empty($userInfo))
        	{
		        $model = new RegisterForm();
		        $model->setScenario('confirm');
				$info['error'] = 1; //ПОЛЬЗОВАТЕЛЬ С УКАЗАННЫМ ХЭШЭМ НЕ НАЙДЕН
        	}
        	else
        	{
				//РАБОТА С ФОРМОЙ ПАРОЛЯ
	        	$model = new PasswordForm();
				$subAction = 'askpassword';
        		//ГЕНЕРИРУЕМ ПАРОЛЬ (ЧТОБЫ ПРЕДЛОЖИТЬ К ИСПОЛЬЗОВАНИЮ)
        		$newPassword = strtolower(substr(md5(time() . $userInfo['name']), 0, 7));
				$info['newpassword'] = $newPassword;
				$info['hash'] = $hash;

				if (!empty($_POST))
				{
		            $model->attributes = $_POST['PasswordForm'];
		            if ($model->validate()) {
						//ПРИСВАИВАЕМ ПАРОЛЬ ПОЛЬЗОВАТЕЛЮ
		            	$attrs = $model->getAttributes();
						$this->identity->password = $attrs['pwd'];
						$newPassword = $this->identity->transformPassword($userInfo);
			        	$sql = 'UPDATE {{users}} SET pwd="' . $newPassword . '", confirmed=1 WHERE id = ' . $userInfo['id'];
			        	$cmd = Yii::app()->db->createCommand($sql)->query();

			        	$this->setTrialMode($userInfo);
	                    //АВТОМАТИЧЕСКАЯ АВТОРИЗАЦИЯ
	                    $this->identity->saveAuthInfo(CUser::model()->findByPk($userInfo['id'])->attributes);
			     		$this->redirect('/universe');
		            }
		            else
		            {
		            	$model->clearErrors();
						$model->addError('pwd', Yii::t('users', 'Password should consist of at least 5 characters'));
		            }
				}
        	}
		}
		else
		{
			//РАБОТА С ФОРМОЙ ЕМЭЙЛА
	        $model = new RegisterForm();
	        $model->setScenario('confirm');
			if (!empty($_POST))
			{
				$subAction = 'post';
	            $model->attributes = $_POST['RegisterForm'];
	            if ($model->validate('confirm')) {
	            	$attrs = $model->getAttributes();
					$model->email = $attrs['email'];
	            	$cmd = Yii::app()->db->createCommand()
	            		->select('*')
	            		->from('{{users}}')
	            		->where('email = :email');
	            	$cmd->bindParam(':email', $model->email, PDO::PARAM_STR);
	            	$userInfo = $cmd->queryRow();
	            	if (!empty($userInfo))
	            	{
						$info['body'] = $this->sendConfirmMail($userInfo);
	            	}
	            	else
	            	{
		            	$model->clearErrors();
		            	$info['error'] = 1;
						$model->addError('email', Yii::t('users', 'Email not found'));
	            	}
	            }
	            else
	            {
	            	$subAction = '';
	            }
			}
		}
        $this->render('confirm', array('model' => $model, 'subAction' => $subAction, 'info' => $info));
    }

	/**
	 * установка нового пароля
	 *
	 * @param string $hash
	 */
    public function actionForget($hash = '') {
		if (!(Yii::app()->request->isAjaxRequest))
        $this->layout = '/layouts/start';
    	$subAction = ''; $info = array();
		if (!empty($hash))
		{
        	$cmd = Yii::app()->db->createCommand()
        		->select('*')
        		->from('{{users}}')
        		->where('sess_id = :sess_id');
        	$cmd->bindParam(':sess_id', $hash, PDO::PARAM_STR);
        	$userInfo = $cmd->queryRow();

        	if (empty($userInfo))
        	{
		        $model = new RegisterForm();
		        $model->setScenario('forget');
				$info['error'] = 1; //ПОЛЬЗОВАТЕЛЬ С УКАЗАННЫМ ХЭШЭМ НЕ НАЙДЕН
        	}
        	else
        	{
				//РАБОТА С ФОРМОЙ ПАРОЛЯ
	        	$model = new PasswordForm();
				$subAction = 'askpassword';
        		//ГЕНЕРИРУЕМ ПАРОЛЬ (ЧТОБЫ ПРЕДЛОЖИТЬ К ИСПОЛЬЗОВАНИЮ)
        		$newPassword = strtolower(substr(md5(time() . $userInfo['name']), 0, 7));
				$info['newpassword'] = $newPassword;
				$info['hash'] = $hash;

				if (!empty($_POST))
				{
		            $model->attributes = $_POST['PasswordForm'];
		            if ($model->validate()) {
						//ПРИСВАИВАЕМ ПАРОЛЬ ПОЛЬЗОВАТЕЛЮ
		            	$attrs = $model->getAttributes();
						$this->identity->password = $attrs['pwd'];
						$newPassword = $this->identity->transformPassword($userInfo);
			        	$sql = 'UPDATE {{users}} SET pwd="' . $newPassword . '" WHERE id = ' . $userInfo['id'];
			        	$cmd = Yii::app()->db->createCommand($sql)->query();

	                    //АВТОМАТИЧЕСКАЯ АВТОРИЗАЦИЯ
	                    $this->identity->saveAuthInfo(CUser::model()->findByPk($userInfo['id'])->attributes);
			     		$this->redirect('/universe');
		            }
		            else
		            {
		            	$model->clearErrors();
						$model->addError('pwd', Yii::t('users', 'Password should consist of at least 5 characters'));
		            }
				}
        	}
		}
		else
		{
			//РАБОТА С ФОРМОЙ ЕМЭЙЛА
	        $model = new RegisterForm();
	        $model->setScenario('forget');
			if (!empty($_POST))
			{
				$subAction = 'post';
	            $model->attributes = $_POST['RegisterForm'];
	            if ($model->validate('forget')) {
	            	$attrs = $model->getAttributes();
					$model->email = $attrs['email'];
	            	$cmd = Yii::app()->db->createCommand()
	            		->select('*')
	            		->from('{{users}}')
	            		->where('email = :email');
	            	$cmd->bindParam(':email', $model->email, PDO::PARAM_STR);
	            	$userInfo = $cmd->queryRow();
	            	if (!empty($userInfo))
	            	{
						//ОТПРАВКА ПИСЬМА СО ССЫЛКОЙ НА СМЕНУ ПАРОЛЯ
						$hashLink = Yii::app()->params['tushkan']['siteURL'] . '/register/forget/' . $userInfo['sess_id'];
						$body = "Здравствуйте!\n\n"
						. "Если вы забыли ваш пароль, перейдите по следующей ссылке:\n\n"
						. "{$hashLink}\n\n"
						. "Если вы не запрашивали восстановление пароля, просто удалите это письмо.\n\n"
						. "С уважением, администрация ресурса " . Yii::app()->name;

						$ml = new SimpleMail();
						$ml->setFrom(Yii::app()->params['adminEmail']);
						$ml->setTo($model->email);
						$ml->setSubject(Yii::t('users', 'Forget password?'));
						$ml->setTextBody($body);
						$ml->send();

						$info['body'] = $body;
	            	}
	            	else
	            	{
		            	$subAction = '';
		            	$model->clearErrors();
						$model->addError('email', Yii::t('users', 'Email not found'));
	            	}
	            }
	            else
	            {
	            	$subAction = '';
	            }
			}
		}
        $this->render('forget', array('model' => $model, 'subAction' => $subAction, 'info' => $info));
    }

    /**
     * действие быстрой регистрации
     *
     */
	public function actionQuick()
	{
        $registered = false;
		if (!(Yii::app()->request->isAjaxRequest))
        $this->layout = '/layouts/start';
        $model = new RegisterForm();
        $model->setScenario('quick');
        if (isset($_POST['RegisterForm'])) {
            $model->attributes = $_POST['RegisterForm'];
            if ($model->validate('quick')) {
                $users = new CUser();
                $attrs = $model->getAttributes();
                unset($attrs['verifyCode']);
                $attrs['salt'] = substr(md5(time()), 0, 5); //СОЛЬ ГЕНЕРИРУЕМ ПРИ ДОБАВЛЕНИИ
                $attrs['created'] = date('Y-m-d H:i:s');
                $attrs['lastvisit'] = date('Y-m-d H:i:s');
                $attrs['server_id'] = 0;
                $attrs['sess_id'] = md5($attrs['salt']);//ПРИ РЕГИСТРАЦИИ НУЖЕН ЛЮБОЙ ХЭШ
                $attrs['active'] = 1;
                $attrs['confirmed'] = 0;
                $attrs['pwd'] = substr(md5($attrs['sess_id'] . time()), 0, 7);//ДЛЯ БЫСТРОЙ РЕГИСТРАЦИИ ГЕНЕРИРУЕМ ПАРОЛЬ

                foreach ($attrs as $k => $v) {
                    //if (property_exists($users, $k))
                    $users->{$k} = $v;
                }

                $users->free_limit = 0;
                $users->group_id = Yii::app()->params['tushkan']['userGroupId'];

                $this->identity->password = $users->pwd;
                $users->pwd = $this->identity->transformPassword($attrs); //ШИФРУЕМ ПАРОЛЬ ПО ПРАВИЛАМ АВТОРИЗАТОРА
                if ($users->save()) {
                    $userId = Yii::app()->db->getLastInsertID('{{users}}');
					$userInfo = CUser::model()->findByPk($userId)->attributes;
					$body = $this->sendConfirmMail($userInfo);
					$this->redirect('/register/confirm');
                }
            }
        }
        $this->render('quick', array('model' => $model));
	}

	/**
	 * Установка триал-тарифа
	 *
	 * @param mixed $userInfo
	 * @param mixed $trial
	 *
	 * @return boolean
	 */
	public function setTrialMode($userInfo, $trial = array())
	{
		if (empty($trial))
		{
            $trial = Yii::app()->db->createCommand()
            	->select('*')
            	->from('{{tariffs}}')
            	->where('id = ' . Yii::app()->params['tushkan']['trialTariffId'])
            	->queryRow();
		}

        // ВЫСТАВЛЯЕМ ТАРИФ ТРИАЛ
        if (!empty($trial))
        {
			$already = Yii::app()->db->createCommand()
				->select('user_id')
				->from('{{tariffs_users}}')
				->where('tariff_id = ' . $trial['id'] . ' AND user_id = ' . $userInfo['id'])
				->queryRow();
			if ($already)
				return true; //ТРИАЛ ТАРИФ УЖЕ ПОДКЛЮЧЕН

			$sql = 'INSERT INTO {{tariffs_users}} (user_id, tariff_id, switch_to) VALUES (' . $userInfo['id'] . ', ' . $trial['id'] . ', 0)';
			Yii::app()->db->createCommand($sql)->execute();

			//ПОДКЛЮЧАЕМ ПЕРИОДИЧЕСКУЮ УСЛУГУ
			$operationId = Yii::app()->params['tushkan']['abonentFeeId'];
			$paidBy = date('Y-m-d H:i:s', time() + Utils::parsePeriod($trial['period']));//ДЛЯ ТРИАЛА
			$sql = 'INSERT INTO {{user_subscribes}} (id, user_id, operation_id, period, paid_by, tariff_id, eof_period)
				VALUES (NULL, ' . $userInfo['id'] . ', ' . $operationId . ', "", "' . $paidBy . '", ' . $trial['id'] . ', "' . date('Y-m-d H:i:s', time() + Utils::parsePeriod($trial['period'])) . '")';
			Yii::app()->db->createCommand($sql)->execute();;

			//КОРРЕКТИРУЕМ ЛИМИТ ПП
			$sql = 'UPDATE {{users}} SET free_limit = ' . $trial['size_limit'] . ' WHERE id = ' . $userInfo['id'];
			Yii::app()->db->createCommand($sql)->execute();;

			$already = Yii::app()->db->createCommand()
				->select('user_id')
				->from('{{balance}}')
				->where('user_id = ' . $userInfo['id'])
				->queryRow();

//*
//ПОСЛЕ ОКОНЧАНИЯ ТЕСТИРОВАНИЯ БЛОК ЗАКОММЕНТИРОВАТЬ
			if (!$already)
			{
				$sum = 50;//ДЛЯ ТЕСТИРОВАНИЯ ВЫДАЕМ 50 монет
				$now = date('Y-m-d H:i:s');
				$hash = PaysController::createPaymentHash(array('user_id' => $this->userInfo['id'], 'date' => $now, 'summa' => $sum));
				$sql = 'INSERT INTO {{balance}} (user_id, balance, hash, modified) VALUES
				(' . $userInfo['id'] . ', ' . $sum . ', "' . $hash . '", "' . $now . '")';
				Yii::app()->db->createCommand($sql)->execute();
				return true;
			}
//*/
			return true;
        }
        return false;
	}

	public function sendConfirmMail($userInfo)
	{
		//ОТПРАВКА ПИСЬМА СО ССЫЛКОЙ НА ПОДТВЕРЖДЕНИЕ
		$headers="From: " . Yii::app()->params['adminEmail'] . "\r\nReply-To: " . $userInfo['email'];
		$hashLink = Yii::app()->params['tushkan']['siteURL'] . '/register/confirm/' . $userInfo['sess_id'];
		$body = "Здравствуйте!\n\n"
		. "Для подтверждения регистрации на сайте " . Yii::app()->name . ", пожалуйста, перейдите по следующей ссылке:\n\n"
		. "{$hashLink}\n\n"
		. "Если вы не регистрировались на данном ресурсе, просто удалите это письмо.\n\n"
		. "С уважением, администрация " . Yii::app()->name;

		$ml = new SimpleMail();
		$ml->setFrom(Yii::app()->params['adminEmail']);
		$ml->setTo($userInfo['email']);
		$ml->setSubject(Yii::t('users', 'Confirm registration'));
		$ml->setTextBody($body);
		$ml->send();

		return $body;
	}

    /**
     * действие регистрации пользователя (вывод формы и сохранение в БД
     *
     */
    public function actionIndex() {
        $this->breadcrumbs = array(
            Yii::t('common', 'Registration')
        );
        $registered = false;
        $model = new RegisterForm();
        if (isset($_POST['RegisterForm'])) {
            $model->attributes = $_POST['RegisterForm'];
            if ($model->validate()) {
                $users = new CUser();
                $attrs = $model->getAttributes();
                unset($attrs['verifyCode']);
                $attrs['salt'] = substr(md5(time()), 0, 5); //СОЛЬ ГЕНЕРИРУЕМ ПРИ ДОБАВЛЕНИИ
                $attrs['created'] = date('Y-m-d H:i:s');
                $attrs['lastvisit'] = date('Y-m-d H:i:s');
                $attrs['server_id'] = 0;
                $attrs['sess_id'] = md5($attrs['salt']);//ПРИ РЕГИСТРАЦИИ НУЖЕН ЛЮБОЙ ХЭШ
                $attrs['active'] = 1;
                $attrs['confirmed'] = 0;

                foreach ($attrs as $k => $v) {
                    //if (property_exists($users, $k))
                    $users->{$k} = $v;
                }

                $users->free_limit = 0;
                $users->group_id = Yii::app()->params['tushkan']['userGroupId'];
                $this->identity->password = $users->pwd;
                $users->pwd = $this->identity->transformPassword($attrs); //ШИФРУЕМ ПАРОЛЬ ПО ПРАВИЛАМ АВТОРИЗАТОРА
                if ($users->save()) {

                    Yii::app()->user->setFlash('success', Yii::t('users', 'User registered'));
                    $registered = true;

                    $userId = Yii::app()->db->getLastInsertID('{{users}}');
					$userInfo = CUser::model()->findByPk($userId)->attributes;
					$this->sendConfirmMail($userInfo);

					$this->redirect('/register/confirm');

                } else {
                    $model->addError('name', Yii::t('users', 'Unable to register user'));
                }
            }
        }
        $this->render('index', array('model' => $model, 'registered' => $registered));
    }

    /**
     * Displays the login page
     */
    public function actionLogin() {
		if (!(Yii::app()->request->isAjaxRequest))
    	$this->layout = '/layouts/start';
        $this->breadcrumbs = array(
            Yii::t('common', 'Login')
        );
        $model = new LoginForm();
        $model->identity = $this->identity; //ЧТОБЫ НЕ ПЛОДИЛИСЬ ЭКЗЕМПЛЯРЫ
        // if it is ajax validation request
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // collect user input data
        if (isset($_POST['LoginForm'])) {
            $model->attributes = $_POST['LoginForm'];
            // validate user input and redirect to the previous page if valid
            if ($model->validate() && $model->login()) {
                $this->redirect(Yii::app()->user->returnUrl);
            }
        }
        if (Yii::app()->user->id){
            $this->redirect('/');
        }
        $this->render('login', array('model' => $model));
    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout() {
        $this->identity->dropAuthInfo();
        $this->redirect(Yii::app()->homeUrl);
    }

    /**
     * действие выбора/смены тарифа
     *
     */
    public function actionTariff()
    {
    	$result = '';
    	$this->layout = '/layouts/ajax';
    	$userId = Yii::app()->user->id;
		if (!empty($userId) && !empty($_POST['tariff_id']))
		{
	    	$userPower = Yii::app()->user->getState('dmUserPower');
			$cmd = Yii::app()->db->createCommand()
				->select('*')
				->from('{{tariffs}}')
				->where('id = :id AND is_option=0 AND is_archive=0 AND active <= ' . $userPower);
			$cmd->bindParam(':id', $_POST['tariff_id'], PDO::PARAM_INT);
			$tariff = $cmd->queryRow();
			if (!empty($tariff))
			{
				$curTariffRelation = Yii::app()->db->createCommand()
					->select('t.id, t.price, tu.switch_to')
					->from('{{tariffs}} t')
					->where('t.is_option = 0')
					->join('{{tariffs_users}} tu', 't.id=tu.tariff_id AND tu.user_id = ' . $userId)
					->queryRow();
				if (!empty($curTariffRelation))
				{
					if (($tariff['id'] <> $curTariffRelation['id'])
						||
					 	(($tariff['id'] == $curTariffRelation['id']) && !empty($curTariffRelation['switch_to']))
					 	)
					{
						//ЗАПУСКАЕМ ПРОЦЕДУРУ ОТЛОЖЕННОГО ПЕРЕКЛЮЧЕНИЯ
						Yii::app()->user->setFlash('success', Yii::t('users', 'Request for change of tariff approved'));
						if ($tariff['id'] == $curTariffRelation['id'])
						{
							$tariff['id'] = 0; //ОТКАЗ ОТ СМЕНЫ ТАРИФА
							Yii::app()->user->setFlash('success', Yii::t('users', 'Request for change of tariff canceled'));
						}
						$sql = 'UPDATE {{tariffs_users}} SET switch_to = ' . $tariff['id'] . ' WHERE user_id = ' . $userId . ' AND tariff_id = ' . $curTariffRelation['id'];
						Yii::app()->db->createCommand($sql)->execute();
						$result = 'ok';
					}
					else
						Yii::app()->user->setFlash('error', Yii::t('users', 'Choose another tariff'));

					if ($tariff['id'] > 0)//ЕСЛИ ПОЛЬЗОВАТЕЛЬ НЕ ОТКАЗАЛСЯ ОТ ПЕРЕКЛЮЧЕНИЯ
					{
						//ПРОВЕРЯЕМ ВОЗМОЖНОСТЬ НЕМЕДЛЕННОГО ПОДКЛЮЧЕНИЯ НОВОГО
						$subsInfo = Yii::app()->db->createCommand()
							->select('*')
							->from('{{user_subscribes}}')
							->where('user_id = ' . $this->userInfo['id'] . ' AND tariff_id = ' . $curTariffRelation['id'])
							->queryRow();
						if (!empty($subsInfo) &&
							(
								((strtotime($subsInfo["paid_by"]) - time()) < 3600*24)
								||
								($curTariffRelation['price'] == 0)
							)
						) {
							//ЕСЛИ ДО КОНЦА ОПЛАТЫ ТЕКУЩЕГО ПЕРИОДА МЕНЬЩЕ СУТОК
							//ИЛИ ОПЛАТА ПРОСРОЧЕНА
							//ИЛИ ТАРИФ БЕСПЛАТНЫЙ - НЕМЕДЛЕННОЕ ПЕРЕКЛЮЧЕНИЕ ВОЗМОЖНО
							//ВЫЧИСЛЯЕМ РАЗНИЦУ СТОИМОСТИ СТАРОГО И НОВОГО ТАРИФА ЗА СУТКИ
							$oldTariff = Yii::app()->db->createCommand()
								->select('*')
								->from('{{tariffs}}')
								->where('id = ' . $curTariffRelation['id'])
								->queryRow();

							$oldCost = $oldTariff['price'] / Utils::parsePeriod($oldTariff['period']) * 3600 * 24;
							$newCost = $tariff['price'] / Utils::parsePeriod($tariff['period']) * 3600 * 24;
							$canSwitch = true;

							if ($newCost > $oldCost)
							{
								//СПИСЫВАЕМ РАЗНИЦУ СТОИМОСТИ ТАРИФОВ ЗА ТЕКУЩИЕ СУТКИ
								$balanceInfo = Yii::app()->db->createCommand()
									->select('*')
									->from('{{balance}}')
									->where('user_id = ' . $this->userInfo['id'])
									->queryRow();
								$cost = $newCost - $oldCost;
								if ($balanceInfo['balance'] > $cost)
								{
									$now = date('Y-m-d H:i:s');
									$hash = PaysController::createPaymentHash(array('user_id' => $this->userInfo['id'], 'date' => $now, 'summa' => $balanceInfo['balance'] - $cost));
									$sql = 'UPDATE {{balance}} SET balance = balance - ' . $cost . ', hash = "' . $hash . '" WHERE user_id = ' . $this->userInfo['id'];
									Yii::app()->db->createCommand($sql)->execute();
									//ФИКСИРУЕМ СПИСАНИЕ ПО РАЗНОСТИ СТОИМОСТИ
									$hash = PaysController::createPaymentHash(array('user_id' => $this->userInfo['id'], 'date' => $now, 'summa' => $cost));
									$sql = '
										INSERT INTO {{debits}}
											(id, user_id, created, operation_id, order_id, summa, hash)
										VALUES
											(null, ' . $this->userInfo['id'] . ', "' . $now . '", ' . $subsInfo['operation_id'] . ', 0, ' . $cost . ', "' . $hash . '")
									';
									Yii::app()->db->createCommand($sql)->execute();
								}
								else
									$canSwitch = false;
							}

							if ($canSwitch)
							{
								//ОБНОВЛЯЕМ СВЯЗЬ ПОЛЬЗОВАТЕЛЬ-ТАРИФ
								$sql = 'UPDATE {{tariffs_users}}
									SET switch_to=0, tariff_id=' . $tariff['id'] . '
									WHERE user_id=' . $this->userInfo['id'] . ' AND tariff_id=' . $curTariffRelation['id'];
								Yii::app()->db->createCommand($sql)->execute();

								//КОРРЕКТИРУЕМ ОБЪЕМ СВОБОДНОГО МЕСТА ПП
								$userInfo = Yii::app()->db->createCommand()
									->select('free_limit')
									->from('{{users}}')
									->where('id = ' . $this->userInfo['id'])
									->queryRow();
								$freeLimit = $tariff['size_limit'] - ($oldTariff['size_limit'] - $userInfo['free_limit']);
								if ($freeLimit < 0) $freeLimit = 0;
								$sql = 'UPDATE {{users}} SET free_limit=' . $freeLimit . ' WHERE id=' . $this->userInfo['id'];
								Yii::app()->db->createCommand($sql)->execute();

								//ОБНОВЛЯЕМ ИНФ О ПЕРИОДИЧЕСКОЙ УСЛУГЕ
								$eofSql = ', eof_period = "' . date("Y-m-d H:i:s", strtotime($subsInfo['paid_by']) + Utils::parsePeriod($tariff['period'])) . '"';
								$sql = 'UPDATE {{user_subscribes}}
									SET period = "' . $tariff['period'] . '"' . $eofSql . ', tariff_id = ' . $tariff['id'] . ' WHERE id = ' . $subsInfo['id'];
								Yii::app()->db->createCommand($sql)->execute();

								//ОЧИЩАЕМ ИНФУ О БАНАХ (НА ВСЯКИЙ СЛУЧАЙ)
								$sql = 'DELETE FROM {{bannedusers}} WHERE user_id = ' . $this->userInfo['id'] . ' AND reason = ' . _BANREASON_ABONENTFEE_;
								Yii::app()->db->createCommand($sql)->execute();
								$bansInfo = Yii::app()->db->createCommand()
									->select('*')
									->from('{{bannedusers}}')
									->where('user_id = ' . $this->userInfo['id'])
									->order('state DESC')
									->queryAll();
								Yii::app()->user->setState('dmUserBans', $bansInfo);

								$result = 'ok';
							}
						}
					}
				}
				else
				{
					//ПРОПИСЫВАЕМ СВЯЗЬ С ТАРИФОМ
					$sql = 'INSERT INTO {{tariffs_users}} (user_id, tariff_id, switch_to) VALUES (' . $userId . ', ' . $tariff['id'] . ', 0)';
					Yii::app()->db->createCommand($sql)->execute();;

					//ПОДКЛЮЧАЕМ ПЕРИОДИЧЕСКУЮ УСЛУГУ
					$operationId = Yii::app()->params['tushkan']['abonentFeeId'];
					//$paidBy = date('Y-m-d H:i:s', time() + Utils::parsePeriod($tariff['period']));//ДЛЯ ТРИАЛА
					$paidBy = date('Y-m-d H:i:s');
					$sql = 'INSERT INTO {{user_subscribes}} (id, user_id, operation_id, period, paid_by, tariff_id, eof_period)
						VALUES (NULL, ' . $userId . ', ' . $operationId . ', "' . $tariff['period'] . '", "' . $paidBy . '", ' . $tariff['id'] . ', "' . date('Y-m-d H:i:s', time() + Utils::parsePeriod($tariff['period'])) . '")';
					Yii::app()->db->createCommand($sql)->execute();;
					$result = 'ok';
				}
			}
			else
			{
				Yii::app()->user->setFlash('error', Yii::t('users', 'The tariff is not available'));
			}
		}
		$this->render('tariff', array('result' => $result));
    }

    /**
     * персональные данные пользователя
     *
     */
	public function actionPersonal()
	{
    	$userId = $this->userInfo['id'];
    	$info = array(); $ajaxResult = '';
    	if (!empty($userId))
    	{
	    	$userPower = Yii::app()->user->getState('dmUserPower');
    		$info = Yii::app()->db->createCommand()
    			->select('*')
    			->from('{{users}}')
    			->where('id = ' . $userId)
    			->queryRow();

    		if (!empty($info))
    		{
    			$personalParams = Yii::app()->db->createCommand()
    				->select('p.id AS pid, p.title, p.tp, p.required, p.parent_id, v.id AS vid, v.text_value, v.textarea_value, v.int_value')
    				->from('{{personaldata_params}} p')
    				->leftJoin('{{personaldata_values}} v', 'v.param_id = p.id AND v.user_id = ' . $userId)
    				->where('p.active <= ' . $userPower)
    				->group('p.id')
    				->order('p.parent_id ASC, p.srt DESC')
    				->queryAll();
    			$info['personalParams'] = $personalParams;
	    		if (!empty($_POST['action']))
	    		{
	    			//$ajaxResult = Yii::t('common', 'Request cannot be processed');
	    			$ajaxResult = '';
    				switch ($_POST['action'])
	    			{
	    				case "name":
	    					$sql = 'UPDATE {{users}} SET name = :name WHERE id = ' . $userId;
	    					$cmd = Yii::app()->db->createCommand($sql);
	    					$cmd->bindParam(':name', $_POST['value'], PDO::PARAM_STR);
	    					if ($cmd->execute())
	    					{
	    						Yii::app()->user->setName($_POST['value']);
	    						$ajaxResult = 'ok';
	    					}
	    				break;
	    				case "email":
	    					$validator = new CEmailValidator();
							if ($validator->validateValue($_POST['value']))
							{
								$cmd = Yii::app()->db->createCommand()
									->select('id')
									->from('{{users}}')
									->where('email = :email AND id <> ' . $userId)
									->limit(1);
								$cmd->bindParam(':email', $attrs['email'], PDO::PARAM_STR);
								$result = $cmd->queryRow();

								if (empty($result))
								{
			    					$sql = 'UPDATE {{users}} SET email = :email WHERE id = ' . $userId;
			    					$cmd = Yii::app()->db->createCommand($sql);
			    					$cmd->bindParam(':email', $_POST['value'], PDO::PARAM_STR);
			    					if ($cmd->execute())
			    					{
			    						$ajaxResult = 'ok';
			    					}
								}
								else
								{
					    			$ajaxResult = Yii::t('users', 'Email exists');
								}
							}
							else
							{
				    			$ajaxResult = Yii::t('users', 'Invalid email');
							}
	    				break;

	    				case "pwd":
							if (!empty($_POST['value2']))
							{
								$this->identity->password = $_POST['value2'];
								$oldPassword = $this->identity->transformPassword($info);
								if ($oldPassword == $info['pwd'])
								{
									$this->identity->password = $_POST['value'];
									$newPassword = $this->identity->transformPassword($info);
					        		$sql = 'UPDATE {{users}} SET pwd="' . $newPassword . '" WHERE id = ' . $userId;
					        		$cmd = Yii::app()->db->createCommand($sql);
			    					if ($cmd->execute())
			    					{
			    						$info['pwd'] = $newPassword;
			    						$this->identity->saveAuthInfo($info);
			    						$ajaxResult = 'ok';
			    						break;
			    					}
								}
	    					}
			    			$ajaxResult = Yii::t('users', 'Invalid old password');
	    				break;

	    				default:
	    					if (substr($_POST['action'], 0, 5) == 'param')
	    					{
//СОХРАНЕНИЕ ПЕРСОНАЛЬНЫХ ДАННЫХ
	    						$pid = intval(substr($_POST['action'], 6));
	    						//ИДЕНТИФИКАТОР ПРИХОДИТ В ИМЕНИ action в виде "param_[pid]" (c 6го символа)
	    						if ($pid > 0)
	    						{
	    							$paramInfo = Yii::app()->db->createCommand()
	    								->select('tp')
	    								->from('{{personaldata_params}}')
	    								->where('id = ' . $pid . ' AND active <= ' . $userPower)
	    								->queryRow();
	    						}

	    						if (!empty($paramInfo))
	    						{
	    							switch ($paramInfo['tp'])
	    							{
	    								case "text":
	    								case "file":
	    								case "password":
											$fieldName = 'text_value';
											$insertValues = ', :value, "", 0';
	    								break;
	    								case "textarea":
											$fieldName = 'textarea_value';
											$insertValues = ', "", :value, 0';
	    								break;
	    								case "checkbox":
	    								case "radio":
											$fieldName = 'int_value';
											$insertValues = ', "", "", :value';
	    								break;
	    							}
	    							$valueInfo = Yii::app()->db->createCommand()
	    								->select('id')
	    								->from('{{personaldata_values}}')
	    								->where('param_id = ' . $pid . ' AND user_id = ' . $userId)
	    								->queryRow();
	    							if (empty($valueInfo))
	    							{
						        		$sql = 'INSERT INTO {{personaldata_values}} (id, user_id, param_id, text_value, textarea_value, int_value)
						        			VALUES (null, ' . $userId . ', ' . $pid . $insertValues . ')
						        		';
						        		$cmd = Yii::app()->db->createCommand($sql);
						        		if ($fieldName == 'int_value')
						        			$cmd->bindParam(':value', $_POST['value'], PDO::PARAM_INT);
						        		else
						        			$cmd->bindParam(':value', $_POST['value'], PDO::PARAM_STR);
				    					if ($cmd->execute())
				    					{
				    						$ajaxResult = 'ok';
				    					}
	    							}
	    							else
	    							{

						        		$sql = 'UPDATE {{personaldata_values}} SET ' . $fieldName . '= :value WHERE param_id = ' . $pid . ' AND user_id = ' . $userId;
						        		$cmd = Yii::app()->db->createCommand($sql);
						        		if ($fieldName == 'int_value')
						        			$cmd->bindParam(':value', $_POST['value'], PDO::PARAM_INT);
						        		else
						        			$cmd->bindParam(':value', $_POST['value'], PDO::PARAM_STR);
				    					if ($cmd->execute())
				    					{
				    						$ajaxResult = 'ok';
				    					}
	    							}
	    						}
	    					}
	    			}
	    		}
    		}
    	}
		$this->render('personal', array('info' => $info, 'ajaxResult' => $ajaxResult));
	}

    /**
     * информация профиля пользователя
     *
     */
    public function actionProfile()
    {
    	$userId = Yii::app()->user->id;
    	$info = $balance = $subscribes = $tariffs = $tariff = $newTariff = array();
    	if (!empty($userId))
    	{
	    	$userPower = Yii::app()->user->getState('dmUserPower');
    		$info = Yii::app()->db->createCommand()
    			->select('*')
    			->from('{{users}}')
    			->where('id = ' . $userId)
    			->queryRow();
			$balance = Yii::app()->db->createCommand()
				->select('*')
				->from('{{balance}}')
				->where('user_id = ' . $userId)
				->queryRow();
			$tariff = Yii::app()->db->createCommand()
				->select('t.id AS tid, t.title, t.price, t.size_limit, t.period, tu.switch_to')
				->from('{{tariffs}} t')
				->where('t.is_option = 0')
				->join('{{tariffs_users}} tu', 't.id=tu.tariff_id AND tu.user_id = ' . $userId)
				->queryRow();
			if (empty($tariff['switch_to']))
				$tid = $tariff['tid'];
			else
				$tid = $tariff['switch_to'];

			$tariffs = Yii::app()->db->createCommand()
				->select('*')
				->from('{{tariffs}}')
				->where('id <> ' . $tid . ' AND active <= ' . $userPower . ' AND is_archive=0 AND is_option=0')
				->queryAll();
			$subscribes = Yii::app()->db->createCommand()
				->select('us.paid_by, us.period, bo.title AS botitle, t.title AS ttitle, t.price')
				->from('{{user_subscribes}} us')
				->join('{{balanceoperations}} bo', 'bo.id=us.operation_id')
				->leftJoin('{{tariffs}} t', 't.id=us.tariff_id')
				->where('us.user_id = ' . $userId)
				->queryAll();
			if (!empty($tariff['switch_to']))
			{
				$newTariff = Yii::app()->db->createCommand()
				->select('*')
				->from('{{tariffs}}')
				->where('id = ' . $tariff['switch_to'] . ' AND active <= ' . $userPower)
				->queryRow();
			}
    	}
        $this->render('profile', array(
        	'info' => $info,
        	'balance' => $balance,
        	'subscribes' => $subscribes,
        	'tariff' => $tariff,
        	'tariffs' => $tariffs,
        	'newTariff' => $newTariff));
    }

    public function actionTariffs()
    {
    	$userPower = intval(Yii::app()->user->getState('dmUserPower'));
		$lst = Yii::app()->db->createCommand()
			->select('*')
			->from('{{tariffs}}')
			->where('active <= ' . $userPower)
			->queryAll();
		$this->render('tariffs', array('lst' => $lst));
    }

	/**
	 * Обратная связь
	 */
	public function actionFeedback()
	{
		$model=new FeedbackForm;
		if(isset($_POST['FeedbackForm']))
		{
			$model->attributes=$_POST['FeedbackForm'];
			if($model->validate())
			{
				$ml = new SimpleMail();
				$ml->setFrom(Yii::app()->params['adminEmail']);
				$ml->setTo($model->email);
				$ml->setSubject($model->subject);
				$ml->setTextBody($model->body);
				$ml->send();

				Yii::app()->user->setFlash('contact', Yii::t('users', 'Thank you for contacting us. We will respond to you as soon as possible.'));
				$this->refresh();
			}
		}
		$this->render('feedback',array('model'=>$model));
	}
}