<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class LoginForm extends CFormModel
{
	public $email;
	public $password;
	public $rememberMe;

	public $identity;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			// username and password are required
			array('email, password', 'required'),
			array('email', 'email'),
			// rememberMe needs to be a boolean
			array('rememberMe', 'boolean'),
			// password needs to be authenticated
			array('password', 'authenticate'),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'rememberMe'=>'Remember me next time',
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate($attribute,$params)
	{
		if(!$this->hasErrors())
		{
			if($this->identity === null)
			{
				$this->identity=new UserIdentity($this->email,$this->password);
			}

			$this->identity->email = $this->email;
			$this->identity->password = $this->password;
			$this->identity->rememberMe = $this->rememberMe;
			if(!$this->identity->authenticate())
			{
				$this->addError('password','Incorrect email or password.');
				$this->addError('email','Incorrect email or password.');
			}
		}
	}

	/**
	 * Logs in the user using the given username and password in the model.
	 * @return boolean whether login is successful
	 */
	public function login()
	{
		if($this->identity === null)
		{
			$this->identity=new UserIdentity($this->email,$this->password);
		}
		$this->identity->email = $this->email;
		$this->identity->password = $this->password;
		$this->identity->rememberMe = $this->rememberMe;
		$this->identity->authenticate();

		if($this->identity->errorCode===UserIdentity::ERROR_NONE)
			return true;

		return false;
	}
}
