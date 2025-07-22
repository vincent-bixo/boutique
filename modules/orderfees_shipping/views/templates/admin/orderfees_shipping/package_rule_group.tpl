{*
* Order Fees Shipping
*
*  @author    motionSeed <ecommerce@motionseed.com>
*  @copyright 2017 motionSeed. All rights reserved.
*  @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*}
<tr id="package_rule_group_{$package_rule_group_id|intval}_tr">
    <input type="hidden" name="package_rule_group[]" value="{$package_rule_group_id|intval}" />
    <input type="hidden" class="package-unit-weight" id="package_rule_group_unit_weight_{$package_rule_group_id|intval}" name="package_rule_group_unit_weight_{$package_rule_group_id|intval}" value="{$package_rule_group_unit_weight|escape:'html':'UTF-8'}" />
    
    <td>
        <a class="btn btn-default" href="javascript:removePackageRuleGroup({$package_rule_group_id|intval});">
            <i class="icon-remove text-danger"></i>
        </a>
    </td>
    <td>
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Weight/Volume ratio' mod='orderfees_shipping'}</label>
            
            <div class="col-lg-2">
                <select class="form-control package-units" id="package_rule_group_unit_{$package_rule_group_id|intval}" name="package_rule_group_unit_{$package_rule_group_id|intval}">
                    <option value="kg/m3" data-weight="kg">kg/m3</option>
                    <option value="cm3/kg" data-weight="kg">cm3/kg</option>
                </select>
            </div>
                    
            <div class="col-lg-1 control-label">
            </div>
                    
            <div class="col-lg-2">
                <select class="form-control package-units-predefined" name="package_rule_group_unit_predefined_{$package_rule_group_id|intval}" id="package_rule_group_unit_predefined_{$package_rule_group_id|intval}">
                    <option value="">--</option>
                    
                    <option data-unit="kg/m3" value="333">333 kg/m3</option>
                    <option data-unit="kg/m3" value="250">250 kg/m3</option>
                    <option data-unit="kg/m3" value="200">200 kg/m3</option>
                    <option data-unit="kg/m3" value="166.667">166 kg/m3</option>
                    <option data-unit="kg/m3" value="150">150 kg/m3</option>
                    <option data-unit="kg/m3" value="142.857">142 kg/m3</option>

                    <option data-unit="cm3/kg" value="5000">5000 cm3/kg</option>
                    <option data-unit="cm3/kg" value="6000">6000 cm3/kg</option>
                    <option data-unit="cm3/kg" value="7000">7000 cm3/kg</option>
                </select>
            </div>
                    
            <div class="col-lg-1 control-label" style="text-align: center;">
                {l s='or' mod='orderfees_shipping'}
            </div>
            
            <div class="col-lg-3">
                <div class="input-group col-lg-12">
                    <span class="input-group-addon package-unit">
                        {$package_rule_group_unit|escape:'html':'UTF-8'}
                    </span>
                    <input type="text" name="package_rule_group_ratio_{$package_rule_group_id|intval}" id="package_rule_group_ratio_{$package_rule_group_id|intval}" value="{if isset($package_rule_group_ratio)}{$package_rule_group_ratio|escape:'html':'UTF-8'}{/if}" />
                </div>
            </div>
        </div>
                
        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Add a range'  mod='orderfees_shipping'}</label>
            
            <div class="col-lg-3">
                <div class="input-group col-lg-12">
                    <span class="input-group-addon package-unit-weight">
                        {$package_rule_group_unit_weight|escape:'html':'UTF-8'}
                    </span>
                    <input type="text" id="package_rule_range_start_{$package_rule_group_id|intval}" name="package_rule_range_start_{$package_rule_group_id|intval}" />
                </div>
            </div>
                
            <div class="col-lg-1 control-label" style="text-align: center;">
                {l s='to' mod='orderfees_shipping'}
            </div>
                
            <div class="col-lg-3">
                <div class="input-group col-lg-12">
                    <span class="input-group-addon package-unit-weight">
                        {$package_rule_group_unit_weight|escape:'html':'UTF-8'}
                    </span>
                    <input type="text" id="package_rule_range_end_{$package_rule_group_id|intval}" name="package_rule_range_end_{$package_rule_group_id|intval}" />
                </div>
            </div>

            <div class="col-lg-2">
                <a class="btn btn-default" href="javascript:addPackageRule({$package_rule_group_id|intval});">
                    <i class="icon-plus-sign"></i>
                    {l s='Add' mod='orderfees_shipping'}
                </a>
            </div>

        </div>

        <table id="package_rule_table_{$package_rule_group_id|intval}" class="table table-bordered">
            <thead>
                <tr>
                    <th>{l s='Range' mod='orderfees_shipping'}</th>
                    <th>{l s='Round' mod='orderfees_shipping'}</th>
                    <th>{l s='Divider' mod='orderfees_shipping'}</th>
                    <th colspan="4">{l s='Amount' mod='orderfees_shipping'}</th>
                </tr>
            </thead>
            {if isset($package_rules) && $package_rules|@count}
                {foreach from=$package_rules item='package_rule'}
                    {$package_rule nofilter}
                {/foreach}
            {/if}
        </table>

    </td>
</tr>

<script type="text/javascript">
    var package_rule_counters = package_rule_counters || new Array();
    
    package_rule_counters[{$package_rule_group_id|intval}] = {count($package_rules)|intval};
    
    $('#package_rule_group_unit_{$package_rule_group_id|intval}').val('{$package_rule_group_unit|escape:'html':'UTF-8'}');
    
    $('#package_rule_group_unit_{$package_rule_group_id|intval}').change();
</script>