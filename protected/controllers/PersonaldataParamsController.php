<?php

class PersonalDataParamsController extends Controller {

    public function actionAdmin() {
        $this->layout = '/layouts/admin';
        $this->breadcrumbs = array(
            Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
            Yii::t('params', 'Administrate personal data params'),
        );

		$params = Yii::app()->db->createCommand()
			->select('*')
			->from('{{personaldata_params}}')
			->queryAll();

        $this->render('admin', array('params' => $params));
    }

    public function actionEdit($id = 0) {
        $this->layout = '/layouts/admin';
        $this->breadcrumbs = array(
            Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
            Yii::t('params', 'Administrate personal data params') => array($this->createUrl('personaldataparams/admin')),
            Yii::t('common', 'Edit'),
        );

        $cmd = Yii::app()->db->createCommand()
                ->select('*')
                ->from('{{personaldata_params}}')
                ->where('id=:id');
        $cmd->bindParam(':id', $id, PDO::PARAM_INT);
        $param = $cmd->queryRow();

        $paramForm = new PersonalDataParamsForm();
        if (isset($_POST['PersonaldataParamsForm'])) {
            $paramForm->attributes = $_POST['PersonaldataParamsForm'];

            if ($paramForm->validate()) {
                //СОХРАНЕНИЕ ДАННЫХ C УЧЕТОМ ВСЕХ СВЯЗЕЙ
                $params = new CPersonaldataParams();
                $attrs = $paramForm->getAttributes();
                foreach ($attrs as $k => $v) {
                    $param[$k] = $v;
                    $params->{$k} = $v;
                }
                if (empty($params->srt))
                	$params->srt = 0;
                if (empty($params->parent_id))
                	$params->parent_id = 0;
                $params->id = $param['id'];
                $params->isNewRecord = false;
                $params->save();
                Yii::app()->user->setFlash('success', Yii::t('params', 'Param saved'));
            }
        }
        $this->render('edit', array('info' => $param, 'model' => $paramForm));
    }

    /**
     * действие формы добавления
     *
     */
    public function actionForm() {
        $this->layout = '/layouts/admin';
        $this->breadcrumbs = array(
            Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
            Yii::t('params', 'Administrate personal data params') => array($this->createUrl('personaldataparams/admin')),
            Yii::t('params', 'Add param'),
        );

        $paramForm = new PersonalDataParamsForm();
        if (isset($_POST['PersonaldataParamsForm'])) {
            $paramForm->attributes = $_POST['PersonaldataParamsForm'];

            if ($paramForm->validate()) {
                //СОХРАНЕНИЕ ДАННЫХ C УЧЕТОМ ВСЕХ СВЯЗЕЙ
                $params = new CPersonaldataParams();
                $attrs = $paramForm->getAttributes();
                foreach ($attrs as $k => $v) {
                    $params->{$k} = $v;
                }
                if (empty($params->srt))
                	$params->srt = 0;
                if (empty($params->parent_id))
                	$params->parent_id = 0;
                $params->save();
                Yii::app()->user->setFlash('success', Yii::t('params', 'Param saved'));
            }
        }
        $this->render('form', array('model' => $paramForm));
    }

}