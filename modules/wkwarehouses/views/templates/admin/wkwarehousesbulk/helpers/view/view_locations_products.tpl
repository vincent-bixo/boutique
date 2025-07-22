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
<h3>{l s='Associated warehouses for:' mod='wkwarehouses'}<br />
	<span class="product-name">{$product_name|escape:'html':'UTF-8'}</span>
</h3>
<div class="row">
{if !empty($warehouses_names) && $warehouses_names|@count}
    {foreach from=$warehouses_names item=name}
    <div class="panel">
        <div class="panel-heading">&nbsp;<i class="icon-check"></i> {$name|escape:'html':'UTF-8'}</div>
    </div>
	{/foreach}
{else}
	<div class="alert alert-danger">{l s='No warehouses are associated yet to this product' mod='wkwarehouses'}!</div>
{/if}
</div>
