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
{extends file="helpers/form/form.tpl"}

{block name="script"}
var warehouse_required = '{l s='Please select warehouse from the list' js=1 mod='wkwarehouses'}.';
{/block}

{block name="description"}
    {if isset($input.desc) && !empty($input.desc)}
        <p class="help-block">
            {if is_array($input.desc)}
                {foreach $input.desc as $p}
                    {if is_array($p)}
                        <span id="{$p.id|escape:'html':'UTF-8'}">{$p.text|escape:'html':'UTF-8'}</span><br />
                    {else}
                        {$p}{* HTML CONTENT *}<br />
                    {/if}
                {/foreach}
            {else}
                {if $input.name == 'WKWAREHOUSE_STOCKPRIORITY_INC' || $input.name == 'WKWAREHOUSE_STOCKPRIORITY_DEC'}
                <div class="alert alert-info">
                    {$input.desc|escape:'html':'UTF-8'}
                </div>          
                {else}
                	{$input.desc}{* HTML CONTENT *}
                    {if $input.name == 'WKWAREHOUSE_PAGINATION_NUMBER_LINKS'}
                    <br />
                    <img src="{$module_path|escape:'html':'UTF-8'}/views/img/links_pagination.png" />
                    {/if}
                {/if}
            {/if}
        </p>
    {/if}
{/block}

{block name="label"}
    {if $input.type == 'free'}
    	{if $input.name == 'option_settings'}
			<div class="left-free-block">{$input.label|escape:'html':'UTF-8'}</div>
    	{else if $input.name == 'option_warnings'}
            <div class="alert alert-warning warn">
        		{$input.label|escape:'html':'UTF-8'|replace:'\n':'<br>'}
        	</div>
    	{else if $input.name == 'separator'}
            <hr />
    	{/if}
    {else}
		{$smarty.block.parent}
    {/if}
{/block}

{block name='input'}
    {************************************ HANDLE PRIORITY IN CASE OF INCREASE **************************************}
    {if $input.type == 'priority_increase'}
    	{if $warehouses_increase|@count}
	    <div class="row" id="{$input.type|escape:'html':'UTF-8'}">
	    	<div class="col-lg-1">
	    		<h4>{l s='Position' mod='wkwarehouses'}</h4> 
                <a href="#" class="btn btn-default menuOrderUp"><i class="icon-chevron-up"></i></a><br />
                <a href="#" class="btn btn-default menuOrderDown"><i class="icon-chevron-down"></i></a><br />
	    	</div>
	    	<div class="col-lg-5">
	    		<h4>{l s='Selected warehouses' mod='wkwarehouses'}</h4>
                <select multiple="multiple" name="warehouseBox[]" class="warehouseList pages-select">
        		{assign var=k value=1}
                {foreach from=$warehouses_increase item=wh}
                    <option value="{$wh.id_warehouse|intval}" selected="selected">{$k|intval} - {$wh.name|escape:'html':'UTF-8'}</option>
            		{assign var=k value=$k+1}
                {/foreach}
                </select>
	    	</div>
	    </div>
	    <br />
        {else}
            <div class="alert alert-warning">
            	{l s='You have to create warehouse(s) before to be able to define priorities' mod='wkwarehouses'}.<br />
                <a class="btn btn-default" href="{$link->getAdminLink('AdminManageWarehouses')|escape:'html':'UTF-8'}&addwarehouse" target="_blank"><i class="icon-plus-sign"></i> {l s='Create a new warehouse' mod='wkwarehouses'}?</a>
            </div>
        {/if}
        <div class="alert alert-info">
            {l s='The Warehouses Priority is used to determine which one has priority to be updated' mod='wkwarehouses'}.
        </div>
    {************************************ HANDLE PRIORITY IN CASE OF DECREASE **************************************}
    {else if $input.type == 'priority_decrease'}
    	{if $warehouses_decrease|@count}
	    <div class="row" id="{$input.type|escape:'html':'UTF-8'}">
	    	<div class="col-lg-1">
	    		<h4>{l s='Position' mod='wkwarehouses'}</h4>
                <a href="#" class="btn btn-default menuOrderUp"><i class="icon-chevron-up"></i></a><br />
                <a href="#" class="btn btn-default menuOrderDown"><i class="icon-chevron-down"></i></a><br />
	    	</div>
	    	<div class="col-lg-5">
	    		<h4>{l s='Selected warehouses' mod='wkwarehouses'}</h4>
                <select multiple="multiple" name="warehouseDecreaseBox[]" class="warehouseList pages-select">
        		{assign var=k value=1}
                {foreach from=$warehouses_decrease item=wh}
                    <option value="{$wh.id_warehouse|intval}" selected="selected">{$k|intval} - {$wh.name|escape:'html':'UTF-8'}</option>
            		{assign var=k value=$k+1}
                {/foreach}
                </select>
	    	</div>
	    </div>
	    <br/>
        {else}
            <div class="alert alert-warning">
            	{l s='You have to create warehouse(s) before to be able to define priorities' mod='wkwarehouses'}.<br />
                <a class="btn btn-default" href="{$link->getAdminLink('AdminManageWarehouses')|escape:'html':'UTF-8'}&addwarehouse" target="_blank"><i class="icon-plus-sign"></i> {l s='Create a new warehouse' mod='wkwarehouses'}?</a>
            </div>
        {/if}
        <div class="alert alert-info">
            {l s='The Warehouses Priority is used to determine which one has priority to be updated' mod='wkwarehouses'}.
        </div>
    {else if $input.type == 'cronjob_fix_infos'}
        <div class="alert alert-info">
        	{l s='This interface let you set up the [1]automatic fix of the gap[/1] between the warehouses and Prestashop quantities through a cron job' tags=['<strong>'] mod='wkwarehouses'}.
        </div>
        <div class="clear"></div>
    {else if $input.type == 'cronjob_fix_asm'}
        <div class="alert alert-success">
        	{l s='To make this task automatic, you have to create and schedule a cron job which will be called by the given secure URL below, thus triggering the alignment between the warehouses and Prestashop quantities' mod='wkwarehouses'}.
        	<br />
        	<br />
        	{l s='If your server uses Linux and you have access to crontab. In that case add the line below to your crontab file which will have to be executed [1]every day at midnight[/1]' tags=['<strong>'] mod='wkwarehouses'}:
        	<br />
        	<br />
        	<code><span class="planification-crontime">0 0 * * *</span> {$cron_lunched_by|escape:'html':'UTF-8'} {$cron_url|escape:'html':'UTF-8'}</code><br /><br />
        </div>
        <div>
            {l s='If you need help to change the planification above, please use the following field' tags=['<strong>'] mod='wkwarehouses'}:
            <input type="text" name="cron_mhdmd" id="cron_mhdmd" value="0 0 * * *"/>
            <p class="help-block">{l s='At what time should this task be executed?' mod='wkwarehouses'}</p>
            {include file="./cron_schedule.tpl"}
        </div>
        <div class="clear"></div>
    {elseif $input.type == 'radio'}
        {foreach $input.values as $value}
            <div class="radio {if isset($input.class)}{$input.class|escape:'html':'UTF-8'}{/if}">
                {strip}
                <label>
                <input type="radio" name="{$input.name|escape:'html':'UTF-8'}" id="{$value.id|escape:'html':'UTF-8'}" value="{$value.value|escape:'html':'UTF-8'}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if (isset($input.disabled) && $input.disabled) or (isset($value.disabled) && $value.disabled)} disabled="disabled"{/if}/>
                    {if $input.name == 'WKWAREHOUSE_MODE_MULTICARRIER_CHOICE'}
                    	<a class="preview_large" href="{$value.label|escape:'html':'UTF-8'}">
                        	<img src="{$value.label|escape:'html':'UTF-8'}"/>
                        </a>
						<p class="help-block" style="color:#09C; font-size:14px"><strong>{if $value.value == "carriers-combinations"}{l s='As carriers combinations' mod='wkwarehouses'}{else}{l s='Carriers by warehouses' mod='wkwarehouses'}{/if}</strong></p>
                    {else}
                    	{$value.label|escape:'html':'UTF-8'}
                    {/if}
                </label>
                {/strip}
            </div>
            {if isset($value.p) && $value.p}<p class="help-block">{$value.p|escape:'html':'UTF-8'}</p>{/if}
        {/foreach}
	{else}
		{$smarty.block.parent}
    {/if}
{/block}
