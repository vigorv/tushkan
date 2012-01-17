<?php

class FilesController extends Controller {

    public function actionAdd() {
        $files = new CUserfilelist();
        $this->render('add');
    }

    public function actionView($path) {
        //$id = Yii::app()->user->id;
        $files = new CUserfilelist();
        //$path = $files->CheckPath($path);
        $filelist = $files->LoadFileList($path);
        $folder_data = $files->ParseFileList($filelist);
        $this->render('view');
    }

    public function actionAjaxFoldersList() {
        if (!Yii::app()->request->isAjaxRequest) {
            exit();
        }
        

        $my_data = array(
            array(
                'text' => 'Node 1',
                'expanded' => true, // будет развернута ветка или нет (по умолчанию)
                'children' => array(
                    array(
                        'text' => 'Node 1.1',
                    ),
                    array(
                        'text' => 'Node 1.2',
                    ),
                    array(
                        'text' => 'Node 1.3',
                    ),
                )
            ),
        );
        
        echo CTreeView::saveDataAsJson($my_data);
        
        exit();
    }

    public function actionRemove() {
        echo "remove";
    }

    public function actionIndex() {
        $this->render('view');
    }
    public function actionDonothing(){
        echo '{success:true}';
    }

}

?>
