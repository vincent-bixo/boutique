/**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your needs
* please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*/

$(document).ready(function(){

    //setting datepicker
    var today = new Date();
    var tomorrow = new Date();
    tomorrow.setDate(today.getDate() + 1);

    $("#donation_expiry_date").datepicker({
        showOtherMonths: true,
        dateFormat: 'yy-mm-dd',
        minDate: tomorrow,
        beforeShow: function() {
            setTimeout(function(){
                $('.ui-datepicker').css('z-index', 10);
            }, 0);
        }
    });

    //setting active tab
    if ($('#active_tab').val() != '') {
        var active_tab = $('#active_tab').val();
        changeTabStatus(active_tab);
    }

    //setting default position for switches
    if(parseInt($('#price_type').val()) == 1) {
        $('#fixed').removeClass('hidden');
        $('#minimum_price').addClass('hidden');
    } else if(parseInt($('#price_type').val()) == 2) {
        $('#fixed').addClass('hidden');
        $('#minimum_price').removeClass('hidden');
    }
    $('#price_type').on('change',function(){
        if(parseInt($(this).val()) == 1) {
            $('#fixed').removeClass('hidden');
            $('#minimum_price').addClass('hidden');
        } else if(parseInt($(this).val()) == 2) {
            $('#fixed').addClass('hidden');
            $('#minimum_price').removeClass('hidden');
        }
    });

    if (parseInt($('input[name="advertise"]:checked').val())) {
        $("#advertisement_config").removeClass('hidden');
    } else {
        $("#advertisement_config").addClass('hidden');
    }
    $('input[name="advertise"]').on('change', function(){
        if (parseInt($(this).val())) {
            $("#advertisement_config").removeClass('hidden');
        } else {
            $("#advertisement_config").addClass('hidden');
        }
    });

    if (parseInt($('input[name="show_donate_button"]:checked').val())) {
        $(".donate_button").removeClass('hidden');
    } else {
        $(".donate_button").addClass('hidden');
    }
    $('input[name="show_donate_button"]').on('change', function(){
        if (parseInt($(this).val())) {
            $(".donate_button").removeClass('hidden');
        } else {
            $(".donate_button").addClass('hidden');
        }
    });
    //setting image upload button
    $('#image_select_btn_left_right').click(function(e) {
        $('#background_image_left_right').trigger('click');
    });

    $('#banner_file_name_left_right').click(function(e) {
        $('#background_image_left_right').trigger('click');
    });
    $('#background_image_left_right').change(function (e) {
        if (typeof this.files[0] != 'undefined') {
            if (this.files[0].size > maxSizeAllowed * 1000000) {
                showErrorMessage(filesizeError);
                return false;
            }
        }
        if ($(this)[0].files !== undefined) {
            var files = $(this)[0].files;
            var name = '';

            $.each(files, function (index, value) {
                name += value.name + ', ';
            });

            $('#banner_file_name_left_right').val(name.slice(0, -2));
        }
        else // Internet Explorer 9 Compatibility
        {
            var name = $(this).val().split(/[\\/]/);
            $('#banner_file_name_left_right').val(name[name.length - 1]);
        }
    });

    $('#image_select_btn_head_foot').click(function (e) {
        $('#background_image_head_foot').trigger('click');
    });

    $('#banner_file_name_head_foot').click(function (e) {
        $('#background_image_head_foot').trigger('click');
    });
    $('#background_image_head_foot').change(function(e) {
        if (typeof this.files[0] != 'undefined') {
            if (this.files[0].size > maxSizeAllowed * 1000000) {
                showErrorMessage(filesizeError);
                return false;
            }
        }
        if ($(this)[0].files !== undefined)
        {
            var files = $(this)[0].files;
            var name  = '';

            $.each(files, function(index, value) {
                name += value.name+', ';
            });

            $('#banner_file_name_head_foot').val(name.slice(0, -2));
        }
        else // Internet Explorer 9 Compatibility
        {
            var name = $(this).val().split(/[\\/]/);
            $('#banner_file_name_head_foot').val(name[name.length-1]);
        }
    });

});

$(document).on("ready", function() {
    //setting colorpicker
    $('.mColorPickerinput').on('click', function () {
        $(this).next('.mColorPickerTrigger').click();
    });
    setTimeout(() => {
        $('.mColorPickerTrigger').html("<img src='" + baseDir + "img/admin/color.png' style='border:0;margin:0 0 0 3px' align='absmiddle'>");
        $('#mColorPickerImg').css({
            'background-image': "url('" + baseDir + "img/admin/colorpicker.png')"
        });
        $('#mColorPickerImgGray').css({
            'background-image': "url('" + baseDir + "img/admin/graybar.jpg')"
        });
        $('#mColorPickerFooter').css({
            'background-image': "url('" + baseDir + "img/admin/grid.gif')"
        });
    }, 100);
});

function showManageDonationLangField(select_lang_name, id_lang)
{
	$('#donation_lang_btn').html(select_lang_name + ' <span class="caret"></span>');

	$('.wk_text_field_all').hide();
    $('.wk_text_field_' + id_lang).show();
    $('.all_lang_icon').attr('src', ps_img_lang_dir+id_lang+'.jpg');
    $('#choosedLangId').val(id_lang);
}

function changeTabStatus(active_tab) {
    //Remove all tabs from active (make normal)
    $('.wk-tabs-panel .tab-content .tab-pane').removeClass('active');
    $('.wk-tabs-panel .nav-tabs li').removeClass('active');
    // //Add active class in selected tab
    $('[href="#' + active_tab + '"]').parent('li').addClass('active');
    $('#' + active_tab).addClass('active in');
}

function getActiveTabAfterSubmitForm() {
    //put active tab in input hidden type
        var active_tab_id = $('.wk-tabs-panel .nav-tabs li.active a').attr('href');

        var active_tab_name = active_tab_id.substring(1, active_tab_id.length);
        $('#active_tab').val(active_tab_name);

}

tinySetup({
    editor_selector: "wk_tinymce",
    width: 500
});

$(document).on('click', '.wk_delete_charity_banner', function(){
    if (confirm(confirmDelete)) {
        var refthis = $(this);
        var file = refthis.data('file');
        $.ajax({
            type: 'POST',
            url: adminManageDonationUrl,
            data: {
                file: file,
                action: "deleteImage",
                ajax: "1"
            },
            dataType: "json",
            success: function(data) {
                if (data.response) {
                    refthis.siblings("p").hide();
                    refthis.hide();
                    return $.growl.notice({
                        title: data.title,
                        size: "large",
                        message: data.msg
                    });

                } else {
                    return $.growl.error({
                        title: data.title,
                        size: "large",
                        message: data.msg
                    });
                }
            }
        });
    }
})