var imgObject;
var cloudUrl = "http://mycloud.anka.ws";
function addToCloud(img, pid, oid, vid)
{
	imgObject = img;

	$.get(cloudUrl + "/products/addtoqueue", {partner_id: pid, original_id: oid, original_variant_id: vid}, function(answer){
		//id = parseInt(answer);
		//if (id > 0)
//		{
	//		location.href = cloudUrl + "/universe/tview/" + id;
		//}
		//alert(answer);
		//img = $(imgObject).firstChild();
		//img.attr("src", img.attr("src") + "/flg/1");
	});
	return false;
}