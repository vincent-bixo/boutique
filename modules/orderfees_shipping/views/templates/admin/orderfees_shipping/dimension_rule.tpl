{*
* Order Fees Shipping
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*}
<tr id="dimension_rule_{$dimension_rule_group_id|intval}_{$dimension_rule_id|intval}_tr">
	<td>
		<input type="hidden" name="dimension_rule_{$dimension_rule_group_id|intval}[]" value="{$dimension_rule_id|intval}" />
		<input type="hidden" name="dimension_rule_{$dimension_rule_group_id|intval}_{$dimension_rule_id|intval}_type" value="{$dimension_rule_type|escape:'html':'UTF-8'}" />
		{if $dimension_rule_type == 'width'}
                    {l s='Width' mod='orderfees_shipping'}
                {elseif $dimension_rule_type == 'height'}
                    {l s='Height' mod='orderfees_shipping'}
                {elseif $dimension_rule_type == 'depth'}
                    {l s='Depth' mod='orderfees_shipping'}
                {elseif $dimension_rule_type == 'weight'}
                    {l s='Weight' mod='orderfees_shipping'}
                {elseif $dimension_rule_type == 'volume'}
                    {l s='Volume' mod='orderfees_shipping'}
                {elseif $dimension_rule_type == 'combined'}
                    {l s='Combined (L + W + H)' mod='orderfees_shipping'}
                {elseif $dimension_rule_type == 'combined_girth'}
                    {l s='Combined (L + 2W + 2H)' mod='orderfees_shipping'}
                {/if}
	</td>
        <td>
		<select class="form-control" id="dimension_rule_{$dimension_rule_group_id|intval}_{$dimension_rule_id|intval}_operator" name="dimension_rule_{$dimension_rule_group_id|intval}_{$dimension_rule_id|intval}_operator">
                    <option value="=">{l s='=' mod='orderfees_shipping'}</option>
                    <option value=">">{l s='>' mod='orderfees_shipping'}</option>
                    <option value="<">{l s='<' mod='orderfees_shipping'}</option>
                    <option value=">=">{l s='>=' mod='orderfees_shipping'}</option>
                    <option value="<=">{l s='<=' mod='orderfees_shipping'}</option>
                    <option value="!=">{l s='!=' mod='orderfees_shipping'}</option>
                </select>
	</td>
	<td>
            <div class="input-group col-lg-12">
                <span class="input-group-addon">
                    {if $dimension_rule_type == 'weight'}
                        {$ps_weight_unit|escape:'html':'UTF-8'}
                    {elseif $dimension_rule_type == 'volume'}
                        {$ps_dimension_unit|escape:'html':'UTF-8'}3
                    {else}
                        {$ps_dimension_unit|escape:'html':'UTF-8'}
                    {/if}
                </span>
		<input type="text" name="dimension_rule_{$dimension_rule_group_id|intval}_{$dimension_rule_id|intval}_value" value="{if isset($value)}{$value|escape:'html':'UTF-8'}{/if}" />
            </div>
        </td>
	<td class="text-right">
		<a class="btn btn-default" href="javascript:removeDimensionRule({$dimension_rule_group_id|intval}, {$dimension_rule_id|intval});">
			<i class="icon-remove"></i>
		</a>
	</td>
</tr>

<script type="text/javascript">
	$('#dimension_rule_{$dimension_rule_group_id|intval}_{$dimension_rule_id|intval}_operator').val('{$operator|escape:'quotes':'UTF-8'}');
</script>