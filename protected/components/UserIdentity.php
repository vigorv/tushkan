<?php

/**
 * расширяем класс аутентификации
 * полностью исключаем стандартный для фреймворка механизм хранения данных в куках
 * класс поддерживает механизмы автологина
 * реализован алгоритм генерации (и перегенерации при авторизации во время сессии) хэш-ключа с хранением в БД
 *
 */
class UserIdentity extends CUserIdentity
{
	public $email;
	public $rememberMe = 0;
	/**
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
		$this->errorCode = self::ERROR_NONE;
		if (!$this->checkAuthInfo())
		{
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
			/*
			ЕСЛИ ДАННЫЕ В СЕССИИ И В КУКИ НЕ СОВПАДАЮТ
			ПЫТАЕМСЯ АВТОРИЗОВАТЬСЯ ПО КУКИ
			ИЛИ ЧЕРЕЗ ФОРМУ АВТОРИЗАЦИИ (и БД)
			*/
			if (!empty($this->email))
			{
				$cmd = Yii::app()->db->createCommand()
					->select('*')
					->from('{{users}}')
					->where('email = :email')
					->limit(1);
				$cmd->bindParam(':email', $this->email, PDO::PARAM_STR);
				$userInfo = $cmd->queryRow();

				if (($userInfo['email'] == $this->email) && ($userInfo['pwd'] == $this->transformPassword($userInfo)))
				{
					$this->errorCode = self::ERROR_NONE;
					$this->saveAuthInfo($userInfo);
				}
			}
		}
		return !$this->errorCode;
	}

	/**
	 * СРАВНИВАЕМ ДАННЫЕ СЕССИИ И КУКИ
	 * пробуем восстановить сессию по данным из куки (autologin)
	 *
	 * @return boolean
	 */
	public function checkAuthInfo()
	{
		$cookies = Yii::app()->request->getCookies();
		if (!empty($cookies['dmUserId']))
		{
			$dmUserId = $cookies['dmUserId']->value;
		}
		if (!empty($cookies['dmUserHash']))
		{
			$dmUserHash = $cookies['dmUserHash']->value;
		}
		if (!empty($dmUserId) && !empty($dmUserHash)
				&& ($dmUserId == Yii::app()->user->getState('dmUserId'))
				&& ($dmUserHash == Yii::app()->user->getState('dmUserHash'))
				&& ($this->getUserIp() == Yii::app()->user->getState('dmUserIp'))
				&& (Yii::app()->user->getState('dmHashExpired') > time())
			)
		{
			$this->errorCode = self::ERROR_NONE;
			return true;
		}

		if (!empty($dmUserId) && !empty($dmUserHash))
		{
			//ПРОБУЕМ ВОССТАНОВИТЬ АВТОРИЗАЦИЮ ЧЕРЕЗ КУКИ
			$cmd = Yii::app()->db->createCommand()
				->select('*')
				->from('{{users}}')
				->where('id = :id')
				->limit(1);
			$cmd->bindParam(':id', $dmUserId, PDO::PARAM_INT);
			$userInfo = $cmd->queryRow();

			if (($userInfo['sess_id'] == $dmUserHash)
			 	&& ($this->createHash($userInfo) == $dmUserHash)
			 )
			{
				$this->errorCode = self::ERROR_NONE;
				//ВОССТАНАВЛИВАЕМ АВТОРИЗАЦИЮ И ПЕРЕГЕНЕРИРУЕМ ХЭШ СЕССИИ АВТОРИЗАЦИИ
				$this->rememberMe = 1; //ЕСЛИ ВОССТАНАВЛИВАЕМ ИЗ КУК? ЗНАЧИТ И ДАЛЕЕ БУДЕМ ЭТИ КУКИ ПОМНИТЬ
				$this->saveAuthInfo($userInfo);
				return true;
			}
			$this->dropAuthInfo();
		}
		return false;
	}

	/**
	 * шифрование пароля пользователя
	 *
	 * @param mixed $userInfo - запись пользователя в БД в виде массива
	 * @return string
	 */
	public function transformPassword($userInfo)
	{
		return md5($this->password . $userInfo['salt']);
	}

	/**
	 * подготовка IP пользователя
	 *
	 * @return string
	 */
	public function getUserIp()
	{
		$ip = explode('.', $_SERVER['REMOTE_ADDR']);
		array_pop($ip);//ОТРЕЗАЕМ ДО ПОДСЕТИ
		$ip = implode('.', $ip);
		return $ip;
	}

	public function createHash($userInfo)
	{
		$ip = $this->getUserIp();
		$hash = md5($userInfo['pwd'] . $ip . $userInfo['lastvisit']);
		return $hash;
	}

	/**
	 * удалить данные авторизации и разлогинить пользователя
	 *
	 */
	public function dropAuthInfo()
	{
		Yii::app()->user->clearStates();

		Yii::app()->request->cookies->remove('dmUserId');
		Yii::app()->request->cookies->remove('dmUserHash');

		Yii::app()->user->logout();
	}

	/**
	 * данные сессии для авторизации сохраняем в сессии пользователя, куках и корректируем данные в БД
	 *
	 * @param mixed $userInfo - данные записи пользователя в БД
	 */
	public function saveAuthInfo($userInfo)
	{
		$id = $userInfo['id'];
		$ip = $this->getUserIp();
		$users = new CUser();
		$userRecord = $users->findByPk($id);
		$userInfo['lastvisit'] = date('Y-m-d H:i:s', time());
		$hash = $this->createHash($userInfo);

		//ПРОВЕРЯЕМ МНФО ПО БАНАМ (САМЫЕ СУРОВЫЕ В НАЧАЛЕ)
		$bansInfo = Yii::app()->db->createCommand()
			->select('*')
			->from('{{bannedusers}}')
			->where('user_id = ' . $id)
			->order('state DESC')
			->queryAll();

		//СОХРАНИЛИ В СЕССИЮ
		Yii::app()->user->setState('dmUserId', $id);
		Yii::app()->user->setState('dmUserGroupId', $userInfo['group_id']);
		Yii::app()->user->setState('dmUserHash', $hash);
		Yii::app()->user->setState('dmUserIp', $ip);
		Yii::app()->user->setState('dmHashExpired', time() + Yii::app()->params['tushkan']['hashDuration']);
		Yii::app()->user->setState('dmUserBans', $bansInfo);

		//СОХРАНИЛИ В КУКИ
		$dmUserId = new CHttpCookie('dmUserId', $id);
		$dmUserHash = new CHttpCookie('dmUserHash', $hash);
		if ($this->rememberMe)
		{
			$expire = time() + 3600*24*30; // 30 days
			$dmUserId->expire = $expire;
			$dmUserHash->expire = $expire;
		}
		Yii::app()->request->cookies->add('dmUserId', $dmUserId);
		Yii::app()->request->cookies->add('dmUserHash', $dmUserHash);

		$userRecord->saveAttributes(array('sess_id' => $hash, 'lastvisit' => $userInfo['lastvisit']));

		if (empty($userInfo['name']))
			$userInfo['name'] = Yii::t('common', 'User');
		Yii::app()->user->setName($userInfo['name']);
		Yii::app()->user->setId($userInfo['id']);
	}
}