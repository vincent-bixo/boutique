{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2013-2024 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}
<script type="text/javascript">
    var list_color = getColor(3);
    var datasets_sales = [];
    var labels = [
        "{l s='January' mod='ntstats'}",
        "{l s='February' mod='ntstats'}",
        "{l s='March' mod='ntstats'}",
        "{l s='April' mod='ntstats'}",
        "{l s='May' mod='ntstats'}",
        "{l s='June' mod='ntstats'}",
        "{l s='July' mod='ntstats'}",
        "{l s='August' mod='ntstats'}",
        "{l s='September' mod='ntstats'}",
        "{l s='October' mod='ntstats'}",
        "{l s='November' mod='ntstats'}",
        "{l s='December' mod='ntstats'}"
    ];

    {foreach $list_sales as $year => $year_data}
        var data = [];
        var color = list_color[0];

        {foreach $year_data as $sales}
            data.push({$sales|floatval});
        {/foreach}

        datasets_sales.push({
            label: {$year|intval},
            data: data,
            borderColor: color,
            backgroundColor: color,
        });

        list_color.splice(0, 1);// Remove one item, started at index 0
    {/foreach}

    list_color = getColor(3);
    var datasets_nb_orders = [];

    {foreach $list_nb_orders as $year => $year_data}
        var data = [];
        var color = list_color[0];

        {foreach $year_data as $nb_orders}
            data.push({$nb_orders|intval});
        {/foreach}

        datasets_nb_orders.push({
            label: {$year|intval},
            data: data,
            borderColor: color,
            backgroundColor: color,
        });

        list_color.splice(0, 1);// Remove one item, started at index 0
    {/foreach}
</script>

<div id="ntstats">
    <section id="ntstats_dashboard_sales" class="panel {if !$enable_dashboard_sales}hide{/if}">
        <div class="panel-heading">
            <i class="icon-bar-chart"></i> {l s='Last 3 years sales' mod='ntstats'}
        </div>
        <section>
            <div class="data_chart">
                <div class="canvas_block">
                    <canvas id="ntstats_sales_chart" height="200"></canvas>
                </div>
            </div>
        </section>
    </section>

    <section id="ntstats_dashboard_nb_orders" class="panel {if !$enable_dashboard_nb_orders}hide{/if}">
        <div class="panel-heading">
            <i class="icon-bar-chart"></i> {l s='Last 3 years orders' mod='ntstats'}
        </div>
        <section>
            <div class="data_chart">
                <div class="canvas_block">
                    <canvas id="ntstats_nb_orders_chart" height="200"></canvas>
                </div>
            </div>
        </section>
    </section>
</div>
