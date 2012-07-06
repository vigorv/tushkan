<?php
/**
 * общий класс проверки прав доступа к контроллерам и действиям
 *
 */

define('_IN_BASKET_',		100);
define('_IS_ADMIN_',		70);
define('_IS_MODERATOR_',	60);
define('_IS_USER_',			10);
define('_IS_GUEST_',		0);
class AccessFilter extends CFilter
{
    protected function preFilter($filterChain)
    {
    	$userGroupId = Yii::app()->user->getState('dmUserGroupId');
    	$userPower = Yii::app()->user->getState('dmUserPower');

    	if ((empty($userPower)) && (!empty($userGroupId)))
    	{
	    	$groups = Yii::app()->db->createCommand()
	    		->select('*')
	    		->from('{{user_groups}}')
	    		->queryAll();
	    	foreach ($groups as $g)
	    	{
	    		if ($g['id'] == $userGroupId)
	    		{
	    			$userPower = $g['power'];
	    			Yii::app()->user->setState('dmUserPower', $userPower);
	    		}
	    	}
    	}

    	$access = true;
		switch (get_class($filterChain->controller))
		{
			case "OrdersController":
			case "DevicesController":
			case "UniverseController":
				$access = ($userPower >= _IS_USER_);
				if ($filterChain->action->id == 'typify')
					$access = true;
			break;
			case "PagesController":
				//$access = ($userPower >= _IS_USER_);
			break;
			case "ProductsController":
				if (($filterChain->action->id == 'admin') || ($filterChain->action->id == 'edit') || ($filterChain->action->id == 'form'))
					$access = ($userPower >= _IS_MODERATOR_);
				if (($filterChain->action->id == 'tocloud'))
					$access = ($userPower >= _IS_USER_);
				if (($filterChain->action->id == 'Fillpartnerproducts'))
					$access = ($userPower >= _IS_GUEST_);
			break;

			case "RegisterController":
				if (($filterChain->action->id == 'logout')
					|| ($filterChain->action->id == 'profile')
					|| ($filterChain->action->id == 'personal')
					|| ($filterChain->action->id == 'tariff')
					|| ($filterChain->action->id == 'feedback')
				)
					$access = ($userPower >= _IS_USER_);
			break;

			case "PersonaldataparamsController":
			case "ParamsController":
			case "TypesController":
			case "PaysystemsController":
			case "AdminController":
				$access = ($userPower >= _IS_MODERATOR_);
			break;

			case "UsersController":
				$access = ($userPower >= _IS_ADMIN_);
			break;

			case "PaysController":
				if (Yii::app()->user->isGuest
					&& (($filterChain->action->id == 'index') || ($filterChain->action->id == 'do')))
				{
					$access = false;
				}
			break;
		}

		if (!$access)
		{
			Yii::app()->user->setFlash('error', Yii::t('common', 'Access denied. Authentication required'));

			if (Yii::app()->user->isGuest)
			{
				//$filterChain->controller->redirect('/register/login');
				Yii::app()->user->setReturnUrl(Yii::app()->request->getUrl());
				Yii::app()->request->redirect('/register/login');
			}
			else
			{
				//$filterChain->controller->redirect('/register/login');
				Yii::app()->request->redirect('/');
			}
		}

		$access = $this->checkBans();

        return $access; // false — для случая, когда действие не должно быть выполнено
    }

    protected function postFilter($filterChain)
    {
        // код, выполняемый после выполнения действия
    }

    public function checkBans()
    {
return true;//ЗАГЛУШКА
    	$userBans = Yii::app()->user->getState('userBans');
		if (!empty($userBans))
		{
			$now = date('Y-m-d H:i:s');
			foreach ($userBans as $b)
			{
				if (($now >= $b['start']) && ($now <= $b['finish']))
				{
					$readonly = empty($b['state']);
				}

				if ($readonly)
				{
					//ПРОВЕРЯЕМ ПО СПИКУ КОНТРОЛЛЕРОВ И ДЕЙСТВИЙ, ДОСТУПНЫХ В РЕЖИМЕ "ТОЛЬКО ДЛЯ ЧТЕНИЯ"
				}
			}
			return false;
		}
		return true;
    }
}