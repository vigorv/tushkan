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
		$nm = 'name';
		echo '
			<h4>Имя:
				<div id="' . $nm . '_static">
					<span id="' . $nm . '_static_value">' . $info[$nm] . '</span> <a id="' . $nm . '" href="#" onclick="return editText(this);">' . Yii::t('common', 'Edit') . '</a>
				</div>
				<div id="' . $nm . '_edit" style="display: none">
					' . CHtml::textField($nm, $info['name'], array('id' => $nm . '_edit_value', 'onblur' => 'return keyUpText(this, 27);', 'onkeyup' => 'return keyUpText(this, event.keyCode);')) . '
				</div>
				<div id="' . $nm . '_wait" style="display: none">
					' . Yii::t('common', 'Please wait...') . '
				</div>
			</h4>
		';
		$nm = 'email';
		echo '
			<h4>Email:
				<div id="' . $nm . '_static">
					<span id="' . $nm . '_static_value">' . $info[$nm] . '</span> <a id="' . $nm . '" href="#" onclick="return editText(this);">' . Yii::t('common', 'Edit') . '</a>
				</div>
				<div id="' . $nm . '_edit" style="display: none">
					' . CHtml::textField($nm, $info['name'], array('id' => $nm . '_edit_value', 'onblur' => 'return keyUpText(this, 27);', 'onkeyup' => 'return keyUpText(this, event.keyCode);')) . '
				</div>
				<div id="' . $nm . '_wait" style="display: none">
					' . Yii::t('common', 'Please wait...') . '
				</div>
			</h4>
		';
		$nm = 'pwd';
		echo '
			<h4>pwd:
				<div id="' . $nm . '_static">
					<span id="' . $nm . '_static_value">******</span> <a id="' . $nm . '" href="#" onclick="return editPassword(this);">' . Yii::t('common', 'Edit') . '</a>
				</div>
				<div id="' . $nm . '_edit" style="display: none">
					' . Yii::t('users', 'New password') . '<br />
					' . CHtml::passwordField($nm, '', array('id' => $nm . '_edit_value', 'onkeyup' => 'return keyUpPassword(this, event.keyCode);')) . '<br />
					' . Yii::t('users', 'Old password') . '<br />
					' . CHtml::passwordField($nm . '2', '', array('id' => $nm . '_edit_value2', 'onkeyup' => 'return keyUpPassword(this, event.keyCode);')) . '<br />
				</div>
				<div id="' . $nm . '_wait" style="display: none">
					' . Yii::t('common', 'Please wait...') . '
				</div>
			</h4>
		';
	}
}
else
	echo $ajaxResult;