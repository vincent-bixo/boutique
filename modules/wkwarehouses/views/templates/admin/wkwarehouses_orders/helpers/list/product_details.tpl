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
	</td>
    <td class="text-right tbody-order"><div class="btn-group pull-right"><a href="{$order_link|escape:'html':'UTF-8'}" class="btn btn-default" target="_blank"><i class="icon-search-plus"></i> {l s='View' mod='wkwarehouses'}</a></div></td>
</tr>
<tr style="display: table-row;">
	<td colspan="11" style="border:none!important; border-bottom:5px solid #a0d0eb!important; padding-left:6%;">
		<table id="product_details" class="table tableDnD" cellpadding="0" cellspacing="0">
            <thead>
                <tr class="nodrag nodrop">
                    <th width="1%"></th>
                    <th width="4%">{l s='ID' mod='wkwarehouses'}</th>
                    <th width="35%">{l s='Product name' mod='wkwarehouses'}</th>
                    <th><strong>{l s='Warehouse' mod='wkwarehouses'}</strong></th>
                    <th class="text-center">{l s='In stock' mod='wkwarehouses'}</th>
                    <th class="text-center">{l s='Ordered Quantity' mod='wkwarehouses'}</th>
                    <th class="text-center">{l s='Reserved Stock' mod='wkwarehouses'}</th>
                    <th class="text-center">{l s='Refunded Quantity' mod='wkwarehouses'}</th>
                </tr>
            </thead>
            <tbody>
            {foreach $products as $product}
                <tr>
            		<td>{if $product.advanced_stock_management}<input type="checkbox" name="productBox[]" value="{$product.id_order_detail|intval}" class="productOutBox row-selector text-center" />{/if}</td>
                    <td>{$product.id_product|intval}</td>
                    <td><a href="{$product['product_link']|escape:'html':'UTF-8'}" target="_blank" title="{l s='Edit Product' mod='wkwarehouses'}">{$product.product_name|escape:'html':'UTF-8'}</a></td>
                    <td>
                        {** Warehouses list **}
                        {if $use_asm && isset($product['warehouses_list'])}
                        <select id="warehouse_{$product['id_order_detail']|intval}" class="warehouse-select">
                            <option value="">{l s='Select warehouse' mod='wkwarehouses'}</option>
                            {foreach from=$product['warehouses_list'] item='warehouse'}
                            <option value="{$warehouse['id_warehouse']|intval}" {if isset($warehouse['is_default']) && $warehouse['is_default']}selected="selected"{/if}>
                            {$warehouse['name']|escape:'html':'UTF-8'}
                            </option>
                            {/foreach}
                        </select>
                    {/if}
                    </td>
                    <td class="text-center"><span class="badge badge-{if $product.in_stock <= 0}danger{else}success{/if}">{$product.in_stock|intval}</span></td>
                    <td class="text-center">{$product.product_quantity|intval}</td>
                    <td class="text-center">{if $product.product_quantity == $product.product_quantity_refunded}0{else}{math equation="x - y" x=$product.product_quantity_in_stock y=$product.product_quantity_refunded}{/if}</td>
                    <td class="text-center">{$product.product_quantity_refunded|intval}</td>
                </tr>
            {/foreach}
            </tbody>
		</table>
	</td>
</tr>
