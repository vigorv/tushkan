<div class="good_title">   
    <div class="P_section_1 fleft">My Files</div>
    <div class="P_section_2_0 fleft">
        <ul class="options fleft">
            <li><a href ="#"><img/>New</a></li>
            <li><a href="/files/add"><img />Add</a></li>
        </ul>
        <ul class="options fright">
            <li><a id="item_del" href="#"><img/>Delete</a></li>
        </ul>
    </div>
    <div class="clearfix"></div>
</div>

<?php
for ($i = 1; $i < 1000; $i++) {
    $files[] = array('name' => 'test');
}
?>
<div id="result"></div>
<?php if (!empty($files)): ?>
    <div id="FileList">
        <div id="folders">
            <?php
            echo $this->widget('CTreeView', array(
                'id' => 'folder_tree',
                'url' => array('/files/AjaxFoldersList'))
            );
            ?>
        </div>
        <div id="files" style="fleft">
            <ul id="file_list" tabindex="1" >
                <?
                CFiletypes::ParsePrint($files, 'FL1');
                ?>
            </ul>
        </div>
    </div>
    <div class="clearfix"></div>
<?php endif; ?>

<script type="text/javascript">
    
    $("#file_list li").click(function(e){
        if ($(this).hasClass('selected')){
            $(this).removeClass('selected');
        } else {
            $(this).addClass('selected');
        }
    });
    $("#file_list").keydown(function(e){
        var elem=$(".elem",this);
        new_e=null;
        switch(e.keyCode){            
            case 39: //right
                e.preventDefault()
                var new_e = elem.next('li');
                break;
            case 37:// left
                e.preventDefault()
                var new_e = elem.prev('li');
                break;
            case 38://up
                e.preventDefault()
                var line_count=parseInt($(this).width() / 75)-1;
                var new_e = elem.prevAll("li:eq("+line_count+")");
                break;
            case 40://down         
                e.preventDefault()
                var line_count=parseInt($(this).width() / 75)-1;
                var new_e = elem.nextAll("li:eq("+line_count+")");    
                break;
            case 35://end
                e.preventDefault()
                var new_e =  $("#file_list li").last();
                break;
            case 36://home
                e.preventDefault()
                var new_e =  $("#file_list li").first();
                break;
            case 32://space
                e.preventDefault();
                if ($(elem).hasClass('selected')){
                    $(elem).removeClass('selected');
                } else {
                    $(elem).addClass('selected');
                }
                break;
            default:
        }
        if  (new_e && new_e.length){            
            if(!($(new_e).hasClass('elem'))){
                elem.removeClass('elem');
                new_e.addClass('elem');
                //$(this).scrollTop(new_e.position().top);
            }
        }
    }
);
    $("#file_list li:first ").addClass('elem');
    
    $('#item_del').click(function(e){
        var postText = "";
        $('#file_list li.selected').each(function(){
            //postText += $( this ).attr( "name" ) +',';
            postText += $( this ).text() +',';
        });
        $.ajax( { 
            url: "/files/remove",
            type: "POST",
            data: "postText=" + postText,
            success: function( response ) {
                // request has finished at this point.
                $("#result").html(response);
            }
        } );
    });
    
    
    
</script>  
<?php
/*
  <script type="text/javascript">
  $(function() {
  var items = $("#file_list li"),
  title = $("title").text() || document.title;

  //make images draggable
  items.draggable({
  //create draggable helper
  helper: function() {
  return $("<div>").attr("id", "helper").html("<span>" + title + "</span><img id='thumb' src='" + $(this).attr("src") + "'>").appendTo("body");
  },
  cursor: "pointer",
  cursorAt: { left: -10, top: 20 },
  zIndex: 99999,
  //show overlay and targets
  start: function() {
  $("<div>").attr("id", "overlay").css("opacity", 0.7).appendTo("body");
  $("#tip").remove();
  $(this).unbind("mouseenter");
  $("#targets").css("left", ($("body").width() / 2) - $("#targets").width() / 2).slideDown();
  },
  //remove targets and overlay
  stop: function() {
  $("#targets").slideUp();
  $(".share", "#targets").remove();
  $("#overlay").remove();
  $(this).bind("mouseenter", createTip);
  }
  });

  //make targets droppable
  $("#targets li").droppable({
  tolerance: "pointer",
  //show info when over target
  over: function() {
  $(".share", "#targets").remove();
  $("<span>").addClass("share").text("Share on " + $(this).attr("id")).addClass("active").appendTo($(this)).fadeIn();
  },
  drop: function() {
  var id = $(this).attr("id"),
  currentUrl = window.location.href,
  baseUrl = $(this).find("a").attr("href");

  if (id.indexOf("twitter") != -1) {
  window.location.href = baseUrl + "/home?status=" + title + ": " + currentUrl;
  } else if (id.indexOf("delicious") != -1) {
  window.location.href = baseUrl + "/save?url=" + currentUrl + "&title=" + title;
  } else if (id.indexOf("facebook") != -1) {
  window.location.href = baseUrl + "/sharer.php?u=" + currentUrl + "&t=" + title;
  }
  }
  });

  var createTip = function(e) {
  //create tool tip if it doesn't exist
  ($("#tip").length === 0) ? $("<div>").html("<span>Drag this image to share the page<\/span><span class='arrow'><\/span>").attr("id", "tip").css({ left:e.pageX + 30, top:e.pageY - 16 }).appendTo("body").fadeIn(2000) : null;
  };

  items.bind("mouseenter", createTip);

  items.mousemove(function(e) {

  //move tooltip
  $("#tip").css({ left:e.pageX + 30, top:e.pageY - 16 });
  });

  items.mouseleave(function() {

  //remove tooltip
  $("#tip").remove();
  });
  });
  </script> */
?>