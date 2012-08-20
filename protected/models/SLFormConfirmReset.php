<?php

Yii::import('ext.classes.SimpleMail');

class SLFormConfirmReset extends CFormModel {

    public $email;
    public $id;
    public $password;
    public $verifyCode;
    private $_identity;


    public function rules() {
        return array(
            array('password', 'ext.validators.EPasswordStrength', 'min' => 5),
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

    public function setPassword(){
        if ($this->_identity === null) {
            $user = CUser::model()->findByAttributes(array('id'=>$this->id));
            if ($user){
                $user->pwd = $this->password;
                if ($user->save()){
                    $magic_key = CUser::deleteMagicKeyForUser($user['id']);
                    $this->sendConfirmMail($user,$magic_key);
                    return true;

                }
            }
        }
        return false;
    }

    public function sendConfirmMail($user) {
        //ОТПРАВКА ПИСЬМА СО ССЫЛКОЙ НА ПОДТВЕРЖДЕНИЕ
        //$headers = "From: " . Yii::app()->params['adminEmail'] . "\r\nReply-To: " . $user['email'];

        $body = "Здравствуйте!\n\n"
            . "Вы поменяли пароль на сайте " . Yii::app()->name . ". \n\n";

        $ml = new SimpleMail();
        $ml->setFrom(Yii::app()->params['adminEmail']);
        $ml->setTo($user['email']);
        $ml->setSubject(Yii::t('user', 'Confirm registration'));
        $ml->setTextBody($body);
        $ml->send();

        return $body;
    }

}
