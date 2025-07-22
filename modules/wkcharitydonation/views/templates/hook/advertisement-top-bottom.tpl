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
            .wk-adveritsement-hf{$donationAd.id_donation_info|escape:'html':'UTF-8'} .wkadv-title{
                color: {$donationAd.adv_title_color|escape:'html':'UTF-8'};
            }
            .wk-adveritsement-hf{$donationAd.id_donation_info|escape:'html':'UTF-8'} .wkadv-description{
                color: {$donationAd.adv_desc_color|escape:'html':'UTF-8'}
            }
            .wk-adveritsement-hf{$donationAd.id_donation_info|escape:'html':'UTF-8'} .wkadv-description p{
                color: {$donationAd.adv_desc_color|escape:'html':'UTF-8'}
            }
            .wk-adveritsement-hf{$donationAd.id_donation_info|escape:'html':'UTF-8'} .wkadv-description pre{
                color: {$donationAd.adv_desc_color|escape:'html':'UTF-8'}
            }
            .wk-adveritsement-hf{$donationAd.id_donation_info|escape:'html':'UTF-8'} .wkadv-donate-btn{
                border : 1px solid {$donationAd.button_border_color|escape:'html':'UTF-8'} !important;
                color: {$donationAd.button_text_color|escape:'html':'UTF-8'} !important;
            }
            .wk-adveritsement-hf{$donationAd.id_donation_info|escape:'html':'UTF-8'} {
                background-image: url('{$donationAd.image_path|escape:'html':'UTF-8'}');
                background-position: center;
                background-repeat: no-repeat;
                background-size: cover;
                clear: both;
                margin-bottom: 25px;
            }
        </style>
        {if $donationAd.is_global}
            <div class="wk-adveritsement-hf{$donationAd.id_donation_info|escape:'html':'UTF-8'}">
                <div class="wkad-detail row row-eq-height">
                    <div class="col-sm-12 col-md-9" id="donation-info" >
                        <div class="row">
                            <div class="col-sm-12 wkadv-title" id="">
                                {$donationAd.advertisement_title|escape:'html':'UTF-8'}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12 wkadv-description" id="wkadv-description-side">
                                {$donationAd.advertisement_description nofilter }
                            </div>
                        </div>
                    </div>
                    {if ($donationAd.show_donate_button)}
                        <div class="col-sm-12 col-md-3 wkadv-btn-div">
                            <a class="btn wkadv-donate-btn" href="{if isset($donationAd.button_link)}{$donationAd.button_link|escape:'html':'UTF-8'}{/if}">
                                {if (isset($donationAd.donate_button_text))}{$donationAd.donate_button_text|escape:'html':'UTF-8'}{/if}
                            </a>
                        </div>
                    {/if}
                </div>
            </div>
        {else}
            <div class="wk-adveritsement-hf{$donationAd.id_donation_info|escape:'html':'UTF-8'}">
                <div class="wkad-detail">
                    <div class="wkadv-title">
                        {$donationAd.advertisement_title|escape:'html':'UTF-8'}
                    </div>
                    <div class="wkadv-description">
                        {$donationAd.advertisement_description nofilter}
                    </div>
                    {if ($donationAd.show_donate_button) && isset($donationAd.button_link)}
                        <div class="wkadv-btn-div2">
                            <a href="{if isset($donationAd.button_link)}{$donationAd.button_link|escape:'html':'UTF-8'}{/if}" class="btn wkadv-donate-btn">{if (isset($donationAd.donate_button_text))}{$donationAd.donate_button_text|escape:'html':'UTF-8'}{/if}</a>
                        </div>
                    {/if}
                </div>
            </div>
        {/if}
    {/foreach}
{/if}