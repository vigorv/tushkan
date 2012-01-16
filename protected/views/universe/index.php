<style>
    #Universe{
        padding:10px;
    }
    .block_content{
     margin:10px;   
    }
</style>
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
        
    </div>
    <div id="section_content" class="block_content">
        
    </div>
    <div id="section_files" class="block_content">
        
    </div>
    <div id="device_content" class="block_content">
        
    </div>
</div>
<script langauge="javascript">
    $('#user_content').load('user/view');
    $('#device_content').load('devices/view');
    $('#section_content').load('sections/view')
    $('#section_files').load('files')
</script>