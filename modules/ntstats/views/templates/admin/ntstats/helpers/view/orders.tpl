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
    <i class="fas fa-shopping-cart"></i>&nbsp;<span class="tab_name">{l s='Orders' mod='ntstats'}</span>
</div>
<div>
    <form class="form_grid">
        <button type="button" class="btn btn-default all_dates_btn" id="orders_all_dates" name="orders_all_dates">
            <i class="fas fa-calendar-alt"></i>&nbsp;{l s='All dates' mod='ntstats'}
        </button>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='From' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_from form-control" name="orders_date_from" id="orders_date_from"
                       value="{$date_from|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="choose_date">
            <div class="input-group">
                <span class="input-group-addon">{l s='To' mod='ntstats'}</span>
                <input type="text" class="datepicker input-medium date_to form-control" name="orders_date_to" id="orders_date_to"
                       value="{$date_to|escape:'html':'UTF-8'}" />
                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
            </div>
        </div>
        <div class="filter_block">
            <select id="orders_id_group" name="orders_id_group[]" class="form-control select2_field"
                    multiple="multiple" data-placeholder="{l s='All groups' mod='ntstats'}">
                {foreach $list_groups as $group}
                    <option value="{$group.id_group|intval}">{$group.name|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <select id="orders_payment_method" name="orders_payment_method[]" class="form-control select2_field"
                    multiple="multiple" data-placeholder="{l s='All payment methods' mod='ntstats'}">
                {foreach $list_payments as $payment}
                    <option value="{$payment.payment_method|escape:'html':'UTF-8'}">{$payment.payment_method|escape:'html':'UTF-8'}</option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <select id="orders_id_category" name="orders_id_category[]" class="select_id_category form-control select2_field"
                    multiple="multiple" data-placeholder="{l s='All categories' mod='ntstats'}">
                <option value="0">{l s='All categories' mod='ntstats'}</option>
                {* Data in ajax to prevent too long loading time of the page*}
            </select>
        </div>
        <div class="filter_block">
            <select id="orders_id_product" name="orders_id_product[]" class="select_id_product form-control select2_field display_products_ordered"
                    multiple="multiple" data-placeholder="{l s='All products' mod='ntstats'}">
                {foreach $list_order_products as $product}
                    <option value="{$product.id_product|intval}" class="category_id">
                        {$product.reference|escape:'html':'UTF-8'} - {$product.name|escape:'html':'UTF-8'}
                    </option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <select id="orders_id_cart_rule" name="orders_id_cart_rule[]" class="form-control select2_field"
                    multiple="multiple" data-placeholder="{l s='All cart rules' mod='ntstats'}">
                {foreach $list_order_cart_rules as $cart_rule}
                    <option value="{$cart_rule.id_cart_rule|intval}">
                        {$cart_rule.name|escape:'html':'UTF-8'}{if $cart_rule.code} - {$cart_rule.code|escape:'html':'UTF-8'} {/if}
                    </option>
                {/foreach}
            </select>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default filter_btn" id="orders_valid" name="orders_valid">
                <i class="fas fa-check"></i>&nbsp;{l s='Filter' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_csv_btn" id="orders_exp_csv" name="orders_exp_csv">
                <i class="fas fa-file-download"></i>&nbsp;{l s='Export CSV' mod='ntstats'}
            </button>
        </div>
        <div class="filter_block">
            <button type="button" class="btn btn-default exp_xls_btn" id="orders_exp_xls" name="orders_exp_xls">
                <i class="fas fa-file-excel"></i>&nbsp;{l s='Export Excel' mod='ntstats'}
            </button>
        </div>
        <input type="hidden" name="type_list" value="orders"/>
        <span class="clear"></span>
    </form>
    <span class="clear"></span>
    <br/>
    <div class="stats_data">
        <table id="orders" class="data_table" width="100%" data-sorting="true" data-colreorder="true">
            <thead>
                <tr>
                    <th title="{l s='The order ID' mod='ntstats'}">
                        {l s='ID' mod='ntstats'}
                    </th>
                    <th title="{l s='The order reference' mod='ntstats'}">
                        {l s='Reference' mod='ntstats'}
                    </th>
                    <th title="{l s='The order total amount tax excluded' mod='ntstats'}">
                        {l s='Total tax excl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The order total amount tax included' mod='ntstats'}">
                        {l s='Total tax incl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The order total vat' mod='ntstats'}">
                        {l s='Total VAT' mod='ntstats'}
                    </th>
                    <th title="{l s='The order products amount tax excluded' mod='ntstats'}">
                        {l s='Products tax excl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The order products amount tax included' mod='ntstats'}">
                        {l s='Products tax incl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The order products vat' mod='ntstats'}">
                        {l s='Products VAT' mod='ntstats'}
                    </th>
                    <th title="{l s='The order discount amount tax excluded' mod='ntstats'}">
                        {l s='Discount tax excl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The order discount amount tax included' mod='ntstats'}">
                        {l s='Discount tax incl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The order discount vat' mod='ntstats'}">
                        {l s='Discount VAT' mod='ntstats'}
                    </th>
                    <th title="{l s='The order shipping amount tax excluded' mod='ntstats'}">
                        {l s='Shipping tax excl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The order shipping amount tax included' mod='ntstats'}">
                        {l s='Shipping tax incl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The order shipping vat' mod='ntstats'}">
                        {l s='Shipping VAT' mod='ntstats'}
                    </th>
                    <th title="{l s='The order wrapping amount tax excluded' mod='ntstats'}">
                        {l s='Wrapping tax excl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The order wrapping amount tax included' mod='ntstats'}">
                        {l s='Wrapping tax incl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The order wrapping vat' mod='ntstats'}">
                        {l s='Wrapping VAT' mod='ntstats'}
                    </th>
                    <th title="{l s='The order ecotax amount tax excluded' mod='ntstats'}">
                        {l s='Ecotax tax excl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The order ecotax amount tax included' mod='ntstats'}">
                        {l s='Ecotax tax incl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The order ecotax vat' mod='ntstats'}">
                        {l s='Ecotax VAT' mod='ntstats'}
                    </th>
                    <th title="{l s='The order total cost' mod='ntstats'}">
                        {l s='Cost' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'})
                    </th>
                    <th title="{l s='The order gross profit (Products tax excl. - Cost)' mod='ntstats'}">
                        {l s='Gross profit before discounts' mod='ntstats'} ({l s='Tax excl.' mod='ntstats'})
                    </th>
                    <th title="{l s='The order total profit (Products tax excl. - Discount tax excl. - Cost)' mod='ntstats'}">
                        {l s='Net profit tax excl.' mod='ntstats'}
                    </th>
                    <th title="{l s='The order gross margin (Gross profit before discounts / Products tax excl. x 100)' mod='ntstats'}">
                        {l s='Gross margin before discounts' mod='ntstats'} %
                    </th>
                    <th title="{l s='The order net margin ((Net profit tax excl. + Wrapping tax excl.) / (Products tax excl. - Discount tax excl. + Wrapping tax excl.) x 100)' mod='ntstats'}">
                        {l s='Net margin tax excl.' mod='ntstats'} %
                    </th>
                    <th title="{l s='The order cart rules names list' mod='ntstats'}">
                        {l s='Cart rules names' mod='ntstats'}
                    </th>
                    <th title="{l s='The shipping is free with those cart rules or not' mod='ntstats'}">
                        {l s='Free carrier' mod='ntstats'}
                    </th>
                    <th title="{l s='The invoice number' mod='ntstats'}">
                        {l s='Invoice number' mod='ntstats'}
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
                    <th title="{l s='The order customer' mod='ntstats'}">
                        {l s='Customer' mod='ntstats'}
                    </th>
                    {if $config.order_type_location == $order_type_location_delivery}
                        <th title="{l s='The delivery address postcode' mod='ntstats'}">
                    {else}
                        <th title="{l s='The invoice address postcode' mod='ntstats'}">
                    {/if}
                        {l s='Postcode' mod='ntstats'}
                    </th>
                    {if $config.order_type_location == $order_type_location_delivery}
                        <th title="{l s='The delivery address city' mod='ntstats'}">
                    {else}
                        <th title="{l s='The invoice address city' mod='ntstats'}">
                    {/if}
                        {l s='City' mod='ntstats'}
                    </th>
                    {if $config.order_type_location == $order_type_location_delivery}
                        <th title="{l s='The delivery address country' mod='ntstats'}">
                    {else}
                        <th title="{l s='The invoice address country' mod='ntstats'}">
                    {/if}
                        {l s='Country' mod='ntstats'}
                    </th>
                    <th title="{l s='The order state' mod='ntstats'}">
                        {l s='State' mod='ntstats'}
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
                <canvas id="orders_chart" height="560"></canvas>
            </div>
        </div>
    </div>
</div>