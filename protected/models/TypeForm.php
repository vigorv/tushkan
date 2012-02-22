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
	public $media_id;

	public function rules()
	{
		return array(
			array('title', 'required'),
			array('buy_limit', 'required'),
			array('buy_limit', 'numerical'),
			array('media_id', 'numerical'),
			array('active', 'numerical'),
			array('active', 'safe'),
		);
	}
}