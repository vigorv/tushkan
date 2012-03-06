<?php
$uploadServer = CServers::model()->getServer(UPLOAD_SERVER);
$user_id = Yii::app()->user->id;
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
    	<a href="#content" onClick="return BackAction()">Back</a>
<?php
	$userProducts = $productsInfo['tFiles'];
	$productParams = $productsInfo['fParams'];
	if (!empty($userProducts))
	{
		echo '<h4>Видео с витрин</h4>';
		foreach ($userProducts as $f)
		{
			$curVariantId = $f['variant_id'];
			$params = array();
			foreach($productParams as $p)
			{
				if ($p['id'] == $curVariantId)
				{
					$params[$p['title']] = $p['value'];
				}
			}

			if (!empty($params))
			{
				echo '<div class="chess"><a href="/universe/tview/' . $f['id'] . '">';
				if (!empty($params['poster']))
				{
					$poster = $params['poster'];
					unset($params['poster']);
				}
				else
				{
					$poster = '/images/films/noposter.jpg';
				}
				echo '<img align="left" width="80" src="' . $poster . '" />';
				echo '<b>' . $f['title'] . '</b>';
				echo '</a></div>';
			}
		}
		echo '<div class="divider"></div>';
	}
?>
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
    	<div class="items_add">
    	    <input  id="FileUpload" type="file" rel="fileInput" onChange="return UploadFilelistChange(this);" multiple /><br/>
    	    <input type="button" value="Choose file(s)..."  onClick="ChooseFile('FileUpload')"/>
    	    <ul id="UploadFilelist">


    	    </ul>
    	    <input type="button" onclick="return UploadFiles('FileUpload')" value="Upload"/>
    	    <div  id="progressBar" class="progress striped active animated">
    		<div class="bar" style="width: 0%"></div>
    	    </div>


    	</div>
    	<div id="items_unt">
    	    UntypedItems
    	    <ul>
		    <?= CFiletypes::ParsePrint($mb_content_items_unt, 'UTL1'); ?>
    	    </ul>
    	</div>


        </div>
    </div>



    <script langauge="javascript">

        var pBar= $("#progressBar div.bar");
        var unt =$("#items_unt");
        var ufs  = $("#UploadFilelist");


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

        function uploadComplete(smsg,msg)
        {
    	var successCount=0;
    	function parseAnswer(element, index, array){
    	    answer = $.parseJSON(element);

    	    if (answer != null){
    		if (answer.success){
    		    var fid= answer.fid;
    		    successCount++;
    		    ufs.html('');

    		    //alert(fid);
    		    //loadParams(currentTypeId, fid);
    		    //$("#paramsform").dialog("open");
    		} else{
    		    //upload failed
    		}

    	    }else{
    		//alert('bad JSON in uploader answer')
    	    }
    	}
    	smsg.forEach(parseAnswer);
    	if (successCount>0)
    	    $("#items_unt ul").load('/files/AjaxUntypedList');

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
    		$(pBar).width("0%");
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
    		//totalBar.style.width = ((rpe.loaded * progressWidth / rpe.total) >> 0) + "px";
    		console.log(pBar);
    		$(pBar).width((((this.sent + rpe.loaded) * 100 / this.total) >> 0) + "%");
    		//totalLoaded = this.total;
    		allAnswers = this.rtexts;
    	    },

    	    // fired when last file has been uploaded
    	    onload:function(rpe, xhr){

    		//progressBar.style.width = totalBar.style.width = progressWidth + "px";
    		uploadComplete(this.rtexts);
    		//"Server Response: " + xhr.responseText +
    		//"<br /><?php echo Yii::t('common', 'Total'); ?>: " + size(100))
    	    },

    	    // if something is wrong ... (from native instance or because of size)
    	    onerror:function(){
    		uploadComplete("The file " + this.file.fileName + " is too big [" + size(this.file.fileSize) + "]");
    	    }
    	});
        }



        function UploadFiles(ifiles) {
    	infiles = document.getElementById(ifiles);

    	startUpload(infiles.files,'none');
    	//self.clear;
    	$(infiles).replaceWith('<input  id="FileUpload" type="file" rel="fileInput" onChange="return UploadFilelistChange(this);" multiple style="display:hidden" />');
        }

        function UploadFilelistChange(e){
    	ufs.html('');
    	for (var x = 0; x < e.files.length; x++) {
    	    ufs.append('<li>'+e.files[x].name+'</li>');
    	}
        }

        function ChooseFile(ifiles){
    	infiles = document.getElementById(ifiles);
    	infiles.click();
        }

        function BackAction(){
    	cont.load(cl_history[cl_history.length-2]);
    	if (cl_history.length>1)
    	    cl_history.pop();
    	return false;
        }

    </script>

<?php endif; ?>

