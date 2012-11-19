<?php

/**
 *  AppController
 * @author Snow
 *
 */
class AppController extends ControllerApp
{
    public $layout = '//layouts/none';
    private $offset = 0;
    private $page = 0;
    private $per_page = 10;
    private $search = '';
    const ERROR_NONE = 0;
    const ERROR_USER_NOT_EXISTS = 1;
    const ERROR_INPUT_DATA_FAILED = 2;
    const ERROR_REGISTER_EMAIL_EXISTS = 3;
    const ERROR_USER_NEED_LOGIN = 4;
    const ERROR_UNKNOWN_ITEM = 5;
    const ERROR_UNKNOWN_CATEGORY = 6;
    const ERROR_UNKNOWN_PARTNER = 7;

    const CONTENT_TYPE_VIDEO = 1;
    const CONTENT_TYPE_AUDIO = 2;
    const CONTENT_TYPE_GAMES = 3;
    const CONTENT_TYPE_DOCS = 4;
    const CONTENT_TYPE_PICTURES = 5;

    const SECTION_PARTNERS = 1;
    const SECTION_PARTNER_CATALOG = 2;
    const SECTION_UNIVERSE_CATALOG_PARTNER = 3;
    const SECTION_UNIVERSE_CATALOG_TYPED = 4;
    const SECTION_UNIVERSE_CATALOG_QUEUED = 5;
    const SECTION_UNIVERSE_CATALOG_UNTYPED = 6;

    public function beforeAction($action)
    {
        parent::beforeAction($action);
        if (isset($_REQUEST['offset'])) {
            $this->offset = (int)$_REQUEST['offset'];
            $this->page = (int)($this->offset / $this->per_page) + 1;
        }
        if (isset($_REQUEST['search']))
            $this->search = trim(filter_var($_REQUEST['search'], FILTER_SANITIZE_STRING));

        switch (strtolower($action->id)) {
            case 'register':
            case 'login':
            case 'error':
            case 'registercheck':
            case 'resetpassword':
                break;
            default:
                if (!Yii::app()->user->id) {
                    $result = array('error' => self::ERROR_USER_NEED_LOGIN, 'error_msg' => Yii::t('app', 'User need to be logged in'));
                    echo json_encode($result);
                    return false;
                }
        }
        return true;
    }

    public function afterAction($action)
    {
        if (defined('YII_DEBUG') && YII_DEBUG)
            $this->render('/admin/none');
        parent::afterAction($action);
    }

    public function actionError()
    {
        if ($error = Yii::app()->errorHandler->error) {
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
            // 1. Check UserData
            $username = filter_var($_REQUEST['username']);
            $password = filter_var($_REQUEST['password']);
            $identity = new UserIdentityApp($username, $password);
            $identity->authenticate();

            if ($identity->errorCode === UserIdentity::ERROR_NONE) {
                // 2. User Login
                Yii::app()->user->login($identity);

                // 3. Check device
                if (isset($_REQUEST['guid'])) {
                    $guid = $_REQUEST['guid'];
                } else {
                    $guid = CDevices::generateGUID(time());
                }
                $device = CDevices::model()->findByAttributes(array('guid' => $guid, 'user_id' => Yii::app()->user->id));

                // 4. if is new assign device to account
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
                echo json_encode(array('cmd' => 'Login', 'error' => self::ERROR_NONE, 'user_id' => Yii::app()->user->id, 'guid' => $device->guid,
                    'hash' => $device->hash));
                return;
            } else {
                echo json_encode(array('cmd' => 'Login', 'error' => self::ERROR_USER_NOT_EXISTS, 'error_msg' => Yii::t('app', 'Unknown user')));
                Yii::app()->end();
            }
            echo json_encode(array('LoginData' => $username . ' ' . $password));
        } else {
            echo json_encode(array('cmd' => 'Login', 'error' => self::ERROR_INPUT_DATA_FAILED, 'error_msg' => Yii::t('app', 'Unknown user')));
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

    public function actionRegisterCheck()
    {
        if (isset($_REQUEST['email'])) {
            // 1. Check UserData
            $email = filter_var($_REQUEST['email']);
            $password = filter_var($_REQUEST['password']);
            $user = CUser::model()->find('email= :email', array(':email' => $email));
            /* @var CUser $user */
            if (!$user) {
                $result = array('error' => self::ERROR_NONE);
            } else
                $result = array('error' => self::ERROR_REGISTER_EMAIL_EXISTS);
        } else
            $result = array('error' => self::ERROR_INPUT_DATA_FAILED);
        echo json_encode($result);
    }

    public function actionLogout()
    {
        if (Yii::app()->user->id) {
            Yii::app()->user->logout();
        }
        echo json_encode(array('error' => self::ERROR_NONE, "msg" => "Bye"));
    }

    /* API 3.0 */

    public function getCategoryList(){

    }

    public function getSectionList(){

    }

    public function getCatalogList(){
        if (isset($_REQUEST['section']) && isset($_REQUEST['category'])){
            $section = (int) $_REQUEST['section'];
            $category = (int) $_REQUEST['category'];
            switch ($section){
                case self::SECTION_PARTNERS:

                    $userPower = Yii::app()->user->UserPower;
                    $list = CPartners::getPartnerListA($userPower);
                    $count = count($list);
                    $total_count = $count;
                    foreach ($list as $item) {
                        $item['image'] = '';
                    }
                    echo json_encode(array('cmd' => "PartnerList", 'error' => self::ERROR_NONE, 'Data' => $list, 'count' => $count, 'total_count' => $total_count));

                    break;
                case self::SECTION_PARTNER_CATALOG:
                    isset($_REQUEST['partner_id'])? $partner_id = (int) $_REQUEST['partner_id'] : $partner_id =0;
                    $list = CProduct::getPartnerProductsForUser($this->search, $partner_id, $this->page, $this->per_page);
                    $count = count($list);
                    $total_count = CProduct::CountPartnerProductsForUser($this->search, $partner_id);
                    echo json_encode(array('cmd' => "PartnerProducts", 'error' => self::ERROR_NONE, 'Data' => $list, 'count' => $count, 'total_count' => $total_count, 'search' => $this->search));

                    break;
                case self::SECTION_UNIVERSE_CATALOG_PARTNER:

                    $list = CAppHandler::findUserProducts($this->search, Yii::app()->user->id, $category, $this->page, $this->per_page);
                    $total_count = CAppHandler::countFoundProducts($this->search, Yii::app()->user->id, self::CONTENT_TYPE_VIDEO);
                    $count = count($list);
                    $result = array('cmd' => "ItemList", 'error' => self::ERROR_NONE, 'Data' => $list, 'count' => $count, 'total_count' => $total_count, 'search' => $this->search);
                    echo json_encode($result);

                    break;
                case self::SECTION_UNIVERSE_CATALOG_TYPED:

                    $list = CUserObjects::findObjects($this->search, Yii::app()->user->id, $category, $this->page, $this->per_page);
                    $total_count = CUserObjects::countFoundObjects($this->search, Yii::app()->user->id, self::CONTENT_TYPE_VIDEO);
                    $count = count($list);
                    $result = array('cmd' => "UserLibraryList", 'error' => self::ERROR_NONE, 'Data' => $list, 'count' => $count, 'total_count' => $total_count, 'search' => $this->search);
                    echo json_encode($result);

                    break;
                case self::SECTION_UNIVERSE_CATALOG_QUEUED:

                    $list = CConvertQueue::findObjects($this->search, Yii::app()->user->id, $this->page, $this->per_page);
                    $total_count = CConvertQueue::countFoundObjects($this->search, Yii::app()->user->id);
                    $count = count($list);
                    $result = array('cmd' => "UserQueueList", 'error' => self::ERROR_NONE, 'Data' => $list, 'count' => $count, 'total_count' => $total_count, 'search' => $this->search);
                    echo json_encode($result);

                    break;
                case self::SECTION_UNIVERSE_CATALOG_UNTYPED:

                    $list = CUserfiles  ::findObjects($this->search, Yii::app()->user->id, $this->page, $this->per_page);
                    $total_count = CConvertQueue::countFoundObjects($this->search, Yii::app()->user->id);
                    $count = count($list);
                    $result = array('cmd' => "UserLibraryList", 'error' => self::ERROR_NONE, 'Data' => $list, 'count' => $count, 'total_count' => $total_count, 'search' => $this->search);
                    echo json_encode($result);

                    break;
            }
        }
    }

    public function getCatalogData(){
        if (isset($_REQUEST['section']) && isset($_REQUEST['category']) && isset($_REQUEST['item_id'])){
            $section = (int) $_REQUEST['section'];
            $category = (int) $_REQUEST['category'];
            $item_id = $_REQUEST['item_id'];

            switch ($section){
                case self::SECTION_PARTNERS:

                    break;
                case self::SECTION_PARTNER_CATALOG:

                    $data = CProduct::getProductFullInfo($item_id);
                    if (!empty($data))
                        $result = array('cmd' => "ItemData", 'error' => self::ERROR_NONE, 'Data' => $data);
                    else
                        $result = array('cmd' => "ItemData", 'error' => self::ERROR_UNKNOWN_ITEM, 'error_msg' => 'Unknown item');
                    echo json_encode($result);


                    break;
                case self::SECTION_UNIVERSE_CATALOG_PARTNER:

                    $item = CUserProduct::getUserProduct($item_id, Yii::app()->user->id);
                    if (!empty($item)) {
                        $result = array('cmd' => "ItemData", 'error' => self::ERROR_NONE, 'Data' => $item[0]);
                    } else
                        $result = array('cmd' => "ItemData", 'error' => self::ERROR_UNKNOWN_ITEM, 'error_msg' => 'Unknown item');
                    echo json_encode($result);

                    break;
                case self::SECTION_UNIVERSE_CATALOG_TYPED:

                    $data = CUserObjects::getUserObject($item_id, Yii::app()->user->id);
                    if (!empty($data)) {
                        $result = array('cmd' => "ItemData", 'error' => self::ERROR_NONE, 'Data' => $data[0]);
                    } else
                        $result = array('cmd' => "ItemData", 'error' => self::ERROR_UNKNOWN_ITEM, 'error_msg' => 'Unknown item');
                    echo json_encode($result);

                    break;
                case self::SECTION_UNIVERSE_CATALOG_QUEUED:

                    $data = CConvertQueue::getUserObject($item_id, Yii::app()->user->id);
                    if (!empty($data)) {
                        $result = array('cmd' => "UserQueueData", 'error' => self::ERROR_NONE, 'Data' => $data[0]);
                    } else
                        $result = array('cmd' => "UserQueueData", 'error' => self::ERROR_UNKNOWN_ITEM, 'error_msg' => 'Unknown item');
                    echo json_encode($result);

                    break;
                case self::SECTION_UNIVERSE_CATALOG_UNTYPED:

                    $data = CConvertQueue::getUserObject($item_id, Yii::app()->user->id);
                    if (!empty($data)) {
                        $result = array('cmd' => "UserQueueData", 'error' => self::ERROR_NONE, 'Data' => $data[0]);
                    } else
                        $result = array('cmd' => "UserQueueData", 'error' => self::ERROR_UNKNOWN_ITEM, 'error_msg' => 'Unknown item');
                    echo json_encode($result);

                    break;
            }
        }
    }








    /* API 2.0 */


    public function actionUserProductList($content_type = self::CONTENT_TYPE_VIDEO)
    {
        $list = CAppHandler::findUserProducts($this->search, Yii::app()->user->id, $content_type, $this->page, $this->per_page);
        $total_count = CAppHandler::countFoundProducts($this->search, Yii::app()->user->id, self::CONTENT_TYPE_VIDEO);
        $count = count($list);
        $result = array('cmd' => "ItemList", 'error' => self::ERROR_NONE, 'Data' => $list, 'count' => $count, 'total_count' => $total_count, 'search' => $this->search);
        echo json_encode($result);
    }

    public function actionUserProductData($item_id = 0)
    {
        $item = CUserProduct::getUserProduct($item_id, Yii::app()->user->id);
        if (!empty($item)) {
            $result = array('cmd' => "ItemData", 'error' => self::ERROR_NONE, 'Data' => $item[0]);
        } else
            $result = array('cmd' => "ItemData", 'error' => self::ERROR_UNKNOWN_ITEM, 'error_msg' => 'Unknown item');
        echo json_encode($result);
    }

    public function actionPartners()
    {
        $userPower = Yii::app()->user->UserPower;
        $list = CPartners::getPartnerListA($userPower);
        $count = count($list);
        $total_count = $count;
        foreach ($list as $item) {
            $item['image'] = '';
        }
        echo json_encode(array('cmd' => "PartnerList", 'error' => self::ERROR_NONE, 'Data' => $list, 'count' => $count, 'total_count' => $total_count));
    }

    public function actionPartnerProducts($partner_id = 0)
    {
        $list = CProduct::getPartnerProductsForUser($this->search, $partner_id, $this->page, $this->per_page);
        $count = count($list);
        $total_count = CProduct::CountPartnerProductsForUser($this->search, $partner_id);
        echo json_encode(array('cmd' => "PartnerProducts", 'error' => self::ERROR_NONE, 'Data' => $list, 'count' => $count, 'total_count' => $total_count, 'search' => $this->search));
    }

    public function actionPartnerProductData($variant_id = 0)
    {
        $data = CProduct::getProductFullInfo($variant_id);
        if (!empty($data))
            $result = array('cmd' => "ItemData", 'error' => self::ERROR_NONE, 'Data' => $data);
        else
            $result = array('cmd' => "ItemData", 'error' => self::ERROR_UNKNOWN_ITEM, 'error_msg' => 'Unknown item');
        echo json_encode($result);


    }

    public function actionUserLibraryList($content_type = self::CONTENT_TYPE_VIDEO)
    {
        $list = CUserObjects::findObjects($this->search, Yii::app()->user->id, $content_type, $this->page, $this->per_page);
        $total_count = CUserObjects::countFoundObjects($this->search, Yii::app()->user->id, self::CONTENT_TYPE_VIDEO);
        $count = count($list);
        $result = array('cmd' => "UserLibraryList", 'error' => self::ERROR_NONE, 'Data' => $list, 'count' => $count, 'total_count' => $total_count, 'search' => $this->search);
        echo json_encode($result);

    }

    public function actionUserLibraryItemData($item_id = 0)
    {
        $data = CUserObjects::getUserObject($item_id, Yii::app()->user->id);
        if (!empty($data)) {
            $result = array('cmd' => "ItemData", 'error' => self::ERROR_NONE, 'Data' => $data[0]);
        } else
            $result = array('cmd' => "ItemData", 'error' => self::ERROR_UNKNOWN_ITEM, 'error_msg' => 'Unknown item');
        echo json_encode($result);
    }

    public function actionUserQueueList()
    {
        $list = CConvertQueue::findObjects($this->search, Yii::app()->user->id, $this->page, $this->per_page);
        $total_count = CConvertQueue::countFoundObjects($this->search, Yii::app()->user->id);
        $count = count($list);
        $result = array('cmd' => "UserQueueList", 'error' => self::ERROR_NONE, 'Data' => $list, 'count' => $count, 'total_count' => $total_count, 'search' => $this->search);
        echo json_encode($result);
    }

    public function actionUserQueueData($item_id = 0)
    {
        $data = CConvertQueue::getUserObject($item_id, Yii::app()->user->id);
        if (!empty($data)) {
            $result = array('cmd' => "UserQueueData", 'error' => self::ERROR_NONE, 'Data' => $data[0]);
        } else
            $result = array('cmd' => "UserQueueData", 'error' => self::ERROR_UNKNOWN_ITEM, 'error_msg' => 'Unknown item');
        echo json_encode($result);
    }

    public function actionUserFileList()
    {
        $list = CUserfiles  ::findObjects($this->search, Yii::app()->user->id, $this->page, $this->per_page);
        $total_count = CConvertQueue::countFoundObjects($this->search, Yii::app()->user->id);
        $count = count($list);
        $result = array('cmd' => "UserLibraryList", 'error' => self::ERROR_NONE, 'Data' => $list, 'count' => $count, 'total_count' => $total_count, 'search' => $this->search);
        echo json_encode($result);
    }

    public function actionUserFileData($item_id = 0)
    {
        $data = CConvertQueue::getUserObject($item_id, Yii::app()->user->id);
        if (!empty($data)) {
            $result = array('cmd' => "UserQueueData", 'error' => self::ERROR_NONE, 'Data' => $data[0]);
        } else
            $result = array('cmd' => "UserQueueData", 'error' => self::ERROR_UNKNOWN_ITEM, 'error_msg' => 'Unknown item');
        echo json_encode($result);

    }


    /* --- API 2.0 END --- */

    public
    function actionFilmList()
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
            $list = CAppHandler::findUserProducts($search, Yii::app()->user->id, self::CONTENT_TYPE_VIDEO, $page, $per_page);
            $total_count = CAppHandler::countFoundProducts($search, Yii::app()->user->id, self::CONTENT_TYPE_VIDEO);
            $count = count($list);
            echo json_encode(array('cmd' => "FilmList", 'error' => self::ERROR_NONE, 'Data' => $list, 'count' => $count, 'total_count' => $total_count, 'search' => $search));
        } else {
            echo json_encode(array('cmd' => 'FilmList', 'error' => self::ERROR_USER_NEED_LOGIN, 'error_msg' => 'Please login'));
        }
    }

    /**
     *  OLD FOR COMPATABITLE
     */
    public
    function actionFilmSearch()
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
            echo json_encode(array('cmd' => "FilmList", 'error' => self::ERROR_NONE, 'Data' => $list, 'total_count' => $total_count, 'count' => $count, 'search' => $search));
        } else {
            echo json_encode(array('cmd' => 'FilmList', 'error' => self::ERROR_USER_NEED_LOGIN, 'error_msg' => 'Please login'));
        }
    }

    public
    function actionFilmData()
    {
        if (Yii::app()->user->id) {
            if (isset($_REQUEST['fc_id'])) {
                $fc_id = (int)$_REQUEST['fc_id'];
                if (isset($_SESSION['device_preset'])) {
                    $preset = $_SESSION['device_preset'];
                } else $preset = 0;
                $list = CAppHandler::getVtrItemA($fc_id, Yii::app()->user->id, $preset);

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
                                $fn = pathinfo($res['fname'], PATHINFO_FILENAME) . '.mp4';
                                $link = sprintf($partnerInfo['sprintf_url'], $partnerInfo['original_id'], 'low', $fn, 1);
                                break;
                            default:
                                if ($partnerInfo && isset($partnerInfo['sprintf_url'])) {
                                    $fn = pathinfo($res['fname'], PATHINFO_FILENAME) . '.mp4';
                                    $link = sprintf($partnerInfo['sprintf_url'], $partnerInfo['original_id'], 'low', $fn, 1);
                                    break;
                                } else {
                                    echo json_encode(array('cmd' => "FilmData", 'error' => 1, 'error_msg' => 'unknown partner'));
                                    Yii::app()->end();
                                }
                        }
                        $res['link'] = $link;
                        echo json_encode(array('cmd' => "FilmData", 'error' => self::ERROR_NONE, 'Data' => $res));
                    } else
                        echo json_encode(array('cmd' => "FilmData", 'error' => 1, 'error_msg' => 'Not found file'));
                } else
                    echo json_encode(array('cmd' => "FilmData", 'error' => self::ERROR_UNKNOWN_ITEM, 'error_msg' => 'Unknown item'));
            } else {
                echo json_encode(array('cmd' => "FilmData", 'error' => self::ERROR_INPUT_DATA_FAILED, 'error_msg' => 'Unknown item'));
            }
        } else {
            echo json_encode(array('cmd' => "FilmData", 'error' => self::ERROR_USER_NEED_LOGIN, 'error_msg' => 'Please Login'));
        }
    }

    public
    function actionFilmLink()
    {
        if (Yii::app()->user->id) {
            if (isset($_REQUEST['fc_id'])) {
                $fc_id = (int)$_REQUEST['fc_id'];
                if (isset($_SESSION['device_preset'])) {
                    $preset = $_SESSION['device_preset'];
                } else $preset = 0;
                $list = CAppHandler::getVtrItemA($fc_id, Yii::app()->user->id, $preset);
                $zFlag = Yii::app()->user->UserInZone;
                $zSql = '';
                if (!$zFlag) {
                    $zSql = ' AND p.flag_zone = 0';
                }

                if ($res = $list->read()) {
                    if ($res['fname']) {
                        $partnerInfo = Yii::app()->db->createCommand()
                            ->select('prt.id, prt.title, prt.sprintf_url, p.original_id')
                            ->from('{{products}} p')
                            ->join('{{partners}} prt', 'prt.id = p.partner_id')
                            ->where('p.id = ' . $res['product_id'] . $zSql)->queryRow();
                        $fn = pathinfo($res['fname'], PATHINFO_FILENAME) . '.mp4';
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
                        echo json_encode(array('cmd' => "FilmLink", 'error' => self::ERROR_NONE, 'Data' => $data));

                    } else
                        echo json_encode(array('cmd' => "FilmLink", 'error' => 1, 'error_msg' => 'Not found file'));
                } else
                    echo json_encode(array('cmd' => "FilmLink", 'error' => 1, 'error_msg' => 'Not found partner data'));
            } else {
                echo json_encode(array('cmd' => "FilmLink", 'error' => self::ERROR_INPUT_DATA_FAILED, 'error_msg' => 'Unknown item'));
            }
        } else {
            echo json_encode(array('cmd' => "FilmLink", 'error' => self::ERROR_USER_NEED_LOGIN, 'error_msg' => 'Please Login'));
        }
    }


    public
    function actionPartnerList()
    {
        if (Yii::app()->user->id) {
            $userPower = Yii::app()->user->UserPower;
            $list = CAppHandler::getPartnerList($userPower);
            $count = count($list);
            $total_count = $count;
            foreach ($list as $item) {
                $item['image'] = '';
            }
            echo json_encode(array('cmd' => "PartnerList", 'error' => self::ERROR_NONE, 'Data' => $list, 'count' => $count, 'total_count' => $total_count));
        } else {
            json_encode(array('cmd' => "PartnerList", "error" => self::ERROR_USER_NEED_LOGIN, "error_msg" => 'Please login'));
        }
    }

    public
    function actionPartnerItemList()
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

    public
    function actionPartnerItemData()
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

    public
    function actionAddItemFromPartner()
    {
        if (Yii::app()->user->id) {
            if (isset($_REQUEST['variant_id'])) {
                $variant_id = (int)$_REQUEST['variant_id'];
                if ($res = CAppHandler::addProductToUser($variant_id)) {
                    echo json_encode(array('cmd' => "AddItemFromPartner", 'error' => self::ERROR_NONE, 'cloud_id' => $res));
                } else
                    echo json_encode(array('cmd' => "AddItemFromPartner", 'error' => 1, 'err_code' => $res));
            } else
                echo json_encode(array('cmd' => "AddItemFromPartner", 'error' => self::ERROR_UNKNOWN_ITEM, "error_msg" => 'Unknown item'));
        } else
            echo json_encode(array('cmd' => "AddItemFromPartner", 'error' => self::ERROR_USER_NEED_LOGIN, "error_msg" => 'Unknown user'));

    }

    public
    function actionRemoveFromMe()
    {
        if (Yii::app()->user->id) {
            if (isset($_REQUEST['id'])) {
                $item_id = (int)$_REQUEST['id'];
                CAppHandler::removeFromUser($item_id);
                echo json_encode(array('cmd' => "RemoveFromMe", 'error' => self::ERROR_NONE));
            } else echo json_encode(array('cmd' => "RemoveFromMe", 'error' => self::ERROR_UNKNOWN_ITEM, "error_msg" => 'Unknown item'));
        } else
            echo json_encode(array('cmd' => "RemoveFromMe", 'error' => self::ERROR_USER_NEED_LOGIN, "error_msg" => 'Unknown user'));
    }


    public
    function actionGetList($cid = 0)
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

    public
    function actionSetDeviceParams()
    {
        if (Yii::app()->user->id) {
            if (isset($_SESSION['device_id'])) {
                $device_id = $_SESSION['device_id'];
                $device = CDevices::model()->find('id=:id', array(':id' => $device_id));
                /** @var CDevices $device */
                if ($device) {
                    if (isset($_REQUEST['quality']))
                        $device->max_preset = (int)$_REQUEST['quality'];
                    $device->save();
                }
            }
        }
    }

    public
    function actionConverter()
    {

    }

    public
    function actionLibraryList()
    {
        if (Yii::app()->user->id) {
            $list = CAppHandler::findUserObjects(Yii::app()->user->id, 1, $this->search, $this->page, $this->per_page);
            $total_count = CAppHandler::countFoundUserObjects(Yii::app()->user->id, 1, $this->search);
            $count = count($list);
            echo json_encode(array('cmd' => "LibraryList", 'error' => self::ERROR_NONE, 'Data' => $list, 'count' => $count, 'total_count' => $total_count, 'search' => $this->search));
        } else {
            echo json_encode(array('cmd' => 'LibraryList', 'error' => self::ERROR_USER_NEED_LOGIN, 'error_msg' => 'Please login'));
        }
    }

    public
    function actionLibraryData()
    {
        if (Yii::app()->user->id) {
            if (isset($_REQUEST['fc_id'])) {
                $fc_id = (int)$_REQUEST['fc_id'];
                if (isset($_SESSION['device_preset'])) {
                    $preset = $_SESSION['device_preset'];
                } else $preset = 0;
                $list = CAppHandler::getUserObjectsA($fc_id, Yii::app()->user->id, $preset);

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
                                $fn = pathinfo($res['fname'], PATHINFO_FILENAME) . '.mp4';
                                $link = sprintf($partnerInfo['sprintf_url'], $partnerInfo['original_id'], 'low', $fn, 1);
                                break;
                            default:
                                if ($partnerInfo && isset($partnerInfo['sprintf_url'])) {
                                    $fn = pathinfo($res['fname'], PATHINFO_FILENAME) . '.mp4';
                                    $link = sprintf($partnerInfo['sprintf_url'], $partnerInfo['original_id'], 'low', $fn, 1);
                                    break;
                                } else {
                                    echo json_encode(array('cmd' => "FilmData", 'error' => 1, 'error_msg' => 'unknown partner'));
                                    Yii::app()->end();
                                }
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
            echo json_encode(array('cmd' => "FilmData", 'error' => self::ERROR_USER_NEED_LOGIN, 'error_msg' => 'Please Login'));
        }
    }


    public
    function actionQueueList()
    {
        if (Yii::app()->user->id) {
            $list = CAppHandler::findQueueObjects(Yii::app()->user->id, 1, $this->search, $this->page, $this->per_page);
            $total_count = CAppHandler::countFoundQueueObjects(Yii::app()->user->id, 1, $this->search);
            $count = count($list);
            echo json_encode(array('cmd' => "LibraryList", 'error' => 0, 'Data' => $list, 'count' => $count, 'total_count' => $total_count, 'search' => $this->search));
        } else {
            echo json_encode(array('cmd' => 'LibraryList', 'error' => self::ERROR_USER_NEED_LOGIN, 'error_msg' => 'Please login'));
        }
    }

    public
    function actionQueueData()
    {
        if (Yii::app()->user->id) {
            if (isset($_REQUEST['fc_id'])) {
                $fc_id = (int)$_REQUEST['fc_id'];
                if (isset($_SESSION['device_preset'])) {
                    $preset = $_SESSION['device_preset'];
                } else $preset = 0;
                $list = CAppHandler::getQueueObjectsA($fc_id, Yii::app()->user->id, $preset);

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
                                $fn = pathinfo($res['fname'], PATHINFO_FILENAME) . '.mp4';
                                $link = sprintf($partnerInfo['sprintf_url'], $partnerInfo['original_id'], 'low', $fn, 1);
                                break;
                            default:
                                if ($partnerInfo && isset($partnerInfo['sprintf_url'])) {
                                    $fn = pathinfo($res['fname'], PATHINFO_FILENAME) . '.mp4';
                                    $link = sprintf($partnerInfo['sprintf_url'], $partnerInfo['original_id'], 'low', $fn, 1);
                                    break;
                                } else {
                                    echo json_encode(array('cmd' => "FilmData", 'error' => 1, 'error_msg' => 'unknown partner'));
                                    Yii::app()->end();
                                }
                        }
                        $res['link'] = $link;
                        echo json_encode(array('cmd' => "FilmData", 'error' => self::ERROR_NONE, 'Data' => $res));

                    } else
                        echo json_encode(array('cmd' => "FilmData", 'error' => 1, 'error_msg' => 'Not found file'));
                } else
                    echo json_encode(array('cmd' => "FilmData", 'error' => 1, 'error_msg' => 'Not found partner data'));
            } else {
                echo json_encode(array('cmd' => "FilmData", 'error' => 1, 'error_msg' => 'Unknown item'));
            }
        } else {
            echo json_encode(array('cmd' => "FilmData", 'error' => self::ERROR_USER_NEED_LOGIN, 'error_msg' => 'Please Login'));
        }
    }


    public
    function actionGetWindow($wid = 0)
    {
        if ($wid == 0) {
            //Display list
        } else {
        }

    }


    public
    function actionGetSettings()
    {

    }


    public
    function actionGetUserInfo()
    {
        //$user= CUser::model()->getU
        echo "No info";
    }

    public
    function actionGetDirTree()
    {
        //$dirs = CUserfiles::model()->getDirTree($user_id);

    }

    public
    function actionGetFileList()
    {
        $pid = 0;
        //  $files = CUserfiles::model()->getFileList($this->user_id, $pid);

    }

    /*
     *
     */

    public
    function actionRegister()
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

    public
    function actionConfirm($user_id = 0, $hash = '')
    {
        if ($hash != '') {
            $hash = filter_var($hash, FILTER_SANITIZE_STRING);
            $user_id = (int)$user_id;
            $msg = '';
            if ($user_id) {
                $user = CUser::model()->findByPk($user_id);
                /* @var CUser $user */
                if ($user) {
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

    public
    function actionResetPassword($hash = '', $user_id = 0)
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
        } else {
            if ($user_id > 0 && CUser::checkMagicKeyForUser($user_id, $hash)) {
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
