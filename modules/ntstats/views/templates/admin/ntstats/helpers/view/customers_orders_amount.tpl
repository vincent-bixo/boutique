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
    <i class="fas fa-user"></i>&nbsp;<span class="tab_name">{l s='Customers orders amount' mod='ntstats'}</span>
</div>
<div>
    <form>
        <div class="filter_block">
            <label>
                {l s='Customer having order more than once for a total amount superior to' mod='ntstats'}
                <input type="text" class="form-control" name="customer_amount_customer_min_orders" id="customer_amount_customer_min_orders" value="{$config.amount_customer_min_orders|intval}"/>
            </label>
        </div>
        <div class="form_grid">
            <div class="filter_block">
                <button type="button" class="btn btn-default filter_btn" id="customer_orders_amount_valid" name="customer_orders_amount_valid">
                    <i class="fas fa-check"></i>&nbsp;{l s='Filter' mod='ntstats'}
                </button>
            </div>
            <div class="filter_block">
                <button type="button" class="btn btn-default exp_csv_btn" id="customer_orders_amount_exp_csv" name="customer_orders_amount_exp_csv">
                    <i class="fas fa-file-download"></i>&nbsp;{l s='Export CSV' mod='ntstats'}
                </button>
            </div>
            <div class="filter_block">
                <button type="button" class="btn btn-default exp_xls_btn" id="customer_orders_amount_exp_xls" name="customer_orders_amount_exp_xls">
                    <i class="fas fa-file-excel"></i>&nbsp;{l s='Export Excel' mod='ntstats'}
                </button>
            </div>
        </div>
        <input type="hidden" name="type_list" value="customer_orders_amount"/>
        <span class="clear"></span>
    </form>
    <span class="clear"></span>
    <br/>
    <div class="stats_data">
        <table id="customer_orders_amount" class="data_table" width="100%" data-sorting="true" data-colreorder="true">
            <thead>
                <tr>
                    <th class="chart_label" title="{l s='The email of the customer' mod='ntstats'}">
                        {l s='Email' mod='ntstats'}
                    </th>
                    <th class="chart_label" title="{l s='The firstname of the customer' mod='ntstats'}">
                        {l s='Firstname' mod='ntstats'}
                    </th>
                    <th class="chart_label" title="{l s='The lastname of the customer' mod='ntstats'}">
                        {l s='Lastname' mod='ntstats'}
                    </th>
                    <th title="{l s='The ID of the customer' mod='ntstats'}">
                        {l s='ID' mod='ntstats'}
                    </th>
                    <th title="{l s='The number of valid orders done by the customer' mod='ntstats'}">
                        {l s='Nb orders' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The total amount paid by the customer' mod='ntstats'}">
                        {l s='Total amount' mod='ntstats'} ({l s='Tax incl.' mod='ntstats'}) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The average amount per order for this customer' mod='ntstats'}">
                        {l s='Average amount per order' mod='ntstats'} ({l s='Tax incl.' mod='ntstats'}) <i class="fas fa-chart-pie"></i>
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
                <canvas id="customer_orders_amount_chart" height="560"></canvas>
            </div>
        </div>
    </div>
</div>