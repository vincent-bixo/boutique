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
    <i class="fas fa-user"></i>&nbsp;<span class="tab_name">{l s='Customers' mod='ntstats'}</span>
</div>
<div>
    <form class="form_grid">
        <button type="button" class="btn btn-default all_dates_btn" id="customers_all_dates" name="customers_all_dates">
            <i class="fas fa-calendar-alt"></i>&nbsp;{l s='All dates' mod='ntstats'}
        </button>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='From' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_from form-control" name="customers_date_from" id="customers_date_from"
                       value="{$date_from|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='To' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_to form-control" name="customers_date_to" id="customers_date_to"
                       value="{$date_to|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="filter_block">
            <select id="customers_id_group" name="customers_id_group[]" class="form-control select2_field"
                    multiple="multiple" data-placeholder="{l s='All groups' mod='ntstats'}">
                {foreach $list_groups as $group}
                    <option value="{$group.id_group|intval}">{$group.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default filter_btn" id="customers_valid" name="customers_valid">
                <i class="fas fa-check"></i>&nbsp;{l s='Filter' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_csv_btn" id="customers_exp_csv" name="customers_exp_csv">
                <i class="fas fa-file-download"></i>&nbsp;{l s='Export CSV' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_xls_btn" id="customers_exp_xls" name="customers_exp_xls">
                <i class="fas fa-file-excel"></i>&nbsp;{l s='Export Excel' mod='ntstats'}
            </button>
        </div>
        <input type="hidden" name="type_list" value="customers"/>
        <span class="clear"></span>
    </form>
    <span class="clear"></span>
    <br/>
    <div class="stats_data">
        <table id="customers" class="data_table" width="100%" data-sorting="true" data-colreorder="true">
            <thead>
                <tr>
                    <th class="chart_label" title="{l s='The customer' mod='ntstats'}">
                        {l s='Customer' mod='ntstats'}
                    </th>
                    <th title="{l s='The customer ID' mod='ntstats'}">
                        {l s='Customer ID' mod='ntstats'}
                    </th>
                    <th title="{l s='The nb of valid orders for this customer' mod='ntstats'}">
                        {l s='Nb valid orders' mod='ntstats'} <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The nb of invalid orders for this customer' mod='ntstats'}">
                        {l s='Nb invalid orders' mod='ntstats'} <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The nb of products for this customer' mod='ntstats'}">
                        {l s='Nb products' mod='ntstats'} <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The total amount (tax excl.) of this customer orders' mod='ntstats'}">
                        {l s='Orders tax excl.' mod='ntstats'} <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The average amount (tax excl.) of this customer orders' mod='ntstats'}">
                        {l s='Average order tax excl.' mod='ntstats'} <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The average product quantity of this customer orders' mod='ntstats'}">
                        {l s='Average product qty' mod='ntstats'} <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The average number of days between two orders for this customer' mod='ntstats'}">
                        {l s='Average nb days between orders' mod='ntstats'} <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The profit for this customer (products amount - discount amount - products cost)' mod='ntstats'}">
                        {l s='Profit tax excl' mod='ntstats'} <i class="fas fa-chart-bar"></i>
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
                <canvas id="customers_chart" height="560"></canvas>
            </div>
        </div>
    </div>
</div>