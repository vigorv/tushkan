<?php

class PaysystemsController extends Controller {

    private $_crumbs = array();

    public function actionAdmin() {

        $this->layout = '/layouts/admin';

        $criteria = new CDbCriteria();
        $count = CPaysystem::model()->count($criteria);
        $pages = new CPagination($count);
        // элементов на страницу
        $pages->pageSize = 10;
        $pages->applyLimit($criteria);
        $criteria->select = '*';

        $paysystems = CPaysystem::model()->findAll($criteria);

        $this->render('admin', array('paysystems' => $paysystems, 'pages' => $pages));
    }

    /**
     * действие формы добавления
     *
     */
    public function actionForm() {
        $this->layout = '/layouts/admin';
        $this->_crumbs = array(Yii::t('pays', 'Add Paysystem'));

        $paysystemForm = new PaysystemForm();
        if (isset($_POST['PaysystemForm'])) {
            $paysystemForm->attributes = $_POST['PaysystemForm'];

            if ($paysystemForm->validate()) {
                $paysystems = new CPaysystem();
                $attrs = $paysystemForm->getAttributes();
                if (empty($attrs['active'])) {
                    $attrs['active'] = 1;
                }
                foreach ($attrs as $k => $v) {
                    $paysystems->{$k} = $v;
                }

                $paysystems->save();
                Yii::app()->user->setFlash('success', Yii::t('pays', 'Paysystem saved'));
            }
        }
        $this->render('form', array('model' => $paysystemForm));
    }

    /**
     * действие редактирования
     *
     */
    public function actionEdit($id = 0) {
        $this->layout = '/layouts/admin';
        $this->_crumbs = array(Yii::t('common', 'edit'));

        $cmd = Yii::app()->db->createCommand()
                ->select('*')
                ->from('{{paysystems}}')
                ->where('id=:id');
        $cmd->bindParam(':id', $id, PDO::PARAM_INT);
        $info = $cmd->queryRow();

        $paysystemForm = new PaysystemForm();
        if (isset($_POST['PaysystemForm'])) {
            $paysystemForm->attributes = $_POST['PaysystemForm'];
            $attrs = $paysystemForm->getAttributes();
            if ($paysystemForm->validate()) {
                $paysystems = new CPaysystem();
                foreach ($attrs as $k => $v) {
                    if (empty($v)) {
                        $attrs[$k] = $info[$k];
                    }
                    $paysystems->{$k} = $attrs[$k];
                }

                $paysystems->isNewRecord = false;
                $paysystems->save();

                Yii::app()->user->setFlash('success', Yii::t('pays', 'Paysystem saved'));
            }
            $info = $attrs;
        }

        $this->render('edit', array('model' => $paysystemForm, 'info' => $info));
    }

    /**
     * действие удаления
     *
     */
    public function actionDelete($id = 0) {
        $this->layout = '/layouts/admin';
        $this->_crumbs = array(Yii::t('common', 'delete'));

        $sql = 'UPDATE {{paysystems}} SET active=0 WHERE id = :id';
        $cmd = Yii::app()->db->createCommand($sql);
        $cmd->bindParam(':id', $id, PDO::PARAM_INT);
        $info = $cmd->query();

        Yii::app()->user->setFlash('success', Yii::t('pays', 'Paysystem deleted'));
        $this->redirect('/paysystems/admin');
    }

    /**
     * действие восстановления удаленного пользователя
     *
     */
    public function actionRestore($id = 0) {
        $this->layout = '/layouts/admin';
        $this->_crumbs = array(Yii::t('common', 'delete'));

        $sql = 'UPDATE {{paysystems}} SET active=1 WHERE id = :id';
        $cmd = Yii::app()->db->createCommand($sql);
        $cmd->bindParam(':id', $id, PDO::PARAM_INT);
        $info = $cmd->query();

        Yii::app()->user->setFlash('success', Yii::t('pays', 'Paysystem restored'));
        $this->redirect('/paysystems/admin');
    }

    /**
     * используем этот callback для генерирования строки обратной навигации
     *
     * @return ищщдуфт
     */
    protected function beforeRender($view) {
        $controllerRoot = array(Yii::t('pays', 'Administrate paysystems'));
        if (!empty($this->_crumbs)) {
            $controllerRoot = array(Yii::t('pays', 'Administrate paysystems') => $this->createUrl('paysystems/admin'));
        }
        $this->breadcrumbs = array_merge(
                array(
            Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
                ), $controllerRoot, $this->_crumbs
        );

        return true;
    }

}