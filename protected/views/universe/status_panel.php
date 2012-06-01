<?php
$space_busy = (int) $userInfo['size_limit'] - (int) $userInfo['free_limit'];
if (empty($userInfo['free_limit'])) $userInfo['free_limit'] = 1;
$space_percent = $space_busy * 100 / (int) $userInfo['free_limit'];

isset($userInfo['balance']) ? $balance = sprintf("%01.2f", $userInfo['balance']) : $balance=0;
$balance.=' руб';
?>
<ul>
	<li>
		<a class="btn btn-small l_ajax " href="/"> <i class="icon-home"></i><?= Yii::t('users', 'Main'); ?></a>
	</li>
    <li id="mail" class="dropdown">
		<a class="dropdown-toggle btn" data-toggle="dropdown" href="#">
			<i class="icon-user"></i> <?= Yii::t('common', 'Email'); ?>: <?= @$userInfo['email']; ?></a>
		<ul class="dropdown-menu">
			<li><a href="/register/profile" class="l_ajax"><?= Yii::t('users', 'Settings'); ?></a></li>
			<li><a href="/register/logout" ><?= Yii::t('users', 'Logout'); ?></a></li>
			<?php //TODO: LOGOUT should be POST ?>
		</ul>
    </li>

    <li  id="balance"  class="dropdown">
		<a class="dropdown-toggle btn btn-small" data-toggle="dropdown" href="#" >
			<i class="icon-plane"></i> <span id="balance_id"><?= Yii::t('users', 'Account balance'); ?>: <?= $balance?></span></a>
		<ul class="dropdown-menu">
			<li><a class="l_ajax" href="/pays/do/1" ><?= Yii::t('users', 'Fill up balance'); ?></a></li>
			<li><a  class="l_ajax"href="/pays" ><?= Yii::t('users', 'Payments history'); ?></a></li>
			<li><a  class="l_ajax"href="/orders" ><?= Yii::t('orders', 'Orders'); ?></a></li>
		</ul></li>
<script type="text/javascript">
<!--
	function updateActualBalance()
	{
		$.post('/pays/actualbalance', function(data){
			if (data)
			{
				$("#balance_id").html(data);
			}
		})
	}
-->
</script>
	<li>
		<a class="btn btn-small" href="#"  onClick="window.open('/universe/upload?mini','Uploader','width=400,height=400'); return false;">
			<i class="icon-upload"></i><?=Yii::t('users','Uploads');?>
		</a>
	</li>
    <li id="userspace">
		<a  class="btn btn-small" rel="tooltip" title="<?= Yii::t('users', 'Userspace'); ?>">
			<div  class="progress-info  striped ">
				<i class="icon-inbox"></i> <?= @$space_busy; ?>MB  : <?= @$userInfo['size_limit'] ?>MB
				<div class="bar" style="width: <?= @$space_percent; ?>%"></div>
			</div>
		</a>
		<ul class="dropdown-menu">
			<li>lorem ipsum</li>
		</ul></li>
    <li  id="goods" class="dropdown">
		<a  class="dropdown-toggle btn btn-small" data-toggle="dropdown" href="#">
			<i class=' icon-shopping-cart'></i> <?= Yii::t('users', 'Goods'); ?></a>
		<ul class="dropdown-menu">
			<li><a class="l_ajax" href="/products"><?= Yii::t('common', 'All'); ?></a></li>
			<?php foreach ($partners as $partner): ?>
				<li>  <a class="l_ajax" href="/products/partner/<?= $partner['id']; ?>"><?= $partner['title']; ?></a></li>
			<?php endforeach; ?>
		</ul></li>


	<li id="search">
		<input id="i_search" type="text" placeholder="<?=Yii::t('users','Global search');?>..." />
		<a href="#search" onClick="SearchEverywhere($(i_search).val());">
			<i class="icon-search"></i>
		</a>
	</li>
</ul>
<div class="clearfix"></div>
<script langauge="javascript">

	$('.dropdown-toggle').dropdown();

	var cont= $("#content");
	$('#i_search').keypress(function(e){
		if(e.which == 13){
			$.address.value('/universe/search?text='+this.value);
		}
	});

	function SearchEverywhere(text){
		cont.load('/universe/search?text='+text);
	}

					$('#m_panel a.l_ajax').click(function() {
						lnk= $(this).attr('href');
						$.address.value(lnk);
						return false;
					});

</script>