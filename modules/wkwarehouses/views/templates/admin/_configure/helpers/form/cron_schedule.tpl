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
<div class="frequency_description">
  <div class="description_text">{l s='At 00:00 on every day.' mod='wkwarehouses'}</div>
  <div class="minutes">
    <div class="value">
      0
    </div>
    <div class="description">{l s='Minutes' mod='wkwarehouses'}</div>
  </div>
  <div class="hours">
    <div class="value">
      0
    </div>
    <div class="description">{l s='Hours' mod='wkwarehouses'}</div>
  </div>
  <div class="day_of_month">
    <div class="value">
      *
    </div>
    <div class="description">{l s='Day of month' mod='wkwarehouses'}</div>
  </div>
  <div class="month">
    <div class="value">
      *
    </div>
    <div class="description">{l s='Month' mod='wkwarehouses'}</div>
  </div>
  <div class="day_of_week">
    <div class="value">
      *
    </div>
    <div class="description">{l s='Day of week' mod='wkwarehouses'}</div>
  </div>
</div>

<div class="frequency_info_block">
  <div class="title">{l s='Crontab commands' mod='wkwarehouses'}</div>
  <div class="row_info">
    <span class="command">*/5 * * * *</span>
    <span class="example">{l s='at every 5th minute' mod='wkwarehouses'}</span>
  </div>
  <div class="row_info">
    <span class="command">30 4 1 * 0,6</span>
    <span class="example">{l s='At 4:30 on the 1st day of every month, plus on Sun and Sat' mod='wkwarehouses'}</span>
  </div>
  <div class="row_info">
    <span class="command">*</span>
    <span class="example">{l s='any value' mod='wkwarehouses'}</span>
  </div>
  <div class="row_info">
    <span class="command">,</span>
    <span class="example">{l s='value list separator' mod='wkwarehouses'}</span>
  </div>
  <div class="row_info">
    <span class="command">-</span>
    <span class="example">{l s='range of values' mod='wkwarehouses'}</span>
  </div>
  <div class="row_info">
    <span class="command">/</span>
    <span class="example">{l s='step values' mod='wkwarehouses'}</span>
  </div>
  <div class="title">{l s='Examples (Copy / paste to the field above to apply)' mod='wkwarehouses'}</div>
  <div class="row_info">
    <span class="command">{l s='Every hour' mod='wkwarehouses'}:</span>
    <span class="example">0 * * * *</span>
  </div>
  <div class="row_info">
    <span class="command">{l s='Daily (Midnight)' mod='wkwarehouses'}:</span>
    <span class="example">0 0 * * *</span>
  </div>
  <div class="row_info">
    <span class="command">{l s='Weekly (Sunday)' mod='wkwarehouses'}:</span>
    <span class="example">0 0 * * 0</span>
  </div>
  <div class="row_info">
    <span class="command">{l s='Monthly (first)' mod='wkwarehouses'}:</span>
    <span class="example">0 0 1 * *</span>
  </div>
  <div class="row_info">
    <span class="command">{l s='Yearly (January 1)' mod='wkwarehouses'}:</span>
    <span class="example">0 0 1 1 *</span>
  </div>
</div>
