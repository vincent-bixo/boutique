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

<div class="panel-heading">
    <i class="fas fa-store"></i>
    &nbsp;{l s='Our modules' mod='ntstats'}
</div>
<div class="our_modules">
    <div class="list_nt_modules">
        {foreach $our_modules as $module}
            {if $module.link}
                <div class="nt_module panel">
                    <a href="{$module.link|escape:'html':'UTF-8'}" target="_blank" class="name">{$module.name|escape:'html':'UTF-8'}</a>
                    <br/>
                    <br/>
                    <a href="{$module.link|escape:'html':'UTF-8'}" target="_blank">
                        <img class="nt_logo" src="../modules/ntstats/views/img/{$module.logo|escape:'html':'UTF-8'}" alt="2N Technologies"/>
                    </a>
                    <br/>
                    <br/>
                    <a href="{$module.link|escape:'html':'UTF-8'}" target="_blank" class="desc">{$module.desc|escape:'html':'UTF-8'}</a>
                </div>
            {/if}
        {/foreach}
    </div>
    <br/><br/>
    <p>
        <a target="_blank" href="https://addons.prestashop.com/fr/2_community-developer?contributor=311046">
            <i class="fas fa-external-link-alt"></i> {l s='See all our modules on the Addons Marketplace' mod='ntstats'}
        </a>
    </p>
</div>
