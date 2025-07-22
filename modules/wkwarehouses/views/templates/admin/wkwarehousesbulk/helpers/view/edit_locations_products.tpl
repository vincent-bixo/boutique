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
<input id="product_id" value="{$product->id|intval}" type="hidden"/>
<h3>{l s='Product location in warehouses' mod='wkwarehouses'}<br />
	<span class="product-name">{$product->name[{$id_lang|intval}]|escape:'html':'UTF-8'}</span>
</h3>
<div class="row">
    <div class="alert alert-info" style="display:block; position:'auto';">
        <p>{l s='This interface allows you to specify the warehouse in which the product is stocked.' mod='wkwarehouses'}</p>
        <p>{l s='You can also specify product/product combinations as it relates to warehouse location.' mod='wkwarehouses'}</p>
    </div>
</div>
<div class="row">
    <div id="warehouse-accordion">
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
                        {foreach $attributes AS $index => $attribute}
                            {assign var=location value=''}
                            {assign var=selected value=''}
                            {foreach from=$associated_warehouses item=aw}
                                {if $aw->id_product == $attribute['id_product'] && $aw->id_product_attribute == $attribute['id_product_attribute'] && $aw->id_warehouse == $warehouse['id_warehouse']}
                                    {assign var=location value=$aw->location}
                                    {assign var=selected value=true}
                                {/if}
                            {/foreach}
                            <tr {if $index is odd}class="alt_row"{/if}>
                                <td class="fixed-width-xs" align="center"><input type="checkbox" class="chk_locations" 
                                    name="check_warehouse_{$warehouse['id_warehouse']|intval}_{$attribute['id_product']|intval}_{$attribute['id_product_attribute']|intval}"
                                    {if $selected == true}checked="checked"{/if}
                                    value="1" />
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
                    {if $attributes|@count gt 1}
                    <button type="button" class="btn btn-default check_all_warehouses" value="check_warehouse_{$warehouse['id_warehouse']|intval}"><i class="icon-check-sign"></i> {l s='Mark / Unmark all product combinations as stored in this warehouse' mod='wkwarehouses'}</button>
                    {/if}
                </div>
            </div>
        {/foreach}
    </div>
</div>
<a class="button btn btn-primary pull-right" style="cursor:pointer" id="save-locations-btn">
    <i class="icon-save"></i> {l s='Save Modifications' mod='wkwarehouses'}
</a>
