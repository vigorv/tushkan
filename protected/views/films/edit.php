<pre>
<?php

print_r($film);

?>
</pre>

<script type="text/javascript">
alert(1);

	curForm = null;
	function confirm(form)
	{
		if (curForm != null) return true;

		curForm = form;
		$( "#dialog:ui-dialog" ).dialog( "destroy" );

		$( "#dialog-confirm" ).dialog({
			resizable: false,
			height:140,
			modal: true,
			buttons: {
				"Delete all items": function() {
					$( this ).dialog( "close" );
				},
				Cancel: function() {
					$( this ).dialog( "close" );
					curForm = null;
				}
			}
		});
		return false;
	};

	$(function() {
		$("input:submit").button();
	}

</script>

<form onsubmit="alert(0); return confirm(this);">
	<fieldset>
		<label for="name">Name</label>
		<input type="text" name="name" id="name" class="text ui-widget-content ui-corner-all" />
		<label for="email">Email</label>
		<input type="text" name="email" id="email" value="" class="text ui-widget-content ui-corner-all" />
		<label for="password">Password</label>
		<input type="password" name="password" id="password" value="" class="text ui-widget-content ui-corner-all" />
		<input type="submit" name="submit" />
	</fieldset>
</form>

<div id="dialog-confirm" title="Empty the recycle bin?" style="display:none">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>These items will be permanently deleted and cannot be recovered. Are you sure?</p>
</div>
