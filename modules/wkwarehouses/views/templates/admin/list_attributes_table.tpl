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
<table id="product_view" class="table tableDnD" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th class="center" style="width:1%;"><input type="checkbox" class="check_all" /></th>
            <th class="center" style="width:2%;">#ID</th>
            <th class="text-left" style="width:30%;">{if $hasAttributes}{l s='Combination' mod='wkwarehouses'}{else}{l s='Name' mod='wkwarehouses'}{/if}</th>
            <th class="text-left">{if $hasAttributes}{l s='Reference' mod='wkwarehouses'}{/if}</th>
            <th class="center" style="width:14%;">{l s='Warehouses / Locations' mod='wkwarehouses'}</th>
            <th class="center" style="width:14%;">{l s='Warehouse(s) Qty' mod='wkwarehouses'}</th>
            <th class="center" style="width:10%;">{l s='Shop physical Qty' mod='wkwarehouses'}</th>
            <th class="center" style="width:7%;">{l s='Reserved' mod='wkwarehouses'}</th>
            <th class="center" style="width:10%;">{l s='Edit available quantity' mod='wkwarehouses'}</th>
        </tr>
    </thead>
    <tbody>
    {if $combinations|@count}
        {foreach $combinations as $product}
            {assign var=class_bg value=''}
            {if $product_asm && $product.warehouses_qty_sum|intval != $product.physical_quantity|intval}
                {assign var=class_bg value=' stock-warning'}
            {/if}
        <tr>
            <td class="center{$class_bg|escape:'html':'UTF-8'}"><input type="checkbox" name="productBox[]" value="{$product.id_product|intval}_{$product.id_product_attribute|intval}" class="productOutBox row-selector text-center" /></td>
            <td class="center{$class_bg|escape:'html':'UTF-8'}">{if $product.id_product_attribute}{$product.id_product_attribute|intval}{else}--{/if}</td>
            <td class="text-left{$class_bg|escape:'html':'UTF-8'}">{$product.name|escape:'html':'UTF-8'}</td>
            <td class="text-left{$class_bg|escape:'html':'UTF-8'}">{if $hasAttributes && isset($product.reference)}{$product.reference|escape:'html':'UTF-8'}{/if}</td>
            <td class="center pointer{$class_bg|escape:'html':'UTF-8'}"><a data-id="{$product.id_product|intval}" data-id-pa="{$product.id_product_attribute|intval}" class="button btn btn-default edit_warehouses" href="javascript:void(0);"><i class="icon-home"></i></a></td>
            <td class="center{$class_bg|escape:'html':'UTF-8'}">
                <a class="button btn btn-default warehouses_qty_{$product.id_product|intval}_{$product.id_product_attribute|intval}" id="warehouses_qty">{$product.warehouses_qty_sum|intval}</a> 
                {if $product_asm && $asm}
                <a data-id="{$product.id_product|intval}" data-id-pa="{$product.id_product_attribute|intval}" class="button btn btn-default set_warehouses_stock{if $show_quantities == false} input-disabled{/if}" href="javascript:void(0);"><i class="icon-archive"></i></a>
                {/if}                        
            </td>
            <td class="center{$class_bg|escape:'html':'UTF-8'}"><span class="physical_qty_{$product.id_product|intval}_{$product.id_product_attribute|intval}">{$product.physical_quantity|intval}</span></td>
            <td class="center{$class_bg|escape:'html':'UTF-8'}"><span class="reserved_qty_{$product.id_product|intval}_{$product.id_product_attribute|intval}">{$product.reserved_quantity|intval}</span></td>
            <td class="center pointer{$class_bg|escape:'html':'UTF-8'}">
                <input type="text" 
                name="real_quantity_{$product.id_product|intval}_{$product.id_product_attribute|intval}" 
                id="real_quantity_{$product.id_product|intval}_{$product.id_product_attribute|intval}" 
                data-id="{$product.id_product|intval}" 
                data-id-pa="{$product.id_product_attribute|intval}" 
                value="{$product.stock|intval}" size="2" 
                class="edit_realstock{if $show_quantities == false} input-disabled{/if}">
            </td>
        </tr>
        {/foreach}
    {/if}
    </tbody>
</table>
{if isset($pagination_links) && $pagination_links}
<div class="row">
	<div class="col-lg-3">
    	<div class="pagination">
        	{$pagination_infos|escape:'html':'UTF-8'} <select class="pagination_length">
        		{foreach $pagination_lengths as $limit}
                <option value="{$limit['id']|intval}" {if $pagination_length == $limit['id']}selected{/if}>{$limit['name']|escape:'html':'UTF-8'}</option>
        		{/foreach}
            </select>
    	</div>
    </div>
    <div class="col-lg-9"><ul class="pagination pull-right">{$pagination_links}{* HTML CONTENT *}</ul></div>
</div>
<div class="clear"></div>
{/if}
