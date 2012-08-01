<?php

class UsersController extends Controller {

    private $_crumbs = array();

    public function actionAdmin()
    {
		$this->layout = '/layouts/admin';

		$filterCondition = array();
		$filterInfo = Utils::getFilterInfo();
		if (!empty($filterInfo['search']))
		{
			$filterCondition['search'] = '(name LIKE :name OR email LIKE :email)';
		}

		$cmd = Yii::app()->db->createCommand()
			->select('count(id)')
			->from('{{users}}');
		if (!empty($filterCondition))
		{
			$cmd->where(implode(' AND ', $filterCondition));

			if (!empty($filterInfo['search']))
			{
				$searchValue = '%' . $filterInfo['search']['value'] . '%';
				$cmd->bindParam(':name', $searchValue, PDO::PARAM_STR);
				$cmd->bindParam(':email', $searchValue, PDO::PARAM_STR);
			}
		}
		$count = $cmd->queryScalar();
		$paginationParams = Utils::preparePagination('/users/admin', $count);
		$users = array();

		if ($count)
		{
			$cmd = Yii::app()->db->createCommand()
				->select('id')
				->from('{{users}}');
			if (!empty($filterCondition))
			{
				$cmd->where(implode(' AND ', $filterCondition));

			}

			$sortInfo = Utils::getSortInfo();
			if (!empty($sortInfo))
			{
				$sortCondition = array();
				foreach (array('name', 'email', 'created') as $srt)
					if (!empty($sortInfo[$srt]))
						$sortCondition[$srt] = $sortInfo[$srt]['name'] . ' ' . $sortInfo[$srt]['direction'];
			}
			if (!empty($sortCondition))
			{
				$cmd->order(implode(',', $sortCondition));
			}
			$cmd->limit($paginationParams['limit']);
			$cmd->offset($paginationParams['offset']);
/*
//FUCK НЕ НАКЛАДЫВАЮТСЯ ЛИМИТЫ НА ЗАПИСЫ С УСЛОВИЯМИ WHERE
//КАК ПОФИКСИТЬ? bindParam вызывать ПОСЛЕ ФОРМИРОВАНИЯ ВСЕХ УСЛОВИЙ ЗАПРОСА
$sql = $cmd->getText(); //ВОЗВРАЩАЕТ SQL код, сформированный с помощью createCommand
echo $sql;
exit;
//*/
			if (!empty($filterInfo['search']))
			{
				$searchValue = '%' . $filterInfo['search']['value'] . '%';
				$cmd->bindParam(':name', $searchValue, PDO::PARAM_STR);
				$cmd->bindParam(':email', $searchValue, PDO::PARAM_STR);
			}
			$pst = $cmd->queryAll();

			if (!empty($pst))
			{
				$pst = implode(',', Utils::arrayToKeyValues($pst, 'id', 'id'));
			}
			else
				$pst = 0;
/*
echo '<pre>';
var_dump($paginationParams);
echo '</pre>';
echo '<pre>';
var_dump($pst);
echo '</pre>';
//*/
			$cmd = Yii::app()->db->createCommand()
				->select('u.id, u.name, u.email, u.created, u.lastvisit, u.active, u.confirmed, u.sess_id, g.title as gtitle')
				->from('{{users}} u')
				->leftJoin('{{user_groups}} g', 'g.id=u.group_id')
				->where('u.id IN (' . $pst . ')')
				->group('u.id');
			if (!empty($sortCondition))
			{
				$cmd = $cmd->order(implode(',', $sortCondition));
			}
			$users = $cmd->queryAll();
			$dt = date('Y-m-d H:i:s');
			$banInfo = Yii::app()->db->createCommand()
				->select('u.id, u.user_id, u.start, u.finish, u.state, u.reason')
				->from('{{bannedusers}} u')
				->where('u.user_id IN (' . $pst . ') AND u.start < "' . $dt . '" AND (u.finish = "0000-00-00 00:00:00" OR u.finish > "' . $dt . '")')
				->order('u.user_id')
				->queryAll();

		}
		$this->render('admin', array('users' => $users, 'banInfo' => $banInfo, 'paginationParams' => $paginationParams));
    }

    /**
     * действие формы добавления
     *
     */
    public function actionForm() {
	$this->layout = '/layouts/admin';
	$this->_crumbs = array(Yii::t('users', 'Add User'));

	$gLst = Yii::app()->db->createCommand()
		->select('id, title')
		->from('{{user_groups}}')
		->order('power ASC')
		->queryAll();

	$trialInfo = Yii::app()->db->createCommand()
		->select('id, size_limit')
		->from('{{tariffs}}')
		->where('id = ' . Yii::app()->params['tushkan']['trialTariffId'])
		->queryRow();

	$groups = array();
	foreach ($gLst as $g) {
	    $groups[$g['id']] = $g['title'];
	}

	$userForm = new UserForm();
	if (isset($_POST['UserForm'])) {
	    $userForm->attributes = $_POST['UserForm'];

	    if ($userForm->validate()) {
		$users = new CUser();
		$attrs = $userForm->getAttributes();
		$attrs['salt'] = substr(md5(time()), 0, 5); //СОЛЬ ГЕНЕРИРУЕМ ПРИ ДОБАВЛЕНИИ
		$attrs['created'] = date('Y-m-d H:i:s');
		$attrs['lastvisit'] = date('Y-m-d H:i:s');
		$attrs['server_id'] = 0;
		$attrs['sess_id'] = 0;
		$attrs['free_limit'] = $trialInfo['size_limit'];
		$attrs['confirmed'] = 1;
		if (empty($attrs['active'])) {
		    $attrs['active'] = 1;
		}
		foreach ($attrs as $k => $v) {
		    $users->{$k} = $v;
		}

		$this->identity->password = $users->pwd;
		$users->pwd = $this->identity->transformPassword($attrs); //ШИФРУЕМ ПАРОЛЬ ПО ПРАВИЛАМ АВТОРИЗАТОРА
		$users->sess_id = $this->identity->createHash(array('pwd' => $users->pwd, 'lastvisit' => $users->lastvisit));
		if ($users->save())
		{
			$userId = Yii::app()->db->getLastInsertID('{{users}}');
			$tariffRelation = array('tariff_id' => $trialInfo['id'], 'user_id' => $userId, 'switch_to' => 0);
			$cmd = Yii::app()->db->createCommand()->insert('{{tariffs_users}}', $tariffRelation);
		}

		Yii::app()->user->setFlash('success', Yii::t('users', 'User saved'));
	    }
	}
	$this->render('form', array('model' => $userForm, 'groups' => $groups));
    }

    public function actionBan($id = 0)
    {
    	$id = intval($id);
    	if (!empty($id))
    	{
    		$banInfo = array(
    			'user_id'	=> $id,
    			'start'		=> date('Y-m-d H:i:s'),
    			'finish'	=> '0000-00-00 00:00:00',
    			'reason'	=> _BANREASON_VIOLATION_,
    			'state'		=> _BANSTATE_READONLY_,
    		);
    		Yii::app()->db->createCommand()->insert('{{bannedusers}}', $banInfo);
    	}
		$this->redirect('/users/admin');
    }

    public function actionunban($id = 0)
    {
    	$id = intval($id);
    	if (!empty($id))
    	{
    		$sql = 'DELETE FROM {{bannedusers}} WHERE id = ' . $id;
    		Yii::app()->db->createCommand($sql)->execute();
    	}
		$this->redirect('/users/admin');
    }

    /**
     * действие редактирования
     *
     */
    public function actionEdit($id = 0) {
	$this->layout = '/layouts/admin';
	$this->_crumbs = array(Yii::t('common', 'edit'));

	$cmd = Yii::app()->db->createCommand()
		->select('*')
		->from('{{users}}')
		->where('id=:id');
	$cmd->bindParam(':id', $id, PDO::PARAM_INT);
	$info = $cmd->queryRow();

	$gLst = Yii::app()->db->createCommand()
		->select('id, title')
		->from('{{user_groups}}')
		->order('power ASC')
		->queryAll();

	$groups = array();
	foreach ($gLst as $g) {
	    $groups[$g['id']] = $g['title'];
	}

	$userForm = new UserForm();
	if (isset($_POST['UserForm'])) {
	    $userForm->attributes = $_POST['UserForm'];
	    $attrs = $userForm->getAttributes();
	    if ($userForm->validate()) {
		$users = new CUser;
		foreach ($attrs as $k => $v) {
		    if (empty($v)) {
			$attrs[$k] = $info[$k];
		    }
		    $users->{$k} = $attrs[$k];
		}

		if ($attrs['pwd'] <> $info['pwd']) {//ПЕРЕКОДИРУЕМ ПАРОЛЬ, ЕСЛИ ТОЛЬКО УКАЗАЛИ НОВЫЙ
		    $this->identity->password = $attrs['pwd'];
		    $users->pwd = $this->identity->transformPassword($attrs); //ШИФРУЕМ ПАРОЛЬ ПО ПРАВИЛАМ АВТОРИЗАТОРА
		}
		$users->isNewRecord = false;
		$users->save();

		Yii::app()->user->setFlash('success', Yii::t('users', 'User saved'));
	    }
	    $info = $attrs;
	}

	$this->render('edit', array('model' => $userForm, 'groups' => $groups, 'info' => $info));
    }

    /**
     * действие для AJAX валидации/сохранения
     *
     * ! НЕ ДОДЕЛАНО !
     *
     */
    public function actionSave($id = 0) {
	$this->layout = '/layouts/ajax';
	if (!empty($_POST)) {
	    $sql = 'UPDATE';
	    $cmd = Yii::app()->db->createCommand($sql);
	    $cmd->bindParam(':id', $id, PDO::PARAM_INT);
	    $info = $cmd->query();
	    $result = 'ok';
	}
	$this->render('save', array('result' => $result));
    }

    /**
     * действие удаления
     *
     */
    public function actionHide($id = 0) {
		$this->layout = '/layouts/admin';
		$this->_crumbs = array(Yii::t('common', 'delete'));

		$sql = 'UPDATE {{users}} SET active=' . _IS_ADMIN_ . ' WHERE id = :id';
		$cmd = Yii::app()->db->createCommand($sql);
		$cmd->bindParam(':id', $id, PDO::PARAM_INT);
		$info = $cmd->query();

		Yii::app()->user->setFlash('success', Yii::t('users', 'User hided'));
		$this->redirect('/users/admin');
	}

    /**
     * действие удаления
     *
     */
    public function actionDelete($id = 0) {
		$this->layout = '/layouts/admin';
		$this->_crumbs = array(Yii::t('common', 'delete'));

		CUser::deleteUser($id);

		Yii::app()->user->setFlash('success', Yii::t('users', 'User deleted'));
		$this->redirect('/users/admin');
	}

	/**
	 * действие восстановления удаленного пользователя
	 *
	 */
	public function actionRestore($id = 0) {
		$this->layout = '/layouts/admin';
		$this->_crumbs = array(Yii::t('common', 'delete'));

		$sql = 'UPDATE {{users}} SET active=0 WHERE id = :id';
		$cmd = Yii::app()->db->createCommand($sql);
		$cmd->bindParam(':id', $id, PDO::PARAM_INT);
		$info = $cmd->query();

		Yii::app()->user->setFlash('success', Yii::t('users', 'User restored'));
		$this->redirect('/users/admin');
    }

    /**
     * используем этот callback для генерирования строки обратной навигации
     *
     * @return boolean
     */
    public function beforeRender($view) {
	parent::beforeRender($view);

	$controllerRoot = array(Yii::t('users', 'Administrate users'));
	if (!empty($this->_crumbs)) {
	    $controllerRoot = array(Yii::t('users', 'Administrate users') => $this->createUrl('users/admin'));
	}
	$this->breadcrumbs = array_merge(
		array(
	    Yii::t('common', 'Admin index') => array($this->createUrl('admin')),
		), $controllerRoot, $this->_crumbs
	);

	return true;
    }

}