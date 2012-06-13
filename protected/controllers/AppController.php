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
            if (isset($_REQUEST['offset'])) {

                $page = (int)((int)$_REQUEST['offset'] / $per_page) + 1;
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
                        $data = array('title' => $res['title'], 'poster' => $res['poster'], 'link' => $link, 'variant_id'=>$res['variant_id'], 'description' => $res['description']);
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

    public function actionPartnerItemList()
    {
        if (Yii::app()->user->id) {
            $per_page = 10;
            if (isset($_POST['offset'])) {
                $page = (int)((int)$_POST['offset'] / $per_page) + 1;
            } else {
                $page = 0;
            }
            $partner_id = 0;
            if (isset($_REQUEST['partner_id']))
                $partner_id = (int)$_REQUEST['partner_id'];
            $search = '';
            if (isset($_REQUEST['search']))
                $search = filter_var($_REQUEST['search'], FILTER_SANITIZE_STRING);
            $list = CAppHandler::getPartnerProductsForUser($search, $partner_id, $page);

            $count = count($list);
            $total_count = CAppHandler::CountPartnerProductsForUser($search, $partner_id);
            echo json_encode(array('cmd' => "PartnerData", 'error' => 0, 'Data' => $list, 'count' => $count, 'total_count' => $total_count));
        }
    }

    public function actionPartnerItemData()
    {
        if (Yii::app()->user->id) {
            $partner_id = 0;
            if (isset($_REQUEST['partner_id']))
                $partner_id = (int)$_REQUEST['partner_id'];
            $item_id = 0;
            if (isset($_REQUEST['item_id'])) // Should be variant_id
                $item_id = (int)$_REQUEST['item_id'];
            if ($item_id && $partner_id) {
                $list = CAppHandler::getProductFullInfo($item_id);
                if ($res = $list->read()) {
                    //$data = array('title' => $res['title'], 'poster' => $res['poster'], 'link' => $link, 'description' => $res['description']);
                    echo json_encode(array('cmd' => "PartnerItemData", 'error' => 0, 'Data' => $res));
                }
            }
        }
    }

    public function actionAddItemFromPartner(){
        if (Yii::app()->user->id){
            if ($_REQUEST['variant_id']){
                $variant_id =(int)$_REQUEST['variant_id'];
                if ($res=CAppHandler::addProductToUser($variant_id)){
                    echo json_encode(array('cmd'=>"AddItemFromPartner",'error'=> 0));
                } else
                    echo json_encode(array('cmd'=>"AddItemFromPartner",'error'=> 1));
            }
                else
                    echo json_encode(array('cmd'=>"AddItemFromPartner",'error'=> 1,"error_msg" => 'Unknown item'));
        } else
             echo json_encode(array('cmd'=>"AddItemFromPartner",'error'=> 1,"error_msg" => 'Unknown user'));

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

    public function actionGetWindow($wid = 0)
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


}
