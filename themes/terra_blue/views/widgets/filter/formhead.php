<?php
	$onSubmit = '';
	switch ($method)
	{
		case "GET":
			if (empty($formAction))
			{
				$formAction = '/' . Yii::app()->getController()->getId()
					 . '/' . Yii::app()->getController()->getAction()->id;
			//РЕАЛИЗОВАТЬ JS ФУНКЦИИ ОБРАБОТКИ ПОЛЕЙ ФОРМЫ И ФОРМИРОВАНИЯ СТРОКИ ЗАПРОСА С ПАРАМЕТРАМИ/ЗНАЧЕНИЯМИ ФИЛЬТРА
				$onSubmit = 'return false;';
?>
<?php
			}
		break;
		default://POST
			$formAction = '/' . Yii::app()->getController()->getId() . '/postfilter';
			$action = Yii::app()->getController()->getAction()->id;
	}
?>

	<form method="<?php echo $method; ?>" action="<?php echo $formAction; ?>" onsubmit="<?php echo $onSubmit; ?>">
<?php
	if (!empty($action))
		echo '<input type="hidden" name="action" value="' . $action . '" />';