{*
* Order Fees Shipping
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*}
<tr id="zipcode_rule_group_{$zipcode_rule_group_id|intval}_tr">
    <input type="hidden" name="zipcode_rule_group[]" value="{$zipcode_rule_group_id|intval}" />
    
    <td>
        <a class="btn btn-default" href="javascript:removeZipcodeRuleGroup({$zipcode_rule_group_id|intval});">
            <i class="icon-remove text-danger"></i>
        </a>
    </td>
    <td>      
        <div class="form-group">
            <label class="control-label col-lg-4">{l s='Add a rule concerning' mod='orderfees_shipping'}</label>
            <div class="col-lg-4">
                <select class="form-control" id="zipcode_rule_type_{$zipcode_rule_group_id|intval}">
                    <option value="">{l s='-- Choose --' mod='orderfees_shipping'}</option>
                    {foreach $zipcode_countries as $zipcode_country}
                        <option value="{$zipcode_country.id_country|intval}">{$zipcode_country.country|escape:'html':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>

            <div class="col-lg-2">
                <a class="btn btn-default" href="javascript:addZipcodeRule({$zipcode_rule_group_id|intval});">
                    <i class="icon-plus-sign"></i>
                    {l s='Add' mod='orderfees_shipping'}
                </a>
            </div>

        </div>
        
        <table id="zipcode_rule_table_{$zipcode_rule_group_id|intval}" class="table table-logical table-bordered">
            {if isset($zipcode_rules) && $zipcode_rules|@count}
                {foreach from=$zipcode_rules item='zipcode_rule'}
                    {$zipcode_rule nofilter}
                {/foreach}
            {/if}
        </table>
    </td>
</tr>

<script type="text/javascript">
    var zipcode_rule_counters = zipcode_rule_counters || new Array();
    
    zipcode_rule_counters[{$zipcode_rule_group_id|intval}] = {count($zipcode_rules)|intval};
    
    $('.label-tooltip', $('#zipcode_rule_group_{$zipcode_rule_group_id|intval}_tr')).tooltip();
</script>