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
    <i class="fas fa-shopping-cart"></i>&nbsp;<span class="tab_name">{l s='Total manufacturers sales' mod='ntstats'}</span>
</div>
<div>
    <form class="form_grid">
        <div>
            <button type="button" class="btn btn-default all_dates_btn" id="total_manufacturers_sales_all_dates" name="total_manufacturers_sales_all_dates">
                <i class="fas fa-calendar-alt"></i>&nbsp;{l s='All dates' mod='ntstats'}
            </button>
        </div>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='From' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_from form-control" name="total_manufacturers_sales_date_from" id="total_manufacturers_sales_date_from"
                       value="{$date_from|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='To' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_to form-control" name="total_manufacturers_sales_date_to" id="total_manufacturers_sales_date_to"
                       value="{$date_to|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="filter_block">
            <select id="total_manufacturers_sales_id_group" name="total_manufacturers_sales_id_group[]"
                    class="select2_field form-control" multiple="multiple" data-placeholder="{l s='All groups' mod='ntstats'}">
                {foreach $list_groups as $group}
                    <option value="{$group.id_group|intval}">{$group.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <select id="total_manufacturers_sales_id_manufacturer" name="total_manufacturers_sales_id_manufacturer[]"
                    class="select2_field form-control" multiple="multiple" data-placeholder="{l s='All manufacturers' mod='ntstats'}">
                {foreach $list_order_manufacturers as $manufacturer}
                    <option value="{$manufacturer.id_manufacturer|intval}">{$manufacturer.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default filter_btn" id="total_manufacturers_sales_valid" name="total_manufacturers_sales_valid">
                <i class="fas fa-check"></i>&nbsp;{l s='Filter' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_csv_btn" id="total_manufacturers_sales_exp_csv" name="total_manufacturers_sales_exp_csv">
                <i class="fas fa-file-download"></i>&nbsp;{l s='Export CSV' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_xls_btn" id="total_manufacturers_sales_exp_xls" name="total_manufacturers_sales_exp_xls">
                <i class="fas fa-file-excel"></i>&nbsp;{l s='Export Excel' mod='ntstats'}
            </button>
        </div>
        <input type="hidden" name="type_list" value="total_manufacturers_sales"/>
        <span class="clear"></span>
    </form>
    <span class="clear"></span>
    <br/>
    <div class="stats_data">
        <table id="total_manufacturers_sales" class="data_table" width="100%" data-sorting="true" data-colreorder="true">
            <thead>
                <tr>
                    <th class="chart_label" title="{l s='The manufacturer name' mod='ntstats'}">
                        {l s='Name' mod='ntstats'}
                    </th>
                    <th title="{l s='The number of products of this manufacturer sold during the chosen period' mod='ntstats'}">
                        {l s='Qty sold' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The percentage of products sold for that manufacturer for the period' mod='ntstats'}">
                        {l s='Qty sold' mod='ntstats'} (%) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The number of customer having bought for that manufacturer for the period' mod='ntstats'}">
                        {l s='Nb customers' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The percentage of customer having bought for that manufacturer for the period' mod='ntstats'}">
                        {l s='Nb customers' mod='ntstats'} (%) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The amount of all the products of this manufacturer sold for the period' mod='ntstats'}">
                        {l s='Amount' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The number of products of this manufacturer returned for the period' mod='ntstats'}">
                        {l s='Qty returned' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The amount of all the products of this manufacturer refunded for the period' mod='ntstats'}">
                        {l s='Refund' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The amount of discount for products of this manufacturer for the period' mod='ntstats'}">
                        {l s='Discount' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The amount of purchase cost for products of this manufacturer for the period' mod='ntstats'}">
                        {l s='Purchase cost' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The margin for this manufacturer for the period' mod='ntstats'}">
                        {l s='Margin' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The percentage of products of this manufacturer returned for the period' mod='ntstats'}">
                        {l s='Qty returned' mod='ntstats'} (%) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The percentage of amount of products of this manufacturer refunded for the period' mod='ntstats'}">
                        {l s='Refund amount' mod='ntstats'} (%) <i class="fas fa-chart-pie"></i>
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
                <canvas id="total_manufacturers_sales_chart" height="560"></canvas>
            </div>
        </div>
    </div>
</div>