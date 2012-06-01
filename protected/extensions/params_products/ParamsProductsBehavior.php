<?php

class ParamsProductsBehavior extends CActiveRecordBehavior
{
	public $params;
	public $variants;

	/**
	 * Сохраняем варианты продукта
	 * и значения параметров для всех вариантов продукта
	 *
	 */
	public function afterSave($event)
	{

		if (!empty($this->getOwner()->id))
		{
			//ВАРИАНТЫ ПРОДУКТА НЕ УДАЛЯЕМ, А ТОЛЬКО ДОБАВЛЯЕМ ИЛИ ОБНОВЛЯЕМ
			if (!empty($this->variants))
			{
				foreach ($this->variants as $vk => $variant)
				{
					$vId = $variant['id']; //ЗАПОМИНАЕМ ID ТЕКУЩЕГО ВАРИАНТА
					$variant['product_id'] = $this->getOwner()->id;
					(!empty($variant['online_only']) && ($variant['online_only'] == 'on')) ?
						$variant['online_only'] = 1 : $variant['online_only'] = 0;
					if ($variant['id'] < 0)
					{
						//ЭТО НОВЫЙ ВАРИАНТ
//print_r($variant);
//exit;
						$sql = 'INSERT INTO {{product_variants}} (id, product_id, online_only, type_id, active)
							VALUES(null, :product_id, ' . $variant['online_only'] . ', :type_id, :active)
						';
						$cmd = Yii::app()->db->createCommand($sql);
						$cmd->bindParam(":product_id", $variant['product_id'], PDO::PARAM_INT);
						$cmd->bindParam(":type_id", $variant['type_id'], PDO::PARAM_INT);
						$cmd->bindParam(":active", $variant['active'], PDO::PARAM_INT);
						$cmd->execute();
						$variant['id'] = Yii::app()->db->getLastInsertID('{{product_variants}}');
					}
					else
					{
						//ОБНОВЛЯЕМ ВАРИАНТ
						$sql = 'UPDATE {{product_variants}} SET online_only = ' . $variant['online_only'] . ',
							active = :active WHERE id = :id';
						$cmd = Yii::app()->db->createCommand($sql);
						$cmd->bindParam(":id", $variant['id'], PDO::PARAM_INT);
						$cmd->bindParam(":active", $variant['active'], PDO::PARAM_INT);
						$cmd->execute();
					}

					if (!empty($this->params))
					{
						foreach ($this->params[$vk] as $pid => $param)
						{
							//СОХРАНЯЕМ ПАРАМЕТРЫ ТЕКУЩЕГО ВАРИАНТА
							if (empty($param['vlid']))
							{
								//ДОБАВЛЯЕМ НОВЫЙ ПАРАМЕТР
								$param['variant_id'] = $variant['id'];
								$sql = 'INSERT INTO {{product_param_values}} (id, param_id, value, variant_id)
									VALUES(null, :param_id, :value, :variant_id)
								';
								$cmd = Yii::app()->db->createCommand($sql);
								$cmd->bindParam(":param_id", $pid, PDO::PARAM_INT);
								$cmd->bindParam(":value", $param['value'], PDO::PARAM_STR);
								$cmd->bindParam(":variant_id", $param['variant_id'], PDO::PARAM_INT);
								$cmd->execute();
							}
							else
							{
								//ОБНОВЛЯЕМ ЗНАЧЕНИЕ ПАРАМЕТРА
								$sql = 'UPDATE {{product_param_values}} SET value = :value WHERE id = :id';
								$cmd = Yii::app()->db->createCommand($sql);
								$cmd->bindParam(":id", $param['vlid'], PDO::PARAM_INT);
								$cmd->bindParam(":value", $param['value'], PDO::PARAM_STR);
								$cmd->execute();
							}
						}
					}
				}
			}
		}
	}
}