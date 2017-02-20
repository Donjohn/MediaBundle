/**
 * Created by jgn on 20/02/2017.
 */
$(function () {

    $('[data-dropzone="on"] input[type="text"]').css('visibility','hidden');

    var processFile = function (file, element) {
        var reader  = new FileReader();
        reader.addEventListener("load", function () {
            var dropzoneId= $(element).closest('[data-dropzone="on"]').attr('id');
            var img = $('#'+dropzoneId+' img');
            if( img.length ) {
                img.attr('src', reader.result).attr('height', '120px');
            } else {
                $('#'+dropzoneId+' span.media_info').html(file.name);
            }
            $('#'+dropzoneId+' input[type="text"]').val(reader.result);

        }, false);
        reader.readAsDataURL(file);
    };


    $('[data-dropzone="on"]').on(
        'click ontouchstart',
        function(e) {
            var _this = this;
            $('#'+ $(this).attr('id').replace('dropzone','hiddenFile')).click()
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
        'drop change',
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