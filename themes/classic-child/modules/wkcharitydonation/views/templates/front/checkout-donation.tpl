{*
* 2010-2018 Webkul.
*
* NOTICE OF LICENSE
*
* All rights is reserved,
* Please go through this link for complete license : https://store.webkul.com/license.html
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to https://store.webkul.com/customisation-guidelines/ for more information.
*
*  @author    Webkul IN <support@webkul.com>
*  @copyright 2010-2018 Webkul IN
*  @license   https://store.webkul.com/license.html
*}

{extends file='checkout/cart.tpl'}

{block name='content'}

	<section id="main">
		<div class="cart-grid row">

		<!-- Left Block: cart product informations & shpping -->
			<div class="cart-grid-body col-xs-12 col-lg-8">

				<!-- cart products detailedX -->
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

								<form class="row donation-block" method="POST" action="{$cart_url}">
									<div class="{if isset($columnLayout) && $columnLayout == 0}col-xs-12{else}col-sm-7{/if}">
										<div class="donation-title">
											<strong>Nous diffusons gratuitement nos livrets missionnaires. Pouvez-vous nous aider dans cette œuvre d’évangélisation par un don&nbsp;?
                      {* {if $checkoutDonation['product_visibility'] == 1}<a href="{$checkoutDonation['link']}" class="label">{/if}{$checkoutDonation['name'][$id_current_lang]}{if $checkoutDonation['product_visibility'] == 1}</a>{/if} *} </strong>
										</div>
										<div class="donation-description">
											{*$checkoutDonation['description'][$id_current_lang] nofilter*}
                      Après déduction fiscale, un don de 50€, par exemple, ne vous coûte que 17€. Il nous permet de <strong>diffuser 500 livrets</strong> et d'annoncer ainsi la beauté et la vérité de la foi chrétienne <strong>à plus de 1000 personnes</strong>. Si vous ne souhaitez pas faire de don, merci d’inscrire le chiffre «&nbsp;0&nbsp;» dans la case ci-contre.
										</div>
									</div>
									<div class="{if isset($columnLayout) && $columnLayout == 0}col-xs-12{else}col-sm-3{/if} donation-price-div">
										<div class="row">
											<div class="col-sm-12">
												{if ($checkoutDonation['price_type']) == 1}
													<strong>{$checkoutDonation['displayPrice']}</strong>
													<input type="hidden" value={$checkoutDonation['id_donation_info']} name="id_donation_info" class="id-donation-info">
												{else}
													<div class="input-group">
														<input type="text" class="input-group form-control donation-price" name="donation_price" value="50">
														<span class="input-group-addon">{$currency_sign}</span>
														<input type="hidden" value={$checkoutDonation['id_donation_info']} name="id_donation_info" class="id-donation-info">
													</div>
												{/if}
											</div>
											<div class="col-sm-12">
												<i><p class="text-danger price-error hide"></p></i>
											</div>
										</div>
									</div>
									<div class="col-sm-2 donation-btn">
										<input type="hidden" name="add-donation-to-cart">
										<button type="submit" class="btn btn-primary btn-sm donation-btn-text submitDonationForm">{l s='DONATE' mod='wkcharitydonation'}</button>
									</div>
								</form>
{*
								{if ($checkoutDonation['price_type']) == 2}
									<div class="donation-note">
									<span class="text-danger">{l s='Note' mod='wkcharitydonation'}: </span>{l s='Minimum amount for this donation is' mod='wkcharitydonation'} {$checkoutDonation['displayPrice']}
									</div>
								{/if}
*}
							{/foreach}
						</div>
					</div>
				{/block}

				{block name='continue_shopping'}
					<a class="label" href="{$urls.pages.index}">
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
