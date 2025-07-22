{*
* Order Fees Shipping
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*}
<tr id="zipcode_rule_{$zipcode_rule_group_id|intval}_{$zipcode_rule_id|intval}_tr">
	<td>
		<input type="hidden" name="zipcode_rule_{$zipcode_rule_group_id|intval}[]" value="{$zipcode_rule_id|intval}" />
		<input type="hidden" name="zipcode_rule_{$zipcode_rule_group_id|intval}_{$zipcode_rule_id|intval}_type" value="{$zipcode_rule_type|escape}" />
		{l s='Zip/Postal Code : %s' mod='orderfees_shipping' sprintf=Country::getNameById($id_lang, $zipcode_rule_type)}
	</td>
        <td>
		<select class="form-control" id="zipcode_rule_{$zipcode_rule_group_id|intval}_{$zipcode_rule_id|intval}_operator" name="zipcode_rule_{$zipcode_rule_group_id|intval}_{$zipcode_rule_id|intval}_operator">
                    <option value="begin">{l s='start with' mod='orderfees_shipping'}</option>
                    <option value="end">{l s='end with' mod='orderfees_shipping'}</option>
                    <option value="=">{l s='=' mod='orderfees_shipping'}</option>
                    <option value=">">{l s='>' mod='orderfees_shipping'}</option>
                    <option value="<">{l s='<' mod='orderfees_shipping'}</option>
                    <option value=">=">{l s='>=' mod='orderfees_shipping'}</option>
                    <option value="<=">{l s='<=' mod='orderfees_shipping'}</option>
                    <option value="!=">{l s='!=' mod='orderfees_shipping'}</option>
                </select>
	</td>
	<td>
		<input class="form-control" type="text" name="zipcode_rule_{$zipcode_rule_group_id|intval}_{$zipcode_rule_id|intval}_value" value="{if isset($value)}{$value|escape:'quotes':'UTF-8'}{/if}" />
        </td>
	<td class="text-right">
		<a class="btn btn-default" href="javascript:removeZipcodeRule({$zipcode_rule_group_id|intval}, {$zipcode_rule_id|intval});">
			<i class="icon-remove"></i>
		</a>
	</td>
</tr>

<script type="text/javascript">
	$('#zipcode_rule_{$zipcode_rule_group_id|intval}_{$zipcode_rule_id|intval}_operator').val('{$operator|escape:'quotes':'UTF-8'}');
</script>