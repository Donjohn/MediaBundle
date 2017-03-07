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
    };

    var switchInputTextToInputFile = function (formGroup){
        var inputText = $(formGroup).find('input[type="text"]');
        if ($(inputText).length) {
            $(formGroup).find('input[type="file"]').attr('id',$(inputText).attr('id'))
                                                    .attr('name',$(inputText).attr('name'));
            $(inputText).remove();
        }
    };

    var createOrGetFormGroup = function(dropzone){
        var prototype = $(dropzone).find('[data-prototype]');
        if ($(prototype).length) {
            var total = $(dropzone).find('.form-group[data-provider]').length;
            var formGroup = $.parseHTML( $(prototype).data('prototype').replace(/__name__/g, ++total) );
            $(formGroup).attr('data-provider',$(dropzone).data('provider'))
                        .attr('data-thumbnail-height',$(dropzone).data('thumbnail-height'))
                        .attr('data-multi','on')
                        .appendTo($(prototype));
            return $(formGroup);
        } else {
            return $(dropzone);
        }

    };

    var dropzone_image = function(reader, formGroup){
        return $(document.createElement('img')).attr('src', reader.result).attr('height', $(formGroup).data('thumbnail-height')).addClass('img-rounded visible-xs-inline-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block');
    };

    var dropzone_file = function(file, formGroup){
        return file.name;
    };

    var processFile = function (file, formGroup) {
        var reader  = new FileReader();
        reader.addEventListener("load", function () {
            if (reader.result) {
                if ($(formGroup).data('multi')=='on') {
                    $(formGroup).find('span.media-info').append(
                        $(formGroup).data('provider')=='image' ?  dropzone_image(reader, formGroup) : dropzone_file(file, formGroup)
                    );
                } else {
                    $(formGroup).find('span.media-info').html(
                        $(formGroup).data('provider')=='image' ?  dropzone_image(reader, formGroup) : dropzone_file(file, formGroup)
                    );
                }
                var inputText = $(formGroup).find('input[type="text"]');
                $(formGroup).find('[id$="originalFilename"]').val(file.name);
                if ($(inputText).length) $(inputText).val(reader.result);
            }

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
    )
    .on(
        'click ontouchstart',
        function() {
            var _this = this;
            var formGroup = createOrGetFormGroup($(this).closest('[data-dropzone="on"]'));
            switchInputTextToInputFile(formGroup);
            $(formGroup).addClass('hidden');
            $(formGroup).find('input[type="file"]').click()
            .on(
                'change',
                function(e){
                    $(formGroup).removeClass('hidden');
                    e.preventDefault();
                    e.stopPropagation();
                    files = $(this).prop('files');
                    if(files.length) {
                        $(files).each(function(){
                            if (!formGroup) {
                                formGroup = createOrGetFormGroup($(_this).closest('[data-dropzone="on"]'));
                            }
                            processFile(this, formGroup);
                            formGroup = false; //on unset le formgroup pour forcer sa re-creation au file suivant
                        });
                    } else {
                        $(formGroup).remove();
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
            files =  e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer.files : $(this).prop('files');
            if(files.length) {
                $(files).each(function(){
                    if (this.name!='') {
                        var formGroup = createOrGetFormGroup($(_this).closest('[data-dropzone="on"]'));
                        switchInputFileToInputText(formGroup);
                        processFile(this, formGroup);
                    }
                });
            }
        }
    );
    

});