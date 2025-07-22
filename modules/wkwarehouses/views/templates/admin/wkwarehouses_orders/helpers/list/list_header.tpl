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
<script type="text/javascript">
	var txt_warehouse_required = '{l s='The warehouse selection is required!' js=1 mod='wkwarehouses'}';
	var product_selection_required = '{l s='Please select at least one product!' js=1 mod='wkwarehouses'}';
</script>
<div class="panel" id="help-header" style="padding-bottom:0px">    
	<div class="panel-heading"><i class="icon-info-circle"></i> {l s='What does this section do' mod='wkwarehouses'} ? <input type="checkbox" id="orders-warehouses-help" name="orders-warehouses-help" style="vertical-align: text-top"/></div>
    <div class="expandible_content">
    	<div class="col-lg-2" style="width:10%">
        	<img src="{$this_path|escape:'html':'UTF-8'}/views/img/assign-order.png" width="90" />
        </div>
        <ul class="col-lg-9">
            <li>{l s='This page allows you to list the products purchased for each customer order made on your shop so that you can quickly assign a warehouse for each one' mod='wkwarehouses'}.</li>
            <li>{l s='A drop-down list containing the associated warehouses list is displayed in front of each product using advanced stock management' mod='wkwarehouses'}.</li>
            <li>{l s='The warehouses list are loaded and filtered according to the [1]assigned order carrier[/1]' tags=['<strong>'] mod='wkwarehouses'}.</li>
            <li>{l s='Use the filter from the panel below to list orders products by [1]warehouse[/1] or by [1]product (ID, reference or name)[/1]' tags=['<strong>'] mod='wkwarehouses'}.</li>
        </ul>
        <div class="clearfix"></div>
    </div>
</div>
<div class="clearfix"></div>

<div class="panel filters_orders_warehouses">
	<form method="post" action="{$action|escape:'htmlall':'UTF-8'}" class="form-horizontal clearfix">
		<div class="panel-heading">
        	<i class="icon-search"></i> {l s='Filter by:' mod='wkwarehouses'}
        </div>
		<div class="row">
            <div class="col-lg-5">
				{******* W A R E H O U S E S *******}
                <div class="col-lg-12">
                    <div class="col-lg-1 filter_label">{l s='Warehouse:' mod='wkwarehouses'}</div>
                    <div class="col-lg-10">
                        <select name="{$list_id|escape:'html':'UTF-8'}{$filter_warehouse|escape:'htmlall':'UTF-8'}" id="{$list_id|escape:'html':'UTF-8'}{$filter_warehouse|escape:'htmlall':'UTF-8'}">
                            <option value="">{l s='Choose warehouse' mod='wkwarehouses'}</option>
                            {if !empty($warehouses) && count($warehouses)}
                                {foreach from=$warehouses item=warehouse}
                                <option value="{$warehouse['id_warehouse']|intval}" {if $warehouse['is_selected']}selected{/if}>{$warehouse['name']|escape:'html':'UTF-8'}</option>
                                {/foreach}
                            {/if}
                        </select>
                    </div>
                </div>
                <div class="clearfix"></div>
				{******* W A R E H O U S E   S T A T U S *******}
                <div class="col-lg-12" style="margin-top:5px; margin-bottom:-10px">
                    <div class="form-group">
                        <label class="control-label col-lg-8">{l s='Display only products without warehouse association' mod='wkwarehouses'}</label>
                        <div class="col-lg-3">
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input type="radio" name="{$list_id|escape:'html':'UTF-8'}Filter_warehouse_status" id="warehouse_status_on" value="1" {if isset($smarty.request.orderFilter_warehouse_status) && $smarty.request.orderFilter_warehouse_status == 1}checked="checked"{/if} />
                                <label for="warehouse_status_on">
                                    {l s='Enabled' mod='wkwarehouses'}
                                </label>
                                <input type="radio" name="{$list_id|escape:'html':'UTF-8'}Filter_warehouse_status" id="warehouse_status_off" value="0" {if !isset($smarty.request.orderFilter_warehouse_status) || (isset($smarty.request.orderFilter_warehouse_status) && $smarty.request.orderFilter_warehouse_status == 0)}checked="checked"{/if} /> 
                                <label for="warehouse_status_off">
                                    {l s='Disabled' mod='wkwarehouses'}
                                </label>
                                <a class="slide-button btn"></a>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>                
				{******* O R D E R   S T A T U S *******}
                <div class="col-lg-12" style="margin-top:5px;">
                    <div class="col-lg-1 filter_label">{l s='Order:' mod='wkwarehouses'}</div>
                    <div class="col-lg-10">
                        <select name="{$list_id|escape:'html':'UTF-8'}Filter_order_status" id="{$list_id|escape:'html':'UTF-8'}Filter_order_status">
                            <option value="">{l s='All orders' mod='wkwarehouses'}</option>
                            <option value="1" {if isset($smarty.request.orderFilter_order_status) && $smarty.request.orderFilter_order_status == 1}selected{/if}>{l s='List orders that not yet delivered and shipped' mod='wkwarehouses'}</option>
                        </select>
                    </div>
                </div>
                <div class="clearfix"></div>
				{******* PRODUCT AUTOCOMPLETE SEARCH *******}
                <div class="col-lg-12" style="margin-top:5px;">
                    <div class="col-lg-1 filter_label">{l s='Product:' mod='wkwarehouses'}</div>
                    <div class="col-lg-10">
                        <input type="text" 
                            name="{$list_id|escape:'html':'UTF-8'}Filter_product_q" 
                            class="product_order_autocomplete_input" 
                            placeholder="{l s='Search by product' mod='wkwarehouses'}" 
                            value="{if isset($smarty.request.orderFilter_product_q) && $smarty.request.orderFilter_product_q}{$smarty.request.orderFilter_product_q|escape:'html':'UTF-8'}{/if}" 
                            autocomplete="off">
                        <p class="help-block">{l s='Start by typing the first letters of the product (ID, reference or name), then select the product from the drop-down list' mod='wkwarehouses'}.</p>
                    </div>
                </div>
                <div class="clearfix"></div>
				{******* C O M B I N A T I O N *******}
                {if Combination::isFeatureActive()}
                <div class="col-lg-12 combinations_filter" style="margin-top:5px; {if !count($combinations)}display:none{/if}">
                    <div class="col-lg-1 filter_label">{l s='Combination:' mod='wkwarehouses'}</div>
                    <div class="col-lg-10">
                        <select name="{$list_id|escape:'html':'UTF-8'}Filter_combination_q" id="{$list_id|escape:'html':'UTF-8'}Filter_combination_q">
                            {if isset($combinations) && !empty($combinations) && count($combinations)}
                            	<option value="">{l s='All combinations' mod='wkwarehouses'}</option>
                                {foreach from=$combinations item=combination}
                                <option value="{$combination['id_product_attribute']|intval}" {if $combination['is_selected']}selected{/if}>{$combination['attributes']|escape:'html':'UTF-8'}</option>
                                {/foreach}
                            {/if}
                        </select>
                    </div>
                </div>
                <div class="clearfix"></div>
                {/if}
            </div>
			{***************************** BUTTONS ACTIONS IF FILTER IS USED ******************************}
            <div class="col-lg-4">
                <span class="pull-left">
                    {if $is_warehouse_filter || (isset($smarty.request.orderFilter_product_q) && $smarty.request.orderFilter_product_q) || (isset($smarty.request.orderFilter_order_status) && $smarty.request.orderFilter_order_status)}
                    <button type="submit" name="submitReset{$list_id|escape:'html':'UTF-8'}" class="btn btn-warning">
                        <i class="icon-eraser"></i> {l s='Reset' mod='wkwarehouses'}
                    </button>
                    {/if}
                    {* Search must be before reset for default form submit *}
                    <button type="submit" name="submitFilter" class="btn btn-default" data-list-id="{$list_id|escape:'html':'UTF-8'}">
                        <i class="icon-search"></i> {l s='Search' mod='wkwarehouses'}
                    </button>
                </span>
            </div>
		</div>
	</form>
</div>
<div class="clearfix"></div>
{/block}
