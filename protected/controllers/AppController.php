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
            echo '<pre>';
            var_dump($error);
            echo '</pre>';
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
                $_SESSION['device_id'] = $device->id;
                $_SESSION['device_preset'] = $device->max_preset;

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
                echo json_encode(array('cmd' => 'Login', 'error' => 1, 'error_msg' => Yii::t('app', 'Unknown user')));
                Yii::app()->end();
            }
            echo json_encode(array('LoginData' => $username . ' ' . $password));
        } else {
            echo json_encode(array('cmd' => 'Login', 'error' => 1, 'error_msg' => Yii::t('app', 'Unknown user')));
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
            Yii::app()->user->logout();
        }
        echo json_encode(array('error' => 0, "msg" => "Bye"));
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
            $search = '';
            if (isset($_REQUEST['search']))
                $search = trim(filter_var($_REQUEST['search'], FILTER_SANITIZE_STRING));
            $list = CAppHandler::getVtrList(Yii::app()->user->id, 1, $page, $per_page);
            $total_count = CAppHandler::countVtrList(Yii::app()->user->id, 1);
            $count = count($list);
            echo json_encode(array('cmd' => "FilmList", 'error' => 0, 'Data' => $list, 'count' => $count, 'total_count' => $total_count,'search'=>$search));
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
                if(isset($_SESSION['device_preset'])){
                    $preset = $_SESSION['device_preset'];
                } else $preset = 0;
                $list = CAppHandler::getVtrItemA($fc_id, Yii::app()->user->id,$preset);

                if ($res = $list->read()) {
                    if ($res['fname']) {
                        $partnerInfo = Yii::app()->db->createCommand()
                            ->select('prt.id, prt.title, prt.sprintf_url, p.original_id')
                            ->from('{{products}} p')
                            ->join('{{partners}} prt', 'prt.id = p.partner_id')
                            ->where('p.id = ' . $res['product_id'])->queryRow();
                        switch ($res['partner_id']) {
                            case 2:
                                $link = Yii::app()->params['tushkan']['safelib_video'] . $res['fname'][0] . '/' . $res['fname'];
                                break;
                            case 1:
                                $fn = pathinfo($res['fname'], PATHINFO_FILENAME).'mp4';
                                $link = sprintf($partnerInfo['sprintf_url'], $partnerInfo['original_id'], 'low', $fn, 1);
                                break;
                            default:
                                echo json_encode(array('cmd' => "FilmData", 'error' => 1, 'error_msg' => 'unknown partner'));
                                Yii::app()->end();
                        }
                        $res['link'] = $link;
                        echo json_encode(array('cmd' => "FilmData", 'error' => 0, 'Data' => $res));

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

    public function actionFilmLink()
    {
        if (Yii::app()->user->id) {
            if (isset($_REQUEST['fc_id'])) {
                $fc_id = (int)$_REQUEST['fc_id'];
                if(isset($_SESSION['device_preset'])){
                    $preset = $_SESSION['device_preset'];
                } else $preset = 0;
                $list = CAppHandler::getVtrItemA($fc_id, Yii::app()->user->id,$preset);
		    	$zFlag = Yii::app()->user->UserInZone;
		    	$zSql = '';
		    	if (!$zFlag)
		    	{
		    		$zSql = ' AND p.flag_zone = 0';
		    	}

                if ($res = $list->read()) {
                    if ($res['fname']) {
                        $partnerInfo = Yii::app()->db->createCommand()
                            ->select('prt.id, prt.title, prt.sprintf_url, p.original_id')
                            ->from('{{products}} p')
                            ->join('{{partners}} prt', 'prt.id = p.partner_id')
                            ->where('p.id = ' . $res['product_id'] . $zSql)->queryRow();
                        $fn =  pathinfo($res['fname'], PATHINFO_FILENAME).'mp4';
                        switch ($res['partner_id']) {
                            case 2:
                                $link = Yii::app()->params['tushkan']['safelib_video'] . $res['fname'][0] . '/' . $res['fname'];
                                break;
                            case 1:
                                $link = sprintf($partnerInfo['sprintf_url'], $partnerInfo['original_id'], 'low', $fn, 0);
                                break;
                            default:
                                echo json_encode(array('cmd' => "FilmData", 'error' => 1, 'error_msg' => 'unknown partner'));
                                Yii::app()->end();
                        }
                        $data = array('id' => $res['id'], 'link' => $link);
                        echo json_encode(array('cmd' => "FilmLink", 'error' => 0, 'Data' => $data));

                    } else
                        echo json_encode(array('cmd' => "FilmLink", 'error' => 1, 'error_msg' => 'Not found file'));
                } else
                    echo json_encode(array('cmd' => "FilmLink", 'error' => 1, 'error_msg' => 'Not found partner data'));
            } else {
                echo json_encode(array('cmd' => "FilmLink", 'error' => 1, 'error_msg' => 'Unknown item'));
            }
        } else {
            echo json_encode(array('cmd' => "FilmLink", 'error' => 1, 'error_msg' => 'Please Login'));
        }
    }


    public function actionPartnerList()
    {
        if (Yii::app()->user->id) {
        	$userPower = Yii::app()->user->UserPower;
            $list = CAppHandler::getPartnerList($userPower);
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
//            Yii::log(implode(',',$_SERVER),CLogger::LEVEL_ERROR);
//           Yii::log(implode(',',array_keys($_SERVER)),CLogger::LEVEL_ERROR);
//	    Yii::log(implode(',',$_REQUEST),CLogger::LEVEL_ERROR);
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
            echo json_encode(array('cmd' => "PartnerData", 'error' => 0, 'Data' => $list, 'count' => $count, 'total_count' => $total_count, 'search' => $search));
        }
    }

    public function actionPartnerItemData()
    {
        if (Yii::app()->user->id) {
            $partner_id = 0;
            if (isset($_REQUEST['partner_id']))
                $partner_id = (int)$_REQUEST['partner_id'];
            $variant_id = 0;
            if (isset($_REQUEST['variant_id'])) // Should be variant_id
                $variant_id = (int)$_REQUEST['variant_id'];
            if ($variant_id && $partner_id) {
                $list = CAppHandler::getProductFullInfo($variant_id);
                if ($res = $list->read()) {
                    //$data = array('title' => $res['title'], 'poster' => $res['poster'], 'link' => $link, 'description' => $res['description']);
                    echo json_encode(array('cmd' => "PartnerItemData", 'error' => 0, 'Data' => $res));
                }
            }
        }
    }

    public function actionAddItemFromPartner()
    {
        if (Yii::app()->user->id) {
            if (isset($_REQUEST['variant_id'])) {
                $variant_id = (int)$_REQUEST['variant_id'];
                if ($res = CAppHandler::addProductToUser($variant_id)) {
                    echo json_encode(array('cmd' => "AddItemFromPartner", 'error' => 0, 'cloud_id' => $res));
                } else
                    echo json_encode(array('cmd' => "AddItemFromPartner", 'error' => 1 , 'err_code' => $res));
            } else
                echo json_encode(array('cmd' => "AddItemFromPartner", 'error' => 1, "error_msg" => 'Unknown item'));
        } else
            echo json_encode(array('cmd' => "AddItemFromPartner", 'error' => 1, "error_msg" => 'Unknown user'));

    }

    public function actionRemoveFromMe()
    {
        if (Yii::app()->user->id) {
            if (isset($_REQUEST['id'])) {
                $item_id = (int)$_REQUEST['id'];
                CAppHandler::removeFromUser($item_id);
                echo json_encode(array('cmd' => "RemoveFromMe", 'error' => 0));
            } else echo json_encode(array('cmd' => "RemoveFromMe", 'error' => 1, "error_msg" => 'Unknown item'));
        } else
            echo json_encode(array('cmd' => "RemoveFromMe", 'error' => 1, "error_msg" => 'Unknown user'));
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

    public function actionSetDeviceParams(){
        if (Yii::app()->user->id) {
            if (isset($_SESSION['device_id'])) {
                $device_id = $_SESSION['device_id'];
                $device = CDevices::model()->find('id=:id',array(':id'=>$device_id));
                /** @var CDevices $device  */
                if($device){
                    if (isset($_REQUEST['quality']))
                        $device->max_preset= (int) $_REQUEST['quality'];
                    $device->save();
                }
            }
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

    public function actionRegister()
    {
        $this->layout = 'app';
        $model = new SLFormRegister();
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'register-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
        if (isset($_POST['SLFormRegister'])) {
            $model->attributes = $_POST['SLFormRegister'];
            if ($model->validate() && $model->register()) {
                $msg = Yii::t('user', 'Please, confirm your email. Instructions sended to ') . $model->email;
                $this->render('/app/messages', array('msg' => $msg));
                Yii::app()->end();
            } else
                $this->render('register', array('model' => $model));
        } else
            $this->render('register', array('model' => $model));
    }

    public function actionConfirm($user_id = 0, $hash = '')
    {
        if ($hash != '') {
            $hash = filter_var($hash, FILTER_SANITIZE_STRING);
            $user_id = (int)$user_id;
            $msg = '';
            if ($user_id) {
                $user = CUser::model()->findByPk($user_id);
                if ($user){
                    if ($hash == CUser::makeHash($user['pwd'], $user['salt'])) {
                        $user->confirmed = 1;
                        if ($user->save()) {
                            $msg = Yii::t('user', 'Yours email is confirmed') . '!';
                            $this->render('/app/messages', array('msg' => $msg));
                            Yii::app()->end();
                        } else
                            $msg = 'Error: saving data';
                    } else
                        $msg = 'Error: unknown hash';
                } else $msg = 'Error: unknown user';
            } else
                $msg = 'Error: unknown user';
            $msg .= '<p>Yours mail is not confirmed</p>';
        } else
            $msg = 'No confirm data';
        $this->render('/app/messages', array('msg' => $msg));
        Yii::app()->end();

    }

    public function actionResetPassword($hash = '',$user_id =0)
    {
        $this->layout = 'app';
        if ($hash == '') {
            $model = new SLFormResetPassword();
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'register-form') {
                echo CActiveForm::validate($model);
                Yii::app()->end();
            }
            if (isset($_POST['SLFormResetPassword'])) {
                $model->attributes = $_POST['SLFormResetPassword'];
                if ($model->validate() && $model->resetpassword()) {
                    $msg = Yii::t('user', 'Instructions sended to ') . $model->email;
                    $this->render('/app/messages', array('msg' => $msg));
                    Yii::app()->end();
                } else
                    $this->render('resetPassword', array('model' => $model));
            } else
                $this->render('resetPassword', array('model' => $model));
        } else{
            if ($user_id>0 && CUser::checkMagicKeyForUser($user_id,$hash)){
                $model = new SLFormConfirmReset();
                if (isset($_POST['ajax']) && $_POST['ajax'] === 'register-form') {
                    echo CActiveForm::validate($model);
                    Yii::app()->end();
                }
                if (isset($_POST['SLFormConfirmReset'])) {
                    $model->attributes = $_POST['SLFormConfirmReset'];
                    $model->id = $user_id;
                    if ($model->validate() && $model->setPassword()) {

                        $msg = Yii::t('user', 'Password changed ');
                        $this->render('/app/messages', array('msg' => $msg));
                        Yii::app()->end();
                    } else
                        $this->render('resetPasswordConfirm', array('model' => $model));
                } else
                    $this->render('resetPasswordConfirm', array('model' => $model));
            } else
                $this->render('/app/messages', array('msg' => "Unknown user"));
        }

    }


}
