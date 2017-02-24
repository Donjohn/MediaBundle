/**
 * Created by jgn on 20/02/2017.
 */
$(function () {

    var switchInputFileToInputText = function (formGroup){
        var inputText = $(formGroup).find('input[type="text"]');
        if (!$(inputText).length) {
            var inputFile = $(formGroup).find('input[type="file"]');
            inputText = $(document.createElement('input')).attr('type','text')
                                                .attr('id',$(inputFile).attr('id'))
                                                .attr('name',$(inputFile).attr('name'))
                                                .attr('class','hidden');
            $(inputFile).attr('id','').attr('name','');
            $(inputText).appendTo($(formGroup));
        }
        return inputText;
    }

    var switchInputTextToInputFile = function (formGroup){
        var inputText = $(formGroup).find('input[type="text"]');
        if ($(inputText).length) {
            $(formGroup).find('input[type="file"]').attr('id',$(inputText).attr('id'))
                                                    .attr('name',$(inputText).attr('name'));
            $(inputText).remove();
        }
    }

    var createOrGetFormGroup = function(dropzone){
        var prototype = $(dropzone).find('[data-prototype]');
        if ($(prototype).length) {
            //si multi
            var formGroup = $(prototype).data('prototype')
            var total = $(dropzone).find('.form-group').length;
            formGroup = $.parseHTML( formGroup.replace(/__name__/g, ++total) );
            $(formGroup).attr('data-provider',$(dropzone).data('provider'));
            $(formGroup).attr('data-thumbnail-height',$(dropzone).data('thumbnail-height'));
            $(formGroup).attr('data-multi','on');
            $(formGroup).appendTo($(prototype));
            return $(formGroup);
        } else {
            return $(dropzone);
        }

    }

    var dropzone_image = function(reader, formGroup){
        return $(document.createElement('img')).attr('src', reader.result).attr('height', $(formGroup).data('thumbnail-height')).addClass('img-rounded visible-xs-inline-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block');
    }

    var dropzone_file = function(file, formGroup){
        return file.name;
    }

    var processFile = function (file, formGroup) {
        var reader  = new FileReader();
        reader.addEventListener("load", function () {

            if ($(formGroup).data('multi')=='on') {
                console.log($(formGroup).data('provider')=='image' ?  dropzone_image(reader, formGroup) : dropzone_file(file, formGroup));
                $(formGroup).find('span.media-info').append(
                    $(formGroup).data('provider')=='image' ?  dropzone_image(reader, formGroup) : dropzone_file(file, formGroup)
                );
            } else {
                $(formGroup).find('span.media-info').html(
                    $(formGroup).data('provider')=='image' ?  dropzone_image(reader, formGroup) : dropzone_file(file, formGroup)
                );
            }
            var inputText = $(formGroup).find('input[type="text"]');
            if ($(inputText).length) $(inputText).val(reader.result);

        }, false);
        reader.readAsDataURL(file);
    };

    //on vire le bloc empty de easyadmin
    $('[data-dropzone="on"] > .empty').remove();

    $('[data-dropzone="on"] > span.message')
    .on(
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
    );

    $('[data-dropzone="on"] > span.message').on(
        'click ontouchstart',
        function(e) {
            var _this = this;
            var formGroup = createOrGetFormGroup($(this).closest('[data-dropzone="on"]'));
            switchInputTextToInputFile(formGroup);
            $(formGroup).find('input[type="file"]').click()
            .on(
                'change',
                function(e){
                    e.preventDefault();
                    e.stopPropagation();
                    files = $(this).prop('files');
                    if(files.length) {
                        $(files).each(function(){
                            processFile(this, formGroup);
                        });
                    }
                }
            );
        }
    ).on(
        'drop',
        function(e){
            e.preventDefault();
            e.stopPropagation();
            var _this = this;
            var formGroup = createOrGetFormGroup($(this).closest('[data-dropzone="on"]'));
            var inputText = switchInputFileToInputText(formGroup);
            files =  e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer.files : $(this).prop('files');
            if(files.length) {
                $(files).each(function(){
                    processFile(this, formGroup);
                });
            }
        }
    );
    
    


});