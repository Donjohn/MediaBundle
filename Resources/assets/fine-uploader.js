import qq from 'fine-uploader';

require('fine-uploader/fine-uploader/fine-uploader-gallery.css');

qq.uiPrivateApi._isEditFilenameEnabled = function () {
    return this._templating.isEditFilenamePossible() && qq.FilenameClickHandler && qq.FilenameInputFocusHandler && qq.FilenameInputFocusHandler;
};


// Create an observer instance linked to the callback function
let observer = new MutationObserver((mutationList) => {
    mutationList.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
            if (!node.parentElement) {
                return;
            }
            initFineUploader(node.parentElement);
        });
    });
});

// Start observing the target node for configured mutations
observer.observe(document, { attributes: false, childList: true, subtree: true });





const initFineUploader = elementDOM => {
    elementDOM.querySelectorAll('div[data-fine-uploader="true"]').forEach((element) => {
        if (element.dataset.id && !document.querySelector('#'+element.dataset.id)) {
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
                dragAndDrop: {
                    extraDropzones: eval(element.dataset.extra_dropzones)
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
        }
    });
};

initFineUploader(document);

