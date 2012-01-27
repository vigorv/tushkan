<?php

class FilesController extends Controller {

    var $user_id;

    public function beforeAction($action) {
        parent::beforeAction($action);
        $this->user_id = Yii::app()->user->id;
        if ($this->user_id)
            return true;
    }

    /**
     * 
     * @param type $pid
     * @param type $title
     * @param type $is_dir 
     */
    public function FCreate($pid, $title, $is_dir) {
        $files = new CUserfiles();
        $files->title = $title;
        $files->pid = $pid;
        $files->is_dir = $is_dir;
        $files->user_id = $this->user_id;
        $files->save();
    }

    /**
     *
     * @param type $id
     * @param type $pid 
     */
    public function FMove($id, $pid) {
//  TO DO :check
        CUserfiles::model()->updateByPk(array('user_id' => $this->user_id, 'id' => $id), 'pid=' . $pid);
        echo "OK";
    }

    /**
     *
     * @param type $id 
     */
    public function FRemove($id) {
//TO DO:delete all files
        CUserfiles::model()->deleteByPk(array('user_id' => $this->user_id, 'id' => $id));
        echo "OK";
    }

    public function actionFOpen($id=0) {
        if ($id > 0)
            $item = CUserfiles::model()->findByPk(array('user_id' => $this->user_id, 'id' => $id));
        if (($id == 0) || ($item->is_dir)) {
            $flist = CUserfiles::model()->findAllByAttributes(array('user_id' => $this->user_id, 'pid' => $id), array('select' => 'id,pid,title,is_dir'));
            if (true) {
                echo CFiletypes::ParsePrint($flist, 'FL1');
                exit;
            } else {
//$this
            }
        } else {
            switch (filetype($item->title)) {
                default:
                    echo "Open files support comes later";
            }
        }
    }

    public function actionAdd() {
        $user_id = Yii::app()->user->id;
        $sid = CUser::model()->findByPk($user_id)->getAttribute('sess_id');
        $this->render('add', array('user_id' => $user_id, 'sid' => $sid));
    }

    public function actionAjaxFoldersList() {
        if (!Yii::app()->request->isAjaxRequest) {
//exit();
        }
        $parentId = 0;
        if (isset($_GET['root']) && $_GET['root'] !== 'source') {
            $parentId = (int) $_GET['root'];
        } else {
            $data = array(array(
                    'text' => 'All files',
                    'id' => "0",
                    ));
        }

        $req = Yii::app()->db->createCommand('SELECT uf.id as id, uf.title AS text, uf2.id is not null  as hasChildren '
                . 'FROM {{userfiles}} AS uf  LEFT JOIN {{userfiles}} as uf2  on uf.id = uf2.pid and uf2.is_dir = 1 and uf.user_id =' . $this->user_id
                . " WHERE uf.pid <=> $parentId and uf.user_id <=> $this->user_id and uf.is_dir = 1 "
                . 'GROUP BY uf.id order by text ASC'
        );
        if (isset($data))
            $data[0]['children'] = $req->queryAll();
        else
            $data = $req->queryAll();
        echo str_replace(
                '"hasChildren":"0"', '"hasChildren":false', CTreeView::saveDataAsJson($data)
        );
        exit();
    }

    public function actionRemove() {
        echo "remove";
        var_dump($_POST);
    }

    private function GetServer($stype) {

        //$zone = CZones::model()->F
        $zone = 0;

        $server = CServers::model()->findByAttributes(array('zone_id' => $zone, 'stype' => $stype));
        if ($server['alias'] == '')
            return $server['ip'];
        else
            return $server['alias'];
    }

    public function actionIndex() {
        $UPLOAD_SERVER = 2;
        $sid = CUser::model()->findByPk($this->user_id)->getAttribute('sess_id');
        $kpt = md5($this->user_id . $sid . "I am robot");
        $server = $this->GetServer($UPLOAD_SERVER);
        $this->render('view', array('user_id' => $this->user_id, 'sid' => $sid, 'kpt' => $kpt,'server'=>$server));
    }

    public function actionDownload() {
        $fid = (int) $_GET['fid'];
        if ($fid > 0)
            $item = CUserfiles::model()->findByPk(array('user_id' => $this->user_id, 'id' => $fid));
        if ($item->is_dir == 0) {
            $server = CFilelocations::model()->findAllByPK(array('user_id' => $this->user_id, 'id' => $fid));
            echo "it's file aviable on " . $server['id'];
        } else {
            echo "It's not aviable to download folder via browser for this moment";
        }
        exit();
    }

}

?>
