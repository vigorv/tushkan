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

		switch (get_class($filterChain->controller))
		{
			case "PaysController":
				if (Yii::app()->user->isGuest && ($filterChain->action->id == 'index'))
				{
					Yii::app()->user->setFlash('error', Yii::t('common', 'Access denied. Authentication required'));
					$filterChain->controller->redirect('/register/login');
				}
			break;
		}
        return true; // false — для случая, когда действие не должно быть выполнено
    }

    protected function postFilter($filterChain)
    {
        // код, выполняемый после выполнения действия
    }
}