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
    <i class="fas fa-tshirt"></i>&nbsp;<span class="tab_name">{l s='Product with combinations without enough stock' mod='ntstats'}</span>
</div>
<div>
    <form>
        <div class="filter_block">
            <label>
                {l s='Product with no more than' mod='ntstats'}
                <input type="text" class="form-control" name="product_nb_combinations_min_without_stock"
                       id="product_nb_combinations_min_without_stock" value="{$config.nb_combinations_min_without_stock|intval}"/>
                {l s='combinations with stock' mod='ntstats'}
            </label>
        </div>
        <div class="form_grid">
            <div class="filter_block">
                <select id="product_with_combinations_without_enough_stock_active" name="product_with_combinations_without_enough_stock_active" class="form-control">
                    <option value="-1">{l s='Active:' mod='ntstats'} ...</option>
                    <option value="1" selected="selected">{l s='Active:' mod='ntstats'} {l s='Yes' mod='ntstats'}</option>
                    <option value="0">{l s='Active:' mod='ntstats'} {l s='No' mod='ntstats'}</option>
                </select>
            </div>
            <div class="filter_block">
                <button type="button" class="btn btn-default filter_btn"
                        id="product_with_combinations_without_enough_stock_valid" name="product_with_combinations_without_enough_stock_valid">
                    <i class="fas fa-check"></i>&nbsp;{l s='Filter' mod='ntstats'}
                </button>
            </div>
            <div class="filter_block">
                <button type="button" class="btn btn-default exp_csv_btn"
                        id="product_with_combinations_without_enough_stock_exp_csv" name="product_with_combinations_without_enough_stock_exp_csv">
                    <i class="fas fa-file-download"></i>&nbsp;{l s='Export CSV' mod='ntstats'}
                </button>
            </div>
            <div class="filter_block">
                <button type="button" class="btn btn-default exp_xls_btn"
                        id="product_with_combinations_without_enough_stock_exp_xls" name="product_with_combinations_without_enough_stock_exp_xls">
                    <i class="fas fa-file-excel"></i>&nbsp;{l s='Export Excel' mod='ntstats'}
                </button>
            </div>
            <input type="hidden" name="type_list" value="product_with_combinations_without_enough_stock"/>
        </div>
    </form>
    <br/>
    <table id="product_with_combinations_without_enough_stock" class="data_table" width="100%" data-sorting="true" data-colreorder="true">
        <thead>
            <tr>
                <th title="{l s='The reference of the product' mod='ntstats'}">
                    {l s='Reference' mod='ntstats'}
                </th>
                <th title="{l s='The name of the product' mod='ntstats'}">
                    {l s='Name' mod='ntstats'}
                </th>
                <th title="{l s='The quantity of the product' mod='ntstats'}">
                    {l s='Qty' mod='ntstats'}
                </th>
                <th title="{l s='The number of combinations with stock for the product' mod='ntstats'}">
                    {l s='Nb combinations with stock' mod='ntstats'}
                </th>
                <th title="{l s='The number of combinations total for the product' mod='ntstats'}">
                    {l s='Nb combinations total' mod='ntstats'}
                </th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>