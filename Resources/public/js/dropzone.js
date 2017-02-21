/**
 * Created by jgn on 20/02/2017.
 */
$(function () {

    var processFile = function (file, element) {
        var reader  = new FileReader();
        reader.addEventListener("load", function () {
            var dropzone = $(element).closest('[data-dropzone="on"]');
            var dropzoneId= dropzone.attr('id');

            if ($(dropzone).data('provider')=='image') {
                var img = $(document.createElement('img')).attr('src', reader.result).attr('height', $(dropzone).data('thumbnail-height')).addClass('img-rounded visible-xs-inline-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block');
                $('#'+dropzoneId+' span.media-info').html(img);
                console.log($(img));
            } else {
                $('#'+dropzoneId+' span.media-info').html(file.name);
            }
            $('#'+dropzoneId+' textarea').val(reader.result);

        }, false);
        reader.readAsDataURL(file);
    };

    //on vire le bloc empty de easyadmin
    $('[data-dropzone="on"] > .empty').remove();

    $('[data-dropzone="on"] > span').on(
        'click ontouchstart',
        function(e) {
            var _this = this;
            $('#'+ $(this).parent().attr('id').replace('dropzone','hiddenFile')).click()
            .on(
                'change',
                function(e){
                    e.preventDefault();
                    e.stopPropagation();
                    files = $(this).prop('files');
                    if(files.length) {
                        $(files).each(function(){
                            processFile(this, _this);
                        })

                    }
                }
            );
        }
    ).on(
        'dragover dragenter',
        function(e) {
            $(this).addClass('hover');
            e.preventDefault();
            e.stopPropagation();
        }
    ).on(
        'dragleave dragexit',
        function(e) {
            $(this).removeClass('hover');
            e.preventDefault();
            e.stopPropagation();
        }
    ).on(
        'drop',
        function(e){
            e.preventDefault();
            e.stopPropagation();
            var _this = this;
            files =  e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer.files : $(this).prop('files');
            if(files.length) {
                $(files).each(function(){
                    processFile(this, _this);
                })

            }
        }
    );

});