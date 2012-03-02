<?php
$uploadServer = CServers::model()->getServer(UPLOAD_SERVER);
$user_id=Yii::app()->user->id;
Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/multiuploader.js");
?>
<?php if (!isset($mb_content_items)): ?>
    <ul id ="lib_menu">
        <li><a href="/universe/library?lib=v">Video</a></li>
        <li><a href="/universe/library?lib=a">Audio</a></li>
        <li><a href="/universe/library?lib=p">Photo</a></li>
        <li><a href="/universe/library?lib=d">Docs</a></li>
    </ul>
<?php else: ?>
    <?php if (isset($mb_top_items)): ?>
	<div class="lib_top">
	    <ul>
		<?php foreach ($mb_top_items as $mb_top_item): ?>
	    	<li><a href="<?= $mb_top_item['link']; ?>"><?= $mb_top_item['caption']; ?></a></li>
		<?php endforeach; ?>
	    </ul>
	</div>
    <?php endif; ?>
    <div class="lib_content">
        <div class="top_menu">
    	<a href="Back">Back</a>
    	<h4></h4>
        </div>
        <div class="filters">   

        </div>
        <div class="items">
    	TypedItems
    	<ul>	
		<?= CFiletypes::ParsePrint($mb_content_items, 'TL1'); ?>
    	</ul>
        </div>
        <div class="ext">
    	<div class="items_unt">
    	    UntypedItems
    	    <ul>
		    <?= CFiletypes::ParsePrint($mb_content_items_unt, 'UTL1'); ?>
    	    </ul>
    	</div>
    	<div class="items_add">	    
    	    <input id="FileUpload" type="file" rel="fileInput" />
    	</div>
    	<div class="gradusnik" rel="totalBar"><span></span></div>
    	<div id="progressBar" class="gradusnik" rel="progressBar"><span></span></div>
        </div>
    </div>


    <script langauge="javascript">
        progressBar= $("#progressBar");
        
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
                	
        function uploadComplete(msg)
        {
    	infoDiv.innerHTML = '';

    	answer = $.parseJSON(allAnswers);
    	$("#wizardform").dialog("close");

    	if (answer != null)
    	{
    	    if (answer.success)
    	    {
    		var fid= answer.fid;
    		alert(fid);
    		loadParams(currentTypeId, fid);
    		$("#paramsform").dialog("open");
    	    }
    	    else
    	    {
    		alert('upload failed')
    	    }
    	}
    	else
    	{
    	    alert('bad JSON in uploader answer')
    	}
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

    	url = "http://<?php echo $uploadServer; ?>/files/uploads?preset="+preset
    	    + "&kpt=" + kpt
    	    + "&user_id=<?php echo $user_id; ?>";
    	sendMultipleFiles({
    	    url: url,
    	    files:files,
    	    onloadstart:function(){
    		//    		infoDiv.innerHTML = "<?php echo Yii::t('common', 'Init upload'); ?> ...";
    		progressBar.style.width = totalBar.style.width = "0px";
    	    },
    	    onprogress:function(rpe){
    		infoDiv.innerHTML = [
    		    "<?php echo Yii::t('common', 'Uploading'); ?>: " + this.file.fileName,
    		    "<?php echo Yii::t('common', 'Sent'); ?>: " + size(rpe.loaded) + " <?php echo Yii::t('common', 'of'); ?> " + size(rpe.total),
    		    "<?php echo Yii::t('common', 'Total'); ?>: " + size(this.sent + rpe.loaded) + " <?php echo Yii::t('common', 'of'); ?> " + size(this.total)
    		].join("<br />");
    		totalBar.style.width = ((rpe.loaded * progressWidth / rpe.total) >> 0) + "px";
    		progressBar.style.width = (((this.sent + rpe.loaded) * progressWidth / this.total) >> 0) + "px";
    		totalLoaded = this.total;
    		allAnswers = this.rtexts;
    	    },

    	    // fired when last file has been uploaded
    	    onload:function(rpe, xhr){
    		allAnswers = this.rtexts;
    		progressBar.style.width = totalBar.style.width = progressWidth + "px";
    		uploadComplete("Server Response: " + xhr.responseText +
    		    "<br /><?php echo Yii::t('common', 'Total'); ?>: " + size(totalLoaded));
    	    },

    	    // if something is wrong ... (from native instance or because of size)
    	    onerror:function(){
    		uploadComplete("The file " + this.file.fileName + " is too big [" + size(this.file.fileSize) + "]");
    	    }
    	});
        }
                	
                	
                	
        $("#FileUpload").change(function(){
    	startUpload(this.files,'none');
    	//self.clear;
        });
                	
    </script>

<?php endif; ?>

