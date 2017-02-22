/**
 * Created by jgn on 20/02/2017.
 */
$(function () {

    var switchInputFileToInputText = function (_this){
        var dropzone = $(_this).closest('[data-dropzone="on"]');
        var inputText = $(dropzone).find('input[type="text"]');
        if (!$(inputText).length) {
            var inputFile = $(dropzone).find('input[type="file"]');
            inputText = $(document.createElement('input')).attr('type','text')
                                                .attr('id',$(inputFile).attr('id'))
                                                .attr('name',$(inputFile).attr('name'))
                                                .attr('class',$(inputFile).attr('class'));
            inputFile.attr('id','').attr('name','');
            $(inputFile).append(inputText);
        }
        return inputText;
    }

    var switchInputTextToInputFile = function (_this){
        var dropzone = $(_this).closest('[data-dropzone="on"]');
        var inputText = $(dropzone).find('input[type="text"]');
        if ($(inputText).length) {
            $(dropzone).find('input[type="file"]').attr('id',$(inputText).attr('id'))
                                                    .attr('name',$(inputText).attr('name'));
            $(inputText).remove();
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
            if (target) $(target).val(reader.result);

        }, false);
        reader.readAsDataURL(file);
    };

    //on vire le bloc empty de easyadmin
    $('[data-dropzone="on"] > .empty').remove();

    $('[data-dropzone="on"] > span.message').on(
        'click ontouchstart',
        function(e) {
            var _this = this;
            switchInputTextToInputFile(_this);
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
            var inputText = switchInputFileToInputText(_this);
            files =  e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer.files : $(this).prop('files');
            if(files.length) {
                $(files).each(function(){
                    processFile(this, _this, $(inputText));
                })

            }
        }
    );

});