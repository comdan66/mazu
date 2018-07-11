CKEDITOR.plugins.add( 'dropler', {
    init: function( editor ) {
        backends = {
            basic: {
                upload: uploadBasic,
                required: ['uploadUrl'],
                init: function() {}
            }
        };

        var checkRequirement = function(condition, message) {
            if (!condition)
                throw Error("Assert failed" + (typeof message !== "undefined" ? ": " + message : ""));
        };

        function validateConfig() {
            var errorTemplate = 'DragDropUpload Error: ->';
            checkRequirement(
                editor.config.hasOwnProperty('droplerConfig'),
                errorTemplate + "Missing required droplerConfig in CKEDITOR.config.js"
            );

            var backend = backends[editor.config.droplerConfig.backend];

            var suppliedKeys = Object.keys(editor.config.droplerConfig.settings);
            var requiredKeys = backend.required;

            var missing = requiredKeys.filter(function(key) {
                return suppliedKeys.indexOf(key) < 0;
            });

            if (missing.length > 0) {
                throw 'Invalid Config: Missing required keys: ' + missing.join(', ');
            }
        }

        validateConfig();

        var backend = backends[editor.config.droplerConfig.backend];
        backend.init();

        function doNothing(e) { }
        function orPopError(err) { alert(err.data.error); }

        function dropHandler(e) {
            e.preventDefault();
            var file = e.dataTransfer.files[0];
            backend.upload(file).then(insertImage, orPopError);
        }

        function setWidthAndHeight() {
            if (this.width > 0) {
                imageElement.setAttribute('width', this.width);
            }
            if (this.height > 0) {
                imageElement.setAttribute('height', this.height);
            }
            return true;
        }

        function insertImage(res) {
            
            var elem = editor.document.createElement('img', {
                attributes: {
                    alt: "",
                    src: res.url
                }
            });

            var tempImage = new Image();
            tempImage.src = res.url;
            tempImage.onload = function () {
                if (!(this.width > 0 && this.height > 0)) return ;
                elem.setAttribute('style', 'width: ' + (this.width / 2) + 'px; height: ' + (this.height / 2) + 'px;');
                return true;
            };

            editor.insertElement(elem);
        }

        function addHeaders(xhttp, headers) {
            for (var key in headers) {
                if (headers.hasOwnProperty(key)) {
                    xhttp.setRequestHeader(key, headers[key]);
                }
            }
        }

        function post(url, data, headers) {
            return new Promise(function(resolve, reject) {
                var xhttp    = new XMLHttpRequest();
                xhttp.open('POST', url);
                addHeaders(xhttp, headers);
                xhttp.onreadystatechange = function () {
                    if (xhttp.readyState === 4) {
                        if (xhttp.status === 200) {
                            resolve(JSON.parse(xhttp.responseText));
                        } else {
                            reject(JSON.parse(xhttp.responseText));
                        }
                    }
                };
                var formData = new FormData ();
                formData.append ("upload", data);
                xhttp.send(formData);
            });
        }

        function uploadBasic(file) {
            var settings = editor.config.droplerConfig.settings;
            return post(settings.uploadUrl, file, settings.headers);
        }


        CKEDITOR.on('instanceReady', function() {
            var iframeBase = document.querySelector('iframe').contentDocument.querySelector('html');
            var iframeBody = iframeBase.querySelector('body');

            iframeBody.ondragover = doNothing;
            iframeBody.ondrop = dropHandler;

            paddingToCenterBody = ((iframeBase.offsetWidth - iframeBody.offsetWidth) / 2) + 'px';
            iframeBase.style.height = '100%';
            iframeBase.style.width = '100%';
            iframeBase.style.overflowX = 'hidden';

            iframeBody.style.height = '100%';
            iframeBody.style.margin = '0';
            iframeBody.style.paddingLeft = paddingToCenterBody;
            iframeBody.style.paddingRight = paddingToCenterBody;
        });
    }
});
