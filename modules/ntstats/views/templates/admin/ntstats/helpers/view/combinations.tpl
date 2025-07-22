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
    <i class="fas fa-tshirt"></i>&nbsp;<span class="tab_name">{l s='Combinations' mod='ntstats'}</span>
</div>
<div id="ntstats_combinations">
    <form class="form_grid">
        <div class="filter_block">
            <select id="combination_already_sold" name="combination_already_sold" class="form-control">
                <option value="-1">{l s='Already sold:' mod='ntstats'} ...</option>
                <option value="1">{l s='Already sold:' mod='ntstats'} {l s='Yes' mod='ntstats'}</option>
                <option value="0">{l s='Already sold:' mod='ntstats'} {l s='No' mod='ntstats'}</option>
            </select>
        </div>
        <div class="filter_block">
            <select id="combination_with_stock" name="combination_with_stock" class="form-control">
                <option value="-1">{l s='With stock:' mod='ntstats'} ...</option>
                <option value="1">{l s='With stock:' mod='ntstats'} {l s='Yes' mod='ntstats'}</option>
                <option value="0">{l s='With stock:' mod='ntstats'} {l s='No' mod='ntstats'}</option>
            </select>
        </div>
        <div class="filter_block">
            <input type="text" name="combination_min_quantity" id="combination_min_quantity" class="form-control" placeholder="{l s='Quantity min' mod='ntstats'}"/>
        </div>
        <div class="filter_block">
            <input type="text" name="combination_max_quantity" id="combination_max_quantity" class="form-control" placeholder="{l s='Quantity max' mod='ntstats'}"/>
        </div>
        <div class="filter_block">
            <select id="combination_active" name="combination_active" class="form-control">
                <option value="-1">{l s='Active:' mod='ntstats'} ...</option>
                <option value="1" selected="selected">{l s='Active:' mod='ntstats'} {l s='Yes' mod='ntstats'}</option>
                <option value="0">{l s='Active:' mod='ntstats'} {l s='No' mod='ntstats'}</option>
            </select>
        </div>
        <div class="filter_block">
            <select id="combination_id_group" name="combination_id_group[]" class="select2_field form-control"
                    multiple="multiple" data-placeholder="{l s='All groups' mod='ntstats'}">
                {foreach $list_groups as $group}
                    <option value="{$group.id_group|intval}">{$group.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <select id="combination_id_category" name="combination_id_category[]" class="select_id_category form-control select2_field"
                    multiple="multiple" data-placeholder="{l s='All categories' mod='ntstats'}">
                {* Data in ajax to prevent too long loading time of the page*}
            </select>
        </div>
        <div class="filter_block">
            <select id="combination_id_product" name="combination_id_product[]" class="select_id_product form-control select2_field display_products_combinations"
                    multiple="multiple" data-placeholder="{l s='All products' mod='ntstats'}">
                {foreach $list_products_with_combinations as $product}
                    <option value="{$product.id_product|intval}" class="category_id">
                        {$product.reference|escape:'html':'UTF-8'} - {$product.name|escape:'html':'UTF-8'}
                    </option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <select id="combination_id_manufacturer" name="combination_id_manufacturer[]" class="form-control select2_field"
                    multiple="multiple" data-placeholder="{l s='All manufacturers' mod='ntstats'}">
                {foreach $list_manufacturers as $manufacturer}
                    <option value="{$manufacturer.id_manufacturer|intval}">{$manufacturer.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div>
            <div class="filter_block">
                <select id="combination_id_feature" name="combination_id_feature[]"
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
                <select id="combination_id_feature_value" name="combination_id_feature_value[]"
                        class="select2_field form-control select_id_feature_value" multiple="multiple" data-placeholder="{l s='All feature values' mod='ntstats'}">
                </select>
            </div>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default filter_btn" id="combination_valid" name="combination_valid">
                <i class="fas fa-check"></i>&nbsp;{l s='Filter' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_csv_btn" id="combination_exp_csv" name="combination_exp_csv">
                <i class="fas fa-file-download"></i>&nbsp;{l s='Export CSV' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_xls_btn" id="combination_exp_xls" name="combination_exp_xls">
                <i class="fas fa-file-excel"></i>&nbsp;{l s='Export Excel' mod='ntstats'}
            </button>
        </div>
        <input type="hidden" name="type_list" value="combination"/>
        <span class="clear"></span>
    </form>
    <span class="clear"></span>
    <br/>
    <div class="stats_data">
        <table id="combination" class="data_table" width="100%" data-sorting="true" data-colreorder="true">
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
                    <th title="{l s='The current unit purchase price of the combination' mod='ntstats'}">
                        {l s='Unit purchase price tax excl.' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The current unit price of the combination' mod='ntstats'}">
                        {l s='Unit price tax excl.' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The current unit margin of the combination' mod='ntstats'}">
                        {l s='Unit margin tax excl.' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The quantity of the combination' mod='ntstats'}">
                        {l s='Qty' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The stock purchase value (Quantity * Unit purchase price tax excl.)' mod='ntstats'}">
                        {l s='Stock purchase value tax excl.' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The stock value (Quantity * Unit price tax excl.)' mod='ntstats'}">
                        {l s='Stock value tax excl.' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The stock margin (Quantity * Unit margin tax excl.)' mod='ntstats'}">
                        {l s='Stock margin tax excl.' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The quantity sold of  the combination' mod='ntstats'}">
                        {l s='Qty sold' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The quantity of the combination returned' mod='ntstats'}">
                        {l s='Qty returned' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The ean13 of the combination' mod='ntstats'}">
                        {l s='Ean13' mod='ntstats'}
                    </th>
                    <th title="{l s='The product with the combination is active or not' mod='ntstats'}">
                        {l s='Active' mod='ntstats'}
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
                <canvas id="combination_chart" height="560"></canvas>
            </div>
        </div>
    </div>
</div>