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

  if($('#form_step3_minimal_quantity').length) {
    $('#form_step3_minimal_quantity').after('<p class="small subtitle italic" style="color: red;"><a href=="" id="configureOpArtQty">' + defaultQuantityConfigureMessage + '</a></p>');
    $('#form_step3_minimal_quantity').after('<p class="small subtitle italic" style="color: red;">' + defaultQuantityDesactivatedMessage + '</p>');
    $('#configureOpArtQty').on('click', function(e) {
      e.preventDefault();
      $('#tab_hooks').find('a').trigger('click');
      $('[data-target="module-opartlimitquantity"]').trigger('click');
    });
  }

  var minimalQtyMsgAdded = [];
  $('#form_content').on('click', '.combinations-list .btn-open', function(e) {
    let index = $(this).parent().parent().parent().data('index');

    if($('#combination_' + index + '_attribute_minimal_quantity').length && minimalQtyMsgAdded.indexOf(index) == -1) {
      $('#combination_' + index + '_attribute_minimal_quantity').after('<p class="subtitle italic" style="color: red;">' + defaultQuantityDesactivatedMessage + '</p>');
      minimalQtyMsgAdded.push(index);
    }
  });

  $('.adminproducts').on('change', 'input[name="opartsingleproduct"]', function() {
    if($(this).val() == 1) {
      $('#opartproductqty').show('fast');
      $('#opartdeclinationqty').hide('fast');
    } else {
      $('#opartproductqty').hide('fast');
      $('#opartdeclinationqty').show('fast');
    }
  });

  $('.adminproducts').on('click', '.addBatch', function(e) {
    e.preventDefault();

    let id_product_attribute = parseInt($(this).data('id'));
    $(this).parent().parent().find('.batch_list').append('<div class="form-group row">\n' +
      '  <div class="form-control-label py-0">\n' +
      '   <div style="display: flex; justify-content: flex-end;">\n' +
      '     <select name="batches[' + id_product_attribute + '][type][]" id="" class="form-control" style="width: 200px;">\n' +
      '       <option value="multiple">' + multiple_of + '</option>\n' +
      '       <option value="fixed">' + fixed_quantity + '</option>\n' +
      '     </select>\n' +
      '   </div>\n' +
      '  </div>\n' +
      '  <div class="col-xs">\n' +
      '    <input type="text" name="batches[' + id_product_attribute + '][quantity][]" class="fixed-width-sm form-control" value="">\n' +
      '  </div>\n' +
      '  <a href="" class="removeBatch"><i class="material-icons">delete</i></a>\n' +
      '</div>\n');
  });

  $('.adminproducts').on('click', '.removeBatch', function(e) {
    e.preventDefault();

    $(this).parent().remove();
  });

});
