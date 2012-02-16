<?php
/**
 * модель формы регистрации пользователя
 *
 */
class RegisterForm extends CFormModel
{
	public $name;
	public $email;
	public $pwd;
	public $verifyCode;

	public function rules()
	{
		return array(
			array('name', 'safe'),
		    array('name', 'length', 'min' => 3, 'message'=>'Имя должно состоять миниммум из трех символов.'),

			array('pwd', 'required'),
		    array('pwd', 'length', 'min' => 5),
			array('verifyCode', 'captcha', 'allowEmpty' => !CCaptcha::checkRequirements()),
			array('email', 'required'),
			array('email', 'email'),
			array('email', 'required', 'on' => 'forget'),
			array('email', 'email', 'on' => 'forget'),
			array('email', 'required', 'on' => 'quick'),
			array('email', 'email', 'on' => 'quick'),
			array('email', 'required', 'on' => 'confirm'),
			array('email', 'email', 'on' => 'confirm'),
		);
	}

	public function afterValidate()
	{
		if (($this->scenario == 'forget') || ($this->scenario == 'confirm') || ($this->scenario == 'quick'))
		{
			if (!$this->hasErrors('email'))
				$this->clearErrors();
		}

		if (empty($this->scenario) || ($this->scenario == 'quick'))
		$attrs = $this->getAttributes();
		if (!empty($attrs['email']))
		{
			$cmd = Yii::app()->db->createCommand()
				->select('id')
				->from('{{users}}')
				->where('email = :email')
				->limit(1);
			$cmd->bindParam(':email', $attrs['email'], PDO::PARAM_STR);
			$result = $cmd->queryRow();
			if (!empty($result))
			{
				$this->addError('email', Yii::t('users', 'Email exists'));
			}
		}
		else
		{
			$this->addError('email', Yii::t('users', 'Email could not be empty'));
		}
	}
}