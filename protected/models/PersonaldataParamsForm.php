<?php
/**
 * модель формы параметров персональных данных пользователя
 *
 */
class PersonaldataParamsForm extends CFormModel
{
	public $title;
	public $srt;
	public $active;
	public $parent_id;
	public $tp;

	public function rules()
	{
		return array(
			array('title', 'required'),
			array('srt', 'numerical'),
			array('parent_id', 'numerical'),
			array('active', 'numerical'),
			array('tp', 'safe'),
		);
	}
}