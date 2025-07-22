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
    <i class="fas fa-tshirt"></i>&nbsp;<span class="tab_name">{l s='Unsold combinations with stock' mod='ntstats'}</span>
</div>
<div>
    <form class="form_grid">
        <div class="filter_block">
            <button type="button" class="btn btn-default filter_btn" id="combination_unsold_with_stock_valid" name="combination_unsold_with_stock_valid">
                <i class="fas fa-sync-alt"></i>&nbsp;{l s='Refresh' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_csv_btn" id="combination_unsold_with_stock_exp_csv" name="combination_unsold_with_stock_exp_csv">
                <i class="fas fa-file-download"></i>&nbsp;{l s='Export CSV' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_xls_btn" id="combination_unsold_with_stock_exp_xls" name="combination_unsold_with_stock_exp_xls">
                <i class="fas fa-file-excel"></i>&nbsp;{l s='Export Excel' mod='ntstats'}
            </button>
        </div>
        <input type="hidden" name="type_list" value="combination_unsold_with_stock"/>
        <span class="clear"></span>
    </form>
    <span class="clear"></span>
    <br/>
    <div class="stats_data">
        <table id="combination_unsold_with_stock" class="data_table" width="100%" data-sorting="true" data-colreorder="true">
            <thead>
                <tr>
                    <th class="chart_label" title="{l s='The reference of the combination' mod='ntstats'}">
                        {l s='Reference' mod='ntstats'}
                    </th>
                    <th class="chart_label" title="{l s='The name of the product with the combination' mod='ntstats'}">
                        {l s='Name' mod='ntstats'}
                    </th>
                    <th class="chart_label" title="{l s='The detail of the combination' mod='ntstats'}">
                        {l s='Combination' mod='ntstats'}
                    </th>
                    <th title="{l s='The quantity of the combination' mod='ntstats'}">
                        {l s='Qty' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The ean13 of the combination' mod='ntstats'}">
                        {l s='Ean13' mod='ntstats'}
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
                <canvas id="combination_unsold_with_stock_chart" height="560"></canvas>
            </div>
        </div>
    </div>
</div>