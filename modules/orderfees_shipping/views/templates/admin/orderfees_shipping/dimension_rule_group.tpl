{*
* Order Fees Shipping
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*}
<tr id="dimension_rule_group_{$dimension_rule_group_id|intval}_tr">
    <input type="hidden" name="dimension_rule_group[]" value="{$dimension_rule_group_id|intval}" />
    
    <td>
        <a class="btn btn-default" href="javascript:removeDimensionRuleGroup({$dimension_rule_group_id|intval});">
            <i class="icon-remove text-danger"></i>
        </a>
    </td>
    <td>
        <div class="form-group">
            <label class="control-label col-lg-4">{l s='Basis for the calculation' mod='orderfees_shipping'}</label>
            
            <div class="col-lg-4">
                <select class="form-control" id="dimension_rule_group_base_{$dimension_rule_group_id|intval}" name="dimension_rule_group_base_{$dimension_rule_group_id|intval}">
                    <option value="product">{l s='Per product' mod='orderfees_shipping'}</option>
                    <option value="product_quantity">{l s='Per product with quantity' mod='orderfees_shipping'}</option>
                    <option value="all">{l s='Set of all products' mod='orderfees_shipping'}</option>
                </select>
            </div>
        </div>
                
        <div class="form-group">
            <label class="control-label col-lg-4">{l s='Add a rule concerning' mod='orderfees_shipping'}</label>
            <div class="col-lg-4">
                <select class="form-control" id="dimension_rule_type_{$dimension_rule_group_id|intval}">
                    <option value="">{l s='-- Choose --' mod='orderfees_shipping'}</option>
                    <option value="width">{l s='Width' mod='orderfees_shipping'}</option>
                    <option value="height">{l s='Height' mod='orderfees_shipping'}</option>
                    <option value="depth">{l s='Depth' mod='orderfees_shipping'}</option>
                    <option value="weight">{l s='Weight' mod='orderfees_shipping'}</option>
                    <option value="volume">{l s='Volume' mod='orderfees_shipping'}</option>
                    <option value="combined">{l s='Combined length, width and height (L + W + H)' mod='orderfees_shipping'}</option>
                    <option value="combined_girth">{l s='Combined length and girth (L + 2W + 2H)' mod='orderfees_shipping'}</option>
                </select>
            </div>

            <div class="col-lg-2">
                <a class="btn btn-default" href="javascript:addDimensionRule({$dimension_rule_group_id|intval});">
                    <i class="icon-plus-sign"></i>
                    {l s='Add' mod='orderfees_shipping'}
                </a>
            </div>

        </div>
        
        <table id="dimension_rule_table_{$dimension_rule_group_id|intval}" class="table table-logical table-bordered">
            {if isset($dimension_rules) && $dimension_rules|@count}
                {foreach from=$dimension_rules item='dimension_rule'}
                    {$dimension_rule nofilter}
                {/foreach}
            {/if}
        </table>

    </td>
</tr>

<script type="text/javascript">
    var dimension_rule_counters = dimension_rule_counters || new Array();
    
    dimension_rule_counters[{$dimension_rule_group_id|intval}] = {count($dimension_rules)|intval};
    
    $('#dimension_rule_group_base_{$dimension_rule_group_id|intval}').val('{$dimension_rule_group_base|escape:'html':'UTF-8'}');
</script>