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
{if isset($product->id)}
    <input id="product_id" value="{$product->id|intval}" type="hidden"/>
    {if !$forall && isset($id_product_attribute)}<input id="product_attribute_id" value="{$id_product_attribute|intval}" type="hidden"/>{/if}
    <h3>{l s='Product location in warehouses' mod='wkwarehouses'} >> {$product->name[{$id_lang|intval}]|escape:'html':'UTF-8'}</h3>
    <div class="row">
        <div class="alert alert-info" style="display:block; position:'auto';">
			<p>{l s='This interface allows you to specify the warehouse in which the product/combination is stocked' mod='wkwarehouses'}.</p>
            {if $forall}<p>{l s='You can also specify product/product combinations as it relates to warehouse location.' mod='wkwarehouses'}</p>{/if}
        </div>
    </div>
	{if $warehouses|@count > 0}
    <div class="row">
        <div class="panel-group asm_management_table" id="warehouse-accordion">
            {foreach from=$warehouses item=warehouse name=data}
                <div class="panel panel-default">
                    <div class="panel-heading">
                         <a class="accordion-toggle" data-toggle="collapse" data-parent="#warehouse-accordion" href="#warehouse-{$warehouse['id_warehouse']|intval}">{$warehouse['name']|escape:'html':'UTF-8'}</a>
                    </div>
                    <div id="warehouse-{$warehouse['id_warehouse']|intval}">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="fixed-width-xs" align="center"><span class="title_box">{l s='Stored' mod='wkwarehouses'}</span></th>
                                    <th><span class="title_box">{l s='Product' mod='wkwarehouses'}</span></th>
                                    <th><span class="title_box">{l s='Location (optional)' mod='wkwarehouses'}</span></th>
                                </tr>
                            </thead>
                            <tbody>
                            {foreach $attributes as $index => $attribute}
                            	{assign var=location value=''}
                            	{assign var=selected value=''}
                                {foreach from=$associated_warehouses item=aw}
                                    {if $aw->id_product == $attribute['id_product'] && $aw->id_product_attribute == $attribute['id_product_attribute'] && $aw->id_warehouse == $warehouse['id_warehouse']}
                                        {assign var=location value=$aw->location}
                                        {assign var=selected value=true}
                                        {if isset($reserved_warehouses[$aw->id_product_attribute]) && $reserved_warehouses[$aw->id_product_attribute] == $aw->id_warehouse}
                                        {assign var=reserved value=true}
                                    	{/if}
                                    {/if}
                                {/foreach}
                                <tr {if $index is odd}class="alt_row"{/if}>
                                    <td class="fixed-width-xs" align="center"><input type="checkbox" class="chk_locations" 
                                        name="check_warehouse_{$warehouse['id_warehouse']|intval}_{$attribute['id_product']|intval}_{$attribute['id_product_attribute']|intval}"
                                        {if $selected == true}checked="checked"{/if}
                                        value="1" {if $selected && isset($reserved) && $reserved}onclick="showErrorMessage('{l s='Action not allowed: product already reserved in this warehouse.' js=1 mod='wkwarehouses'}'); return false;"{/if} />
                                    </td>
                                    <td>{$product_designation[$attribute['id_product_attribute']]|escape:'html':'UTF-8'}</td>
                                    <td><input type="text" class="input_locations"
                                        name="location_warehouse_{$warehouse['id_warehouse']|intval}_{$attribute['id_product']|intval}_{$attribute['id_product_attribute']|intval}"
                                        value="{$location|escape:'html':'UTF-8'}"
                                        size="20" />
                                    </td>
                                </tr>
                            {/foreach}
                        </table>
                        {if $forall && $attributes|@count gt 1}
                        <button type="button" class="btn btn-default check_all_warehouses" value="check_warehouse_{$warehouse['id_warehouse']|intval}"><i class="icon-check-sign"></i> {l s='Mark / Unmark all combinations as stored in this warehouse' mod='wkwarehouses'}</button>
                        {/if}
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
    <a class="button btn btn-primary css_btn" style="cursor:pointer" id="save-warehouses-locations-{if $forall}forall{else}foreach{/if}">
        <i class="icon-save"></i>&nbsp;{l s='Save Modifications' mod='wkwarehouses'}
    </a>
    {else}
        <div class="alert alert-warning" role="alert">
          <p class="alert-text">
            - {l s='You must create at least one warehouse' mod='wkwarehouses'}!
          </p>
        </div>
    {/if} 
{/if}
