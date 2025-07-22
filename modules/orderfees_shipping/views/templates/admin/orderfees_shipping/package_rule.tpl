{*
* Order Fees Shipping
*
*  @author    motionSeed <ecommerce@motionseed.com>
*  @copyright 2017 motionSeed. All rights reserved.
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*}
<tr id="package_rule_{$package_rule_group_id|intval}_{$package_rule_id|intval}_tr">
	<td class="col-lg-3">
		<input type="hidden" name="package_rule_{$package_rule_group_id|intval}[]" value="{$package_rule_id|intval}" />
		<input type="hidden" name="package_rule_{$package_rule_group_id|intval}_{$package_rule_id|intval}_range_start" value="{$range_start|escape:'html':'UTF-8'}" />
                <input type="hidden" name="package_rule_{$package_rule_group_id|intval}_{$package_rule_id|intval}_range_end" value="{$range_end|escape:'html':'UTF-8'}" />

                {$range_start|floatval} {$unit_weight|escape:'html':'UTF-8'} {l s='to' mod='orderfees_shipping'} {$range_end|floatval} {$unit_weight|escape:'html':'UTF-8'}
	</td>
        <td class="col-lg-1">
            <div>
		<input type="text" name="package_rule_{$package_rule_group_id|intval}_{$package_rule_id|intval}_round" value="{if isset($round)}{$round|escape:'html':'UTF-8'}{/if}" />
            </div>
        </td>
        <td class="col-lg-1">
            <div>
		<input type="text" name="package_rule_{$package_rule_group_id|intval}_{$package_rule_id|intval}_divider" value="{if isset($divider)}{$divider|escape:'html':'UTF-8'}{/if}" />
            </div>
        </td>
        <td class="col-lg-3">
            <div>
		<input type="text" name="package_rule_{$package_rule_group_id|intval}_{$package_rule_id|intval}_value" value="{if isset($value)}{$value|floatval}{/if}" />
            </div>
        </td>
        
        <input type="hidden" name="package_rule_{$package_rule_group_id|intval}_{$package_rule_id|intval}_currency" value="{$selected_currency->id|intval}" />
        <input type="hidden" name="package_rule_{$package_rule_group_id|intval}_{$package_rule_id|intval}_tax" value="0" />
        
	<td class="col-lg-1 text-right">
		<a class="btn btn-default" href="javascript:removePackageRule({$package_rule_group_id|intval}, {$package_rule_id|intval});">
			<i class="icon-remove"></i>
		</a>
	</td>
</tr>