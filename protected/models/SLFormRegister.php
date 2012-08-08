<?php

Yii::import('ext.classes.SimpleMail');

class SLFormRegister extends CFormModel {

    public $email;
    public $password;
    private $_identity;
    public $verifyCode;


    public function rules() {
        return array(
            array('password', 'ext.validators.EPasswordStrength', 'min' => 5),
            array('email', 'email'),
            array('email', 'userExists'),
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels() {
        return array(
            'password' => Yii::t('user', 'Password'),
            'email' => Yii::t('user', 'email'),
        );
    }

    public function userExists($attribute, $params) {
        //  echo $attribute;
        //$this->email = $p->purify($this->email);
        //t$his->username = $p->purify($this->username);
        switch ($attribute) {
            case 'email':
                $email_exist = CUser::model()->count('email ="' . $this->email . '"');
                if ($email_exist)
                    $this->addError('email', Yii::t('user','User with this address already registred.'));
                break;
        }
    }

    public function register() {
        if ($this->_identity === null) {
            //Данные уже свалидированы
            $user = new CUser('add');
            $user->email = $this->email;
            $salt = 'mb'.rand(0,9);
            $user->pwd = md5($this->password . $salt);
            $user->salt = $salt;
            //$user->last_ip = Yii::app()->request->getUserHostAddress();
            if ($user->save()) {
                $this->sendConfirmMail($user);
                return true;
            }
        }
        return false;
    }

    public function sendConfirmMail($user) {
        //ОТПРАВКА ПИСЬМА СО ССЫЛКОЙ НА ПОДТВЕРЖДЕНИЕ
        $headers = "From: " . Yii::app()->params['adminEmail'] . "\r\nReply-To: " . $user['email'];
        $hashLink = Yii::app()->getBaseUrl(true) . '/app/confirm?hash=' . CUser::makeResetHash($user['pwd'], $user['salt']).'&user_id='.$user['id'];
        $body = "Здравствуйте!\n\n"
                . "Для подтверждения регистрации на сайте " . Yii::app()->name . ", пожалуйста, перейдите по следующей ссылке:\n\n"
                . "{$hashLink}\n\n"
                . "Если вы не регистрировались на данном ресурсе, просто удалите это письмо.\n\n"
                . "С уважением, администрация " . Yii::app()->name;

        $ml = new SimpleMail();
        $ml->setFrom(Yii::app()->params['adminEmail']);
        $ml->setTo($user['email']);
        $ml->setSubject(Yii::t('user', 'Confirm registration'));
        $ml->setTextBody($body);
        $ml->send();

        return $body;
    }

}
