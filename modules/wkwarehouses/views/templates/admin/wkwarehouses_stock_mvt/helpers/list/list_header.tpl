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
{extends file="helpers/list/list_header.tpl"}

{block name=override_header}
	{if count($list_warehouses) > 0}
    <div class="panel">
        <h3><i class="icon-cogs"></i> {l s='Filters' mod='wkwarehouses'}</h3>
        <div class="filter-stock">
            <form method="get" id="stock-movement-filter" class="form-horizontal">
                <input type="hidden" name="controller" value="AdminWkwarehousesStockMvt" />
                <input type="hidden" name="token" value="{$token|escape:'html':'UTF-8'}" />
                <div class="form-group">
                    <label for="id_warehouse" class="control-label col-lg-3">{l s='Filter movements by warehouse:' mod='wkwarehouses'}</label>
                    <div class="col-lg-9">					
                        <select id="id_warehouse" name="id_warehouse" onchange="$('#stock-movement-filter').submit();">
                            {foreach $list_warehouses as $warehouse}
                                <option value="{$warehouse.id_warehouse|intval}" {if $warehouse.id_warehouse == $current_warehouse}selected="selected"{/if}>{$warehouse.name|escape:'html':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
	{/if}
{/block}
