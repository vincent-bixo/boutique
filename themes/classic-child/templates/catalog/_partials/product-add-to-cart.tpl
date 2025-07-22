{**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
<script>
  {literal}
    function sendDon(data) {
      var XHR = new XMLHttpRequest();
      var urlEncodedData = "";
//      var urlEncodedDataPairs = [];
//      var name;
      
      if (document.getElementById('donation-price-input').value > 0) {
        urlEncodedData = 'ajax=true&action=checkMinimumPrice&id_donation=1&addProduct=1&donation_price='
        + document.getElementById('donation-price-input').value
        + '&token=' + ajaxToken;

        XHR.open('POST', origin + '/module/wkcharitydonation/validatedonation');
        XHR.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        XHR.send(urlEncodedData);
  //      https://mesconvictions.com/module/wkcharitydonation/validatedonation?ajax=true&action=checkMinimumPrice&donation_price=10&id_donation=1&addProduct=1&token=c9ad68c1a3d528b141da6ff4f8b1a72f
  //      debugger;
//        console.log(urlEncodedData);
      }
    }
  {/literal}
</script>

<div class="product-add-to-cart">
  {if !$configuration.is_catalog}
    <span class="control-label">{l s='Quantity' d='Shop.Theme.Catalog'}</span>

    {block name='product_quantity'}
      <div class="product-quantity clearfix">
        <div class="qty">
          <input
            type="number"
            name="qty"
            id="quantity_wanted"
            value="{$product.quantity_wanted}"
            class="input-group"
            min="{$product.minimal_quantity}"
            aria-label="{l s='Quantity' d='Shop.Theme.Actions'}"
          >
        {block name='product_availability'}
          <span id="product-availability">
            {if $product.show_availability && $product.availability_message}
              {if $product.availability == 'available'}
                <i class="material-icons rtl-no-flip product-available">&#xE5CA;</i>
              {elseif $product.availability == 'last_remaining_items'}
                <i class="material-icons product-last-items">&#xE002;</i>
              {else}
                <i class="material-icons product-unavailable">&#xE14B;</i>
              {/if}
              {$product.availability_message}
            {/if}
          </span>
        {/block}
        </div>

        {if $product.category_name == "Les livres missionnaires"}
          <!-- champ pour le don -->
          <script>
            {literal}
                function calculdon() {
                  var montantdon = $("#donation-price-input").val();
                  var coutlivre = 0.5;
                  var nbrlivres = montantdon / 0.5;
                  var nbrarrondi = nbrlivres.toFixed();
                  var montantdon1 = parseFloat(montantdon).toFixed();
                  var montantdon2 = parseFloat(montantdon).toFixed(2);
          //        document.getElementById("lemontantdon").innerHTML = montantdon1;
          //        document.getElementById("nbrgratuit").innerHTML = nbrarrondi;
                  if (! montantdon1)
                    document.getElementById("bilandon").innerHTML = "";
                  else if (nbrarrondi == 1)
                    document.getElementById("bilandon").innerHTML = "Avec mon don de " + montantdon1 + "€, 1 exemplaire de ce livre pourra être distribué gratuitement.";
                  else
                    document.getElementById("bilandon").innerHTML = "Avec mon don de " + montantdon1 + "€, " + nbrarrondi + " exemplaires de ce livre pourront être distribués gratuitement.";

                  document.getElementById("coutdon").innerHTML = parseFloat(montantdon1/100*34).toFixed(2);
                }
            {/literal}
          </script>
          <br>
          <div class="input-group">
            <p>Mon don qui permettra de distribuer gratuitement et largement nos livres missionnaires</p>
            <div style="display:inline-table">
              <span class="input-group-addon donation-price-input-addon">Je fais un don en € de</span>
              <input type="number" min="0" form="add-to-cart-or-refresh" class="input-group form-control" id="donation-price-input" name="donation_price" value="0" step="1" placeholder="€" onchange="calculdon()" role="spinbutton" aria-valuemax="100" aria-valuemin="0" aria-valuenow="50" />
            </div>
            <!--p>Avec mon don de <span id="lemontantdon">0</span>€, <span class="nbrgratuit" id="nbrgratuit">0</span> exemplaires de ce livre pourront être distribués gratuitement.</p-->
            <p id="bilandon"></p>
            <p>Je recevrai un reçu fiscal sur le montant de mon don. 66% de mon don est déductible de mon impôt sur le revenu (il me coûtera donc <span id="coutdon">0</span>€).</p>
          </div>
          <input type="hidden" form="add-to-cart-or-refresh" value="1" name="id_donation_info" class="id-donation-info">

          {* Input to refresh product HTML removed, block kept for compatibility with themes *}
          {block name='product_refresh'}{/block}

        {/if}

                  {block name='product_discounts'}
                    {include file='catalog/_partials/product-discounts.tpl'}
                  {/block}


        <div class="add">
          <button
            class="btn btn-primary add-to-cart"
            data-button-action="add-to-cart"
            type="submit"
            {if !$product.add_to_cart_url}
              disabled
            {/if}
            onclick="sendDon()"
          >
            <i class="material-icons shopping-cart">&#xE547;</i>
            {l s='Add to cart' d='Shop.Theme.Actions'}
          </button>
        </div>

                  {block name='product_additional_info'}
                    {include file='catalog/_partials/product-additional-info.tpl'}
                  {/block}

        {hook h='displayProductActions' product=$product}
      </div>
    {/block}

    {block name='product_minimal_quantity'}
      <p class="product-minimal-quantity">
        {if $product.minimal_quantity > 1}
          {l
          s='The minimum purchase order quantity for the product is %quantity%.'
          d='Shop.Theme.Checkout'
          sprintf=['%quantity%' => $product.minimal_quantity]
          }
        {/if}
      </p>
    {/block}
  {/if}
</div>
