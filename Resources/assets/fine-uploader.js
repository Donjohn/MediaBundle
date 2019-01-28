import qq from 'fine-uploader';

require('fine-uploader/fine-uploader/fine-uploader-gallery.css');
require('@fortawesome/fontawesome-free');

qq.uiPrivateApi._isEditFilenameEnabled = function () {
    return this._templating.isEditFilenamePossible() && qq.FilenameClickHandler && qq.FilenameInputFocusHandler && qq.FilenameInputFocusHandler;
};

window.addEventListener('load', () => {
    document.querySelectorAll('div[data-fine-uploader="true"]').forEach((element) => {
        new qq.FineUploader({
            // debug: true,
            element: element,
            request: {
                endpoint: element.dataset.request_endpoint,
                paramsInBody: false
            },
            chunking: {
                enabled: true,
                partSize: parseInt(element.dataset.chunking_partsize, 10)
            },
            retry: {
                enableAuto: true
            },
            deleteFile: {
                enabled: true,
                endpoint: element.dataset.deletefile_endpoint,
                method: 'POST'
            },
            callbacks: {
                onSubmitDelete: function (id) {
                    this.setDeleteFileParams({filename: this.getName(id)}, id);
                }
            },
            thumbnails: {
                placeholders: {
                    notAvailablePath: element.dataset.thumbnails_placeholders_notavailablepath,
                    waitingPath: element.dataset.thumbnails_placeholders_waitingpath,
                    waitUntilResponse: true
                }
            },
            multiple: element.dataset.multiple,
            session: {
                endpoint: element.dataset.session_endpoint
            },
            template: element.dataset.template,
            validation: {
                acceptFiles: element.dataset.validation_accept_files,
                allowedExtensions: eval(element.dataset.validation_allowed_extensions)
            },
        });
    });
}, true);
