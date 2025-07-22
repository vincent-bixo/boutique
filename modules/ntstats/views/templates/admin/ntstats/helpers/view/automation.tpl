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
    <i class="far fa-clock"></i>
    &nbsp;{l s='Automation' mod='ntstats'}
</div>
<div>
    <div {if !$activate_2nt_automation}class="deactivate"{/if}>
        {if !$activate_2nt_automation}
            <p class="error alert alert-danger">
                {l s='This option is not available for local websites.' mod='ntstats'}
            </p>
        {/if}
        <p>
            <label for="automation_2nt" id="automation_2nt">
                <i class="far fa-question-circle label-tooltip" data-toggle="tooltip" data-placement="right" data-html="true"
                   title="{l s='This automation will launch a daily email stock alert.' mod='ntstats'}"
                ></i>
                {l s='Automation by %1$s at' sprintf='2N Technologies' mod='ntstats'}
            </label>
            <select id="automation_2nt_hours" name="automation_2nt_hours">
                {for $i=0; $i<24; $i++}
                    {if $i < 10}
                        {assign var='hours' value="0$i"}
                    {else}
                        {assign var='hours' value=$i}
                    {/if}
                    <option {if $config.automation_2nt_hours == $i}selected="selected"{/if} value="{$i|intval}">{$hours|escape:'html':'UTF-8'}</option>
                {/for}
            </select>
            H
            <select id="automation_2nt_minutes" name="automation_2nt_minutes">
                {for $i=0; $i<60; $i++}
                    {if $i < 10}
                        {assign var='minutes' value="0$i"}
                    {else}
                        {assign var='minutes' value=$i}
                    {/if}
                    <option {if $config.automation_2nt_minutes == $i}selected="selected"{/if} value="{$i|intval}">{$minutes|escape:'html':'UTF-8'}</option>
                {/for}
            </select>
                {l s='Current server time:' mod='ntstats'}
                <span id="current_hour">{$current_hour|escape:'html':'UTF-8'}</span>
                <span id="time_zone">({$time_zone|escape:'html':'UTF-8'})</span>
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="automation_2nt" id="automation_2nt_on" value="1" {if $config.automation_2nt}checked="checked"{/if}/>
                <label class="t" for="automation_2nt_on">
                    {l s='Yes' mod='ntstats'}
                </label>
                <input type="radio" name="automation_2nt" id="automation_2nt_off" value="0"  {if !$config.automation_2nt}checked="checked"{/if}/>
                <label class="t" for="automation_2nt_off">
                    {l s='No' mod='ntstats'}
                </label>
                <a class="slide-button btn"></a>
            </span>
        </p>
        <p class="alert alert-warning warn">
            {l s='The automation service by %1$s only automatically send you an email stock alert at the specified time. Your data is not sent to the %2$s server. It\'s always your server that create the alert and send it to you.' sprintf=['2N Technologies', '2N Technologies'] mod='ntstats'}
        </p>
    </div>
    {if $shop_url_changed}
        <p>
            <button type="button" class="btn btn-default" id="nt_shop_url_changed" name="nt_shop_url_changed">
                <i class="fas fa-sync-alt"></i>
                {l s='Update domain' mod='ntstats'}
            </button>
        </p>
    {/if}
    <p>
        <button type="button" class="btn btn-default" id="nt_advanced_automation" name="nt_advanced_automation">
            <i class="fas fa-sliders-h"></i>
            {l s='Advanced' mod='ntstats'}
        </button>
    </p>
    <div id="nt_advanced_automation_diplay">
        <div class="panel">
            <div class="panel-heading">
                <i class="far fa-clock"></i>&nbsp;{l s='Advanced automation - Cron.' mod='ntstats'}
            </div>
            <p>
                {l s='If you want to send the email stock alert automatically yourself, you can create a CRON on your server.' mod='ntstats'} <br/>
                {l s='The way to do this depends on your hosting.' mod='ntstats'} <br/>
                {l s='To simplify the task, you will find below several usual techniques.' mod='ntstats'} <br/>
            </p>

            <div id="cron_block">
                <ul id="nt_advanced_automation_tab">
                    <li id="nt_aat_0" class="active">{l s='WGet' mod='ntstats'}</li>
                    <li id="nt_aat_1">{l s='Path' mod='ntstats'}</li>
                    <li id="nt_aat_2">{l s='URL' mod='ntstats'}</li>
                    <li id="nt_aat_3">{l s='cURL' mod='ntstats'}</li>
                    <li id="nt_aat_4">{l s='PHP Script' mod='ntstats'}</li>
                </ul>
                <div class="clear"></div>

                {assign var='email_alert_url' value="`$url_cron`/email_alert_`$config.id_shop_group`_`$config.id_shop`_`$secure_key`.php"}
                {assign var='email_alert_curl_path' value="`$path_cron`/email_alert_curl_`$config.id_shop_group`_`$config.id_shop`_`$secure_key`.php"}

                <div class="nt_aat" id="nt_aat_0_content">
                    <p>{l s='WGet works with most web hosts.' mod='ntstats'}</p>
                    <div id="cron_wget">
                        <p>
                            <span class="cron">
                                wget -O - -q -t 1 --max-redirect=10000 "{$email_alert_url|escape:'html':'UTF-8'}" >/dev/null 2>&1
                            </span>
                        </p>
                    </div>
                </div>
                <div class="nt_aat" id="nt_aat_1_content">
                    <p>{l s='Direct path to send the alert. Useful for hosters who only allow CRONs through PHP CLI' mod='ntstats'}</p>
                    <div id="cron_path">
                        <p>
                            <span class="cron">{$email_alert_curl_path|escape:'html':'UTF-8'}</span>
                        </p>
                    </div>
                </div>
                <div class="nt_aat" id="nt_aat_2_content">
                    <p>{l s='Direct URL to send the alert. Useful for services sites of Web Cron.' mod='ntstats'}</p>
                    <div id="cron_url">
                        <p>
                            <a class="cron" target="_blank" href="{$email_alert_url|escape:'html':'UTF-8'}">
                                {$email_alert_url|escape:'html':'UTF-8'}
                            </a>
                        </p>
                    </div>
                </div>
                <div class="nt_aat" id="nt_aat_3_content">
                    <p>{l s='CURL works with some web hosts.' mod='ntstats'}</p>
                    <div id="cron_curl">
                        <p>
                            <span class="cron">
                                curl -L --max-redirs 10000 -s "{$email_alert_url|escape:'html':'UTF-8'}" >/dev/null 2>&1
                            </span>
                        </p>
                    </div>
                </div>
                <div class="nt_aat" id="nt_aat_4_content">
                    <p>{l s='You can directly integrate into your PHP scripts the alert sending' mod='ntstats'}</p>
                    <div id="cron_php_script">
                        <div>
                            <pre class="cron">
&lsaquo;?php
    $curl_handle=curl_init();
    curl_setopt($curl_handle,CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl_handle,CURLOPT_MAXREDIRS, 10000);
    curl_setopt($curl_handle, CURLOPT_URL, '{$email_alert_url|escape:'html':'UTF-8'}');
    $result = curl_exec($curl_handle);
    curl_close($curl_handle);
    if (empty($result))
        echo 'An error occured during email alert';
    else
        echo 'Email alert sent';
                            </pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <p>
                <label for="automation_2nt_ip">
                    {l s='Automation by %1$s IP authorization. Automation by %2$s requires IP to be authorized to start automation if maintenance mode is enabled (default IPv4 and IPv6):' sprintf=['2N Technologies', '2N Technologies'] mod='ntstats'}
                </label>
                <select name="automation_2nt_ip" id="automation_2nt_ip">
                    <option value="0" {if $config.automation_2nt_ip == 0}selected="selected"{/if}>
                        {l s='Authorize IPv4 and IPv6' mod='ntstats'}
                    </option>
                    <option value="1" {if $config.automation_2nt_ip == 1}selected="selected"{/if}>
                        {l s='Authorize only IPv4' mod='ntstats'}
                    </option>
                    <option value="2" {if $config.automation_2nt_ip == 2}selected="selected"{/if}>
                        {l s='Authorize only IPv6' mod='ntstats'}
                    </option>
                    <option value="3" {if $config.automation_2nt_ip == 3}selected="selected"{/if}>
                        {l s='Authorize neither IPv4 nor IPv6' mod='ntstats'}
                    </option>
                </select>
            </p>
        </div>
    </div>
    <div class="panel-footer">
        <button id="nt_save_automation_btn" class="btn btn-default pull-right">
            <i class="far fa-save process_icon"></i> {l s='Save' mod='ntstats'}
        </button>
    </div>
</div>