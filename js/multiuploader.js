/** Basic upload manager for single or multiple files (Safari 4 Compatible)
 * @author  Andrea Giammarchi
 * @blog    WebReflection [webreflection.blogspot.com]
 * @license Mit Style License
 */

var sendFile = 2 * 1024*1024*1024; // maximum allowed file size
// should be smaller or equal to the size accepted in the server for each file

// function to upload a single file via handler
sendFile = (function(toString, maxSize){
	var isFunction = function(Function){
		return  toString.call(Function) === "[object Function]";
	},
	split = "onabort.onerror.onloadstart.onprogress".split("."),
	length = split.length;
	return  function(handler){
		fileSize =handler.file.fileSize;
		if(fileSize ==undefined){
			fileSize = handler.file.size;
		}
		if(maxSize && maxSize < fileSize){
			if(isFunction(handler.onerror))
				handler.onerror();
			return;
		};
		var xhr = new XMLHttpRequest,
		upload = xhr.upload;
		for(var
			xhr = new XMLHttpRequest,
			upload = xhr.upload,
			i = 0;
			i < length;
			i++
			)
		upload[split[i]] = (function(event){
			return  function(rpe){
				if(isFunction(handler[event]))
					handler[event].call(handler, rpe, xhr);
			};
		})(split[i]);
		upload.onload = function(rpe){
			if(handler.onreadystatechange === false){
				if(isFunction(handler.onload))
					handler.onload(rpe, xhr);
			} else {
				setTimeout(function(){
					if(xhr.readyState === 4){
						if(isFunction(handler.onload))
							handler.onload(rpe, xhr);
					} else
						setTimeout(arguments.callee, 15);
				}, 15);
			}
		};
		//	Access-Control-Request-Headers:Origin, X-Requested-With, Content-Disposition, X-File-Name, Content-Type
		filename = handler.file.fileName;
		if (filename == undefined) filename=handler.file.name;
	
		xhr.open("post", handler.url, true);
		xhr.setRequestHeader("If-Modified-Since", "Mon, 26 Jul 1997 05:00:00 GMT");
		//xhr.setRequestHeader("Origin", "http://mycloud.anka.ws");
		xhr.setRequestHeader("Cache-Control", "no-cache");
		xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
		xhr.setRequestHeader("X-File-Name", filename);
		xhr.setRequestHeader("X-File-Size", fileSize);
		xhr.setRequestHeader("Content-Type", "application/octet-stream");
		xhr.setRequestHeader("Content-Disposition",'attachment,filename="'+filename+'"');
		xhr.send(handler.file);
		return  handler;
	};
})(Object.prototype.toString, sendFile);

// function to upload multiple files via handler
function sendMultipleFiles(handler){
	var length = handler.files.length,
	i = 0,
	onload = handler.onload;
	handler.current = 0;
	handler.total = 0;
	handler.sent = 0;
	//handler.rtexts = new Array();
	
		
		
	while(handler.current < length){
		fileSize =handler.files[handler.current].fileSize;
		if(fileSize ==undefined){
			fileSize = handler.files[handler.current].size;
		}
		console.log(fileSize);
		handler.total += fileSize;
		handler.current++;
	}
	handler.current =0;
	if(length){
		handler.file = handler.files[handler.current];
		sendFile(handler).onload = function(rpe, xhr){
			if(++handler.current < length){
				handler.sent += handler.files[handler.current - 1].fileSize;
				//handler.rtexts.push(xhr.responseText);
				if(onload) {
					handler.onload = onload;
					handler.onload(rpe, xhr);
				}
				handler.file = handler.files[handler.current];
				sendFile(handler).onload = arguments.callee;
			}
			else
			{
				//handler.rtexts.push(xhr.responseText);
				if(onload) {
					handler.onload = onload;
					handler.onload(rpe, xhr);
				}
			}
		};
	};
	return  handler;
};