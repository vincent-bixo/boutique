{**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
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
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

{*
Ici on insert une ligne s'il y a un don
d'abord on fait le total des dons, puis si > 0 on insert
*}
{assign var="totaldon" value="0"}
{foreach $order_details as $order_detail}
  {if $order_detail.product_reference == 'DON'}
    {$totaldon = $totaldon + $order_detail.unit_price_tax_excl_including_ecotax * $order_detail.product_quantity}
  {/if}
{/foreach}

<table id="total-tab" width="100%">

	<tr>
		<td class="grey" width="50%">
			{l s='Total Products' d='Shop.Pdf' pdf='true'}
		</td>
		<td class="white" width="50%">
			{displayPrice currency=$order->id_currency price=$footer.products_before_discounts_tax_excl - $totaldon}
		</td>
	</tr>

	{if $footer.product_discounts_tax_excl > 0}
    {if $footer.products_before_discounts_tax_excl < $footer.wrapping_tax_excl +.1}
      {if $footer.products_before_discounts_tax_excl > $footer.wrapping_tax_excl -.1}
        <tr>
          <td class="grey" width="50%">
            {l s='Total Discounts' d='Shop.Pdf' pdf='true'}
          </td>
          <td class="white" width="50%">
            - {displayPrice currency=$order->id_currency price=$footer.product_discounts_tax_excl}
          </td>
        </tr>
      {/if}
    {/if}
	{/if}

	{if !$order->isVirtual()}
	<tr>
		<td class="grey" width="50%">
			{l s='Shipping Costs' d='Shop.Pdf' pdf='true'}
		</td>
		<td class="white" width="50%">
			{if $footer.shipping_tax_excl > 0}
				{displayPrice currency=$order->id_currency price=$footer.shipping_tax_excl}
			{else}
				{l s='Free Shipping' d='Shop.Pdf' pdf='true'}
			{/if}
		</td>
	</tr>
	{/if}

	{if $footer.wrapping_tax_excl > 0}
		<tr>
			<td class="grey">
				{l s='Wrapping Costs' d='Shop.Pdf' pdf='true'}
			</td>
			<td class="white">{displayPrice currency=$order->id_currency price=$footer.wrapping_tax_excl}</td>
		</tr>
	{/if}

	<tr class="bold">
		<td class="grey">
			{l s='Total (Tax excl.)' d='Shop.Pdf' pdf='true'}
		</td>
		<td class="white">
			{displayPrice currency=$order->id_currency price=$footer.total_paid_tax_excl - $totaldon}
		</td>
	</tr>
	{if $footer.total_taxes > 0}
	<tr class="bold">
		<td class="grey">
			{l s='Total Tax' d='Shop.Pdf' pdf='true'}
		</td>
		<td class="white">
			{displayPrice currency=$order->id_currency price=$footer.total_taxes}
		</td>
	</tr>
	{/if}

{*
  Ajout du don
  *}
  {if $totaldon > 0}
    <tr class="bold">
      <td class="grey">
        Don
      </td>
      <td class="white">
        {displayPrice currency=$order->id_currency price=$totaldon}
      </td>
    </tr>
  {/if}


	<tr class="bold big">
		<td class="grey">
			Total TTC{*l s='Total' d='Shop.Pdf' pdf='true'*}
		</td>
		<td class="white">
			{displayPrice currency=$order->id_currency price=$footer.total_paid_tax_incl}
		</td>
	</tr>
</table>
