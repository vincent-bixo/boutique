{*
* Order Fees Shipping
*
*  @author    motionSeed <ecommerce@motionseed.com>
*  @copyright 2017 motionSeed. All rights reserved.
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*}
<div class="col-lg-12 bootstrap rule-product-itemlist">
	<div class="col-lg-6">
		{l s='Unselected' mod='orderfees_shipping'}
		<select multiple size="10" id="product_rule_select_{$product_rule_group_id|intval}_{$product_rule_id|intval}_1">
			{foreach from=$product_rule_itemlist.unselected item='item'}
				<option value="{$item.id|intval}" title="{$item.name|escape:'html':'UTF-8'}">&nbsp;{$item.name|escape:'html':'UTF-8'}</option>
			{/foreach}
		</select>
		<div class="clearfix">&nbsp;</div>
		<a id="product_rule_select_{$product_rule_group_id|intval}_{$product_rule_id|intval}_add" class="btn btn-default btn-block" >
			{l s='Add' mod='orderfees_shipping'}
			<i class="icon-arrow-right"></i>
		</a>
	</div>
	<div class="col-lg-6">
		{l s='Selected' mod='orderfees_shipping'}
		<select multiple size="10" name="product_rule_select_{$product_rule_group_id|intval}_{$product_rule_id|intval}[]" id="product_rule_select_{$product_rule_group_id|intval}_{$product_rule_id|intval}_2" class="product_rule_toselect" >
			{foreach from=$product_rule_itemlist.selected item='item'}
				<option value="{$item.id|intval}" title="{$item.name|escape:'html':'UTF-8'}">&nbsp;{$item.name|escape:'html':'UTF-8'}</option>
			{/foreach}
		</select>
		<div class="clearfix">&nbsp;</div>
		<a id="product_rule_select_{$product_rule_group_id|intval}_{$product_rule_id|intval}_remove" class="btn btn-default btn-block" >
			<i class="icon-arrow-left"></i>
			{l s='Remove' mod='orderfees_shipping'}
		</a>
	</div>
</div>
			
<script type="text/javascript">
	$('#product_rule_select_{$product_rule_group_id|intval}_{$product_rule_id|intval}_remove').click(function() { removeRuleOption(this); updateProductRuleShortDescription(this); });
	$('#product_rule_select_{$product_rule_group_id|intval}_{$product_rule_id|intval}_add').click(function() { addRuleOption(this); updateProductRuleShortDescription(this); });
	$(document).ready(function() { updateProductRuleShortDescription($('#product_rule_select_{$product_rule_group_id|intval}_{$product_rule_id|intval}_add')); });
</script>
