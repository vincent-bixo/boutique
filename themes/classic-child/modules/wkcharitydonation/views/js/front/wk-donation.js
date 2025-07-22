/*
* 2010-2019 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through this link for complete license : https://store.webkul.com/license.html
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to https://store.webkul.com/customisation-guidelines/ for more information.
*
*  @author    Webkul IN <support@webkul.com>
*  @copyright 2010-2019 Webkul IN
*  @license   https://store.webkul.com/license.html
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
/*
    var maxContentLength = 200; // œ& vm le 25/11/22 pour la longueur d'affichage dans le panier
    var textSuffix = '...';
    if ($('.donation-description').html()) {
        $(".donation-description").each(function () {
            var description = $(this);
            if (description.html().length > maxContentLength) {
                var show = description.html().substr(0, maxContentLength);
                var hide = description.html().substr(maxContentLength, description.html().length);
                var data = show + '<span class="hide">' + hide + '</span> <a href class="show-hide"> ' + readMoreTxt + textSuffix + '</a>';
                description.html(data);
            }
        });
    }
    $('.show-hide').click(function () {
        if ($(this).prev().hasClass('hide')) {
            $(this).prev().removeClass('hide');
            $(this).html(readLessTxt + textSuffix);
        } else {
            $(this).prev().addClass('hide');
            $(this).html(readMoreTxt + textSuffix);
        }
        return false;
    });
*/
});
