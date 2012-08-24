<?php
/**
 * модель формы варианта
 *
 */
class VariantForm extends CFormModel
{
	public $online_only;
	public $active;
	public $title;
	public $description;
	public $original_id;
	public $sub_id;

	public function rules()
	{
		return array(
			array('online_only', 'safe'),
			array('active', 'numerical'),
			array('title', 'required'),
			array('description', 'safe'),
			array('original_id', 'numerical'),
			array('sub_id', 'numerical'),
		);
	}
}