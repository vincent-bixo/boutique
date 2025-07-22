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
{if $order_details|@count}
<table class="product" width="100%" cellpadding="4" cellspacing="0">
	<thead>
		<tr>
			<th class="product left" colspan="3" style="color:#FFF; background-color:#666;">{l s='WAREHOUSES PRODUCTS LOCATIONS' d='Shop.Pdf' pdf='true' mod='wkwarehouses'}</th>
		</tr>
	</thead>
	<thead>
		<tr>
			<th class="product left" width="50%">{l s='Product' d='Shop.Pdf' pdf='true' mod='wkwarehouses'}</th>
			<th class="product left" width="20%">{l s='Reference' d='Shop.Pdf' pdf='true' mod='wkwarehouses'}</th>
			<th class="product left" width="30%">{l s='Location in Warehouse' d='Shop.Pdf' pdf='true' mod='wkwarehouses'}</th>
		</tr>
	</thead>
	<tbody>
		{foreach $order_details as $order_detail}
			{cycle values=["color_line_even", "color_line_odd"] assign=bgcolor_class}
			<tr class="product {$bgcolor_class|escape:'html':'UTF-8'}">
				<td class="product left">
					{$order_detail.product_name|escape:'html':'UTF-8'}
				</td>
				<td class="product left">
					{if empty($order_detail.product_reference)}
						---
					{else}
						{$order_detail.product_reference|escape:'html':'UTF-8'}
					{/if}
				</td>
				<td class="product left">
                	<strong>{$order_detail.warehouse_name|escape:'html':'UTF-8'}</strong>
					{if !empty($order_detail.warehouse_location)}<br />&nbsp;&nbsp;{$order_detail.warehouse_location|escape:'html':'UTF-8'}{/if}
				</td>
			</tr>
		{/foreach}
	</tbody>
</table>
{/if}
