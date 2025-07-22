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
    <i class="fas fa-shopping-cart"></i>&nbsp;<span class="tab_name">{l s='Total combinations sales' mod='ntstats'}</span>
</div>
<div>
    <form class="form_grid">
        <div>
            <button type="button" class="btn btn-default all_dates_btn" id="total_combinations_sales_all_dates" name="total_combinations_sales_all_dates">
                <i class="fas fa-calendar-alt"></i>&nbsp;{l s='All dates' mod='ntstats'}
            </button>
        </div>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='From' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_from form-control" name="total_combinations_sales_date_from" id="total_combinations_sales_date_from"
                       value="{$date_from|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='To' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_to form-control" name="total_combinations_sales_date_to" id="total_combinations_sales_date_to"
                       value="{$date_to|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="filter_block">
            <select id="total_combinations_sales_id_group" name="total_combinations_sales_id_group[]" class="form-control select2_field"
                    multiple="multiple" data-placeholder="{l s='All groups' mod='ntstats'}">
                {foreach $list_groups as $group}
                    <option value="{$group.id_group|intval}">{$group.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <select id="total_combinations_sales_id_manufacturer" name="total_combinations_sales_id_manufacturer[]" class="form-control select2_field"
                    multiple="multiple" data-placeholder="{l s='All manufacturers' mod='ntstats'}">
                {foreach $list_manufacturers as $manufacturer}
                    <option value="{$manufacturer.id_manufacturer|intval}">{$manufacturer.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div>
            <div class="filter_block">
                <select id="total_combinations_sales_id_country_invoice" name="total_combinations_sales_id_country_invoice[]"
                        class="select2_field form-control" multiple="multiple" data-placeholder="{l s='All countries' mod='ntstats'}">
                    {foreach $list_countries as $country}
                        <option value="{$country.id_country|intval}">{$country.name|escape:'html':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div>
            <div class="filter_block">
                <select id="total_combinations_sales_id_feature" name="total_combinations_sales_id_feature[]"
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
                <select id="total_combinations_sales_id_feature_value" name="total_combinations_sales_id_feature_value[]"
                        class="select2_field form-control select_id_feature_value" multiple="multiple" data-placeholder="{l s='All feature values' mod='ntstats'}">
                </select>
            </div>
        </div>
        <div class="filter_block">
            <select id="total_combinations_sales_id_category" name="total_combinations_sales_id_category[]" class="form-control select2_field select_id_category"
                    multiple="multiple" data-placeholder="{l s='All categories' mod='ntstats'}">
                {* Data in ajax to prevent too long loading time of the page*}
            </select>
        </div>
        <div class="filter_block">
            <select id="total_combinations_sales_id_product" name="total_combinations_sales_id_product[]"
                    class="select_id_product form-control select2_field display_products_ordered" multiple="multiple" data-placeholder="{l s='All products' mod='ntstats'}">
                {foreach $list_order_products as $product}
                    <option value="{$product.id_product|intval}" class="category_id">{$product.reference|escape:'html':'UTF-8'} - {$product.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <select id="total_combinations_sales_id_combination" name="total_combinations_sales_id_combination[]"
                    class="select_id_combination form-control select2_field display_combinations_ordered"
                    multiple="multiple" data-placeholder="{l s='All combinations' mod='ntstats'}">
                {*{foreach $list_combinations as $combination}
                    <option class="product_id_{$combination.id_product|intval} product_id" value="{$combination.id_product_attribute|intval}">
                        {$combination.reference|escape:'html':'UTF-8'} - {$combination.name|escape:'html':'UTF-8'} {$combination.combination|escape:'html':'UTF-8'}
                    </option>
                {/foreach}*}
            </select>
        </div>
        <div class="filter_block">
            <select id="total_combinations_sales_simple" name="total_combinations_sales_simple" class="form-control">
                <option value="1" selected="selected">{l s='All products with and without combinations' mod='ntstats'}</option>
                <option value="0">{l s='Only products with combinations' mod='ntstats'}</option>
            </select>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default filter_btn" id="total_combinations_sales_valid" name="total_combinations_sales_valid">
                <i class="fas fa-check"></i>&nbsp;{l s='Filter' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_csv_btn" id="total_combinations_sales_exp_csv" name="total_combinations_sales_exp_csv">
                <i class="fas fa-file-download"></i>&nbsp;{l s='Export CSV' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_xls_btn" id="total_combinations_sales_exp_xls" name="total_combinations_sales_exp_xls">
                <i class="fas fa-file-excel"></i>&nbsp;{l s='Export Excel' mod='ntstats'}
            </button>
        </div>
        <input type="hidden" name="type_list" value="total_combinations_sales"/>
        <span class="clear"></span>
    </form>
    <span class="clear"></span>
    <br/>
    <div class="stats_data">
        <table id="total_combinations_sales" class="data_table" width="100%" data-sorting="true" data-colreorder="true">
            <thead>
                <tr>
                    <th class="chart_label" title="{l s='The combination reference' mod='ntstats'}">
                        {l s='Reference' mod='ntstats'}
                    </th>
                    <th class="chart_label" title="{l s='The product reference' mod='ntstats'}">
                        {l s='Product reference' mod='ntstats'}
                    </th>
                    <th class="chart_label" title="{l s='The name of the product with the combination' mod='ntstats'}">
                        {l s='Name' mod='ntstats'}
                    </th>
                    <th title="{l s='The number of this combination sold during the chosen period' mod='ntstats'}">
                        {l s='Qty sold' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The current quantity of this combination' mod='ntstats'}">
                        {l s='Qty current' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='Hint on the needed quantity to buy or to discard for the same period. a positive value means buy, a negative value means discard.' mod='ntstats'}">
                        {l s='Need' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The amount of all the instance of this combination sold for the period' mod='ntstats'}">
                        {l s='Combination' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The number of this combination returned for the period' mod='ntstats'}">
                        {l s='Qty returned' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The amount of all the instance of this combination refunded for the period' mod='ntstats'}">
                        {l s='Refund' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The percentage of this combination returned for the period' mod='ntstats'}">
                        {l s='Qty returned' mod='ntstats'} (%) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The percentage of amount of this combination refunded for the period' mod='ntstats'}">
                        {l s='Refund amount' mod='ntstats'} (%) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The purchase cost tax excl. of this combination for this period' mod='ntstats'}">
                        {l s='Purchase cost' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The pro-rata discount from the order for this combination for this period' mod='ntstats'}">
                        {l s='Order discount' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The unit margin for this combination for this period' mod='ntstats'}">
                        {l s='Unit margin' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The margin for this combination for this period' mod='ntstats'}">
                        {l s='Margin' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The percentage of margin for this combination for this period' mod='ntstats'}">
                        {l s='Margin' mod='ntstats'} % <i class="fas fa-chart-pie"></i>
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
                <canvas id="total_combinations_sales_chart" height="560"></canvas>
            </div>
        </div>
    </div>
</div>