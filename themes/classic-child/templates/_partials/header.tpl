{**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{block name='header_banner'}
  <div class="header-banner">
    {hook h='displayBanner'}
  </div>
{/block}

{block name='header_nav'}
  <nav class="header-nav">
    <div class="container">
      <div class="row">
        <div class="hidden-sm-down">
          <div class="col-md-7 col-xs-12">
            {hook h='displayNav1'}


            <div class="ph-social-link-block displayBanner button_size_large button_border_rounded button_type_flat_icon ">
              {*<h4 class="ph_social_link_title">Suivez-nous</h4>*}
              <ul>
                {literal}
                  <li class="ph_social_item facebook">
                    <a title="Facebook" href="https://www.facebook.com/editions.mariedenazareth" target="_blank">
                      <i><svg width="1792" height="1792" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1343 12v264h-157q-86 0-116 36t-30 108v189h293l-39 296h-254v759h-306v-759h-255v-296h255v-218q0-186 104-288.5t277-102.5q147 0 228 12z"></path></svg></i>
                      <span class="tooltip_title">Facebook</span>
                    </a>
                  </li>
                  <li class="ph_social_item instagram">
                    <a title="Instagram" href="https://www.instagram.com/editions.mariedenazareth/" target="_blank">
                      <i><svg id="Layer_1" style="enable-background:new 0 0 512 512;" version="1.1" viewBox="0 0 512 512" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><style type="text/css">
	.st0{fill:url(#SVGID_1_);}
	.st1{fill:#FFFFFF;}
</style><g><radialGradient cx="225.4737" cy="222.8046" gradientTransform="matrix(14.2175 0 0 14.2171 -3055.7039 -2615.9958)" gradientUnits="userSpaceOnUse" id="SVGID_1_" r="47.7212"><stop offset="9.693880e-02" style="stop-color:#FFD87A"></stop><stop offset="0.1426" style="stop-color:#FCCE78"></stop><stop offset="0.2263" style="stop-color:#F5B471"></stop><stop offset="0.3378" style="stop-color:#EB8D65"></stop><stop offset="0.449" style="stop-color:#E36058"></stop><stop offset="0.6786" style="stop-color:#CD3694"></stop><stop offset="1" style="stop-color:#6668B0"></stop></radialGradient><path class="st0" d="M512,395.1c0,64.6-52.3,116.9-116.9,116.9H116.9C52.3,512,0,459.7,0,395.1V117C0,52.4,52.4,0,117,0h276.3   C458.9,0,512,53.1,512,118.7V395.1z"></path><g><path class="st1" d="M327.2,70.6H184.8c-63.1,0-114.3,51.2-114.3,114.3v142.3c0,63.1,51.1,114.2,114.3,114.2h142.3    c63.1,0,114.2-51.1,114.2-114.2V184.9C441.4,121.7,390.3,70.6,327.2,70.6z M405.8,313.5c0,51-41.3,92.3-92.3,92.3h-115    c-51,0-92.3-41.3-92.3-92.3v-115c0-51,41.3-92.3,92.3-92.3h115c51,0,92.3,41.4,92.3,92.3V313.5z"></path><path class="st1" d="M261,159c-54,0-97.7,43.7-97.7,97.7c0,53.9,43.7,97.7,97.7,97.7c53.9,0,97.7-43.7,97.7-97.7    C358.6,202.7,314.9,159,261,159z M261,315.4c-32.5,0-58.8-26.3-58.8-58.8c0-32.5,26.3-58.8,58.8-58.8c32.4,0,58.8,26.3,58.8,58.8    C319.7,289.1,293.4,315.4,261,315.4z"></path><path class="st1" d="M376.7,157.5c0,13.7-11.1,24.8-24.8,24.8c-13.7,0-24.8-11.1-24.8-24.8c0-13.7,11.1-24.9,24.8-24.9    C365.6,132.6,376.7,143.7,376.7,157.5z"></path></g></g></svg></i>
                      <span class="tooltip_title">Instagram</span>
                    </a>
                  </li>
                {/literal}
              </ul>
            </div>

          </div>
          <div class="col-md-5 right-nav">
              {hook h='displayNav2'}
          </div>
        </div>
        <div class="hidden-md-up text-sm-center mobile">
          <div class="float-xs-left" id="menu-icon">
            <i class="material-icons d-inline">&#xE5D2;</i>
          </div>
          <div class="float-xs-right" id="_mobile_cart"></div>
          <div class="float-xs-right" id="_mobile_user_info"></div>
          <div class="top-logo" id="_mobile_logo"></div>
          <div class="clearfix"></div>
        </div>
      </div>
    </div>
  </nav>
{/block}

{block name='header_top'}
  <div class="header-top">
    <div class="container">
      <div class="row">
        <div class="col-md-9 hidden-sm-down" id="_desktop_logo">
            {if $page.page_name == 'index'}
                <div>
                  <a href="{$urls.base_url}">
                    <img class="logo img-responsive" src="{$shop.logo}" alt="{$shop.name}">
                  </a>
                </div>
                <div class="titre">
                  <h1>Librairie catholique et&nbsp;missionnaire</h1>
                </div>
            {else}
              <div>
                <a href="{$urls.base_url}">
                  <img class="logo img-responsive" src="{$shop.logo}" alt="{$shop.name}">
                </a>
              </div>
              <div class="titre">Librairie catholique et&nbsp;missionnaire</div>
            {/if}

        </div>
        <div class="col-md-3 col-sm-12 position-static">
          {hook h='displayTop'}
          <div class="clearfix"></div>
        </div>
      </div>
      <div id="mobile_top_menu_wrapper" class="row hidden-md-up" style="display:none;">
        <div class="js-top-menu mobile" id="_mobile_top_menu"></div>
        <div class="js-top-menu-bottom">
          <div id="_mobile_currency_selector"></div>
          <div id="_mobile_language_selector"></div>
          <div id="_mobile_contact_link"></div>
        </div>
      </div>
    </div>
  </div>
  {hook h='displayNavFullWidth'}
{/block}
