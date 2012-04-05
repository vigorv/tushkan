var imgObject;
var cloudUrl = "http://mycloud.anka.ws";
var iFrameResult;
function addToCloud(img, pid, oid, vid)
{
	imgObject = img;

	iFrameResult = "";
	var f = document.getElementById(img.id + "frame");
    f.contentWindow.document.body.innerHTML = '';

	f.src = cloudUrl + "/products/addtoqueue/partner_id/" + pid + "/original_id/" + oid + "/original_variant_id/" + vid;
	cloudFrameResult();

//	$.get(cloudUrl + "/products/addtoqueue", {partner_id: pid, original_id: oid, original_variant_id: vid}, function(answer){
		//id = parseInt(answer);
		//if (id > 0)
//		{
	//		location.href = cloudUrl + "/universe/tview/" + id;
		//}
		//alert(answer);
		//img = $(imgObject).firstChild();
		//img.attr("src", img.attr("src") + "/flg/1");
//	});
	return false;
}

function cloudFrameResult(id)
{
	var f = document.getElementById(id + "frame");
	var iFrameResult = f.contentWindow.document.body.innerHTML;
	if (iFrameResult)
	{
		alert(iFrameResult);
		return;
	}
	window.setTimeout("cloudFrameResult('" + id + "')", 500);
}