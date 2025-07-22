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
<style>
.wkalign {
    vertical-align: super;
}
.wkfont {
    font-size:18px;
}
</style>

{if isset($smarty.get.err)}
    {foreach from=$errors item=item key=key }
        {if $smarty.get.err == $key}
            <div class="alert alert-danger">
                {$item|escape:'html':'UTF-8'}
            </div>
        {/if}
    {/foreach}
{/if}

<form class="defaultForm form-horizontal Adminpatterns" action="" method="POST" enctype="multipart/form-data">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-cogs"></i> {l s='Global Advertisement' mod='wkcharitydonation'}
        </div>
        <div class="form-wrapper">
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
                    <p class="help-block">{l s='Change language for updating information in multiple language.' mod='wkcharitydonation'}</p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label">
                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Enable if you want to show global advertisement block on different pages.' mod='wkcharitydonation'}">{l s='Enable' mod='wkcharitydonation'}</span>
                </label>
                <div class="col-sm-9">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="activate_global_donation" id="activate_global_donation_on" value="1" checked="checked">
                        <label for="activate_global_donation_on">{l s='Yes' mod='wkcharitydonation'}</label>
                        <input type="radio" name="activate_global_donation" id="activate_global_donation_off" value="0" {if isset($smarty.post.activate_global_donation) && $smarty.post.activate_global_donation == 0}checked="checked"{elseif isset($donationInfo.active) && $donationInfo.active == 0}checked="checked"{/if}>
                        <label for="activate_global_donation_off">{l s='No' mod='wkcharitydonation'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            <div class="form-group">
                <label for="advertisement_title" class="col-sm-3 control-label required">
                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='This text will be used as the title of the global donation advertisement.' mod='wkcharitydonation'}">{l s='Advertisement title' mod='wkcharitydonation'}</span>
                    <img class="all_lang_icon" data-lang-id="{$currentLang.id_lang|escape:'html':'UTF-8'}" src="{$ps_img_lang_dir|escape:'html':'UTF-8'}{$currentLang.id_lang|escape:'html':'UTF-8'}.jpg">
                </label>
                <div class="col-sm-7">
                    {foreach from=$languages item=language}
                    {assign var="advertisement_title" value="advertisement_title_`$language.id_lang`"}
                    <input type="text" id="{$advertisement_title|escape:'html':'UTF-8'}" name="{$advertisement_title|escape:'html':'UTF-8'}" value="{if isset($smarty.post.$advertisement_title) && $smarty.post.$advertisement_title}{$smarty.post.$advertisement_title|escape:'html':'UTF-8'}{elseif isset($donationInfo.advertisement_title[$language.id_lang])}{$donationInfo.advertisement_title[$language.id_lang]|escape:'html':'UTF-8'}{/if}" class="form-control wk_text_field_all wk_text_field_{$language.id_lang|escape:'html':'UTF-8'}" {if $currentLang.id_lang != $language.id_lang}style="display:none;"{/if}>
                    {/foreach}
                   {l s='Maximum 128 character allowed.' mod='wkcharitydonation'}
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label required">
                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Choose the global donation advertisement title text color.' mod='wkcharitydonation'}">{l s='Advertisement title text color' mod='wkcharitydonation'}</span>
                </label>
                <div class="col-sm-3">
                    <div class="input-group">
                        <input type="color" name="adv_title_color" id="adv_title_color" class="form-control mColorPickerInput mColorPicker" data-hex="true" value="{if isset($smarty.post.adv_title_color)}{$smarty.post.adv_title_color|escape:'htmlall':'UTF-8'}{elseif isset($donationInfo.adv_title_color) && $donationInfo.adv_title_color != null}{$donationInfo.adv_title_color|escape:'html':'UTF-8'}{else}#b20600{/if}" readonly/>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="advertisement_description" class="col-sm-3 control-label required">
                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='This text will be used as the description of the global donation advertisement.' mod='wkcharitydonation'}">{l s='Advertisement description' mod='wkcharitydonation'}</span>
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
                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Choose the global donation advertisement description text color.' mod='wkcharitydonation'}">{l s='Advertisement description text color' mod='wkcharitydonation'}</span>
                </label>
                <div class="col-sm-3">
                    <div class="input-group">
                        <input type="color" name="adv_desc_color" id="adv_desc_color" class="form-control mColorPickerInput mColorPicker" data-hex="true" value="{if isset($smarty.post.adv_desc_color)}{$smarty.post.adv_desc_color|escape:'htmlall':'UTF-8'}{elseif isset($donationInfo.adv_desc_color) && $donationInfo.adv_desc_color != null}{$donationInfo.adv_desc_color|escape:'html':'UTF-8'}{else}#b20600{/if}" readonly/>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-3 required">
                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='This image will be used as background image for global donation advertisement at header and footer position.' mod='wkcharitydonation'}">{l s='Advertisement background image header/footer' mod='wkcharitydonation'}</span>
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
                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='This image will be used as background image for global donation advertisement at left and right column.' mod='wkcharitydonation'}">{l s='Advertisement background image left/right' mod='wkcharitydonation'}</span>
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
                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='If enabled, a button will be displayed with the category link of all donations on global donation advertisement block.' mod='wkcharitydonation'}">{l s='Show all donations category button' mod='wkcharitydonation'}</span>
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
                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='This text will be used as the category button text' mod='wkcharitydonation'}">{l s='Donation category button text' mod='wkcharitydonation'}</span>
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
                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Choose the global donation advertisement category button text color' mod='wkcharitydonation'}">{l s='Donation category button text color' mod='wkcharitydonation'}</span>
                </label>
                <div class="col-sm-3">
                    <div class="input-group">
                        <input type="color" name="button_text_color" id="button_text_color" class="form-control mColorPickerInput mColorPicker" data-hex="true" value="{if isset($smarty.post.button_text_color)}{$smarty.post.button_text_color|escape:'html':'UTF-8'}{elseif isset($donationInfo.button_text_color) && $donationInfo.button_text_color != null}{$donationInfo.button_text_color|escape:'html':'UTF-8'}{else}#b20600{/if}" readonly/>
                    </div>
                </div>
            </div>
            <div class="form-group donate_button hidden">
                <label class="col-sm-3 control-label required">
                    <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Choose the global donation advertisement category button border color' mod='wkcharitydonation'}">{l s='Donation category button border color' mod='wkcharitydonation'}</span>
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
                        <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Select the position at which global donation advertisement block will be displayed' mod='wkcharitydonation'}">{l s='Select places for advertisement' mod='wkcharitydonation'}</span>
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
                                    <td><input type="checkbox" name="page_hook[{$page.id_page|escape:'html':'UTF-8'}][]" value="{$hook.id_hook|escape:'html':'UTF-8'}"{if isset($smarty.post.page_hook[$page.id_page])}{if $hook.id_hook|in_array:$smarty.post.page_hook[$page.id_page]} checked{/if}{elseif isset($donate_hooks[$page.id_page]) && !empty($donate_hooks[$page.id_page])}{if $hook.id_hook|in_array:$donate_hooks[$page.id_page]} checked {/if}{/if} /></td>
                                    {/foreach}
                                </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" name="submitAdd{$table|escape:'html':'UTF-8'}" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i> {l s='Save' mod='wkcharitydonation'}
                </button>
            </div>
        </div>
    </div>
</form>
