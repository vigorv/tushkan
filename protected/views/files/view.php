<?php Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/fileuploader.js"); ?>
<?php Yii::app()->getClientScript()->registerCssFile(Yii::app()->request->baseUrl . "/css/fileuploader.css"); ?>

<div class="good_title">   
    <div class="P_section_1 fleft">My   Files</div>
    <div class="clearfix"></div>
</div>
<div id="result"></div>
<div id="file_manager">
    <div id="file_left_panel">
	<?php
	$this->widget('CFileTreeExt', array(
	    'id' => 'folder_tree',
	    'url' => array('/files/AjaxFoldersList'))
	);
	?>
        <div id="file_uploader">		
            <noscript><p>Please enable JavaScript to use file uploader.</p></noscript>         
        </div>
    </div>
    <div id="file_right_panel">
        <div  id="file_option">
            <a href="#" id="CreateDir">Create Directory</a>
            <a href="#"  id="downloadButton"><img />Download</a>
            <a href="#"  id="deleteButton"><img />Delete</a>
            <a href="#" id="AddtoCollection"><img />Add to Collection</a>

        </div>
        <ul id="file_list" tabindex="1" >
	    <?php if (!empty($filelist)): ?>
		<?php CFiletypes::ParsePrint($filelist, 'FL1'); ?>
	    <?php endif; ?>
        </ul>
    </div>
    <div class="clearfix"></div>
</div>


<script type="text/javascript">  
    var uploader = new qq.FileUploader({
        element: document.getElementById('file_uploader'),
        action: 'http://<?= $up_server; ?>/files/upload',
        params:{            
            user_id:'<?= $user_id; ?>',
            pid: 0
        },
        onSubmit: function(id, fileNanme){
	    var kpt_value;
	    $.ajax({
		url:'files/kpt',
		async:false,
		success:function(data){	
		    kpt_value=data;
		    uploader.setParam(
			'kpt',data
		    );	
		return true;
		}
	    });
	    if (kpt_value==undefined)
	    return false;
	},
        onComplete:function(id, fileName, responseJSON){
            flist=$('#file_list');
            flist.load('/files/fopen?id='+flist.attr('fid'));              
        },
        debug: false
    });           
    $('#downloadButton').click(function(e){
        var elem = $('#file_list').find('li.selected');
        fid = elem.attr('fid');
        dir = elem.attr('dir');
        if (fid>0){
            if (dir==undefined){                
                window.location.href=('/files/download?fid='+fid);
            } else alert("Can't download directory via browser");
        } else alert('Nothing selected');
    });
    $('#deleteButton').click(function(e){
        var elem = $('#file_list').find('li.selected');
        fid = elem.attr('fid');
        dir = elem.attr('dir');
        if (fid>0){
            if (dir==undefined){
                $.post('/files/remove',{id:fid},function(data){
                    if (data=='OK'){
                        elem.remove();
                    } 
                });  
            } else {
                $.post('/files/remove',{id:fid},function(data){
                    if (data=='OK'){
                        elem.remove();
                        $("#folder_tree").update();
                    } 
                });
            }
        } else alert('Nothing selected');
    });
    
    $('#CreateDir').click(function(e){
        var elem = $('#file_list')
        fid=elem.attr('fid');
        if (fid!=undefined) {
            window.location=('http://mycloud.local/files/create?fid='+fid);
        } else alert("unknown place to CreateDIr");
    });
    
    $('#AddtoCollection').click(function(e){
        var elem = $('#file_list').find('li.selected');
        fid=elem.attr('fid');
        if (fid!=undefined) {
            window.location=('http://mycloud.local/files/types?fid='+fid);
        } else alert("No items Selected");
    });
           
    $(document).delegate("#file_list li","click",function(e){
        if ($(this).hasClass('selected')){
            $(this).removeClass('selected');
        } else {
            $(this).addClass('selected');
        }
    });
    $(document).delegate("#file_list",'keydown',function(e){
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
    
    $('#file_list').load('/files/fopen?id=0');              
    $('#file_list').attr('fid',0);
    
    $("#folder_tree").bind("click", function(event) {
        if ($(event.target).is("span")) {
            var pid = $(event.target).parent('li').attr('id');
            $('#file_list').attr('fid',pid);
            $('#file_list').load('/files/fopen?id='+pid);              
            
            uploader.setParams(
            {
		//             kpt:'<= $kpt; ?>',
                user_id:'<= $user_id; ?>',
                pid:pid
            })
            return false;
        }
    });  
    
    
</script>  
