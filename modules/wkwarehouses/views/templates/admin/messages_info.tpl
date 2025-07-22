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
<br />
{if !Configuration::get('PS_STOCK_MANAGEMENT')}
	<div class="alert alert-danger">
		{l s='You must enable Stock Management before (from your Products settings page) to avoid any troubles by using our module' mod='wkwarehouses'}.
	</div>
{/if}
{if isset($show_rating_block) && $show_rating_block}
<div id="addons-rating-container" class="ui-widget note">
    <div style="margin-bottom: 20px; padding: 1em; text-align: center;" class="ui-state-highlight ui-corner-all">
        <p class="invite">
        	{l s='You are satisfied with our module and want to encourage us to add new features ?' mod='wkwarehouses'}
            <br/>
            <a href="http://addons.prestashop.com/ratings.php" target="_blank"><strong>
            {l s='Please rate it on Prestashop Addons, and give us 5 stars !' mod='wkwarehouses'}
            </strong></a>
        </p>
        <p class="dismiss">
            [<a href="javascript:void(0);">
            {l s='Don\'t show this message again' mod='wkwarehouses'}
            </a>]
        </p>
    </div>
</div>
{/if}
{if Configuration::get('PS_DISABLE_OVERRIDES')}
	<div class="alert alert-danger">
		<strong>{l s='INCOMPLETE INSTALLATION' mod='wkwarehouses'}</strong> :<br />
		{l s='You are prohibited overrides, the module may not work properly' mod='wkwarehouses'}! <a href="{$link->getAdminLink('AdminPerformance')|escape:'html':'UTF-8'}">{l s='Enable it' mod='wkwarehouses'}?</a>
	</div>
{/if}
{if count($missing_overrides)}
	<div class="alert alert-danger">
		<strong>{l s='INCOMPLETE INSTALLATION' mod='wkwarehouses'}</strong> :
        <ul>
		{foreach $missing_overrides as $override}
			<li>{l s='Override from file [1]%s[/1] is not present in [1]%s[/1] folder' sprintf=[$override['source']|escape:'html':'UTF-8', $override['targetdir']|escape:'html':'UTF-8'] tags=['<strong>'] mod='wkwarehouses'}</li>
        {/foreach}
        </ul>
        <br />{l s='Please [1]reset[/1] module or contact module developer' tags=['<strong>'] mod='wkwarehouses'}.<br />
        {l s='If you decide to do by your own, don\'t forget to [1]save your settings[/1] from configuration page just after module reset' tags=['<strong>'] mod='wkwarehouses'}.
	</div>
{/if}
