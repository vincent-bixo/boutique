{**
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if $tmleftbanner.slides}
	<div id="tmleftbanner" class="block">
		<ul class="hidden-md-down">
			{foreach from=$tmleftbanner.slides item=slide}
				<li class="slide tmleftbanner-container">
					<a href="{$slide.url}" title="{$slide.title}">
						<img src="{$slide.image_url}" alt="{$slide.title}" title="{$slide.title}" />
					</a>				
				</li>
			{/foreach}
		</ul>
	<div class="block hidden-lg-up">
  <h4 class="block_title hidden-lg-up" data-target="#block_banner1_toggle" data-toggle="collapse">{l s='left banner' d='Shop.Theme'}
    <span class="pull-xs-right">
      <span class="navbar-toggler collapse-icons">
      <i class="material-icons add">&#xE147;</i>
      <i class="material-icons remove">&#xE15c;</i>
      </span>
    </span>
  </h4>

  		 <div class="col-md-12 col-xs-12 block_content collapse" id="block_banner1_toggle">

		<ul>
			{foreach from=$tmleftbanner.slides item=slide}
				<li class="slide tmleftbanner-container">
					<a href="{$slide.url}" title="{$slide.title}">
						<img src="{$slide.image_url}" alt="{$slide.title}" title="{$slide.title}" />
					</a>				
				</li>
			{/foreach}
		</ul>
	</div>			
	</div>		
	</div>	
{/if}