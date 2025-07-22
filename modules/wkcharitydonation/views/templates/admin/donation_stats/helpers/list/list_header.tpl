{**
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License version 3.0
* that is bundled with this package in the file LICENSE.txt
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to a newer
* versions in the future. If you wish to customize this module for your needs
* please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright Since 2010 Webkul
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
*}

{extends file="helpers/list/list_header.tpl"}

{if isset($stats_page) && $stats_page == 'viewwk_donation_stats'}
    {block name="leadin"}
        <div class="panel kpi-container">
            <div class="row">
                <div class="col-xs-6 col-sm-4 box-stats color1" >
                    <div class="kpi-content">
                        <i class="icon-shopping-cart"></i>
                        <span class="title">{l s='Total Donations' mod='wkcharitydonation'}</span>
                        <span class="value">{$total_donations|escape:'html':'UTF-8'}</span>
                    </div>
                </div>
                <div class="col-xs-6 col-sm-4 box-stats color3" >
                    <div class="kpi-content">
                        <i class="icon-money"></i>
                        <span class="title">{l s='Total Donation Amount' mod='wkcharitydonation'}</span>
                        <span class="value">{$total_amount|escape:'html':'UTF-8'}</span>
                    </div>
                </div>
                <div class="col-xs-6 col-sm-4 box-stats color4" >
                    <a href="#start_products">
                        <div class="kpi-content">
                            <i class="icon-user"></i>
                            <span class="title">{l s='Total Customers' mod='wkcharitydonation'}</span>
                            <span class="value">{$total_customer|escape:'html':'UTF-8'}</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    {/block}
{/if}