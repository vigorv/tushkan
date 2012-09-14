<?php

class FileserversController extends Controller
{

    var $layout = 'admin';

    public function actionAdmin()
    {
        $criteria = new CDbCriteria();
        $count = CFileservers::model()->count($criteria);
        $server_count = CFileservers::model()->count();
        $per_page = 10;
        $pages = new CPagination($count);
        $pages->pageSize = $per_page;
        $server_list = CFileservers::model()->findAll($criteria);
        $this->render('admin', array('file_servers' => $server_list));
    }

    public function actionAdd()
    {
        $model = new FileServersForm;
        if (isset($_POST['FileServersForm'])) {
        // collects user input data
            $model->attributes = $_POST['FileServersForm'];
            // validates user input and redirect to previous page if validated
            if ($model->validate()) {
                $this->redirect(Yii::app()->fileservers->returnUrl);
            }
        }
        // displays the login form
        $this->render('add', array('model' => $model));
    }

    public function actionEdit($id = 0)
    {
        if ($id) {
            $model = new FileServersForm();
            $this->render('edit', array('model' => $model));
        } else die('Unknown server');

    }

}

?>