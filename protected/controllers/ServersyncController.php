<?php


class ServersyncController extends ControllerSync
{
    var $layout = 'ajax';

    public function beforeAction($action)
    {
        return true;
        /*
        $shash = $_GET['shash'];
        $hash_local = md5(date('%h%d') . 'where am i');
        if ($shash <> $hash_local) {
            //var_dump($shash);
            echo 'bye';
            return false;
        }

        $this->server = CServers::model()->findByAttributes();
        if ($this->server === null)
            die('Unknown Server ' . $_SERVER['REMOTE_ADDR']);
        Yii::log(print_r($_GET, true), CLOGGER::LEVEL_TRACE, 'snow');
        return true;
        */
    }

    /**
     * GET  USERINFO
     * @param int $user_id
     */
    /*
    public function actionUserdata($user_id = 0)
    {
        $uid = (int)$user_id;
        if ($uid > 0) {
            $response = array();
            $response['sid'] = CUser::model()->findByPk($uid)->getAttribute('sess_id');
            echo serialize($response);
        }
        exit();
    }

    public function actionFiledata($user_id = 0)
    {
        $user_id = (int)$user_id;
        if ($user_id > 0) {
            if (!isset($_GET['data']))
                die("Data is not enough");
            $data = @unserialize($_GET['data']);
            if ($data) {
                $fid = (int)$data['fid'];
                $stype = (int)$data['stype'];
                $user_ip = (int)$data['user_ip'];
                $zone = CZones::model()->GetZoneByIp($user_ip);
                $filemeta = CUserfiles::model()->getFileMeta($user_id, $fid);
                if ($filemeta) {
                    $response['title'] = $filemeta['title'];
                    $variant = CUserfiles::model()->getFileVariantUser($fid);
                    if ($variant) {
                        $filedata = CUserfiles::model()->getFileLocUser($variant['id'], $zone);

                        foreach ($filedata as $file) {
                            $fdata = array();
                            $fdata['ip'] = $file['ip'];
                            $fdata['port'] = $file['port'];
                            $fdata['name'] = $file['fname'];
                            $fdata['size'] = $file['fsize'];
                            $response['filedata'][] = $fdata;
                        }
                    } else
                        $response['error'] = 'no variants';
                } else {
                    $response['error'] = 'unknown file';
                }
            } else {
                $response['error'] = 'bad data';
            }
            echo (serialize($response));
        } else
            die('bye bye');
        exit;
    }

    /**
     * actionCreateMetaFile
     * @param int $user_id
     * @param string$data
     */
    /*
    public function actionCreateMetaFile($user_id = 0, $data = '')
    {
        if ($user_id > 0) {
//OK
            $input = @unserialize($data);
            if (!($input === false)) {
                $files = new CUserfiles();
                $files->title = base64_decode($input['title']);
                $files->user_id = $user_id;
                $ext = pathinfo($files->title, PATHINFO_EXTENSION);
                $files->type_id = Utils::getSectionIdByExt($ext);
                if ($files->type_id > 0) {
                    if ($files->save())
                        $result = array('fid' => $files->id);
                    else
                        $result = array('error' => "Can't save record");
                } else
                    $result = array('error' => "Unsupported filetype " . $ext, 'error_code' => 1);
            } else
                $result = array('error' => 'Bad input data');
            echo serialize($result);
            exit;
        }
        exit;
    }
*/
    /**
     * actionCreateFileVariant
     * @param int $user_id
     * @param string $data
     */
    /*
    public function actionCreateFileVariant($data = '')
    {
        $input = @unserialize($data);
        if (!($input === false)) {
            $file_variant = new CFilesvariants();
            $file_variant->file_id = $input['fid'];
            $preset_id = 0; //CPresets::model()->findPresetByName($data['preset']);
            $file_variant->preset_id = $preset_id;
            $file_variant->fmd5 = $input['fmd5'];
            $file_variant->fsize = $input['fsize'];
            if ($file_variant->save())
                $result = array('variant_id' => $file_variant->id);
            else
                $result = array('error' => "Can't save record");
        } else
            $result = array('error' => 'Bad input data');
        echo serialize($result);
        exit;
    }*/

    /**
     * actionCreateFileLocation
     * @param int $user_id
     * @param string $data
     */
    /*
    public function actionCreateFileLocation($data = '')
    {
        $input = @unserialize($data);
        if (!($input === false)) {
            $file_location = new CFilelocations();
            $file_location->id = $input['variant_id'];
            $file_location->fname = $input['sname'];
            $file_location->fsize = $input['fsize'];
            if (isset($input['modified']))
                $file_location->modified = $input['modified'];
            if (isset($input['folder']))
                $file_location->folder = $input['folder'];
            $file_location->server_id = $this->server->id;
            if ($file_location->save())
                $result = array('file_location_id' => $file_location->id);
            else
                $result = array('error' => "Can't save record");
        } else
            $result = array('error' => 'Bad input data');
        echo serialize($result);
        exit;
    }
*/
    /*
    public function actionCreateConvertTask($data = '')
    {
        $input = @unserialize($data);
        if (!($input === false)) {
            $cqueue = new CConvertQueue();
            $cqueue->id = $data['fid'];
            $preset_id = 0; //CPresets::model()->findPresetByName($data['preset']);
            $cqueue->preset_id = $preset_id;
            $cqueue->server_id = $this->server->id;
            $cqueue->task_id = $data['job_id'];
            if ($cqueue->save())
                $result = array('queue_id' => $cqueue->id);
            else
                $result = array('error' => "Can't save record");
        } else
            $result = array('error' => 'Bad input data');
        echo serialize($result);
        exit;
    }

    public function actionCompleteConvertTask($data = '')
    {
        $input = @unserialize($data);
        if (!($input === false)) {
            $cqueue = CConvertQueue::model()->findByAttributes(array('task_id' => (int)$data['job_id']));
            if ($cqueue) {

                //CompleteConverTask
                // 1. For What file is
                if ($cqueue->file_id > 0) {
                    $file_id = $cqueue->file_id;
                    //Create variant
                    $file_variant = new CFilesvariants();
                    $file_variant->fsize = $input['fsize'];
                    $file_variant->fmd5 = $input['fmd5'];
                    $file_variant->preset_id = $cqueue->preset_id;
                    $file_variant->file_id = $file_id;

                    if ($file_variant->save()) {
                        $file_loc = new CFilelocations();
                        $file_loc->id = $file_variant->id;
                        $file_loc->server_id = $this->server->id;
                        $file_loc->fsize = $input['fsize'];
                        $file_loc->fname = $input['fname'];
                        //$file_loc->modified=now();
                        if ($file_loc->save()) {
                            $result = array('success' => 'Location created');
                            $cqueue->delete();
                        } else
                            $result = array('error' => 'Location not created');
                    } else
                        $result = array('error' => 'file_variant not created');
                } else
                    $result = array('error' => 'unknown file');
            } else
                $result = array('error' => 'Unknown task');
        } else
            $result = array('error' => 'Bad input data');
        echo serialize($result);
        exit;
    }
    */


    public function actionFiledata()
    {
        $answer = array();
        if (isset($_REQUEST['fdata']) && isset($_REQUEST['sdata'])) {
            $check_data = sha1($_REQUEST['fdata'] . Yii::app()->params['uploads_skey']);
            if ($check_data == $_REQUEST['sdata']) {
                $rdata = unserialize(base64_decode($_REQUEST['fdata']));
                if ($rdata) {
                    /*
                        $syncData['variant_id'] = $vid;
                        $syncData['user_ip'] = $user_ip;
                        $syncData['server_ip'] = Yii::app()->params['server_ip'];
                        $syncData['uid'] = $user_id;
                        $syncData['key'] = $user_key;
                    */
                    $variant_id = (int)$rdata['variant_id'];
                    if (CUser::getDownloadSign($variant_id . $rdata['uid'])) {
                        if (CUserfiles::DidUserHaveVariant($rdata['uid'], $variant_id)) {
                            $server_ip = $rdata['server_ip'];
                            $user_ip = (int)$rdata['user_ip'];
                            $zones = CZones::getActiveZones($user_ip);
                            $server = CServers::model()->findByAttributes(array('ip' => $server_ip, 'downloads' => 1));
                            if ($server) {
                                $locations = CFilelocations::model()->findByAttributes(array('id' => $variant_id, 'server_id' => $server['id']));
                                if ($locations) {
                                    $answer['folder'] = $locations->folder;
                                    $answer['fname'] = $locations->fname;
                                    $answer['fsize'] = $locations->fsize;
                                } else {
                                    $answer['error'] = 1;
                                    $answer['error_msg'] = "File not found";
                                }
                            } else {
                                if (count($zones))
                                    $locations = CFilelocations::getLocationByZone($variant_id, $zones[0]['zone_id']);
                                if (!empty($locations)) {
                                    $answer['server'] = $locations['server_ip'];
                                }
                            }
                        } else {
                            $answer['error'] = 1;
                            $answer['error_msg'] = "User " . $rdata['uid'] . " didn't have variant $variant_id";
                        }
                    } else {
                        $answer['error'] = 1;
                        $answer['error_msg'] = "Bad key";
                    }
                } else {
                    $answer['error'] = 1;
                }
            } else {
                $answer['error'] = 1;
            }
            echo base64_encode(serialize($answer));
        } else {
            die();
        }
    }


    public function actionPartnerFiledata()
    {
        $answer = array();
        if (isset($_REQUEST['fdata']) && isset($_REQUEST['sdata'])) {
            $check_data = sha1($_REQUEST['fdata'] . Yii::app()->params['uploads_skey']);
            if ($check_data == $_REQUEST['sdata']) {
                $rdata = unserialize(base64_decode($_REQUEST['fdata']));
                if ($rdata) {
                    /*
                        $syncData['file_id'] = $fid;
                        $syncData['user_ip'] = $user_ip;
                        $syncData['server_ip'] = Yii::app()->params['server_ip'];
                        $syncData['uid'] = $user_id;
                        $syncData['key'] = $user_key;
                    */
                    $file_id = (int)$rdata['file_id'];
                    if (CUser::getDownloadSign($file_id . $rdata['uid'])) {
                        $data = CTypedfiles::GetPartnerFileData($file_id);
                        if (!empty($data)) {
                            $server_ip = $rdata['server_ip'];
                            $user_ip = (int)$rdata['user_ip'];
                            $zones = CZones::getActiveZones($user_ip);
                            $server = CServers::model()->findByAttributes(array('ip' => $server_ip, 'downloads' => 1));
                            if ($server) {
                                //$locations = CFilelocations::model()->findByAttributes(array('id' => $variant_id, 'server_id' => $server['id']));
                                //if ($locations){
                                /// $answer['folder'] = $locations->folder;
                                $answer['partner_id'] = $data[0]['partner_id'];
                                $answer['fname'] = $data[0]['fname'];
                                $answer['original_variant_id'] = $data[0]['original_variant_id'];
                                //} else{
                                //    $answer['error'] = 1;
                                //    $answer['error_msg'] = "File not found";
                                // }
                            } else {
                                //if (count($zones))
                                //    $locations = CFilelocations::getLocationByZone($variant_id, $zones[0]['zone_id']);
                                //if (!empty($locations)) {
                                //   $answer['server'] = $locations['server_ip'];
                                //}
                            }
                        } else {
                            $answer['error'] = 1;
                            $answer['error_msg'] = "User " . $rdata['uid'] . " Partners didn't have file $file_id";
                        }
                    } else {
                        $answer['error'] = 1;
                        $answer['error_msg'] = "Bad key";
                    }
                } else {
                    $answer['error'] = 1;
                }
            } else {
                $answer['error'] = 1;
            }
            echo base64_encode(serialize($answer));
        } else {
            die();
        }
    }

    /**
     * действие обработки запроса файлового сервера по загруженным пользователем файлам
     * вся информация о пользователе, файле и параметрах приходит в $_POST в виде структуры
     * array(
     *         key            - ключ межсерверных запросов
     *         uid            - userid
     *         sum            - контрольная сумма информации о файле и id сервера
     *         sid            - id сервера
     *         sfile        - сериализованный массив инфо о файле
     *         sparams        - сериализованный массив параметров типизации
     * )
     *
     */

    public function actionUpload()
    {
        $answer = array();
        if (isset($_REQUEST['fdata']) && isset($_REQUEST['sdata'])) {
            $check_data = sha1($_REQUEST['fdata'] . Yii::app()->params['uploads_skey']);
            if ($check_data == $_REQUEST['sdata']) {
                $rdata = unserialize(base64_decode($_REQUEST['fdata']));
                $user_id = (int)$rdata['uid'];
                /*
                   $syncData = array();
                   $syncData['md5'] = $_POST['Filedata_md5'];
                   $syncData['size'] = $_POST['Filedata_size'];
                   $syncData['name'] = $fname;
                   $syncData['path'] = $dpath;
                   $syncData['src'] = $fname;
                   $syncData['key'] = filter_var($_POST['key'], FILTER_SANITIZE_STRING);
                   $syncData['uid'] = $uid;
                   $syncData['server_ip'] = Yii::app()->params['server_ip'];
                 */
                if (CUser::checkfishkey($user_id, $rdata['key'])) {
                    $file_server = CServers::model()->findByAttributes(array('ip' => $rdata['server_ip']));
                    if ($file_server) {
                        $uf = new CUserfiles();
                        $uf->title = $rdata['name'];
                        $uf->object_id = 0; //ДО ТЕХ ПОР, ПОКА НЕ БУДЕТ ТИПИЗИРОВАН
                        $uf->user_id = $rdata['uid'];
                        $uf->type_id = 0; //ДО ТЕХ ПОР, ПОКА НЕ БУДЕТ ТИПИЗИРОВАН
                        if ($uf->save(false)) {
                            $userFileId = $uf->id;
                            if (!empty($userFileId)) {
                                //СОЗДАЕМ ЗАПИСЬ files_variants
                                $fv = new CFilesvariants();
                                $fv->file_id = $userFileId;
                                $fv->preset_id = 0; //ДО ТЕХ ПОР, ПОКА НЕ БУДЕТ ТИПИЗИРОВАН
                                $fv->fsize = $rdata['size'];
                                $fv->fmd5 = $rdata['md5'];
                                if ($fv->save(false)) {
                                    $fileVariantId = $fv->id;
                                    if (!empty($fileVariantId)) {
                                        //СОЗДАЕМ ЗАПИСЬ В filelocations
                                        $fl = new CFilelocations();
                                        $fl->id = $fileVariantId;
                                        $fl->server_id = $file_server->id;
                                        // $fl->server_id = $sid;
                                        $fl->state = 0;
                                        $fl->modified = date('Y-m-d H:i:s');
                                        $fl->fsize = $fv->fsize;
                                        $fl->fname = $rdata['name'];
                                        $fl->folder = $rdata['path'];
                                        if ($fl->save(false)) {
                                            CUser::UpdateSpaceInfo($user_id, $fv->fsize);
                                            $fileLocationId = $fl->id;
                                            $answer['success'] = 1;
                                            $answer['id'] = $userFileId;
                                            //СОХРАНЕНИЕ ЗАВЕРШЕНО

                                            $mediaList = Utils::getMediaList();
                                            $fInfo = pathinfo(strtolower($rdata['name']));
                                            if (!empty($fInfo['extension']) && !empty($mediaList[1]['exts']) && in_array($fInfo['extension'], $mediaList[1]['exts'])) {
                                                $partnerId = 0;
                                                $queue = array(
                                                    'id' => null,
                                                    'product_id' => 0,
                                                    'original_id' => $userFileId,
                                                    'task_id' => 0,
                                                    'cmd_id' => 0,
                                                    'info' => "",
                                                    'priority' => 200,
                                                    'state' => 0,
                                                    'station_id' => 0,
                                                    'partner_id' => $partnerId,
                                                    'user_id' => $rdata['uid'],
                                                    'original_variant_id' => 0,
                                                );
                                                $cmd = Yii::app()->db->createCommand()->insert('{{income_queue}}', $queue);
                                                $result = 'queue';
                                            }

                                            echo base64_encode(serialize($answer));
                                            Yii::app()->end();
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $answer['error'] = 1;
                    $answer['error_msg'] = "Bad user key";
                }

            } else
                $answer['error'] = 1;
            $answer['error_msg'] = "Bad server key";

        } else {
            $answer['error'] = 1;
            $answer['error_msg'] = "Bad data";
        }

        echo base64_encode(serialize($answer));
    }

    /**
     * Compressor complete function
     */

    public function actionCompletePartners()
    {
        function makeDataAds($fileName)
        {
            preg_match('/s([0-9]{1,2})e([0-9]{1,2})/i', $fileName, $matches);
            if (!$matches[1]) {
                return array(
                    's' => $matches[1],
                    'e' => $matches[2]
                );
            } else return NULL;
        }

        function getSubId($fileName)
        {
            /*
             1.Film
             2.Part
             3.One seria
             4.Serial
            */
            preg_match('/e([0-9]{2,})/i', $fileName, $matches); //ИЩЕМ НУМЕРАЦИЮ ЭПИЗОДА
            if (!empty($matches[1])) {
                return 3;
            }
            preg_match('/part/i', $fileName, $matches); //ИЩЕМ НУМЕРАЦИЮ ЭПИЗОДА
            if (!empty($matches[1])) {
                return 2;
            }
            return 1;
        }

        $qualityStrings = array(
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            'ultra' => 4
        );

        $answer = array();
        if (isset($_REQUEST['fdata']) && isset($_REQUEST['sdata'])) {
            $check_data = sha1($_REQUEST['fdata'] . Yii::app()->params['converter_skey']);
            if ($check_data == $_REQUEST['sdata']) {
                $rdata = @unserialize(base64_decode($_REQUEST['fdata']));
                $id = (int)$rdata['id'];
                /** @var CConvertQueue $queue  */
                $queue = CConvertQueue::model()->find('id=:id', array(':id' => $id));
                if ($queue) {
                    //$info = unserialize(base64_decode($queue->info));
                    // var_dump($rdata);
                    $product = new CProduct();
                    $product->active = 0;
                    $product->partner_id = $rdata['partner_id'];
                    $product->title = $rdata['title'];
                    $product->created = date("Y-m-d H:i:s");
                    switch ($queue->partner_id) {
                        case 5:
                        case 6:
                            $product->flag_zone = 1;
                            break;
                        default:
                            echo "Bad Partner " . $queue->partner_id;
                            return;
                    }
                    $product->original_id = $queue->original_id;
                    if ($product->save()) {
                        foreach ($rdata['variants']['files'] as $quality_files) {
                            $product_variant = new CProductVariant();
                            $product_variant->product_id = $product->id;
                            $product_variant->type_id = 1; // VIDEO
                            $product_variant->title = $rdata['variants']['title'];
                            $product_variant->original_id = $queue->original_variant_id;
                            if ($product_variant->save()) {
                                $product_variant->setParamValue(10, $rdata['variants']['poster']);
                                $product_variant->setParamValue(13, $rdata['variants']['year']);
                                $product_variant->setParamValue(12, $rdata['variants']['original_title']);
                                $product_variant->setParamValue(14, $rdata['variants']['country']);
                                foreach ($quality_files as $preset => $file) {
                                    $product_variant_quality = new CProductVariantQualities();
                                    $product_variant_quality->preset_id = $qualityStrings[$preset];
                                    $product_variant_quality->variant_id = $product_variant->id;
                                    if ($product_variant_quality->save()) {
                                        $product_files = new CProductFiles();
                                        $product_files->size = $file['size'];
                                        $product_files->fname = $file['name'];
                                        $product_files->md5 = $file['md5'];
                                        $product_files->preset_id = $qualityStrings[$preset];
                                        $product_files->variant_quality_id = $product_variant_quality->id;
                                        if ($product_files->save()) {
                                            $saved_files[] = "Y";
                                        } else
                                            $saved_files[] = "N";
                                        $answer['saved_files'] = $saved_files;
                                    } else {
                                        $answer['error'] = 1;
                                        $answer['error_msg'] = 'Error: save variant_quality';
                                    }
                                }
                            } else {
                                $answer['error'] = 1;
                                $answer['error_msg'] = 'Error: save product_variant';
                            }
                        }
                    } else {
                        $answer['error'] = 1;
                        $answer['error_msg'] = 'Error: save product';
                    }
                } else {
                    $answer['error'] = 1;
                    $answer['error_msg'] = 'Error: unknown task';
                }
                echo base64_encode(serialize($answer));
            } else echo "BAD DATA";
        } else echo "NO DATA";
    }


    public
    function actionConverterDataOLD()
    {
        $answer = array();
        if (isset($_REQUEST['fdata']) && isset($_REQUEST['sdata'])) {
            $check_data = sha1($_REQUEST['fdata'] . Yii::app()->params['converter_skey']);
            if ($check_data == $_REQUEST['sdata']) {
                $rdata = @unserialize(base64_decode($_REQUEST['fdata']));

                /*
                  $syncData = array();
                  $syncData['partner_id'] = $_POST['partner_id'];
                  $syncData['title'] = $_POST['title'];
                  $syncData['original_id'] = $_POST['original_id];
                  $syncData['type_id'] = $_POST['type_id'];
                  $syncData['variant_title'] = $_POST['variant_title'];
                  $syncData['original_variant_id'] = $_POST['original_variant_id'];
                  $syncData['sub_id'] = $_POST['sub_id'];
                  $syncData['files'] = array(
                       'low'=>array(
                           'size' => 100,
                           'variant_quality_id'=>1,
                           'name' => 'name',
                           'md5' => '35dfghdsfgsdfgsdfgsdf',
                           'preset_id'=> 1,
                       )
                   );
                */
                $product = new CProduct();
                $product->active = 0;
                $product->partner_id = $rdata['partner_id'];
                $product->title = $rdata['title'];
                switch ($rdata['partner_id']) {
                    case 5:
                    case 6:
                        $product->flag_zone = 1;
                        break;
                    default:
                        echo "Bad Partner " . $rdata['partner_id'];
                        return;
                }
                $product->original_id = $rdata['original_id'];
                if ($product->save()) {
                    $product_variant = new CProductVariant();
                    $product_variant->product_id = $product->id;
                    $product_variant->type_id = $rdata['type_id'];
                    $product_variant->title = $rdata['variant_title'];
                    $product_variant->description = '';
                    $product_variant->original_id = $rdata['original_variant_id'];
                    $product_variant->sub_id = $rdata['sub_id'];
                    if ($product_variant->save()) {
                        if (isset($rdata['poster'])) {
                            $product_variant->setParamValue(10, $rdata['poster']);
                        } else {
                            $answer = array(
                                'error_code' => 3,
                                'error_msg' => "NO POSTER");
                            echo base64_encode(serialize($answer));
                            return;
                        }
                        if (isset($rdata['year'])) {
                            $product_variant->setParamValue(13, $rdata['year']);
                        } else {
                            $answer = array(
                                'error_code' => 3,
                                'error_msg' => "NO Year");
                            echo base64_encode(serialize($answer));
                            return;
                        }
                        if (isset($rdata['title_en'])) {
                            $product_variant->setParamValue(12, $rdata['title_en']);
                        } else {
                            $answer = array(
                                'error_code' => 3,
                                'error_msg' => "NO Title_en");
                            echo base64_encode(serialize($answer));
                            return;
                        }
                        if (isset($rdata['country'])) {
                            $product_variant->setParamValue(14, $rdata['country']);
                        } else {
                            $answer = array(
                                'error_code' => 3,
                                'error_msg' => "NO Country");
                            echo base64_encode(serialize($answer));
                            return;
                        }

                        $saved_files = array();
                        $files = $rdata['files'];
                        foreach ($files as $file) {
                            $product_files = new CProductFiles();
                            $product_files->size = $file['size'];
                            $product_files->variant_quality_id = $file['variant_quality_id'];
                            $product_files->fname = $file['name'];
                            $product_files->md5 = $file['md5'];
                            $product_files->preset_id = $file['preset_id'];
                            if ($product_files->save()) {
                                $saved_files[] = "Y";
                            } else
                                $saved_files[] = "N";
                            $answer['saved_files'] = $saved_files;
                        }
                    } else
                        $answer['error_code'] = 2;
                } else
                    $answer['error_code'] = 1;

                echo base64_encode(serialize($answer));
            } else echo "BAD DATA";
        } else echo "NO DATA";
    }

    /*

        public function actionTadd($user_id, $id)
        {


            $variant_id = $id;
            if (CTypedfiles::DidUserHavePartnerVariant($user_id, $variant_id)) {
                CProductVariant::getPrice($variant_id);

            } else {
                $product  = CProduct::getProductByVariantId($variant_id);
                if ($product) {
                    $typedfile = new CTypedfiles();
                    $typedfile -> variant_id = $variant_id;
                    $typedfile -> user_id = $user_id;
                    $typedfile -> title = $product['title'];
                    $typedfile -> collection_id =0;
                    $typedfile -> variant_quality_id = $variant_quality_id;
                    if($typedfile->save()){
                        $result = $typedfile->id;
                    }
                }
            }
        }
    */
    public function actionCheckReady()
    {
        $answer = array();
        if (isset($_REQUEST['fdata']) && isset($_REQUEST['sdata'])) {
            $check_data = sha1($_REQUEST['fdata'] . Yii::app()->params['converter_skey']);
            if ($check_data == $_REQUEST['sdata']) {
                $rdata = @unserialize(base64_decode($_REQUEST['fdata']));
                if ($rdata['variants']) {
                    foreach ($rdata['variants'] as $variant_id) {
                        if (!$variant = CProductVariant::model()->find('id=:id', array(':id' => $variant_id))) {
                            $answer['wait'] = 1;
                            echo base64_encode(serialize($answer));
                            Yii::app()->end();
                        }
                    }
                    $answer['wait'] = 0;
                    echo base64_encode(serialize($answer));
                    Yii::app()->end();
                }
            }
        }
    }

    public function actionCreateVariantData()
    {
        $answer = array();
        if (isset($_REQUEST['fdata']) && isset($_REQUEST['sdata'])) {
            $check_data = sha1($_REQUEST['fdata'] . Yii::app()->params['converter_skey']);
            if ($check_data == $_REQUEST['sdata']) {
                $rdata = @unserialize(base64_decode($_REQUEST['fdata']));

                /*
                  $syncData = array();
                  $syncData['partner_id'] = $_POST['partner_id'];
                  $syncData['title'] = $_POST['title'];
                  $syncData['original_id'] = $_POST['original_id];
                  $syncData['type_id'] = $_POST['type_id'];
                  $syncData['variant_title'] = $_POST['variant_title'];
                  $syncData['original_variant_id'] = $_POST['original_variant_id'];
                  $syncData['sub_id'] = $_POST['sub_id'];
                  $syncData['files'] = array(
                       'low'=>array(
                           'size' => 100,
                           'variant_quality_id'=>1,
                           'name' => 'name',
                           'md5' => '35dfghdsfgsdfgsdfgsdf',
                           'preset_id'=> 1,
                       )
                   );
                */
                $product_variant = new CProductVariant();
                $product_variant->type_id = $rdata['type_id'];
                $product_variant->title = $rdata['variant_title'];
                $product_variant->description = '';
                $product_variant->original_id = $rdata['original_variant_id'];
                $product_variant->sub_id = $rdata['sub_id'];
                if ($product_variant->save()) {
                    $answer['variant_id'] = $product_variant->id;
                    if (isset($rdata['poster'])) {
                        $product_variant->setParamValue(10, $rdata['poster']);
                    } else {
                        $answer = array(
                            'error_code' => 3,
                            'error_msg' => "NO POSTER");
                        echo base64_encode(serialize($answer));
                        return;
                    }
                    if (isset($rdata['year'])) {
                        $product_variant->setParamValue(13, $rdata['year']);
                    } else {
                        $answer = array(
                            'error_code' => 3,
                            'error_msg' => "NO Year");
                        echo base64_encode(serialize($answer));
                        return;
                    }
                    if (isset($rdata['title_en'])) {
                        $product_variant->setParamValue(12, $rdata['title_en']);
                    } else {
                        $answer = array(
                            'error_code' => 3,
                            'error_msg' => "NO Title_en");
                        echo base64_encode(serialize($answer));
                        return;
                    }
                    if (isset($rdata['country'])) {
                        $product_variant->setParamValue(14, $rdata['country']);
                    } else {
                        $answer = array(
                            'error_code' => 3,
                            'error_msg' => "NO Country");
                        echo base64_encode(serialize($answer));
                        return;
                    }

                    $saved_files = array();
                    $files = $rdata['files'];
                    foreach ($files as $file) {
                        $product_files = new CProductFiles();
                        $product_files->size = $file['size'];
                        $product_files->variant_quality_id = $file['variant_quality_id'];
                        $product_files->fname = $file['name'];
                        $product_files->md5 = $file['md5'];
                        $product_files->preset_id = $file['preset_id'];
                        if ($product_files->save()) {
                            $saved_files[] = "Y";
                        } else
                            $saved_files[] = "N";
                        $answer['saved_files'] = $saved_files;

                    }
                } else
                    $answer['error_code'] = 2;
                echo base64_encode(serialize($answer));
            } else echo "BAD DATA";
        } else echo "NO DATA";
    }


    /*
    public function actionUploadIvan()
    {
    function nothingEr($n, $s)
    {
    throw new Exception($s);
    }

    //		set_error_handler("nothingEr");

    $result = '';
    //ПРОВЕРКА КЛЮЧА ПОЛЬЗОВАТЕЛЯ (С УЧЕТОМ ПЕРЕХОДА ЧЕРЕЗ НАЧАЛО СУТОК)
    if (!empty($_POST['key']) && !empty($_POST['uid'])) {
    $userId = $_POST['uid'];
    $key = $_POST['key'];
    $key1 = CUser::getfishkey($_POST['uid'], date('Y-m-d'));
    $key2 = CUser::getfishkey($_POST['uid'], date('Y-m-d', time() - 3600 * 24));
    if (($key == $key1) || ($key == $key2)) {
        $result = 'key ok';
    }
    }

    if (!empty($result) && !empty($_POST['sfile'])) {
    try {
        $fileInfo = unserialize($_POST['sfile']);
    } catch (Exception $e) {
        $result = '';
    }

    if (!empty($result)) {
        $result = '';
        if (!empty($_POST['sum']) && !empty($_POST['sid']) && ($_POST['sum'] == sha1($_POST['sfile'] . $_POST['sid']))) {
            //КОНТРОЛЬНАЯ СУММА ИНФО О ФАЙЛЕ В ПОРЯЛКЕ
            $sid = $_POST['sid'];
            $result = 'info ok';
        }
    }
    } else $result = '';

    if (!empty($result)) {
    //СОХРАНЕНИЕ INFO О ФАЙЛЕ В БД
    /* ИСХОДНЫЕ ДАННЫЕ
        $fileInfo["file_original"];
        $fileInfo["file_name"];
        $fileInfo["file_path"];
        $fileInfo["file_MD5"];
        $fileInfo["file_size"];
        $fileInfo["server_ip"];
    */
//СОЗДАЕМ ЗАПИСЬ В userobjects ТОЛЬКО ПОСЛЕ ТИПИЗАЦИИ
//СОЗДАЕМ ЗАПИСЬ В userfiles
    /*    $uf = new CUserfiles();
                $uf->title = $fileInfo["file_original"];
                $uf->object_id = 0; //ДО ТЕХ ПОР, ПОКА НЕ БУДЕТ ТИПИЗИРОВАН
                $uf->user_id = $userId;
                $uf->type_id = 0; //ДО ТЕХ ПОР, ПОКА НЕ БУДЕТ ТИПИЗИРОВАН
                if ($uf->save(false)) {
                    $userFileId = $uf->id;
                    if (!empty($userFileId)) {
                        //СОЗДАЕМ ЗАПИСЬ files_variants
                        $fv = new CFilesvariants();
                        $fv->file_id = $userFileId;
                        $fv->preset_id = 0; //ДО ТЕХ ПОР, ПОКА НЕ БУДЕТ ТИПИЗИРОВАН
                        $fv->fsize = $fileInfo['file_size'];
                        $fv->fmd5 = $fileInfo['file_MD5'];
                        if ($fv->save(false)) {
                            $fileVariantId = $fv->id;
                            if (!empty($fileVariantId)) {
                                //СОЗДАЕМ ЗАПИСЬ В filelocations
                                $fl = new CFilelocations();
                                $fl->id = $fileVariantId;
                                $fl->server_id = $sid;
                                $fl->state = 0;
                                $fl->modified = date('Y-m-d H:i:s');
                                $fl->fsize = $fv->fsize;
                                $fl->fname = $fileInfo['file_name'];
                                $fl->folder = $fileInfo['file_path'];
                                if ($fl->save(false)) {
                                    $fileLocationId = $fl->id;
                                    $result = 'ok';
                                    //СОХРАНЕНИЕ ЗАВЕРШЕНО

                                    //ПРОВЕРЯЕМ ДОП. ПАРАМЕТРЫ ДЛЯ ТИПИЗАЦИИ
                                    if (!empty($_POST['sparams'])) {
                                        try {
                                            $params = unserialize($_POST['sparams']);
                                            //ПОЛУЧИЛИ МАССИВ С КЛЮЧАМИ = идентификаторам параметров по (product_type_params)
                                            //И СО ЗНАЧЕНИЯМИ ЭТИХ ПАРАМЕТРОВ СООТВЕТСВЕННО
                                        } catch (Exception $e) {
                                            $params = array();
                                        }
                                        if (!empty($params)) {
                                            //СОХРАНЕНИЕ ВСЕХ ЗНАЧЕНИЙ ПАРАМЕТРОВ в userobjects_param_values
                                        }
                                        //ПРИ НАЛИЧИИ ПАРАМЕТРОВ ТИПИЗАЦИИ, ДОБАВЛЕНИЕ В ОЧЕРЕДЬ КОНВЕРТИРОВАНИЯ
                                        //В ТАБЛИЦУ convert_queue
                                    }
                                }
                            }
                        }
                    }
                }
            }
            restore_error_handler();
            die($result);
        }
    */
    /*
        public function actionDownload($user_id = 0)
        {
            if ($user_id > 0) {
    //OK
    //WHat is server doing this

                if (!isset($_GET['data']))
                    die('not enough data');
                $input = @unserialize($_GET['data']);
                if ($input) {
                    $fileloc = new CFilelocations();
                    $fileloc->id = $input['fid'];
                    $fileloc->server_id = $this->server['id'];
                    $fileloc->user_id = $user_id;
                    $fileloc->fsize = $input['fsize'];
                    $fileloc->fname = $input['save'];
                    if (isset($input['folder']))
                        $fileloc->folder = (int)$input['folder'];
                    $fileloc->save();
                    echo "OK";
                    exit();
                } else {
                    echo "Bad data";
                    exit();
                }
            } else
                die("Bad User");
        }
    */
    /*
        public
        function actionTypify($user_id = 0, $data = '')
        {
            if ($user_id > 0) {
                $input = @unserialize($data);
                if (!($input === false)) {
                    $result = 1;
    //$folder = $convertInfo['folder'];
                    $server_id = $this->server->id;
                    $fid = (int)$input['file_id'];
                    $filename = $input['save'];
                    $fsize = $input['fsize'];
                    $folder = $input['folder'];
                    $task_id = (int)$input['task_id'];
                    if ($task_id > 0) {
                        $queue = CConvertQueue::model()->findByAttributes(array('task_id' => $task_id));
                        if (!(queue == null)) { //ЕСЛИ ЕСТЬ ИНФО О ЗАДАНИИ
    //ПРОВЕРКА РЕЗУЛЬТАТА ТИПИЗАЦИИ
                            if (!empty($result)) {
    //ОБРАБОТКА ОШИБКИ ТИПИЗАЦИИ
                            } else {
    //ЧТЕНИЕ ИНФО О ФАЙЛЕ
                                $cmd = Yii::app()->db->createCommand()
                                    ->select('*')
                                    ->from('{{userfiles}}')
                                    ->where('id = ' . $queue['id']);
                                $fileInfo = $cmd->queryRow();
    //ЧТЕНИЕ ИНФО О ЛОКАЦИИ ФАЙЛА
                                $cmd = Yii::app()->db->createCommand()
                                    ->select('*')
                                    ->from('{{filelocations}}')
                                    ->where('id = ' . $queue['id']);
                                $locInfo = $cmd->queryRow();
    //ЧТО ДЕЛАТЬ С ЗАПИСЯМИ О ФАЙЛЕ? УТОЧНИТЬ
    //СОЗДАНИЕ ЗАПИСИ ТИПИЗИРОВАННОГО ОБЪЕКТА
                                $objInfo = array(
                                    'id' => $fid,
                                    'user_id' => $user_id,
                                    'title' => $filename,
                                    'type_id' => $queue->preset_id,
                                );

    //CREATE METAFILE
                                $sql = 'INSERT INTO {{typedfiles}} (id, variant_id, user_id, fsize, title, userobject_id)
                            VALUES (null, 0, ' . $objInfo['user_id'] . ', :fsize, "' . $objInfo['title'] . '", ' . $objInfo['id'] . ')';
                                $cmd = Yii::app()->db->createCommand($sql);
                                $cmd->bindParam(':fsize', $fsize, PDO::PARAM_LOB);
                                $cmd->execute();

    //CREATE FILELOC
                                $sql = 'INSERT INTO {{usertobjects}} (id, user_id, title, type_id)
                            VALUES (' . $objInfo['id'] . ', ' . $objInfo['user_id'] . ', "' . $objInfo['title'] . '", ' . $objInfo['type_id'] . ')';
                                Yii::app()->db->createCommand($sql)->execute();

    //ВЫБИРАЕМ ПЕРЕЧЕНЬ ПАРАМЕТРОВ ДЛЯ ОБЪЕКТОВ ДАННОГО ТИПА
                                $cmd = Yii::app()->db->createCommand()
                                    ->select('ptp.id, ptp.title')
                                    ->from('{{product_type_params}} ptp')
                                    ->join('{{product_types_type_params}} pttp', 'ptp.id = pttp.param_id')
                                    ->where('pttp.type_id = :id');
                                $cmd->bindParam(':id', $type_id, PDO::PARAM_INT);
                                $params = $cmd->queryAll();

                                $height = 200;
                                $width = 400; //ПАРАМЕТРЫ ДЛЯ ТЕСТА
    //ВООБЩЕ ПАРАМЕТРЫ ДОЛЖНЫ ПРИХОДИТЬ ОТДЕЛЬНО. К ОБСУЖДЕНИЮ: ОТКУДА?
                                if (!empty($params)) {
    //СОХРАНЯЕМ ЗНАЧЕНИЯ ПАРАМЕТРОВ ДЛЯ ОБЪЕКОВ ДАННОГО ТИПА
                                    foreach ($params as $p) {
                                        if (!empty($$p['title'])) {
                                            $p_id = $p['id'];
                                            $p_vl = $$p['title'];
                                            $sql = 'INSERT INTO {{tobjects_param_values}} (id, param_id, value, userobject_id)
                                        VALUES (null, ' . $p_id . ',
                                        "' . $p_vl . '", ' . $objInfo['id'] . ')';
                                            Yii::app()->db->createCommand($sql)->execute();
                                        }
                                    }
                                }

    //СОЗДАНИЕ ЛОКАЦИИ ОБЪЕКТА
                                $objLocInfo = array(
                                    'id' => $locInfo['id'],
                                    'user_id' => $locInfo['user_id'],
                                    'server_id' => intval($server_id),
                                    'state' => 0, // ?? ЧТО СЮДА ПРОПИСАТЬ ??
                                    'fsize' => $fsize,
                                    'fname' => $filename,
                                    'folder' => $folder,
                                );
                                $sql = 'INSERT INTO {{userobjectlocations}} (id, user_id, server_id, state, fsize, fname, folder)
                            VALUES (' . $locInfo['id'] . ', ' . $locInfo['user_id'] . ', ' . $locInfo['server_id'] . ',
                            ' . $locInfo['state'] . ', ' . $locInfo['fsize'] . ', "' . $locInfo['fname'] . '", ' . $locInfo['folder'] . ')';
                                Yii::app()->db->createCommand($sql)->execute();

    //ЧИСТКА ОЧЕРЕДИ КОНВЕРТИРОВАНИЯ
                                $sql = 'DELETE FROM {{convert_queue}} WHERE id=' . $queue['id'];
                                Yii::app()->db->createCommand($sql)->execute();
                            }
                        }
                    }
                }
            }
        }
    */
}