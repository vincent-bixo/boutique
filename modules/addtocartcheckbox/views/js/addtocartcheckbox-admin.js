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
 *  @copyright 2016 BeComWeb
 *  @license   LICENSE.txt
 */

$(document).ready(function(){
	addBootstrapClasses();
	$('div.atccb_search_field .input-group-addon').click(function(){
		$(this).prev('input[type="text"]').val('');
	});
	var search_type = $('input[name="search_type"]:checked').val();
	if(search_type === 'by_ref'){
		$('input[name="id_product"]').attr('disabled', true);
	}
	else if(search_type === 'by_id'){
		$('input[name="reference"]').attr('disabled', true);
	}
	$('input[name="search_type"]').change(function(){
		if($(this).val() === 'by_ref'){
			$('input[name="id_product"]').val('').attr('disabled', true);
			$('input[name="reference"]').attr('disabled', false);
		}
		else if($(this).val() === 'by_id'){
			$('input[name="reference"]').val('').attr('disabled', true);
			$('input[name="id_product"]').attr('disabled', false);
		}
	});
});

//So far HelperForm does not allow to control bootstrap classes for form-group elements
//That's what explains this little hack thatinjects more class for a better ,control on responsive 
function addBootstrapClasses(){
	$('form.atccb_admin_form .form-group').children('label').addClass('col-xs-12 col-sm-5 col-md-4');
	$('form.atccb_admin_form .form-group div:not(.radio)').addClass('col-xs-12 col-sm-7 col-md-8');
}