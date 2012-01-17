
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
            action: '/files/donothing',
            debug: true
        });           
    }
        
    // in your app create uploader as soon as the DOM is ready
    // don't wait for the window to load  
    window.onload = createUploader;     
</script>    