
jQuery(document).ready(function() {
    $('.ItemList_v_1 li').click(function(e){
        var ul =$(this).parent();
        $('.selected',ul).removeClass('selected');
        $(this).addClass('selected');
    });
});