<?php
/* ОРИГИНАЛЬНАЯ ВЕРСТКА
<div class="span12 no-horizontal-margin some-space"></div>
						<div class="pagination pagination-right">
							<ul>
								<li><a href="#">&larr;</a></li>
								<li><a href="#">6</a></li>
								<li class="active"><a href="#">7</a></li>
								<li><a href="#">8</a></li>
								<li class="disabled"><a href="#">...</a></li>
								<li><a href="#">35</a></li>
								<li><a href="#">&rarr;</a></li>
							</ul>
						</div>
*/
$pages = $this->getPageCount();
if ($pages > 1)
{
	$outPages = $this->getPagePairs();
?>
<div class="span12 no-horizontal-margin some-space"></div>
						<div class="pagination pagination-right" style="clear:both">
							<ul>
<?php
	if (!empty($this->params['loadId']))
	{
?>
<script type="text/javascript">
	function ajaxPage<?php echo $this->params['loadId']; ?>(url)
	{
		$('#<?php echo $this->params['loadId']; ?>').load(url);
		return false;
	}
</script>
<?php
	}
	if (!empty($outPages))
		foreach($outPages as $i => $p)
		{
			if (!empty($p['is_current'])) $a = ' class="active"'; else $a = '';
			$href= '';
			if (!empty($p['url']))
				$href = 'href="' . $p['url'] . '"';
			if (!empty($this->params['loadId']) && !empty($href))
			{
				$href = 'href="" onclick="return ajaxPage' . $this->params['loadId'] . '(\'' . $p['url'] . '\');"';
			}
			echo '<li' . $a . '><a ' . $href . '>' . $p['title']. '</a></li>';
		}
?>
							</ul>
						</div>
<?php
}

