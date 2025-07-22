{**
 * Copyright (c) since 2010 Stripe, Inc. (https://stripe.com)
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Stripe <https://support.stripe.com/contact/email>
 * @copyright Since 2010 Stripe, Inc.
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}

{* {extends file=$layout} *}
{extends file='page.tpl'}

{block name='content'}
    <section id="content-hook_order_confirmation" class="card">
        <div class="card-block">
            <div class="row">
                <div class="col-md-12">

                    {block name='order_confirmation_header'}
                      <h3 class="h1 card-title">
                        <i class="material-icons rtl-no-flip done">&#xE876;</i>{l s='Your order is confirmed' d='Shop.Theme.Checkout'}
                      </h3>
                    {/block}

                    <p>
                        <b>{l s='Congratulations, your order has been placed and will be processed soon.' mod='stripe_official'}</b><br/><br/>

                        {l s='An email has been sent to your mail address %email%.' d='Shop.Theme.Checkout' sprintf=['%email%' => $customer.email]}<br/><br/>

                        {if $payment_method == 'oxxo'}
                            {{l s='Your can see your OXXO voucher [a @href1@]here[/a].' mod='stripe_official'}|stripelreplace:['@href1@' => {{$voucher_url|escape:'htmlall'}}, '@target@' => {'target="blank"'}] nofilter}<br/><br/>
                        {/if}

                        {l s='We appreciate your business.' mod='stripe_official'}
                    </p>

                </div>
            </div>
        </div>
    </section>
{/block}