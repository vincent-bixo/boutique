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
<h2>{l s='Advanced stock management' mod='wkwarehouses'}</h2>
<p class="product-name-asm" style="color: #777">{$product_name|escape:'html':'UTF-8'}{if !empty($attributes_name)} - <span class="product-name-asm" style="color:#999">{$attributes_name|escape:'html':'UTF-8'}</span>{/if}</p>

<div class="alert alert-info"><strong>{l s='Be careful' mod='wkwarehouses'}:</strong>
    <ul>
        <li>{l s='The sum of physical quantities in all warehouses should be [1]equal[/1] to the global physical quantity' tags=['<strong>'] mod='wkwarehouses'}.</li>
        <li>{l s='If you check a warehouse to set a quantity, [1]the association[/1] to this warehouse will be added automatically' tags=['<strong>'] mod='wkwarehouses'}.</li>
        <li>{l s='If you uncheck a warehouse to remove a quantity, [1]the association[/1] to this warehouse will be also removed' tags=['<strong>'] mod='wkwarehouses'}.</li>
        <li>{l s='Indicate the current unit purchase price. By default, it is the [1]last saved unit price[/1] loaded from the [1]last stock movement[/1] for this product' tags=['<strong>'] mod='wkwarehouses'}.</li>
        <li style="list-style:none">{l s='If no stock movement yet, it will be the [1]wholesale price[/1] indicated in the product backoffice' tags=['<strong>'] mod='wkwarehouses'}.</li>
        <li>{l s='Indicate the currency associated to the product unit price. The price will be converted automatically to the default currency' tags=['<strong>'] mod='wkwarehouses'}.</li>
    </ul>
</div>
<div class="alert alert-warning">
	{l s='If you notice [1]a gap between the warehouses and Prestashop reserved quantities[/1], you can fix it:' tags=['<strong>'] mod='wkwarehouses'} 
    <ul>
     	<li>{l s='[1]By assigning the appropriate warehouse to the concerned orders[/1] through the following link:' tags=['<strong>'] mod='wkwarehouses'} <a href="{$link->getAdminLink('AdminWkwarehousesOrders')|escape:'html':'UTF-8'}&orderFilter_product_q={$id_product|intval}{if $id_product_attribute}&orderFilter_combination_q={$id_product_attribute|intval}{/if}">{l s='Orders/Warehouses Assignments page' mod='wkwarehouses'}</a>.</li>
        <li>{l s='Or through this same page by using the [1]"Bulk actions"[/1] button' tags=['<strong>'] mod='wkwarehouses'}.</li>
    </ul>
</div>
<hr />

<table class="warehouses-table">
	<tbody>
		<tr>
            {* ******* PHYSICAL QUANTITY ******** *}
            <td class="text-center">
            	<i class="icon-archive"></i> {l s='Global physical quantity' mod='wkwarehouses'} : <span class="badge badge-info">{$currentQties['physical_quantity']|intval}</span>
            </td>
            {* ******* AVAILABLE QUANTITY ******** *}
            <td class="text-center">
            	<i class="icon-archive"></i> {l s='Available quantity (for sale)' mod='wkwarehouses'} : <span class="badge badge-info">{$currentQties['quantity']|intval}</span>
            </td>
            {* ******* RESERVED QUANTITY ******** *}
            <td class="text-center">
            	<i class="icon-archive"></i> {l s='Reserved quantity' mod='wkwarehouses'} : <span class="badge badge-danger">{$currentQties['reserved_quantity']|intval}</span>
            </td>
            {* ******* GAP & CHECK QUANTITIES ******** *}
		</tr>
        <tr>
            <td colspan="3" class="text-center">
            	<span class="badge badge-{if $warehouses_qty_sum|intval != $currentQties['physical_quantity']|intval}danger{else}info{/if}"><i class="icon-archive"></i> {l s='Current warehouses physical quantity' mod='wkwarehouses'} : <strong>{$warehouses_qty_sum|intval}</strong></span>
            </td>
        </tr>
	</tbody>
</table>
{if $warehouses_qty_sum != $currentQties['physical_quantity'] && count($locations) && $isPresentInStock}
<div align="center" class="panelCorrectQty">
<button type="button" data-id-product="{$id_product|intval}" data-id-product-attribute="{$id_product_attribute|intval}" class="button btn btn-warning alignWarehouseToPhysicalQty"> {l s='Click to Align Warehouses quantities to the global physical quantity' mod='wkwarehouses'}</button></div>
{/if}

<hr />
{if is_array($warehouses) && count($warehouses)}
<div class="asm_management_table">
<table class="table advanced_stock_management_table">
    <thead>
    	<th></th>
        <th><strong>{l s='Warehouse' mod='wkwarehouses'}</strong></th>
        <th class="center" style="width:130px"><strong>{l s='Physical Quantity' mod='wkwarehouses'}</strong></th>
        <th class="center"><strong>{l s='Reserved Quantity' mod='wkwarehouses'}</strong></th>
        <th><strong>{l s='Action' mod='wkwarehouses'}</strong></th>
        <th><strong>{l s='Unit price (tax excl.)' mod='wkwarehouses'}</strong></th>
        <th><strong>{l s='Currency' mod='wkwarehouses'}</strong></th>
        <th></th>
    </thead>
    <tbody>
    {foreach from=$warehouses item=warehouse}
    <tr>
        <td><input value="1" class="trigger_stage" {if in_array($warehouse.id_warehouse, $locations)}checked{/if} type="checkbox" name="locations[{$warehouse.id_warehouse|intval}][location]">
            <span class="disable_stage"></span></td>
        <td>{$warehouse.name|escape:'quotes':'UTF-8'}</td>
        <td class="center">
        	{if array_key_exists($warehouse.id_warehouse, $quantity_locations) && isset($quantity_locations[$warehouse.id_warehouse]['physical'])}<strong>{$quantity_locations[$warehouse.id_warehouse]['physical']|intval}</strong>{else}0{/if}
        </td>
        <td class="center">
        	{if array_key_exists($warehouse.id_warehouse, $quantity_locations)}<strong>{$quantity_locations[$warehouse.id_warehouse]['reserved']|intval}</strong>{else}0{/if}
        </td>
        <td><div class="row">
                <div class="col-lg-6">
                    <select name="locations[{$warehouse.id_warehouse|intval}][action]">
                    	{if array_key_exists($warehouse.id_warehouse, $quantity_locations)}
                        <option value="1">{l s='Increase quantity' mod='wkwarehouses'}</option>
                        <option value="-1">{l s='Decrease quantity' mod='wkwarehouses'}</option>
                        {else}
                        <option value="2">{l s='Define quantity' mod='wkwarehouses'}</option>
                        {/if}
                    </select>
                </div>
                <div class="col-lg-4">
                    <input name="locations[{$warehouse.id_warehouse|intval}][quantity]" type="text" style="text-align:center">
                </div>
            </div>
        </td>
		<td><input name="locations[{$warehouse.id_warehouse|intval}][price_te]" type="text" style="text-align:center;" {if array_key_exists($warehouse.id_warehouse, $quantity_locations) && $quantity_locations[$warehouse.id_warehouse]['price_te']}value="{$quantity_locations[$warehouse.id_warehouse]['price_te']|floatval}"{/if}></td>
		<td>
            <select name="locations[{$warehouse.id_warehouse|intval}][id_currency]" class="id_currency">
            {foreach from=$currencies item=currency}
                <option value="{$currency.id_currency|intval}" {if $currency.id_currency == Configuration::get('PS_CURRENCY_DEFAULT')}selected{/if}>{$currency.name|escape:'html':'UTF-8'}</option>
            {/foreach}
            </select>        
        </td>
        <td><input type="hidden" value="{$id_product|intval}" name="locations[{$warehouse.id_warehouse|intval}][id_product]">
            <input type="hidden" value="{$id_product_attribute|intval}" name="locations[{$warehouse.id_warehouse|intval}][id_product_attribute]">
            <input type="hidden" value="{$warehouse.id_warehouse|intval}" name="locations[{$warehouse.id_warehouse|intval}][id_warehouse]">
            <button type="button" data-id-product="{$id_product|intval}" data-id-product-attribute="{$id_product_attribute|intval}" class="button btn btn-default applyQuantityAdvancedStock"> {l s='Apply changes' mod='wkwarehouses'}</button></td>
    </tr>
    {/foreach}
    </tbody>
</table>
</div>
{/if}
