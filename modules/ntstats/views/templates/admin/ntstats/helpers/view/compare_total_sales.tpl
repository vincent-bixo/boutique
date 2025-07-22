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
    <i class="fas fa-balance-scale-right"></i>&nbsp;<span class="tab_name">{l s='Compare total sales' mod='ntstats'}</span>
</div>
<div>
    <form>
        <div class="form_grid compare">
            <div class="compare_date">
                <div class="input-group">
                    <span class="input-group-addon">{l s='From' mod='ntstats'}</span>
                    <input type="text" class="datepicker input-medium form-control" name="compare_total_sales_date_from_period1" id="compare_total_sales_date_from_period1"
                           value="{$date_from_prev|escape:'html':'UTF-8'}" />
                    <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
                </div>
            </div>
            <div class="compare_date">
                <div class="input-group">
                    <span class="input-group-addon">{l s='To' mod='ntstats'}</span>
                    <input type="text" class="datepicker input-medium form-control" name="compare_total_sales_date_to_period1" id="compare_total_sales_date_to_period1"
                           value="{$date_to_prev|escape:'html':'UTF-8'}" />
                    <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
                </div>
                <i class="fas fa-bolt separator_period"></i>
            </div>
            <div class="compare_date">
                <div class="input-group">
                    <span class="input-group-addon">{l s='From' mod='ntstats'}</span>
                    <input type="text" class="datepicker input-medium form-control" name="compare_total_sales_date_from_period2" id="compare_total_sales_date_from_period2"
                           value="{$date_from|escape:'html':'UTF-8'}" />
                    <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
                </div>
            </div>
            <div class="compare_date">
                <div class="input-group">
                    <span class="input-group-addon">{l s='To' mod='ntstats'}</span>
                    <input type="text" class="datepicker input-medium form-control" name="compare_total_sales_date_to_period2" id="compare_total_sales_date_to_period2"
                           value="{$date_to|escape:'html':'UTF-8'}" />
                    <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
                </div>
            </div>
            <span class="clear"></span>
        </div>
        <div class="form_grid">
            <div class="filter_block">
                <select id="compare_total_sales_id_group" name="compare_total_sales_id_group[]"
                        class="select2_field form-control" multiple="multiple" data-placeholder="{l s='All groups' mod='ntstats'}">
                    {foreach $list_groups as $group}
                        <option value="{$group.id_group|intval}">{$group.name|escape:'html':'UTF-8'}</option>
                    {/foreach}
                </select>
            </div>
            <div class="filter_block">
                <button type="button" class="btn btn-default filter_btn" id="compare_total_sales_valid" name="compare_total_sales_valid">
                    <i class="fas fa-check"></i>&nbsp;{l s='Filter' mod='ntstats'}
                </button>
            </div>
            <div class="filter_block">
                <button type="button" class="btn btn-default exp_csv_btn" id="compare_total_sales_exp_csv" name="compare_total_sales_exp_csv">
                    <i class="fas fa-file-download"></i>&nbsp;{l s='Export CSV' mod='ntstats'}
                </button>
            </div>
            <div class="filter_block">
                <button type="button" class="btn btn-default exp_xls_btn" id="compare_total_sales_exp_xls" name="compare_total_sales_exp_xls">
                    <i class="fas fa-file-excel"></i>&nbsp;{l s='Export Excel' mod='ntstats'}
                </button>
            </div>
            <input type="hidden" name="type_list" value="compare_total_sales"/>
            <span class="clear"></span>
        </div>
    </form>
    <span class="clear"></span>
    <br/>
    <div class="stats_data">
        <table id="compare_total_sales" class="data_table" width="100%" data-sorting="true" data-colreorder="true">
            <thead>
                <tr>
                    <th class="chart_label" title="{l s='The start of the period' mod='ntstats'}">
                        {l s='From' mod='ntstats'}
                    </th>
                    <th class="chart_label" title="{l s='The end of the period' mod='ntstats'}">
                        {l s='To' mod='ntstats'}
                    </th>
                    <th title="{l s='The number of valid orders for the period' mod='ntstats'}">
                        {l s='Nb orders' mod='ntstats'} <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The total amount of product in those orders' mod='ntstats'}">
                        {l s='Product' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The total amount of shipping in those orders' mod='ntstats'}">
                        {l s='Shipping' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The total shipping amount refunded for the period' mod='ntstats'}">
                        {l s='Shipping refund' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The total product amount refunded for the period' mod='ntstats'}">
                        {l s='Product refund' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The total amount of discount used for the period' mod='ntstats'}">
                        {l s='Discount' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The total purchase cost tax excl. of the products sold for the period' mod='ntstats'}">
                        {l s='Purchase cost' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The total margin for the period. ("Sales" - "Purchase cost")' mod='ntstats'}">
                        {l s='Margin' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The sales amount for the period. It means "Product" - "Product refund" - "Discount"' mod='ntstats'}">
                        {l s='Sales' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The average amount of the cart for those orders' mod='ntstats'}">
                        {l s='Average cart' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-bar"></i>
                    </th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
        <div class="data_chart">
            <i class="fas fa-list"></i>
            <br/>
            <div class="canvas_block">
                <canvas id="compare_total_sales_chart" height="560"></canvas>
            </div>
        </div>
    </div>
</div>