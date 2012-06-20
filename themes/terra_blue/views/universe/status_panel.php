<?php
$space_busy = (int) $userInfo['size_limit'] - (int) $userInfo['free_limit'];
if (empty($userInfo['free_limit'])) $userInfo['free_limit'] = 1;
$space_percent = $space_busy * 100 / (int) $userInfo['free_limit'];

isset($userInfo['balance']) ? $balance = sprintf("%01.2f", $userInfo['balance']) : $balance=0;
$balance.=' ' . Yii::t('pays', _CURRENCY_);
?>
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
    <div class="modal hide" id="confirmLogout" style="position: absolute; top: 70%">
	    <div class="modal-header">
		    <button type="button" class="close" data-dismiss="modal">×</button>
		    <h3><?php echo Yii::t('common', 'Сonfirmation'); ?></h3>
	    </div>
	    <div class="modal-body">
	    	<center><?php echo Yii::t('common', 'Are you sure?'); ?></center>
		    <a href="/register/logout" class="btn btn-primary"><?php echo Yii::t('users', 'Logout'); ?></a>
		    <a href="" class="btn" data-dismiss="modal"><?php echo Yii::t('common', 'Cancel'); ?></a>
	    </div>
    </div>
    			<div class="navbar-inner navbar-top">
				<ul class="nav">
					<li> <!-- class="active" -->
						<a href="/"><?php echo Yii::t('users', 'Main'); ?></a>
					</li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo Yii::t('users', 'Account balance'); ?><b class="caret caret-top-menu"></b></a>
						<div class="ann">(<span id="balance_id"><?php echo $balance?></span>)</div>
						<ul class="dropdown-menu pull-right">
			<li><a href="/pays/do/1" ><?= Yii::t('users', 'Fill up balance'); ?></a></li>
			<li><a href="/pays" ><?= Yii::t('users', 'Payments history'); ?></a></li>
			<li><a href="/orders" ><?= Yii::t('orders', 'Orders'); ?></a></li>
						</ul>
					</li>
					<li>
						<a noref><?php echo Yii::t('users', 'Userspace'); ?></a>
						<div class="ann">(<?php echo @$space_busy; ?>MB : <?php echo @$userInfo['size_limit'] ?>MB)</div>
					</li>
					<li>
						<a href="#" onClick="window.open('/universe/uploadui','Uploader','width=700,height=500'); return false;"><?php echo Yii::t('users','Uploads');?></a>
					</li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo Yii::t('users', 'Goods'); ?><b class="caret caret-top-menu"></b></a>
						<ul class="dropdown-menu pull-right">
							<li><a class="l_ajax" href="/products"><?php echo Yii::t('common', 'All'); ?></a></li>
						<?php foreach ($partners as $partner): ?>
							<li>  <a href="/products/partner/<?php echo $partner['id']; ?>"><?php echo $partner['title']; ?></a></li>
						<?php endforeach; ?>
						</ul>
					</li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo Yii::t('common', 'Profile'); ?><b class="caret caret-top-menu"></b></a>
						<div class="ann">(<?php echo @$userInfo['email']; ?>)</div>
						<ul class="dropdown-menu pull-right">
							<li><a href="/register/profile" class="l_ajax"><?= Yii::t('users', 'Settings'); ?></a></li>
							<li><a href="" onclick="$('#confirmLogout').modal();$('#confirmLogout').click();" ><?= Yii::t('users', 'Logout'); ?></a></li>
						</ul>
					</li>
				</ul>
			</div>
