<?php
/**
 * модель формы параметров типа продукта
 *
 */
class ParamForm extends CFormModel
{
	public $title;
	public $description;
	public $srt;
	public $active;

	public function rules()
	{
		return array(
			array('title', 'required'),
			array('description', 'safe'),
			array('srt', 'numerical'),
			array('active', 'numerical'),
		);
	}
}