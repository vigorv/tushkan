<?php

class ParamsController extends Controller {

    public function actionAdmin() {
        $this->layout = '/layouts/admin';
        $this->breadcrumbs = array(
            Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
            Yii::t('params', 'Administrate params'),
        );

		$params = Yii::app()->db->createCommand()
			->select('*')
			->from('{{product_type_params}}')
			->queryAll();

        $this->render('admin', array('params' => $params));
    }

    /**
     * действие инлайн редактирования
     *
     */
    public function actionEdit($id = 0) {
        $this->layout = '/layouts/admin';
        $this->breadcrumbs = array(
            Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
            Yii::t('params', 'Administrate params') => array($this->createUrl('params/admin')),
            Yii::t('common', 'Edit'),
        );

        $cmd = Yii::app()->db->createCommand()
                ->select('*')
                ->from('{{product_type_params}}')
                ->where('id=:id');
        $cmd->bindParam(':id', $id, PDO::PARAM_INT);
        $param = $cmd->queryRow();

        $paramForm = new ParamForm();
        if (isset($_POST['ParamForm'])) {
            $paramForm->attributes = $_POST['ParamForm'];

            if ($paramForm->validate()) {
                //СОХРАНЕНИЕ ДАННЫХ C УЧЕТОМ ВСЕХ СВЯЗЕЙ
                $params = new CParam();
                $attrs = $paramForm->getAttributes();
                foreach ($attrs as $k => $v) {
                    $param[$k] = $v;
                    $params->{$k} = $v;
                }
                $params->id = $param['id'];
                $params->isNewRecord = false;
                $params->save();
                Yii::app()->user->setFlash('success', Yii::t('params', 'Param saved'));
            }
        }
        $this->render('/params/edit', array('param' => $param, 'model' => $paramForm));
    }

    /**
     * действие формы добавления
     *
     */
    public function actionForm() {
        $this->layout = '/layouts/admin';
        $this->breadcrumbs = array(
            Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
            Yii::t('params', 'Administrate params') => array($this->createUrl('params/admin')),
            Yii::t('params', 'Add param'),
        );

        $paramForm = new ParamForm();
        if (isset($_POST['ParamForm'])) {
            $paramForm->attributes = $_POST['ParamForm'];

            if ($paramForm->validate()) {
                //СОХРАНЕНИЕ ДАННЫХ C УЧЕТОМ ВСЕХ СВЯЗЕЙ
                $params = new CParam();
                $attrs = $paramForm->getAttributes();
                if (empty($attrs['srt'])) {
                    $attrs['srt'] = 0;
                }
                foreach ($attrs as $k => $v) {
                    $params->{$k} = $v;
                }
                $params->save();
                Yii::app()->user->setFlash('success', Yii::t('params', 'Param saved'));
            }
        }
        $this->render('/params/form', array('model' => $paramForm));
    }

}