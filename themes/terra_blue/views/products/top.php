<div id="m_goods_carousel" class="goods_top_carousel">
    <ul>
        <? foreach ($pst as $item): ?><? if (empty($item['poster']))
        $item['poster'] = Yii::app()->params['tushkan']['postersURL'] . '/noposter.jpg';   ?>
        <li>
            <img class="small-poster" src="<?=$item['poster'];?>" alt="<?=$item['ptitle'];?>"
                 title="<?=$item['ptitle'];?>">
            <a class="top-film" href="/products/view/<?=$item['id'];?>"
               onClick="return m_goods_carousel_link(this);"><?=$item['ptitle'];?></a>
        </li><? endforeach; ?>
    </ul>
</div>

<a class="jcarousel-prev-horizontal disabled" href="#"></a>
<a class="jcarousel-next-horizontal disabled" href="#"></a>

<div id="m_goods_carousel_pages">

</div>



<script language="javascript">
    var m_goods_carousel = $('#m_goods_carousel').jcarousel();

    var m_goods_pages = $('#m_goods_carousel_pages')
            .delegate('a', 'active.jcarouselcontrol', function () {
                $("#m_goods_carousel_pages a").removeClass('active');
                $(this).addClass('active');
            })
            .delegate('a', 'inactive.jcarouselcontrol', function () {

            })
            .jcarouselPagination({
                carousel:m_goods_carousel,
                'perPage':<?=Yii::app()->params['product_top_count'];?>,
                'item':function (page, carouselItems) {
                    return '<a class="item" href="#' + page + '"></a>';
                }
            });



    $('.jcarousel-prev-horizontal')
            .bind('active.jcarouselcontrol', function() {
                 $(this).removeClass('disabled');
            })
            .bind('inactive.jcarouselcontrol', function() {
                 $(this).addClass('disabled');
            })
            .jcarouselControl({target: '-=<?=Yii::app()->params['product_top_count'];?>'});

    $('.jcarousel-next-horizontal')
            .bind('active.jcarouselcontrol', function() {
                $(this).removeClass('disabled');
            })
            .bind('inactive.jcarouselcontrol', function() {
                $(this).addClass('disabled');
            })
            .jcarouselControl({target: '+=<?=Yii::app()->params['product_top_count'];?>'});




</script>