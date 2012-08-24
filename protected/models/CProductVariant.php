<?php

/**
 * модель вариантов товаров
 *
 */
class CProductVariant extends CActiveRecord {
    /**
     *
     * @param string $className
     * @return CActiveRecord
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return '{{product_variants}}';
    }

    public static function getPartnerVariantData($partner_id,$original_id){
        return Yii::app()->db->createCommand()
            ->select('pv.*,p.*')
            ->from('{{product_variants}} pv')
            ->leftJoin('{{products}} p','p.partner_id = :partner_id AND  pv.product_id = p.id',array(':partner_id'=>$partner_id))
            ->where('pv.id = :variant_id',array(':variant_id'=>$original_id))
            ->queryAll();
    }

    /**
     * получить дерево вариантов продукта в ввиде массива
     *
     * @param integer $productId - идентификатор продукта
     * @return mixed массив вида
     * 		array(
     * 			variantId1 => array(
     * 					id
     * 					title
     * 					type_id
     * 					active
     * 					childs
     * 					childsInfo => array(
	 *	 					id
	 *	 					title
	 *	 					type_id
	 *	 					active
	 *	 					childs
     * 					)
     * 				)
     * 			...
     * 			variantId{n} => array(...)
     * 		)
     */
    public static function getProductVariantsTree($productId)
    {
		$cmd = Yii::app()->db->createCommand()
			->select('pv.id, pv.title, pv.type_id, pv.active, pv.childs')
			->from('{{product_variants}} pv')
			->where('pv.product_id = :product_id')
			->order('pv.childs DESC');//ЭТО ВАЖНО! ЧТОБЫ РОДИТЕЛЬСКИЕ ВАРИАНТЫ ОКАЗАЛИСЬ В НАЧАЛЕ
		$cmd->bindParam(':product_id', $productId, PDO::PARAM_INT);
		$cmd->queryAll();

		$errMsg = ' !update this variant to fix structure error';
		$variantsInfo = $cmd->queryAll();
		$tree = array();
		foreach ($variantsInfo as $vi)
		{
			if (($vi['childs'] != '') && ($vi['childs'] != ',,'))
			{
				//ЭТО РОДИТЕЛЬСКИЙ ВАРИАНТ
				$cIds = CProductVariant::getChildsIds($vi['childs']);
				$vi['childsInfo'] = array();
				foreach ($variantsInfo as $cv)
				{
					if (in_array($cv['id'], $cIds))
					{
						if (!empty($cv['childs']))
							$cv['title'] .= $errMsg;
						$vi['childsInfo'][$cv['id']] = $cv;
					}
				}
			}
			else
			{
				if (empty($vi['childs']))
				{
					$parentExists = false;
					foreach ($variantsInfo as $pv)
					{
						$cIds = CProductVariant::getChildsIds($pv['childs']);
						if (in_array($vi['id'], $cIds))
						{
							$parentExists = true;
							break;
						}
					}

					if (!$parentExists)
						$cv['title'] .= $errMsg;
					else
						continue;
				}
			}
			$tree[$vi['id']] = $vi;
		}

		return $tree;
    }

    /**
     * преобразовать значение строкового поля childs варианта в массив идентификаторов
     *
     * @param string $childs
     * @return mixed
     */
    public static function getChildsIds($childs)
    {
		$childs = explode(',', $childs);
		$ids = array();
		foreach ($childs as $v)
		{
			$v = intval($v);
			if (!empty($v))
			{
				$ids[$v] = $v;
			}
		}
    	return $ids;
    }

    /**
     * получить все связи варианта в виде структуры
     *
     * @param integer $variantId
     * @return mixed
     */
    public static function getVariantRelations($variantId = 0)
    {
    	$result = array();
    	$variantId = intval($variantId);

    	if (empty($variantId))
    		return $result;
    	$relations = array(
    		'actual_rents',
    		'order_items',
    		'prices',
    		'product_param_values',
    		'rents',
    		'typedfiles',
    		'variant_qualities',
    	);
    	foreach ($relations as $r)
    	{
    		$result[$r] = Yii::app()->db->createCommand()
    			->select('*')
    			->from('{{' . $r . '}}')
    			->where('variant_id = ' . $variantId)
    			->queryAll();
    	}
    	return $result;
    }
}