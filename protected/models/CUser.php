<?php
/**
 *  модель пользователей
 * @property $id
 * @property $email
 * @property $name
 * @property $group_id
 * @property $pwd
 * @property $active
 * @property $server_id
 * @property $gtitle;
 * @property $sess_id;
 * @property $free_limit
 * @property $confirmed
 *
 */
class CUser extends CActiveRecord
{

    /**
     *
     * @param string $className
     * @return CUser
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function defaultScope()
    {
        return array(
            'alias' => 'u',
        );
    }

    public function tableName()
    {
        return '{{users}}';
    }

    /**
     * @static
     * @param $info
     * @return string
     */
    public static function getDownloadSign($info){
        return sha1($info.'_make_download_allow');
    }

    /**
     * генерация ключа пользователя, для межсерверных запросов (к файловым серверам итп)
     *
     * @param integer $user_id
     * @param date $dt (YYYY-MM-DD)
     * @return string
     */
    public static function getfishkey($user_id, $dt)
    {
        $salt = CUser::model()->findByPk($user_id)->salt;
        $key = sha1($user_id . $dt . $salt);
        return $key;
    }

    public static function checkfishkey($uid, $key)
    {
        $key1 = CUser::getfishkey($uid, date('Y-m-d'));
        $key2 = CUser::getfishkey($uid, date('Y-m-d', time() - 3600 * 24));
        if (($key == $key1) || ($key == $key2)) {
            return true;
        } else return false;
    }


    /**
     *
     * @param integer $user_id
     * @return array UserInfo
     */
    public function getUserInfo($user_id)
    {
        return Yii::app()->db->createCommand()
            ->select('u.id, u.email,b.balance,u.free_limit,t.size_limit')
            ->from('{{users}} u')
            ->leftJoin('{{balance}} b', 'b.user_id = u.id')
            ->leftJoin('{{tariffs_users}} tu', 'tu.user_id=u.id')
            ->leftJoin('{{tariffs}} t', ' t.id = tu.tariff_id')
            ->where('u.id = ' . $user_id)
            ->queryRow();
    }

    public static function UKey($record)
    {
        return md5($record->id . $record->pwd . 'magic' . $record->lastvisit);
    }

    /**
     * @static
     * @param $pwd
     * @param $salt
     * @return string
     *  App Register HASH
     */
    public static function makeHash($pwd, $salt) {
        return md5($pwd . Yii::app()->getBaseUrl(true) . $salt.'magic_hash');
    }

    public static function createMagicKeyForUser($user_id=0){
        if ($user_id>0){
            $key = md5(md5('magic_key'.time()).$user_id);
            Yii::app()->db->createCommand(" INSERT INTO {{user_keys}} (user_id,gen_time,hash) VALUES ('".(int)$user_id."',CURRENT_TIMESTAMP,'".$key."') ON DUPLICATE KEY UPDATE hash='$key',gen_time = CURRENT_TIMESTAMP" )->execute();
            return $key;
        }
    }

    public static function checkMagicKeyForUser($user_id=0,$key=''){
        if ($user_id>0){
            $key = FILTER_VAR($key,FILTER_SANITIZE_STRING);
            $result = Yii::app()->db->createCommand("SELECT Count(user_id) as count FROM {{user_keys}} WHERE user_id =".(int)$user_id." AND hash='".$key."'")->queryScalar();
            return $result;
        }
    }

    public static function deleteMagicKeyForUser($user_id=0){
        if ($user_id>0){
           Yii::app()->db->createCommand("DELETE FROM {{user_keys}} WHERE user_id =".(int)$user_id." LIMIT 1")->execute();
        }
    }


    /**
     * @static
     * @param $user_id
     * @param $add_size
     * @return mixed
     */

    public static function UpdateSpaceInfo($user_id,$add_size){
        $add_size = $add_size >> 20;
        return Yii::app()->db->createCommand("UPDATE {{users}} set free_limit = free_limit - $add_size where id = $user_id")->execute();
    }

	public static function deleteUser($id = 0)
	{
    	$id = intval($id);

		$sqls[] = 'DELETE FROM {{actual_rents}} WHERE user_id = ' . $id;
		$sqls[] = 'DELETE FROM {{balance}} WHERE user_id = ' . $id;
		$sqls[] = 'DELETE FROM {{bannedusers}} WHERE user_id = ' . $id;
		$sqls[] = 'DELETE FROM {{convert_queue}} WHERE user_id = ' . $id;
		$sqls[] = 'DELETE FROM {{income_queue}} WHERE user_id = ' . $id;
		$sqls[] = 'DELETE FROM {{debits}} WHERE user_id = ' . $id;

		$orders = Yii::app()->db->createCommand()
			->select('id')
			->from('{{orders}}')
			->where('user_id = ' . $id)
			->queryAll();
		if (!empty($orders))
		{
			foreach ($orders as $o)
			{
				$sqls[] = 'DELETE FROM {{order_items}} WHERE order_id = ' . $o['id'];
			}
			$sqls[] = 'DELETE FROM {{orders}} WHERE user_id = ' . $id;
		}

		$sqls[] = 'DELETE FROM {{payments}} WHERE user_id = ' . $id;
		$sqls[] = 'DELETE FROM {{personaldata_values}} WHERE user_id = ' . $id;
		$sqls[] = 'DELETE FROM {{tariffs_users}} WHERE user_id = ' . $id;
		$sqls[] = 'DELETE FROM {{typedfiles}} WHERE user_id = ' . $id;
		$sqls[] = 'DELETE FROM {{userdevices}} WHERE user_id = ' . $id;
		$sqls[] = 'DELETE FROM {{user_subscribes}} WHERE user_id = ' . $id;

		$userobjects = Yii::app()->db->createCommand()
			->select('id')
			->from('{{userobjects}}')
			->where('user_id = ' . $id)
			->queryAll();
		if (!empty($userobjects))
		{
			foreach ($userobjects as $o)
			{
				$sqls[] = 'DELETE FROM {{userobjects_param_values}} WHERE object_id = ' . $o['id'];
			}
			$sqls[] = 'DELETE FROM {{userobjects}} WHERE user_id = ' . $id;
		}

		$userfiles = Yii::app()->db->createCommand()
			->select('id')
			->from('{{userfiles}}')
			->where('user_id = ' . $id)
			->queryAll();
		if (!empty($userfiles))
		{
			foreach ($userfiles as $o)
			{
				CUserfiles::RemoveFile($id, $o['id']);//ПОКА УДАЛЯЕТ ТОЛЬКО НЕТИПИЗИРОВАННЫЕ ФАЙЛЫ
				$sqls[] = 'DELETE FROM {{filelocations}} WHERE id = ' . $o['id'];//СТРАХУЕМ (ДЛЯ УДАЛЕНИЯ ТИПИЗИРОВАННЫХ ФАЙЛОВ)
			}
			$sqls[] = 'DELETE FROM {{userfiles}} WHERE user_id = ' . $id;//СТРАХУЕМ
		}

		$sqls[] = 'DELETE FROM {{users}} WHERE id = ' . $id;

		if (!empty($sqls))
		{
			foreach ($sqls as $s)
			{
				Yii::app()->db->createCommand($s)->execute();
			}
		}

	}
}