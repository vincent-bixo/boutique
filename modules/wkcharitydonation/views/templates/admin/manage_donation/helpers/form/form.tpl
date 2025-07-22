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
{if isset($smarty.get.err)}
    {foreach from=$errors item=$item key=$key}
        {if $key == $smarty.get.err}
            <div class="alert alert-danger">
                {$item|escape:'html':'UTF-8'}
            </div>
        {/if}
    {/foreach}
{/if}

<form class="defaultForm form-horizontal Adminpatterns" action="{$adminManageDonationUrl|escape:'html':'UTF-8'}" method="POST" enctype="multipart/form-data" onsubmit=getActiveTabAfterSubmitForm()>
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-cogs"></i>
            {if isset($donationInfo['id'])}
                {l s='Manage Donation' mod='wkcharitydonation'}
            {else}
                {l s='Add new Donation' mod='wkcharitydonation'}
            {/if}
        </div>
        <div class="form-wrapper wk-tabs-panel">
            <div class="form-group">
                <div class="col-lg-6">
                    <label class="control-label">{l s='Choose language' mod='wkcharitydonation'}</label>
                    <input type="hidden" name="choosedLangId" id="choosedLangId" value="{$currentLang.iso_code|escape:'html':'UTF-8'}">
                    <button type="button" id="donation_lang_btn" class="btn btn-default dropdown-toggle wk_language_toggle" data-toggle="dropdown">
                            {$currentLang.name|escape:'html':'UTF-8'}
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu wk_language_menu" style="left:14%;top:32px;">
                        {foreach from=$languages item=language}
                            <li>
                                <a href="javascript:void(0)" onclick="showManageDonationLangField('{$language.name|escape:'html':'UTF-8'}', '{$language.id_lang|escape:'html':'UTF-8'}');">
                                    {$language.name|escape:'html':'UTF-8'}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                    <p class="help-block">{l s='Change language for updating information in multiple languages.' mod='wkcharitydonation'}</p>
                </div>
            </div>
            <input type="hidden" name="active_tab" value="{if isset($active_tab)}{$active_tab|escape:'html':'UTF-8'}{/if}" id="active_tab">
            <div class="alert alert-info">
                {l s='For this donation, a product in catalog will be automatically created. You will have to fill details of some fields according to that created product' mod='wkcharitydonation'}
            </div>
            <ul class="nav nav-tabs" id="tab-select">
                <li class="active">
                    <a href="#information" data-toggle="tab">
                        <i class="icon-info-sign"></i> {l s='Information' mod='wkcharitydonation'}
                    </a>
                </li>
                <li>
                    <a href="#advetisement" data-toggle="tab">
                        <i class="icon-th-large"></i> {l s='Advertisement' mod='wkcharitydonation'}
                    </a>
                </li>
                <li>
                    <a href="#wk_donation_product_images" data-toggle="tab">
                        <i class="icon-image"></i> {l s='Images' mod='wkcharitydonation'}
                    </a>
                </li>
            </ul>
            <div class="tab-content panel">
                <div id="information" class="tab-pane active">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Enable if you want to enable this donation' mod='wkcharitydonation'}">{l s='Enable' mod='wkcharitydonation'}</span>
                        </label>
                        <div class="col-sm-9">
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input type="radio" name="activate_donation" id="activate_donation_on" value="1" checked="checked">
                                <label for="activate_donation_on">{l s='Yes' mod='wkcharitydonation'}</label>
                                <input type="radio" name="activate_donation" id="activate_donation_off" value="0" {if isset($donationInfo.active) && $donationInfo.active == 0}checked="checked"{/if}>
                                <label for="activate_donation_off">{l s='No' mod='wkcharitydonation'}</label>
                                <a class="slide-button btn"></a>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name" class="col-sm-3 control-label required">
                            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='This text will be used as the title for the donation product.' mod='wkcharitydonation'}">{l s='Name' mod='wkcharitydonation'}</span>
                            <img class="all_lang_icon" data-lang-id="{$currentLang.id_lang|escape:'html':'UTF-8'}" src="{$ps_img_lang_dir|escape:'html':'UTF-8'}{$currentLang.id_lang|escape:'html':'UTF-8'}.jpg">
                        </label>
                        <div class="col-sm-7">
                            {foreach from=$languages item=language}
                            {assign var="name" value="name_`$language.id_lang`"}
                            <input type="text" id="{$name|escape:'html':'UTF-8'}" name="{$name|escape:'html':'UTF-8'}" value="{if isset($smarty.post.$name)}{$smarty.post.$name|escape:'html':'UTF-8'}{elseif isset($donationInfo.name[$language.id_lang])}{$donationInfo.name[$language.id_lang]|escape:'html':'UTF-8'}{/if}" class="form-control wk_text_field_all wk_text_field_{$language.id_lang|escape:'html':'UTF-8'}" {if $currentLang.id_lang != $language.id_lang}style="display:none;"{/if}/>
                            {/foreach}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description" class="col-sm-3 control-label required">
                            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='This text will be used as the description for the donation product.' mod='wkcharitydonation'}">{l s='Description' mod='wkcharitydonation'}</span>
                            <img class="all_lang_icon" data-lang-id="{$currentLang.id_lang|escape:'html':'UTF-8'}" src="{$ps_img_lang_dir|escape:'html':'UTF-8'}{$currentLang.id_lang|escape:'html':'UTF-8'}.jpg">
                        </label>
                        <div class="col-sm-7">
                            <div class="row">
                                <div class="col-sm-12">
                                    {foreach from=$languages item=language}
                                    {assign var="description" value="description_`$language.id_lang`"}
                                        <div class="wk_text_field_all wk_text_field_{$language.id_lang|escape:'html':'UTF-8'}" {if $currentLang.id_lang != $language.id_lang}style="display:none;"{/if}>
                                            <textarea id="{$description|escape:'html':'UTF-8'}" name="{$description|escape:'html':'UTF-8'}" class="form-control wk_tinymce">{if isset($smarty.post.$description)}{$smarty.post.$description|escape:'html':'UTF-8'}{elseif isset($donationInfo.description[$language.id_lang])}{$donationInfo.description[$language.id_lang]|escape:'html':'UTF-8'}{/if}</textarea>
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="price_type" class="col-sm-3 control-label">
                            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Choose "fixed" if you want fixed price for donation. Choose "by customer" if you want price to be entered by customer.' mod='wkcharitydonation'}">{l s='Price type' mod='wkcharitydonation'}</span>
                        </label>
                        <div class="col-sm-3">
                            <select name="price_type" class="" id="price_type">
                                <option value="1" selected>{l s='Fixed' mod='wkcharitydonation'}</option>
                                <option value="2" {if isset($smarty.post.price_type) && $smarty.post.price_type == 2}selected{elseif isset($donationInfo.price_type) && $donationInfo.price_type == 2}selected{/if}>{l s='By customer' mod='wkcharitydonation'}</option>
                            </select>
                            <div class="help-block"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="price" class="col-sm-3 control-label required" id="fixed">
                            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='If price is "fixed" then this price will be taken as fixed price. If price type is "by customer" then this price will be taken as minimum price which customer can donate.' mod='wkcharitydonation'}">{l s='Fixed price' mod='wkcharitydonation'}</span>
                        </label>
                        <label for="price" class="col-sm-3 control-label required hidden" id="minimum_price">
                            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='If price is "fixed" then this price will be taken as fixed price. If price type is "by customer" then this price will be taken as minimum price which customer can donate.' mod='wkcharitydonation'}">{l s='Minimum price' mod='wkcharitydonation'}</span>
                        </label>
                        <div class="col-sm-3">
                            <div class="input-group">
                                <input type="text" id="price" name="price" value="{if isset($smarty.post.price)}{$smarty.post.price|escape:'htmlall':'UTF-8'}{elseif isset($donationInfo.price)}{$donationInfo.price|escape:'htmlall':'UTF-8'}{/if}" class="form-control">
                                <span class="input-group-addon">{$defaultCurrencySign|escape:'html':'UTF-8'}</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Date, till which the charity is valid.' mod='wkcharitydonation'}">{l s='Expiry date' mod='wkcharitydonation'}</span>
                        </label>
                        <div class="col-sm-3">
                            <div class="input-group">
                                <input type="text" name="expiry_date" id="donation_expiry_date" class="form-control" value="{if isset($smarty.post.expiry_date)}{$smarty.post.expiry_date|date_format: "%Y-%m-%d"|escape:'html':'UTF-8'}{elseif isset($donationInfo.expiry_date)}{$donationInfo.expiry_date|date_format: "%Y-%m-%d"|escape:'html':'UTF-8'}{/if}"autocomplete="off" readonly>
                                <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Enable if you want to show the product created for this donation to the customer.' mod='wkcharitydonation'}">{l s='Show product' mod='wkcharitydonation'}</span>
                        </label>
                        <div class="col-sm-9">
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input type="radio" name="product_visibility" id="product_visibility_on" value="1" checked="checked">
                                <label for="product_visibility_on">{l s='Yes' mod='wkcharitydonation'}</label>
                                <input type="radio" name="product_visibility" id="product_visibility_off" value="0" {if isset($smarty.post.product_visibility) && $smarty.post.product_visibility == 0}checked="checked"{elseif isset($donationInfo.product_visibility) && $donationInfo.product_visibility == 0}checked="checked"{/if}>
                                <label for="product_visibility_off">{l s='No' mod='wkcharitydonation'}</label>
                                <a class="slide-button btn"></a>
                            </span>
                            <p class="help-block">
                                {l s='Note' mod='wkcharitydonation'} : {l s='By disabling \'Show product\', Donate button on advertisement will automatically disappear.' mod='wkcharitydonation'}
                            </p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Enable if you want to show the donation options on the cart page.' mod='wkcharitydonation'}">{l s='Show on cart page' mod='wkcharitydonation'}</span>
                        </label>
                        <div class="col-sm-9">
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input type="radio" name="show_at_checkout" id="show_at_checkout_on" value="1" checked="checked">
                                <label for="show_at_checkout_on">{l s='Yes' mod='wkcharitydonation'}</label>
                                <input type="radio" name="show_at_checkout" id="show_at_checkout_off" value="0" {if isset($smarty.post.show_at_checkout) && $smarty.post.show_at_checkout == 0}checked="checked"{elseif isset($donationInfo.show_at_checkout) && $donationInfo.show_at_checkout == 0}checked="checked"{/if}>
                                <label for="show_at_checkout_off">{l s='No' mod='wkcharitydonation'}</label>
                                <a class="slide-button btn"></a>
                            </span>
                        </div>
                    </div>
                </div>
                <div id="advetisement" class="tab-pane">
                    {if isset($donationInfo)}
                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Enable if you want to show this donation advertisement block on different pages.' mod='wkcharitydonation'}">{l s='Enable' mod='wkcharitydonation'}</span>

                        </label>
                        <div class="col-sm-9">
                            <span class="switch prestashop-switch fixed-width-lg">
                                <input type="radio" name="advertise" id="advertise_on" value="1"{if isset($smarty.post.advertise) && $smarty.post.advertise == 1} checked="checked"{elseif isset($donationInfo.advertise) && $donationInfo.advertise == 1}checked="checked"{/if}>
                                <label for="advertise_on">{l s='Yes' mod='wkcharitydonation'}</label>
                                <input type="radio" name="advertise" id="advertise_off" value="0" {if isset($smarty.post.advertise)}{if $smarty.post.advertise == 0} checked="checked"{/if}{elseif !isset($donationInfo.advertise)}checked="checked"{elseif isset($donationInfo.advertise) && $donationInfo.advertise == 0}checked="checked"{/if}>
                                <label for="advertise_off">{l s='No' mod='wkcharitydonation'}</label>
                                <a class="slide-button btn"></a>
                            </span>
                        </div>
                    </div>
                    <div class="form-wrapper hidden" id="advertisement_config">
                        <div class="form-group">
                            <label for="advertisement_title" class="col-sm-3 control-label required">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='This text will be used as the title for this donation advertisement.' mod='wkcharitydonation'}">{l s='Advertisement title' mod='wkcharitydonation'}</span>
                                <img class="all_lang_icon" data-lang-id="{$currentLang.id_lang|escape:'html':'UTF-8'}" src="{$ps_img_lang_dir|escape:'html':'UTF-8'}{$currentLang.id_lang|escape:'html':'UTF-8'}.jpg">
                            </label>
                            <div class="col-sm-7">
                                {foreach from=$languages item=language}
                                {assign var="advertisement_title" value="advertisement_title_`$language.id_lang`"}
                                <input type="text" id="{$advertisement_title|escape:'html':'UTF-8'}" name="{$advertisement_title|escape:'html':'UTF-8'}" value="{if isset($smarty.post.$advertisement_title)}{$smarty.post.$advertisement_title|escape:'html':'UTF-8'}{elseif isset($donationInfo.advertisement_title[$language.id_lang])}{$donationInfo.advertisement_title[$language.id_lang]|escape:'html':'UTF-8'}{/if}" class="form-control wk_text_field_all wk_text_field_{$language.id_lang|escape:'html':'UTF-8'}" {if $currentLang.id_lang != $language.id_lang}style="display:none;"{/if}>
                                {/foreach}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label required">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='choose the donation advertisement title text color.' mod='wkcharitydonation'}">{l s='Advertisement title color' mod='wkcharitydonation'}</span>
                            </label>
                            <div class="col-sm-3">
                                <div class="input-group">
                                <input type="color" name="adv_title_color" id="adv_title_color" class="form-control mColorPickerInput mColorPicker" data-hex="true" value="{if isset($smarty.post.adv_title_color)}{$smarty.post.adv_title_color|escape:'htmlall':'UTF-8'}{elseif isset($donationInfo.adv_title_color) && $donationInfo.adv_title_color != null}{$donationInfo.adv_title_color|escape:'html':'UTF-8'}{else}#b20600{/if}" readonly/>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="advertisement_description" class="col-sm-3 control-label required">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='This text will be used as the description for this donation advertisement.' mod='wkcharitydonation'}">{l s='Advertisement description' mod='wkcharitydonation'}</span>
                                <img class="all_lang_icon" data-lang-id="{$currentLang.id_lang|escape:'html':'UTF-8'}" src="{$ps_img_lang_dir|escape:'html':'UTF-8'}{$currentLang.id_lang|escape:'html':'UTF-8'}.jpg">
                            </label>
                            <div class="col-sm-7">
                                <div class="row">
                                    <div class="col-sm-12">
                                        {foreach from=$languages item=language}
                                        {assign var="advertisement_description" value="advertisement_description_`$language.id_lang`"}
                                            <div class="wk_text_field_all wk_text_field_{$language.id_lang|escape:'html':'UTF-8'}" {if $currentLang.id_lang != $language.id_lang}style="display:none;"{/if}>
                                                <textarea id="{$advertisement_description|escape:'html':'UTF-8'}" name="{$advertisement_description|escape:'html':'UTF-8'}" class="form-control wk_tinymce">{if isset($smarty.post.$advertisement_description)}{$smarty.post.$advertisement_description|escape:'html':'UTF-8'}{elseif isset($donationInfo.advertisement_description[$language.id_lang])}{$donationInfo.advertisement_description[$language.id_lang]|escape:'html':'UTF-8'}{/if}</textarea>
                                            </div>
                                        {/foreach}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label required">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Choose the donation advertisement description text color.' mod='wkcharitydonation'}">{l s='Advertisement description color' mod='wkcharitydonation'}</span>
                            </label>
                            <div class="col-sm-3">
                                <div class="input-group">
                                    <input type="color" name="adv_desc_color" id="adv_desc_color" class="form-control mColorPickerInput mColorPicker" data-hex="true" value="{if isset($smarty.post.adv_desc_color)}{$smarty.post.adv_desc_color|escape:'htmlall':'UTF-8'}{elseif isset($donationInfo.adv_desc_color) && $donationInfo.adv_desc_color != null}{$donationInfo.adv_desc_color|escape:'html':'UTF-8'}{else}#b20600{/if}" readonly/>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-3 required">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='This image will be used as background image for this donation advertisement at header and footer position.' mod='wkcharitydonation'}">{l s='Advertisement background image Header/Footer' mod='wkcharitydonation'}</span>
                            </label>
                            <div class="col-sm-8">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <p>
                                            <img src="{$imagePath_head_foot|escape:'html':'UTF-8'}" height="80px" width="600px" class="{if !$imagePath_head_foot}hidden{/if}">
                                        </p>
                                        <button class="btn btn-danger mb-1 wk_delete_charity_banner {if !$imagePath_head_foot}hidden{/if}" data-file="{$header_file|escape:'html':'UTF-8'}" type="button" style="margin-bottom: 10px;">
                                            <span><i class="material-icons wkfont">delete</i> <span class="wkalign">{l s='Delete image' mod='wkcharitydonation'}</span></span>
                                        </button>
                                        <input type="file" name="background_image_head_foot" class="hide" id="background_image_head_foot">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="icon-image"></i></span>
                                            <input id="banner_file_name_head_foot" type="text" name="background_image_head_foot" readonly>
                                            <span class="input-group-btn"><button type="button" id="image_select_btn_head_foot" name="submit_attachments" class="btn btn-default"><i class="icon-folder-open"></i> {l s='Add file' mod='wkcharitydonation'}</button></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 help-block">
                                        {l s='Note' mod='wkcharitydonation'} : {l s='Maximum image size' mod='wkcharitydonation'} - {$maxSizeAllowed|escape:'html':'UTF-8'}{l s='MB, Recommended size 1110 x 106px' mod='wkcharitydonation'}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-3">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='This image will be used as background image for this donation advertisement at left and right column.' mod='wkcharitydonation'}">{l s='Advertisement background image Left/Right' mod='wkcharitydonation'}</span>
                            </label>
                            <div class="col-sm-8">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <p>
                                            <img src="{$imagePath_left_right|escape:'html':'UTF-8'}" height="200px" width="160px" class="{if !$imagePath_left_right}hidden{/if}">
                                        </p>
                                        <button class="btn btn-danger mb-1 wk_delete_charity_banner {if !$imagePath_left_right}hidden{/if}" data-file="{$left_right_file|escape:'html':'UTF-8'}" type="button" style="margin-bottom: 10px;">
                                            <span><i class="material-icons wkfont">delete</i> <span class="wkalign">{l s='Delete image' mod='wkcharitydonation'}</span></span>
                                        </button>
                                        <input type="file" name="background_image_left_right" class="hide" id="background_image_left_right">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="icon-image"></i></span>
                                            <input id="banner_file_name_left_right" type="text" name="background_image_left_right" readonly>
                                            <span class="input-group-btn"><button type="button" id="image_select_btn_left_right" name="submit_attachments" class="btn btn-default"><i class="icon-folder-open"></i> {l s='Add file' mod='wkcharitydonation'}</button></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 help-block">
                                        {l s='Note' mod='wkcharitydonation'} : {l s='Maximum image size' mod='wkcharitydonation'} - {$maxSizeAllowed|escape:'html':'UTF-8'}{l s='MB, Recommended size 275 x 332px' mod='wkcharitydonation'}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='If enabled, a button will be displayed with the product link of this donation on advertisement block.' mod='wkcharitydonation'}">{l s='Show donate button' mod='wkcharitydonation'}</span>
                            </label>
                            <div class="col-sm-3">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="show_donate_button" id="show_donate_button_on" value="1" checked="checked">
                                    <label for="show_donate_button_on">{l s='Yes' mod='wkcharitydonation'}</label>
                                    <input type="radio" name="show_donate_button" id="show_donate_button_off" value="0" {if isset($smarty.post.show_donate_button) && $smarty.post.show_donate_button == 0}checked="checked"{elseif isset($donationInfo.show_donate_button) && $donationInfo.show_donate_button == 0}checked="checked"{/if}>
                                    <label for="show_donate_button_off">{l s='No' mod='wkcharitydonation'}</label>
                                    <a class="slide-button btn"></a>
                                </span>
                            </div>
                        </div>
                        <div class="form-group hidden donate_button" id="button_text_div">
                            <label for="donate_button_text" class="col-sm-3 control-label required">
                                <span>{l s='Donate button text' mod='wkcharitydonation'}</span>
                                <img class="all_lang_icon" data-lang-id="{$currentLang.id_lang|escape:'html':'UTF-8'}" src="{$ps_img_lang_dir|escape:'html':'UTF-8'}{$currentLang.id_lang|escape:'html':'UTF-8'}.jpg">
                            </label>
                            <div class="col-sm-3">
                                {foreach from=$languages item=language}
                                {assign var="donate_button_text" value="donate_button_text_`$language.id_lang`"}
                                <input type="text" id="{$donate_button_text|escape:'html':'UTF-8'}" name="{$donate_button_text|escape:'html':'UTF-8'}" value="{if isset($smarty.post.$donate_button_text)}{$smarty.post.$donate_button_text|escape:'html':'UTF-8'}{elseif isset($donationInfo.donate_button_text[$language.id_lang])}{$donationInfo.donate_button_text[$language.id_lang]|escape:'html':'UTF-8'}{/if}" class="form-control wk_text_field_all wk_text_field_{$language.id_lang|escape:'html':'UTF-8'}" {if $currentLang.id_lang != $language.id_lang}style="display:none;"{/if}>
                                {/foreach}
                            </div>
                        </div>
                        <div class="form-group donate_button hidden">
                            <label class="col-sm-3 control-label required">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Choose this donation advertisement donation button text color.' mod='wkcharitydonation'}">{l s='Button text color' mod='wkcharitydonation'}</span>
                            </label>
                            <div class="col-sm-3">
                                <div class="input-group">
                                    <input type="color" name="button_text_color" id="button_text_color" class="form-control mColorPickerInput mColorPicker" data-hex="true" value="{if isset($smarty.post.button_text_color)}{$smarty.post.button_text_color|escape:'htmlall':'UTF-8'}{elseif isset($donationInfo.button_text_color) && $donationInfo.button_text_color != null}{$donationInfo.button_text_color|escape:'html':'UTF-8'}{else}#b20600{/if}" readonly/>
                                </div>
                            </div>
                        </div>
                        <div class="form-group donate_button hidden">
                            <label class="col-sm-3 control-label required">
                                <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Choose this donation advertisement donation button border color.' mod='wkcharitydonation'}">{l s='Button border color' mod='wkcharitydonation'}</span>
                            </label>
                            <div class="col-sm-3">
                                <div class="input-group">
                                    <input type="color" name="button_border_color" id="button_border_color" class="form-control mColorPickerInput mColorPicker" data-hex="true" value="{if isset($smarty.post.button_border_color)}{$smarty.post.button_border_color|escape:'htmlall':'UTF-8'}{elseif isset($donationInfo.button_border_color) && $donationInfo.button_border_color != null}{$donationInfo.button_border_color|escape:'html':'UTF-8'}{else}#b20600{/if}" readonly/>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label class="col-lg-3 control-label required">
                                <span class="title_box">
                                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Select the position at which this donation advertisement block will be displayed.' mod='wkcharitydonation'}">{l s='Select places for advertisement' mod='wkcharitydonation'}</span>
                                </span>
                                </label>
                                <div class="col-lg-7">
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                            <tr>
                                                <th>{l s='Pages / Places' mod='wkcharitydonation'}</th>
                                                {foreach from=$hooks item=$hook}
                                                    <th>{$hook.name|escape:'html':'UTF-8'}</th>
                                                {/foreach}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {foreach from=$pages item=$page}
                                            <tr>
                                                <td>{$page.name|escape:'html':'UTF-8'}</td>
                                                {foreach from=$hooks item=$hook}
                                                <td><input type="checkbox" name="page_hook[{$page.id_page|escape:'html':'UTF-8'}][]" value="{$hook.id_hook|escape:'html':'UTF-8'}" {if isset($smarty.post.page_hook[$page.id_page])}{if $hook.id_hook|in_array:$smarty.post.page_hook[$page.id_page]} checked{/if}{elseif isset($donate_hooks[$page.id_page]) && !empty($donate_hooks[$page.id_page])}{if $hook.id_hook|in_array:$donate_hooks[$page.id_page]} checked {/if}{/if} /></td>
                                                {/foreach}
                                            </tr>
                                            {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>


                    </div>
                    {else}
                        <div class="alert alert-warning">
                            {l s='You must save this donation before adding advertisement.' mod='wkcharitydonation'}
                        </div>
					{/if}
                </div>
                <div id="wk_donation_product_images" class="tab-pane">
                    <div class="alert alert-info">
                        {l s='From this page, you can upload the images of the created product for this donation.' mod='wkcharitydonation'}
                    </div>
                    {if isset($donationInfo)}
                        <div class="form-group row">
                            <label for="" class="col-sm-3 control-label">
								{l s='Upload images' mod='wkcharitydonation'}
							</label>
                            <div class="col-sm-5">
                                <input id="id_donation_info" type="hidden" name="id_donation_info" value="{$donationInfo['id']|escape:'html':'UTF-8'}">
								<input class="form-control-static hide" type="file" id="donation_product_images" name="donation_product_images[]" multiple>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="icon-image"></i></span>
                                    <input id="image_file_name" type="text" name="donation_product_images" readonly>
                                    <span class="input-group-btn"><button type="button" id="donation_image_select_btn" name="submit_donation_images" class="btn btn-default"><i class="icon-folder-open"></i> {l s='Add file' mod='wkcharitydonation'}</button></span>
                                </div>
							</div>
                        </div>

                        {*image Table*}
                        <h4><i class="icon-image"></i> <span>{l s='Donation product images' mod='wkcharitydonation'}</span></h4>
						<div class="row">
							<div class="col-sm-12">
								<div class="table-responsive">
									<table class="table" id="donation-image-table">
										<thead>
											<tr>
												<th class="text-center">{l s='Image id' mod='wkcharitydonation'}</th>
												<th class="text-center">{l s='Image' mod='wkcharitydonation'}</th>
												<th class="text-center">{l s='Cover' mod='wkcharitydonation'}</th>
												<th class="text-center">{l s='Action' mod='wkcharitydonation'}</th>
											</tr>
										</thead>
										<tbody>
											{if isset($donationImages) && $donationImages}
												{foreach from=$donationImages item=image name=donationImage}
													<tr class="{if $image.cover == 1}cover-image-tr{/if}">
														<td class="text-center image-id">{$image.id_image|escape:'html':'UTF-8'}</td>
														<td class="text-center">
															<a class="img-preview" href="{$image.image_link|escape:'html':'UTF-8'}">
																<img class="img-thumbnail" width="100" src="{$image.image_link|escape:'html':'UTF-8'}"/>
															</a>
														</td>
														<td class="text-center {if $image.cover == 1}cover-image-td{/if}">
															<a href="#" class="{if $image.cover == 1}text-success{else}text-danger{/if} changer-cover-image" data-id-donation-product="{$donationInfo.id_product|escape:'html':'UTF-8'}" data-is-cover="{if $image.cover == 1}1{else}0{/if}" data-id-image="{$image.id_image|escape:'html':'UTF-8'}">
																{if $image.cover == 1}
																	<i class="icon-check"></i>
																{else}
																	<i class="icon-times"></i>
																{/if}
															</a>
														</td>
														<td class="text-center">
															<button type="button" class="btn btn-default delete-donation-image" data-id-donation-product="{$donationInfo.id_product|escape:'html':'UTF-8'}" data-is-cover="{if $image.cover == 1}1{else}0{/if}" data-id-image="{$image.id_image|escape:'html':'UTF-8'}"><i class="icon-trash"></i></button>
														</td>
													</tr>
												{/foreach}
											{else}
												<tr class="list-empty-tr">
													<td class="list-empty" colspan="5">
														<div class="list-empty-msg">
															<i class="icon-warning-sign list-empty-icon"></i>
															{l s='No Image Found' mod='wkcharitydonation'}
														</div>
													</td>
												</tr>
											{/if}
										</tbody>
									</table>
								</div>
							</div>
						</div>
                    {else}
                    <div class="alert alert-warning">
                        {l s='You must save this donation before adding images.' mod='wkcharitydonation'}
                    </div>
                    {/if}
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <a href="{$adminManageDonationUrl|escape:'html':'UTF-8'}" class="btn btn-default">
                <i class="process-icon-cancel"></i>{l s='Cancel' mod='wkcharitydonation'}
            </a>
            <button type="submit" name="submitAdd{$table|escape:'html':'UTF-8'}" class="btn btn-default pull-right wk_save_btn">
                <i class="process-icon-save"></i>{l s='Save' mod='wkcharitydonation'}
            </button>
            <input type="hidden"></input>
            <button type="submit" name="submitAdd{$table|escape:'html':'UTF-8'}AndStay" onclick=test() class="btn btn-default pull-right wk_save_btn">
                <i class="process-icon-save"></i> {l s='Save and stay' mod='wkcharitydonation'}
            </button>
        </div>
    </div>
</form>