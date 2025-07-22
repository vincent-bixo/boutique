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
    $("input#donation-price-input").on('keypress', function(e){
        if(e.keyCode == 13) {
            e.preventDefault();
        }
    });

    //product page donation validation
    $(document).on("click", ".donation-add-to-cart", function(e) {
        price = $("input#donation-price-input");
        idDonationInfo = $("input.id-donation-info");
        priceError = $("p.price-error");

        $.ajax({
            url: addDonationControllerlink,
            cache: false,
            method: 'POST',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'checkMinimumPrice',
                donation_price: price.val(),
                id_donation: idDonationInfo.val(),
                token: ajaxToken,
            },
            success: function (data) {
                if (data.status == 0) {
                    $(priceError).removeClass("hide")
                    $(priceError).text(data.errors);
                    $(price).css('border-color', 'red');
                } else {
                    $(".add-to-cart").click();
                }
            }
        });
    });

    //cart page donation validation ajax
    $(".submitDonationForm").on("click", function(e){
        form = $(this).closest("form.donation-block");
        price = $(this).closest(".donation-block").find("input.donation-price");
        idDonationInfo = $(this).closest(".donation-block").find("input.id-donation-info");
        priceError = $(this).closest(".donation-block").find("p.price-error");
        e.preventDefault();
        $.ajax({
                url: addDonationControllerlink,
                cache: false,
                method: 'POST',
                dataType: 'json',
                data: {
                    ajax: true,
                    action: 'checkMinimumPrice',
                    donation_price: price.val(),
                    id_donation: idDonationInfo.val(),
                    addProduct: 1,
                    token: ajaxToken,
                },
                success: function (data) {
                    if (data.status == 0) {
                        $(priceError).removeClass("hide")
                        $(priceError).text(data.errors);
                        $(price).css('border-color', 'red');
                    } else {
                        location.reload();
                    }
                }
            });

      });

    // var maxContentLength = 105;
    // var textSuffix = '...';
    // if ($('.donation-description').html()) {
    //     $(".donation-description").each(function () {
    //         var description = $(this);
    //         if (description.html().length > maxContentLength) {
    //             var show = description.html().substr(0, maxContentLength);
    //             var hide = description.html().substr(maxContentLength, description.html().length);
    //             var data = show + '<span class="hide">' + hide + '</span> <a href class="show-hide"> ' + readMoreTxt + textSuffix + '</a>';
    //             console.log(show);
    //             console.log(hide);
    //             description.html(data);
    //         }
    //     });
    // }
    // $('.donation-description .show-hide').click(function () {
    //     if ($(this).prev().hasClass('hide')) {
    //         $(this).prev().removeClass('hide');
    //         $(this).html(readLessTxt + textSuffix);
    //     } else {
    //         $(this).prev().addClass('hide');
    //         $(this).html(readMoreTxt + textSuffix);
    //     }
    //     return false;
    // });
});