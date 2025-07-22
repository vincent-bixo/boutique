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
    <i class="fas fa-cogs"></i>&nbsp;{l s='Configuration' mod='ntstats'}
</div>
<div id="ntstats_config">
    <form>
        <input type="hidden" name="config_id_nts_config" id="config_id_nts_config" value="{$config.id_nts_config|intval}"/>
        <div class="panel">
            <div class="panel-heading">
                {l s='Orders' mod='ntstats'}
            </div>
            <div>
                <label for="config_order_date">{l s='Orders dates to use' mod='ntstats'}</label>
                <div>
                    <div class="radio">
                        <label for="config_order_invoice_date">
                            <input type="radio" name="config_order_type_date" id="config_order_invoice_date" value="{$order_type_date_invoice|intval}"
                                {if $config.order_type_date == $order_type_date_invoice}checked="checked"{/if} />
                            {l s='Invoice date' mod='ntstats'}
                        </label>
                    </div>
                    <div class="radio">
                        <label for="config_order_state_date">
                            <input type="radio" name="config_order_type_date" id="config_order_state_date" value="{$order_type_date_state|intval}"
                                {if $config.order_type_date == $order_type_date_state}checked="checked"{/if} />
                            {l s='Status date' mod='ntstats'}
                        </label>
                        <select name="config_order_date_state" id="config_order_date_state">
                            {foreach $list_order_states as $order_state}
                                <option value="{$order_state.id_order_state|intval}" {if $config.order_date_state == $order_state.id_order_state}selected="selected"{/if}>
                                    {$order_state.name|escape:'htmlall':'UTF-8'}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="radio">
                        <label for="config_order_add_date">
                            <input type="radio" name="config_order_type_date" id="config_order_add_date" value="{$order_type_date_add|intval}"
                                {if $config.order_type_date == $order_type_date_add}checked="checked"{/if} />
                            {l s='Creation date' mod='ntstats'}
                        </label>
                    </div>
                </div>
            </div>
            <div>
                <label for="config_order_location">{l s="Orders locations to use" mod='ntstats'}</label>
                <div>
                    <div class="radio">
                        <label for="config_order_invoice_location">
                            <input type="radio" name="config_order_type_location" id="config_order_invoice_location" value="{$order_type_location_invoice|intval}"
                                {if $config.order_type_location == $order_type_location_invoice}checked="checked"{/if} />
                            {l s='Invoice location' mod='ntstats'}
                        </label>
                    </div>
                    <div class="radio">
                        <label for="config_order_delivery_location">
                            <input type="radio" name="config_order_type_location" id="config_order_delivery_location" value="{$order_type_location_delivery|intval}"
                                {if $config.order_type_location == $order_type_location_delivery}checked="checked"{/if} />
                            {l s='Delivery location' mod='ntstats'}
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <br/>
        <div class="panel">
            <div class="panel-heading">
                {l s='Returns' mod='ntstats'}
            </div>
            <div>
                <label for="config_return_statut">{l s='Consider the associated return as validated' mod='ntstats'}</label>
                <div>
                    {foreach $list_order_return_states as $order_return_state}
                        <div class="checkbox">
                            <label for="config_return_{$order_return_state.id_order_return_state|intval}">
                                <input type="checkbox" name="config_return_valid_states[]" id="config_return_{$order_return_state.id_order_return_state|intval}"
                                    value="{$order_return_state.id_order_return_state|intval}"
                                    {if in_array($order_return_state.id_order_return_state, $config.return_valid_states)}checked="checked"{/if} />
                                {$order_return_state.name|escape:'htmlall':'UTF-8'}
                            </label>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
        <br/>
        <div class="panel">
            <div class="panel-heading">
                {l s='Default period' mod='ntstats'}
            </div>
            <p>
                <label for="config_default_period">{l s='Period that should be displayed by default' mod='ntstats'}</label>
                <select class="form-control" name="config_default_period" id="config_default_period">
                    <option {if $config.default_period == $period_last_month}selected="selected"{/if} value="{$period_last_month|intval}">
                        {l s='Last month' mod='ntstats'}
                    </option>
                    <option {if $config.default_period == $period_last_three_months}selected="selected"{/if} value="{$period_last_three_months|intval}">
                        {l s='Last three months' mod='ntstats'}
                    </option>
                    <option {if $config.default_period == $period_last_year}selected="selected"{/if} value="{$period_last_year|intval}">
                        {l s='Last year' mod='ntstats'}
                    </option>
                    <option {if $config.default_period == $period_last_three_years}selected="selected"{/if} value="{$period_last_three_years|intval}">
                        {l s='Last three years' mod='ntstats'}
                    </option>
                    <option {if $config.default_period == $period_all_date}selected="selected"{/if} value="{$period_all_date|intval}">
                        {l s='All dates' mod='ntstats'}
                    </option>
                </select>
            </p>
        </div>
        <br/>
        <div>
            <label for="config_nb_combinations_min_without_stock">{l s='Product with combinations without enough stock: Number min of combinations without stock' mod='ntstats'}</label>
            <input type="text" class="form-control" name="config_nb_combinations_min_without_stock"
                   id="config_nb_combinations_min_without_stock" value="{$config.nb_combinations_min_without_stock|intval}"/>
        </div>
        <div>
            <label for="config_amount_customer_min_one_order">{l s='Customers single order amount: Amount for one order' mod='ntstats'}</label>
            <input type="text" class="form-control" name="config_amount_customer_min_one_order"
                   id="config_amount_customer_min_one_order" value="{$config.amount_customer_min_one_order|intval}"/>
        </div>
        <div>
            <label for="config_amount_customer_min_orders">{l s='Customers orders amount: Minimum amount' mod='ntstats'}</label>
            <input type="text" class="form-control" name="config_amount_customer_min_orders"
                   id="config_amount_customer_min_orders" value="{$config.amount_customer_min_orders|intval}"/>
        </div>
        <div>
            <label for="config_group_product_reference">{l s='Group product reference if it changed' mod='ntstats'}</label>
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" class="form-control" name="config_group_product_reference"
                       id="config_group_product_reference_on" value="1" {if $config.group_product_reference}checked="checked"{/if}
                />
                <label class="t" for="config_group_product_reference_on">{l s='Yes' mod='ntstats'}</label>
                <input type="radio" class="form-control" name="config_group_product_reference"
                       id="config_group_product_reference_off" value="0" {if !$config.group_product_reference}checked="checked"{/if}
                />
                <label class="t" for="config_group_product_reference_off">{l s='No' mod='ntstats'}</label>
                <a class="slide-button btn"></a>
            </span>
        </div>
        <div>
            <label for="config_autoload">{l s='Autoload the stats' mod='ntstats'}</label>
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" class="form-control" name="config_autoload"
                       id="config_autoload_on" value="1" {if $config.autoload}checked="checked"{/if}
                />
                <label class="t" for="config_autoload_on">{l s='Yes' mod='ntstats'}</label>
                <input type="radio" class="form-control" name="config_autoload"
                       id="config_autoload_off" value="0" {if !$config.autoload}checked="checked"{/if}
                />
                <label class="t" for="config_autoload_off">{l s='No' mod='ntstats'}</label>
                <a class="slide-button btn"></a>
            </span>
        </div>
        <br/>
        <div class="panel">
            <div class="panel-heading">
                {l s='Version alert' mod='ntstats'}
            </div>
            <p>
                <label for="config_receive_email_version">{l s='Receive an email when there is a new version' mod='ntstats'}</label>
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" class="form-control" name="config_receive_email_version"
                           id="config_receive_email_version_on" value="1" {if $config.receive_email_version}checked="checked"{/if}
                    />
                    <label class="t" for="config_receive_email_version_on">{l s='Yes' mod='ntstats'}</label>
                    <input type="radio" class="form-control" name="config_receive_email_version"
                           id="config_receive_email_version_off" value="0" {if !$config.receive_email_version}checked="checked"{/if}
                    />
                    <label class="t" for="config_receive_email_version_off">{l s='No' mod='ntstats'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </p>
            <p>
                <label for="config_mail_version">
                    {l s='Emails you want to use to receive message when there is a new version (separated by ";" if more than one)' mod='ntstats'}
                </label>
                <input type="text" name="config_mail_version" id="config_mail_version" class="form-control"
                       value="{$config.mail_version|escape:'html':'UTF-8'}" title="{l s='You will receive your notification on those emails' mod='ntstats'}"
                />
            </p>
        </div>
        <br/>
        <div class="panel">
            <div class="panel-heading">
                {l s='Stock alert' mod='ntstats'}
            </div>
            <p>
                <label for="config_mail_stock_alert">
                    {l s='Email address to receive stock alert message (separated by ";" if more than one)' mod='ntstats'}
                </label>
                <input type="text" name="config_mail_stock_alert" id="config_mail_stock_alert" class="form-control"
                       value="{$config.mail_stock_alert|escape:'html':'UTF-8'}" title="{l s='You will receive your alert on those emails' mod='ntstats'}"
                />
            </p>
            <p>
                <label for="config_email_alert_threshold">
                    {l s='Stock alert default quantity threshold (if product low stock level is not defined)' mod='ntstats'}
                </label>
                <input type="text" name="config_email_alert_threshold" id="config_email_alert_threshold" class="form-control"
                       value="{$config.email_alert_threshold|intval}" title="{l s='Quantity for which a product is considered out of stock and should create an alert' mod='ntstats'}"
                />
            </p>
            <p>
                <label for="config_email_alert_type">
                    {l s='Alert sending format' mod='ntstats'}
                </label>
                <select class="form-control" name="config_email_alert_type" id="config_email_alert_type">
                    <option {if $config.email_alert_type == $email_alert_type_included}selected="selected"{/if} value="{$email_alert_type_included|intval}">{l s='Included in email' mod='ntstats'}</option>
                    <option {if $config.email_alert_type == $email_alert_type_csv}selected="selected"{/if} value="{$email_alert_type_csv|intval}">{l s='CSV file' mod='ntstats'}</option>
                    {if $enable_excel}
                    <option {if $config.email_alert_type == $email_alert_type_excel}selected="selected"{/if} value="{$email_alert_type_excel|intval}">{l s='Excel file' mod='ntstats'}</option>
                    {/if}
                </select>
            </p>
            <p>
                <label for="config_email_alert_active">
                    {l s='Status of the products affected by the alert' mod='ntstats'}
                </label>
                <select class="form-control" name="config_email_alert_active" id="config_email_alert_active">
                    <option {if $config.email_alert_active == $email_alert_active_all}selected="selected"{/if} value="{$email_alert_active_all|intval}">{l s='All' mod='ntstats'}</option>
                    <option {if $config.email_alert_active == $email_alert_active_no}selected="selected"{/if} value="{$email_alert_active_no|intval}">{l s='Not active' mod='ntstats'}</option>
                    {if $enable_excel}
                    <option {if $config.email_alert_active == $email_alert_active_yes}selected="selected"{/if} value="{$email_alert_active_yes|intval}">{l s='Active' mod='ntstats'}</option>
                    {/if}
                </select>
            </p>
            <p>
                <label for="config_email_alert_send_empty">{l s='Receive an email even if there is no stock alert' mod='ntstats'}</label>
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" class="form-control" name="config_email_alert_send_empty"
                           id="config_email_alert_send_empty_on" value="1" {if $config.email_alert_send_empty}checked="checked"{/if}
                    />
                    <label class="t" for="config_email_alert_send_empty_on">{l s='Yes' mod='ntstats'}</label>
                    <input type="radio" class="form-control" name="config_email_alert_send_empty"
                           id="config_email_alert_send_empty_off" value="0" {if !$config.email_alert_send_empty}checked="checked"{/if}
                    />
                    <label class="t" for="config_email_alert_send_empty_off">{l s='No' mod='ntstats'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </p>
        </div>
        <br/>
        <div class="panel">
            <div class="panel-heading">
                {l s='Dashboard' mod='ntstats'}
            </div>
            <p>
                <label for="config_dashboard_sales">{l s='Display last 3 years sales block on dashboard' mod='ntstats'}</label>
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" class="form-control" name="config_dashboard_sales"
                           id="config_dashboard_sales_on" value="1" {if $config.dashboard_sales}checked="checked"{/if}
                    />
                    <label class="t" for="config_dashboard_sales_on">{l s='Yes' mod='ntstats'}</label>
                    <input type="radio" class="form-control" name="config_dashboard_sales"
                           id="config_dashboard_sales_off" value="0" {if !$config.dashboard_sales}checked="checked"{/if}
                    />
                    <label class="t" for="config_dashboard_sales_off">{l s='No' mod='ntstats'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </p>
            <p>
                <label for="config_dashboard_nb_orders">{l s='Display last 3 years orders block on dashboard' mod='ntstats'}</label>
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" class="form-control" name="config_dashboard_nb_orders"
                           id="config_dashboard_nb_orders_on" value="1" {if $config.dashboard_nb_orders}checked="checked"{/if}
                    />
                    <label class="t" for="config_dashboard_nb_orders_on">{l s='Yes' mod='ntstats'}</label>
                    <input type="radio" class="form-control" name="config_dashboard_nb_orders"
                           id="config_dashboard_nb_orders_off" value="0" {if !$config.dashboard_nb_orders}checked="checked"{/if}
                    />
                    <label class="t" for="config_dashboard_nb_orders_off">{l s='No' mod='ntstats'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </p>
        </div>
        <br/>
        <div class="panel payment_method">
            <div class="panel-heading">
                {l s='Text to display for each payments methods' mod='ntstats'}
            </div>
            {foreach $config.payment_method as $key => $pm}
                <p>
                    <label for="config_payment_method_{$key|intval}">{$pm.payment_method|escape:'html':'UTF-8'}</label>
                    <input type="text" class="form-control" name="config_payment_method[{$pm.payment_method|escape:'html':'UTF-8'}]"
                           id="config_payment_method_{$key|intval}" value="{$pm.display_name|escape:'html':'UTF-8'}"/>
                </p>
            {/foreach}
        </div>
        {if $super_admin}
        <div class="panel">
            <div class="panel-heading">
                {l s='Allowed countries per user profile' mod='ntstats'}
            </div>
            {foreach $config.profil_countries as $profil}
                <p>
                    <label for="config_profil_countries_{$profil.id_profile|intval}">{$profil.name|escape:'html':'UTF-8'}</label>
                    <select name="config_profil_countries[{$profil.id_profile|intval}][]"
                            class="select2_field form-control" multiple="multiple" data-placeholder="{l s='All countries' mod='ntstats'}">
                        {foreach $list_all_countries as $country}
                            {assign var=selected value=false}

                            {foreach $profil.id_countries as $id_country}
                                {if $country.id_country == $id_country}
                                    {assign var=selected value=true}
                                {/if}
                            {/foreach}

                            <option value="{$country.id_country|intval}" {if $selected}selected="selected"{/if}>{$country.name|escape:'html':'UTF-8'}</option>
                        {/foreach}
                    </select>
                </p>
            {/foreach}
        </div>
        {/if}
        <div class="panel">
            <p>
                <label>
                    {l s='Attempt to increase server timeout (Currently, your server max execution time is %1$d seconds.)' sprintf=$max_execution_time mod='ntstats'}
                </label>
                <span class="switch prestashop-switch fixed-width-lg increase_server_timeout_block">
                    <input
                        type="radio" name="config_increase_server_timeout" id="config_increase_server_timeout_on" value="1"
                        {if $config.increase_server_timeout}checked="checked"{/if}
                    />
                    <label class="t" for="config_increase_server_timeout_on"> {l s='Yes' mod='ntstats'}</label>
                    <input
                        type="radio" name="config_increase_server_timeout" id="config_increase_server_timeout_off" value="0"
                        {if !$config.increase_server_timeout}checked="checked"{/if}
                    />
                    <label class="t" for="config_increase_server_timeout_off">{l s='No' mod='ntstats'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </p>
            <p>
                <label for="config_server_timeout_value">{l s='New timeout limit.' mod='ntstats'}</label>
                <span>
                    <input type="text" name="config_server_timeout_value" id="config_server_timeout_value" value="{$config.server_timeout_value|intval}"/>
                </span>
            </p>
            <p>
                <label>
                    {l s='Attempt to increase the memory limit. Currently, the memory limit of your server is' mod='ntstats'} {$memory_limit|intval}{l s='MB' mod='ntstats'}
                </label>
                <span class="switch prestashop-switch fixed-width-lg increase_server_memory_block">
                    <input
                        type="radio" name="config_increase_server_memory" id="config_increase_server_memory_on"
                        value="1" {if $config.increase_server_memory}checked="checked"{/if}
                    />
                    <label class="t" for="config_increase_server_memory_on">{l s='Yes' mod='ntstats'}</label>
                    <input
                        type="radio" name="config_increase_server_memory" id="config_increase_server_memory_off"
                        class="config_increase_server_memory_off" value="0" {if !$config.increase_server_memory}checked="checked"{/if}
                    />
                    <label class="t" for="config_increase_server_memory_off">{l s='No' mod='ntstats'}</label>
                    <a class="slide-button btn"></a>
                </span>
            </p>
            <p>
                <label for="config_server_memory_value">{l s='New memory limit.' mod='ntstats'}</label>
                <span>
                    <input type="text" name="config_server_memory_value" id="config_server_memory_value" value="{$config.server_memory_value|intval}"/>
                </span>
            </p>
        </div>
    </form>
</div>
<div class="panel-footer">
    <button type="button" class="btn btn-default pull-right" id="save_config" name="save_config">
        <i class="far fa-save process_icon"></i>&nbsp;{l s='Save' mod='ntstats'}
    </button>
</div>