import qq from 'fine-uploader';

require('fine-uploader/fine-uploader/fine-uploader-gallery.css');
require('@fortawesome/fontawesome-free');

qq.uiPrivateApi._isEditFilenameEnabled = function(){
    return this._templating.isEditFilenamePossible() && qq.FilenameClickHandler && qq.FilenameInputFocusHandler && qq.FilenameInputFocusHandler;
};

window.addEventListener('load', () => {
    let fineUploaders = [];

    document.querySelectorAll('div[fine-uploader="true"]').forEach((element, index) => {

        fineUploaders[index] = new qq.FineUploader({
                // debug: true,
                element: element,
                request: {
                    endpoint: element.dataset.requestEndpoint,
                    paramsInBody: false
                },
                template: 'donjohn-media',
                chunking: {
                    enabled: true,
                    partSize: parseInt(element.dataset.chunkingPartsize, 10)
                },
                retry: {
                    enableAuto: true
                },
                deleteFile: {
                    enabled: true,
                    endpoint: element.dataset.delete_fileEndpoint
                },
                callbacks: {
                    onSubmitDelete: function(id)  {
                        this.setDeleteFileParams({filename: this.getName(id)}, id);
                    }
                },
                thumbnails: {
                    placeholders: {
                        notAvailablePath: element.dataset.thumbnailsPlaceholdersNotAvailablepath,
                        waitingPath: element.dataset.thumbnailsPlaceholdersWaitingpath,
                        waitUntilResponse: true
                    }
                },
                multiple: element.dataset.multiple,
                session: {
                    endpoint: element.dataset.sessionEndpoint
                }
        });
    });
}, true);
