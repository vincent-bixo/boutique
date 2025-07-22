{*
* Order Fees Shipping
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*}
<tr id="city_rule_{$city_rule_group_id|intval}_{$city_rule_id|intval}_tr">
	<td>
		<input type="hidden" name="city_rule_{$city_rule_group_id|intval}[]" value="{$city_rule_id|intval}" />
		<input type="hidden" name="city_rule_{$city_rule_group_id|intval}_{$city_rule_id|intval}_type" value="{$city_rule_type|escape}" />
		{l s='Cities : %s' mod='orderfees_shipping' sprintf=Country::getNameById($id_lang, $city_rule_type)}
	</td>
	<td>
		<input class="form-control" type="text" name="city_rule_{$city_rule_group_id|intval}_{$city_rule_id|intval}_value" value="{if isset($value)}{$value|escape:'quotes':'UTF-8'}{/if}" />
        </td>
	<td class="text-right">
		<a class="btn btn-default" href="javascript:removeCityRule({$city_rule_group_id|intval}, {$city_rule_id|intval});">
			<i class="icon-remove"></i>
		</a>
	</td>
</tr>