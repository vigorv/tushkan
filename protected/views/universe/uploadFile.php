<?php
$uploadServer = CServers::model()->getServer(UPLOAD_SERVER);
Yii::app()->clientScript->registerScriptFile('/js/multiuploader.js');
$user_id = Yii::app()->user->id;
?>
<div id="upload_container" class="container-fluid closed">
    <div class="row-fluid">
        <div class="span9">
            <input  id="FileUpload" type="file" rel="fileInput" onChange="return UploadFilelistChange(this);" multiple/>
            <div class="clearfix"></div>
            <ul id="tmp_ufs"></ul>
            <ul id="UploadFileList">

            </ul>
            <div class="clearfix"></div>
            <input type="submit" class="btn" onClick="UploadFiles('FileUpload');" value="<?= Yii::t('common', 'Upload'); ?>" />

            <button class="btn" onClick="clearFinished(); return false;" ><?= Yii::t('common', 'Clear finished'); ?></button>

            <?/*  <div  id="progresstotal" class="progress striped active animated">
                <div class="bar" style="width: 0%"></div>
            </div>
           * 
           */?>
        </div>
        <div class="span3">
            <div class="well">
                <?= Yii::t('users', 'Supported filetypes'); ?>:<br/>
                <?= Yii::t('users', 'All'); ?>(*.*)
            </div>
        </div>
    </div>
</div>

<div id="uploadResults" style="margin:10px">

</div>

<script language="javascript">

    var upload_queue_id =0;
    var unt =$("#items_unt");
    var ufs  = $("#UploadFileList");
    var tmp_ufs=$('#tmp_ufs');
    var supportedExtensions ='*';// ['mkv','mp4','flv','avi',];

    function getFileExt(filename)
    {
        if( filename.length == 0 ) return "";
        var dot = filename.lastIndexOf(".");
        if( dot == -1 ) return "";
        var extension = filename.substr(dot + 1, filename.length);
        return extension;
    }

    $.support.xhrFileUpload = !!(window.XMLHttpRequestUpload && window.FileReader);
    $.support.xhrFormDataFileUpload = !!window.FormData;


    var UploadList=new Array();


    function size(bytes){   // simple function to show a friendly size
        var sizes = [" B", " KB ", " MB ", " GB ", " TB "," PB "];
        var i = 0;
        var sz ='';
        sz = (bytes & 0x3FF) + sizes[i] + sz;
        while(1023 < bytes){
            bytes = bytes >> 10;
            ++i;
            sz = (bytes & 0x3FF) + sizes[i] + sz;
        };
        return  sz;
    };

    var kpt = '';

    function startUpload(uqueue_id,preset)
    {

        url = "http://<?= $uploadServer; ?>/files/uploads?uid=<?=Yii::app()->user->id;?>&key=<?=Yii::app()->user->getState('ukey');?>";
        sendMultipleFiles({
            url: url,
            files:UploadList,
            onloadstart:function(rpe,xhr){
                // Init
                str='#progressBar_'+uqueue_id+'_'+this.current;
                prB = ufs.find(str);
                if($(prB).parent().find('.icon-remove').length==0) {
                    xhr.abort();
                    prB.parent().append('<i class="icon-ok-sign"></i>')
                    return;
                }
                pr = $(ufs).find(str).children('div');
                $(pr).width("0%");
                $(pr).html("<p>0%</p>");
            },
            onprogress:function(rpe,xhr){
                dstat  = (((rpe.loaded)  / this.file.size)*100 >> 0) + "%";
                str='#progressBar_'+uqueue_id+'_'+this.current;
                prB= ufs.find(str)
                if($(prB).parent().find('.icon-remove').length==0) {
                    xhr.abort();
                    return;
                }
                pr=(prB).children();
                $(pr).width(dstat);
                $(pr).html('<p>'+dstat+'</p>');
            },

            // fired when last file has been uploaded
            onload:function(rpe, xhr){
                answer = $.parseJSON(xhr.responseText);
                //console.log(xhr.responseText);
                if (answer != null){
                    if (answer.success){
                        var fid = answer.success;
                        str='#progressBar_'+uqueue_id+'_'+(this.current-1);
                        prB = ufs.find(str);
                        $(prB).parent().find('.icon-remove').remove();
                        $(prB).addClass('progress-success');
                        pr = $(prB).children();
                        $(pr).width("100%");
                        $(pr).html('<p>Success</p>');
                        $(prB).parent().append('<a href="#" onClick="return clearU(this);"><i class="icon-ok-sign"></i></a>');
                        $('#uploadResults').append("<p><?=Yii::app()->createAbsoluteUrl('catalog/viewv');?>/"+fid+"</p>");
                    } else{
                        str='#progressBar_'+uqueue_id+'_'+(this.current-1);
                        prB = ufs.find(str);
                        $(prB).addClass('progress-danger');
                        $(prB).parent().find('.icon-remove').remove();
                        $(prB).parent().append('<a href="#" onClick="return clearU(this);"><i class="icon-ok-sign"></i></a>');
                        pr = $(prB).children();
                        $(pr).width("100%");
                        $(pr).html('<p>'+answer.error+'</p>');
                    }
                }else{
                    str='#progressBar_'+uqueue_id+'_'+(this.current-1);
                    prB = ufs.find(str);
                    $(prB).addClass('progress-danger');
                    $(prB).parent().find('.icon-remove').remove();
                    $(prB).parent().append('<a href="#" onClick="return clearU(this);"><i class="icon-ok-sign"></i></a>');
                    pr = prB.children();
                    $(pr).width("100%");
                    $(pr).html("<p>bad answer</p>");
                }
            },

            // if something is wrong ... (from native instance or because of size)
            onerror:function(rpe){
                str='#progressBar_'+uqueue_id+'_'+(this.current-1);
                prB = ufs.find(str);
                $(prB).addClass('progress-danger');
                $(prB).parent().find('.icon-remove').remove();
                $(prB).parent().append('<a href="#" onClick="return clearU(this);"><i class="icon-ok-sign"></i></a>');
                pr = prB.children();
                $(pr).width("100%");
                $(pr).html("<p>bad answer</p>");

            }
        });
    }

    function AbortUpload(qid,id){
        prB = ufs.find('#progressBar_'+qid+'_'+id);
        $(prB).parent().find('.icon-remove').remove();
        $(prB).children().html('aborted');
        $(prB).addClass('progress-danger');
        $(prB).parent().append('<a  href="#" onClick="return clearU(this);"><i class="icon-ok-sign"></i></a>');
        pr = prB.children();
        $(pr).width("100%");
        return false;
    }

    function clearU(e){
        $(e).closest('li').remove();
    }

    function clearFinished(){
        $(ufs).find(".icon-ok-sign").closest("li").remove();
    }

    function UploadFiles(ifiles) {

        tmp_ufs.html('');
        uqueue_id = upload_queue_id;
        upload_queue_id++;


        e = document.getElementById(ifiles);
        for (var x = 0; x < e.files.length; x++) {
            fname= e.files[x].name.toLowerCase();
            ext = getFileExt(fname);
            if (( supportedExtensions=='*') || ($.inArray(ext,supportedExtensions)>-1))	{
                current =UploadList.push(e.files[x]);
                ufs.append('<li>'+e.files[x].name+'<div  id="progressBar_'+uqueue_id+'_'+(current-1)+'" class="progress striped active animated"><div class="bar" style="width: 0%">wait</div></div>\n\
                                <a href="#" onClick="return AbortUpload('+uqueue_id+','+(current-1)+')" ><i class="icon-remove"></i></a></li>');
            }
        }


        startUpload(uqueue_id,'none');

        //self.clear;
        $(e).replaceWith('<input  id="FileUpload" type="file" rel="fileInput" onChange="return UploadFilelistChange(this);" multiple "/>');


    }
    function UploadFilelistChange(e){

        tmp_ufs.html('');
        for (var x = 0; x < e.files.length; x++) {
            fname= e.files[x].name.toLowerCase();
            ext = getFileExt(fname);
            if ((supportedExtensions=='*') || ($.inArray(ext,supportedExtensions)>-1))	{
                tmp_ufs.append('<li><img src="/images/16x16/actions/ok.png" /> Поддерживаемый формат: &#9; &#9; '+e.files[x].name+'</li>');
            } else {
                tmp_ufs.append('<li><img src="/images/16x16/actions/no.png" /> Неподдерживаемый формат:&#9; '+e.files[x].name +'</li>');

            }
        }
    }

</script>


