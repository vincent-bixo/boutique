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

use StripeOfficial\Classes\StripeProcessLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PaymentHandler implements PaymentHandlerInterface
{
    private $context;
    /**
     * @var Stripe_official
     */
    private $module;

    public function __construct($context, $module)
    {
        $this->context = $context;
        $this->module = $module;
        $this->module->setStripeAppInformation();
    }

    /**
     * @throws Exception
     */
    public function handlePayment($paymentFlow, $separateAuthAndCapture, $stripePaymentMethodId = null)
    {
        StripeProcessLogger::logInfo('flow ' . json_encode($paymentFlow), 'ElementsFlowHandler');

        if (!$this->isSupportedPaymentFlow($paymentFlow)) {
            throw new Exception("The payment flow: $paymentFlow is not supported.");
        }

        $handler = $this->getPaymentFlowHandler($paymentFlow, $this->context, $this->module, $stripePaymentMethodId);
        StripeProcessLogger::logInfo('handler ' . json_encode($handler), 'ElementsFlowHandler');
        StripeProcessLogger::logInfo('method id ' . json_encode($stripePaymentMethodId), 'ElementsFlowHandler');

        if (!$handler) {
            throw new Exception("The handler for payment flow: $paymentFlow is not found.");
        }

        StripeProcessLogger::logInfo('separate auth and capture payment handler ' . json_encode($separateAuthAndCapture), 'ElementsFlowHandler');

        return $handler->handlePayment($separateAuthAndCapture);
    }

    public function getPaymentFlowHandler($paymentFlow, $context, $module, $stripePaymentMethodId)
    {
        $handler = null;
        StripeProcessLogger::logInfo('payment flow ' . json_encode($paymentFlow), 'PaymentHandler');
        StripeProcessLogger::logInfo('payment method id ' . json_encode($stripePaymentMethodId), 'PaymentHandler');
        $newOrderFlow = !(int) Configuration::get(Stripe_official::ORDER_FLOW);

        switch ($paymentFlow) {
            case Stripe_official::PM_PAYMENT_ELEMENTS:
                $handler = $newOrderFlow ?
                    new ElementsFlowHandlerNew($context, $module, $stripePaymentMethodId) :
                    new ElementsFlowHandler($context, $module, $stripePaymentMethodId)
                ;
                break;
            case Stripe_official::PM_CHECKOUT:
                $handler = $newOrderFlow ?
                    new CheckoutFlowHandlerNew($context, $module, $stripePaymentMethodId) :
                    new CheckoutFlowHandler($context, $module, $stripePaymentMethodId)
                ;
                break;
        }

        return $handler;
    }

    public function isSupportedPaymentFlow($paymentFlow)
    {
        return $paymentFlow && in_array($paymentFlow, Stripe_official::$allowedPaymentFlows);
    }
}
