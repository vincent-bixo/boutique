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
$(function(){
	$('input[name="atccb_checkbox"]').on('click', function(e){
		$(this).attr('disabled', true);
		$('#atccb_processing_message').fadeIn(300);
		if($(this).is(':checked')){
			//Adding
			var atccb_action = 'add';
			var atccb_action_url = $(this).data('up-url');
		}
		else{
			//Removing
			var atccb_action = 'remove';
			var atccb_action_url = $(this).data('remove-url');
		}
		console.log(atccb_action_url);
		$.ajax({
			type: 'POST',
			headers: { "cache-control": "no-cache" },
			url: atccb_action_url,
			cache: false,
			dataType: 'json',
			data : {ajax: '1',action: 'update'},
			beforeSend : function(){
				$('#atccb_processing_message').fadeIn(200);
			},
			complete : function(jsonData){
				$('#atccb_processing_message').hide(100);
				setTimeout(function(){location.reload();}, 2000);
			},
			success: function(jsonData){
				if(jsonData.success){
					showAtccbMessage(atccb_result = true, atccb_action);
				}
				else{
					showAtccbMessage(atccb_result = false, null);
				}
			},
			error: function(){
				showAtccbMessage(atccb_result = false, null);
			}
		});
	});
});

function showAtccbMessage(atccb_result, atccb_action){
	if(atccb_result){
		if(atccb_action === 'add'){
			$('#atccb_add_success_message').fadeIn(300);
		}
		if(atccb_action === 'remove'){
			$('#atccb_delete_success_message').fadeIn(300);
		}
	}
	else{
		$('#atccb_error_message').fadeIn(300);
	}
}
