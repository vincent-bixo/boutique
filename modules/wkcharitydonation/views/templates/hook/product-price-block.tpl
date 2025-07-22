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

{block name="price-by-customer"}
    {if !$configuration.is_catalog}
        {if $price_type == $price_by_customer}
            <div class="row donation-price-block">
                <div class="col-xs-12 col-md-10 col-lg-7">
                    <div class="input-group">
                        <span class="input-group-addon donation-price-input-addon">{$currency_sign|escape:'html':'UTF-8'}</span>
                        <input type="text" form="add-to-cart-or-refresh" class="input-group form-control" id="donation-price-input" name="donation_price" value="{$minimum_price|escape:'html':'UTF-8'}" placeholder="{$minimum_price|escape:'html':'UTF-8'}">
                    </div>
                </div>
                <i><p class="col-xs-12 text-danger price-error hide"></p></i>
                <div class="col-xs-12 text-muted" id="donation-price-note">
                    {l s='You can’t donate less than ' mod='wkcharitydonation'}{$currency_sign|escape:'html':'UTF-8'}{$minimum_price|escape:'html':'UTF-8'}.
                </div>
            </div>
            <hr id="donation-block-seperator">
        {/if}
        <input type="hidden" form="add-to-cart-or-refresh" value={$id_donation_info|escape:'html':'UTF-8'} name="id_donation_info" class="id-donation-info">
        <button type="button" class="btn btn-primary donation-add-to-cart" {if !$product.add_to_cart_url}disabled{/if}>
            <i class="material-icons shopping-cart">&#xE547;</i>
            {l s='Add to cart' mod='wkcharitydonation'}
        </button>
    {/if}
{/block}

{block name='product_availability'}
    <div>
        <span id="product-availability">
        {if $product.show_availability && $product.availability_message}
            {if $product.availability == 'available'}
            <i class="material-icons rtl-no-flip product-available">&#xE5CA;</i>
            {elseif $product.availability == 'last_remaining_items'}
            <i class="material-icons product-last-items">&#xE002;</i>
            {else}
            <i class="material-icons product-unavailable">&#xE14B;</i>
            {/if}
            {$product.availability_message|escape:'html':'UTF-8'}
        {/if}
        </span>
    </div>
{/block}