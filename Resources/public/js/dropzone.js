/**
 * Created by jgn on 20/02/2017.
 */
$(function () {

    $('[data-dropzone="on"] input[type="text"]').css('visibility','hidden');

    var processFile = function (file, element) {
        var reader  = new FileReader();
        reader.addEventListener("load", function () {
            var dropzone = $(element).closest('[data-dropzone="on"]');
            var img = $('#'+dropzone.attr(id)+' img');
            if( img.length ) {
                img.attr('src', reader.result).attr('height', '120px');
            } else {
                $('#'+dropzone.attr(id)+' span.media-info').html(file.name);
            }
            alert(dropzone.attr(id));
            $('#'+dropzone.attr(id)+' input[type="text"]').val(reader.result);

        }, false);
        reader.readAsDataURL(file);
    };




    $('[data-dropzone="on"]').on(
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
            files =  e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer.files : $(this).prop('files');
            var _this = this;
            if(files.length) {
                $(files).each(function(){
                    processFile(this, _this);
                })

            }
        }
    );

});