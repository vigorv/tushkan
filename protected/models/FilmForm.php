<?php
/**
 * модель формы фильма
 *
 */
class FilmForm extends CFormModel
{
	public $title;
	public $y;
	public $active;
	public $description;
	public $countries;
	public $created;
	public $modified;

	public function rules()
	{
		return array(
			array('title', 'required'),
			array('y', 'safe'),
			array('active', 'numerical'),
			array('description', 'safe'),
			array('countries', 'safe'),
			array('created', 'safe'),
			array('modified', 'safe'),
		);
	}
}