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
{assign var="nondon" value="0"}
{foreach $order_details as $order_detail}
  {if $order_detail.product_reference != 'DON'}
    {$nondon = 1}
  {/if}
{/foreach}

{if $nondon == "1"}
*}
{$style_tab}


<table width="100%" id="body" border="0" cellpadding="0" cellspacing="0" style="margin:0;">
	<!-- Addresses -->
	<tr>
		<td colspan="12">

		{$addresses_tab}

		</td>
	</tr>

	<tr>
		<td colspan="12" height="30">&nbsp;</td>
	</tr>

	<tr>
		<td colspan="12">

		{$summary_tab}

		</td>
	</tr>

	<tr>
		<td colspan="12" height="20">&nbsp;</td>
	</tr>

	<!-- Products -->
	<tr>
		<td colspan="12">

		{$product_tab}

		</td>
	</tr>


	<tr>
		<td colspan="12" height="10">&nbsp;</td>
	</tr>

	<tr>
		<td colspan="12">

      <p>MDNProductions est une filiale de l’association Marie de Nazareth.</p>
      <p>Marie de Nazareth est une association de laïcs catholiques qui se sont donnés pour mission de "<i>faire connaître et aimer la Vierge Marie</i>" (St Louis Marie Grignion de Montfort) et de permettre au plus grand nombre de découvrir toute la beauté, la profondeur et la vérité de la foi chrétienne.</p>
      <p>Vous pouvez découvrir ici nos réalisations et propositions&nbsp;: www.mariedenazareth.com</p>
      <p>Vous pouvez découvrir et commander nos publications sur le catalogue ci-joint, sur editions.mariedenazareth.com et dans les librairies religieuses (distribution Salvator)</p>
      <p style="text-align:right;">En Jésus et Marie<br>L'équipe de MDNProductions</p>
		</td>
	</tr>


	<!-- Hook -->
	{if isset($HOOK_DISPLAY_PDF)}
	<tr>
		<td colspan="12" height="30">&nbsp;</td>
	</tr>

	<tr>
		<td colspan="2">&nbsp;</td>
		<td colspan="10">
			{$HOOK_DISPLAY_PDF}
		</td>
	</tr>
	{/if}

</table>

{*/if*}
