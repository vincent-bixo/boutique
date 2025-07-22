{*
* Order Fees Shipping
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*}

<style type="text/css">
    .table tr > td {
        border-bottom: 1px solid #ccc;
    }
    
    .table tr:last-of-type > td {
        border-bottom: none;
    }
    
    #of_shipping_rule_form a.help-link:hover, #of_shipping_rule_form a.help-link:focus {
        text-decoration: none;
    }
    
    .checkbox + div table tr {
        position: relative;
    }
    
    .checkbox + div table tr td {
        padding: 13px 7px !important;
    }
    
    .table-logical tr:not(:first-of-type) > td:first-of-type:before {
        content: "{l s='And' mod='orderfees_shipping'}";
        position: absolute;
        left: 50%;
        display: table-cell;
        top: -11px;
        background: #eaedef;
        width: 40px;
        border-radius: 15px;
        text-align: center;
        border: 1px solid #999;
    }

    .table-logical.table-bordered tr:not(:first-of-type) td:first-of-type:before {
        content: "{l s='Or' mod='orderfees_shipping'}";
    }
    
    #rule_conditions .table select[multiple] {
        height: 150px;
    }
    
    .rule-product-itemlist select[multiple] {
        height: 180px;
    }
</style>

<div class="panel">
    <h3>
        <i class="icon-random"></i> {l s='Rule' mod='orderfees_shipping'}
    </h3>

    <div>
        <ul class="tab nav nav-tabs">
            <li class="tab-row">
                <a class="tab-page" id="rule_link_informations" href="javascript:displayRuleTab('informations');"><i class="icon-info"></i> {l s='Information' mod='orderfees_shipping'}</a>
            </li>
            <li class="tab-row">
                <a class="tab-page" id="rule_link_conditions" href="javascript:displayRuleTab('conditions');"><i class="icon-random"></i> {l s='Conditions' mod='orderfees_shipping'}</a>
            </li>
            <li class="tab-row">
                <a class="tab-page" id="rule_link_actions" href="javascript:displayRuleTab('actions');"><i class="icon-wrench"></i> {l s='Actions' mod='orderfees_shipping'}</a>
            </li>
        </ul>
    </div>

    <form action="{$currentIndex|escape:'html':'UTF-8'}&token={$currentToken|escape:'html':'UTF-8'}&addof_shipping_rule" id="{$table|escape:'quotes':'UTF-8'}_form" method="post" class="form-horizontal">
        {if $rule->id}<input type="hidden" name="id_of_shipping_rule" value="{$rule->id|intval}" />{/if}
        <input type="hidden" id="currentFormTab" name="currentFormTab" value="informations" />
        
        <div id="rule_informations" class="panel rule_tab">
            {include './tabs/informations.tpl'}
        </div>
        <div id="rule_conditions" class="panel rule_tab">
            {include './tabs/conditions.tpl'}
        </div>
        <div id="rule_actions" class="panel rule_tab">
            {include './tabs/actions.tpl'}
        </div>
        
        <div class="separation"></div>
        <div style="text-align:center">
            <input type="submit" value="{l s='Save' mod='orderfees_shipping'}" class="button" name="submitAddof_shipping_rule" id="{$table|escape:'quotes':'UTF-8'}_form_submit_btn" />
            <!--<input type="submit" value="{l s='Save and stay' mod='orderfees_shipping'}" class="button" name="submitAddshipping_ruleAndStay" id="" />-->
        </div>
    </form>
    <script type="text/javascript">
        var product_rule_groups_counter = {$product_rule_groups|@count|intval};
        var product_rule_counters =  product_rule_counters || new Array();

        var dimension_rule_groups_counter = {$dimension_rule_groups|@count|intval};
        var dimension_rule_counters = dimension_rule_counters || new Array();
        
        var city_rule_groups_counter = {$city_rule_groups|@count|intval};
        var city_rule_counters = city_rule_counters || new Array();
        
        var zipcode_rule_groups_counter = {$zipcode_rule_groups|@count|intval};
        var zipcode_rule_counters = zipcode_rule_counters || new Array();
        
        var package_rule_groups_counter = {$package_rule_groups|@count|intval};
        var package_rule_counters = package_rule_counters || new Array();

        var currentToken = '{$currentToken|escape:'quotes':'UTF-8'}';
        var currentFormTab = '{if isset($smarty.post.currentFormTab)}{$smarty.post.currentFormTab|escape:'quotes':'UTF-8'}{else}informations{/if}';
    </script>
    <script type="text/javascript" src="{$module->getPathUri()|escape:'html':'UTF-8'}views/js/admin.js"></script>
    {include file="footer_toolbar.tpl"}
</div>

{hook h="actionDiscoverModules" module=$module->name}