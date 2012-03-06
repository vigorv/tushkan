<?php
$space_busy = (int) $userInfo['size_limit'] - (int) $userInfo['free_limit'];
$space_percent = $space_busy * 100 / (int) $userInfo['free_limit'];
?>
<ul id="mail">
    <li class="dropdown">
	<a class="dropdown-toggle" data-toggle="dropdown" href="#"><?= Yii::t('common', 'Email'); ?>: <?= @$userInfo['email']; ?></a>
	<ul class="dropdown-menu">
	    <li><a href="/register/profile" >Настройки</a></li>
	    <li><a href="/register/logout">Выйти</a></li>
	    <?php //TODO: LOGOUT should be POST ?>
	</ul>
    </li>
</ul>
<ul id="balance" >
    <li  class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Balance :  <?= @$userInfo['balance']; ?></a>
	<ul class="dropdown-menu">
	    <li><a href="/pays/do/1" >Пополнить</a></li>
	    <li><a  href="/pays" >История платежей</a></li>   
	</ul></li>
</ul>
<ul id="space">
    <li>
	<a href="/">
	    <div  class="progress active striped animated ">
		<p>Space: <?= @$space_busy; ?> of <?= @$userInfo['size_limit'] ?></p>
		<div class="bar" style="width: <?= @$space_percent; ?>%"></div>
	    </div>
	</a>
	<ul class="dropdown-menu">
	    <li>lorem ipsum</li>
	    <li>lorem ipsum</li>
	</ul></li>
</ul>
<ul id="goods" >
    <li  class="dropdown">
	<a  class="dropdown-toggle" data-toggle="dropdown" href="#">Goods</a>	
	<ul class="dropdown-menu">
	    <li><a href="/universe/goods">Al<li></a></li>
	    <?php foreach ($partners as $partner): ?>
    	    <li>  <a href="/universe/goods/<?= $partner['id']; ?>"><?= $partner['title']; ?></a></li>
	    <?php endforeach; ?>
	</ul></li>
</ul>
<ul id="uploads">
    <li  class="dropdown">
	<a  class="dropdown-toggle" data-toggle="dropdown" href="#">
	    <div  id="progressBar" class="progress striped active animated">
		<div class="bar" style="width: 100%">
		    <p>Upload</p></div>
	    </div>
	</a>
	<ul  id="progressList" class="dropdown-menu">

	</ul>
    </li>
</ul>
<ul id="search">
    <input id="i_search" type="text" placeholder="Global search..." />
    <input type="button" value="Search" onClick="SearchEverywhere($(i_search).val());"/>   
</ul>



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