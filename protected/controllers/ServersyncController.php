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
        if (isset($_GET['fdata']) && isset($_GET['sdata'])) {
            $check_data = sha1($_GET['fdata'] . Yii::app()->params['uploads_skey']);
            if ($check_data == $_GET['sdata']) {
                $rdata = unserialize(base64_decode($_GET['fdata']));
                if ($rdata) {
                    /*
                        $syncData['variant_id'] = $vid;
                        $syncData['user_ip'] = $user_ip;
                        $syncData['server_ip'] = Yii::app()->params['server_ip'];
                        $syncData['uid'] = $user_id;
                        $syncData['key'] = $user_key;
                    */
                    $variant_id = (int)$rdata['variant_id'];
                    if (CUser::getDownloadSign($variant_id.$rdata['uid'])) {
                        if (CUserfiles::DidUserHaveVariant($rdata['uid'], $variant_id)) {
                            $server_ip = $rdata['server_ip'];
                            $user_ip = (int)$rdata['user_ip'];
                            $zone = CZones::model()->GetZoneByIp($user_ip);
                            $server = CServers::model()->findByAttributes(array('ip' => $server_ip, 'downloads' => 1));
                            if ($server) {
                                $locations = CFilelocations::model()->findAllByAttributes(array('id' => $variant_id, 'server_id' => $server['id']));
                                $answer['folder'] = $locations['folder'];
                                $answer['fname'] = $locations['fname'];
                                $answer['fsize'] = $locations['fsize'];
                            } else {
                                $locations = CFilelocations::getLocationByZone($variant_id, $zone);
                                if (!empty($locations)) {
                                    $answer['server'] = $locations['server_ip'];
                                }
                            }
                        } else {
                            $answer['error'] = 1;
                            $answer['error_msg']="User ".$rdata['uid']." didn't have variant $variant_id";
                        }
                    } else {
                        $answer['error'] = 1;
                        $answer['error_msg']="Bad key";
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
        if (isset($_GET['fdata']) && isset($_GET['sdata'])) {
            $check_data = sha1($_GET['fdata'] . Yii::app()->params['uploads_skey']);
            if ($check_data == $_GET['sdata']) {
                $rdata = unserialize(base64_decode($_GET['fdata']));
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
                if (CUser::checkfishkey($rdata['uid'], $rdata['key'])) {
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
                                            $fileLocationId = $fl->id;
                                            $answer['success'] = 1;
                                            $answer['id'] = $userFileId;
                                            //СОХРАНЕНИЕ ЗАВЕРШЕНО
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

}