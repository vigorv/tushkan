<?php
$space_busy = (int) $userInfo['size_limit'] - (int) $userInfo['free_limit'];
$space_percent = $space_busy * 100 / (int) $userInfo['free_limit'];
?>
<ul>
	<li>
		<a class="btn  " href="/"><i class="icon-home icon-white"></i><?= Yii::t('common', 'Main'); ?></a>
	</li>
    <li id="mail" class="dropdown">
		<a class="dropdown-toggle btn" data-toggle="dropdown" href="#"><?= Yii::t('common', 'Email'); ?>: <?= @$userInfo['email']; ?></a>
		<ul class="dropdown-menu">
			<li><a href="/register/profile" ><?= Yii::t('users', 'Settings'); ?></a></li>
			<li><a href="/register/logout"><?= Yii::t('users', 'Logout'); ?></a></li>
			<?php //TODO: LOGOUT should be POST ?>
		</ul>
    </li>

    <li  id="balance"  class="dropdown"><a class="dropdown-toggle btn" data-toggle="dropdown" href="#"><?= Yii::t('users', 'Account balance'); ?> :  <?= @$userInfo['balance']; ?></a>
		<ul class="dropdown-menu">
			<li><a href="/pays/do/1" ><?= Yii::t('users', 'Fill up balance'); ?></a></li>
			<li><a  href="/pays" ><?= Yii::t('users', 'Payments history'); ?></a></li>   
		</ul></li>

    <li>
		<a  class="btn" href="#" rel="tooltip" title="<?= Yii::t('users', 'Userspace'); ?>">
			<div  class="progress-info active striped ">
				<?= @$space_busy; ?>MB  : <?= @$userInfo['size_limit'] ?>MB
				<div class="bar" style="width: <?= @$space_percent; ?>%"></div>
			</div>
		</a>
		<ul class="dropdown-menu">
			<li>lorem ipsum</li>
			<li>lorem ipsum</li>
		</ul></li>
    <li  id="goods" class="dropdown">
		<a  class="dropdown-toggle btn" data-toggle="dropdown" href="#"><?= Yii::t('users', 'Goods'); ?></a>	
		<ul class="dropdown-menu">
			<li><a href="/products/index"><?= Yii::t('common', 'All'); ?><li></a></li>
			<?php foreach ($partners as $partner): ?>
				<li>  <a href="/products/partner/<?= $partner['id']; ?>"><?= $partner['title']; ?></a></li>
			<?php endforeach; ?>
		</ul></li>


	<li id="search">
		<input id="i_search" type="text" placeholder="Global search..." />
		<input type="button" value="Search" onClick="SearchEverywhere($(i_search).val());"/>   
	</li>
</ul>
<div class="clearfix"></div>
<script langauge="javascript">
        
	$('.dropdown-toggle').dropdown();
         
	var cont= $("#content");
	$('#i_search').keypress(function(e){
		if(e.which == 13){
			cont.load('/universe/search?text='+this.value);
		}
	});
    
	function SearchEverywhere(text){
		cont.load('/universe/search?text='+text);
	}
</script>