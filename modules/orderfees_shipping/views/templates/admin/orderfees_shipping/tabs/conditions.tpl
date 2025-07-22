{*
* Order Fees Shipping
*
* @author    motionSeed <ecommerce@motionseed.com>
* @copyright 2017 motionSeed. All rights reserved.
* @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
*}

{hook h="displayOrderFeesShippingFormConditionsBefore" module=$module controller=$controller object=$rule}

<div class="form-group">
	<label class="control-label col-lg-3">
            {l s='Limit to a single customer' mod='orderfees_shipping'}
	</label>
        <div class="col-lg-6">
            <div class="input-group">
                    <span class="input-group-addon"><i class="icon-user"></i></i></span>
                    <input type="hidden" id="id_customer" name="id_customer" value="{$controller->getFieldValue($rule, 'id_customer')|intval}" />
                    <input type="text" id="customerFilter" class="input-xlarge" name="customerFilter" value="{$customerFilter|escape:'html':'UTF-8'}" />
                    <span class="input-group-addon"><i class="icon-search"></i></span>
            </div>
        </div>
        <div class="pull-left">
            <a href="#" tabindex="0" data-trigger="focus" data-html="true" class="help-link" data-toggle="popover" data-placement="top" data-content="{l s='Optional, this rule will be applied to everyone if you leave this field blank.' mod='orderfees_shipping'}"><i class="process-icon-help"></i></a>
        </div>
</div>

<div class="form-group">
	<label class="control-label col-lg-3">
            {l s='Minimum amount' mod='orderfees_shipping'}
	</label>
	<div class="col-lg-9">
            <div class="row">
                <div class="col-lg-3">
                    <input type="text" name="minimum_amount" value="{$controller->getFieldValue($rule, 'minimum_amount')|floatval}" />
                </div>
                <div class="col-lg-2">
                    <select name="minimum_amount_currency">
                        {foreach from=$currencies item='currency'}
                            <option value="{$currency.id_currency|intval}"
                                {if $controller->getFieldValue($rule, 'minimum_amount_currency') == $currency.id_currency
                                    || (!$controller->getFieldValue($rule, 'minimum_amount_currency') && $currency.id_currency == $defaultCurrency)}
                                    selected="selected"
                                {/if}
                            >
                                {$currency.iso_code|escape:'html':'UTF-8'}
                            </option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-lg-2">
                    <select name="minimum_amount_tax">
                        <option value="0" {if $controller->getFieldValue($rule, 'minimum_amount_tax') == 0}selected="selected"{/if}>{l s='Tax excluded' mod='orderfees_shipping'}</option>
                        <option value="1" {if $controller->getFieldValue($rule, 'minimum_amount_tax') == 1}selected="selected"{/if}>{l s='Tax included' mod='orderfees_shipping'}</option>
                    </select>
                </div>
                <div class="col-lg-4">
                    <select name="minimum_amount_restriction">
                        <option value="{ShippingRule::ORDER|intval}" {if $controller->getFieldValue($rule, 'minimum_amount_restriction') == ShippingRule::ORDER}selected="selected"{/if}>{l s='Order total (without shipping cost)' mod='orderfees_shipping'}</option>
                        <option value="{ShippingRule::PRODUCTS|intval}" {if $controller->getFieldValue($rule, 'minimum_amount_restriction') == ShippingRule::PRODUCTS}selected="selected"{/if}>{l s='Selected products' mod='orderfees_shipping'}</option>
                    </select>
                </div>
                <div class="pull-left">
                    <a href="#" tabindex="0" data-trigger="focus" data-html="true" class="help-link" data-toggle="popover" data-placement="top" data-content="{l s='The rule will be applied only if the cart amount is [1]greater[/1] than the [1]Minimum amount[/1].' tags=['<strong>'] mod='orderfees_shipping'}"><i class="process-icon-help"></i></a>
                </div>
		</div>
	</div>
</div>
                                
<div class="form-group">
        <label class="control-label col-lg-3">
            {l s='Maximum amount' mod='orderfees_shipping'}
        </label>
        <div class="col-lg-9">
            <div class="row">
                <div class="col-lg-3">
                    <input type="text" name="maximum_amount" value="{$controller->getFieldValue($rule, 'maximum_amount')|floatval}" />
                </div>
                <div class="col-lg-2">
                    <select name="maximum_amount_currency">
                        {foreach from=$currencies item='currency'}
                            <option value="{$currency.id_currency|intval}" {if $controller->getFieldValue($rule, 'maximum_amount_currency') == $currency.id_currency || (!$controller->getFieldValue($rule, 'maximum_amount_currency') && $currency.id_currency == $defaultCurrency)}selected="selected"{/if}>
                                {$currency.iso_code|escape:'html':'UTF-8'}
                            </option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-lg-2">
                    <select name="maximum_amount_tax">
                        <option value="0" {if $controller->getFieldValue($rule, 'maximum_amount_tax') == 0}selected="selected"{/if}>{l s='Tax excluded' mod='orderfees_shipping'}</option>
                        <option value="1" {if $controller->getFieldValue($rule, 'maximum_amount_tax') == 1}selected="selected"{/if}>{l s='Tax included' mod='orderfees_shipping'}</option>
                    </select>
                </div>
                <div class="col-lg-4">
                    <select name="maximum_amount_restriction">
                        <option value="{ShippingRule::ORDER|intval}" {if $controller->getFieldValue($rule, 'maximum_amount_restriction') == ShippingRule::ORDER}selected="selected"{/if}>{l s='Order total (without shipping cost)' mod='orderfees_shipping'}</option>
                        <option value="{ShippingRule::PRODUCTS|intval}" {if $controller->getFieldValue($rule, 'maximum_amount_restriction') == ShippingRule::PRODUCTS}selected="selected"{/if}>{l s='Selected products' mod='orderfees_shipping'}</option>
                    </select>
                </div>
                <div class="pull-left">
                    <a href="#" tabindex="0" data-trigger="focus" data-html="true" class="help-link" data-toggle="popover" data-placement="top" data-content="{l s='The rule will be applied only if the cart amount is [1]less[/1] than the [1]Maximum amount[/1].' tags=['<strong>'] mod='orderfees_shipping'}"><i class="process-icon-help"></i></a>
                </div>
            </div>
        </div>
</div>
                                
<div class="form-group">
	<label class="control-label col-lg-3">
		{l s='Restrictions' mod='orderfees_shipping'}
	</label>
	<div class="col-lg-9">
                {hook h="displayOrderFeesShippingFormConditionsRestrictionsBefore" module=$module controller=$controller object=$rule}
            
		{if ($countries.unselected|@count) + ($countries.selected|@count) > 0}
			<p class="checkbox">
				<label>
					<input type="checkbox" id="country_restriction" name="country_restriction" value="1" {if $countries.unselected|@count}checked="checked"{/if} />
					{l s='Countries' mod='orderfees_shipping'}
				</label>
			</p>
			<div id="country_restriction_div">
                                <span class="help-block">{l s='This restriction applies to the country of delivery.' mod='orderfees_shipping'}</span>
				<table class="table">
					<tr>
						<td>
							<p>{l s='Unselected countries' mod='orderfees_shipping'}</p>
							<select id="country_select_1" multiple>
								{foreach from=$countries.unselected item='country'}
									<option value="{$country.id_country|intval}">&nbsp;{$country.name|escape}</option>
								{/foreach}
							</select>
							<a id="country_select_add" class="btn btn-default btn-block clearfix">{l s='Add' mod='orderfees_shipping'} <i class="icon-arrow-right"></i></a>
						</td>
						<td>
							<p>{l s='Selected countries' mod='orderfees_shipping'}</p>
							<select name="country_select[]" id="country_select_2" class="input-large" multiple>
								{foreach from=$countries.selected item='country'}
									<option value="{$country.id_country|intval}">&nbsp;{$country.name|escape}</option>
								{/foreach}
							</select>
							<a id="country_select_remove" class="btn btn-default btn-block clearfix"><i class="icon-arrow-left"></i> {l s='Remove' mod='orderfees_shipping'} </a>
						</td>
					</tr>
				</table>
			</div>
		{/if}
                
                {if ($zones.unselected|@count) + ($zones.selected|@count) > 0}
			<p class="checkbox">
				<label>
					<input type="checkbox" id="zone_restriction" name="zone_restriction" value="1" {if $zones.unselected|@count}checked="checked"{/if} />
					{l s='Zones' mod='orderfees_shipping'}
				</label>
			</p>
			<div id="zone_restriction_div">
				<table class="table">
					<tr>
						<td>
							<p>{l s='Unselected zones' mod='orderfees_shipping'}</p>
							<select id="zone_select_1" multiple>
								{foreach from=$zones.unselected item='zone'}
									<option value="{$zone.id_zone|intval}">&nbsp;{$zone.name|escape}</option>
								{/foreach}
							</select>
							<a id="zone_select_add" class="btn btn-default btn-block clearfix">{l s='Add' mod='orderfees_shipping'} <i class="icon-arrow-right"></i></a>
						</td>
						<td>
							<p>{l s='Selected zones' mod='orderfees_shipping'}</p>
							<select name="zone_select[]" id="zone_select_2" class="input-large" multiple>
								{foreach from=$zones.selected item='zone'}
									<option value="{$zone.id_zone|intval}">&nbsp;{$zone.name|escape}</option>
								{/foreach}
							</select>
							<a id="zone_select_remove" class="btn btn-default btn-block clearfix"><i class="icon-arrow-left"></i> {l s='Remove' mod='orderfees_shipping'} </a>
						</td>
					</tr>
				</table>
			</div>
		{/if}
                
                {if ($states.unselected|@count) + ($states.selected|@count) > 0}
			<p class="checkbox">
				<label>
					<input type="checkbox" id="state_restriction" name="state_restriction" value="1" {if $states.unselected|@count}checked="checked"{/if} />
					{l s='States' mod='orderfees_shipping'}
				</label>
			</p>
			<div id="state_restriction_div">
				<table class="table">
					<tr>
						<td>
							<p>{l s='Unselected states' mod='orderfees_shipping'}</p>
							<select id="state_select_1" multiple>
								{foreach from=$states.unselected item='state'}
									<option value="{$state.id_state|intval}">&nbsp;{$state.name|escape}</option>
								{/foreach}
							</select>
							<a id="state_select_add" class="btn btn-default btn-block clearfix">{l s='Add' mod='orderfees_shipping'} <i class="icon-arrow-right"></i></a>
						</td>
						<td>
							<p>{l s='Selected states' mod='orderfees_shipping'}</p>
							<select name="state_select[]" id="state_select_2" class="input-large" multiple>
								{foreach from=$states.selected item='state'}
									<option value="{$state.id_state|intval}">&nbsp;{$state.name|escape}</option>
								{/foreach}
							</select>
							<a id="state_select_remove" class="btn btn-default btn-block clearfix"><i class="icon-arrow-left"></i> {l s='Remove' mod='orderfees_shipping'} </a>
						</td>
					</tr>
				</table>
			</div>
		{/if}
                
                {if ($cities.unselected|@count) + ($cities.selected|@count) > 0}
			<p class="checkbox">
				<label>
					<input type="checkbox" id="city_restriction" name="city_restriction" value="1" {if $cities.unselected|@count}checked="checked"{/if} />
					{l s='Cities' mod='orderfees_shipping'}
				</label>
			</p>
			<div id="city_restriction_div">
				<table class="table">
					<tr>
						<td>
							<p>{l s='Unselected cities' mod='orderfees_shipping'}</p>
							<select id="city_select_1" multiple>
								{foreach from=$cities.unselected item='city'}
									<option value="{$city.name|escape}">&nbsp;{$city.name|escape}</option>
								{/foreach}
							</select>
							<a id="city_select_add" class="btn btn-default btn-block clearfix">{l s='Add' mod='orderfees_shipping'} <i class="icon-arrow-right"></i></a>
						</td>
						<td>
							<p>{l s='Selected cities' mod='orderfees_shipping'}</p>
							<select name="city_select[]" id="city_select_2" class="input-large" multiple>
								{foreach from=$cities.selected item='city'}
									<option value="{$city.name|escape}">&nbsp;{$city.name|escape}</option>
								{/foreach}
							</select>
							<a id="city_select_remove" class="btn btn-default btn-block clearfix"><i class="icon-arrow-left"></i> {l s='Remove' mod='orderfees_shipping'} </a>
						</td>
					</tr>
				</table>
			</div>
                {elseif $countries_nb > 0}
                    <p class="checkbox">
                        <label>
                            <input type="checkbox" id="city_restriction" name="city_restriction" value="1" {if $city_rule_groups|@count}checked="checked"{/if} />
                            {l s='Cities' mod='orderfees_shipping'}
                        </label>
                    </p>
                    <div id="city_restriction_div">
                        <span class="help-block">{l s='This restriction applies to the country of delivery.' mod='orderfees_shipping'} {l s='You can define multiple values by separating them with a comma.' mod='orderfees_shipping'}</span>
                        <table id="city_rule_group_table" class="table table-logical">
                            {foreach from=$city_rule_groups item='city_rule_group'}
                                {$city_rule_group nofilter}
                            {/foreach}
                        </table>
                        <a href="javascript:addCityRuleGroup();" class="btn btn-default ">
                            <i class="icon-plus-sign"></i> {l s='Cities selection' mod='orderfees_shipping'}
                        </a>
                    </div>
		{/if}
                
                {if $countries_nb > 0}
                    <p class="checkbox">
                        <label>
                            <input type="checkbox" id="zipcode_restriction" name="zipcode_restriction" value="1" {if $zipcode_rule_groups|@count}checked="checked"{/if} />
                            {l s='Zip/Postal Codes' mod='orderfees_shipping'}
                        </label>
                    </p>
                    <div id="zipcode_restriction_div">
                        <span class="help-block">{l s='This restriction applies to the country of delivery.' mod='orderfees_shipping'} {l s='You can define multiple values by separating them with a comma.' mod='orderfees_shipping'}</span>
                        <table id="zipcode_rule_group_table" class="table table-logical">
                            {foreach from=$zipcode_rule_groups item='zipcode_rule_group'}
                                {$zipcode_rule_group nofilter}
                            {/foreach}
                        </table>
                        <a href="javascript:addZipcodeRuleGroup();" class="btn btn-default ">
                            <i class="icon-plus-sign"></i> {l s='Zip/Postal Codes selection' mod='orderfees_shipping'}
                        </a>
                    </div>
		{/if}

		{if ($carriers.unselected|@count) + ($carriers.selected|@count) > 0}
			<p class="checkbox">
				<label>
					<input type="checkbox" id="carrier_restriction" name="carrier_restriction" value="1" {if $carriers.unselected|@count}checked="checked"{/if} />
					{l s='Carriers' mod='orderfees_shipping'}
				</label>
			</p>
			<div id="carrier_restriction_div">
				<table class="table">
					<tr>
						<td>
							<p>{l s='Unselected carriers' mod='orderfees_shipping'}</p>
							<select id="carrier_select_1" class="input-large" multiple>
								{foreach from=$carriers.unselected item='carrier'}
									<option value="{$carrier.id_reference|intval}">&nbsp;{if $carrier.name != '0'}{$carrier.name|escape}{else}{$carrier.delay|escape}{/if}</option>
								{/foreach}
							</select>
							<a id="carrier_select_add" class="btn btn-default btn-block clearfix" >{l s='Add' mod='orderfees_shipping'} <i class="icon-arrow-right"></i></a>
						</td>
						<td>
							<p>{l s='Selected carriers' mod='orderfees_shipping'}</p>
							<select name="carrier_select[]" id="carrier_select_2" class="input-large" multiple>
								{foreach from=$carriers.selected item='carrier'}
									<option value="{$carrier.id_reference|intval}">&nbsp;{if $carrier.name != '0'}{$carrier.name|escape}{else}{$carrier.delay|escape}{/if}</option>
								{/foreach}
							</select>
							<a id="carrier_select_remove" class="btn btn-default btn-block clearfix"><i class="icon-arrow-left"></i> {l s='Remove' mod='orderfees_shipping'} </a>
						</td>
					</tr>
				</table>
			</div>
		{/if}

		{if ($groups.unselected|@count) + ($groups.selected|@count) > 0}
			<p class="checkbox">
				<label>
					<input type="checkbox" id="group_restriction" name="group_restriction" value="1" {if $groups.unselected|@count}checked="checked"{/if} />
					{l s='Customer groups' mod='orderfees_shipping'}
				</label>
			</p>
			<div id="group_restriction_div">
				<table class="table">
					<tr>
						<td>
							<p>{l s='Unselected groups' mod='orderfees_shipping'}</p>
							<select id="group_select_1" class="input-large" multiple>
								{foreach from=$groups.unselected item='group'}
									<option value="{$group.id_group|intval}">&nbsp;{$group.name|escape}</option>
								{/foreach}
							</select>
							<a id="group_select_add" class="btn btn-default btn-block clearfix" >{l s='Add' mod='orderfees_shipping'} <i class="icon-arrow-right"></i></a>
						</td>
						<td>
							<p>{l s='Selected groups' mod='orderfees_shipping'}</p>
							<select name="group_select[]" class="input-large" id="group_select_2" multiple>
								{foreach from=$groups.selected item='group'}
									<option value="{$group.id_group|intval}">&nbsp;{$group.name|escape}</option>
								{/foreach}
							</select>
							<a id="group_select_remove" class="btn btn-default btn-block clearfix" ><i class="icon-arrow-left"></i> {l s='Remove' mod='orderfees_shipping'}</a>
						</td>
					</tr>
				</table>
			</div>
		{/if}

		{if ($shipping_rules.unselected|@count) + ($shipping_rules.selected|@count) > 0}
			<p class="checkbox">
				<label>
					<input type="checkbox" id="of_shipping_rule_restriction" name="of_shipping_rule_restriction" value="1" {if $shipping_rules.unselected|@count}checked="checked"{/if} />
					{l s='Compatibility with other rules' mod='orderfees_shipping'}
				</label>
			</p>
			<div id="of_shipping_rule_restriction_div" >
				<table  class="table">
					<tr>
						<td>
							<p>{l s='Uncombinable rules' mod='orderfees_shipping'}</p>
							<select id="of_shipping_rule_select_1" multiple="">
								{foreach from=$shipping_rules.unselected item='shipping_rule'}
									<option value="{$shipping_rule.id_of_shipping_rule|intval}">&nbsp;{$shipping_rule.name|escape}</option>
								{/foreach}
							</select>
							<a id="of_shipping_rule_select_add" class="btn btn-default btn-block clearfix">{l s='Add' mod='orderfees_shipping'} <i class="icon-arrow-right"></i></a>
						</td>
						<td>
							<p>{l s='Combinable rules' mod='orderfees_shipping'}</p>
							<select name="of_shipping_rule_select[]" id="of_shipping_rule_select_2" multiple>
								{foreach from=$shipping_rules.selected item='shipping_rule'}
									<option value="{$shipping_rule.id_of_shipping_rule|intval}">&nbsp;{$shipping_rule.name|escape}</option>
								{/foreach}
							</select>
							<a id="of_shipping_rule_select_remove" class="btn btn-default btn-block clearfix" ><i class="icon-arrow-left"></i> {l s='Remove' mod='orderfees_shipping'}</a>
						</td>
					</tr>
				</table>
			</div>
		{/if}

                <p class="checkbox">
                        <label>
                                <input type="checkbox" id="product_restriction" name="product_restriction" value="1" {if $product_rule_groups|@count}checked="checked"{/if} />
                                {l s='Products, Categories, Attributes, Suppliers ...' mod='orderfees_shipping'}
                        </label>
                </p>
                <div id="product_restriction_div">
                        <table id="product_rule_group_table" class="table table-logical">
                                {foreach from=$product_rule_groups item='product_rule_group'}
                                        {$product_rule_group nofilter}
                                {/foreach}
                        </table>
                        <a href="javascript:addProductRuleGroup();" class="btn btn-default ">
                                <i class="icon-plus-sign"></i> {l s='Product selection' mod='orderfees_shipping'}
                        </a>
                </div>
                                
                <p class="checkbox">
                    <label>
                        <input type="checkbox" id="dimension_restriction" name="dimension_restriction" value="1" {if $dimension_rule_groups|@count}checked="checked"{/if} />
                        {l s='Dimensions' mod='orderfees_shipping'}
                    </label>
                </p>
                <div id="dimension_restriction_div">
                    <table id="dimension_rule_group_table" class="table table-logical">
                        {foreach from=$dimension_rule_groups item='dimension_rule_group'}
                            {$dimension_rule_group nofilter}
                        {/foreach}
                    </table>
                    <a href="javascript:addDimensionRuleGroup();" class="btn btn-default ">
                        <i class="icon-plus-sign"></i> {l s='Dimension selection' mod='orderfees_shipping'}
                    </a>
                </div>
                
                <p class="checkbox">
                    <label>
                        <input type="checkbox" id="package_restriction" name="package_restriction" value="1" {if $package_rule_groups|@count}checked="checked"{/if} />
                        {l s='Package dimensions with volumetric weight' mod='orderfees_shipping'}
                    </label>
                </p>

                <div id="package_restriction_div">
                    <table id="package_rule_group_table" class="table">
                        {foreach from=$package_rule_groups item='package_rule_group'}
                            {$package_rule_group nofilter}
                        {/foreach}
                    </table>
                    <a href="javascript:addPackageRuleGroup();" class="btn btn-default ">
                        <i class="icon-plus-sign"></i> {l s='Package dimension selection' mod='orderfees_shipping'}
                    </a>
                </div>
                    
                <p class="checkbox">
                    <label>
                        <input type="checkbox" id="date_restriction" name="date_restriction" value="1" {if $controller->getFieldValue($rule, 'date_from') || $controller->getFieldValue($rule, 'date_to')}checked="checked"{/if} />
                        {l s='Date' mod='orderfees_shipping'}
                    </label>
                </p>

                <div id="date_restriction_div">
                    <span class="help-block">{l s='Define the start and/or end date of the rule.' mod='orderfees_shipping'}</span>
                    <div class="row">
                        <div class="col-lg-1 form-control-static text-center">{l s='From' mod='orderfees_shipping'}</div>
                        <div class="col-lg-2">
                            <div class="input-group">
                                <input id="date_from" name="date_from" class="datepicker" type="text" value="{$controller->getFieldValue($rule, 'date_from')|date_format:'%Y-%m-%d'|escape:'html':'UTF-8'}">
                                <span class="input-group-addon">
                                    <i class="icon-calendar"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-lg-1 form-control-static text-center">{l s='To' mod='orderfees_shipping'}</div>
                        <div class="col-lg-2">
                            <div class="input-group">
                                <input id="date_to" name="date_to" class="datepicker" type="text" value="{$controller->getFieldValue($rule, 'date_to')|date_format:'%Y-%m-%d'|escape:'html':'UTF-8'}">
                                <span class="input-group-addon">
                                    <i class="icon-calendar"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                                
                <p class="checkbox">
                    <label>
                        <input type="checkbox" id="time_restriction" name="time_restriction" value="1" {if $controller->getFieldValue($rule, 'time_from') || $controller->getFieldValue($rule, 'time_to')}checked="checked"{/if} />
                        {l s='Time' mod='orderfees_shipping'}
                    </label>
                </p>

                <div id="time_restriction_div">
                    <span class="help-block">{l s='Define the start and end time of the rule.' mod='orderfees_shipping'}</span>
                    <div class="row">
                        <div class="col-lg-1 form-control-static text-center">{l s='From' mod='orderfees_shipping'}</div>
                        <div class="col-lg-2">
                            <div class="input-group">
                                <input id="time_from" name="time_from" type="text" value="{$controller->getFieldValue($rule, 'time_from')|escape:'html':'UTF-8'}">
                                <span class="input-group-addon">
                                    <i class="icon-clock-o"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-lg-1 form-control-static text-center">{l s='To' mod='orderfees_shipping'}</div>
                        <div class="col-lg-2">
                            <div class="input-group">
                                <input id="time_to" name="time_to" type="text" value="{$controller->getFieldValue($rule, 'time_to')|escape:'html':'UTF-8'}">
                                <span class="input-group-addon">
                                    <i class="icon-clock-o"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                                
                {if ($titles.unselected|@count) + ($titles.selected|@count) > 1}
			<p class="checkbox">
				<label>
					<input type="checkbox" id="gender_restriction" name="gender_restriction" value="1" {if $titles.unselected|@count}checked="checked"{/if} />
					{l s='Titles' mod='orderfees_shipping'}
				</label>
			</p>
			<div id="gender_restriction_div">
				<table class="table">
					<tr>
						<td>
							<p>{l s='Unselected titles' mod='orderfees_shipping'}</p>
							<select id="gender_select_1" multiple>
								{foreach from=$titles.unselected item='gender'}
									<option value="{$gender.id_gender|intval}">&nbsp;{$gender.name|escape}</option>
								{/foreach}
							</select>
							<a id="gender_select_add" class="btn btn-default btn-block clearfix">{l s='Add' mod='orderfees_shipping'} <i class="icon-arrow-right"></i></a>
						</td>
						<td>
							<p>{l s='Selected titles' mod='orderfees_shipping'}</p>
							<select name="gender_select[]" id="gender_select_2" class="input-large" multiple>
								{foreach from=$titles.selected item='gender'}
									<option value="{$gender.id_gender|intval}">&nbsp;{$gender.name|escape}</option>
								{/foreach}
							</select>
							<a id="gender_select_remove" class="btn btn-default btn-block clearfix"><i class="icon-arrow-left"></i> {l s='Remove' mod='orderfees_shipping'} </a>
						</td>
					</tr>
				</table>
			</div>
		{/if}
                
                {if $rule->isSupportedType('nb_supplier')}
                    <p class="checkbox">
                        <label>
                            <input type="checkbox" id="nb_supplier_restriction" name="nb_supplier_restriction" value="1" {if $controller->getFieldValue($rule, 'nb_supplier_min') !== false}checked="checked"{/if} />
                            {l s='Number of suppliers' mod='orderfees_shipping'}
                        </label>
                    </p>

                    <div id="nb_supplier_restriction_div">
                        <span class="help-block">{l s='Define the number of suppliers' mod='orderfees_shipping'}</span>
                        <div class="row">
                            <div class="col-lg-1 form-control-static text-center">{l s='Min' mod='orderfees_shipping'}</div>
                            <div class="col-lg-2">
                                <input id="nb_supplier_min" name="nb_supplier_min" type="text" value="{$controller->getFieldValue($rule, 'nb_supplier_min')|escape:'html':'UTF-8'}">
                            </div>
                            <div class="col-lg-1 form-control-static text-center">{l s='Max' mod='orderfees_shipping'}</div>
                            <div class="col-lg-2">
                                <input id="nb_supplier_max" name="nb_supplier_max" type="text" value="{$controller->getFieldValue($rule, 'nb_supplier_max')|escape:'html':'UTF-8'}">
                            </div>
                        </div>
                    </div>
                {/if}
                                
                {hook h="displayOrderFeesShippingFormConditionsRestrictionsAfter" module=$module controller=$controller object=$rule}

		{if ($shops.unselected|@count) + ($shops.selected|@count) > 1}
			<p class="checkbox">
				<label>
					<input type="checkbox" id="shop_restriction" name="shop_restriction" value="1" {if $shops.unselected|@count}checked="checked"{/if} />
					{l s='Shop' mod='orderfees_shipping'}
				</label>
			</p>
			<div id="shop_restriction_div">
				<br/>
				<table class="table">
					<tr>
						<td>
							<p>{l s='Unselected shops' mod='orderfees_shipping'}</p>
							<select id="shop_select_1" multiple>
								{foreach from=$shops.unselected item='shop'}
									<option value="{$shop.id_shop|intval}">&nbsp;{$shop.name|escape}</option>
								{/foreach}
							</select>
							<br/>
							<a id="shop_select_add" class="btn btn-default" >{l s='Add' mod='orderfees_shipping'} &gt;&gt; </a>
						</td>
						<td>
							<p>{l s='Selected shops' mod='orderfees_shipping'}</p>
							<select name="shop_select[]" id="shop_select_2" multiple>
								{foreach from=$shops.selected item='shop'}
									<option value="{$shop.id_shop|intval}">&nbsp;{$shop.name|escape}</option>
								{/foreach}
							</select>
							<br/>
							<a id="shop_select_remove" class="btn btn-default" > &lt;&lt; {l s='Remove' mod='orderfees_shipping'} </a>
						</td>
					</tr>
				</table>
			</div>
		{/if}
	</div>
</div>
        
{hook h="displayOrderFeesShippingFormConditionsAfter" module=$module controller=$controller object=$rule}

<script type="text/javascript">
    var message_errors_city_select_country = "{l s='Please select a country.' mod='orderfees_shipping'}";
    var message_errors_zipcode_select_country = "{l s='Please select a country.' mod='orderfees_shipping'}";
    var message_errors_dimension_select_dimension = "{l s='Please select a dimension (eg. : width, height, ...).' mod='orderfees_shipping'}";
    var message_errors_package_range_start = "{l s='Please enter a starting weight for this range.' mod='orderfees_shipping'}";
    var message_errors_package_range_end = "{l s='Please enter an ending weight for this range.' mod='orderfees_shipping'}";
    var message_errors_package_range = "{l s='The ending weight must be greater than the starting weight.' mod='orderfees_shipping'}";
    var message_errors_package_unit = "{l s='Please select or provide a Weight/Volume ratio.' mod='orderfees_shipping'}";
</script>