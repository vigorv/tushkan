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

    public function actionForget() {

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