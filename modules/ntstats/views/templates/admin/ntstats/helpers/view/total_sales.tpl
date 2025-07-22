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
    <i class="fas fa-shopping-cart"></i>&nbsp;<span class="tab_name">{l s='Total sales' mod='ntstats'}</span>
</div>
<div>
    <form class="form_grid">
        <div>
            <button type="button" class="btn btn-default all_dates_btn" id="total_sales_all_dates" name="total_sales_all_dates">
                <i class="fas fa-calendar-alt"></i>&nbsp;{l s='All dates' mod='ntstats'}
            </button>
        </div>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='From' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_from form-control" name="total_sales_date_from" id="total_sales_date_from" value="{$date_from|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='To' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_to form-control" name="total_sales_date_to" id="total_sales_date_to" value="{$date_to|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="filter_block">
            <select id="total_sales_id_group" name="total_sales_id_group[]"
                    class="select2_field form-control" multiple="multiple" data-placeholder="{l s='All groups' mod='ntstats'}">
                {foreach $list_groups as $group}
                    <option value="{$group.id_group|intval}">{$group.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default filter_btn" id="total_sales_valid" name="total_sales_valid">
                <i class="fas fa-check"></i>&nbsp;{l s='Filter' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_csv_btn" id="total_sales_exp_csv" name="total_sales_exp_csv">
                <i class="fas fa-file-download"></i>&nbsp;{l s='Export CSV' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_xls_btn" id="total_sales_exp_xls" name="total_sales_exp_xls">
                <i class="fas fa-file-excel"></i>&nbsp;{l s='Export Excel' mod='ntstats'}
            </button>
        </div>
        <input type="hidden" name="type_list" value="total_sales"/>
        <span class="clear"></span>
    </form>
    <span class="clear"></span>
    <br/>
    <div class="stats_data">
        <table id="total_sales" class="data_table" width="100%" data-sorting="true" data-colreorder="true">
            <thead>
                <tr>
                    <th class="chart_label reverse" title="{l s='The day of sales' mod='ntstats'}">
                        {l s='Days' mod='ntstats'}
                    </th>
                    <th class="nb_orders" title="{l s='Number of valid orders for that day' mod='ntstats'}">
                        {l s='Nb orders' mod='ntstats'} <i class="fas fa-chart-line"></i>
                    </th>
                    <th class="total_product" title="{l s='Total amount of products in those orders' mod='ntstats'}">
                        {l s='Product' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-line"></i>
                    </th>
                    <th class="total_product_vat" title="{l s='Total amount of products VAT in those orders' mod='ntstats'}">
                        {l s='Product VAT' mod='ntstats'} <i class="fas fa-chart-line"></i>
                    </th>
                    <th class="total_shipping" title="{l s='Total amount of shipping in those orders' mod='ntstats'}">
                        {l s='Shipping' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-line"></i>
                    </th>
                    <th class="total_shipping_vat" title="{l s='Total amount of shipping VAT in those orders' mod='ntstats'}">
                        {l s='Shipping VAT' mod='ntstats'} <i class="fas fa-chart-line"></i>
                    </th>
                    <th class="total_shipping_refund" title="{l s='Total amount of shipping refunded that day' mod='ntstats'}">
                        {l s='Shipping refund' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-line"></i>
                    </th>
                    <th class="total_shipping_refund_vat" title="{l s='Total amount of shipping VAT refunded that day' mod='ntstats'}">
                        {l s='Shipping refund VAT' mod='ntstats'} <i class="fas fa-chart-line"></i>
                    </th>
                    <th class="total_product_refund" title="{l s='Total amount of products refunded that day' mod='ntstats'}">
                        {l s='Product refund' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-line"></i>
                    </th>
                    <th class="total_product_refund_vat" title="{l s='Total amount of products VAT refunded that day' mod='ntstats'}">
                        {l s='Product refund VAT' mod='ntstats'} <i class="fas fa-chart-line"></i>
                    </th>
                    <th class="total_discount" title="{l s='Total amount of discount used in those orders' mod='ntstats'}">
                        {l s='Discount' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-line"></i>
                    </th>
                    <th class="total_discount_vat" title="{l s='Total amount of discount VAT used in those orders' mod='ntstats'}">
                        {l s='Discount VAT' mod='ntstats'} <i class="fas fa-chart-line"></i>
                    </th>
                    <th class="total_cost" title="{l s='Total purchase cost tax excl. of the products sold in those orders' mod='ntstats'}">
                        {l s='Purchase cost' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-line"></i>
                    </th>
                    <th class="total_margin" title="{l s='Total margin for the day. ("Sales" - "Purchase cost")' mod='ntstats'}">
                        {l s='Margin' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-line"></i>
                    </th>
                    <th class="total_margin_per" title="{l s='Total margin in percentage for the day' mod='ntstats'}">
                        {l s='Margin' mod='ntstats'} % <i class="fas fa-chart-line"></i>
                    </th>
                    <th class="sales" title="{l s='Sales amount for that day. It means "Product" - "Product refund" - "Discount"' mod='ntstats'}">
                        {l s='Sales' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-line"></i>
                    </th>
                    <th class="average_cart" title="{l s='Average amount of carts for those orders' mod='ntstats'}">
                        {l s='Average cart' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-line"></i>
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
                <canvas id="total_sales_chart" height="560"></canvas>
            </div>
        </div>
    </div>
</div>