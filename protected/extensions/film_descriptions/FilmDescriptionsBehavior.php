<?php

class FilmDescriptionsBehavior extends CActiveRecordBehavior
{
	public $description;

	public function afterSave()
	{
		if (!empty($this->getOwner()->id))
		{
			Yii::app()->db->createCommand('DELETE FROM {{film_descriptions}} WHERE film_id = ' . $this->getOwner()->id)->query();
			$cmd = Yii::app()->db->createCommand('INSERT INTO {{film_descriptions}} (film_id, description) VALUES (' . $this->getOwner()->id . ', :description);');
			$cmd->bindParam(':description', $this->description, PDO::PARAM_STR);
			$cmd->query();
		}
	}
}