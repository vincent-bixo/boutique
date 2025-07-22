{*
* Order Fees Shipping
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*}

{hook h="displayOrderFeesShippingFormInformationsBefore" module=$module controller=$controller object=$rule}

<div class="form-group">
	<label class="control-label col-lg-3">
		{l s='Name' mod='orderfees_shipping'}
	</label>
	<div class="col-lg-8">	
                <input id="name" type="text"  name="name" value="{$controller->getFieldValue($rule, 'name')|escape:'html':'UTF-8'}">
	</div>
</div>

<div class="form-group">
	<label class="control-label col-lg-3">
            {l s='Priority' mod='orderfees_shipping'}
	</label>
	<div class="col-lg-2">
		<input type="text" class="input-mini" name="priority" value="{if $controller->getFieldValue($rule, 'priority')}{$controller->getFieldValue($rule, 'priority')|intval}{else}1{/if}" />
	</div>
        <div class="pull-left">
            <a href="#" tabindex="0" data-trigger="focus" data-html="true" class="help-link" data-toggle="popover" data-placement="top" data-content="{l s='Rules are applied to shipping cost by priority. A rule with priority of "1" will be processed before a rule with a priority of "2".' mod='orderfees_shipping'}"><i class="process-icon-help"></i></a>
        </div>
</div>

<div class="form-group">
	<label class="control-label col-lg-3">{l s='Status' mod='orderfees_shipping'}</label>
        <div class="col-lg-2">
            <div class="input-group col-lg-12">
                    <span class="switch prestashop-switch">
                            <input type="radio" name="active" id="active_on" value="1" {if $controller->getFieldValue($rule, 'active')|intval}checked="checked"{/if} />
                            <label class="t" for="active_on">{l s='Yes' mod='orderfees_shipping'}</label>
                            <input type="radio" name="active" id="active_off" value="0"  {if !$controller->getFieldValue($rule, 'active')|intval}checked="checked"{/if} />
                            <label class="t" for="active_off">{l s='No' mod='orderfees_shipping'}</label>
                            <a class="slide-button btn"></a>
                    </span>
            </div>
        </div>
</div>
                     
{hook h="displayOrderFeesShippingFormInformationsAfter" module=$module controller=$controller object=$rule}