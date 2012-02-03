<?php
/**
 * общий класс проверки прав доступа к контроллерам и действиям
 *
 */

define('_IS_ADMIN_',		70);
define('_IS_MODERATOR_',	60);
define('_IS_USER_',			10);
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
			case "UniverseController":
				$access = ($userPower >= _IS_USER_);
			break;
			case "PagesController":
				//$access = ($userPower >= _IS_USER_);
			break;
			case "ProductController":
				if (($filterChain->action->id == 'admin') || ($filterChain->action->id == 'edit') || ($filterChain->action->id == 'form'))
					$access = ($userPower >= _IS_MODERATOR_);
				if (($filterChain->action->id == 'tocloud'))
					$access = ($userPower >= _IS_USER_);
			break;

			case "RegisterController":
				if (($filterChain->action->id == 'logout')
					|| ($filterChain->action->id == 'profile')
					|| ($filterChain->action->id == 'personal')
					|| ($filterChain->action->id == 'tariff')
				)
					$access = ($userPower >= _IS_USER_);
			break;

			case "ParamsController":
			case "TypesController":
			case "AdminController":
				$access = ($userPower >= _IS_MODERATOR_);
			break;

			case "UserController":
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
        return $access; // false — для случая, когда действие не должно быть выполнено
    }

    protected function postFilter($filterChain)
    {
        // код, выполняемый после выполнения действия
    }
}