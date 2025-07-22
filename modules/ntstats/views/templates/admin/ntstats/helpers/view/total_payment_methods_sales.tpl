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
    <i class="fas fa-shopping-cart"></i>&nbsp;<span class="tab_name">{l s='Total payment methods sales' mod='ntstats'}</span>
</div>
<div>
    <form class="form_grid">
        <div>
            <button type="button" class="btn btn-default all_dates_btn" id="total_payment_methods_sales_all_dates" name="total_payment_methods_sales_all_dates">
                <i class="fas fa-calendar-alt"></i>&nbsp;{l s='All dates' mod='ntstats'}
            </button>
        </div>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='From' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_from form-control" name="total_payment_methods_sales_date_from" id="total_payment_methods_sales_date_from"
                       value="{$date_from|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='To' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_to form-control" name="total_payment_methods_sales_date_to" id="total_payment_methods_sales_date_to"
                       value="{$date_to|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="filter_block">
            <select id="total_payment_methods_sales_id_group" name="total_payment_methods_sales_id_group[]"
                    class="select2_field form-control" multiple="multiple" data-placeholder="{l s='All groups' mod='ntstats'}">
                {foreach $list_groups as $group}
                    <option value="{$group.id_group|intval}">{$group.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <select id="total_payment_methods_sales_payment_method" name="total_payment_methods_sales_payment_method[]"
                    class="select2_field form-control" multiple="multiple" data-placeholder="{l s='All payment methods' mod='ntstats'}">
                {foreach $list_payments as $payment}
                    <option value="{$payment.payment_method|escape:'html':'UTF-8'}">{$payment.payment_method|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default filter_btn" id="total_payment_methods_sales_valid" name="total_payment_methods_sales_valid">
                <i class="fas fa-check"></i>&nbsp;{l s='Filter' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_csv_btn" id="total_payment_methods_sales_exp_csv" name="total_payment_methods_sales_exp_csv">
                <i class="fas fa-file-download"></i>&nbsp;{l s='Export CSV' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_xls_btn" id="total_payment_methods_sales_exp_xls" name="total_payment_methods_sales_exp_xls">
                <i class="fas fa-file-excel"></i>&nbsp;{l s='Export Excel' mod='ntstats'}
            </button>
        </div>
        <input type="hidden" name="type_list" value="total_payment_methods_sales"/>
        <span class="clear"></span>
    </form>
    <span class="clear"></span>
    <br/>
    <div class="stats_data">
        <table id="total_payment_methods_sales" class="data_table" width="100%" data-sorting="true" data-colreorder="true">
            <thead>
                <tr>
                    <th class="chart_label" title="{l s='The payment method name' mod='ntstats'}">
                        {l s='Name' mod='ntstats'}
                    </th>
                    <th title="{l s='The number of customer having paid with that payment method for the period' mod='ntstats'}">
                        {l s='Nb customers' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The percentage of customer having paid with that payment method for the period' mod='ntstats'}">
                        {l s='Nb customers' mod='ntstats'} (%) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The amount tax incl. paid with that payment method for the period' mod='ntstats'}">
                        {l s='Amount tax incl.' mod='ntstats'} <i class="fas fa-chart-pie"></i>
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
                <canvas id="total_payment_methods_sales_chart" height="560"></canvas>
            </div>
        </div>
    </div>
</div>