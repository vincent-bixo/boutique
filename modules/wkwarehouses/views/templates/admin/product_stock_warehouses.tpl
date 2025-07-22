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
{if count($quantity_locations) > 0}
<div class="advanced_stock_warehouses_table col-xl-12">
    <table class="table">
        <thead>
            <th><strong>{l s='Warehouse' mod='wkwarehouses'}</strong></th>
            <th class="center"><strong>{l s='Quantity' mod='wkwarehouses'}</strong></th>
        </thead>
        <tbody>
        {foreach from=$quantity_locations item=warehouse}
        <tr>
            <td>{$warehouse['name']|escape:'html':'UTF-8'}</td>
            <td class="center"><strong>{$warehouse['quantity']|intval}</strong></td>
        </tr>
        {/foreach}
        </tbody>
    </table>
</div>
{/if}
