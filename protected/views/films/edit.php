<pre>
<?php

print_r($film);

?>
</pre>

<div id="dialog-confirm">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>These items will be permanently deleted and cannot be recovered. Are you sure?</p>
</div>

<form name="addForm" id="addForm" onsubmit="return confirmForm();">
	<fieldset>
		<label for="name">Name</label>
		<input type="text" name="name" id="name" class="text ui-widget-content ui-corner-all" />
		<label for="email">Email</label>
		<input type="text" name="email" id="email" value="" class="text ui-widget-content ui-corner-all" />
		<label for="password">Password</label>
		<input type="password" name="password" id="password" value="" class="text ui-widget-content ui-corner-all" />
		<input type="submit" name="Send" />
	</fieldset>
</form>

<script type="text/javascript">
	$(function() {
		$( "#dialog:ui-dialog" ).dialog( "destroy" );

		$( "#dialog-confirm" ).dialog({
			resizable: false,
			height:200,
			width:300,
			modal: true,
			autoOpen: false,
			title: "<?php echo Yii::t('common', 'Сonfirmation'); ?>",
			buttons: {
				"Delete all items": function() {
					modalResult = 1;
					document.getElementById("addForm").submit();
				},
				Cancel: function() {
					modalResult = null;
					$(this).dialog("close");
				}
			}
		});
	});

	modalResult	= null;
	function confirmForm()
	{
		if (modalResult == 1) return true;

		$("#dialog-confirm").dialog("open");

		return false;
	}
</script>