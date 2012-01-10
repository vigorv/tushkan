<?php
/**
 * модель пользователей
 *
 */
class users extends CActiveRecord
{
	public function tableName()
	{
		return '{{users}}';
	}
}