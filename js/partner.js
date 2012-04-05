var imgObject;
var cloudUrl = "http://mycloud.anka.ws";
var iFrameResult;
function addToCloud(img, pid, oid, vid)
{
	imgObject = img;

	iFrameResult = "";
	f = $(".cloudframe");
	f.html("");
	f.attr("src", cloudUrl + "/products/addtoqueue/partner_id/" + pid + "/original_id/" + oid + "/original_variant_id/" + vid);

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

function cloudFrameResult()
{
	f = $(".cloudframe");
	iFrameResult = f.html();
	if (iFrameResult)
	{
		alert(iFrameResult);
		return;
	}
	window.setTimeOut("cloudFrameResult()", 500);
}