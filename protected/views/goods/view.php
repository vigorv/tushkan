<div id="result" style="color:black"></div>
<?php if (!empty($goods)): ?>
    <?php foreach ($goods as $itype): ?>
        <div class="good_section">
            <div class="good_title"><?= $itype['name']; ?></div>         
            <ul class="listview_a">
                <? CFiletypes::ParsePrint($itype['items'], $itype['itemtype']); ?>
            </ul>
            <div class="clearfix"></div>
            <div class="action_bar">
                <a href="#" id="good_add">Add</a>
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
        
        $('#good_add').click(function(e){
            var postText = "";
            $('.listview_a li.selected').each(function(){
                postText += $( this ).attr( "id" ) +',';
            });
            $.ajax( { 
            url: "/goods/add",
            type: "POST",
            data: "postText=" + postText,
            success: function( response ) {
                // request has finished at this point.
                $("#result").html(response);
            }
        } );
        });
    </script>
<?php endif; ?>