<?php
	if (empty($result))
	{
		$msg = Yii::t('common', 'Changes was successfully saved');
		$state = 'success';
	}
	else
	{
		$msg = Yii::t('common', 'Request cannot be processed');
		$state = 'danger';
	}
	echo '<h3>' . $msg . '</h3>';
?>
<a href="#" class="btn btn-<?php echo $state; ?>" data-dismiss="modal">Ok</a>