<?php
/**
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
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../classes/StripeWebhook.php';

function upgrade_module_2_3_2($module)
{
    $context = Context::getContext();

    $installer = new Stripe_officialClasslib\Install\ModuleInstaller($module);
    $installer->registerHooks();

    $webhooksList = StripeWebhook::getWebhookList();

    foreach ($webhooksList as $webhookEndpoint) {
        if ($webhookEndpoint->url == $context->link->getModuleLink('stripe_official', 'webhook', [], true, Configuration::get('PS_LANG_DEFAULT'), Configuration::get('PS_SHOP_DEFAULT'))) {
            $webhookEndpoint->update(
                $webhookEndpoint->id,
                [
                    'enabled_events' => $module::$webhook_events,
                ]
            );
        }
    }

    return true;
}
