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
<script type="text/javascript">
	var warehouses_in_cart = {Configuration::get('WKWAREHOUSE_ENABLE_INCART')|intval};
	var display_warehouse_name = {Configuration::get('WKWAREHOUSE_WAREHOUSES_INCART')|intval};
	var display_warehouse_location = {Configuration::get('WKWAREHOUSE_LOCATIONS_INCART')|intval};
	var display_warehouse_quantity = {Configuration::get('WKWAREHOUSE_QUANTITIES_INCART')|intval};
	var display_delivery_time = {Configuration::get('WKWAREHOUSE_DELIVERYTIMES_INCART')|intval};
	var display_country = {Configuration::get('WKWAREHOUSE_COUNTRIES_INCART')|intval};
	var deliver_address_incart = {$deliver_address_incart|intval};

	var item_instock_txt = '{l s='items in stock' js=1 mod='wkwarehouses'}';
	var item_location_txt = '{l s='Location:' js=1 mod='wkwarehouses'}';
	{if isset($delivery_address)}
	var undelivered_product_txt = '{l s='This product can not be delivered by any carrier to your address in %s' sprintf=[$delivery_address|escape:'html':'UTF-8'] js=1 mod='wkwarehouses'}';
	{/if}
	var position_cart = '{Configuration::get('WKWAREHOUSE_POSITION_INCART')|escape:'html':'UTF-8'}';
	{if isset($warehouses_cart_details)}
		var warehouses_cart_details = '{$warehouses_cart_details|json_encode}';
		warehouses_cart_details = warehouses_cart_details.replace(/&quot;/ig,'"');
	{/if}
	{if isset($carriers_restrictions)}
		var carriers_restrictions = '{$carriers_restrictions|json_encode}';
		carriers_restrictions = carriers_restrictions.replace(/&quot;/ig,'"');
	{/if}

	var process_cart_url = '{$link->getModuleLink('wkwarehouses', 'processactions', array(), true)|escape:'html':'UTF-8'|replace:'&amp;':'&'}';
	process_cart_url = process_cart_url.replace(/&amp;/g, '&');

	if (document.readyState != 'loading'){
		loadWarehousesDetailsAftertheDomHasLoaded();
	} else if (document.addEventListener) {
		document.addEventListener('DOMContentLoaded', loadWarehousesDetailsAftertheDomHasLoaded, false);
	} else {
		document.attachEvent('onreadystatechange', function() {
			if (document.readyState != 'loading') {
				loadWarehousesDetailsAftertheDomHasLoaded();
			}
		});
	}
	function loadWarehousesDetailsAftertheDomHasLoaded()
	{
		if (typeof warehouses_cart_details !== 'undefined') {
			loadWarehousesDetails(warehouses_cart_details, true);
		}
		if (typeof carriers_restrictions !== 'undefined') {
			loadCarriersWarehousesRestrictionsInCart(carriers_restrictions);
		}
	}
</script>
