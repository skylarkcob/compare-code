jQuery(document).ready(function ($) {
    (function () {
        var inputFile = $("#IDInputFile");

        var Upload = function (file) {
            this.file = file;
        };

        Upload.prototype.getType = function () {
            return this.file.type;
        };

        Upload.prototype.getSize = function () {
            return this.file.size;
        };

        Upload.prototype.getName = function () {
            return this.file.name;
        };

        Upload.prototype.doUpload = function () {
            var that = this;
            var formData = new FormData();

            formData.append("file", this.file);
            formData.append("upload_file", true);
            formData.append("accept", inputFile.attr("accept"));

            $.ajax({
                type: "POST",
                url: localizedObject.ajaxUrl + "?action=wordpress_ajax_action",
                xhr: function () {
                    var myXhr = $.ajaxSettings.xhr();

                    if (myXhr.upload) {
                        myXhr.upload.addEventListener("progress", that.progressHandling, false);
                    }

                    return myXhr;
                },
                success: function (response) {
                    console.log(response);
                },
                error: function (error) {
                    console.log(error);
                },
                async: true,
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                timeout: 60000
            });
        };

        Upload.prototype.progressHandling = function (event) {
            var percent = 0;
            var position = event.loaded || event.position;
            var total = event.total;

            if (event.lengthComputable) {
                percent = Math.ceil(position / total * 100);
            }

            console.log(percent);
        };

        inputFile.on("change", function () {
            if (this.files && this.files.length) {
                var files = this.files,
                    i = 0,
                    count = files.length;

                for (i; i < count; i++) {
                    var file = files[i];

                    var upload = new Upload(file);

                    upload.doUpload();
                }
            }
        });
    })();
});