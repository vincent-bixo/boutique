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

{if isset($donationAds) && $donationAds}
    {foreach $donationAds as $donationAd}
        <style>
            .wk-adveritsement-lr{$donationAd.id_donation_info|escape:'html':'UTF-8'} .wkadv-title{
                color: {$donationAd.adv_title_color|escape:'html':'UTF-8'};
            }
            .wk-adveritsement-lr{$donationAd.id_donation_info|escape:'html':'UTF-8'} .wkadv-description{
                color: {$donationAd.adv_desc_color|escape:'html':'UTF-8'}
            }
            .wk-adveritsement-lr{$donationAd.id_donation_info|escape:'html':'UTF-8'} .wkadv-description p{
                color: {$donationAd.adv_desc_color|escape:'html':'UTF-8'}
            }
            .wk-adveritsement-lr{$donationAd.id_donation_info|escape:'html':'UTF-8'} .wkadv-description pre{
                color: {$donationAd.adv_desc_color|escape:'html':'UTF-8'}
            }
            .wk-adveritsement-lr{$donationAd.id_donation_info|escape:'html':'UTF-8'} .wkadv-donate-btn{
                border : 1px solid {$donationAd.button_border_color|escape:'html':'UTF-8'};
                color: {$donationAd.button_text_color|escape:'html':'UTF-8'}
            }
            .wk-adveritsement-lr{$donationAd.id_donation_info|escape:'html':'UTF-8'} {
                background-image : url('{$donationAd.image_path|escape:'html':'UTF-8'}');
                background-position : center;
                background-repeat : no-repeat;
                background-size : cover;
                margin-bottom : 25px;
            }
        </style>
        <div class="wk-adveritsement-lr{$donationAd.id_donation_info|escape:'html':'UTF-8'}">
            <div class="wkad-detail">
                <div class="wkadv-title">
                    {$donationAd.advertisement_title|escape:'html':'UTF-8'}
                </div>
                <div class="wkadv-description">
                    {$donationAd.advertisement_description nofilter}
                </div>
                {if ($donationAd.show_donate_button) && isset($donationAd.button_link)}
                <div id="wkadv-btn-side-div">
                    <a href="{$donationAd.button_link|escape:'html':'UTF-8'}" class="btn wkadv-donate-btn" id="wkadv-donate-btn-side">{if (isset($donationAd.donate_button_text))}{$donationAd.donate_button_text|escape:'html':'UTF-8'}{/if}</a>
                </div>
                {/if}
            </div>
        </div>
    {/foreach}
{/if}