{*
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    Frederic Moreau
 *  @copyright 2016 BeComWeb
 *  @license   LICENSE.txt
 *}
<div id="atccb_product_overview" class="panel">
	<div class="panel-heading">
		<i class="icon-eye-open"></i> {l s='Product Overview' mod='addtocartcheckbox'}
	</div>
	<div class="panel-body">
		{if isset($data)}
			{* For product with combinations we need to get additional details like attributes texts*}
			<div class="row">
				<div class="atccb_product_img col-xs-4 col-lg-2">
					<img src="{$data.atccb_img_url|escape:'htmlall':'UTF-8'}" class="img-responsive" />
				</div>
				<div class="atccb_product_details col-xs-8 col-lg-10">
					<h4>{$data.atccb_product->name[$id_lang]|escape:'htmlall':'UTF-8'} {if !empty($data.atccb_additional_text)}({$data.atccb_additional_text|escape:'htmlall':'UTF-8'}){/if}</h4>
					<p><span class="label label-info">{l s='Product reference' mod='addtocartcheckbox'} :</span> {$data.atccb_product->reference|escape:'htmlall':'UTF-8'}</p>
					<p><span class="label label-info">{l s='Product short description' mod='addtocartcheckbox'} :</span> {$data.atccb_product->description_short[$id_lang]|strip_tags|escape:'htmlall':'UTF-8'}</p>
					<p><span class="label label-info">{l s='Product unit price' mod='addtocartcheckbox'} :</span> {$data.atccb_product_price_tax_excl|string_format:"%.2f"} {$currency_sign|escape:'htmlall':'UTF-8'} (<small>{l s='does not include taxes or discounts' mod='addtocartcheckbox'}</small>)</p>
				</div>
				{if (!$data.atccb_product_can_be_ordered) || !$data.atccb_product->active || ($data.atccb_product->hasAttributes()|intval > 0)}
				<div id="atccb_admin_warnings" class="alert alert-warning col-xs-12">
					<p>{l s='Warning(s) :' mod='addtocartcheckbox'}</p>
					<ul>
					{if ($data.atccb_product->hasAttributes()|intval > 0)}<li>{l s='This product has combinations, only default combination will be shown to customer.' mod='addtocartcheckbox'}</li>{/if}
					{if !$data.atccb_product_can_be_ordered}<li>{l s='This product cannot be ordered by customer. Either it is not available for order OR it is out of stock and out of stock order is not allowed.' mod='addtocartcheckbox'}</li>{/if}
					{if !$data.atccb_product->active}<li>{l s='This product is inactive, before customer can add it to his cart you must activate it.' mod='addtocartcheckbox'}</li>{/if}
					</ul>
				</div>
				{/if}
			</div>
		{else}
		<div class="alert alert-warning">{l s='No product set, please use the form below to add it.' mod='addtocartcheckbox'}</div>
		{/if}
	</div>
</div>