<?php
$uploadServer = CServers::model()->getServer(UPLOAD_SERVER);
$user_id = Yii::app()->user->id;
?>
<div class="container-fluid">
	<div class="row-fluid">
		<div class="span9">
			<i class="btn"> Choose file(s)...</i><input  id="FileUpload" type="file" rel="fileInput" onChange="return UploadFilelistChange(this);" multiple />
			<div class="clearfix"></div>
			<ul id="UploadFileList">

			</ul>
			<div class="clearfix"></div>
			<button class="btn" onClick="return UploadFiles('FileUpload')" >Upload</button>
			<div  id="progresstotal" class="progress striped active animated">
				<div class="bar" style="width: 0%"><p>Total</p></div>
			</div>
		</div>
		<div class="span3">
			<div class="well">
				Supported File Types:<br/>
				avi,mkv,mp4,flv
			</div>
		</div>
	</div>
</div>

<script language="javascript">
    
                                                                       
    var unt =$("#items_unt");
    var ufs  = $("#UploadFileList");

    function detectTypeId()
    {
        $("#fileList").text(''); z = '';
        for (i = 0; i < input.files.length; i++)
        {
			fn = input.files[i].name.toLowerCase();
			ext = getFileExt(fn);
			if (supportedExtensions[ext] != null)
			{
				typeDetected = true;
				res = supportedExtensions[ext];
				$("#fileList").append(z + fn);
				z = ', ';
			}
			else
			{
				typeDetected = false;
				res = 0;
				break;
			}
        }
        if ((res > 0) && (res != currentTypeId))
        {
			currentTypeId = res;
        }
        showWizardPage(currentWizardPage);
        return res;
    }

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

    function startUpload(files,preset)
    {
		$.ajax({type: "GET", url: '/files/KPT', async: false, success: function(data){ kpt = data;}});

		url = "http://<?= $uploadServer; ?>/files/uploads?preset="+preset
			+ "&kpt=" + kpt
			+ "&user_id=<?= $user_id; ?>";
		sendMultipleFiles({
			url: url,
			files:files,
			onloadstart:function(rpe){
				//    		infoDiv.innerHTML = "<?php echo Yii::t('common', 'Init upload'); ?> ...";				
				str="#progressBar"+this.current;					
				pr = $(ufs).find(str).children();
				
				$(pr).width("0%");
				//console.log(pr);
				$(pr).html("<p>0%</p>");
			},
			onprogress:function(rpe){
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
				
				dstat  = (((rpe.total)  / this.file.size)*100 >> 0) + "%";
				str="#progressBar"+this.current;					
				pr = ufs.find(str).children();
				
				$(pr).width(dstat);
				$(pr).html(dstat);
				//totalLoaded = this.total;
				allAnswers = this.rtexts;
			},

			// fired when last file has been uploaded
			onload:function(rpe, xhr){
				//progressBar.style.width = totalBar.style.width = progressWidth + "px";
				smsg=this.rtexts;
				var successCount=0;
				function parseAnswer(element, index, array){
					answer = $.parseJSON(element);

					if (answer != null){
						if (answer.success){
							var fid= answer.fid;
							str="#progressBar"+index;					
							prB = ufs.find(str);
							pr = prB.children();
							$(prB).addClass('progress-success');
							$(pr).html('Success');
							//$(pBar).html('<p>Success: '+successCount+'</p>');
							//progressL.append('<li>Success: '+ index+'</li>')
							//ufs.html('');

							//alert(fid);
							//loadParams(currentTypeId, fid);
							//$("#paramsform").dialog("open");
						} else{
							str="#progressBar"+index;					
							prB = ufs.find(str);
							pr = prB.children();
							$(prB).addClass('progress-danger');
							$(pr).html(answer.error);
							//upload failed
						}

					}else{
						//alert('bad JSON in uploader answer')
					}
				}
				smsg.forEach(parseAnswer);
				if (successCount>0)
					$("#items_unt ul").load('/files/AjaxUntypedList');                 
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
				$(pr).html('Connection error');
				//progressL.html('Troubles ' )
				//$(pBar).html("Error");
				//uploadComplete("The file " + this.file.fileName + " is too big [" + size(this.file.fileSize) + "]");
			}
		});
    }
                                                             	
    function UploadFiles(ifiles) {
		infiles = document.getElementById(ifiles);       
		//if(infiles.files.length>1){
		//	$('#progresstotal').show();
		//}
        startUpload(infiles.files,'none');
		
        //self.clear;
        $(infiles).replaceWith('<input  id="FileUpload" type="file" rel="fileInput" onChange="return UploadFilelistChange(this);" multiple "/>');
    }
    function UploadFilelistChange(e){
        ufs.html('');
        for (var x = 0; x < e.files.length; x++) {
			ufs.append('<li>'+e.files[x].name+'<div  id="progressBar'+x+'" class="progress striped active animated"><div class="bar" style="width: 0%"></div></div></li>');			
		}		
    }
                                                                                	
    
                            	
                                                                      	
</script>

