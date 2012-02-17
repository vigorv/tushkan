
<div id="Universe">
    <h1>Universe</h1>
    <div id="Universe_options">
        <div class="fleft">
            <a href="/universe/add"><img src="" width="25px" height="25px" />Add</a>
        </div>
        <div class="fright">
            <a href=""><img src="" width="25px" height="25px" />Delete </a>
        </div>
    </div>
    <div class="clearfix"></div>

    <div id="user_content" class="block_content">
<?php
	if (!empty($tFiles))
	{
		echo '<h4>Видео с витрин</h4>';
		foreach ($tFiles as $f)
		{
			$curVariantId = $f['variant_id'];
			$params = array();
			foreach($fParams as $p)
			{
				if ($p['id'] == $curVariantId)
				{
					$params[$p['title']] = $p['value'];
				}
			}

			if (!empty($params))
			{
				echo '<div class="shortfilm"><a href="/universe/tview/' . $f['id'] . '">';
				if (!empty($params['poster']))
				{
					$poster = $params['poster'];
					unset($params['poster']);
				}
				else
				{
					$poster = '/images/films/noposter.jpg';
				}
				echo '<img width="50" src="' . $poster . '" />';
				echo '<b>' . $f['title'] . '</b>';
				echo '</a></div>';
			}
		}
	}

	if (!empty($tObjects))
	{
		echo '<h4>Мое видео</h4>';
		foreach ($tObjects as $o)
		{
			$params = array();
			foreach($oParams as $p)
			{
				$params[$p['title']] = $p['value'];
			}
			echo '<div class="shortfilm"><a href="/universe/oview/' . $o['id'] . '">';
			if (!empty($params['poster']))
			{
				$poster = $params['poster'];
				unset($params['poster']);
			}
			else
			{
				$poster = '/images/films/noposter.jpg';
			}
			echo '<img width="50" src="' . $poster . '" />';
			echo '<b>' . $o['title'] . '</b>';
			echo '</a></div>';
		}
	}
?>
    </div>
    <div id="section_content" class="block_content">

    </div>
    <div id="section_files" class="block_content">

    </div>
    <div id="device_content" class="block_content">

    </div>
</div>
<script langauge="javascript">
    //$('#user_content').load('users/view');
  //$('#device_content').load('devices/view');
    //$('#section_content').load('sections/view')
//   $('#section_files').load('files')
</script>

<?php Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/multiuploader.js"); ?>

<script type="text/javascript">
	supportedExtensions = new Array();
	supportedExtensions[".mp4"] = 1;
	supportedExtensions[".mkv"] = 1;
	supportedExtensions[".avi"] = 1;
	supportedExtensions[".jpg"] = 1;

	function detectTypeId()
	{
		$("#fileList").text(''); z = '';
		for (i = 0; i < input.files.length; i++)
		{
			fn = input.files[i].fileName.toLowerCase();
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
			loadParams(currentTypeId);
		}
		showWizardPage(currentWizardPage);
		return res;
	}

	function getFileExt(filename)
	{
		if( filename.length == 0 ) return "";
		var dot = filename.lastIndexOf(".");
		if( dot == -1 ) return "";
		var extension = filename.substr(dot, filename.length);

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
		if (typeDetected)
		{
			radio = $("#wizardform input:radio").filter("[value='0']");
			inputs = $("#wizardform input:text").filter("[value != '']");
			if (inputs.length)
			{
				if (radio.length)
				{
					$(radio[0]).attr("checked", false);
					$(radio[0]).parent().hide();
				}
			}
			else
			{
				if (radio.length)
				{
					$(radio[0]).parent().show();
				}
			}
		}

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

	function loadParams(id)
	{
		document.getElementById("typeIdId").value = currentTypeId;

		if (id > 0)
		{
			$('#wizardpage2').load('/products/ajax', {typeId: id, action: "wizardtypeparams"});
		}
		$('#wizardpage2').html("<?php echo Yii::t('common', 'Please wait...');?>");

		return true;
	}

	var input, totalBar, progressBar, infoDiv, progressWidth, totalLoaded, allAnswers;

	function resetWizard()
	{
		currentTypeId = 0;
		currentWizardPage = 1;
		minWizardPage = 1; maxWizardPage = 4;
		typeDetected = false;

		$("#wizardpage1").html($("#wizardpage1clone").html());
		$("#wizardpage4").html($("#wizardpage4clone").html());

	    input		= $("#wizardpage1 input").filter("[rel='fileInput']");
	    input.attr("id", "fileInput");
	    input = document.getElementById("fileInput");

        totalBar	= $("#wizardpage4 div").filter("[rel='totalBar']");
	    totalBar.attr("id", "totalBar");
	    totalBar = document.getElementById("totalBar");

        progressBar	= $("#wizardpage4 div").filter("[rel='progressBar']");
	    progressBar.attr("id", "progressBar");
		progressBar = document.getElementById("progressBar");

        infoDiv		= $("#wizardpage4 div").filter("[rel='infoDiv']");
	    infoDiv.attr("id", "infoDiv");
	    infoDiv = document.getElementById("infoDiv");

		$( "#wizardpage4 [rel='doUploadbutton']" )
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

	function postParams()
	{
		$("input:hidden").filter("[name='wizardForm[uploadresults]']").val(allAnswers);
		$.post("/univers/postuploadparams", $("#paramsFormId").serialize());
	}
	function uploadComplete(msg)
	{
		infoDiv.innerHTML = '';
		alert('upload Complete');
		alert(allAnswers);
		$("#wizardform").dialog("close");
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
		<div id="wizardform" title="<?php echo Yii::t('common', 'Upload a file');?>">
				<div class="wizardpage" id="wizardpage1clone" style="display: none">
					<h3><?php echo Yii::t('common', 'Choose a file');?></h3>
					<input type="file" rel="fileInput" />
				</div>
				<div class="wizardpage" id="wizardpage1"></div>
			<form name="paramsForm" id="paramsFormId">
				<input type="hidden" name="wizardForm[uploadresults]" />
				<input id="typeIdId" type="hidden" name="wizardForm[typeId]" />

				<div class="wizardpage" id="wizardpage2" style="display: none;"></div>
				<div class="wizardpage" id="wizardpage3" style="display: none;">
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
				<div class="wizardpage" id="wizardpage4" style="display: none;"></div>
				<div class="wizardpage" id="wizardpage4clone" style="display: none;">
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

	function startUpload()
	{
        sendMultipleFiles({

        	url: "/files/receivefile?q=" + $("#wizardpage3 input:radio").filter("[checked != '']").val(),

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
               	postParams();
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
		width: 430,
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

	$( "#showwizardbutton" )
				.button()
				.click(function() {
					$( "#wizardform" ).dialog( "open" );
	});

</script>

