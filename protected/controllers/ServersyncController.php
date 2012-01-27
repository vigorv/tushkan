<?

class ServersyncController extends Controller {

    var $layout = 'ajax';

    public function beforeAction($action) {
        parent::beforeAction($action);
        $shash = $_GET['shash'];
        $hash_local = md5(date('%h%d') . 'where am i');
        if ($shash <> $hash_local) {
            return false;
        }
        return true;
    }

    public function actionUserdata($user_id=0) {
        if ($user_id > 0) {
            $id = (int) $user_id;
            $sid = CUser::model()->findByPk($user_id)->getAttribute('sess_id');
            echo $sid;
            if (count($sid)) {
                $kpt = md5($id . $sid[0]['sess_id'] . "I am robot");
                echo $kpt;
            }
        }
        exit();
    }

    public function actionFiledata($user_id=0) {
        if ($user_id > 0) {
            $id = (int) $user_id;
            $fid = (int) $_GET['fid'];
            $file = CUserfiles::model()->findByPk(array('user_id' => $id, 'id' => $fid));
            $filedata = array();
            $filedata['fname'] = $file->fname;
            $filedata['title'] = $file->title;
            echo (serialize($filedata));
        }
        exit();
    }

    public function actionCreate($user_id=0, $title='', $pid=0, $is_dir=0) {
        if ($user_id > 0) {
            $cur_file = CUserfiles::model()->findAllByAttributes(array('user_id' => $user_id, 'title' => $title, 'pid' => $pid));
            if (!$cur_file['id']) {
                $files = new CUserfiles();
                $files->title = $title;
                $files->pid = $pid;
                $files->is_dir = $is_dir;
                $files->user_id = $user_id;
                $files->save();
            }
        }
    }

    public function actionUpload($user_id=0, $data='') {
        if ($user_id > 0) {
            //OK 
            //WHat is server doing this
            $ip = $_SERVER['REMOTE_ADDR'];
            $server = CServers::model()->findByAttributes(array('ip' => ip2long($ip), 'stype' => 2));
            if ($server === null)
                die('Unknown Server');
            $input = unserialize($data);
            $new_title = $input['filename'];
            $cur_file = CUserfiles::model()->findAllByAttributes(array('user_id' => $user_id, 'title' => $input['filename'], 'pid' => $input['pid']));
            $i = 1;
            while (count($cur_file)) {
                $new_title = pathinfo($input['filename'], PATHINFO_FILENAME) . $i . '.' . pathinfo($input['filename'], PATHINFO_EXTENSION);
                $cur_file = CUserfiles::model()->findAllByAttributes(array('user_id' => $user_id, 'title' => $new_title, 'pid' => $input['pid']));
                $i++;
            }
            $files = new CUserfiles();
            $files->title = $new_title;
            $files->pid = $input['pid'];
            $files->fsize = $input['fsize'];
            $files->user_id = $user_id;
            $files->fname = $input['save'];
            $files->save();
            $fileloc = new CFilelocations();
            $fileloc->id = $files->id;
            $fileloc->server_id=$server->id;
            $fileloc->user_id = $user_id;
            $fileloc->fsize = $files->fsize;
            $fileloc->save();
            echo "OK";
            exit();
        } else
            die("Bad User");
    }

}