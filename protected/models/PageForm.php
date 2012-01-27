<?php
/**
 * модель формы параметров типа продукта
 *
 */
class PageForm extends CFormModel
{
	public $title;
	public $txt;
	public $meta_description;
	public $meta_keywords;
	public $meta_title;
	public $active;
	public $parent_id;
	public $alias;

	public function rules()
	{
		return array(
			array('title', 'required'),
			array('txt', 'safe'),
			array('meta_description', 'safe'),
			array('meta_keywords', 'safe'),
			array('meta_title', 'safe'),
			array('active', 'numerical'),
			array('parent_id', 'numerical'),
			array('alias', 'safe'),
		);
	}
}