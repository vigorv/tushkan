<?php
/**
 * модель формы продукта
 *
 */
class ProductForm extends CFormModel
{
	public $title;
	public $active;
	public $description;
	public $countries;
	public $created;
	public $modified;

	public function rules()
	{
		return array(
			array('title', 'required'),
			array('active', 'numerical'),
			array('description', 'safe'),
			array('countries', 'safe'),
			array('created', 'safe'),
			array('modified', 'safe'),
		);
	}
}