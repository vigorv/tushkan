<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentityApp extends CUserIdentity {

    public $email;
    public $password;
    private $_id = 0;

    /**
     * Authenticates a user.
     * The example implementation makes sure if the username and password
     * are both 'demo'.
     * In practical applications, this should be changed to authenticate
     * against some persistent user identity storage (e.g. database).
     * @return boolean whether authentication succeeds.
     */

    public function __construct($email,$password)
    {
        $this->email=$email;
        $this->password=$password;
    }


    public function authenticate() {

        $record = CUser::model()->findByAttributes(array('email' => $this->email));

        if ($record === null)
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        else if ($record->pwd !== md5($this->password . $record['salt']))
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        else {
            $this->errorCode = self::ERROR_NONE;
            $this->_id = $record->id;

            $ukey= CUser::UKey($record);
            Yii::app()->user->setState('ukey',$ukey);
            Yii::app()->user->userGroupId = (int) $record->group_id;

            $userPower = Yii::app()->db->cache(10)->createCommand()
                    ->select('power')
                    ->from('{{user_groups}}')
                    ->where('id = :id',array(':id'=>$record->group_id))
                    ->queryScalar();

            Yii::app()->user->userPower = $userPower;
            $this->email = $record->email;
        }
        return !$this->errorCode;
    }

    public function getId() {
        return $this->_id;
    }

}