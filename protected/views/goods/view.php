<style>
    .listview_a{
        margin:10px;
    }
    .listview_a li {
        list-style: none;
        display:inline-block;      
        width:100px;
        white-space: normal;
        vertical-align: top;
        margin:3px 0 0 3px;        
        text-align: center;
    }
    .listview_a{
        height:auto;
        max-height:300px;
        overflow:auto;
        width:95%; 
        white-space: nowrap;
    }
</style>

<h3>Goods</h3>
<?php foreach ($goods as $itype): ?>
    <h4><?= $itype['name']; ?></h4>
    <ul class="listview_a">
        <? CFiletypes::ParsePrint($itype['items'], $itype['itemtype']);  ?>
    </ul>
    <div class="clearfix"></div>
<?php endforeach; ?>
