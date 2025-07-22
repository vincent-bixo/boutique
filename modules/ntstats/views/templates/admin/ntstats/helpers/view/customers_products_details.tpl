{*
* 2013-2024 2N Technologies
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to contact@2n-tech.com so we can send you a copy immediately.
*
* @author    2N Technologies <contact@2n-tech.com>
* @copyright 2013-2024 2N Technologies
* @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

<div class="panel-heading">
    <i class="fas fa-user"></i>&nbsp;<span class="tab_name">{l s='Customers products details' mod='ntstats'}</span>
</div>
<div>
    <form class="form_grid">
        <button type="button" class="btn btn-default all_dates_btn" id="customers_products_details_all_dates" name="customers_products_details_all_dates">
            <i class="fas fa-calendar-alt"></i>&nbsp;{l s='All dates' mod='ntstats'}
        </button>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='From' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_from form-control" name="customers_products_details_date_from" id="customers_products_details_date_from"
                       value="{$date_from|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='To' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_to form-control" name="customers_products_details_date_to" id="customers_products_details_date_to"
                       value="{$date_to|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="filter_block">
            <select id="customers_products_details_id_group" name="customers_products_details_id_group[]" class="form-control select2_field"
                    multiple="multiple" data-placeholder="{l s='All groups' mod='ntstats'}">
                {foreach $list_groups as $group}
                    <option value="{$group.id_group|intval}">{$group.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <select id="customers_products_details_id_manufacturer" name="customers_products_details_id_manufacturer[]" class="form-control select2_field"
                    multiple="multiple" data-placeholder="{l s='All manufacturers' mod='ntstats'}">
                {foreach $list_manufacturers as $manufacturer}
                    <option value="{$manufacturer.id_manufacturer|intval}">{$manufacturer.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div>
            <div class="filter_block">
                <select id="customers_products_details_id_feature" name="customers_products_details_id_feature[]"
                        class="select2_field form-control select_id_feature" multiple="multiple" data-placeholder="{l s='All features' mod='ntstats'}">
                    {foreach $list_features as $feature}
                        <option value="{$feature.id_feature|intval}">
                            {$feature.name|escape:'html':'UTF-8'}
                        </option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div>
            <div class="filter_block">
                <select id="customers_products_details_id_feature_value" name="customers_products_details_id_feature_value[]"
                        class="select2_field form-control select_id_feature_value" multiple="multiple" data-placeholder="{l s='All feature values' mod='ntstats'}">
                </select>
            </div>
        </div>
        <div class="filter_block">
            <select id="customers_products_details_id_category" name="customers_products_details_id_category[]" class="select_id_category form-control select2_field"
                    multiple="multiple" data-placeholder="{l s='All categories' mod='ntstats'}">
                {* Data in ajax to prevent too long loading time of the page*}
            </select>
        </div>
        <div class="filter_block">
            <select id="customers_products_details_id_product" name="customers_products_details_id_product[]" class="select_id_product form-control select2_field display_products_ordered"
                    multiple="multiple" data-placeholder="{l s='All products' mod='ntstats'}">
                {foreach $list_order_products as $product}
                    <option value="{$product.id_product|intval}" class="category_id">
                        {$product.reference|escape:'html':'UTF-8'} - {$product.name|escape:'html':'UTF-8'}
                    </option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <select id="customers_products_details_id_combination" name="customers_products_details_id_combination[]"
                    class="select_id_combination form-control select2_field display_combinations_ordered"
                    multiple="multiple" data-placeholder="{l s='All combinations' mod='ntstats'}">
            </select>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default filter_btn" id="customers_products_details_valid" name="customers_products_details_valid">
                <i class="fas fa-check"></i>&nbsp;{l s='Filter' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_csv_btn" id="customers_products_details_exp_csv" name="customers_products_details_exp_csv">
                <i class="fas fa-file-download"></i>&nbsp;{l s='Export CSV' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_xls_btn" id="customers_products_details_exp_xls" name="customers_products_details_exp_xls">
                <i class="fas fa-file-excel"></i>&nbsp;{l s='Export Excel' mod='ntstats'}
            </button>
        </div>
        <input type="hidden" name="type_list" value="customers_products_details"/>
        <span class="clear"></span>
    </form>
    <span class="clear"></span>
    <br/>
    <div class="stats_data">
        <table id="customers_products_details" class="data_table" width="100%" data-sorting="true" data-colreorder="true">
            <thead>
                <tr>
                    <th title="{l s='The customer' mod='ntstats'}">
                        {l s='Customer' mod='ntstats'}
                    </th>
                    <th title="{l s='The customer ID' mod='ntstats'}">
                        {l s='Customer ID' mod='ntstats'}
                    </th>
                    <th title="{l s='The customer social title' mod='ntstats'}">
                        {l s='Social title' mod='ntstats'}
                    </th>
                    <th title="{l s='The customer age' mod='ntstats'}">
                        {l s='Age' mod='ntstats'}
                    </th>
                    <th title="{l s='The order reference' mod='ntstats'}">
                        {l s='Order' mod='ntstats'}
                    </th>
                    <th title="{l s='The total amount paid by the customer tax excluded' mod='ntstats'}">
                        {l s='Total tax excl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The total VAT paid by the customer' mod='ntstats'}">
                        {l s='VAT' mod='ntstats'}
                    </th>
                    <th title="{l s='The total amount paid by the customer tax included' mod='ntstats'}">
                        {l s='Total tax incl.' mod='ntstats'}
                    </th>
                    <th title="{l s='City of the delivery address' mod='ntstats'}">
                        {l s='City of the delivery address' mod='ntstats'}
                    </th>
                    <th title="{l s='Country of the delivery address' mod='ntstats'}">
                        {l s='Country of the delivery address' mod='ntstats'}
                    </th>
                    <th title="{l s='City of the invoice address' mod='ntstats'}">
                        {l s='City of the invoice address' mod='ntstats'}
                    </th>
                    <th title="{l s='Country of the invoice address' mod='ntstats'}">
                        {l s='Country of the invoice address' mod='ntstats'}
                    </th>
                    {if $use_invoice}
                    <th title="{l s='The order invoice date' mod='ntstats'}">
                        {l s='Invoice date' mod='ntstats'}
                    </th>
                    {else}
                        <th title="{l s='The order validity date' mod='ntstats'}">
                        {l s='Validity date' mod='ntstats'}
                    </th>
                    {/if}
                    <th title="{l s='The order payment date' mod='ntstats'}">
                        {l s='Payment date' mod='ntstats'}
                    </th>
                    <th title="{l s='The order payment method' mod='ntstats'}">
                        {l s='Payment method' mod='ntstats'}
                    </th>
                    <th title="{l s='The order state' mod='ntstats'}">
                        {l s='State' mod='ntstats'}
                    </th>
                    <th title="{l s='The product reference' mod='ntstats'}">
                        {l s='Product reference' mod='ntstats'}
                    </th>
                    <th title="{l s='The product name' mod='ntstats'}">
                        {l s='Product name' mod='ntstats'}
                    </th>
                    <th title="{l s='The product quantity' mod='ntstats'}">
                        {l s='Product qty' mod='ntstats'}
                    </th>
                    <th title="{l s='The product price tax excl.' mod='ntstats'}">
                        {l s='Product price tax excl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The product VAT' mod='ntstats'}">
                        {l s='Product VAT' mod='ntstats'}
                    </th>
                    <th title="{l s='The product price tax incl.' mod='ntstats'}">
                        {l s='Product price tax incl.' mod='ntstats'}
                    </th>
                </tr>
            </thead>
            <tbody>

            </tbody>
            <tfoot>

            </tfoot>
        </table>
        <div class="data_chart">
            <i class="fas fa-list"></i>
            <br/>
            <div class="canvas_block">
                <canvas id="customers_products_details_chart" height="560"></canvas>
            </div>
        </div>
    </div>
</div>