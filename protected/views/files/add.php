<form id="javascript-upload" action="http://up.mycloud.anka.ws:82/files/uploads/" enctype="multipart/form-data" method="post">
    <label for="jfile">File Upload:
	<input id="jfile" name="file" type="file" />
    </label>
    <input type="submit" value="Upload File" />
</form>
<div style="border: 1px solid black; width: 300px;">
    <div id="status" style="background-color: #D3DCE3; width: 0px; height: 12px; margin: 1px;"></div>
</div>
<div>
    <span id="received"> </span>
    <span id="speed"> </span>
</div>
