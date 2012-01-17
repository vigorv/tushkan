<div style="  padding:1%;     background-color: #09f">
    <form id="goods_ext"  methos="GET" action="/universe/ext">
        <input type="text" name="goods_add" value="" placeholder="Insert link, or search something" style="width:80%"/>
        <input type="submit"  value="GO" style="width:8%;"/>
    </form>
</div>
<div id="goods_ext_result">
</div>
<div id="goods_content">

</div>
<script langauge="javascript">
    $('#goods_content').load('/goods/index');
    
</script>