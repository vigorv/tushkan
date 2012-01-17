<div class="good_title">   
    <div class="P_section_1 fleft">My Files</div>
    <div class="P_section_2_0 fleft">
        <ul class="options fleft">
            <li><img width="25px" height="25px"/>New</li>
            <li><img width="25px" height="25px"/>Add</li>
        </ul>
        <ul class="options fright">
            <li><img width="25px" height="25px"/>Delete</li>
        </ul>
    </div>
<div class="clearfix"></div>
</div>


<?php
for ($i = 1; $i < 1000; $i++) {
    $files[] = array('name' => 'test');
}
?>
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
                var new_e = elem.next('li');
                break;
            case 37:// left
                var new_e = elem.prev('li');
                break;
            case 38://up
                var line_count=parseInt($(this).width() / 75)-1;
                var new_e = elem.prevAll("li:eq("+line_count+")");
                break;
            case 40://down                
                var line_count=parseInt($(this).width() / 75)-1;
                var new_e = elem.nextAll("li:eq("+line_count+")");    
                break;
            case 35://end
                var new_e =  $("#file_list li").last();
                break;
            case 36://home
                var new_e =  $("#file_list li").first();
                break;
            default:
        }
        if  (new_e && new_e.length){
            elem.removeClass('elem');
            new_e.addClass('elem');
        }
    }
);
    $("#file_list li:first ").addClass('elem');
    
    
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