<?php
$results = 0;
if (!empty($pstContent)) {
	$results++;
?>
<div class="span12 no-horizontal-margin inside-movie my-catalog">
    <h1><?php echo Yii::t('users', 'Goods'); ?></h1>
	<div class="pad-content">
		<?php echo $pstContent; ?>
	</div>
</div>
<?php
}

if (!empty($obj)) {
	$results++;
?>
<div class="span12 no-horizontal-margin inside-movie my-catalog">
    <h1>Library</h1>
	<div class="pad-content">
		<ul>
			<?php echo CFiletypes::ParsePrint($obj, 'TL1'); ?>
		</ul>
	</div>
</div>
<?php
}

if (!empty($unt)) {
	$results++;
?>
<div class="span12 no-horizontal-margin inside-movie my-catalog">
    <h1>Untyped</h1>
	<div class="pad-content">
		<ul>
			<?php echo CFiletypes::ParsePrint($unt, 'UTL1'); ?>
		</ul>
	</div>
</div>
<?php
}

if (empty($results))
{
?>
<div class="span12 no-horizontal-margin inside-movie my-catalog">
    <h1><?php echo Yii::t('common', 'Search results'); ?></h1>
	<div class="pad-content">
	<?php echo Yii::t('common', 'Nothing was found'); ?>
	</div>
</div>
<?php
}