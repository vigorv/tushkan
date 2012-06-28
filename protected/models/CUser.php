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
     * @param $user_id
     * @param $add_size
     * @return mixed
     */

    public static function UpdateSpaceInfo($user_id,$add_size){
        return Yii::app()->db->createCommand("UPDATE {{users}} set free_limit = free_limit - $add_size where id = $user_id")->execute();
    }


}