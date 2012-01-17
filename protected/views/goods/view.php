<?php if (!empty($goods)): ?>
    <?php foreach ($goods as $itype): ?>
        <div class="good_section">
            <div class="good_title"><?= $itype['name']; ?></div>         
            <ul class="listview_a">
                <? CFiletypes::ParsePrint($itype['items'], $itype['itemtype']); ?>
            </ul>
            <div class="clearfix"></div>
            <div class="action_bar">
                <a href="#">Add</a>
            </div>
        </div>
    <?php endforeach; ?>
    <script>
        $('.listview_a li').click(function(e){
            if ($(this).hasClass('selected')){
                $(this).removeClass('selected');
            } else {
                $(this).addClass('selected');
            }
        }  
    );
    </script>
<?php endif; ?>