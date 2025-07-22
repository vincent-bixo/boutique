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
<section class="product-discounts">
  {if $product.quantity_discounts}
    {foreach from=$product.quantity_discounts item='quantity_discount' name='quantity_discounts'}
      <p>Avec {$quantity_discount.quantity} livres achetés, bénéficiez d'une remise de {math equation="reduction*100" reduction=$quantity_discount.reduction format="%.0f"}%
      </p>
{*
      <p>Avec {$quantity_discount.quantity} livres achetés, bénéficiez d'une remise de 5% et de la gratuité des frais de port en France.<br>La remise sur les frais de port sera appliquée après la saisie de votre&nbsp;adresse.
      </p>
*}
    {/foreach}
{*
    <p class="h6 product-discounts-title">{l s='Volume discounts' d='Shop.Theme.Catalog'}</p>
    {block name='product_discount_table'}
      <table class="table-product-discounts">
        <thead>
        <tr>
          <th>{l s='Quantity' d='Shop.Theme.Catalog'}</th>
          <th>{$configuration.quantity_discount.label}</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$product.quantity_discounts item='quantity_discount' name='quantity_discounts'}
          <tr data-discount-type="{$quantity_discount.reduction_type}" data-discount="{$quantity_discount.real_value}" data-discount-quantity="{$quantity_discount.quantity}">
            <td>{$quantity_discount.quantity}</td>
            <td>
              {math equation="ttc/(ttc-reduction)*100 -100" ttc=$product.price_without_reduction reduction=$quantity_discount.reduction format="%.0f"}%
            </td>
          </tr>
        {/foreach}
        </tbody>
      </table>
    {/block}
*}
  {/if}
  
  {if ! $product.is_virtual}
    <p>En France, à&nbsp;partir&nbsp;de&nbsp;35€, bénéficiez&nbsp;des&nbsp;frais&nbsp;de&nbsp;port&nbsp;à&nbsp;1€. La&nbsp;remise&nbsp;sera&nbsp;appliquée après&nbsp;la&nbsp;saisie de&nbsp;votre&nbsp;adresse</p>
  {/if}
</section>
