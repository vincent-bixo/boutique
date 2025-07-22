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
    <i class="fas fa-user"></i>&nbsp;<span class="tab_name">{l s='Customers orders details' mod='ntstats'}</span>
</div>
<div>
    <form class="form_grid">
        <button type="button" class="btn btn-default all_dates_btn" id="customers_orders_details_all_dates" name="customers_orders_details_all_dates">
            <i class="fas fa-calendar-alt"></i>&nbsp;{l s='All dates' mod='ntstats'}
        </button>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='From' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_from form-control" name="customers_orders_details_date_from" id="customers_orders_details_date_from"
                       value="{$date_from|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='To' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_to form-control" name="customers_orders_details_date_to" id="customers_orders_details_date_to"
                       value="{$date_to|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="filter_block">
            <select id="customers_orders_details_id_group" name="customers_orders_details_id_group[]" class="form-control select2_field"
                    multiple="multiple" data-placeholder="{l s='All groups' mod='ntstats'}">
                {foreach $list_groups as $group}
                    <option value="{$group.id_group|intval}">{$group.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <select id="customers_orders_details_sort_by" name="customers_orders_details_sort_by" class="form-control" data-placeholder="{l s='Sort by' mod='ntstats'}">
                <option value="customer">{l s='Sort by customer' mod='ntstats'}</option>
                <option value="nb_orders">{l s='Sort by nb valid orders' mod='ntstats'}</option>
                <option value="nb_invalid_orders">{l s='Sort by nb invalid orders' mod='ntstats'}</option>
                <option value="total_paid_tax_excl">{l s='Sort by total tax excl.' mod='ntstats'}</option>
                <option value="total_paid_tax_incl">{l s='Sort by total tax incl.' mod='ntstats'}</option>
                <option value="total_product_quantity">{l s='Sort by nb total products' mod='ntstats'}</option>
            </select>
        </div>
        <div class="filter_block">
            <select id="customers_orders_details_sort_direction" name="customers_orders_details_sort_direction" class="form-control" data-placeholder="{l s='Sort direction' mod='ntstats'}">
                <option value="asc">{l s='Sort ascending' mod='ntstats'}</option>
                <option value="desc">{l s='Sort descending' mod='ntstats'}</option>
            </select>
        </div>
        <div class="filter_block">
            <input type="text" name="customers_orders_details_min_valid_order" id="customers_orders_details_min_valid_order"
                   class="form-control" placeholder="{l s='Nb valid orders min' mod='ntstats'}"/>
        </div>
        <div class="filter_block">
            <input type="text" name="customers_orders_details_max_valid_order" id="customers_orders_details_max_valid_order"
                   class="form-control" placeholder="{l s='Nb valid orders max' mod='ntstats'}"/>
        </div>
        <div class="filter_block">
            <input type="text" name="customers_orders_details_min_total_tax_excl" id="customers_orders_details_min_total_tax_excl"
                   class="form-control" placeholder="{l s='Total tax excl. min' mod='ntstats'}"/>
        </div>
        <div class="filter_block">
            <input type="text" name="customers_orders_details_max_total_tax_excl" id="customers_orders_details_max_total_tax_excl"
                   class="form-control" placeholder="{l s='Total tax excl. max' mod='ntstats'}"/>
        </div>
        <div class="filter_block">
            <input type="text" name="customers_orders_details_min_nb_total_products" id="customers_orders_details_min_nb_total_products"
                   class="form-control" placeholder="{l s='Nb total products min' mod='ntstats'}"/>
        </div>
        <div class="filter_block">
            <input type="text" name="customers_orders_details_max_nb_total_products" id="customers_orders_details_max_nb_total_products"
                   class="form-control" placeholder="{l s='Nb total products max' mod='ntstats'}"/>
        </div>
        <div class="filter_block">
            <input type="text" name="customers_orders_details_min_nb_products" id="customers_orders_details_min_nb_products"
                   class="form-control" placeholder="{l s='Nb products min' mod='ntstats'}"/>
        </div>
        <div class="filter_block">
            <input type="text" name="customers_orders_details_max_nb_products" id="customers_orders_details_max_nb_products"
                   class="form-control" placeholder="{l s='Nb products max' mod='ntstats'}"/>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default filter_btn" id="customers_orders_details_valid" name="customers_orders_details_valid">
                <i class="fas fa-check"></i>&nbsp;{l s='Filter' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_csv_btn" id="customers_orders_details_exp_csv" name="customers_orders_details_exp_csv">
                <i class="fas fa-file-download"></i>&nbsp;{l s='Export CSV' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_xls_btn" id="customers_orders_details_exp_xls" name="customers_orders_details_exp_xls">
                <i class="fas fa-file-excel"></i>&nbsp;{l s='Export Excel' mod='ntstats'}
            </button>
        </div>
        <input type="hidden" name="type_list" value="customers_orders_details"/>
        <span class="clear"></span>
    </form>
    <span class="clear"></span>
    <br/>
    <div class="stats_data">
        <table id="customers_orders_details" class="data_table" width="100%" data-sorting="false" data-colreorder="false">
            <thead>
                <tr>
                    <th title="{l s='The customer' mod='ntstats'}">
                        {l s='Customer' mod='ntstats'}
                    </th>
                    <th title="{l s='The nb of valid orders for this customer' mod='ntstats'}">
                        {l s='Nb valid orders' mod='ntstats'}
                    </th>
                    <th title="{l s='The nb of invalid orders for this customer' mod='ntstats'}">
                        {l s='Nb invalid orders' mod='ntstats'}
                    </th>
                    <th title="{l s='The total amount paid by the customer tax excluded' mod='ntstats'}">
                        {l s='Total tax excl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The total amount paid by the customer tax included' mod='ntstats'}">
                        {l s='Total tax incl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The nb of products for this customer' mod='ntstats'}">
                        {l s='Nb total products' mod='ntstats'}
                    </th>
                    <th title="{l s='The order reference' mod='ntstats'}">
                        {l s='Reference' mod='ntstats'}
                    </th>
                    <th title="{l s='The order amount tax excluded' mod='ntstats'}">
                        {l s='Order tax excl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The order amount tax included' mod='ntstats'}">
                        {l s='Order tax incl.' mod='ntstats'}
                    </th>
                    {if $use_invoice}
                    <th title="{l s='The order invoice date' mod='ntstats'}">
                        {l s='Invoice date' mod='ntstats'}
                    </th>
                    {else}
                        <th title="{l s='The order validity date' mod='ntstats'}">
                        {l s='Validity date' mod='ntstats'}
                    </th>
                    {/if}
                    <th title="{l s='The order payment date' mod='ntstats'}">
                        {l s='Payment date' mod='ntstats'}
                    </th>
                    <th title="{l s='The order payment method' mod='ntstats'}">
                        {l s='Payment method' mod='ntstats'}
                    </th>
                    <th title="{l s='The order state' mod='ntstats'}">
                        {l s='State' mod='ntstats'}
                    </th>
                    <th title="{l s='The nb of products for this order' mod='ntstats'}">
                        {l s='Nb products' mod='ntstats'}
                    </th>
                    <th title="{l s='The product reference' mod='ntstats'}">
                        {l s='Product reference' mod='ntstats'}
                    </th>
                    <th title="{l s='The product name' mod='ntstats'}">
                        {l s='Product name' mod='ntstats'}
                    </th>
                    <th title="{l s='The product quantity' mod='ntstats'}">
                        {l s='Product qty' mod='ntstats'}
                    </th>
                    <th title="{l s='The product price' mod='ntstats'}">
                        {l s='Product price' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'})
                    </th>
                </tr>
            </thead>
            <tbody>

            </tbody>
            <tfoot>

            </tfoot>
        </table>
    </div>
</div>