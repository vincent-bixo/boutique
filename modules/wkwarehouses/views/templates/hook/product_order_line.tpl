{*
* This file is part of the 'Wk Warehouses Management' module feature.
* Developped by Khoufi Wissem (2018).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author		KHOUFI Wissem - K.W
*  @copyright	Khoufi Wissem
*  @license		http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<tr style="background-color:#{if $key % 2}DDE2E6{else}EBECEE{/if};">
    <td style="padding:0.6em 0.4em;">{$product['product_reference']|escape:'html':'UTF-8'}</td>
    <td style="padding:0.6em 0.4em;">
    	<a href="{$url|escape:'html':'UTF-8'}">{$product['product_name']|escape:'html':'UTF-8'}</a>
        {if isset($product['attributes_small'])} {$product['attributes_small']|escape:'html':'UTF-8'}{/if}
        {if !empty($customization_text)}<br />{$customization_text}{* HTML CONTENT *}{/if}
        {if !empty($warehouse_name)}<br /><strong>{l s='Warehouse:' mod='wkwarehouses'} {$warehouse_name|escape:'html':'UTF-8'}</strong>{/if}
    </td>
    <td style="padding:0.6em 0.4em; text-align:right;">{$unit_price|escape:'html':'UTF-8'}</td>
    <td style="padding:0.6em 0.4em; text-align:center;">{$product['product_quantity']|intval}</td>
    <td style="padding:0.6em 0.4em; text-align:right;">{$total_unit_price|escape:'html':'UTF-8'}</td>
</tr>
