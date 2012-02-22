<?php
Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/multiuploader.js");

$uploadServer = CServers::model()->getServer(UPLOAD_SERVER);

$quality = Utils::getVideoConverterQuality('values');
$media = Utils::getMediaList();
?>

<script type="text/javascript">
	supportedExtensions = new Array();
<?php
	foreach ($media as $m)
	{
		foreach($m['exts'] as $e)
			echo 'supportedExtensions["' . $e . '"] = ' . $m['id'] . ';';
	}
?>

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

	currentTypeId = 0;
	currentWizardPage = 1;
	minWizardPage = 1; maxWizardPage = 4;
	typeDetected = false;

	function showWizardPage(p)
	{
		$(".wizardpage").hide();
		$("#wizardpage" + p).show();
		currentWizardPage = p;
		wButtons = $( "#wizardform" ).dialog( "option", "buttons" );

		if (currentWizardPage == minWizardPage)
			wButtons[2]["disabled"] = true;
		else
			wButtons[2].disabled = false;
		if (currentWizardPage == maxWizardPage)
			wButtons[1]["disabled"] = true;
		else
			wButtons[1]["disabled"]	 = !typeDetected;

		$( "#wizardform" ).dialog( "option", "buttons", wButtons );

	}

	var input, totalBar, progressBar, infoDiv, progressWidth, totalLoaded, allAnswers;

	function resetWizard()
	{
		currentTypeId = 0;
		currentWizardPage = 1;
		minWizardPage = 1; maxWizardPage = 3;
		typeDetected = false;

		$("#wizardpage1").html($("#wizardpage1clone").html());
		$("#wizardpage3").html($("#wizardpage3clone").html());

	    input		= $("#wizardpage1 input").filter("[rel='fileInput']");
	    input.attr("id", "fileInput");
	    input = document.getElementById("fileInput");

        totalBar	= $("#wizardpage3 div").filter("[rel='totalBar']");
	    totalBar.attr("id", "totalBar");
	    totalBar = document.getElementById("totalBar");

        progressBar	= $("#wizardpage3 div").filter("[rel='progressBar']");
	    progressBar.attr("id", "progressBar");
		progressBar = document.getElementById("progressBar");

        infoDiv		= $("#wizardpage3 div").filter("[rel='infoDiv']");
	    infoDiv.attr("id", "infoDiv");
	    infoDiv = document.getElementById("infoDiv");

		$( "#wizardpage3 [rel='doUploadbutton']" )
					.button()
					.click(function() {
						startUpload();
		});

	    //input.setAttribute("multiple", "true");
	    input.setAttribute("multiple", "false");
	    input.addEventListener("change", function(){ detectTypeId(); }, false);

	   	progressWidth = 400;
		totalLoaded = 0;
		allAnswers = '';
		showWizardPage(1);
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
				loadParams(currentTypeId, answer.id);
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

	function loadParams(typeId, fileId)
	{
		document.getElementById("typeIdId").value = typeId;
		document.getElementById("fileIdId").value = fileId;

		if (typeId > 0)
		{
			$('#paramspage1').load('/products/ajax', {typeId: typeId, action: "wizardtypeparams"});
		}
		$('#paramspage1').html("<?php echo Yii::t('common', 'Please wait...');?>");
		$("#paramspage1").show();

		return true;
	}

	function postParams()
	{
		inputs = $("#paramsform input:text").filter("[value != '']");
		if (inputs.length)
		{
			$.post("/universe/postuploadparams", $("#paramsFormId").serialize());
			$("#paramsform").dialog("close");
		}
		else
		{
			return false;
		}
	}

</script>
<style type="text/css">
.gradusnik {
    width: 400px;
    border: 1px solid #BBB;
    background-color: #FFF;
    padding: 0;
    margin-top: 20px;
}
.gradusnik span {
    display: block;
    width: 0px;
    height: 20px;
    background-color: #DDD;
}
#from {
    position: absolute;
    top: 120px;
}
</style>
		<div class="form" id="paramsform" title="<?php echo Yii::t('common', 'File info');?>">
			<form name="paramsForm" id="paramsFormId">
				<input id="typeIdId" type="hidden" name="paramsForm[typeId]" />
				<input id="fileIdId" type="hidden" name="paramsForm[fileId]" />
			<div class="wizardpage" id="paramspage1" style="display: block;"></div>
			</form>
		</div>

		<div class="form" id="wizardform" title="<?php echo Yii::t('common', 'Upload a file');?>">
				<div class="wizardpage" id="wizardpage1clone" style="display: none">
					<h3><?php echo Yii::t('common', 'Choose a file');?></h3>
					<input type="file" rel="fileInput" />
				</div>
				<div class="wizardpage" id="wizardpage1"></div>
				<input type="hidden" name="wizardForm[uploadresults]" />

				<div class="wizardpage" id="wizardpage2" style="display: none;">
					<h3><?php echo Yii::t('common', 'Choose convert quality');?></h3><br />
<?php
				$checked = 'checked';
				foreach ($quality as $k => $v)
				{
					echo '<div><input ' . $checked . ' type="radio" name="wizardForm[quality]" value="' . $k . '" class="text ui-widget-content ui-corner-all" />' . $v . '</div>';
					$checked = '';
				}
?>
				</div>
			</form>
				<div class="wizardpage" id="wizardpage3" style="display: none;"></div>
				<div class="wizardpage" id="wizardpage3clone" style="display: none;">
					<h4><?php echo Yii::t('common', 'Upload process');?></h4>

					<div class="gradusnik" rel="totalBar"><span></span></div>

					<div class="gradusnik" rel="progressBar"><span></span></div>

					<br />
					<div rel="infoDiv">
						<button rel="doUploadbutton"><?php echo Yii::t('common', 'Upload');?></button><br />
						<div rel="fileList"></div>
					</div>


				</div>
		</div>
		<button id="showwizardbutton"><?php echo Yii::t('common', 'Upload a file');?></button>



<script type="text/javascript">

    function size(bytes){   // simple function to show a friendly size
        var i = 0;
        while(1023 < bytes){
            bytes /= 1024;
            ++i;
        };
        return  i ? bytes.toFixed(2) + ["", " Kb", " Mb", " Gb", " Tb"][i] : bytes + " bytes";
    };

    var kpt = '';
	function startUpload()
	{
		$.ajax({
			type: "GET",
			url: '/files/KPT',
			async: false,
			success: function(data){
	    		kpt = data;
			}
	 	});

	 	url = "http://<?php echo $uploadServer; ?>/files/uploads?preset="
        		+ $("#wizardpage2 input:radio").filter("[checked != '']").val()
        		+ "&kpt=" + kpt
        		+ "&user_id=<?php echo $user_id; ?>";
	 	sendMultipleFiles({

        	//url: "/files/receivefile?preset="
        	url: url,

            files:input.files,

            onloadstart:function(){
                infoDiv.innerHTML = "<?php echo Yii::t('common', 'Init upload');?> ...";
                progressBar.style.width = totalBar.style.width = "0px";
            },

            onprogress:function(rpe){
				infoDiv.innerHTML = [
                    "<?php echo Yii::t('common', 'Uploading');?>: " + this.file.fileName,
                    "<?php echo Yii::t('common', 'Sent');?>: " + size(rpe.loaded) + " <?php echo Yii::t('common', 'of');?> " + size(rpe.total),
                    "<?php echo Yii::t('common', 'Total');?>: " + size(this.sent + rpe.loaded) + " <?php echo Yii::t('common', 'of');?> " + size(this.total)
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
                    "<br /><?php echo Yii::t('common', 'Total');?>: " + size(totalLoaded));
            },

            // if something is wrong ... (from native instance or because of size)
            onerror:function(){
				uploadComplete("The file " + this.file.fileName + " is too big [" + size(this.file.fileSize) + "]");
            }
        });
	}


	$( "#wizardform" ).dialog({
		autoOpen: false,
		height: 370,
		width: 440,
		modal: true,
		buttons: {
			1: {
				disabled: true,
				text: "<?php echo Yii::t('common', 'Next');?>",
				click: function() {
					if (currentWizardPage < maxWizardPage)
						showWizardPage(currentWizardPage + 1);
				}
			},
			2: {
				disabled: true,
				text: "<?php echo Yii::t('common', 'Back');?>",
				click: function() {
					if (currentWizardPage > minWizardPage)
						showWizardPage(currentWizardPage - 1);
				}
			},
			3: {
				text: "<?php echo Yii::t('common', 'Close');?>",
				click: function() {
					$( this ).dialog( "close" );
				}
			}
		},
		close: function() {
		},
		open: function(event, ui) { resetWizard(); }
	});

	$( "#paramsform" ).dialog({
		autoOpen: false,
		height: 480,
		width: 640,
		modal: true,
		buttons: {
			1: {
				text: "<?php echo Yii::t('common', 'Submit');?>",
				click: function() {
					postParams();
				}
			},
			3: {
				text: "<?php echo Yii::t('common', 'Close');?>",
				click: function() {
					$( this ).dialog( "close" );
				}
			}
		},
		close: function() {
		},
	});

	$( "#showwizardbutton" )
				.button()
				.click(function() {
					$( "#wizardform" ).dialog( "open" );
	});

</script>

