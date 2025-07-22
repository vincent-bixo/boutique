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
<div class="form-wrapper">
    <div class="customQtyZone">
        <div class="alert alert-info">
            <strong>{l s='This form let you:' mod='wkwarehouses'}</strong>
            <ul style="list-style:decimal; padding-left:15px">
            	<li>{l s='assign a warehouse to products of not finished orders that have been made before you install our module, thus, take into account the reserved quantities calculation by warehouse' mod='wkwarehouses'}.</li>
            	<li>{l s='assign in bulk warehouse to products' mod='wkwarehouses'}.</li>
            </ul>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-5 text-right" style="padding-top:6px;">{l s='Choose warehouse' mod='wkwarehouses'}</label>
            <div class="col-lg-7">
                <select class="id_warehouse">
                    {if !empty($warehouses) && count($warehouses)}
                        {foreach from=$warehouses item=warehouse}
                        <option value="{$warehouse['id_warehouse']|intval}">{$warehouse['name']|escape:'html':'UTF-8'}</option>
                        {/foreach}
                    {/if}
                </select>
            </div>
        </div>
    	<div class="clearfix">&nbsp;</div>
    	<hr />
    </div>
    <div class="clearfix">&nbsp;</div>
	<hr />
</div>
<br />
<a class="button btn btn-success pull-right" id="submit-assign-warehouse-btn" href="javascript:void(0);">
    <i class="icon-send"></i> {l s='Submit' mod='wkwarehouses'}
</a>
