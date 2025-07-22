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
{if isset($warehouse)}
<div class="panel">
	<h3><i class="icon-cogs"></i> {l s='General information' mod='wkwarehouses'}</h3>
	<div class="form-horizontal">
		<div class="row">
			<label class="control-label col-lg-3">{l s='Reference:' mod='wkwarehouses'}</label>
			<div class="col-lg-9"><p class="form-control-static">{$warehouse->reference|escape:'html':'UTF-8'}</p></div>
		</div>
		<div class="row">
			<label class="control-label col-lg-3">{l s='Name:' mod='wkwarehouses'}</label>
			<div class="col-lg-9"><p class="form-control-static">{$warehouse->name|escape:'html':'UTF-8'}</p></div>
		</div>
		<div class="row">
			<label class="control-label col-lg-3">{l s='Manager:' mod='wkwarehouses'}</label>
			<div class="col-lg-9"><p class="form-control-static">{$employee->lastname|escape:'html':'UTF-8'} {$employee->firstname|escape:'html':'UTF-8'}</p></div>
		</div>
		<div class="row">
			<label class="control-label col-lg-3">{l s='Country:' mod='wkwarehouses'}</label>
			<div class="col-lg-9"><p class="form-control-static">{if $address->country != ''}{$address->country|escape:'html':'UTF-8'}{else}{l s='N/A' mod='wkwarehouses'}{/if}</p></div>
		</div>
		<div class="row">
			<label class="control-label col-lg-3">{l s='Phone:' mod='wkwarehouses'}</label>
			<div class="col-lg-9"><p class="form-control-static">{if $address->phone != ''}{$address->phone|escape:'html':'UTF-8'}{else}{l s='N/A' mod='wkwarehouses'}{/if}</p></div>
		</div>
		<div class="row">
			<label class="control-label col-lg-3">{l s='Product references:' mod='wkwarehouses'}</label>
			<div class="col-lg-9"><p class="form-control-static">{$warehouse_num_products|intval}</p></div>
		</div>
		<div class="row">
			<label class="control-label col-lg-3">{l s='Physical product quantities:' mod='wkwarehouses'}</label>
			<div class="col-lg-9"><p class="form-control-static">{$warehouse_quantities|intval}</p></div>
		</div>
	</div>
</div>
<div class="panel">
	<h3><i class="icon-archive"></i> {l s='Stock' mod='wkwarehouses'}</h3>
	<a class="btn btn-link" style=" text-transform:uppercase" href="{$link->getAdminLink('AdminWkwarehousesManageQty')|escape:'html':'UTF-8'}&id_warehouse={$warehouse->id|intval}">{l s='See products details' mod='wkwarehouses'} <i class="icon-external-link-sign"></i></a>
</div>
{else}
	<div class="panel"><div class="alert alert danger">{l s='This warehouse does not exist' mod='wkwarehouses'}.</div></div>
{/if}
