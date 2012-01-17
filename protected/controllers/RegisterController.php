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
        $this->layout='start';
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
                $attrs['sess_id'] = 0;
                $attrs['active'] = 1;

                foreach ($attrs as $k => $v) {
                    //if (property_exists($users, $k))
                    $users->{$k} = $v;
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
        $this->layout='start';
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

}