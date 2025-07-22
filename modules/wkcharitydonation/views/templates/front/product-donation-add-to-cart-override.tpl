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

{if isset($isDonationProduct) && $isDonationProduct}
  <style>
    .product-add-to-cart {
      display: none;
    }

    .qty {
      display: none !important;
    }

    .control-label {
      display: none !important;
    }

    .donation-add-to-cart {
      margin-top: 20px;
    }
  </style>


<div id="quickview-modal-{$product.id|escape:'html':'UTF-8'}-{$product.id_product_attribute|escape:'html':'UTF-8'}" class="modal fade quickview" tabindex="-1"
  role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 col-sm-6 hidden-xs-down">
            {block name='product_cover_tumbnails'}
              {include file='catalog/_partials/product-cover-thumbnails.tpl'}
            {/block}
            <div class="arrows js-arrows">
              <i class="material-icons arrow-up js-arrow-up">&#xE316;</i>
              <i class="material-icons arrow-down js-arrow-down">&#xE313;</i>
            </div>
          </div>
          <div class="col-md-6 col-sm-6">
            <h1 class="h1">{$product.name|escape:'html':'UTF-8'}</h1>
            {block name='product_prices'}
              {include file='catalog/_partials/product-prices.tpl'}
            {/block}
            {block name='product_description_short'}
              <div id="product-description-short" itemprop="description">{$product.description_short nofilter}</div>
            {/block}
            {* File include for added add to cart feature on quick view *}
            {include file='./../hook/product-price-block.tpl'}

            {block name='product_buy'}
              <div class="product-actions">
                <form action="{$urls.pages.cart|escape:'html':'UTF-8'}" method="post" id="add-to-cart-or-refresh">
                  <input type="hidden" name="token" value="{$static_token|escape:'html':'UTF-8'}">
                  <input type="hidden" name="id_product" value="{$product.id|escape:'html':'UTF-8'}" id="product_page_product_id">
                  <input type="hidden" name="id_customization" value="{$product.id_customization|escape:'html':'UTF-8'}"
                    id="product_customization_id">
                  {block name='product_variants'}
                    {include file='catalog/_partials/product-variants.tpl'}
                  {/block}

                  {block name='product_add_to_cart'}
                    {include file='catalog/_partials/product-add-to-cart.tpl'}
                  {/block}

                  {block name='product_refresh'}
                    <input class="product-refresh" data-url-update="false" name="refresh" type="submit"
                      value="{l s='Refresh' mod='wkcharitydonation'}" hidden>
                  {/block}
                </form>
              </div>
              {hook h='displayProductButtons' product=$product}
            {/block}
          </div>
        </div>
      </div>
      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>
{/if}