{*
* Order Fees Shipping
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*}

{hook h="displayOrderFeesShippingFormActionsBefore" module=$module controller=$controller object=$rule}

<div class="form-group">
    <label class="control-label col-lg-3">{l s='Type' mod='orderfees_shipping'}</label>
    <div class="col-lg-9">
        <div class="radio">
            <label for="type_freeshipping">
                <input type="radio" name="type" id="type_freeshipping" value="{ShippingRule::IS_FREE_SHIPPING|intval}" {if $controller->getFieldValue($rule, 'type') & ShippingRule::IS_FREE_SHIPPING}checked="checked"{/if} data-display-trigger="type" />
                {l s='Free shipping' mod='orderfees_shipping'}
            </label>
        </div>
        <div class="radio">
            <label for="type_percent">
                <input type="radio" name="type" id="type_percent" value="{ShippingRule::IS_PERCENT|intval}" {if $controller->getFieldValue($rule, 'type') & ShippingRule::IS_PERCENT}checked="checked"{/if} data-display-trigger="type" />
                {l s='Percent (%)' mod='orderfees_shipping'}
            </label>
        </div>
        <div class="radio">
            <label for="type_amount">
                <input type="radio" name="type" id="type_amount" value="{ShippingRule::IS_AMOUNT|intval}" {if $controller->getFieldValue($rule, 'type') & ShippingRule::IS_AMOUNT}checked="checked"{/if} data-display-trigger="type" />
                {l s='Amount' mod='orderfees_shipping'}
            </label>
        </div>
            <div class="radio">
            <label for="type_formula">
                <input type="radio" name="type" id="type_formula" value="{ShippingRule::IS_FORMULA|intval}" {if $controller->getFieldValue($rule, 'type') & ShippingRule::IS_FORMULA}checked="checked"{/if} data-display-trigger="type" />
                {l s='Formula' mod='orderfees_shipping'}
            </label>
        </div>
        <div class="radio">
            <label for="type_carrier">
                <input type="radio" name="type" id="type_carrier" value="{ShippingRule::IS_CARRIER|intval}" {if $controller->getFieldValue($rule, 'type') & ShippingRule::IS_CARRIER}checked="checked"{/if} data-display-trigger="type" />
                {l s='Show only selected carriers' mod='orderfees_shipping'}
            </label>
        </div>
        <div class="radio">
            <label for="type_weight">
                <input type="radio" name="type" id="type_weight" value="{ShippingRule::IS_WEIGHT|intval}" {if $controller->getFieldValue($rule, 'type') == ShippingRule::IS_WEIGHT}checked="checked"{/if} data-display-trigger="type" />
                {l s='Weight' mod='orderfees_shipping'}
            </label>
        </div>
        <div class="radio">
            <label for="type_none">
                <input type="radio" name="type" id="type_none" value="{ShippingRule::IS_NONE|intval}" {if $controller->getFieldValue($rule, 'type') == ShippingRule::IS_NONE}checked="checked"{/if} data-display-trigger="type" />
                {l s='None' mod='orderfees_shipping'}
            </label>
        </div>
    </div>
</div>

<div class="form-group" data-type="{ShippingRule::IS_PERCENT|intval}">
    <label class="control-label col-lg-3">{l s='Value' mod='orderfees_shipping'}</label>
    <div class="col-lg-2">
        <div class="input-group">
            <span class="input-group-addon">%</span>
            <input type="text" id="percent" class="input-mini" name="percent" value="{$controller->getFieldValue($rule, 'percent')|floatval}" onchange="noComma('percent');" />
        </div>
    </div>
</div>

<div class="form-group" data-type="{ShippingRule::IS_AMOUNT|intval}">
    <label class="control-label col-lg-3">{l s='Amount' mod='orderfees_shipping'}</label>
    <div class="col-lg-7">
        <div class="row">
            <div class="col-lg-4">
                <input type="text" id="amount" name="amount" value="{$controller->getFieldValue($rule, 'amount')|floatval}" onchange="noComma('amount');" />
            </div>
            <div class="col-lg-4">
                <select name="currency" >
                    {foreach from=$currencies item='currency'}
                        <option value="{$currency.id_currency|intval}" {if $controller->getFieldValue($rule, 'currency') == $currency.id_currency || (!$controller->getFieldValue($rule, 'currency') && $currency.id_currency == $defaultCurrency)}selected="selected"{/if}>{$currency.iso_code|escape:'quotes':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>
</div>

<div class="form-group" data-type="{ShippingRule::IS_FORMULA|intval},{ShippingRule::IS_WEIGHT|intval}">
    <label class="control-label col-lg-3">{l s='Formula' mod='orderfees_shipping'}</label>
    <div class="col-lg-8">
        <div class="input-group">
            <span class="input-group-addon">ƒ</span>
            <input type="text" id="formula" class="input-mini" name="formula" value="{$controller->getFieldValue($rule, 'formula')|escape:'html':'UTF-8'}" onchange="noComma('formula');" />
            <span class="input-group-addon">
                <div class="col-lg-2">
                    <a href="" class="dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                        {l s='Variables and Functions' mod='orderfees_shipping'}
                        <i class="icon-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu supported-variables">
                        <li><a data-tag="total">{l s='total : Total amount of the order' mod='orderfees_shipping'}</a></li>
                        <li><a data-tag="shipping">{l s='shipping : Shipping cost of the order' mod='orderfees_shipping'}</a></li>
                        <li><a data-tag="quantity">{l s='quantity : Quantity of products in cart' mod='orderfees_shipping'}</a></li>
                        <li><a data-tag="weight">{l s='weight : Total weight of products in cart' mod='orderfees_shipping'}</a></li>
                        <li><a data-tag="volume">{l s='volume : Total volume of products in cart' mod='orderfees_shipping'}</a></li>
                        <li><a data-tag="width">{l s='width : Total width of products in cart' mod='orderfees_shipping'}</a></li>
                        <li><a data-tag="height">{l s='height : Total height of products in cart' mod='orderfees_shipping'}</a></li>
                        <li><a data-tag="depth">{l s='depth : Total depth of products in cart' mod='orderfees_shipping'}</a></li>
                        <li class="divider"></li>
                        <li><a data-tag="ROUND(">{l s='ROUND() : Round value' mod='orderfees_shipping'}</a></li>
                        <li><a data-tag="FLOOR(">{l s='FLOOR() : Round value to the next lowest integer value' mod='orderfees_shipping'}</a></li>
                        <li><a data-tag="CEIL(">{l s='CEIL() : Round value to the next highest integer value' mod='orderfees_shipping'}</a></li>
                        <li><a data-tag="MIN(">{l s='MIN(x;y;z) : Return lowest value' mod='orderfees_shipping'}</a></li>
                        <li><a data-tag="MAX(">{l s='MAX(x;y;z) : Return highest value' mod='orderfees_shipping'}</a></li>
                    </ul>
                </div>
            </span>
        </div>
        <span class="help-block" data-type="{ShippingRule::IS_FORMULA|intval}">{l s='For example, $15 for the first product and $5 for each additional products : [1]15 + (5 * (quantity - 1))[/1]' tags=['<strong>'] mod='orderfees_shipping'}</span>
        <span class="help-block" data-type="{ShippingRule::IS_WEIGHT|intval}">{l s='For example, enter 0.5 to increase the weight of customer cart of 0.5 %s' tags=['<strong>'] sprintf=[$ps_weight_unit] mod='orderfees_shipping'}</span>
    </div>
</div>

<div class="form-group" data-type="{ShippingRule::IS_PERCENT|intval},{ShippingRule::IS_AMOUNT|intval},{ShippingRule::IS_FORMULA|intval}">
    <label class="control-label col-lg-3">{l s='Tax rule' mod='orderfees_shipping'}</label>
    <div class="col-lg-7">
        <div class="row">
            <div class="col-lg-6">
                <select name="tax_rules_group" {if $tax_exclude_taxe_option}disabled="disabled"{/if}>
                    <option value="0" {if !$controller->getFieldValue($rule, 'tax_rules_group')}selected="selected"{/if}>{l s='No Tax' mod='orderfees_shipping'}</option>
                    {foreach from=$tax_rules_groups item=tax_rules_group}
                        <option value="{$tax_rules_group.id_tax_rules_group|intval}" {if $controller->getFieldValue($rule, 'tax_rules_group') == $tax_rules_group.id_tax_rules_group}selected="selected"{/if} >
                            {$tax_rules_group['name']|escape:'html':'UTF-8'}
                        </option>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>
</div>

<div class="form-group" data-type="{ShippingRule::IS_PERCENT|intval}">
    <label class="control-label col-lg-3">
            {l s='Apply to' mod='orderfees_shipping'}
    </label>
    <div class="col-lg-7">
        <div class="row">
            <div class="col-lg-6">
                <select name="apply_to">
                    <option value="order" {if $controller->getFieldValue($rule, 'product')|intval == 0}selected="selected"{/if}>{l s='Order (without shipping)' mod='orderfees_shipping'}</option>
                    <option value="selection" {if $controller->getFieldValue($rule, 'product')|intval == ShippingRule::PERCENT_PRODUCTS}selected="selected"{/if}>{l s='Selected product(s)' mod='orderfees_shipping'}</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!--<div data-type="{ShippingRule::IS_PERCENT|intval},{ShippingRule::IS_AMOUNT|intval},{ShippingRule::IS_FORMULA|intval}">
    <hr />

    <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='Based on quantity' mod='orderfees_shipping'}
            </label>
            <div class="col-lg-2">
                <div class="input-group col-lg-12">
                        <span class="switch prestashop-switch">
                                <input type="radio" name="quantity_per_product" id="quantity_per_product_on" value="1" {if $controller->getFieldValue($rule, 'quantity_per_product')}checked="checked"{/if} />
                                <label class="t" for="quantity_per_product_on">{l s='Yes' mod='orderfees_shipping'}</label>
                                <input type="radio" name="quantity_per_product" id="quantity_per_product_off" value="0"  {if !$controller->getFieldValue($rule, 'quantity_per_product')}checked="checked"{/if} />
                                <label class="t" for="quantity_per_product_off">{l s='No' mod='orderfees_shipping'}</label>
                                <a class="slide-button btn"></a>
                        </span>
                </div>
            </div>
            <div class="pull-left">
                <a href="#" tabindex="0" data-trigger="focus" data-html="true" class="help-link" data-toggle="popover" data-placement="top" data-content="{l s='This rule will be applied according to the quantity of product in the cart. For example : if there 3 products in the cart, the amount of this rule will be applied 3 times.' mod='orderfees_shipping'}"><i class="process-icon-help"></i></a>
            </div>
    </div>
</div>-->
            
<div data-type="{ShippingRule::IS_FREE_SHIPPING|intval},{ShippingRule::IS_PERCENT|intval},{ShippingRule::IS_AMOUNT|intval},{ShippingRule::IS_FORMULA|intval},{ShippingRule::IS_CARRIER|intval}">
    <hr />
    
    <div class="form-group">
            <label class="control-label col-lg-3">
                {l s='Apply if' mod='orderfees_shipping'}
            </label>
            <div class="col-lg-6">
                <div class="row">
                    <div class="col-lg-12">
                        <select name="apply_if">
                            <option value="{ShippingRule::APPLY_IF_LEAST|intval}" {if $controller->getFieldValue($rule, 'type') & ShippingRule::APPLY_IF_LEAST}selected="selected"{/if}>{l s='At least one product in the cart meets the conditions' mod='orderfees_shipping'}</option>
                            <option value="{ShippingRule::APPLY_IF_ALL|intval}" {if $controller->getFieldValue($rule, 'type') & ShippingRule::APPLY_IF_ALL}selected="selected"{/if}>{l s='All products in the cart meet the conditions' mod='orderfees_shipping'}</option>
                        </select>
                    </div>
                </div>
            </div>
    </div>
                        
    <div class="form-group" data-type="{ShippingRule::IS_PERCENT|intval},{ShippingRule::IS_AMOUNT|intval},{ShippingRule::IS_FORMULA|intval}">
            <label class="control-label col-lg-3">
                {l s='Compatibility with basic shipping costs' mod='orderfees_shipping'}
            </label>
            <div class="col-lg-4">
                <div class="row">
                    <div class="col-lg-12">
                        <select name="basic_shipping">
                            <option value="{ShippingRule::BASIC_SHIPPING_MERGE|intval}" {if $controller->getFieldValue($rule, 'type') & ShippingRule::BASIC_SHIPPING_MERGE}selected="selected"{/if}>{l s='Add the cost of this rule to basic shipping costs' mod='orderfees_shipping'}</option>
                            <option value="{ShippingRule::BASIC_SHIPPING_RULE|intval}" {if $controller->getFieldValue($rule, 'type') & ShippingRule::BASIC_SHIPPING_RULE}selected="selected"{/if}>{l s='Use only the cost of this rule' mod='orderfees_shipping'}</option>
                            <option value="{ShippingRule::BASIC_SHIPPING_BASE|intval}" {if $controller->getFieldValue($rule, 'type') & ShippingRule::BASIC_SHIPPING_BASE}selected="selected"{/if}>{l s='Use only the basic shipping costs' mod='orderfees_shipping'}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="pull-left">
                <a href="#" tabindex="0" data-trigger="focus" data-html="true" class="help-link" data-toggle="popover" data-placement="top" data-content="{l s='Define the behavior of this rule with the shipping costs you have defined in the "Carriers" menu.' mod='orderfees_shipping'}"><i class="process-icon-help"></i></a>
            </div>
    </div>
</div>             
            
<input type="hidden" name="quantity_per_product" value="{$controller->getFieldValue($rule, 'quantity_per_product')|intval}" />
            
{hook h="displayOrderFeesShippingFormActionsAfter" module=$module controller=$controller object=$rule}