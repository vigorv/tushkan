<?php
	if (empty($ajaxResult))
	{
?>
<script type="text/javascript">
	function editText(obj)
	{
		$(obj).parent().hide();
		t = $("#" + obj.id + "_static_value").text();
		$("#" + obj.id + "_edit_value").val(t);
		$("#" + obj.id + "_edit").show();
		$("#" + obj.id + "_edit_value").focus();
		return false;
	}

	function showText(obj)
	{
		$(obj).parent().hide();
		$("#" + obj.name + "_static").show();
		return false;
	}

	function keyUpText(obj, key)
	{
		if (key == 27)
		{
			showText(obj);
		}
		if (key == 13)
		{
			$(obj).parent().hide();
			$("#" + obj.name + "_wait").show();

			$.post('/register/personal', {action: obj.name, value: obj.value}, function(data){
				$("#" + obj.name + "_wait").hide();
				if (data == 'ok')
				{
					$("#" + obj.name + "_static_value").text(obj.value);
					$("#" + obj.name + "_static").show();
				}
				else
				{
					alert('<?php echo Yii::t('common', 'Error'); ?>! ' + data);
					$("#" + obj.name + "_edit").show();
				}
			});
			return false;
		}
		return true;
	}

	function editPassword(obj)
	{
		$(obj).parent().hide();
		$("#" + obj.id + "_edit").show();
		$("#" + obj.id + "_edit_value").focus();
		return false;
	}

	function keyUpPassword(o, key)
	{
		if (key == 27)
		{
			obj = document.getElementById("pwd_edit_value");
			showText(obj);
		}
		if (key == 13)
		{
			obj = document.getElementById("pwd_edit_value");
			obj2 = document.getElementById("pwd_edit_value2");
			$(obj).parent().hide();
			$("#" + obj.name + "_wait").show();

			$.post('/register/personal', {action: obj.name, value: obj.value, value2: obj2.value}, function(data){
				$("#" + obj.name + "_wait").hide();
				if (data == 'ok')
				{
					$("#" + obj.name + "_edit_value").val('');
					$("#" + obj.name + "_edit_value2").val('');
					$("#" + obj.name + "_static").show();
				}
				else
				{
					alert('<?php echo Yii::t('common', 'Error'); ?>! ' + data);
					$("#" + obj.name + "_edit").show();
				}
			});
			return false;
		}
		return true;
	}
</script>
<?php
	if (!empty($info))
	{
		if (empty($info['name']))
			$info['name'] = Yii::t('users', 'Username not specified');
		$pNames = array('name' => 'text', 'email' => 'text', 'pwd' => 'password');
		echo '<h3>' . Yii::t('params', 'Main parameters') . '</h3>';
		foreach ($pNames as $nm => $tp)
		{
			echo '
				<h4>' . Yii::t('params', $nm) . ':
					<div id="' . $nm . '_static">
			';
			if ($tp == 'password')
				echo '<span id="' . $nm . '_static_value">***********</span> <a id="' . $nm . '" href="#" onclick="return editPassword(this);">' . Yii::t('common', 'Edit') . '</a>';
			else
				echo '<span id="' . $nm . '_static_value">' . $info[$nm] . '</span> <a id="' . $nm . '" href="#" onclick="return editText(this);">' . Yii::t('common', 'Edit') . '</a>';
			echo'
					</div>
					<div id="' . $nm . '_edit" style="display: none">
						';
			switch ($tp)
			{
				case "text":
					echo CHtml::textField($nm, $info[$nm], array('id' => $nm . '_edit_value', 'onblur' => 'return keyUpText(this, 27);', 'onkeyup' => 'return keyUpText(this, event.keyCode);'));
				break;
				case "password":
					echo Yii::t('params', 'New ' . $nm) . '<br />
					' . CHtml::passwordField($nm, '', array('id' => $nm . '_edit_value', 'onkeyup' => 'return keyUpPassword(this, event.keyCode);')) . '<br />
					' . Yii::t('params', 'Old ' . $nm) . '<br />
					' . CHtml::passwordField($nm . '2', '', array('id' => $nm . '_edit_value2', 'onkeyup' => 'return keyUpPassword(this, event.keyCode);')) . '<br />';
				break;
			}
			echo '
					</div>
					<div id="' . $nm . '_wait" style="display: none">
						' . Yii::t('common', 'Please wait...') . '
					</div>
				</h4>
			';
		}

		if (!empty($info['personalParams']))
		{
			$currentPid = -1;
			$pdGroups = Utils::getPersonaldataGroups();
			foreach ($info['personalParams'] as $pd)
			{
				$nm = $pd['title'];
				if ($pd['parent_id'] <> $currentPid)
				{
					$currentPid = $pd['parent_id'];
					echo '<h3>' . $pdGroups[$currentPid] . '</h3>';
				}
				$nmId = 'param_' . $pd['pid'];
				echo '
					<h4>' . Yii::t('params', $nm) . ':
						<div id="' . $nmId . '_static">
				';
				$pName = 'param_' . $pd['pid'];
				switch ($pd['tp'])
				{
					case "password":
						echo '<span id="' . $nmId . '_static_value">***********</span> <a id="' . $nmId . '" href="#" onclick="return editPassword(this);">' . Yii::t('common', 'Edit') . '</a>';
					break;
					case "text":
						echo '<span id="' . $nmId . '_static_value">' . $pd['text_value'] . '</span> <a id="' . $nmId . '" href="#" onclick="return editText(this);">' . Yii::t('common', 'Edit') . '</a>';
					break;
					case "textarea":
						echo '<span id="' . $nmId . '_static_value">' . $pd['textarea_value'] . '</span> <a id="' . $nmId . '" href="#" onclick="return editText(this);">' . Yii::t('common', 'Edit') . '</a>';
					break;
					case "checkbox":
						$chkStr = $pd['int_value'];
						if ($chkStr)
							$chkStr = Yii::t('common', 'Yes');
						else
							$chkStr = Yii::t('common', 'No');
						echo '<span id="' . $nmId . '_static_value">' . $chkStr . '</span> <a id="' . $nmId . '" href="#" onclick="return editText(this);">' . Yii::t('common', 'Edit') . '</a>';
					break;
				}
				echo'
						</div>
						<div id="' . $nmId . '_edit" style="display: none">
							';
				switch ($pd['tp'])
				{
					case "text":
						echo CHtml::textField($pName, $pd['text_value'], array('id' => $nmId . '_edit_value', 'onblur' => 'return showText(this);', 'onkeyup' => 'return keyUpText(this, event.keyCode);'));
					break;
					case "textarea":
						echo CHtml::textArea($pName, $pd['textarea_value'], array('id' => $nmId . '_edit_value', 'onblur' => 'return keyUpText(this, 27);', 'onkeyup' => 'return keyUpText(this, event.keyCode);'));
					break;
					case "checkbox":
						echo CHtml::checkBox($pName, $pd['int_value'], array('id' => $nmId . '_edit_value', 'onblur' => 'return keyUpText(this, 27);', 'onkeyup' => 'return keyUpText(this, event.keyCode);'));
					break;
					case "password":
						echo Yii::t('params', 'New ' . $nm) . '<br />
						' . CHtml::passwordField($pName, '', array('id' => $nmId . '_edit_value', 'onkeyup' => 'return keyUpPassword(this, event.keyCode);')) . '<br />
						' . Yii::t('params', 'Old ' . $nm) . '<br />
						' . CHtml::passwordField('old_' . $pName, '', array('id' => $nmId . '_edit_value2', 'onkeyup' => 'return keyUpPassword(this, event.keyCode);')) . '<br />';
					break;
				}
				echo '
						</div>
						<div id="' . $nmId . '_wait" style="display: none">
							' . Yii::t('common', 'Please wait...') . '
						</div>
					</h4>
				';
			}
		}
	}
}
else
	echo $ajaxResult;