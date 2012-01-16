<?php

/**
 * модель фильмов
 *
 */
class CFilm extends CActiveRecord {
    /**
     * @property $id
     * @property $title
     * @property $y
     * @property $active
     * @property $created
     * @property $modified
     */
    
    var $country;
   

    /**
     *
     * @param type $className
     * @return type 
     */   
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }
    
      public function defaultScope() {
        return array(
            'alias' => 'f',
        );
    }
/*
    public function behaviors() {
        return array(
            'description' => array(
                'class' => 'ext.film_descriptions.FilmDescriptionsBehavior',
            ),
            'countries' => array(
                'class' => 'ext.countries_films.CountriesFilmsBehavior',
            ),
        );
    }
*/
    public function tableName() {
        return '{{films}}';
    }

}