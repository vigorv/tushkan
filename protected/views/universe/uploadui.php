<style>
#filelist {
    margin-top: 15px;
}

#uploadFilesButtonContainer, #selectFilesButtonContainer, #overallProgress {
    display: inline-block;
}

#overallProgress {
    float: right;
}
</style>

<div id="uploaderContainer">
    <div id="selectFilesButtonContainer">
    </div>
    <div id="uploadFilesButtonContainer">
      <button type="button" id="uploadFilesButton"
              class="yui3-button" style="width:250px; height:35px;">Upload Files</button>
    </div>
    <div id="overallProgress">
    </div>
</div>

<div id="filelist">
  <table id="filenames">
    <thead>
       <tr><th>File name</th><th>File size</th><th>Percent uploaded</th></tr>
       <tr id="nofiles">
        <td colspan="3">
            No files have been selected.
        </td>
       </tr>
    </thead>
    <tbody>
    </tbody>
  </table>
</div>

<script>

YUI({filter:"raw"}).use("uploader", function(Y) {
Y.one("#overallProgress").set("text", "Uploader type: " + Y.Uploader.TYPE);
   if (Y.Uploader.TYPE != "none" && !Y.UA.ios) {
       var uploader = new Y.Uploader({width: "250px",
                                      height: "35px",
                                      multipleFiles: true,
                                      swfURL: "http://yui.yahooapis.com/3.5.1/build/uploader/assets/flashuploader.swf?t=" + Math.random(),
                                      uploadURL: "/universe/uploaduido",
                                      simLimit: 2
                                     });
       var uploadDone = false;

       uploader.render("#selectFilesButtonContainer");

       uploader.after("fileselect", function (event) {

          var fileList = event.fileList;
          var fileTable = Y.one("#filenames tbody");
          if (fileList.length > 0 && Y.one("#nofiles")) {
            Y.one("#nofiles").remove();
          }

          if (uploadDone) {
            uploadDone = false;
            fileTable.setHTML("");
          }

          var perFileVars = {};
          Y.each(fileList, function (fileInstance) {
				fileTable.append("<tr id='" + fileInstance.get("id") + "_row" + "'>" +
                                    "<td class='filename'>" + fileInstance.get("name") + "</td>" +
                                    "<td class='filesize'>" + fileInstance.get("size") + "</td>" +
                                    "<td class='percentdone'>Hasn't started yet</td>" +
                                    "<td class='serverdata'></td>");
				perFileVars[fileInstance.get("id")] = {key: "111"};
			});

			uploader.set("postVarsPerFile", Y.merge(uploader.get("postVarsPerFile"), perFileVars));
		});

       uploader.on("uploadprogress", function (event) {
            var fileRow = Y.one("#" + event.file.get("id") + "_row");
                fileRow.one(".percentdone").set("text", event.percentLoaded + "%");
       });

       uploader.on("uploadstart", function (event) {
            uploader.set("enabled", false);
            Y.one("#uploadFilesButton").addClass("yui3-button-disabled");
            Y.one("#uploadFilesButton").detach("click");
       });

       uploader.on("uploadcomplete", function (event) {
            var fileRow = Y.one("#" + event.file.get("id") + "_row");
                fileRow.one(".percentdone").set("text", "Finished!");
                fileRow.one(".serverdata").setHTML(event.data);
       });

       uploader.on("totaluploadprogress", function (event) {
                Y.one("#overallProgress").setHTML("Total uploaded: <strong>" + event.percentLoaded + "%" + "</strong>");
       });

       uploader.on("alluploadscomplete", function (event) {
                     uploader.set("enabled", true);
                     uploader.set("fileList", []);
                     Y.one("#uploadFilesButton").removeClass("yui3-button-disabled");
                     Y.one("#uploadFilesButton").on("click", function () {
                          if (!uploadDone && uploader.get("fileList").length > 0) {
                             uploader.uploadAll();
                          }
                     });
                     Y.one("#overallProgress").set("text", "Uploads complete!");
                     uploadDone = true;
       });

       Y.one("#uploadFilesButton").on("click", function () {
         if (!uploadDone && uploader.get("fileList").length > 0) {
            uploader.uploadAll();
         }
       });
   }
   else {
       Y.one("#uploaderContainer").set("text", "We are sorry, but the uploader technology is not supported" +
                                               " on this platform.");
   }


});

</script>