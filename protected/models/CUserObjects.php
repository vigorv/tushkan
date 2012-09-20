<?php

/**
 * ActiveRecord class for UserObjects
 *
 * @property $id
 * @property $title
 * @property $user_id
 * @property $type_id
 * @property $active
 * @property $parent_id
 */
class CUserObjects extends CActiveRecord
{

    /**
     *
     * @param string $className
     * @return CUserObjects
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{userobjects}}';
    }

    public function getList($user_id, $type_id = -1, $page = 1, $count = 100)
    {
        $offset = ($page - 1) * $count;
        if ($type_id >= 0) {
            $type_str = ' AND uo.type_id=' . $type_id;
        } else
            $type_str = '';
        return Yii::app()->db->createCommand()
            ->select('uo.title,uo.id')
            ->from('{{userobjects}} uo')
            ->where('uo.user_id =' . $user_id . $type_str)
            ->limit($count, $offset)
            ->queryAll();
    }

    /**
     * Получить список типизированного с дополнительными параметрами
     *
     * @param integer $user_id
     * @param integer $type_id
     * @param integer $page
     * @param integer $count
     * @return mixed
     */
    public function getExtList($user_id, $type_id = -1, $page = 1, $count = 100)
    {
    	$items = $this->getList($user_id, $type_id, $page, $count);
    	if (!empty($items))
    	{
    		$items = Utils::pushIndexToKey('id', $items);
    		$idSql = ' AND pv.object_id IN (' . implode(',', array_keys($items)) . ')';

    	//ТЕПЕРЬ ДОВЫБИРАЕМ ПАРАМЕТРЫ
    		$params = Yii::app()->db->createCommand()
    			->select('pv.value, pv.object_id, tp.title')
    			->from('{{userobjects_param_values}} pv')
    			->join('{{product_type_params}} tp', 'tp.id = pv.param_id')
    			->where('pv.value <> ""' . $idSql)
    			->queryAll();
    		if (!empty($params))
    		{
    			foreach ($params as $p)
    			{
    				if (empty($items[$p['object_id']]['params']))
    				{
    					$items[$p['object_id']]['params'] = array();
    				}
    				$items[$p['object_id']]['params'][$p['title']] = $p['value'];
    			}
    		}
    	}
//var_dump($items);
//exit;
		return $items;
    }

    public function getObjectsLike($user_id, $like, $page = 1, $per_page = 10, $type_id = -1)
    {
        $offset = ($page - 1) * $per_page;
        if ($type_id >= 0) {
            $type_str = ' AND uo.type_id=' . $type_id;
        } else
            $type_str = '';
        return Yii::app()->db->createCommand()
            ->select('uo.title,uo.id')
            ->from('{{userobjects}} uo')
            ->where('uo.user_id =' . $user_id . ' AND uo.title LIKE "%' . $like . '%"' . $type_str)
            ->limit($per_page, $offset)
            ->queryAll();
    }

    /**
     * Удаление типизированного объекта пользователя со связями и физическими файлами
     *
     * @param integer $uid - идентификатор владельца (пользователя)
     * @param integer $oid - идентификатор объекта
     * @return boolean - результат удаления (удалено/не удалено)
     */
    public static function deleteUserObject($uid, $oid)
    {
    	//ВЫБИРАЕМ ОБЪЕКТ И ЕГО ФАЙЛЫ
    	$cmd = Yii::app()->db->createCommand()
    		->select('uo.id, uf.id as fid')
    		->from('{{userobjects}} uo')
    		->join('{{userfiles}} uf', 'uo.id = uf.object_id')
    		->where('uo.id = :oid AND uo.user_id = :uid');

    	$cmd->bindParam(':uid', $uid, PDO::PARAM_INT);
    	$cmd->bindParam(':oid', $oid, PDO::PARAM_INT);

    	$ost = $cmd->queryAll();
    	if (!empty($ost))
    	{
    		//УДАЛЯЕМ ПАРАМЕТРЫ ТИПИЗАЦММ
    		$sql = 'DELETE FROM {{userobjects_param_values}} WHERE object_id = :oid';
    		$cmd = Yii::app()->db->createCommand($sql);
	    	$cmd->bindParam(':oid', $oid, PDO::PARAM_INT);
	    	$cmd->query();

    		//УДАЛЯЕМ ФАЙЛЫ, ВАРИАНТЫ, ЛОКАЦИИ
	    	foreach ($ost as $o)
	    	{
	    		CUserfiles::RemoveFile($uid, $o['fid']);
	    	}

    		//УДАЛЯЕМ ОБЪЕКТ
    		$sql = 'DELETE FROM {{userobjects}} WHERE id = :oid';
    		$cmd = Yii::app()->db->createCommand($sql);
	    	$cmd->bindParam(':oid', $oid, PDO::PARAM_INT);
	    	$cmd->query();

	    	return true;
    	}
    	return false;
    }
}