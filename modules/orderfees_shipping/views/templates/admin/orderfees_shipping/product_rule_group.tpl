{*
* Order Fees Shipping
*
*  @author    motionSeed <ecommerce@motionseed.com>
*  @copyright 2017 motionSeed. All rights reserved.
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*}
<tr id="product_rule_group_{$product_rule_group_id|intval}_tr">
	<td>
		<a class="btn btn-default" href="javascript:removeProductRuleGroup({$product_rule_group_id|intval});">
			<i class="icon-remove text-danger"></i>
		</a>
	</td>
	<td>


		<div class="form-group">
			<label class="control-label col-lg-4">{l s='The cart must contain at least' mod='orderfees_shipping'}</label>
			<div class="col-lg-1">
				<input type="hidden" name="product_rule_group[]" value="{$product_rule_group_id|intval}" />
				<input class="form-control" type="text" name="product_rule_group_{$product_rule_group_id|intval}_quantity" value="{$product_rule_group_quantity|intval}" />
			</div>
			<div class="col-lg-7">
				<label  class="control-label pull-left">{l s='product(s) matching the following rules:' mod='orderfees_shipping'}</label>
			</div>
		</div>



		<div class="form-group">

			<label class="control-label col-lg-4">{l s='Add a rule concerning' mod='orderfees_shipping'}</label>
			<div class="col-lg-4">
				<select class="form-control" id="product_rule_type_{$product_rule_group_id|intval}">
					<option value="">{l s='-- Choose --' mod='orderfees_shipping'}</option>
					<option value="products">{l s='Products' mod='orderfees_shipping'}</option>
					<option value="attributes">{l s='Attributes' mod='orderfees_shipping'}</option>
                                        <option value="features">{l s='Features' mod='orderfees_shipping'}</option>
					<option value="categories">{l s='Categories' mod='orderfees_shipping'}</option>
					<option value="manufacturers">{l s='Manufacturers' mod='orderfees_shipping'}</option>
					<option value="suppliers">{l s='Suppliers' mod='orderfees_shipping'}</option>
                                        <option value="main_categories">{l s='Main categories' mod='orderfees_shipping'}</option>
                                        <option value="outofstock">{l s='Out of stock products' mod='orderfees_shipping'}</option>
				</select>
			</div>
			<div class="col-lg-4">
				<a class="btn btn-default" href="javascript:addProductRule({$product_rule_group_id|intval});">
					<i class="icon-plus-sign"></i>
					{l s='Add' mod='orderfees_shipping'}
				</a>
			</div>

		</div>

		<table id="product_rule_table_{$product_rule_group_id|intval}" class="table table-logical table-bordered">
			{if isset($product_rules) && $product_rules|@count}
				{foreach from=$product_rules item='product_rule'}
                                    {$product_rule nofilter}
				{/foreach}
			{/if}
		</table>

	</td>
</tr>

<script type="text/javascript">
    var product_rule_counters =  product_rule_counters || new Array();
    
    product_rule_counters[{$product_rule_group_id|intval}] = {count($product_rules)|intval};
</script>