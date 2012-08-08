<?php

Yii::import('ext.classes.SimpleMail');

class SLFormResetPassword extends CFormModel {

    public $email;
    public $password;

    private $_identity;


    public function rules() {
        return array(
            array('email', 'email'),
            array('email', 'userNotExists'),
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

    public function userNotExists($attribute, $params) {
        switch ($attribute) {
            case 'email':
                $email_exist = CUser::model()->count('email ="' . $this->email . '"');
                if (!$email_exist)
                    $this->addError('email', Yii::t('user','User with this address not registered.'));
                break;
        }
    }

    public function resetPassword(){

        if ($this->_identity === null) {
            $user = CUser::model()->findByAttributes(array('email'=>$this->email));
            if ($user){
                $magic_key = CUser::createMagicKeyForUser($user['id']);
                $this->sendConfirmMail($user,$magic_key);
                if ($magic_key)
                    return true;
            }
        }
        return false;
    }

    public function sendConfirmMail($user,$magic_key) {
        //ОТПРАВКА ПИСЬМА СО ССЫЛКОЙ НА ПОДТВЕРЖДЕНИЕ
        $headers = "From: " . Yii::app()->params['adminEmail'] . "\r\nReply-To: " . $user['email'];
        $hashLink = Yii::app()->getBaseUrl(true) . '/app/resetpassword?hash=' .$magic_key.'&user_id='.$user['id'];
        $body = "Здравствуйте!\n\n"
                . "Вы воспользовались восстановлением пароля на сайте " . Yii::app()->name . ", пожалуйста, перейдите по следующей ссылке:\n\n"
                . "{$hashLink}\n\n"
                . "Если вы не хотите менять пароль на данном ресурсе, просто удалите это письмо.\n\n"
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
