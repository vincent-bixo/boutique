{**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your needs
* please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

{extends file='checkout/cart.tpl'}
XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
{block name='content'}

	<section id="main">
		<div class="cart-grid row">

		<!-- Left Block: cart product informations & shpping -->
			<div class="cart-grid-body col-xs-12 col-lg-8">

				<!-- cart products detailed -->
				<div class="card cart-container">
					<div class="card-block">
						<h1 class="h1">{l s='Shopping Cart' mod='wkcharitydonation'}</h1>
					</div>
					<hr class="separator">
					{block name='cart_overview'}
						{include file='checkout/_partials/cart-detailed.tpl' cart=$cart}
					{/block}
				</div>

				{block name="charity_and_donation"}
					<div class="card">
						<div class="card-block">
							<h1 class="h1">{l s='DONATIONS AND CHARITY' mod='wkcharitydonation'}</h1>
						</div>
						<hr class="separator">
						<div class="charity-block">
							{foreach $checkoutDonations as $checkoutDonation}

								<form class="row donation-block" method="POST" action="{$cart_url|escape:'html':'UTF-8'}">
									<div class="col-xs-12">
										<div class="donation-title">
											<strong>{if $checkoutDonation['product_visibility'] == 1}<a href="{$checkoutDonation['link']|escape:'html':'UTF-8'}" class="label">{/if}{$checkoutDonation['name'][$id_current_lang]|escape:'html':'UTF-8'}{if $checkoutDonation['product_visibility'] == 1}</a>{/if}</strong>
										</div>
										<div class="donation-description">
											{$checkoutDonation['description'][$id_current_lang] nofilter}
										</div>
									</div>
									<div class="col-xs-12 col-sm-9" donation-price-div">
										<div class="row">
											<div class="col-xs-12">
												{if ($checkoutDonation['price_type']) == 1}
													<strong>{$checkoutDonation['displayPrice']|escape:'html':'UTF-8'}</strong>
													<input type="hidden" value={$checkoutDonation['id_donation_info']|escape:'html':'UTF-8'} name="id_donation_info" class="id-donation-info">
												{else}
													<div class="input-group">
														<span class="input-group-addon">{$currency_sign|escape:'html':'UTF-8'}</span>
														<input type="text" class="input-group form-control donation-price" name="donation_price" value="22">
														<input type="hidden" value={$checkoutDonation['id_donation_info']|escape:'html':'UTF-8'} name="id_donation_info" class="id-donation-info">
													</div>
												{/if}
											</div>
											<div class="col-xs-12">
												<i><p class="text-danger price-error hide"></p></i>
											</div>
										</div>
									</div>
									<div class="col-xs-12 col-sm-3 donation-btn">
										<input type="hidden" name="add-donation-to-cart">
										<button type="submit" class="btn btn-primary btn-xs donation-btn-text submitDonationForm">{l s='DONATE' mod='wkcharitydonation'}</button>
									</div>
								</form>
								{if ($checkoutDonation['price_type']) == 2}
									<div class="donation-note">
									<span class="text-danger">{l s='Note' mod='wkcharitydonation'}: </span>{l s='Minimum amount for this donation is' mod='wkcharitydonation'} {$checkoutDonation['displayPrice']|escape:'html':'UTF-8'}
									</div>
								{/if}
							{/foreach}
						</div>
					</div>
				{/block}

				{block name='continue_shopping'}
					<a class="label" href="{$urls.pages.index|escape:'html':'UTF-8'}">
						<i class="material-icons">chevron_left</i>{l s='Continue shopping' mod='wkcharitydonation'}
					</a>
				{/block}

				<!-- shipping informations -->
				{block name='hook_shopping_cart_footer'}
					{hook h='displayShoppingCartFooter'}
				{/block}
			</div>

			<!-- Right Block: cart subtotal & cart total -->
			<div class="cart-grid-right col-xs-12 col-lg-4">

				{block name='cart_summary'}
					<div class="card cart-summary">

						{block name='hook_shopping_cart'}
							{hook h='displayShoppingCart'}
						{/block}

						{block name='cart_totals'}
							{include file='checkout/_partials/cart-detailed-totals.tpl' cart=$cart}
						{/block}

						{block name='cart_actions'}
							{include file='checkout/_partials/cart-detailed-actions.tpl' cart=$cart}
						{/block}

					</div>
				{/block}

				{block name='hook_reassurance'}
					{hook h='displayReassurance'}
				{/block}

			</div>

		</div>
	</section>
{/block}