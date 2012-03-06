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
class CUser extends CActiveRecord {

    /**
     *
     * @param string $className
     * @return CUser
     */
    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public function defaultScope() {
	return array(
	    'alias' => 'u',
	);
    }

    public function tableName() {
	return '{{users}}';
    }

    /**
     *
     * @param type $user_id
     * @return type 
     */
    public static function KPT($user_id) {
	$sid = CUser::model()->findByPk($user_id)->sess_id;
	$kpt = md5($user_id . $sid . "I am robot");
	return $kpt;
    }

    /**
     *
     * @param integer $user_id
     * @return array UserInfo
     */
    public function getUserInfo($user_id) {
	return Yii::app()->db->createCommand()
			->select('u.id, u.email,b.balance,u.free_limit,t.size_limit')
			->from('{{users}} u')
			->leftJoin('{{balance}} b', 'b.user_id = u.id')
			->leftJoin('{{tariffs_users}} tu', 'tu.user_id=u.id')
			->leftJoin('{{tariffs}} t', ' t.id = tu.tariff_id')
			->where('u.id = ' . $user_id)
			->queryRow();
    }

}