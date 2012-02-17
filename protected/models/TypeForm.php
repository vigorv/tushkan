<?php
/**
 * модель формы типа продукта
 *
 */
class TypeForm extends CFormModel
{
	public $title;
	public $active;
	public $buy_limit;

	public function rules()
	{
		return array(
			array('title', 'required'),
			array('buy_limit', 'required'),
			array('buy_limit', 'numerical'),
			array('active', 'numerical'),
			array('active', 'safe'),
		);
	}
}