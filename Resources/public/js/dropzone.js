/**
 * Created by jgn on 20/02/2017.
 */
$(function () {

    var switchInputFileToTextearea = function (_this){
        var dropzone = $(_this).closest('[data-dropzone="on"]');
        var textarea = $(dropzone).find('textarea');
        if (!$(textarea).length) {
            var inputFile = $(dropzone).find('input[type="file"]');
            textearea = $(document.createElement('textarea')).attr('id',$(inputFile).attr('id'))
                                                .attr('name',$(inputFile).attr('name'))
                                                .attr('class',$(inputFile).attr('class'));
            inputFile.attr('id','').attr('name','');
            $(dropzone).find('input[type="file"]').parent().append(textearea);
        }
    }

    var switchTexteareaToInputFile = function (_this){
        var dropzone = $(_this).closest('[data-dropzone="on"]');
        var textarea = $(dropzone).find('textarea');
        if ($(textarea).length) {
            $(dropzone).find('input[type="file"]').attr('id',$(textearea).attr('id'))
                                                    .attr('name',$(textearea).attr('name'));
            $(textarea).remove();
        }
}

    var processFile = function (file, element, target) {
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
            if (target) $(target).html(reader.result);

        }, false);
        reader.readAsDataURL(file);
    };

    //on vire le bloc empty de easyadmin
    $('[data-dropzone="on"] > .empty').remove();

    $('[data-dropzone="on"] > span').on(
        'click ontouchstart',
        function(e) {
            var _this = this;
            switchTexteareaToInputFile(_this);
            $('[data-dropzone="on"] input[type="file"]').click()
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
            switchInputFileToTextearea(_this);
            files =  e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer.files : $(this).prop('files');
            if(files.length) {
                $(files).each(function(){
                    processFile(this, _this, $(textearea));
                })

            }
        }
    );

});