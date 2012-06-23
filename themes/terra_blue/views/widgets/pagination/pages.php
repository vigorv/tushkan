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
?>
<div class="span12 no-horizontal-margin some-space"></div>
						<div class="pagination pagination-right" style="clear:both">
							<ul>
								<li><a href="#">&larr;</a></li>
<?php
	for($i = 0; $i < $pages; $i++)
	{
		$url = $this->preparePageUrl($i);
		if ($i == $this->params['page']) $a = ' class="active"'; else $a = '';
		echo '<li' . $a . '><a href="' . $url . '">' . ($i + 1). '</a></li>';
	}
?>
								<li><a href="#">&rarr;</a></li>
							</ul>
						</div>
<?php
}

