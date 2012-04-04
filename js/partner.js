var imgObject;
function addToCloud(img, pid, oid, vid)
{
	imgObject = img;
	$.post("http://tushkan/products/Addtoqueue", {partner_id: pid, original_id: oid, original_variant_id: vid}, function(){
		img = $(imgObject);
		img.attr("src", img.attr("src") + "/flg/1");
	});
}