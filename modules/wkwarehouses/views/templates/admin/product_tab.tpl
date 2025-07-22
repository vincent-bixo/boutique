{*
* This file is part of the 'Wk Warehouses Management' module feature.
* Developped by Khoufi Wissem (2018).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{if $prod->state == Product::STATE_TEMP || (!empty($prod->id) && !$isPack)}
<div class="asm_panel">
    <div class="form-group asm_qty_management">
        <div class="col-sm-12">
            <div class="checkbox">
                <label>
                    <input type="checkbox" id="field_asm" name="field_asm" value="1" {if $prod->advanced_stock_management == 1 || ($prod->state == Product::STATE_TEMP && $use_asm)}checked="checked"{/if} >
                    {l s='I want to use the advanced stock management system for this product' mod='wkwarehouses'}.
                </label>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="container-fluid">
                <div class="row"><a class="btn btn-link" href="{$link->getAdminLink('AdminManageWarehouses')|escape:'html':'UTF-8'}&addwarehouse" target="_blank"><i class="material-icons">open_in_new</i> {l s='Create a new warehouse' mod='wkwarehouses'}</a></div>
                
                <div class="row"><a class="btn btn-link" href="{$link->getAdminLink('AdminWkwarehousesManageQty')|escape:'html':'UTF-8'}&productID_product={$prod->id|intval}&submitFilterproduct"><i class="material-icons">open_in_new</i> {l s='Manage warehouses / locations / stock' mod='wkwarehouses'}</a>{if $prod->state == Product::STATE_TEMP} (<i class="icon-warning"></i> {l s='Please save product before' mod='wkwarehouses'}){/if}</div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
var synchro_msg1 = '{l s='If product is using [1]advanced stock management system[/1] and being associated to warehouses (or default warehouse was set for new product), the quantities will be synchronized automatically with the quantities in your warehouses' tags=['<strong>'] js=1 mod='wkwarehouses'}.';
var synchro_msg2 = '{l s='If product is from different warehouses, the synchronization will be according to [1]the stock and warehouses priority[/1]. See config. page for how to set priorities' tags=['<strong>'] js=1 mod='wkwarehouses'}.';
var div_content = '<div class="alert alert-warning" role="alert"><ul style="margin-left:20px"><li>'+synchro_msg1+'</li><li>'+synchro_msg2+'</li></ul></div>';

$(document).ready(function() {
	// Display notice after / before quantities fields
	if ($('#quantities h2').length > 0) {
		$(div_content).insertAfter('#quantities h2');
	}
	if ($('#attributes-generator').length > 0) {
		$(div_content).insertAfter('#attributes-generator');
	}
	// Hide advanced stock management Panel from product backoffice
	if ($('#depends_on_stock_div').length > 0) {
		$('#depends_on_stock_div').empty().html('');
	}
	// Show / hide ASM panel
	$(document).on('change', '#form_step1_type_product', function() {
		if ($(this).val() == 1) {
			$('.asm_panel').hide();
			$('.field_asm').attr('disabled', true);
			$('.field_asm').removeAttr('checked');
		} else {
			$('.asm_panel').show();
			$('.field_asm').attr('disabled', false);
		}
	});
});
</script>
{/if}
<br />
<div class="alert alert-warning" role="alert">{l s='Advanced stok management is not allowed for pack of products' mod='wkwarehouses'}.</div>
