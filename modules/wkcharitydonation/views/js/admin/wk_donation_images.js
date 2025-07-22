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

$(document).ready(function () {

    $('#donation_image_select_btn').click(function (e) {
        $('#donation_product_images').trigger('click');
    });

    $('#image_file_name').click(function (e) {
        $('#donation_product_images').trigger('click');
    });

    $('#image_file_name').on('dragenter', function (e) {
        e.stopPropagation();
        e.preventDefault();
    });

    $('#image_file_name').on('dragover', function (e) {
        e.stopPropagation();
        e.preventDefault();
    });

    $('#image_file_name').on('drop', function (e) {
        e.preventDefault();
        var files = e.originalEvent.dataTransfer.files;
        $('#donation_product_images')[0].files = files;
        $(this).val(files[0].name);
    });

    $('#donation_product_images').change(function (e) {
        if ($(this)[0].files !== undefined) {
            var files = $(this)[0].files;
            var name = '';

            $.each(files, function (index, value) {
                name += value.name + ', ';
            });

            $('#image_file_name').val(name.slice(0, -2));
        }
        else // Internet Explorer 9 Compatibility
        {
            var name = $(this).val().split(/[\\/]/);
            $('#image_file_name').val(name[name.length - 1]);
        }
    });

    $("#donation_product_images").on("change", function (event) {
        if (typeof this.files[0] != 'undefined') {
            if (this.files[0].size > maxSizeAllowed * 1000000) {
                showErrorMessage(filesizeError);
                return false;
            }
        }
        files = event.target.files;
        for (var i = 0; i < files.length; i++) {
            var formData = new FormData();
            var file = files[i];
            formData.append('donation_image', file);
            uploadDonationImages(formData);
        }
    });

    function uploadDonationImages(formData) {
        var idDonation = $("#id_donation_info").val();
        formData.append('id_donation_info', idDonation);
        formData.append('ajax', true);
        formData.append('action', 'uploadDonationProductImages');
        $.ajax({
            type: 'POST',
            url: adminManageDonationUrl,
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function (image) {
                image = JSON.parse(image);
                if (!image.hasError) {
                    $('.list-empty-tr').remove();
                    var html = '';
                    html += '<tr class="';
                    if (image.is_cover) {
                        html += 'cover-image-tr';
                    }
                    html += '">';
                    html += '<td class="text-center">' + image.id_image + '</td>';
                    html += '<td class="text-center">';
                    html += '<a class="img-preview" href="' + image.image_url + '">';
                    html += '<img class="img-thumbnail" width="100" src="' + image.image_url + '"/>';
                    html += '</a>';
                    html += '</td>';
                    html += '<td class="text-center ';
                    if (image.is_cover) {
                        html += 'cover-image-td';
                    }
                    html += '">';
                    html += '<a href="#" class="';
                    if (image.is_cover) {
                        html += 'text-success';
                    } else {
                        html += 'text-danger';
                    }
                    html += ' changer-cover-image" data-id-donation="' + idDonation + '" data-is-cover="';
                    if (image.is_cover) {
                        html += '1';
                    } else {
                        html += '0';
                    }
                    html += '" data-id-image="' + image.id_image + '">';
                    if (image.is_cover) {
                        html += '<i class="icon-check"></i>';
                    } else {
                        html += '<i class="icon-times"></i>';
                    }
                    html += '</a>';
                    html += '</td>';
                    html += '<td class="text-center">';
                    html += '<button type="button" class="btn btn-default delete-donation-image" data-id-donation="' + idDonation + '" data-is-cover="';
                    if (image.is_cover) {
                        html += '1';
                    } else {
                        html += '0';
                    }
                    html += '" data-id-image="' + image.id_image + '"><i class="icon-trash"></i></button>';
                    html += '</td>';
                    html += '</tr>';
                    $("#donation-image-table tbody").append(html);

                    showSuccessMessage(imgUploadSuccessMsg);
                } else {
                    if (typeof image.message != 'undefined') {
                        showErrorMessage(image.message);
                    } else {
                        showErrorMessage(imgUploadErrorMsg);
                    }
                }
            },
            error: function (data) {
                showErrorMessage(imgUploadErrorMsg);
            }
        });
    }

    $('body').on('click', '.changer-cover-image', function (e) {
        e.preventDefault();
        var idDonation = $("#id_donation_info").val();
        var idImage = $(this).attr('data-id-image');
        var isCover = $(this).attr('data-is-cover');
        var triggerElement = $(this);

        if (parseInt(idDonation) && parseInt(idImage)) {
            if (isCover == 0) {
                $.ajax({
                    type: 'POST',
                    url: adminManageDonationUrl,
                    data: {
                        ajax: true,
                        action: 'changeDonationCoverImage',
                        id_donation_info: idDonation,
                        id_image: idImage,
                    },
                    success: function (result) {
                        if (result) {
                            // remover cover image identifier from old cover image
                            var oldCoverImageTr = $("#donation-image-table tbody tr.cover-image-tr");
                            oldCoverImageTr.removeClass("cover-image-tr").find("td.cover-image-td").removeClass("cover-image-td").find("a.changer-cover-image").removeClass("text-success").addClass("text-danger").attr("data-is-cover", "0").find("i.icon-check").removeClass("icon-check").addClass("icon-times");
                            oldCoverImageTr.find("td button.delete-donation-image").attr("data-is-cover", "0");

                            // Add classes in new cover image elements
                            triggerElement.removeClass("text-danger").addClass("text-success").find("i.icon-times").removeClass("icon-times").addClass("icon-check");
                            triggerElement.parent().addClass("cover-image-td").parent().addClass("cover-image-tr").find("td button.delete-donation-image").attr("data-is-cover", "1");

                            showSuccessMessage(coverImgSuccessMsg);
                        } else {
                            showErrorMessage(coverImgErrorMsg + 's');
                        }
                    },
                    error: function (data) {
                        showErrorMessage(coverImgErrorMsg + 'd');
                    }
                });
            }
        } else {
            showErrorMessage(coverImgErrorMsg + 'e');
        }
    });

    $('body').on('click', '.delete-donation-image', function (e) {
        e.preventDefault();
        var idDonation = $("#id_donation_info").val();
        var idImage = $(this).attr('data-id-image');
        var isCover = $(this).attr('data-is-cover');
        var triggerElement = $(this);

        if (parseInt(idDonation) && parseInt(idImage)) {
            $.ajax({
                type: 'POST',
                url: adminManageDonationUrl,
                data: {
                    ajax: true,
                    action: 'deleteDonationImage',
                    id_donation_info: idDonation,
                    id_image: idImage,
                },
                success: function (result) {
                    result = JSON.parse(result);
                    if (!result.hasError) {
                        if (parseInt(isCover)) {
                            var newCover = $("#donation-image-table tbody tr td.image-id:contains('" + result.idCover + "')").parent().find("a.changer-cover-image");
                            newCover.removeClass("text-danger").addClass("text-success").attr('data-is-cover', 1).find("i.icon-times").removeClass("icon-times").addClass("icon-check");
                            newCover.parent().addClass("cover-image-td").parent().addClass("cover-image-tr").find("td button.delete-donation-image").attr("data-is-cover", "1");
                        }

                            var currentPosition = parseInt(triggerElement.parents('tr').find('td.image-position').text());
                            var nextAllSiblings = triggerElement.parents('tr').nextAll();
                            triggerElement.parents('tr').fadeOut().remove();

                            // Correct Images Positions
                            $(nextAllSiblings).each(function (index, element) {
                                $(this).find('td.image-position').text(parseInt(currentPosition + parseInt(index)));
                            });
                            showSuccessMessage(deleteImgSuccessMsg);

                    } else {
                        showErrorMessage(deleteImgErrorMsg);
                    }
                },
                error: function (data) {
                    showErrorMessage(deleteImgErrorMsg);
                }
            });
        } else {
            showErrorMessage(deleteImgErrorMsg);
        }
    });

});