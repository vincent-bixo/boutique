/**
 * 2007-2022 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2022 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
$(document).ready(function() {

  if(typeof opartproduct !== 'undefined'){
     $("#quantity_wanted").attr("min",opartproduct);
     $("#quantity_wanted").attr("step",opartproduct);
     $( "#quantity_wanted" ).val(opartproduct);
  } 

  $('#product').on('click', '.add-to-cart', function(e) {
    $('.alert-danger.ajax-error').replaceWith($('<span id="product-availability"></span>'));
  });

});

/* document.addEventListener('DOMContentLoaded', function () {
    window.prestashop.on('updatedProduct', function () {
      var product = document.getElementById('product-details');
        var element = product.dataset.product;
        var elements = element.split(',');    


        var tab = new Array();
        elements.forEach(function(item, index, array) {
            if(item.includes('id_product"')){
                var product = item.split(':');
                tab['id_product'] = product[1];
                
            }

            if(item.includes('id_product_attribute')){
                var attribute = item.split(':');
                tab['id_attribute'] = attribute[1];
                
            }
             
        });

        console.log(tab);

        checklimitquantity(
                  tab
                );        
        
    });
}); */
 
  $(document).on('click', '#product .bootstrap-touchspin-up', function() {
    if(typeof multiple !== 'undefined'){
      var element = document.getElementById("quantity_wanted");
      var value = parseInt(element.value);

      var newvalue = value + multiple - 1;
      $( "#quantity_wanted" ).val(newvalue);
  }
 
});

  $(document).on('click', '#product .bootstrap-touchspin-down', function() {
    if(typeof multiple !== 'undefined'){
      var element = document.getElementById("quantity_wanted");
      var value = parseInt(element.value);

      var newvalue = value - multiple +1;
      if(newvalue < opartproduct){
        newvalue = opartproduct;
      }
      $( "#quantity_wanted" ).val(newvalue);
  } 
}); 



/*  function checklimitquantity(data) {
console.log('checklimit');

  $.ajax({
            type: "POST",
            dataType: 'json',
            url: prestashop.urls.base_url,
            data: {
              productId: data.id_product,
              IdAttribute: data.id_attribute,
              ajax: true,
              action: 'PushAlertMessage',
              controller : "alertlimit",
              module : 'opartlimitquantity',
              fc: 'module'
            },
            context: document.body,
            success: function(data){
              var zonealert = document.getElementById('opart-alert-quantity');
              if(data.alert == false){
                zonealert.innerHTML = "";
              }
              else{
                  zonealert.innerHTML = "";
                $('#opart-alert-quantity').prepend("<div class='alert alert-info'>"+data.alert+"</div>");
              }
            },
            error : function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(XMLHttpRequest);
                console.log(textStatus);
                console.log(errorThrown);
              }
          });

}  */