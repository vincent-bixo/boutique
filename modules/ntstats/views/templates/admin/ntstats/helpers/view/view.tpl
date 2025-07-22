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
<script type="text/javascript">
    var admin_link_nts          = "{$link->getAdminLink('AdminNtstats')|escape:'javascript':'UTF-8'}";
	var ajax_loader             = "{$ajax_loader|escape:'javascript':'UTF-8'}";
	var today                   = "{$today|escape:'html':'UTF-8'}";
    var activate_2nt_automation = {$activate_2nt_automation|intval};
    var enable_excel            = {$enable_excel|intval};
    var autoload                = {$config.autoload|intval};
    var save_config_success     = "{l s='The configuration was saved successfully' mod='ntstats'}";
    var save_config_error       = "{l s='An error occured while saving the configuration' mod='ntstats'}";
    var save_automation_success = "{l s='The automation was saved successfully' mod='ntstats'}";
    var save_automation_error   = "{l s='An error occured while saving the automation' mod='ntstats'}";
    var version_php_excel_msg   = "{l s='The Excel export is not available for PHP version under 7.1' mod='ntstats'}";
    var shop_name               = "{$shop_name|escape:'html':'UTF-8'}";

    var DATATABLE_LANG = {
        "decimal":        '',
        "emptyTable":     "{l s='No data available in table' mod='ntstats'}",
        "info":           'Showing _START_ to _END_ of _TOTAL_ entries',
        "infoEmpty":      'Showing 0 to 0 of 0 entries',
        "infoFiltered":   '(filtered from _MAX_ total entries)',
        "infoPostFix":    '',
        "thousands":      '.',
        "lengthMenu":     'Show _MENU_ entries',
        "loadingRecords": "{l s='Loading...' mod='ntstats'}",
        "processing":     "{l s='Processing...' mod='ntstats'}",
        "search":         "{l s='Search:' mod='ntstats'}",
        "zeroRecords":    "{l s='No matching records found' mod='ntstats'}",
        "paginate": {
            "first":      "{l s='First' mod='ntstats'}",
            "last":       "{l s='Last' mod='ntstats'}",
            "next":       "{l s='Next' mod='ntstats'}",
            "previous":   "{l s='Previous' mod='ntstats'}"
        },
        "aria": {
            "sortAscending":  ': activate to sort column ascending',
            "sortDescending": ': activate to sort column descending'
        }
    };
</script>

{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}

<div id="ntstats">
	<div class="sidebar navigation">
		<nav id="nt_tab" class="list-group">
            {* Sales *}
			<a id="nt_tab0" class="list-group-item {if !$nttab || $nttab == "nt_tab0"}active{/if}"><i class="fas fa-shopping-cart"></i>&nbsp;{l s='Total sales' mod='ntstats'}</a>
			<a id="nt_tab1" class="list-group-item {if $nttab == "nt_tab1"}active{/if}"><i class="fas fa-shopping-cart"></i>&nbsp;{l s='Total categories sales' mod='ntstats'}</a>
			<a id="nt_tab2" class="list-group-item {if $nttab == "nt_tab2"}active{/if}"><i class="fas fa-shopping-cart"></i>&nbsp;{l s='Total products sales' mod='ntstats'}</a>
			<a id="nt_tab3" class="list-group-item {if $nttab == "nt_tab3"}active{/if}"><i class="fas fa-shopping-cart"></i>&nbsp;{l s='Total combinations sales' mod='ntstats'}</a>
			<a id="nt_tab4" class="list-group-item {if $nttab == "nt_tab4"}active{/if}"><i class="fas fa-shopping-cart"></i>&nbsp;{l s='Total countries sales' mod='ntstats'}</a>
			<a id="nt_tab5" class="list-group-item {if $nttab == "nt_tab5"}active{/if}"><i class="fas fa-shopping-cart"></i>&nbsp;{l s='Total manufacturers sales' mod='ntstats'}</a>
			<a id="nt_tab6" class="list-group-item {if $nttab == "nt_tab6"}active{/if}"><i class="fas fa-shopping-cart"></i>&nbsp;{l s='Total payment methods sales' mod='ntstats'}</a>
            {* Compare *}
			<a id="nt_tab7" class="list-group-item {if $nttab == "nt_tab7"}active{/if}"><i class="fas fa-balance-scale-right"></i>&nbsp;{l s='Compare total sales' mod='ntstats'}</a>
			<a id="nt_tab8" class="list-group-item {if $nttab == "nt_tab8"}active{/if}"><i class="fas fa-balance-scale-right"></i>&nbsp;{l s='Compare total categories sales' mod='ntstats'}</a>
			<a id="nt_tab9" class="list-group-item {if $nttab == "nt_tab9"}active{/if}"><i class="fas fa-balance-scale-right"></i>&nbsp;{l s='Compare total products sales' mod='ntstats'}</a>
			<a id="nt_tab10" class="list-group-item {if $nttab == "nt_tab10"}active{/if}"><i class="fas fa-balance-scale-right"></i>&nbsp;{l s='Compare total combinations sales' mod='ntstats'}</a>
			<a id="nt_tab11" class="list-group-item {if $nttab == "nt_tab11"}active{/if}"><i class="fas fa-balance-scale-right"></i>&nbsp;{l s='Compare total countries sales' mod='ntstats'}</a>
			<a id="nt_tab12" class="list-group-item {if $nttab == "nt_tab12"}active{/if}"><i class="fas fa-balance-scale-right"></i>&nbsp;{l s='Compare total manufacturers sales' mod='ntstats'}</a>
			<a id="nt_tab13" class="list-group-item {if $nttab == "nt_tab13"}active{/if}"><i class="fas fa-balance-scale-right"></i>&nbsp;{l s='Compare total payment methods sales' mod='ntstats'}</a>
            {* Products *}
			<a id="nt_tab14" class="list-group-item {if $nttab == "nt_tab14"}active{/if}"><i class="fas fa-tshirt"></i>&nbsp;{l s='Products' mod='ntstats'}</a>
            <a id="nt_tab15" class="list-group-item {if $nttab == "nt_tab15"}active{/if}"><i class="fas fa-tshirt"></i>&nbsp;{l s='Products with combinations out of stock' mod='ntstats'}</a>
			<a id="nt_tab16" class="list-group-item {if $nttab == "nt_tab16"}active{/if}"><i class="fas fa-tshirt"></i>&nbsp;{l s='Product with combinations without enough stock' mod='ntstats'}</a>
            {* Combinations *}
			<a id="nt_tab17" class="list-group-item {if $nttab == "nt_tab17"}active{/if}"><i class="fas fa-tshirt"></i>&nbsp;{l s='Combinations' mod='ntstats'}</a>
			<a id="nt_tab18" class="list-group-item {if $nttab == "nt_tab18"}active{/if}"><i class="fas fa-tshirt"></i>&nbsp;{l s='Unsold combinations with stock' mod='ntstats'}</a>
            {* Categories *}
			<a id="nt_tab19" class="list-group-item {if $nttab == "nt_tab19"}active{/if}"><i class="fas fa-tshirt"></i>&nbsp;{l s='Categories' mod='ntstats'}</a>
            {* Orders *}
			<a id="nt_tab20" class="list-group-item {if $nttab == "nt_tab20"}active{/if}"><i class="fas fa-tshirt"></i>&nbsp;{l s='Orders' mod='ntstats'}</a>
            {* Statuses *}
			<a id="nt_tab21" class="list-group-item {if $nttab == "nt_tab21"}active{/if}"><i class="fas fa-tshirt"></i>&nbsp;{l s='Duration statuses' mod='ntstats'}</a>
            {* Carriers *}
			<a id="nt_tab22" class="list-group-item {if $nttab == "nt_tab22"}active{/if}"><i class="fas fa-truck"></i>&nbsp;{l s='Carriers' mod='ntstats'}</a>
            {* Manufacturers *}
			<a id="nt_tab23" class="list-group-item {if $nttab == "nt_tab23"}active{/if}"><i class="fas fa-user-tie"></i>&nbsp;{l s='Manufacturers' mod='ntstats'}</a>
            {* Customers *}
			<a id="nt_tab24" class="list-group-item {if $nttab == "nt_tab24"}active{/if}"><i class="fas fa-user"></i>&nbsp;{l s='Customers' mod='ntstats'}</a>
			<a id="nt_tab25" class="list-group-item {if $nttab == "nt_tab25"}active{/if}"><i class="fas fa-user"></i>&nbsp;{l s='Customers single order amount' mod='ntstats'}</a>
			<a id="nt_tab26" class="list-group-item {if $nttab == "nt_tab26"}active{/if}"><i class="fas fa-user"></i>&nbsp;{l s='Customers orders amount' mod='ntstats'}</a>
			<a id="nt_tab27" class="list-group-item {if $nttab == "nt_tab27"}active{/if}"><i class="fas fa-user"></i>&nbsp;{l s='Customers products' mod='ntstats'}</a>
			<a id="nt_tab28" class="list-group-item {if $nttab == "nt_tab28"}active{/if}"><i class="fas fa-user"></i>&nbsp;{l s='Customers orders details' mod='ntstats'}</a>
			<a id="nt_tab29" class="list-group-item {if $nttab == "nt_tab29"}active{/if}"><i class="fas fa-user"></i>&nbsp;{l s='Customers products details' mod='ntstats'}</a>
            {* Cart rules *}
			<a id="nt_tab30" class="list-group-item {if $nttab == "nt_tab30"}active{/if}"><i class="fas fa-tag"></i>&nbsp;{l s='Cart rules' mod='ntstats'}</a>
            {* Configuration *}
			<a id="nt_tab31" class="list-group-item {if $nttab == "nt_tab31"}active{/if}"><i class="fas fa-cogs"></i>&nbsp;{l s='Configuration' mod='ntstats'}</a>
			<a id="nt_tab32" class="list-group-item {if $nttab == "nt_tab32"}active{/if}"><i class="far fa-clock"></i>&nbsp;{l s='Automation' mod='ntstats'}</a>

			<a id="nt_tab33" class="list-group-item {if $nttab == "nt_tab33"}active{/if}"><i class="fas fa-question-circle"></i>&nbsp;{l s='FAQ' mod='ntstats'}</a>
			<a id="nt_tab34" class="list-group-item {if $nttab == "nt_tab34"}active{/if}"><i class="fas fa-book"></i>&nbsp;{l s='Documentation' mod='ntstats'}</a>
			<a id="nt_tab35" class="list-group-item {if $nttab == "nt_tab35"}active{/if}"><i class="fas fa-envelope"></i>&nbsp;{l s='Contact' mod='ntstats'}</a>
			<a id="nt_tab36" class="list-group-item {if $nttab == "nt_tab36"}active{/if}"><i class="fas fa-store"></i>&nbsp;{l s='Our modules' mod='ntstats'}</a>
            {*{if $display_translate_tab}*}
			<a id="nt_tab37" class="list-group-item {if $nttab == "nt_tab37"}active{/if}"><i class="fas fa-globe-americas"></i>&nbsp;Help us translate into your language</a>
            {*{/if}*}
		</nav>
		<nav class="list-group">
            <a id="nt_request" class="list-group-item" href="{$link_contact|escape:'html':'UTF-8'}" target="_blank">
                <i class="far fa-lightbulb"></i>&nbsp;{l s='Request feature' mod='ntstats'}
            </a>
            <a href="{$changelog|escape:'html':'UTF-8'}" target="_blank" id="nt_version" class="list-group-item">
                <i class="fas fa-info"></i>&nbsp;{l s='Version' mod='ntstats'} {$version|escape:'html':'UTF-8'}
                {if $available_version > $version}({$available_version|escape:'html':'UTF-8'} {l s='avail' mod='ntstats'}){/if}
            </a>
            <div class="list-group-item info_nt_module">
                <p>
                    <a target="_blank" href="https://addons.prestashop.com/fr/2_community-developer?contributor=311046">
                        <span>{l s='Proudly developed by' mod='ntstats'}</span><br/>
                        <img class="nt_logo" src="../modules/ntstats/views/img/logo_module.png" alt="2N Technologies"/>
                    </a>
                </p>
                <p>
                    <a target="_blank" href="https://addons.prestashop.com/fr/2_community-developer?contributor=311046">
                        <span>{l s='We are a Prestashop Superhero seller' mod='ntstats'}</span><br/><br/>
                        <img class="rank_logo" src="../modules/ntstats/views/img/super_hero.png" alt="2N Technologies"/>
                    </a>
                </p>
                <p class="module_rating">
                    <a href="{$link_rate|escape:'html':'UTF-8'}" target="_blank">
                        <span>{l s='Rate and comment this module' mod='ntstats'}</span><br/>
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </a>
                </p>
                <p>
                    <a target="_blank" href="{$link_contact|escape:'html':'UTF-8'}">{l s='Contact us' mod='ntstats'}</a>
                </p>
                <p>
                    <a href="{$documentation|escape:'html':'UTF-8'}" target="_blank" title="{l s='Documentation' mod='ntstats'}"><i class="fas fa-book"></i></a>
                    <a href="{$link_contact|escape:'html':'UTF-8'}" target="_blank" title="{l s='Support' mod='ntstats'}"><i class="far fa-life-ring"></i></a>
                    <a href="{$link_contact|escape:'html':'UTF-8'}" target="_blank" title="{l s='Translate' mod='ntstats'}"><i class="fas fa-globe-americas"></i></a>
                    <a href="{$link_contact|escape:'html':'UTF-8'}" target="_blank" title="{l s='Ask a feature' mod='ntstats'}"><i class="far fa-lightbulb"></i></a>
                </p>
            </div>
		</nav>
	</div>
    <div class="stats_content">
        {if $display_new_version_msg}
            <div class="alert alert-warning warn">
                <p>
                    {l s='This new version brings new options in the Configuration tab, in particular for the detection of validated returns. We advise you to take a look there to configure the module according to your preferences.' mod='ntstats'}
                </p>
            </div>
        {/if}
        <div id="nts_result">
            <div class="error alert alert-danger"></div>
            <div class="confirm alert alert-success"></div>
        </div>
        {* Sales *}
		<div id="nt_tab0_content" class="panel tab">{include file="./total_sales.tpl"}</div>
		<div id="nt_tab1_content" class="panel tab">{include file="./total_categories_sales.tpl"}</div>
		<div id="nt_tab2_content" class="panel tab">{include file="./total_products_sales.tpl"}</div>
		<div id="nt_tab3_content" class="panel tab">{include file="./total_combinations_sales.tpl"}</div>
		<div id="nt_tab4_content" class="panel tab">{include file="./total_countries_sales.tpl"}</div>
		<div id="nt_tab5_content" class="panel tab">{include file="./total_manufacturers_sales.tpl"}</div>
		<div id="nt_tab6_content" class="panel tab">{include file="./total_payment_methods_sales.tpl"}</div>
        {* Compare *}
		<div id="nt_tab7_content" class="panel tab">{include file="./compare_total_sales.tpl"}</div>
		<div id="nt_tab8_content" class="panel tab">{include file="./compare_total_categories_sales.tpl"}</div>
		<div id="nt_tab9_content" class="panel tab">{include file="./compare_total_products_sales.tpl"}</div>
		<div id="nt_tab10_content" class="panel tab">{include file="./compare_total_combinations_sales.tpl"}</div>
		<div id="nt_tab11_content" class="panel tab">{include file="./compare_total_countries_sales.tpl"}</div>
		<div id="nt_tab12_content" class="panel tab">{include file="./compare_total_manufacturers_sales.tpl"}</div>
		<div id="nt_tab13_content" class="panel tab">{include file="./compare_total_payment_methods_sales.tpl"}</div>
        {* Products *}
		<div id="nt_tab14_content" class="panel tab">{include file="./products.tpl"}</div>
        <div id="nt_tab15_content" class="panel tab">{include file="./products_with_out_stock_combination.tpl"}</div>
		<div id="nt_tab16_content" class="panel tab">{include file="./products_with_combinations_without_enough_stock.tpl"}</div>
        {* Combinations *}
		<div id="nt_tab17_content" class="panel tab">{include file="./combinations.tpl"}</div>
		<div id="nt_tab18_content" class="panel tab">{include file="./combinations_unsold_with_stock.tpl"}</div>
        {* Categories *}
		<div id="nt_tab19_content" class="panel tab">{include file="./categories.tpl"}</div>
        {* Orders *}
		<div id="nt_tab20_content" class="panel tab">{include file="./orders.tpl"}</div>
        {* Statuses *}
		<div id="nt_tab21_content" class="panel tab">{include file="./duration_statuses.tpl"}</div>
        {* Carriers *}
		<div id="nt_tab22_content" class="panel tab">{include file="./carriers.tpl"}</div>
        {* Manufacturers *}
		<div id="nt_tab23_content" class="panel tab">{include file="./manufacturers.tpl"}</div>
        {* Customers *}
		<div id="nt_tab24_content" class="panel tab">{include file="./customers.tpl"}</div>
		<div id="nt_tab25_content" class="panel tab">{include file="./customers_single_order_amount.tpl"}</div>
		<div id="nt_tab26_content" class="panel tab">{include file="./customers_orders_amount.tpl"}</div>
		<div id="nt_tab27_content" class="panel tab">{include file="./customers_products.tpl"}</div>
		<div id="nt_tab28_content" class="panel tab">{include file="./customers_orders_details.tpl"}</div>
		<div id="nt_tab29_content" class="panel tab">{include file="./customers_products_details.tpl"}</div>
        {* Cart rules *}
		<div id="nt_tab30_content" class="panel tab">{include file="./cartrules.tpl"}</div>
        {* Configuration *}
		<div id="nt_tab31_content" class="panel tab">{include file="./configuration.tpl"}</div>
		<div id="nt_tab32_content" class="panel tab">{include file="./automation.tpl"}</div>

		<div id="nt_tab33_content" class="panel tab">{include file="./faq.tpl"}</div>
		<div id="nt_tab34_content" class="panel tab">{include file="./documentation.tpl"}</div>
		<div id="nt_tab35_content" class="panel tab">{include file="./contact.tpl"}</div>
		<div id="nt_tab36_content" class="panel tab">{include file="./our_modules.tpl"}</div>
        {*{if $display_translate_tab}*}
		<div id="nt_tab37_content" class="panel tab">{include file="./translate.tpl"}</div>
        {*{/if}*}
		<div class="clear"></div>
	</div>
    <div class="clear"></div>
    <div id="loader_container">
        <div id="grey_background"></div>
        <div id="loader_txt"></div>
        <img id="loader" src="{$ajax_loader|escape:'html':'UTF-8'}"/>
    </div>
</div>

