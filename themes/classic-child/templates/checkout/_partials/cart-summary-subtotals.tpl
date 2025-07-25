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
{*
{assign var="totaldon" value="0"}
{foreach $order_details as $order_detail}
  {if $order_detail.product_reference == 'DON'}
    {$totaldon = $totaldon + $order_detail.unit_price_tax_excl_including_ecotax * $order_detail.product_quantity}
  {/if}
{/foreach}
*}

<div class="card-block cart-summary-subtotals-container">

  {foreach from=$cart.subtotals item="subtotal"}
    {if $subtotal.value && $subtotal.type !== 'tax'}
      {if $subtotal.type && $subtotal.type !== 'gift_wrapping'}
        <div class="cart-summary-line cart-summary-subtotals" id="cart-subtotal-{$subtotal.type}">
          <span class="label">
  {*
            {if $subtotal.label == 'Sous-total'}
              Sous-total produits
            {else}
  *}
              {$subtotal.label}
  {*
            {/if}
  *}
          </span>

          <span class="value">
            {$subtotal.value}
  {*
            {$subtotal.value - $totaldon}
  *}
          </span>
        </div>
      {/if}
    {/if}
  {/foreach}
{*
  {if $totaldon > 0}
    <div class="cart-summary-line cart-summary-subtotals" id="cart-subtotal-don">
      <span class="label">
          Votre don
      </span>
      <span class="value">
        {$totaldon}
      </span>
    </div>
  {/if}
*}
</div>

