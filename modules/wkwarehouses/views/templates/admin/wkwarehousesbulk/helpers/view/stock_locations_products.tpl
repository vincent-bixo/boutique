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
<h3>{l s='Stock in warehouses' mod='wkwarehouses'}<br />
	<span class="product-name">{$product->name[{$id_lang|intval}]|escape:'html':'UTF-8'}</span>
</h3>
<div class="row">
    <div class="alert alert-info" style="display:block; position:'auto';">
        <p>{l s='This interface allows you to see and check the quantities in each warehouse for each product/combinations' mod='wkwarehouses'}.</p>
    </div>
</div>
<div class="row">
    {if !$use_asm}
    <div class="alert alert-danger">
        <p>{l s='The quantities of this product are not based on stock in your warehouses (not using advanced stock management system)' mod='wkwarehouses'}.</p>
    </div>
    {else}
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
                                <th class="text-center"><span class="title_box">{l s='Available quantity' mod='wkwarehouses'}</span></th>
                                <th class="text-center"><span class="title_box">{l s='Physical quantity' mod='wkwarehouses'}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        {foreach $attributes as $index => $attribute}
                            {assign var=physical_quantity value=0}
                            {assign var=available_quantity value=0}
                            {foreach from=$stocks item=stock}
                                {if $stock['id_product'] == $attribute['id_product'] &&
                                	$stock['id_product_attribute'] == $attribute['id_product_attribute'] &&
                                    $stock['id_warehouse'] == $warehouse['id_warehouse']}
                                    {assign var=physical_quantity value=$stock['physical_quantity']}
                                    {if isset($stock['available_quantity'])}
                                    {assign var=available_quantity value=$stock['available_quantity']}
                                    {/if}
                                {/if}
                            {/foreach}
                            <tr {if $index is odd}class="alt_row"{/if}>
                                <td class="fixed-width-xs" align="center"><i class="icon-check{if $physical_quantity <= 0}-empty{/if}"></i></td>
                                <td>{$product_designation[$attribute['id_product_attribute']]|escape:'html':'UTF-8'}</td>
                                <td class="text-center"><span class="badge badge-{if $available_quantity > 0}success{else}danger{/if}">{$available_quantity|intval}</span></td>
                                <td class="text-center"><span class="badge badge-{if $physical_quantity > 0}success{else}danger{/if}">{$physical_quantity|intval}</span></td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
            </div>
        {/foreach}
    </div>
    {/if}
</div>
