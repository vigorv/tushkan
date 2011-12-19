<?php

class CountriesFilmsBehavior extends CActiveRecordBehavior
{
	public $countries;

	public function afterSave()
	{
		if (!empty($this->getOwner()->id))
		{
			Yii::app()->db->createCommand('DELETE FROM {{countries_films}} WHERE film_id = ' . $this->getOwner()->id)->query();

			if (!empty($this->countries))
			{
//print_r($this->countries);
//exit;
				foreach ($this->countries as $c)
				{
					$cmd = Yii::app()->db->createCommand('INSERT INTO {{countries_films}} (film_id, country_id) VALUES (' . $this->getOwner()->id . ', :country);');
					$cmd->bindParam(':country', $c, PDO::PARAM_INT);
					$cmd->query();
				}
			}
		}
	}
}