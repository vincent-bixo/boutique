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

<form class="stripe-payment-form save_card" action="">
    {if $prestashop_version == '1.7'}
        <input type="hidden" name="stripe-payment-method" value="card" data-id_payment_method="{$id_payment_method|escape:'htmlall':'UTF-8'}">
    {else}
        <p>{l s='Pay with Stripe:' mod='stripe_official'} {$brand|escape:'htmlall':'UTF-8'} **** **** **** {$last4|escape:'htmlall':'UTF-8'}</p>
        <button class="stripe-submit-button" data-method="card" data-id_payment_method="{$id_payment_method|escape:'htmlall':'UTF-8'}">{l s='Buy now' mod='stripe_official'}</button>
    {/if}
</form>
