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
    <i class="fas fa-tag"></i>&nbsp;<span class="tab_name">{l s='Cart rules' mod='ntstats'}</span>
</div>
<div>
    <form class="form_grid">
        <button type="button" class="btn btn-default all_dates_btn" id="cartrules_all_dates" name="cartrules_all_dates">
            <i class="fas fa-calendar-alt"></i>&nbsp;{l s='All dates' mod='ntstats'}
        </button>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='From' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_from form-control" name="cartrules_date_from" id="cartrules_date_from"
                       value="{$date_from|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='To' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_to form-control" name="cartrules_date_to" id="cartrules_date_to"
                       value="{$date_to|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default filter_btn" id="cartrules_valid" name="cartrules_valid">
                <i class="fas fa-check"></i>&nbsp;{l s='Filter' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_csv_btn" id="cartrules_exp_csv" name="cartrules_exp_csv">
                <i class="fas fa-file-download"></i>&nbsp;{l s='Export CSV' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_xls_btn" id="cartrules_exp_xls" name="cartrules_exp_xls">
                <i class="fas fa-file-excel"></i>&nbsp;{l s='Export Excel' mod='ntstats'}
            </button>
        </div>
        <input type="hidden" name="type_list" value="cartrules"/>
        <span class="clear"></span>
    </form>
    <span class="clear"></span>
    <br/>
    <div class="stats_data">
        <table id="cartrules" class="data_table" width="100%" data-sorting="true" data-colreorder="true">
            <thead>
                <tr>
                    <th class="chart_label reverse" title="{l s='The month of sales' mod='ntstats'}">
                        {l s='Month' mod='ntstats'}
                    </th>
                    <th class="chart_label" title="{l s='The cart rule name' mod='ntstats'}">
                        {l s='Name' mod='ntstats'}
                    </th>
                    <th title="{l s='The quantity of this cart rule that been used' mod='ntstats'}">
                        {l s='Qty used' mod='ntstats'} <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The total amount tax excl. of the used cart rule' mod='ntstats'}">
                        {l s='Amount tax excl.' mod='ntstats'} <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The total amount tax incl. of the used cart rule' mod='ntstats'}">
                        {l s='Amount tax incl.' mod='ntstats'} <i class="fas fa-chart-bar"></i>
                    </th>
                    <th title="{l s='The code of the cart rule' mod='ntstats'}">
                        {l s='Code' mod='ntstats'}
                    </th>
                    <th title="{l s='The shipping is free with this cart rule or not' mod='ntstats'}">
                        {l s='Free carrier' mod='ntstats'}
                    </th>
                    <th title="{l s='The total amount tax excl. of orders using this cart rule' mod='ntstats'}">
                        {l s='Total orders tax excl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The total amount tax incl. of orders using this cart rule' mod='ntstats'}">
                        {l s='Total orders tax incl.' mod='ntstats'}
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
                <canvas id="cartrules_chart" height="560"></canvas>
            </div>
        </div>
    </div>
</div>