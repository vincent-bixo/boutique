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
	{if isset($product_stocks_list) && $product_stocks_list}
        var product_stocks_list = '{$product_stocks_list|json_encode}';
		product_stocks_list = product_stocks_list.replace(/&quot;/ig,'"');
	{/if}
    var module_dir = '{$module_dir|escape:'html':'UTF-8'}';
	var products_txt = '{l s='products' js=1 mod='wkwarehouses'}';
	var warehouse_select_label = '{l s='Warehouse' js=1 mod='wkwarehouses'}';
	var product_txt = '{l s='product' js=1 mod='wkwarehouses'}';
	var instock_txt = '{l s='In stock' js=1 mod='wkwarehouses'}';
	var location_txt = '{l s='Location' js=1 mod='wkwarehouses'}';
	var txt_ok = '{l s='Ok' js=1 mod='wkwarehouses'}';
	var txt_multi_warehouses = '{l s='You are trying to add a product stored in a warehouse different than the ones where are stored the products found in your cart! Please try with another product' js=1 mod='wkwarehouses'}.';
	var txt_invalid_qty = '{l s='Invalid quantity!' mod='wkwarehouses' js=1}';
	var item_instock_txt = '{l s='items in stock' js=1 mod='wkwarehouses'}';
	var remove_product_cart_txt = '{l s='Remove this product / warehouse from the cart' js=1 mod='wkwarehouses'}';
	var warehouse_selected_txt = '{l s='Selected warehouse:' js=1 mod='wkwarehouses'}';
	var loading_txt = '{l s='Loading...' js=1 mod='wkwarehouses'}';
	var availability_carriers_txt = '{l s='This product can not be delivered!' js=1 mod='wkwarehouses'}';
	var display_warehouses_stock = {Configuration::get('WKWAREHOUSE_DISPLAY_STOCK_INFOS')|intval};
	var display_warehouses_locations = {Configuration::get('WKWAREHOUSE_DISPLAY_LOCATION')|intval};
	var display_deliveries_times = {Configuration::get('WKWAREHOUSE_DISPLAY_DELIVERIES_TIME')|intval};
	var display_warehouses_countries = {Configuration::get('WKWAREHOUSE_DISPLAY_COUNTRIES')|intval};
	var display_icon = {Configuration::get('WKWAREHOUSE_DISPLAY_STOCK_ICON')|intval};
    var display_warehouses_infos = '{Configuration::get('WKWAREHOUSE_WAREHOUSEINFOS_POSITION')|escape:'html':'UTF-8'}';
	{if isset($warehouses_txt) && $warehouses_txt}
	var warehouses_txt = '{$warehouses_txt|escape:'html':'UTF-8'}';
	{/if}

	var process_cart_url = '{$link->getModuleLink('wkwarehouses', 'processactions', array(), true)|escape:'html':'UTF-8'|replace:'&amp;':'&'}';
	process_cart_url = process_cart_url.replace(/&amp;/g, '&');
	var allow_set_warehouse = {Configuration::get('WKWAREHOUSE_ALLOWSET_WAREHOUSE')|intval};
	var display_selected_best_warehouse = {Configuration::get('WKWAREHOUSE_DISPLAY_SELECTED_WAREHOUSE')|intval};
	var display_warehouse_name = {Configuration::get('WKWAREHOUSE_DISPLAY_WAREHOUSE_NAME')|intval};
	var display_warehouse_location = {Configuration::get('WKWAREHOUSE_DISPLAY_SELECTED_LOCATION')|intval};
	var display_warehouse_quantity = {Configuration::get('WKWAREHOUSE_DISPLAY_SELECTED_STOCK')|intval};
	var display_delivery_time = {Configuration::get('WKWAREHOUSE_DISPLAY_DELIVERYTIME')|intval};
	var display_country = {Configuration::get('WKWAREHOUSE_DISPLAY_COUNTRY')|intval};
</script>
