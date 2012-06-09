<?php

/**
 *  AppController
 * @author Snow
 *
 */
class AppController extends ControllerApp
{

    public $layout = '//layouts/ajax';


    public function beforeAction($action)
    {
        parent::beforeAction($action);
        return true;
    }

    public function actionError()
    {
        if ($error = Yii::app()->errorHandler->error) {
            // echo json_encode(array("Error"=>$error));
            /// echo"<html><body><pre>".var_dump($error)."</pre></body><html>";
            var_dump($error);
        }
    }

    public function actionLogin()
    {
        /*
        if (!Yii::app()->user->isGuest) {
            echo json_encode(array('Login'=>'OK'));
            Yii::app()->end();
        }
        */

        // TO DO : $_POST instead $_REQUEST

        if (isset($_REQUEST['username']) && isset($_REQUEST['password'])) {
            $username = filter_var($_REQUEST['username']);
            $password = filter_var($_REQUEST['password']);
            $identity = new UserIdentityApp($username, $password);
            $identity->authenticate();

            if ($identity->errorCode === UserIdentity::ERROR_NONE) {
                Yii::app()->user->login($identity, 0);
                $criteria = new CDbCriteria();
                $criteria->alias = 'u';
                $criteria->select = 'u.*';

                $criteria->condition = 'u.email="' . Yii::app()->user->email . '"';
                //    $criteria->join = 'LEFT JOIN {{site_roles}} sr on sr.site_role_id = u.site_role_id';
                $user = CUser::model()->getCommandBuilder()
                    ->createFindCommand(CUser::model()->tableSchema, $criteria)
                    ->queryRow();
                if (isset($_REQUEST['guid'])) {
                    $guid = $_REQUEST['guid'];
                } else {
                    $guid = CDevices::generateGUID(time());
                }
                $device = CDevices::model()->findByAttributes(array('guid' => $guid, 'user_id' => Yii::app()->user->id));
                if (!$device) {
                    //User hasn't device yet so adding
                    $device = new CDevices();
                    //$device->device_type_id= CDevices::DeviceTypeByInfo($info);
                    $device->guid = $guid;
                    $device->user_id = Yii::app()->user->id;
                    $device->generateDeviceLoginHash();
                    $device->save();
                }
                //  Yii::app()->db->createCommand('UPDATE {{users}} SET last_ip = "' . Yii::app()->request->getUserHostAddress() . '" where user_id=' . $user['user_id'])->execute();

                //     if (($user['confirmed_email'] == 1) && (Yii::app()->params['email_confirm']==true)) {

                //    } else {
                /*  Yii::app()->user->logout();
                  echo json_encode(Yii::t('user','Your mail isn\'t confirmed. Please confirm it. If you didn\'t get email just restore your password.'));
                  return false;
            //  }
                */
                //Yii::app()->user->setState('role', $user['site_role_title']);
                echo json_encode(array('cmd' => 'Login', 'error' => 0, 'user_id' => Yii::app()->user->id, 'guid' => $device->guid,
                    'hash' => $device->hash));
                return true;
            } else {
                echo json_encode(array('cmd' => 'Login', 'error' => 1, 'error_msg' => "unknown user"));
                Yii::app()->end();
            }
            echo json_encode(array('LoginData' => $username . ' ' . $password));
        } else {
            echo json_encode(array('cmd' => 'Login', 'error' => 1, 'error_msg' => "no data"));

        }
        /*
          if (isset($_SERVER['HTTPS']) && !strcasecmp($_SERVER['HTTPS'], 'on')) {
              $username = $_POST['username'];
              $password = $_POST['password'];
          } else {
        */
        //$this->redirect('https://' . getenv('HTTP_HOST') . '/app/login');
        // }
    }


    public function actionLogout()
    {
        if (Yii::app()->user->id) {
            Yii::app()->user->logout;
        }
    }

    public function actionFilmList()
    {
        if (Yii::app()->user->id) {
            $per_page = 10;
            if (isset($_POST['offset'])) {

                $page = (int)((int)$_POST['offset'] / $per_page) + 1;
            } else {
                $page = 0;
            }
            $list = CAppHandler::getVtrList(Yii::app()->user->id, 1, $page, $per_page);
            $total_count = CAppHandler::countVtrList(Yii::app()->user->id, 1);
            $count = count($list);
            echo json_encode(array('cmd' => "FilmList", 'error' => 0, 'Data' => $list, 'count' => $count, 'total_count' => $total_count));
        } else {
            echo json_encode(array('cmd' => 'FilmList', 'error' => 1, 'error_msg' => 'Please login'));
        }
    }

    public function actionFilmSearch()
    {
        if (Yii::app()->user->id && isset($_REQUEST['search'])) {
            $per_page = 10;
            if (isset($_POST['offset'])) {
                $page = (int)((int)$_POST['offset'] / $per_page) + 1;
            } else {
                $page = 0;
            }
            $search = filter_var($_REQUEST['search'], FILTER_SANITIZE_STRING);
            $list = CAppHandler::findUserProducts($search, Yii::app()->user->id, 1, $page, $per_page);
            $total_count = CAppHandler::countFoundProducts($search, Yii::app()->user->id, 1);
            $count = count($list);
            echo json_encode(array('cmd' => "FilmList", 'error' => 0, 'Data' => $list, 'total_count' => $total_count, 'count' => $count, 'search' => $search));
        } else {
            echo json_encode(array('cmd' => 'FilmList', 'error' => 1, 'error_msg' => 'Please login'));
        }
    }

    public function actionFilmData()
    {
        if (Yii::app()->user->id) {
            if (isset($_REQUEST['fc_id'])) {
                $fc_id = (int)$_REQUEST['fc_id'];
                $list = CAppHandler::getVtrItemA($fc_id, Yii::app()->user->id);
                if ($res = $list->read()) {
                    if ($res['fname']) {
                        $partnerInfo = Yii::app()->db->createCommand()
                            ->select('prt.id, prt.title, prt.sprintf_url, p.original_id')
                            ->from('{{products}} p')
                            ->join('{{partners}} prt', 'prt.id = p.partner_id')
                            ->where('p.id = ' . $res['product_id'])->queryRow();
                        switch ($res['partner_id']) {
                            case 2:
                                $link = 'http://212.20.62.34:82/' . $res['fname'][0] . '/' . $res['fname'];
                                break;
                            case 1:
                                $fn = basename($res['fname'], PATHINFO_FILENAME);
                                $link = sprintf($partnerInfo['sprintf_url'], $partnerInfo['original_id'], 'low', $fn, 1);
                                break;
                            default:
                                echo json_encode(array('cmd' => "FilmData", 'error' => 1, 'error_msg' => 'unknown parnter'));
                                Yii:
                                app()->end();
                        }
                        $data = array('title' => $res['title'], 'poster' => $res['poster'], 'link' => $link, 'description' => $res['description']);
                        echo json_encode(array('cmd' => "FilmData", 'error' => 0, 'Data' => $data));

                    } else
                        echo json_encode(array('cmd' => "FilmData", 'error' => 1, 'error_msg' => 'Not found file'));
                } else
                    echo json_encode(array('cmd' => "FilmData", 'error' => 1, 'error_msg' => 'Not found partner data'));
            } else {
                echo json_encode(array('cmd' => "FilmData", 'error' => 1, 'error_msg' => 'Unknown item'));
            }
        } else {
            echo json_encode(array('cmd' => "FilmData", 'error' => 1, 'error_msg' => 'Please Login'));
        }
    }

    public function actionPartnerList()
    {
        if (Yii::app()->user->id) {
            $list = CAppHandler::getPartnerList(Yii::app()->user->UserPower);
            $count = count($list);
            $total_count = $count;
            foreach ($list as $item) {
                $item['image'] = '';
            }
            echo json_encode(array('cmd' => "PartnerList", 'error' => 0, 'Data' => $list, 'count' => $count, 'total_count' => $total_count));
        } else {
            json_encode(array('cmd' => "PartnerList", "error" => 1, "error_msg" => 'Please login'));
        }
    }

    public function actionPartnerItemList($id = 0)
    {
        if (Yii::app()->user->id) {
            $partner_id = (int)$id;
            if ($partner_id) {
                $paramIds = array(10, 12, 13, 14);
                $search = '';
                if (isset($_REQUEST['search']))
                    $search = filter_var($_REQUEST['search'], FILTER_SANITIZE_STRING);
                $list = CAppHandler::getPartnerProductsForUser($paramIds, Yii::app()->user->UserPower, $search, $partner_id);
                $count = count($list);
                $total_count = $count;
                foreach ($list as $item) {
                    $item['image'] = '';
                }
                echo json_encode(array('cmd' => "PartnerData", 'error' => 0, 'Data' => $list, 'count' => $count, 'total_count' => $total_count));
            }
        }
    }

    public function actionPartnerSearch()
    {
        if (Yii::app()->user->id && isset($_REQUEST['search'])) {
            $per_page = 10;
            if (isset($_POST['offset'])) {
                $page = (int)((int)$_POST['offset'] / $per_page) + 1;
            } else {
                $page = 0;
            }
            $search = filter_var($_REQUEST['search'], FILTER_SANITIZE_STRING);
        }
    }

    public function actionSearchPartners()
    {

    }


    public function actionGetList($cid = 0)
    {
        //Echo Categories
        if ($cid == 0) {
            $result = array("Category" => "OK");
            $categoryList = array();
            $categoryList['Video'] = 1;
            // $categoryList['Audio']=0;
            // $categoryList['Docs']=1;
            $result['list'] = $categoryList;

            echo json_encode($result);
            Yii::app()->end();
        } else {
            $result = array("Cat_list" => "OK");
        }
    }

    public  function actionGetWindow($wid = 0)
    {
        if ($wid == 0) {
            //Display list
        } else {
        }

    }


    public function actionGetSettings()
    {

    }


    public function actionGetUserInfo()
    {
        //$user= CUser::model()->getU
        echo "No info";
    }

    public function actionGetDirTree()
    {
        //$dirs = CUserfiles::model()->getDirTree($user_id);

    }

    public function actionGetFileList()
    {
        $pid = 0;
        //  $files = CUserfiles::model()->getFileList($this->user_id, $pid);

    }

    /*

    public function actionCreate() {
        $pid = (int) $_POST['pid'];
        $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
        $flag_dir = (int) $_POST['flag_dir'];
        $files = new CUserfiles();
        $files->title = $title;
        $files->pid = $pid;
        $files->is_dir = 0;
        $files->user_id = Yii::app()->user->id;
        $files->save();

    }


    public function actionMove() {
        $id = (int) $_POST['id'];
        $new_pid = (int) $_POST['new_pid'];
        $category = (int) $_POST['category'];
//Check is directory exists
        $place = CUserfiles::model()->findByPk(array('id' => $id, 'user_id' => $this->user_id));
        if (($place) && ($place->is_dir)) {
            $files = CUserfiles::model()->findByPk(array('id' => $id, 'user_id' => $this->user_id));
            if ($files) {
                $files->pid = $new_pid;
                $files->save();
                echo "OK: Moved";
            }else
                echo "ERROR: Unknown file";
        }else
            echo "ERROR: Unknown place";
    }

    public function actionRename() {
        $id = (int) $_POST['id'];
        $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
        $files = CUserfiles::model()->findByPk(array('id' => $id, 'user_id' => $this->user_id));
        if ($files) {
            $files->title = $title;
            $files->save();
            echo "OK: Renamed";
        }
        else
            echo "ERROR: unknown file";
    }

    public function actionDelete() {
        $id = (int) $_POST['id'];
        $files = CUserfiles::model()->findByPk(array('id' => $id, 'user_id' => $this->user_id));
        if ($files) {
            $files->delete();
            echo "OK: Deleted";
        }
        else
            echo "ERROR: unknown file";
    }

    public function actionGetUpdatesCmdList() {
        echo "No Updates";
    }



    public function actionSetSyncSettings() {
        if (isset($_POST['data'])) {
            $data = $_POST['data'];
        }else
            echo "ERROR: no data";
    }
    */

}
