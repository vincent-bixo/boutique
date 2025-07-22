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
    <td align="center" class="tr-product"><div class="btn-group pull-right"><a href="{$link_product|escape:'html':'UTF-8'}" class="btn btn-default" target="_blank"><i class="icon-pencil"></i> {l s='Edit Product' mod='wkwarehouses'}</a></div></td>
</tr>
<tr style="display: table-row;">
    <td colspan="11" class="list_combinations" data-id-product="{$product_id|intval}">
        <div class="panel">
            <div class="panel-heading"><a class="button btn btn-info">{if $hasAttributes}<i class="icon-arrow-down"></i> {l s='Combinations' mod='wkwarehouses'} <i class="icon-arrow-down"></i></a><a data-id="{$product_id|intval}" class="button btn btn-danger edit_warehouses_product" href="javascript:void(0);" title="{l s='Manage warehouses associations/locations for all combinations at once' mod='wkwarehouses'}"><i class="icon-home"></i>{else}{l s='This product is a simple and it has no combinations' mod='wkwarehouses'}{/if}</a></div>
            {if Configuration::get('WKWAREHOUSE_PAGINATION_USE')}
            <div class="loading-overlay-{$product_id|intval}"><div class="overlay-content">{l s='Loading' mod='wkwarehouses'}...</div></div>
            {/if}
            <div class="dataContainer{$product_id|intval}">
            	{include file="./list_attributes_table.tpl"}
        	</div>
        </div>
	</td>
</tr>
