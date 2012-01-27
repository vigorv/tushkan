<?php

class UsersController extends Controller {

    private $_crumbs = array();

    public function actionAdmin() {

        $this->layout = '/layouts/admin';

        $criteria = new CDbCriteria();
        $count = CUser::model()->count($criteria);
        /*
          $users = Yii::app()->db->createCommand()
          ->select('u.id, u.name, u.email, u.created, u.lastvisit, u.active, g.title as gtitle')
          ->from('{{users}}')
          ->leftJoin('{{user_groups}} g', 'g.id=u.group_id')
          ->group('u.id')
          ->query();
         */
        $pages = new CPagination($count);
        // элементов на страницу
        $pages->pageSize = 10;
        $pages->applyLimit($criteria);
        $criteria->select=' u.*, g.title as gtitle';
        $criteria->join = 'LEFT JOIN {{user_groups}} as g  ON g.id=u.group_id';

        $users = CUser::model()
                ->findAll($criteria);

        $this->render('admin', array('users' => $users, 'pages' => $pages));
    }

    /**
     * действие формы добавления
     *
     */
    public function actionForm() {
        $this->layout = '/layouts/admin';
        $this->_crumbs = array(Yii::t('users', 'Add User'));

        $gLst = Yii::app()->db->createCommand()
                ->select('id, title')
                ->from('{{user_groups}}')
                ->order('power ASC')
                ->queryAll();

        $groups = array();
        foreach ($gLst as $g) {
            $groups[$g['id']] = $g['title'];
        }

        $userForm = new UserForm();
        if (isset($_POST['UserForm'])) {
            $userForm->attributes = $_POST['UserForm'];

            if ($userForm->validate()) {
                $users = new CUser();
                $attrs = $userForm->getAttributes();
                $attrs['salt'] = substr(md5(time()), 0, 5); //СОЛЬ ГЕНЕРИРУЕМ ПРИ ДОБАВЛЕНИИ
                $attrs['created'] = date('Y-m-d H:i:s');
                $attrs['lastvisit'] = date('Y-m-d H:i:s');
                $attrs['server_id'] = 0;
                $attrs['sess_id'] = 0;
                if (empty($attrs['active'])) {
                    $attrs['active'] = 1;
                }
                foreach ($attrs as $k => $v) {
                    $users->{$k} = $v;
                }

                $this->identity->password = $users->pwd;
                $users->pwd = $this->identity->transformPassword($attrs); //ШИФРУЕМ ПАРОЛЬ ПО ПРАВИЛАМ АВТОРИЗАТОРА
                $users->save();
                Yii::app()->user->setFlash('success', Yii::t('users', 'User saved'));
            }
        }
        $this->render('form', array('model' => $userForm, 'groups' => $groups));
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
                ->from('{{users}}')
                ->where('id=:id');
        $cmd->bindParam(':id', $id, PDO::PARAM_INT);
        $info = $cmd->queryRow();

        $gLst = Yii::app()->db->createCommand()
                ->select('id, title')
                ->from('{{user_groups}}')
                ->order('power ASC')
                ->queryAll();

        $groups = array();
        foreach ($gLst as $g) {
            $groups[$g['id']] = $g['title'];
        }

        $userForm = new UserForm();
        if (isset($_POST['UserForm'])) {
            $userForm->attributes = $_POST['UserForm'];
            $attrs = $userForm->getAttributes();
            if ($userForm->validate()) {
                $users = new CUser;
                foreach ($attrs as $k => $v) {
                    if (empty($v)) {
                        $attrs[$k] = $info[$k];
                    }
                    $users->{$k} = $attrs[$k];
                }

                if ($attrs['pwd'] <> $info['pwd']) {//ПЕРЕКОДИРУЕМ ПАРОЛЬ, ЕСЛИ ТОЛЬКО УКАЗАЛИ НОВЫЙ
                    $this->identity->password = $attrs['pwd'];
                    $users->pwd = $this->identity->transformPassword($attrs); //ШИФРУЕМ ПАРОЛЬ ПО ПРАВИЛАМ АВТОРИЗАТОРА
                }
                $users->isNewRecord = false;
                $users->save();

                Yii::app()->user->setFlash('success', Yii::t('users', 'User saved'));
            }
            $info = $attrs;
        }

        $this->render('edit', array('model' => $userForm, 'groups' => $groups, 'info' => $info));
    }

    /**
     * действие для AJAX валидации/сохранения
     *
     * ! НЕ ДОДЕЛАНО !
     *
     */
    public function actionSave($id = 0) {
        $this->layout = '/layouts/ajax';
        if (!empty($_POST)) {
            $sql = 'UPDATE';
            $cmd = Yii::app()->db->createCommand($sql);
            $cmd->bindParam(':id', $id, PDO::PARAM_INT);
            $info = $cmd->query();
            $result = 'ok';
        }
        $this->render('save', array('result' => $result));
    }

    /**
     * действие удаления
     *
     */
    public function actionDelete($id = 0) {
        $this->layout = '/layouts/admin';
        $this->_crumbs = array(Yii::t('common', 'delete'));

        $sql = 'UPDATE {{users}} SET active=0 WHERE id = :id';
        $cmd = Yii::app()->db->createCommand($sql);
        $cmd->bindParam(':id', $id, PDO::PARAM_INT);
        $info = $cmd->query();

        Yii::app()->user->setFlash('success', Yii::t('users', 'User deleted'));
        $this->redirect('/users/admin');
    }

    /**
     * действие восстановления удаленного пользователя
     *
     */
    public function actionRestore($id = 0) {
        $this->layout = '/layouts/admin';
        $this->_crumbs = array(Yii::t('common', 'delete'));

        $sql = 'UPDATE {{users}} SET active=1 WHERE id = :id';
        $cmd = Yii::app()->db->createCommand($sql);
        $cmd->bindParam(':id', $id, PDO::PARAM_INT);
        $info = $cmd->query();

        Yii::app()->user->setFlash('success', Yii::t('users', 'User restored'));
        $this->redirect('/users/admin');
    }

    /**
     * используем этот callback для генерирования строки обратной навигации
     *
     * @return boolean
     */
    public function beforeRender($view) {
    	parent::beforeRender($view);

        $controllerRoot = array(Yii::t('users', 'Administrate users'));
        if (!empty($this->_crumbs)) {
            $controllerRoot = array(Yii::t('users', 'Administrate users') => $this->createUrl('users/admin'));
        }
        $this->breadcrumbs = array_merge(
                array(
            Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
                ), $controllerRoot, $this->_crumbs
        );

        return true;
    }

}