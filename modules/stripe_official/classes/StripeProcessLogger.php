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

namespace StripeOfficial\Classes;

if (!defined('_PS_VERSION_')) {
    exit;
}

class StripeProcessLogger
{
    public static function logInfo($infoMessage, $logName, $cartId = '', $paymentIntentId = '')
    {
        \PrestaShopLogger::addLog(self::getFormattedMessageLogs($infoMessage, $paymentIntentId), 1, null, $logName, $cartId, true);
    }

    public static function logError($errorMessage, $logName, $cartId = '', $paymentIntentId = '')
    {
        \PrestaShopLogger::addLog(self::getFormattedMessageLogs($errorMessage, $paymentIntentId), 3, null, $logName, $cartId, true);
    }

    public static function getFormattedMessageLogs($messageLog, $paymentIntent = '')
    {
        $message = 'stripe_official - ';
        $message .= $paymentIntent ? 'PaymentIntent: ' . $paymentIntent . '; ' : '';
        $message .= $messageLog;

        return $message;
    }
}
