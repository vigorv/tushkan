<ul id="mail">
    <li class="dropdown">
	<a class="dropdown-toggle" data-toggle="dropdown" href="#menu_mail"><?= Yii::t('common', 'Email'); ?>: <?= Yii::app()->user->getState('dmUserEmail'); ?></a>
	<ul class="dropdown-menu">
	    <li><a href="/register/profile" class="ajaxh">Настройки</a></li>
	    <li><a href="/register/logout">Выйти</a></li>
	    <?php //TODO: LOGOUT should be POST ?>
	</ul>
    </li>
</ul>
<ul id="balance" >
    <li  class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#menu_mail">Balance :  <?= @$balance; ?></a>
	<ul class="dropdown-menu">
	    <li><a href="/pays/do/1" class="ajaxh">Пополнить</a></li>
	    <li><a  href="/pays" class="ajaxh">История платежей</a></li>   
	</ul></li>
</ul>
<ul id="space">
    <li  class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#menu_mail">Space: <?= @$free; ?> of <?= @$total; ?></a>
	<ul class="dropdown-menu">
	    <li>lorem ipsum</li>
	    <li>lorem ipsum</li>
	</ul></li>
</ul>
<ul id="goods" >
    <li  class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#menu_mail">Goods</a>
	<ul class="dropdown-menu">
	    <li>lorem ipsum</li>
	    <li>lorem ipsum</li>
	</ul></li>
</ul>
<ul id="search">
    <input id="i_search" type="text" placeholder="Global search..." />
    <input type="button" value="Search" onClick="SearchEverywhere($(i_search).val());"/>   
</ul>

<script langauge="javascript">
    
    
    $('.dropdown-toggle').dropdown();
    $('a.ajaxh').click(function(){
	cont.load(this.href);
	return false;	 
	
    });
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