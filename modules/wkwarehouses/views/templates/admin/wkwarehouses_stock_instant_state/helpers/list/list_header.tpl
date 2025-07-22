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
    {if isset($show_filter) && $show_filter && count($stock_instant_state_warehouses) > 0}
    <div class="panel">
        <h3><i class="icon-cogs"></i> {l s='Filters' mod='wkwarehouses'}</h3>
        <div class="filter-stock">
            <form id="stock_instant_state" method="get" class="form-horizontal">
                <input type="hidden" name="controller" value="AdminWkwarehousesStockInstantState" />
                <input type="hidden" name="token" value="{$token|escape:'html':'UTF-8'}" />
                <div id="stock_instant_state_form_warehouse" class="form-group">
                    <label for="id_warehouse" class="control-label col-lg-3">{l s='Filter by warehouse:' mod='wkwarehouses'}</label>
                    <div class="col-lg-9">
                        <select id="id_warehouse" name="id_warehouse" onchange="$('#stock_instant_state').submit();">
                            {foreach from=$stock_instant_state_warehouses key=k item=i}
                                <option value="{$i.id_warehouse|intval}" {if $i.id_warehouse == $stock_instant_state_cur_warehouse}selected="selected"{/if}>{$i.name|escape:'html':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
    {/if}
{/block}

