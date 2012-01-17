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
			array('name', 'required'),
		    array('name', 'length', 'min' => 3),
			array('email', 'required'),
			array('email', 'email'),
			array('email', 'required', 'on' => 'forget'),
			array('email', 'email', 'on' => 'forget'),
			array('pwd', 'required'),
			array('verifyCode', 'captcha', 'allowEmpty' => !CCaptcha::checkRequirements()),
		);
	}

	public function afterValidate()
	{
		if ($this->scenario == 'forget')
		{
			if (!$this->hasErrors('email'))
				$this->clearErrors();
			return;
		}
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