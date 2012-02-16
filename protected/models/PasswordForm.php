<?php
/**
 * модель формы типа продукта
 *
 */
class PasswordForm extends CFormModel
{
	public $pwd;
	public $rememberMe;

	public function rules()
	{
		return array(
			array('pwd', 'required'),
		    array('pwd', 'length', 'min' => 5),
			array('rememberMe', 'boolean'),
		);
	}
}