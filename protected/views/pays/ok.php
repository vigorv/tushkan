<?php
if (!empty($contentUrl))
	echo '
		<script type="text/javascript">
		$("#content").load("' . $contentUrl . '");
		</script>
	';
