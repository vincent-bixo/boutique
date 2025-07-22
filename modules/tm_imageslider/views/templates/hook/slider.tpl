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
{if $tmhomeslider.slides}
	<div class="flexslider" data-interval="{$tmhomeslider.speed}" data-pause="{$tmhomeslider.pause}">
		
		<div class="loadingdiv spinner"></div>

		{assign var=item value=1}
	
		<ul class="slides" id="owl-demo">
			{foreach from=$tmhomeslider.slides item=slide}
				<li class="slide">
					<a href="{$slide.url}" title="{$slide.legend}">
					<img src="{$slide.image_url}" alt="{$slide.legend}" title="{$slide.title}" />
					</a>
					{if $slide.description }
						<div class="caption-description">
							{$slide.description nofilter}
						</div>
					{/if}					
				</li>
			 {assign var=item value=$item+1}
			{/foreach}
		</ul>
	</div>	
	{/if}	