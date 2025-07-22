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
    <i class="fas fa-tshirt"></i>&nbsp;<span class="tab_name">{l s='Products' mod='ntstats'}</span>
</div>
<div id="ntstats_products">
    <form class="form_grid">
        <div class="filter_block">
            <select id="product_already_sold" name="product_already_sold" class="form-control">
                <option value="-1">{l s='Already sold:' mod='ntstats'} ...</option>
                <option value="1">{l s='Already sold:' mod='ntstats'} {l s='Yes' mod='ntstats'}</option>
                <option value="0">{l s='Already sold:' mod='ntstats'} {l s='No' mod='ntstats'}</option>
            </select>
        </div>
        <div class="filter_block">
            <select id="product_with_stock" name="product_with_stock" class="form-control">
                <option value="-1">{l s='With stock:' mod='ntstats'} ...</option>
                <option value="1">{l s='With stock:' mod='ntstats'} {l s='Yes' mod='ntstats'}</option>
                <option value="0">{l s='With stock:' mod='ntstats'} {l s='No' mod='ntstats'}</option>
            </select>
        </div>
        <div class="filter_block">
            <select id="product_with_stock_mvt" name="product_with_stock_mvt" class="form-control">
                <option value="-1">{l s='With stock movement:' mod='ntstats'} ...</option>
                <option value="1">{l s='With stock movement:' mod='ntstats'} {l s='Yes' mod='ntstats'}</option>
                <option value="0">{l s='With stock movement:' mod='ntstats'} {l s='No' mod='ntstats'}</option>
            </select>
        </div>
        <div class="filter_block">
            <select id="product_with_combination" name="product_with_combination" class="form-control">
                <option value="-1">{l s='With combinations:' mod='ntstats'} ...</option>
                <option value="1">{l s='With combinations:' mod='ntstats'} {l s='Yes' mod='ntstats'}</option>
                <option value="0">{l s='With combinations:' mod='ntstats'} {l s='No' mod='ntstats'}</option>
            </select>
        </div>
        <div class="filter_block">
            <select id="product_with_out_stock_combination" name="product_with_out_stock_combination" class="form-control">
                <option value="-1">{l s='With combinations out of stock:' mod='ntstats'} ...</option>
                <option value="1">{l s='With combinations out of stock:' mod='ntstats'} {l s='Yes' mod='ntstats'}</option>
                <option value="0">{l s='With combinations out of stock:' mod='ntstats'} {l s='No' mod='ntstats'}</option>
            </select>
        </div>
        <div class="filter_block">
            <select id="product_with_image" name="product_with_image" class="form-control">
                <option value="-1">{l s='With image:' mod='ntstats'} ...</option>
                <option value="1">{l s='With image:' mod='ntstats'} {l s='Yes' mod='ntstats'}</option>
                <option value="0">{l s='With image:' mod='ntstats'} {l s='No' mod='ntstats'}</option>
            </select>
        </div>
        <div class="filter_block">
            <select id="product_with_cover_image" name="product_with_cover_image" class="form-control">
                <option value="-1">{l s='With cover image:' mod='ntstats'} ...</option>
                <option value="1">{l s='With cover image:' mod='ntstats'} {l s='Yes' mod='ntstats'}</option>
                <option value="0">{l s='With cover image:' mod='ntstats'} {l s='No' mod='ntstats'}</option>
            </select>
        </div>
        <div class="filter_block">
            <select id="product_active" name="product_active" class="form-control">
                <option value="-1">{l s='Active:' mod='ntstats'} ...</option>
                <option value="1" selected="selected">{l s='Active:' mod='ntstats'} {l s='Yes' mod='ntstats'}</option>
                <option value="0">{l s='Active:' mod='ntstats'} {l s='No' mod='ntstats'}</option>
            </select>
        </div>
        <div class="filter_block">
            <select id="product_with_ean13" name="product_with_ean13" class="form-control">
                <option value="-1">{l s='With ean13:' mod='ntstats'} ...</option>
                <option value="1">{l s='With ean13:' mod='ntstats'} {l s='Yes' mod='ntstats'}</option>
                <option value="0">{l s='With ean13:' mod='ntstats'} {l s='No' mod='ntstats'}</option>
            </select>
        </div>
        <div class="filter_block">
            <select id="product_id_group" name="product_id_group[]" class="select2_field form-control" multiple="multiple" data-placeholder="{l s='All groups' mod='ntstats'}">
                {foreach $list_groups as $group}
                    <option value="{$group.id_group|intval}">{$group.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <select id="product_id_category" name="product_id_category[]" class="select2_field form-control" multiple="multiple" data-placeholder="{l s='All categories' mod='ntstats'}">
                {* Data in ajax to prevent too long loading time of the page*}
            </select>
        </div>
        <div class="filter_block">
            <select id="product_id_manufacturer" name="product_id_manufacturer[]" class="select2_field form-control"
                    multiple="multiple" data-placeholder="{l s='All manufacturers' mod='ntstats'}">
                {foreach $list_manufacturers as $manufacturer}
                    <option value="{$manufacturer.id_manufacturer|intval}">{$manufacturer.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div>
            <div class="filter_block">
                <select id="product_id_feature" name="product_id_feature[]"
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
                <select id="product_id_feature_value" name="product_id_feature_value[]"
                        class="select2_field form-control select_id_feature_value" multiple="multiple" data-placeholder="{l s='All feature values' mod='ntstats'}">
                </select>
            </div>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default filter_btn" id="product_valid" name="product_valid">
                <i class="fas fa-check"></i>&nbsp;{l s='Filter' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_csv_btn" id="product_exp_csv" name="product_exp_csv">
                <i class="fas fa-file-download"></i>&nbsp;{l s='Export CSV' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_xls_btn" id="product_exp_xls" name="product_exp_xls">
                <i class="fas fa-file-excel"></i>&nbsp;{l s='Export Excel' mod='ntstats'}
            </button>
        </div>
        <input type="hidden" name="type_list" value="product"/>
        <span class="clear"></span>
    </form>
    <span class="clear"></span>
    <br/>
    <div class="stats_data">
        <table id="product" class="data_table" width="100%" data-sorting="true" data-colreorder="true">
            <thead>
                <tr>
                    <th class="chart_label" title="{l s='The ID of the product' mod='ntstats'}">
                        {l s='ID' mod='ntstats'}
                    </th>
                    <th class="chart_label" title="{l s='The reference of the product' mod='ntstats'}">
                        {l s='Reference' mod='ntstats'}
                    </th>
                    <th class="chart_label" title="{l s='The name of the product' mod='ntstats'}">
                        {l s='Name' mod='ntstats'}
                    </th>
                    <th title="{l s='The number of combinations out of stock for the product' mod='ntstats'}">
                        {l s='Combinations out of stock' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The number of combinations total for the product' mod='ntstats'}">
                        {l s='Total combinations' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The current unit purchase price of the product' mod='ntstats'}">
                        {l s='Unit purchase price tax excl.' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The current unit price of the product' mod='ntstats'}">
                        {l s='Unit price tax excl.' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The current unit margin of the product' mod='ntstats'}">
                        {l s='Unit margin tax excl.' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The quantity of the product' mod='ntstats'}">
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
                    <th title="{l s='The quantity sold of the product' mod='ntstats'}">
                        {l s='Qty sold' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The quantity of the product returned' mod='ntstats'}">
                        {l s='Qty returned' mod='ntstats'} <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The amount of the product refunded' mod='ntstats'}">
                        {l s='Total refunded' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'}) <i class="fas fa-chart-pie"></i>
                    </th>
                    <th title="{l s='The ean13 of the product' mod='ntstats'}">
                        {l s='Ean13' mod='ntstats'}
                    </th>
                    <th title="{l s='The product is active or not' mod='ntstats'}">
                        {l s='Active' mod='ntstats'}
                    </th>
                    <th title="{l s='Number of abandonned cart with this product' mod='ntstats'}">
                        {l s='Abandoned cart' mod='ntstats'}
                    </th>
                    <th title="{l s='Date of creation of this product' mod='ntstats'}">
                        {l s='Creation date' mod='ntstats'}
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
                <canvas id="product_chart" height="560"></canvas>
            </div>
        </div>
    </div>
</div>