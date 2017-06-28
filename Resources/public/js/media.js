/**
 * Created by jgn on 20/02/2017.
 */
$(function () {

    var readerLoading = false;

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

    var createOrGetFormGroup = function(mediazone){
        var prototype = $(mediazone).find('[data-prototype]');
        if ($(prototype).length) {
            var total = $(mediazone).find('[data-provider]').length;
            var formGroup = $.parseHTML( $(prototype).data('prototype').replace(/__name__/g, ++total) );
            $(formGroup).attr('data-provider',$(mediazone).data('provider'))
                        .attr('data-thumbnail-height',$(mediazone).data('thumbnail-height'))
                        .attr('data-multi','on')
                        .appendTo($(prototype));
            return $(formGroup);
        } else {
            return $(mediazone);
        }

    };

    var media_image = function(file, reader, formGroup){
        return $(document.createElement('img')).attr('src', reader.result).attr('height', $(formGroup).data('thumbnail-height')).addClass('img-rounded visible-xs-inline-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block');
    };

    var media_file = function(file, reader, formGroup){
        return file.name;
    };



    var processFile = function (file, formGroup) {

        var reader  = new FileReader();

        reader.addEventListener('loadstart', function(){
            $(formGroup).find('input[id$="originalFilename"]').val(file.name);
            $(formGroup).find('span.media-info').html(
                    media_file(file, reader, formGroup)
                );
            if ($(formGroup).find('input[type="text"]').length) readerLoading = true;
        });
        reader.addEventListener("loadend", function () {



            if (reader.result) {
                $(formGroup).find('input[id$="originalFilename"]').val(file.name);
                $(formGroup).find('span.media-info').html(
                    eval('media_'+$(formGroup).data('provider')+'(file, reader, formGroup)')
                );
                var inputText = $(formGroup).find('input[type="text"]');
                if ($(inputText).length) {
                    $(inputText).val(reader.result);
                }

            } else if ($(formGroup).find('input[type="text"]').length && $(formGroup).data('multi')) {

                $(formGroup).html($($(formGroup).find('span.media-info').html('error '+file.name+' too big to be dropped').addClass('text-warning')));
            }
            readerLoading =  false;

        }, false);


        reader.readAsDataURL(file);
    };

    //on vire le bloc empty de easyadmin
    $('[data-mediazone="on"] > .empty').remove();

    $('[data-mediazone="on"]').closest('form').submit(function (evt) {
            if (readerLoading) evt.preventDefault();
        });


    $('[data-mediazone="on"] > span.message')
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
            var formGroup = createOrGetFormGroup($(this).closest('[data-mediazone="on"]'));
            switchInputTextToInputFile(formGroup);
            if ($(formGroup).data('multi')) $(formGroup).addClass('hidden');
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
                                formGroup = createOrGetFormGroup($(_this).closest('[data-mediazone="on"]'));
                            }
                            processFile(this, formGroup);
                            formGroup = false; //on unset le formgroup pour forcer sa re-creation au file suivant, ce sont des fakes, les données sont dans le premier
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
            files =  e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer.files : $(this).prop('files');
            if(files.length) {
                $(files).each(function(){
                    if (this.name!=='') {
                        var formGroup = createOrGetFormGroup($(_this).closest('[data-mediazone="on"]'));
                        switchInputFileToInputText(formGroup);
                        processFile(this, formGroup);
                    }
                });
            }
        }
    );
});