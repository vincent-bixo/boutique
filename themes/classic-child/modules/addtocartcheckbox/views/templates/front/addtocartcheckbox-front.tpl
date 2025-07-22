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

{*We show the form if : product can be ordered (see php file for details) *}
{if $data.atccb_product_can_be_ordered}
	{*Product price display : show it with or without taxes depending on group settings*}
	{if $show_atccb_price_with_taxes === 1}
		{assign var=atccb_product_price value=$data.atccb_product_price_tax_excl}
	{else}
		{assign var=atccb_product_price value=$data.atccb_product_price_tax_incl}
	{/if}
<div id="atccb_product_cart" class="card">
	<form id="atccb_form" method="POST" action="{$smarty.server.REQUEST_URI}">
		<div id="atccb_product_infos_cart" class="row">
			<div class="col-xs-12">
				<div class="atccb_product_img col-xs-4 col-md-3">
					<img src="{$data.atccb_img_url}">
				</div>
				<div class="atccb_product_details col-xs-8 col-md-9">
					<p id="atccb_product_name">{$data.atccb_product->name[$id_lang]} {if !empty($data.atccb_additional_text)}({$data.atccb_additional_text|escape:'htmlall':'UTF-8'}){/if}</p>
					{*<p id="atccb_product_text">{$data.atccb_product->description_short[$id_lang]|strip_tags|nl2br}</p>*}
					{*<p id="atccb_product_price_infos">
						{if $atccb_product_price <= 0}
						<span class="atccb_free">{l s='Free !' mod='addtocartcheckbox'}</span>
						{else}
						{l s='Your price :' mod='addtocartcheckbox'}  
						<span class="atccb_product_price">{$atccb_product_price} {$currency_sign} <small>{if $show_atccb_price_with_taxes === 1}{l s='(tax excl.)' mod='addtocartcheckbox'}{else}{l s='(tax incl.)' mod='addtocartcheckbox'}{/if}</small></span> 
						{/if}
					</p>*}
					<p class="checkbox" id="atccb_checkbox_container">
						<label>
							<input type="checkbox" name="atccb_checkbox" id="atccb_checkbox" {if $already_in_cart}checked="checked"{/if} 
							data-remove-url="{if isset($atccb_remove_link)}{$atccb_remove_link}{/if}" 
							data-up-url="{if isset($atccb_add_link)}{$atccb_add_link}{/if}"/>
							{*l s='I would like to add this product to my order' mod='addtocartcheckbox'*}
              Je souhaite recevoir gratuitement le dernier exemplaire de l'Ave&nbsp;Maria
						</label>
					</p>
					<p id="atccb_message_container">
						<span id="atccb_processing_message"><i class="material-icons">autorenew</i> {l s='Please wait while updating your cart' mod='addtocartcheckbox'}</span>
						<span id="atccb_add_success_message"><i class="material-icons">done</i> {l s='Product has been added to your cart. This page will now refresh. Please wait' mod='addtocartcheckbox'}</span>
						<span id="atccb_delete_success_message"><i class="material-icons">done</i> {l s='Product has been deleted from your cart. This page will now refresh. Please wait' mod='addtocartcheckbox'}</span>
						<span id="atccb_error_message"><i class="material-icons">error</i> {l s='An error occured while updating your cart. This page will now refresh. Please wait' mod='addtocartcheckbox'}</span>
					</p>
				</div>
			</div>
		</div>
	</form>
</div>
{/if}
