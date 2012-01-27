<?php
/**
 * контроллер статических страниц
 *
 */
class PagesController extends Controller {

	public function actionIndex($id)
	{
        $cmd = Yii::app()->db->createCommand()
                ->select('*')
                ->from('{{pages}}')
                ->where('id=:id');
        $cmd->bindParam(':id', $id, PDO::PARAM_INT);
        $info = $cmd->queryRow();
        $this->active = $info['active'];
        $this->render('index', array('info' => $info));
	}

    public function actionAdmin() {
        $this->layout = '/layouts/admin';
        $this->breadcrumbs = array(
            Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
            Yii::t('pages', 'Administrate pages'),
        );

		$lst = Yii::app()->db->createCommand()
			->select('*')
			->from('{{pages}}')
			->queryAll();

        $this->render('admin', array('lst' => $lst));
    }

    /**
     * действие инлайн редактирования
     *
     */
    public function actionEdit($id = 0) {
        $this->layout = '/layouts/admin';
        $this->breadcrumbs = array(
            Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
            Yii::t('pages', 'Administrate pages') => array($this->createUrl('pages/admin')),
            Yii::t('common', 'Edit'),
        );

        $cmd = Yii::app()->db->createCommand()
                ->select('*')
                ->from('{{pages}}')
                ->where('id=:id');
        $cmd->bindParam(':id', $id, PDO::PARAM_INT);
        $pageInfo = $cmd->queryRow();

        $pageForm = new PageForm();
        if (isset($_POST['PageForm'])) {
            $pageForm->attributes = $_POST['PageForm'];

            if ($pageForm->validate()) {
                $pageModel = new CPage();
                $attrs = $pageForm->getAttributes();
                foreach ($attrs as $k => $v) {
                    $pageInfo[$k] = $v;
                    $pageModel->{$k} = $v;
                }
                $pageModel->id = $pageInfo['id'];
                $pageModel->isNewRecord = false;
                $pageModel->save();
                Yii::app()->user->setFlash('success', Yii::t('pages', 'Page saved'));
            }
        }
        $this->render('/pages/edit', array('pageInfo' => $pageInfo, 'model' => $pageForm));
    }

    /**
     * действие формы добавления
     *
     */
    public function actionForm() {
        $this->layout = '/layouts/admin';
        $this->breadcrumbs = array(
            Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
            Yii::t('pages', 'Administrate pages') => array($this->createUrl('pages/admin')),
            Yii::t('pages', 'Add page'),
        );

        $pageForm = new PageForm();
        if (isset($_POST['PageForm'])) {
            $pageForm->attributes = $_POST['PageForm'];

            if ($pageForm->validate()) {
                //СОХРАНЕНИЕ ДАННЫХ C УЧЕТОМ ВСЕХ СВЯЗЕЙ
                $pageModel = new CPage();
                $attrs = $pageForm->getAttributes();
                foreach ($attrs as $k => $v) {
                    $pageModel->{$k} = $v;
                }
                $pageModel->save();
                Yii::app()->user->setFlash('success', Yii::t('pages', 'Page saved'));
                $this->redirect('/pages/admin');
            }
        }
        $this->render('/pages/form', array('model' => $pageForm));
    }

}