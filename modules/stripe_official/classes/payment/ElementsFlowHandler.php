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

class ElementsFlowHandler implements FlowHandlerInterface
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
        $stripePaymentIntent = $this->stripePaymentIntentService->createPaymentIntent($cartContextModel, $separateAuthAndCapture);
        if (!$stripePaymentIntent) {
            return $failUrl;
        }

        $successUrl = $this->context->link->getModuleLink('stripe_official', 'orderConfirmationReturn', ['cartId' => $cartContextModel->cartId], true);

        $stripePaymentIntent = $this->stripePaymentIntentService->confirmPaymentIntent($stripePaymentIntent, $this->stripePaymentMethodId, $successUrl);
        if (!$stripePaymentIntent) {
            return $failUrl;
        }

        $returnUrl = $successUrl;

        $nextActionRedirect = isset($stripePaymentIntent->next_action['alipay_handle_redirect']['url']) ? self::ALIPAY_REDIRECT : null;
        $nextActionRedirect = isset($stripePaymentIntent->next_action['redirect_to_url']['url']) ? self::NORMAL_REDIRECT : $nextActionRedirect;
        $nextActionRedirect = $nextActionRedirect !== null ? $nextActionRedirect : self::SPECIAL_REDIRECT;
        if ($stripePaymentIntent->next_action && $stripePaymentIntent->next_action->count()) {
            switch ($nextActionRedirect) {
                case self::ALIPAY_REDIRECT:
                    $returnUrl = $stripePaymentIntent->next_action['alipay_handle_redirect']['url'];
                    break;
                case self::NORMAL_REDIRECT:
                    $returnUrl = $stripePaymentIntent->next_action['redirect_to_url']['url'];
                    break;
                case self::SPECIAL_REDIRECT:
                    $returnUrl = $this->context->link->getModuleLink('stripe_official', 'handleNextAction', ['paymentIntentId' => $stripePaymentIntent->id], true);
                    break;
            }
        }

        StripeProcessLogger::logInfo('Return URL: ' . $returnUrl, 'ElementsFlowHandler', $cartContextModel->cartId, $stripePaymentIntent->id);

        return $returnUrl;
    }
}
