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

use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use StripeOfficial\Classes\services\PrestashopTranslationService;
use StripeOfficial\Classes\StripeProcessLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestashopOrderService
{
    private $context;
    /**
     * @var Stripe_official
     */
    private $module;

    private $psStatuses;
    /**
     * @var StripePaymentMethodService
     */
    private $stripePaymentMethodService;
    /**
     * @var StripePaymentIntentService
     */
    private $stripePaymentIntentService;
    /**
     * @var StripeCheckoutSessionService
     */
    private $stripeCheckoutSessionService;

    /**
     * @var PrestashopTranslationService
     */
    private $translationService;

    public function __construct($context, $module, $secretKey)
    {
        $this->context = $context;
        $this->module = $module;
        $secretKey = $secretKey ?: Stripe_official::getSecretKey();
        $this->stripePaymentMethodService = new StripePaymentMethodService($secretKey);
        $this->stripePaymentIntentService = new StripePaymentIntentService($secretKey);
        $this->stripeCheckoutSessionService = new StripeCheckoutSessionService($secretKey);
        $this->translationService = new PrestashopTranslationService($module, 'Modules.Stripeofficial.PrestashopOrderService', 'PrestashopOrderService');
    }

    public function createPsOrder(OrderModel $orderModel): OrderModel
    {
        $orderId = Order::getIdByCartId($orderModel->cartId);
        $order = null;
        if ($orderId) {
            $order = new Order($orderId);
        }

        StripeProcessLogger::logInfo('createPsOrder: - $orderId ' . json_encode($orderId), 'PrestashopOrderService', $orderModel->cartId);
        if (!$order && (int) $orderModel->status) {
            try {
                $this->module->validateOrder(
                    $orderModel->cartId,
                    (int) $orderModel->status,
                    (float) $orderModel->amount,
                    sprintf(
                        $this->translationService->translate('%s via Stripe'),
                        Tools::ucfirst($orderModel->paymentMethodType)
                    ),
                    $orderModel->message,
                    null,
                    null,
                    false,
                    $orderModel->secureKey,
                    $orderModel->shop,
                    $orderModel->orderReference
                );

                $orderId = Order::getIdByCartId($orderModel->cartId);
                if ($orderId) {
                    $order = new Order($orderId);
                }
                StripeProcessLogger::logInfo('After Validate PrestaShop Order: ' . json_encode($order), 'PrestashopOrderService', $orderModel->cartId);
            } catch (Exception $e) {
                StripeProcessLogger::logError('Create PrestaShop Order Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 'PrestashopOrderService', $orderModel->cartId);
            }
        }
        StripeProcessLogger::logInfo('After Create PrestaShop Order - OrderID: ' . $orderId, 'PrestashopOrderService', $orderModel->cartId);
        $orderId = $orderId ?: null;
        $order = $order ?: null;
        $reference = $order ? $this->context->shop->name . ' / Reference: ' . $order->reference . ' / Order: ' . $orderId : null;
        $orderModel->orderId = $orderId;
        $orderModel->order = $order;
        $orderModel->orderReference = $reference;

        return $orderModel;
    }

    public function getOrderConfirmationLink(Order $order)
    {
        $returnUrl = '';
        try {
            $returnUrl = $this->context->link->getPageLink(
                'order-confirmation',
                true,
                null,
                [
                    'id_cart' => $order->id_cart ?: 0,
                    'id_module' => (int) $this->module->id,
                    'id_order' => $order->id,
                    'key' => $order->secure_key,
                ]
            );
        } catch (Exception $e) {
            StripeProcessLogger::logError('Get Order Confirmation Link Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 'PrestashopOrderService', $order->id_cart);
        }

        return $returnUrl;
    }

    public function buildOrderModel(
        StripePaymentIntent $psStripePaymentIntent = null,
        PaymentIntent $stripePaymentIntent = null,
        Cart $cart = null,
        CartContextModel $cartContextModel = null,
        string $stripePaymentMethodId = null
    ): OrderModel {
        $stripePaymentIntentId = $stripePaymentIntent ? $stripePaymentIntent->id : null;
        $stripeDetails = $stripePaymentIntent ?: $cartContextModel;
        $message = 'Stripe Transaction ID: ' . $stripePaymentIntentId;
        $paymentMethodId = $stripePaymentIntent ? $stripePaymentIntent->payment_method : null;
        $currency = $stripePaymentIntent ? $stripePaymentIntent->currency : $cartContextModel->currencyIsoCode;
        $amountType = (float) $stripeDetails->amount;

        $amount = Stripe_official::isZeroDecimalCurrency($currency) ?
            $amountType :
            number_format($amountType / 100, 2, '.', '')
        ;

        $stripePaymentMethod = $this->stripePaymentMethodService->getStripePaymentMethod($paymentMethodId) ?: $this->stripePaymentMethodService->getStripePaymentMethod($stripePaymentMethodId);
        $paymentMethodType = $this->stripePaymentMethodService->getStripePaymentMethodTypeByPaymentIntent($stripePaymentIntent) ?: $this->stripePaymentMethodService->getStripePaymentMethodType($stripePaymentMethod);

        $orderStatus = $psStripePaymentIntent ? $psStripePaymentIntent->getPsStatus() : Configuration::get(Stripe_official::PAYMENT_WAITING);

        $orderModel = new OrderModel();
        $orderModel->cart = $cart ?: ($this->context->cart ?: null);
        $orderModel->cartId = $cart ? $cart->id : ($this->context->cart ? $this->context->cart->id : null);
        $orderModel->secureKey = $cart ? $cart->secure_key : ($this->context->cart ? $this->context->cart->secure_key : null);
        $orderModel->shop = $this->context->shop;
        $orderModel->message = $message;
        $orderModel->amount = $amount;
        $orderModel->status = $orderStatus;
        $orderModel->paymentMethod = $stripePaymentMethod;
        $orderModel->paymentMethodType = $paymentMethodType;

        return $orderModel;
    }

    public function createPsStripePayment(PaymentIntent $stripePaymentIntent, OrderModel $orderModel): StripePayment
    {
        $stripePaymentMethodBillingDetails = $this->stripePaymentMethodService->getBillingDetailsFromStripePaymentMethod($orderModel->paymentMethod);
        $paymentOwnerName = isset($stripePaymentMethodBillingDetails->name) ? $stripePaymentMethodBillingDetails->name : '';

        $chargeId = (isset($stripePaymentIntent->charges->data->id) ?
            $stripePaymentIntent->charges->data->id :
            (
                isset($stripePaymentIntent->latest_charge) ?
                $stripePaymentIntent->latest_charge :
                null
            ));

        $stripePayment = new StripePayment();
        $stripePayment->setIdStripe($chargeId);
        $stripePayment->setIdPaymentIntent($stripePaymentIntent->id);
        $stripePayment->setName($paymentOwnerName);
        $stripePayment->setIdCart((int) $orderModel->cartId);

        $cardType = $orderModel->paymentMethodType;
        if (isset($orderModel->paymentMethod->card)) {
            $cardType = $orderModel->paymentMethod->card->brand;
        }

        $stripePayment->setType(Tools::strtolower($cardType));
        $stripePayment->setAmount($orderModel->amount);
        $stripePayment->setRefund(0);
        $stripePayment->setCurrency($stripePaymentIntent->currency);
        $stripePayment->setResult(1);
        $stripePayment->setState((int) Configuration::get('STRIPE_MODE'));
        $voucherUrl = isset($stripePaymentIntent->next_action->oxxo_display_details->hosted_voucher_url) ?
            $stripePaymentIntent->next_action->oxxo_display_details->hosted_voucher_url :
            null
        ;
        $voucherExpire = isset($stripePaymentIntent->next_action->oxxo_display_details->expires_after) ?
            date('Y-m-d H:i:s', $stripePaymentIntent->next_action->oxxo_display_details->expires_after) :
            null
        ;
        if ($voucherUrl && $voucherExpire) {
            $stripePayment->setVoucherUrl($voucherUrl);
            $stripePayment->setVoucherExpire($voucherExpire);
        }

        $stripePayment->setDateAdd(date('Y-m-d H:i:s'));
        $stripePayment->save();

        return $stripePayment;
    }

    public function findStripePaymentIntent(string $paymentIntentId = null)
    {
        if (!$paymentIntentId) {
            return null;
        }

        $intent = $this->stripePaymentIntentService->getStripePaymentIntent($paymentIntentId);
        if (!$intent) {
            $session = $this->stripeCheckoutSessionService->getStripeCheckoutSession($paymentIntentId);
            if ($session instanceof Session) {
                $intent = $this->stripePaymentIntentService->getStripePaymentIntent($session->payment_intent);
                if (isset($session->currency_conversion) && isset($session->currency_conversion->amount_total)) {
                    $intent->amount = $session->currency_conversion->amount_total;
                }
                $idempotencyKey = new StripeIdempotencyKey();
                $idempotencyKey->updateIdempotencyKey($session->metadata->id_cart, $intent);
            }
        }

        return $intent;
    }

    public static function setTransactionIdInOrderPayment(string $chargeId, string $orderReference)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'order_payment`
        SET `transaction_id` = "' . $chargeId . '"
        WHERE  `order_reference` = "' . $orderReference . '"';
        Db::getInstance()->execute($sql);
        StripeProcessLogger::logInfo('PrestaShop order : ' . $orderReference . ' was updated with Transaction ID: ' . $chargeId, 'PrestashopOrderService');
    }

    public function createPsStripePaymentFromSession(Session $stripeCheckoutSession, OrderModel $orderModel): StripePayment
    {
        StripeProcessLogger::logInfo('Checkout flow $stripePaymentIntent ' . json_encode($stripeCheckoutSession), 'PrestashopOrderService');

        $stripePayment = new StripePayment();
        $stripePayment->setIdPaymentIntent($stripeCheckoutSession->id);
        $stripePayment->setIdCart((int) $orderModel->cartId);
        $stripePayment->setAmount($orderModel->amount);
        $stripePayment->setRefund(0);
        $stripePayment->setCurrency($stripeCheckoutSession->currency);
        $stripePayment->setResult(1);
        $stripePayment->setState((int) Configuration::get('STRIPE_MODE'));
        $stripePayment->setDateAdd(date('Y-m-d H:i:s'));
        $stripePayment->save();

        return $stripePayment;
    }

    public function updatePsStripePayment(PaymentIntent $stripePaymentIntent, int $cartId)
    {
        StripeProcessLogger::logInfo('Intent : ' . json_encode($stripePaymentIntent) . ' was updated.', 'PrestashopOrderService');
        $paymentOwnerName = $cardType = $chargeId = '';
        if (isset($stripePaymentIntent->charges->data[0])) {
            $paymentOwnerName = ($stripePaymentIntent->charges->data[0]->billing_details->name ?? '');
            $cardType = $stripePaymentIntent->charges->data[0]->payment_method_details->card->brand ?? $stripePaymentIntent->charges->data[0]->payment_method_details->type ?? '';
            $chargeId = (isset($stripePaymentIntent->charges->data[0]->id) ?
                $stripePaymentIntent->charges->data[0]->id :
                (
                    isset($stripePaymentIntent->latest_charge) ?
                        $stripePaymentIntent->latest_charge :
                        null
                ));
        }

        $voucherUrl = $stripePaymentIntent->next_action->oxxo_display_details->hosted_voucher_url ?? null;
        $voucherExpire = isset($stripePaymentIntent->next_action->oxxo_display_details->expires_after) ?
            date('Y-m-d H:i:s', $stripePaymentIntent->next_action->oxxo_display_details->expires_after) :
            null;

        $sql = 'UPDATE `' . _DB_PREFIX_ . 'stripe_payment`
        SET `id_stripe` = "' . $chargeId . '", `id_payment_intent` = "' . $stripePaymentIntent->id . '", `name` = "' . $paymentOwnerName . '", `type` = "' . Tools::strtolower($cardType) . '"';

        if ($voucherUrl && $voucherExpire) {
            $sql .= '`voucher_url` = "' . $voucherUrl . '", `voucher_expire` = "' . $voucherExpire . '"';
        }
        $sql .= ' WHERE  `id_cart` = "' . $cartId . '"';
        StripeProcessLogger::logInfo('Update PS Stripe Payment', 'PrestashopOrderService', $cartId);

        Db::getInstance()->execute($sql);
    }

    public function updatePsOrders(PaymentIntent $stripePaymentIntent, int $orderId)
    {
        $paymentMethodType = $this->stripePaymentMethodService->getStripePaymentMethodTypeByPaymentIntent($stripePaymentIntent);
        $payment = sprintf(
            $this->translationService->translate('%s via Stripe'),
            Tools::ucfirst($paymentMethodType)
        );

        $sql = 'UPDATE `' . _DB_PREFIX_ . 'orders` SET `payment` = "' . $payment . '" WHERE `id_order` = ' . $orderId;
        Db::getInstance()->execute($sql);
        StripeProcessLogger::logInfo('Update payment from PS Orders id: ' . $orderId . ' With ' . $payment, 'PrestashopOrderService', $stripePaymentIntent->metadata->id_cart);
    }
}
