/**
 * Created by jgn on 20/02/2017.
 */
$(function () {

    var processFile = function (file, element) {
        var reader  = new FileReader();
        reader.addEventListener("load", function () {
            var dropzone = $(element).closest('[data-dropzone="on"]');
            var dropzoneId= dropzone.attr('id');
            //TODO ici en fonction du provider, charger un template different...
            if ($(dropzone).data('provider')=='image') {
                $('#'+dropzoneId+' img').attr('src', reader.result).attr('height', '200px');
            } else if ($(dropzone).data('provider')=='file') {
                $('#'+dropzoneId+' span.media_info').html(file.name);
            }
            $('#'+dropzoneId+' input[type="text"]').val(reader.result);

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