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

use Stripe\Exception\ApiErrorException;
use StripeOfficial\Classes\StripeProcessLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class ElementsFlowHandlerNew implements FlowHandlerInterface
{
    const ALIPAY_REDIRECT = 'alipay';
    const NORMAL_REDIRECT = 'normal';
    const SPECIAL_REDIRECT = 'special';

    /**
     * @var Context
     */
    private $context;
    /**
     * @var Module
     */
    private $module;
    /**
     * @var StripePaymentIntentService
     */
    private $stripePaymentIntentService;
    /**
     * @var string|null
     */
    private $stripePaymentMethodId;
    /**
     * @var PrestashopBuildOrderService
     */
    private $prestashopBuildOrderService;
    /**
     * @var PrestashopOrderService
     */
    private $prestashopOrderService;

    /**
     * @param Context $context
     * @param Stripe_official $module
     * @param string|null $secretKey
     * @param string|null $stripePaymentMethodId
     */
    public function __construct($context, $module, $stripePaymentMethodId = null, $secretKey = null)
    {
        $this->context = $context;
        $this->module = $module;
        $secretKey = $secretKey ?: Stripe_official::getSecretKey();
        $this->stripePaymentIntentService = new StripePaymentIntentService($secretKey);
        $this->stripePaymentMethodId = $stripePaymentMethodId;
        $this->module->setStripeAppInformation();
        $this->prestashopBuildOrderService = new PrestashopBuildOrderService($this->context, $this->module, $secretKey);
        $this->prestashopOrderService = new PrestashopOrderService($this->context, $this->module, $secretKey);
    }

    /**
     * @param bool $separateAuthAndCapture
     *
     * @return string|null
     *
     * @throws ApiErrorException
     */
    public function handlePayment($separateAuthAndCapture)
    {
        return $this->getPaymentElementUrl($separateAuthAndCapture);
    }

    /**
     * @param bool $separateAuthAndCapture
     * @param string $paymentMethodId
     *
     * @return string|null
     *
     * @throws ApiErrorException
     */
    public function getPaymentElementUrl($separateAuthAndCapture)
    {
        $cartContextModel = CartContextModel::getFromContext($this->context);

        $failUrl = $this->context->link->getModuleLink('stripe_official', 'orderFailure', ['cartId' => $cartContextModel->cartId], true);

        $cartId = $cartContextModel->cartId;

        $stripePaymentIntent = $this->stripePaymentIntentService->createPaymentIntent($cartContextModel, $separateAuthAndCapture);

        if (!$stripePaymentIntent) {
            StripeProcessLogger::logInfo('Payment Intent does not exists ' . json_encode($stripePaymentIntent), 'ElementsFlowHandler', $cartContextModel->cartId);

            return $failUrl;
        }

        $psStripePaymentIntent = new StripePaymentIntent();
        $psStripePaymentIntent->findByIdPaymentIntent($stripePaymentIntent->id);
        $cart = new Cart($cartId);
        $orderModel = $this->prestashopBuildOrderService->buildAndCreatePrestashopOrder($psStripePaymentIntent, $stripePaymentIntent, $cartContextModel, $cart, $this->stripePaymentMethodId);

        if (!$orderModel->orderReference || !$orderModel->orderId) {
            StripeProcessLogger::logInfo('Order was not created in PrestaShop ' . json_encode($orderModel), 'ElementsFlowHandler', $cartContextModel->cartId);

            return $failUrl;
        }

        $redirectUrl = $this->context->link->getModuleLink('stripe_official', 'orderConfirmationReturn', ['cartId' => $cartContextModel->cartId], true);

        $confirmedPaymentIntent = $this->stripePaymentIntentService->confirmPaymentIntent($stripePaymentIntent, $this->stripePaymentMethodId, $redirectUrl);
        $this->prestashopOrderService->createPsStripePayment($stripePaymentIntent, $orderModel);
        $returnUrl = $redirectUrl;

        if ($confirmedPaymentIntent && isset($confirmedPaymentIntent->next_action) && $confirmedPaymentIntent->next_action->count()) {
            $nextActionRedirect = isset($confirmedPaymentIntent->next_action['alipay_handle_redirect']['url']) ? self::ALIPAY_REDIRECT : null;
            $nextActionRedirect = isset($confirmedPaymentIntent->next_action['redirect_to_url']['url']) ? self::NORMAL_REDIRECT : $nextActionRedirect;
            $nextActionRedirect = $nextActionRedirect !== null ? $nextActionRedirect : self::SPECIAL_REDIRECT;
            switch ($nextActionRedirect) {
                case self::ALIPAY_REDIRECT:
                    $returnUrl = $confirmedPaymentIntent->next_action['alipay_handle_redirect']['url'];
                    break;
                case self::NORMAL_REDIRECT:
                    $returnUrl = $confirmedPaymentIntent->next_action['redirect_to_url']['url'];
                    break;
                case self::SPECIAL_REDIRECT:
                    $returnUrl = $this->context->link->getModuleLink('stripe_official', 'handleNextAction', ['paymentIntentId' => $confirmedPaymentIntent->id, 'cartId' => $cartId], true);
                    break;
            }
        }

        try {
            $status = null;

            if (!$confirmedPaymentIntent) {
                $confirmedPaymentIntent = $this->stripePaymentIntentService->getStripePaymentIntent($stripePaymentIntent->id);
                StripeProcessLogger::logInfo('pi elements flow handler ' . json_encode($confirmedPaymentIntent), 'ElementsFlowHandler', $cartId, $confirmedPaymentIntent->id);
            }

            $lastPaymentError = $confirmedPaymentIntent->last_payment_error ?? null;

            if ($lastPaymentError) {
                $chargeDeclineCode = $lastPaymentError->decline_code ?? $lastPaymentError->code ?? null;
                $status = $psStripePaymentIntent->getStatusFromStripeDeclineCode($chargeDeclineCode);
                StripeProcessLogger::logInfo('Last Payment Error: ' . json_encode($lastPaymentError), 'ElementsFlowHandler', $cartId, $confirmedPaymentIntent->id);
            }

            $status = $status ?? $psStripePaymentIntent->getStatusFromStripePaymentIntentStatus($confirmedPaymentIntent->status);

            if ($psStripePaymentIntent->validateStatusChange($status)) {
                $psStripePaymentIntent->setIdPaymentIntent($confirmedPaymentIntent->id);
                $psStripePaymentIntent->setAmount($confirmedPaymentIntent->amount);
                $psStripePaymentIntent->setStatus($status);
                $psStripePaymentIntent->save();
            }

            $order = new Order($orderModel->orderId);
            $order->setCurrentState($psStripePaymentIntent->getPsStatus());
            $order->update();
            StripeProcessLogger::logInfo('Set curent stats' . $psStripePaymentIntent->getPsStatus(), 'ElementsFlowHandler', $cartContextModel->cartId, $stripePaymentIntent->id);
            $this->stripePaymentIntentService->updateStripePaymentIntent($confirmedPaymentIntent->id, ['description' => $orderModel->orderReference]);
        } catch (Exception $e) {
            StripeProcessLogger::logError('Order Confirmation Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 'ElementsFlowHandler', $cartId, $confirmedPaymentIntent->id);
        }

        if ($confirmedPaymentIntent->last_payment_error) {
            StripeProcessLogger::logError('Cookie ' . json_encode($this->context), 'ElementsFlowHandler');

            return $failUrl;
        }

        StripeProcessLogger::logInfo('Return URL: ' . $returnUrl, 'ElementsFlowHandler', $cartContextModel->cartId, $stripePaymentIntent->id);

        return $returnUrl;
    }
}
