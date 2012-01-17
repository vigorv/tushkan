<?php
/**
 * модель админской формы платежной системы
 *
 */
class PaysystemForm extends CFormModel
{
	public $id;
	public $title;
	public $active;
	public $class;
	public $srt;

	public function rules()
	{
		return array(
			array('title', 'required'),
		    array('title', 'length', 'min' => 3),
			array('active', 'numerical'),
			array('srt', 'numerical'),
			array('class', 'safe'),
			array('id', 'safe'),
		);
	}
}