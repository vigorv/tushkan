<?php
/*
$productLimit = 5;

if (!empty($pst)) {
    $curId = 0;
    $infos = array();
   foreach ($pst as $p) {
    if ($p['pvid'] <> $curId) {
        $curId = $p['pvid'];
        if ($productLimit-- < 0)
            break;
    }
    $infos[$curId]['id'] = $p['id'];
    $infos[$curId]['title'] = $p['ptitle'];
    $infos[$curId][$p['ppvid']] = $p['value'];
    if (!empty($p['prtid'])) {
        $infos[$curId]['partner'] = '<i><a href="/products/partner/' . $p['prtid'] . '">' . $p['prttitle'] . '</a></i>';
    }
    else
        $infos[$curId]['partner'] = '';
    }

    foreach ($infos as $info) {
        if (!empty($info['10']))
            $poster = $info['10'];
        else
            $poster = Yii::app()->params['tushkan']['postersURL'] . '/noposter.jpg';
        $prms = array($info['12'], $info['13'], $info['14']);
        echo '
//htmlentities($info['title'], ENT_QUOTES, 'UTF-8');
        ';
    }
//class="span2 margin-left-only"
}*/
?>

<script language="javascript">
    function m_goods_carousel_link(e) {
        $.address.value($(e).attr('href'));
        return false;
    }

</script>

<ul id="m_goods_carousel" class="jcarousel-skin-tango">
    <? foreach ($pst as $item): ?><? if (empty($item['poster']))
    $item['poster'] = Yii::app()->params['tushkan']['postersURL'] . '/noposter.jpg';   ?>
    <li>
        <img class="small-poster" src="<?=$item['poster'];?>" alt="<?=$item['ptitle'];?>"
             title="<?=$item['ptitle'];?>">
        <a class="top-film" href="/products/view/<?=$item['id'];?>"
           onClick="return m_goods_carousel_link(this);"><?=$item['ptitle'];?></a>
    </li><? endforeach; ?>
</ul>

<div class="pages">
    <a class="item-active" href="#"></a>
    <a class="item" href="#" ></a>

</div>

<script language="javascript">

    function m_goods_carousel_itemLoadCallback(carousel, state) {
        if (state == 'next') {
            offset = carousel.last;
           // console.log(offset);
            jQuery.get('/ajax/productsTop?offset=' + offset, function (data) {
               // $(data).each(function () {
                 //  console.log($(this));
                //});
                $(data).each(function () {
                    offset++;
                    carousel.add(offset, this);

                });
                carousel.size(offset);
            });

            return;
        }
    }

    var goodsCarousel = jQuery('#m_goods #m_goods_carousel').jcarousel({
        itemLoadCallback:m_goods_carousel_itemLoadCallback,
        itemFirstOutCallback:m_good_carousel_turn,
       // itemLastOutCallback:m_good_carousel_prev,
        scroll:<?=Yii::app()->params['product_top_count'];?>
    });


    function m_good_carousel_turn(carousel, elem ,index,state) {
        //console.log(index);
           if (state == "next"){
            var current = $(".pages a.item-active");
            var inext = current.next('a');
            if (inext.length != 0) {
                inext.removeClass("item").addClass("item-active");
                current.removeClass("item-active").addClass("item");
            } else{
                $('.pages').append('<a class="item-active" href="#"></a>');
                current.removeClass("item-active").addClass("item");
            }
           } else if (state == "prev"){
               var current = $(".pages a.item-active");
               var previos = current.prev('a');
               if (previos) {
                   previos.removeClass("item").addClass("item-active");
                   current.removeClass("item-active").addClass("item");
               }
           }


    }



</script>