<style>
    #Universe{
        padding:10px;
    }
    .block_content{
     margin:10px;   
    }
    
</style>
<div id="Universe">
 <h3>Add</h3>
<input type="text" name="search" value="" placeholder="Search for something..."/>
<div class="resourse_link">
    <strong>Add by link</strong>
    <form>
        <input type="text" name="external_link" value="" />
        <input type="submit" value="add"/>
    </form>
        
</div>

<div id="goods_content">

</div>
</div>
<script langauge="javascript">
    $('#goods_content').load('/goods/index');
    
</script>