<?php
$uploadServer = CServers::model()->getServer(UPLOAD_SERVER);
$user_id = Yii::app()->user->id;
?>
<div id="upload_container" class="container-fluid closed">
	<div class="row-fluid">
		<div class="span9">
			<i class="btn"><?= Yii::t('users', 'Choose file(s)'); ?>...</i><input  id="FileUpload" type="file" rel="fileInput" onChange="return UploadFilelistChange(this);" multiple />
			<div class="clearfix"></div>
			<ul id="tmp_ufs"></ul>
			<ul id="UploadFileList">

			</ul>
			<div class="clearfix"></div>
			<button class="btn" onClick="return UploadFiles('FileUpload')" ><?= Yii::t('common', 'Upload'); ?></button>
			<div  id="progresstotal" class="progress striped active animated">
				<div class="bar" style="width: 0%"><p>Total</p></div>
			</div>
		</div>
		<div class="span3">
			<div class="well">
				<?= Yii::t('users', 'Supported filetypes'); ?>:<br/>
				<?= Yii::t('users', 'Video'); ?>(avi,mkv,mp4,flv)
			</div>
		</div>
	</div>
</div>

<script language="javascript">
    
    var upload_queue_id =0;
    var unt =$("#items_unt");
    var ufs  = $("#UploadFileList");
	var tmp_ufs=$('#tmp_ufs');
	supportedExtensions = ['mkv','mp4','flv','avi',];
	//supportedExtensions['mkv']=1;
	//supportedExtensions['mp4']=1;
	//supportedExtensions['flv']=1;
	//supportedExtensions['avi']=1;

	

    function getFileExt(filename)
    {
        if( filename.length == 0 ) return "";
        var dot = filename.lastIndexOf(".");
        if( dot == -1 ) return "";
        var extension = filename.substr(dot + 1, filename.length);
        return extension;
    }

    function size(bytes){   // simple function to show a friendly size
		var i = 0;
		while(1023 < bytes){
			bytes /= 1024;
			++i;
		};
		return  i ? bytes.toFixed(2) + ["", " Kb", " Mb", " Gb", " Tb"][i] : bytes + " bytes";
    };

    var kpt = '';

    function startUpload(files,uqueue_id,preset)
    {
		$.ajax({type: "GET", url: '/files/KPT', async: false, success: function(data){ kpt = data;}});

		url = "http://<?= $uploadServer; ?>/files/uploads?preset="+preset
			+ "&kpt=" + kpt
			+ "&user_id=<?= $user_id; ?>";
		console.log(files.length);
		sendMultipleFiles({
			url: url,
			files:files,
			onloadstart:function(rpe,xhr){
				//    		infoDiv.innerHTML = "<?php echo Yii::t('common', 'Init upload'); ?> ...";								
				str='#progressBar_'+uqueue_id+'_'+this.current;	
				prB = ufs.find(str);				
				if($(prB).parent().find('.icon-remove').length==0) {
					xhr.abort();			
					prB.parent().append('<i class="icon-ok-sign"></i>')
					return;
				}
				pr = $(ufs).find(str).children('div');
				//console.log(pr);
				$(pr).width("0%");
				//console.log(pr);
				$(pr).html("<p>0%</p>");
			},
			onprogress:function(rpe,xhr){
				/*
				 *
				 *
         infoDiv.innerHTML = [
        "<?php echo Yii::t('common', 'Uploading'); ?>: " + this.file.fileName,
        "<?php echo Yii::t('common', 'Sent'); ?>: " + size(rpe.loaded) + " <?php echo Yii::t('common', 'of'); ?> " + size(rpe.total),
        "<?php echo Yii::t('common', 'Total'); ?>: " + size(this.sent + rpe.loaded) + " <?php echo Yii::t('common', 'of'); ?> " + size(this.total)
        ].join("<br />");
				 */
				
				
				//$('#progresstotal').style.width = ((rpe.loaded *100/ rpe.total) >> 0) + "%";
				//console.log(rpe);
				//console.log(this.file.filesize)
				//console.log(rpe);
				dstat  = (((rpe.loaded)  / this.file.size)*100 >> 0) + "%";
				str='#progressBar_'+uqueue_id+'_'+this.current;		
				
				prB= ufs.find(str)
				if($(prB).parent().find('.icon-remove').length==0) {
						xhr.abort();
						//prB.parent().append('<i class="icon-ok-sign"></i>')
						return
					return;
				}
				pr=(prB).children();

				$(pr).width(dstat);
				$(pr).html('<p>'+dstat+'</p>');
				//totalLoaded = this.total;
				//allAnswers = this.rtexts;
			},

			// fired when last file has been uploaded
			onload:function(rpe, xhr){
				//progressBar.style.width = totalBar.style.width = progressWidth + "px";
				//smsg=this.rtexts;
				var successCount=0;
				//function parseAnswer(element, index, array){
				answer = $.parseJSON(xhr.responseText);
				console.log(xhr.responseText);
				if (answer != null){
					if (answer.success){
						var fid= answer.fid;						
						str='#progressBar_'+uqueue_id+'_'+(this.current-1);		
						prB = ufs.find(str);
						$(prB).parent().find('.icon-remove').remove();
						$(prB).addClass('progress-success');
						pr = $(prB).children();
						$(pr).width("100%");
						$(pr).html('<p>Success</p>');
						$(prB).parent().append('<i class="icon-ok-sign"></i>')
						//$(pBar).html('<p>Success: '+successCount+'</p>');
						//progressL.append('<li>Success: '+ index+'</li>')
						//ufs.html('');

						//alert(fid);
						//loadParams(currentTypeId, fid);
						//$("#paramsform").dialog("open");
					} else{
						str='#progressBar_'+uqueue_id+'_'+(this.current-1);
						prB = ufs.find(str);						
						$(prB).addClass('progress-danger');	
						$(prB).parent().find('.icon-remove').remove();
						$(prB).parent().append('<i class="icon-ok-sign"></i>')
						//console.log(prB);
						pr = $(prB).children();
						$(pr).width("100%");
						$(pr).html('<p>'+answer.error+'</p>');
						//upload failed
					}
				}else{
					str='#progressBar_'+uqueue_id+'_'+(this.current-1);							
					prB = ufs.find(str);
					$(prB).addClass('progress-danger');
						$(prB).parent().find('.icon-remove').remove();
						$(prB).parent().append('<i class="icon-ok-sign"></i>')
					pr = prB.children();
					$(pr).width("100%");
					$(pr).html("<p>bad answer</p>");
					//alert('bad JSON in uploader answer')
				}
				//}
				//smsg.forEach(parseAnswer);
				//if (successCount>0)
				//	$("#items_unt ul").load('/files/AjaxUntypedList');                 
				//"Server Response: " + xhr.responseText +
				//"<br /><?php echo Yii::t('common', 'Total'); ?>: " + size(100))
			},

			// if something is wrong ... (from native instance or because of size)
			onerror:function(rpe){
				//progressB.removeClass('progress').addClass('progress-danger');
				str="#progressBar"+this.current;
				prB = ufs.find(str);
				pr = prB.children();
				$(prB).addClass('progress-danger');
				$(pr).html('<p>Error</p>');
				//progressL.html('Troubles ' )
				//$(pBar).html("Error");
				//uploadComplete("The file " + this.file.fileName + " is too big [" + size(this.file.fileSize) + "]");
			}
		});
    }
                                                             
	function AbortUpload(qid,id){
		prB = ufs.find('#progressBar_'+qid+'_'+id);
		//if( prB.children().html() == 'wait' ){
			$(prB).parent().find('.icon-remove').remove();
			$(prB).children().html('aborted');
			$(prB).addClass('progress-danger');
			$(prB).parent().append('<i class="icon-ok-sign"></i>')
			pr = prB.children();
		
			$(pr).width("100%");
		//} 
		return false;

	}															 
															 
															 
    function UploadFiles(ifiles) {
		tmp_ufs.html('');
		uqueue_id = upload_queue_id;	
		upload_queue_id++;	
		
		UploadList=new Array();
		e = document.getElementById(ifiles);       
		for (var x = 0; x < e.files.length; x++) {
			fname= e.files[x].name.toLowerCase();
			ext = getFileExt(fname);
			if ( $.inArray(ext,supportedExtensions)>-1)	{
				current =UploadList.push(e.files[x]);
				ufs.append('<li>'+e.files[x].name+'<div  id="progressBar_'+uqueue_id+'_'+(current-1)+'" class="progress striped active animated"><div class="bar" style="width: 0%">wait</div></div>\n\
				<a href="#" onClick="return AbortUpload('+uqueue_id+','+(current-1)+')" ><i class="icon-remove"></i></a></li>');			
				//				console.log('push'+e.files[x]);
				//				console.log(UploadList);
			} 
		}		
		
		
        startUpload(UploadList,uqueue_id,'none');

        //self.clear;
        $(e).replaceWith('<input  id="FileUpload" type="file" rel="fileInput" onChange="return UploadFilelistChange(this);" multiple "/>');
		delete UploadList;		
		
    }
    function UploadFilelistChange(e){

        tmp_ufs.html('');
        for (var x = 0; x < e.files.length; x++) {
			fname= e.files[x].name.toLowerCase();
			ext = getFileExt(fname);
			if ( $.inArray(ext,supportedExtensions)>-1)	{
				tmp_ufs.append('<li><img src="/images/16x16/actions/ok.png" /> Поддерживаемый формат: &#9; &#9; '+e.files[x].name+'</li>');
			} else {
				tmp_ufs.append('<li><img src="/images/16x16/actions/no.png" /> Неподдерживаемый формат:&#9; '+e.files[x].name +'</li>');
				 
			}
		}		
    }
                                                                                	
    
                            	
                                                                      	
</script>


