<?php
$kpt = md5($user_id . $sid . "I am robot");

/*<iframe src="http://files.mycloud.local/files/upload?kpt=<?= $kpt; ?>&user_id=<?= $user_id; ?>">


</iframe>
*/
?>
<?php Yii::app()->getClientScript()->registerScriptFile(Yii::app()->request->baseUrl . "/js/fileuploader.js"); ?>
<?php Yii::app()->getClientScript()->registerCssFile(Yii::app()->request->baseUrl . "/css/fileuploader.css"); ?>
<div id="file-uploader-demo1">		
    <noscript>			
    <p>Please enable JavaScript to use file uploader.</p>
    <!-- or put a simple form for upload here -->
    </noscript>         
</div>

<script>        
     function createUploader(){            
                var uploader = new qq.FileUploader({
                    element: document.getElementById('file-uploader-demo1'),
                    action: 'http://files.mycloud.local/files/donothing',
                    params:{
                        kpt:'<?= $kpt; ?>',
                        user_id:'<?= $user_id; ?>',
                        pid: 0
                    },
                    debug: true
                });           
            }
    // in your app create uploader as soon as the DOM is ready
    // don't wait for the window to load  
    window.onload = createUploader;     
</script>    