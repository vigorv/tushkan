<?php

class ProductDescriptionsBehavior extends CActiveRecordBehavior
{
	public $description;

	public function afterSave()
	{
		if (!empty($this->getOwner()->id))
		{
			Yii::app()->db->createCommand('DELETE FROM {{product_descriptions}} WHERE product_id = ' . $this->getOwner()->id)->query();
			$cmd = Yii::app()->db->createCommand('INSERT INTO {{product_descriptions}} (product_id, description) VALUES (' . $this->getOwner()->id . ', :description);');
			$cmd->bindParam(':description', $this->description, PDO::PARAM_STR);
			$cmd->query();
		}
	}
}