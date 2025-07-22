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

class StripeOrderConfirmationService
{
    private $context;
    /**
     * @var StripePaymentIntentService
     */
    private $stripePaymentIntentService;

    /**
     * @var PrestashopOrderService
     */
    private $prestashopOrderService;

    /**
     * @var PrestashopBuildOrderService
     */
    private $prestashopBuildOrderService;

    public function __construct($context, $module, $secretKey = null)
    {
        $this->context = $context;
        $secretKey = $secretKey ?: Stripe_official::getSecretKey();
        $this->stripePaymentIntentService = new StripePaymentIntentService($secretKey);
        $this->prestashopOrderService = new PrestashopOrderService($this->context, $module, $secretKey);
        $this->prestashopBuildOrderService = new PrestashopBuildOrderService($this->context, $module, $secretKey);
    }

    public function orderConfirmationNew(string $cartId = null)
    {
        $failUrl = $redirectUrl = $this->context->link->getModuleLink('stripe_official', 'orderFailure', ['cartId' => $cartId], true);

        StripeProcessLogger::logInfo('Order Confirmation Return init: ', 'orderConfirmationReturn', $cartId);
        try {
            if (!$cartId) {
                return $failUrl;
            }

            $stripeIdempotencyKey = StripeIdempotencyKey::getOrCreateIdempotencyKey($cartId);
            if (!$stripeIdempotencyKey->id_payment_intent) {
                return $failUrl;
            }
            $intent = $this->prestashopOrderService->findStripePaymentIntent($stripeIdempotencyKey->id_payment_intent);
            if (!$intent) {
                return $failUrl;
            }

            $psStripePaymentIntent = new StripePaymentIntent();
            $psStripePaymentIntent->findByIdPaymentIntent($stripeIdempotencyKey->id_payment_intent);

            $status = null;
            $lastPaymentError = $intent->last_payment_error ?? null;
            if ($lastPaymentError) {
                $chargeDeclineCode = $lastPaymentError->decline_code ?? $lastPaymentError->code ?? null;
                $status = $psStripePaymentIntent->getStatusFromStripeDeclineCode($chargeDeclineCode);
                StripeProcessLogger::logInfo('Last Payment Error: ' . json_encode($lastPaymentError), 'orderConfirmationReturn', $cartId, $intent->id);

                return $failUrl;
            }
            $status = $status ?? $psStripePaymentIntent->getStatusFromStripePaymentIntentStatus($intent->status);
            if ($psStripePaymentIntent->validateStatusChange($status)) {
                $psStripePaymentIntent->setIdPaymentIntent($intent->id);
                $psStripePaymentIntent->setAmount($intent->amount);
                $psStripePaymentIntent->setStatus($status);
                $psStripePaymentIntent->save();
            }

            $orderId = Order::getIdByCartId($cartId);
            $order = new Order($orderId);
            $orderPayment = $order->getOrderPayments();
            $orderStatus = $psStripePaymentIntent->getPsStatus();
            $chargeId = (isset($intent->charges->data->id) ?
                $intent->charges->data->id :
                (
                    isset($intent->latest_charge) ?
                        $intent->latest_charge :
                        null
                ));

            if (!empty($orderPayment)) {
                $this->prestashopOrderService->setTransactionIdInOrderPayment($chargeId, $order->reference);
            }

            $stripePayment = new StripePayment();
            $stripePayment->getStripePaymentByCart($cartId);
            $this->prestashopOrderService->updatePsStripePayment($intent, $cartId);

            if ((int) Configuration::get(Stripe_official::CAPTURE_WAITING) === (int) $orderStatus) {
                $stripeCapture = new StripeCapture();
                $stripeCapture->id_payment_intent = $intent->id;
                $stripeCapture->id_order = $orderId;
                $stripeCapture->expired = 0;
                $stripeCapture->date_catch = date('Y-m-d H:i:s');
                $stripeCapture->save();
            }

            $order->setCurrentState((int) $orderStatus);
            $this->prestashopOrderService->updatePsOrders($intent, $orderId);
            $redirectUrl = $this->prestashopOrderService->getOrderConfirmationLink($order) ?? $failUrl;

            StripeProcessLogger::logInfo('Order Confirmation Return URL: ' . json_encode($redirectUrl), 'orderConfirmationReturn', $cartId);
        } catch (Exception $e) {
            StripeProcessLogger::logError('Order Confirmation Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 'orderConfirmationReturn', $cartId, $intent->id);
        }

        return $redirectUrl;
    }

    public function orderConfirmationLegacy(string $cartId = null)
    {
        $failUrl = $redirectUrl = $this->context->link->getModuleLink('stripe_official', 'orderFailure', ['cartId' => $cartId], true);

        $intent = null;
        try {
            if (!$cartId) {
                return $failUrl;
            }

            $stripeIdempotencyKey = StripeIdempotencyKey::getOrCreateIdempotencyKey($cartId);
            if (!$stripeIdempotencyKey->id_payment_intent) {
                return $failUrl;
            }
            $intent = $this->prestashopOrderService->findStripePaymentIntent($stripeIdempotencyKey->id_payment_intent);
            if (!$intent) {
                return $failUrl;
            }

            $psStripePaymentIntent = new StripePaymentIntent();
            $psStripePaymentIntent->findByIdPaymentIntent($stripeIdempotencyKey->id_payment_intent);

            $status = null;
            $lastPaymentError = $intent->last_payment_error ?? null;
            if ($lastPaymentError) {
                $chargeDeclineCode = $lastPaymentError->decline_code ?? $lastPaymentError->code ?? null;
                $status = $psStripePaymentIntent->getStatusFromStripeDeclineCode($chargeDeclineCode);
                StripeProcessLogger::logInfo('Last Payment Error: ' . json_encode($lastPaymentError), 'orderConfirmationReturn', $cartId, $intent->id);
            }
            $status = $status ?? $psStripePaymentIntent->getStatusFromStripePaymentIntentStatus($intent->status);
            if ($psStripePaymentIntent->validateStatusChange($status)) {
                $psStripePaymentIntent->setIdPaymentIntent($intent->id);

                if (isset($intent->currency_conversion) && isset($intent->currency_conversion->amount_total)) {
                    $amountInStoreCurrency = $intent->currency_conversion->amount_total / 100;
                } else {
                    $amountInStoreCurrency = $intent->amount / 100;
                }

                $psStripePaymentIntent->setAmount($amountInStoreCurrency);
                $psStripePaymentIntent->setStatus($status);
                $psStripePaymentIntent->save();
            }
            $stripeIdempotencyKeyObject = new StripeIdempotencyKey();
            $stripeIdempotencyKeyObject->updateIdempotencyKey($cartId, $intent);
            $psCart = new Cart($cartId);

            $orderModel = $this->prestashopOrderService->buildOrderModel($psStripePaymentIntent, $intent, $psCart);
            $orderModel = $this->prestashopOrderService->createPsOrder($orderModel);
            StripeProcessLogger::logInfo('Create PrestaShop Order: ' . json_encode($orderModel), 'orderConfirmationReturn', $cartId, $intent->id);

            $this->stripePaymentIntentService->updateStripePaymentIntent($intent->id, ['description' => $orderModel->orderReference]);

            if ((int) Configuration::get(Stripe_official::CAPTURE_WAITING) === (int) $orderModel->status) {
                $stripeCapture = new StripeCapture();
                $stripeCapture->id_payment_intent = $intent->id;
                $stripeCapture->id_order = $orderModel->orderId;
                $stripeCapture->expired = 0;
                $stripeCapture->date_catch = date('Y-m-d H:i:s');
                $stripeCapture->save();
            }

            $this->prestashopOrderService->createPsStripePayment($intent, $orderModel);

            $redirectUrl = $orderModel->order ? $this->prestashopOrderService->getOrderConfirmationLink($orderModel->order) : $failUrl;
        } catch (Exception $e) {
            StripeProcessLogger::logError('Order Confirmation Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 'orderConfirmationReturn', $cartId, $intent ? $intent->id : '');
        }

        return $redirectUrl;
    }
}
