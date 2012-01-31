<?php

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

    }

    public function actionForget($hash = '') {
    	$subAction = ''; $info = array();
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
					$headers="From: " . Yii::app()->params['adminEmail'] . "\r\nReply-To: " . $model->email;
					$hashLink = Yii::app()->params['tushkan']['siteURL'] . '/register/forget/' . $userInfo['sess_id'];
					$body = "Здравствуйте, {$userInfo['name']}!\n\n
					Если вы забыли ваш пароль, перейдите по следующей ссылке:\n\n
					{$hashLink}\n\n
					Если вы не запрашивали восстановление пароля, просто удалите это письмо.\n\n
					С уважением, администрация ресурса " . Yii::app()->name;

					//mail($model->email, Yii::t('users', 'Forget password?'), $body, $headers);

					$info['body'] = $body;
            	}
            }
            else
            {
            	$subAction = '';
            	$model->clearErrors();
				$model->addError('email', Yii::t('users', 'Invalid Email'));
            }
		}
		if (!empty($hash))
		{
			$subAction = 'newpassword';
        	$cmd = Yii::app()->db->createCommand()
        		->select('*')
        		->from('{{users}}')
        		->where('sess_id = :sess_id');
        	$cmd->bindParam(':sess_id', $hash, PDO::PARAM_STR);
        	$userInfo = $cmd->queryRow();

        	if (empty($userInfo))
        	{
				$info['error'] = 1;
        	}
        	else
        	{
        		$newPassword = strtolower(substr(md5(time() . $userInfo['name']), 0, 5));

				//ОТПРАВКА ПИСЬМА СО ССЫЛКОЙ НА СМЕНУ ПАРОЛЯ
				$headers="From: " . Yii::app()->params['adminEmail'] . "\r\nReply-To: " . $model->email;
				$body = "Здравствуйте, {$userInfo['name']}!\n\n
				Ваш новый пароль:\n\n
				{$newPassword}\n\n
				С уважением, администрация ресурса " . Yii::app()->name;

				$this->identity->password = $newPassword;
				$newPassword = $this->identity->transformPassword($userInfo);
		        $sql = 'UPDATE {{users}} SET pwd="' . $newPassword . '" WHERE id = ' . $userInfo['id'];
		        $cmd = Yii::app()->db->createCommand($sql)->query();

				//mail($model->email, Yii::t('users', 'Forget password?'), $body, $headers);
				$info['body'] = $body;
        	}
		}
        $this->render('forget', array('model' => $model, 'subAction' => $subAction, 'info' => $info));
    }

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

                foreach ($attrs as $k => $v) {
                    //if (property_exists($users, $k))
                    $users->{$k} = $v;
                }

                $users->free_limit = 0;
                $users->group_id = Yii::app()->params['tushkan']['userGroupId'];
                $trial = Yii::app()->db->createCommand()
                	->select('*')
                	->from('{{tariffs}}')
                	->where('id = ' . Yii::app()->params['tushkan']['trialTariffId'])
                	->queryRow();

				if (!empty($trial))
				{
                	$users->free_limit = $trial['size_limit'];
				}
                $this->identity->password = $users->pwd;
                $users->pwd = $this->identity->transformPassword($attrs); //ШИФРУЕМ ПАРОЛЬ ПО ПРАВИЛАМ АВТОРИЗАТОРА
                if ($users->save()) {
                    /*
                      //ОТПРАВКА ПИСЬМА СО ССЫЛКОЙ НА ПОДТВЕРЖДЕНИЕ
                      $headers="From: {$model->email}\r\nReply-To: " .Yii::app()->params['adminEmail'];
                      mail($model->email, $model->subject, $body, $headers);
                     */
                    Yii::app()->user->setFlash('success', Yii::t('users', 'User registered'));
                    $registered = true;

                    $userId = Yii::app()->db->getLastInsertID('{{users}}');

                    //АВТОМАТИЧЕСКИ ВЫСТАВЛЯЕМ ТАРИФ ТРИАЛ
                    if (!empty($trial))
                    {
						$sql = 'INSERT INTO {{tariffs_users}} (user_id, tariff_id, switch_to) VALUES (' . $userId . ', ' . $trial['id'] . ', 0)';
						Yii::app()->db->createCommand($sql)->execute();;

						//ПОДКЛЮЧАЕМ ПЕРИОДИЧЕСКУЮ УСЛУГУ
						$operationId = Yii::app()->params['tushkan']['abonentFeeId'];
						$paidBy = date('Y-m-d H:i:s', time() + Utils::parsePeriod($trial['period']));//ДЛЯ ТРИАЛА
						$sql = 'INSERT INTO {{user_subscribes}} (id, user_id, operation_id, period, paid_by, tariff_id)
							VALUES (NULL, ' . $userId . ', ' . $operationId . ', "", "' . $paidBy . '", ' . $trial['id'] . ')';
						Yii::app()->db->createCommand($sql)->execute();;
                    }

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
				->where('id = :id AND is_archive=0 AND active <= ' . $userPower);
			$cmd->bindParam(':id', $_POST['tariff_id'], PDO::PARAM_INT);
			$tariff = $cmd->queryRow();
			if (!empty($tariff))
			{
				$curTariffRelation = Yii::app()->db->createCommand()
					->select('t.id, tu.switch_to')
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
					$sql = 'INSERT INTO {{user_subscribes}} (id, user_id, operation_id, period, paid_by, tariff_id)
						VALUES (NULL, ' . $userId . ', ' . $operationId . ', "' . $tariff['period'] . '", "' . $paidBy . '", ' . $tariff['id'] . ')';
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
    	$userId = Yii::app()->user->id;
    	$info = array(); $ajaxResult = '';
    	if (!empty($userId))
    	{
	    	$userPower = Yii::app()->user->getState('dmUserPower');
    		$info = Yii::app()->db->createCommand()
    			->select('*')
    			->from('{{users}}')
    			->where('id = ' . $userId)
    			->queryRow();

    		if (!empty($_POST['action']))
    		{
    			$ajaxResult = Yii::t('common', 'Request cannot be processed');
    			if (!empty($_POST['value']))
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
				->select('t.title, t.price, t.size_limit, t.period, tu.switch_to')
				->from('{{tariffs}} t')
				->where('t.is_option = 0')
				->join('{{tariffs_users}} tu', 't.id=tu.tariff_id AND tu.user_id = ' . $userId)
				->queryRow();
			$tariffs = Yii::app()->db->createCommand()
				->select('*')
				->from('{{tariffs}}')
				->where('active <= ' . $userPower . ' AND is_archive=0')
				->queryAll();
			$subscribes = Yii::app()->db->createCommand()
				->select('us.paid_by, us.period, bo.title AS botitle, t.title AS ttitle')
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
				$headers="From: {$model->email}\r\nReply-To: {$model->email}";
				mail(Yii::app()->params['adminEmail'],$model->subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact', Yii::t('users', 'Thank you for contacting us. We will respond to you as soon as possible.'));
				$this->refresh();
			}
		}
		$this->render('feedback',array('model'=>$model));
	}
}