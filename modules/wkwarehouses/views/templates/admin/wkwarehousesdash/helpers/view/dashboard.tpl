{*
* This file is part of the 'Wk Warehouses Management' module feature.
* Developped by Khoufi Wissem (2018).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<div class="panel">
    <div class="panel-heading"><i class="icon-link"></i> {l s='Manage Module' mod='wkwarehouses'}</div>
    {foreach $module_tabs AS $module_tab}
        {if !$module_tab['is_tool'] && !$module_tab['is_hidden']}
        {assign var=tabname value=$module_tab.name}
        <div class="col-lg-2">
          <div class="panel text-center"><img src="{$module_folder|escape:'html':'UTF-8'}/views/img/{$module_tab.ico|escape:'html':'UTF-8'}"><br /><br />
          <a href="{$link->getAdminLink({$module_tab.className|escape:'html':'UTF-8'})|escape:'html':'UTF-8'}" id="btn_panel"><i class="icon-cog"></i>&nbsp;{if !isset($tabname[$lang_iso])}{$tabname['en']|escape:'html':'UTF-8'}{else}{$tabname[$lang_iso]|escape:'html':'UTF-8'}{/if}</a>
            </div>
        </div>
        {/if}
    {/foreach}
    <div style="clear:both"></div>
</div>

<div style="clear:both"></div>
<div class="panel" id="dashboard-plus">
    <div class="panel-heading"><i class="icon-cog"></i> {l s='Tools & settings' mod='wkwarehouses'}</div>
    {foreach $module_tabs AS $module_tab}
        {if $module_tab['is_tool']}
        {assign var=tabname value=$module_tab.name}
        <div class="col-lg-2">
          <div class="panel text-center"><img src="{$module_folder|escape:'html':'UTF-8'}/views/img/{$module_tab.ico|escape:'html':'UTF-8'}"><br /><br />
          <a href="{$link->getAdminLink({$module_tab.className|escape:'html':'UTF-8'})|escape:'html':'UTF-8'}" id="btn_panel"><i class="icon-cog"></i>&nbsp;{if !isset($tabname[$lang_iso])}{$tabname['en']|escape:'html':'UTF-8'}{else}{$tabname[$lang_iso]|escape:'html':'UTF-8'}{/if}</a>
            </div>
        </div>
        {/if}
    {/foreach}
    <div class="col-lg-2">
      <div class="panel text-center"><img src="{$module_folder|escape:'html':'UTF-8'}/views/img/config.png"><br /><br />
      <a href="{$url_config|escape:'html':'UTF-8'}" id="btn_panel"><i class="icon-cogs"></i>&nbsp;{l s='Configuration Settings' mod='wkwarehouses'}</a>
        </div>
    </div>
	<div style="clear:both"></div>
</div>
