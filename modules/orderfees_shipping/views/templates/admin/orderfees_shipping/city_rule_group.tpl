{*
* Order Fees Shipping
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*}
<tr id="city_rule_group_{$city_rule_group_id|intval}_tr">
    <input type="hidden" name="city_rule_group[]" value="{$city_rule_group_id|intval}" />
    
    <td>
        <a class="btn btn-default" href="javascript:removeCityRuleGroup({$city_rule_group_id|intval});">
            <i class="icon-remove text-danger"></i>
        </a>
    </td>
    <td>      
        <div class="form-group">
            <label class="control-label col-lg-4">{l s='Add a rule concerning' mod='orderfees_shipping'}</label>
            <div class="col-lg-4">
                <select class="form-control" id="city_rule_type_{$city_rule_group_id|intval}">
                    <option value="">{l s='-- Choose --' mod='orderfees_shipping'}</option>
                    {foreach $city_countries as $city_country}
                        <option value="{$city_country.id_country|intval}">{$city_country.country|escape:'html':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>

            <div class="col-lg-2">
                <a class="btn btn-default" href="javascript:addCityRule({$city_rule_group_id|intval});">
                    <i class="icon-plus-sign"></i>
                    {l s='Add' mod='orderfees_shipping'}
                </a>
            </div>

        </div>
        
        <table id="city_rule_table_{$city_rule_group_id|intval}" class="table table-logical table-bordered">
            {if isset($city_rules) && $city_rules|@count}
                {foreach from=$city_rules item='city_rule'}
                    {$city_rule nofilter}
                {/foreach}
            {/if}
        </table>
    </td>
</tr>

<script type="text/javascript">
    var city_rule_counters = city_rule_counters || new Array();
    
    city_rule_counters[{$city_rule_group_id|intval}] = {count($city_rules)|intval};
    
    $('.label-tooltip', $('#city_rule_group_{$city_rule_group_id|intval}_tr')).tooltip();
</script>