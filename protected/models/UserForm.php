<?php
/**
 * модель админской формы пользователя
 *
 */
class UserForm extends CFormModel
{
	public $name;
	public $email;
	public $active;
	public $pwd;
	public $group_id;
	public $created;
	public $lastvisit;
	public $salt;
	public $server_id;
	public $sess_id;
	public $id;

	public function rules()
	{
		return array(
			array('name', 'required'),
		    array('name', 'length', 'min' => 3),
			array('email', 'email'),
			array('pwd', 'required'),
			array('group_id', 'required'),
			array('active', 'numerical'),
			array('created', 'safe'),
			array('lastvisit', 'safe'),
			array('salt', 'safe'),
			array('server_id', 'safe'),
			array('sess_id', 'safe'),
			array('id', 'safe'),
		);
	}

	public function afterValidate()
	{
		$attrs = $this->getAttributes();
		if (!empty($attrs['email']))
		{
			$idSql = '';
			if (!empty($attrs['id']))
			{
				$idSql = ' AND id <> :id';
			}
			$cmd = Yii::app()->db->createCommand()
				->select('id')
				->from('{{users}}')
				->where('email = :email' . $idSql)
				->limit(1);
			$cmd->bindParam(':email', $attrs['email'], PDO::PARAM_STR);
			if (!empty($attrs['id']))
				$cmd->bindParam(':id', $attrs['id'], PDO::PARAM_INT);
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