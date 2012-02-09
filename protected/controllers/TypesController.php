<?php

class TypesController extends Controller {

    public function actionAdmin() {
        $this->layout = '/layouts/admin';
        $this->breadcrumbs = array(
            Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
            Yii::t('types', 'Administrate types'),
        );

		$types = Yii::app()->db->createCommand()
			->select('*')
			->from('{{product_types}}')
			->queryAll();

        $this->render('admin', array('types' => $types));
    }

    /**
     * действие инлайн редактирования
     *
     */
    public function actionEdit($id = 0) {
        $this->layout = '/layouts/admin';
        $this->breadcrumbs = array(
            Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
            Yii::t('types', 'Administrate types') => array($this->createUrl('types/admin')),
            Yii::t('common', 'Edit'),
        );

        $cmd = Yii::app()->db->createCommand()
                ->select('*')
                ->from('{{product_types}}')
                ->where('id=:id');
        $cmd->bindParam(':id', $id, PDO::PARAM_INT);
        $type = $cmd->queryRow();
        $params = array(); $relations = array(); $chkParams = array();

        if (!empty($type))
        {
			$pLst = Yii::app()->db->createCommand()
	        	->select('*')
	        	->from('{{product_type_params}}')
                ->order('srt DESC')
	        	->queryAll();

	        $relations = Yii::app()->db->createCommand()
	        	->select('param_id')
                ->from('{{product_types_type_params}}')
                ->where('type_id = ' . $type['id'])
                ->queryAll();
            if (!empty($pLst))
            {
	            foreach ($pLst as $p) {
	                $params[$p['id']] = Yii::t('params', $p['title']);
	                if(!empty($relations))
	                {
	                	foreach($relations as $r)
	                	{
	                		if ($r['param_id'] == $p['id'])
	                		{
	                			$chkParams[$r['param_id']] = $r['param_id'];
	                			break;
	                		}
	                	}
	                }
	            }
            }
        }

        $typeForm = new TypeForm();
        if (isset($_POST['TypeForm'])) {
            $typeForm->attributes = $_POST['TypeForm'];

            if ($typeForm->validate()) {
                //СОХРАНЕНИЕ ДАННЫХ C УЧЕТОМ ВСЕХ СВЯЗЕЙ
                $types = new CType();
                $attrs = $typeForm->getAttributes();
                foreach ($attrs as $k => $v) {
                    $type[$k] = $v;
                    $types->{$k} = $v;
                }
                $types->id = $type['id'];
                $types->isNewRecord = false;
                $types->save();
                Yii::app()->user->setFlash('success', Yii::t('types', 'Type saved'));

                $sql = 'DELETE FROM {{product_types_type_params}} WHERE type_id = ' . $type['id'];
                Yii::app()->db->createCommand($sql)->query();

	            if (!empty($_POST['chkParams'])) {
	                $chkParams = $_POST['chkParams'];
	            	foreach ($chkParams as $c)
	            	{
		                $sql = 'INSERT INTO {{product_types_type_params}} (type_id, param_id) VALUES (' . $type['id'] . ', :param_id)';
		                $cmd = Yii::app()->db->createCommand($sql);
		                $cmd->bindParam(':param_id', $c, PDO::PARAM_INT);
		                $cmd->query();
	            	}
	            }
            }
        }
        $this->render('/types/edit', array('type' => $type, 'params' => $params, 'chkParams' => $chkParams, 'model' => $typeForm));
    }

    /**
     * действие формы добавления
     *
     */
    public function actionForm() {
        $this->layout = '/layouts/admin';
        $this->breadcrumbs = array(
            Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
            Yii::t('types', 'Administrate types') => array($this->createUrl('types/admin')),
            Yii::t('types', 'Add type'),
        );

        $typeForm = new TypeForm();
        if (isset($_POST['TypeForm'])) {
            $typeForm->attributes = $_POST['TypeForm'];

            if ($typeForm->validate()) {
                //СОХРАНЕНИЕ ДАННЫХ C УЧЕТОМ ВСЕХ СВЯЗЕЙ
                $types = new CType();
                $attrs = $typeForm->getAttributes();
                foreach ($attrs as $k => $v) {
                    $types->{$k} = $v;
                }
                $types->save();
                Yii::app()->user->setFlash('success', Yii::t('types', 'Type saved'));
            }
        }
        $this->render('/types/form', array('model' => $typeForm));
    }

}