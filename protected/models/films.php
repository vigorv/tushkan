<?php
/**
 * модель фильмов
 *
 */
class films extends CActiveRecord
{
	public function behaviors()
	{
	    return array(
	        'description' => array(
	            'class' => 'ext.film_descriptions.FilmDescriptionsBehavior',
	        ),
	        'countries' => array(
	            'class' => 'ext.countries_films.CountriesFilmsBehavior',
	        ),
	    );
	}

	public function tableName()
	{
		return '{{films}}';
	}
}