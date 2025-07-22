/*
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    Frederic Moreau
 *  @copyright 2020 BeComWeb
 *  @license   LICENSE.txt
 */

$(document).ready(function(){
    $('#submit_creation_date').off('click').on('click', function(e){
        $.ajax({
			type : 'POST',
            url : 'index.php',
            cache : false,
			dataType : 'json',
			data : {
                'ajax' : 1,
                'controller' : 'AdminChangeproductcreationdate',
                'action' : 'updateCreationDate',
                'product_creation_date' : $('#product_creation_date').val(), 
                'id_product' : parseInt($('input[name="cpcd_product_id"]').val()), 
                'token' : $('input[name="cpcd_module_token"]').val(), 
            }, 
			success : function(data) {
                $('#changeproductcreationdate_result').find('.alert-text').text(data.msg);
				if (data.result) {
                    $('#changeproductcreationdate_result').find('.alert').addClass('alert-success');
				} else {
                    $('#changeproductcreationdate_result').find('.alert').addClass('alert-warning');
				}
            },
            complete : function() {
                $('#changeproductcreationdate_result').fadeIn(200);
            },
			error : function() {
                $('#changeproductcreationdate_result').find('.alert-text').text(ajax_error_text);
                $('#changeproductcreationdate_result').find('.alert').addClass('alert-danger');
            },
            beforeSend : function(){
                $('#changeproductcreationdate_result').fadeOut(100);
                $('#changeproductcreationdate_result').find('.alert').removeClass('alert-success alert-warning alert-danger').children('.alert-text').text('');
            }
        });
        e.preventDefault();
    });
});