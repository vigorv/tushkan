<?php
/**
 * модель формы продукта
 *
 */
class ProductForm extends CFormModel
{
	public $title;
	public $active;
	public $srt;
	public $description;
	public $params;
	public $variants;
	public $partner_id;
	public $created;
	public $modified;

	public function rules()
	{
		return array(
			array('title', 'required'),
			array('active', 'numerical'),
			array('srt', 'numerical'),
			array('description', 'safe'),
			array('params', 'safe'),
			array('variants', 'safe'),
			array('partner_id', 'numerical'),
			array('created', 'safe'),
			array('modified', 'safe'),
		);
	}
}