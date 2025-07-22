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
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use StripeOfficial\Classes\StripeProcessLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class StripePaymentIntentService
{
    public const CAPTURE_MANUAL = 'manual';
    public const CAPTURE_AUTOMATIC = 'automatic';

    /**
     * @var StripeClient
     */
    private $stripeClient;
    /**
     * @var StripeCustomerService
     */
    private $stripeCustomerService;

    /**
     * @param string|null $secretKey
     */
    public function __construct($secretKey = null)
    {
        $secretKey = $secretKey ?: Stripe_official::getSecretKey();
        $this->stripeClient = new StripeClient([
            'api_key' => $secretKey,
            'stripe_version' => Stripe_official::STRIPE_API_VERSION,
        ]);

        $this->stripeCustomerService = new StripeCustomerService($secretKey);
    }

    /**
     * @param string $captureMethod
     *
     * @return array
     */
    protected function getPaymentMethodOptions($captureMethod)
    {
        return [
            'link' => [
                'capture_method' => $captureMethod,
            ],
            'card' => [
                'capture_method' => $captureMethod,
            ],
            'klarna' => [
                'capture_method' => $captureMethod,
            ],
            'afterpay_clearpay' => [
                'capture_method' => $captureMethod,
            ],
            'affirm' => [
                'capture_method' => $captureMethod,
            ],
            'wechat_pay' => [
                'client' => 'web',
            ],
        ];
    }

    /**
     * @param CartContextModel $cartContextModel
     * @param bool $separateAuthAndCapture
     * @param bool $checkoutSession
     * @param OrderModel $orderModel
     *
     * @return array
     *
     * @throws ApiErrorException
     * @throws PrestaShopException
     */
    public function buildPaymentIntentParams($cartContextModel, $separateAuthAndCapture, $orderModel = null, $checkoutSession = false)
    {
        $captureMethod = $separateAuthAndCapture ? self::CAPTURE_MANUAL : self::CAPTURE_AUTOMATIC;
        $noShipping = false;
        if ($cartContextModel instanceof ProductContextModel) {
            $noShipping = true;
        }

        $intentParams = [
            'amount' => $cartContextModel->amount,
            'currency' => $cartContextModel->currencyIsoCode,
            'automatic_payment_methods' => ['enabled' => true],
            'capture_method' => $captureMethod,
            'description' => $orderModel ? $orderModel->orderReference : $cartContextModel->reference,
            'metadata' => [
                'id_cart' => $cartContextModel->cartId,
            ],
            'payment_method_options' => [
                'wechat_pay' => [
                    'client' => 'web',
                ],
            ],
        ];

        if (!$noShipping) {
            $intentParams['shipping'] = [
                'name' => $cartContextModel->customerModel->name,
                'address' => $cartContextModel->customerModel->address->__serialize(),
            ];
        }

        if (self::CAPTURE_MANUAL === $captureMethod) {
            $intentParams['capture_method'] = self::CAPTURE_AUTOMATIC;
            $intentParams['payment_method_options'] = $this->getPaymentMethodOptions($captureMethod);
        }

        $stripeCustomerId = $this->stripeCustomerService->getOrCreateStripeCustomerId($cartContextModel);
        if ($stripeCustomerId) {
            $intentParams['customer'] = $stripeCustomerId;
        }

        if ($checkoutSession) {
            $intentParams = [
                'capture_method' => $captureMethod,
                'description' => $orderModel ? $orderModel->orderReference : $cartContextModel->reference,
                'metadata' => [
                    'id_cart' => $cartContextModel->cartId,
                ],
            ];
        }

        return $intentParams;
    }

    /**
     * @return array
     *
     * @throws ApiErrorException
     * @throws PrestaShopException
     */
    public function buildPaymentIntentParamsExpressCheckout($amount, $currencyIsoCode, $customerModel, $reference, $cartId)
    {
        $intentParams = [
            'amount' => $amount,
            'currency' => $currencyIsoCode,
            'automatic_payment_methods' => ['enabled' => true],
            'description' => $reference,
            'shipping' => [
                'name' => $customerModel->name,
                'address' => $customerModel->address,
            ],
            'metadata' => [
                'id_cart' => $cartId,
            ],
        ];

        $stripeCustomerId = $this->stripeCustomerService->getOrCreateStripeCustomerIdExpressCheckout((object) $customerModel, $cartId);

        if ($stripeCustomerId) {
            $intentParams['customer'] = $stripeCustomerId;
        }

        return $intentParams;
    }

    /**
     * @param int $cartId
     *
     * @return array
     *
     * @throws PrestaShopException
     */
    public function buildPaymentIntentOptions($cartId)
    {
        $idempotencyKey = StripeIdempotencyKey::getOrCreateIdempotencyKey($cartId);

        return [
            'idempotency_key' => $idempotencyKey->idempotency_key . uniqid() . StripePaymentIntent::PAYMENT_INTENT_CREATE,
        ];
    }

    /**
     * @param array $stripePaymentIntentParams
     * @param array $stripePaymentIntentOptions
     *
     * @return PaymentIntent|null
     */
    public function createStripePaymentIntent($stripePaymentIntentParams, $stripePaymentIntentOptions)
    {
        $stripePaymentIntent = null;
        $cartId = $stripePaymentIntentParams['metadata']['id_cart'];
        try {
            $stripePaymentIntent = $this->stripeClient->paymentIntents->create($stripePaymentIntentParams, $stripePaymentIntentOptions);
        } catch (ApiErrorException $e) {
            StripeProcessLogger::logError('Create Stripe Payment Intent Error => ' . $e->getMessage() . '-' . $e->getTraceAsString(), 'StripePaymentIntentService', $cartId);
        }

        return $stripePaymentIntent;
    }

    /**
     * @param CartContextModel|ProductContextModel $cartContextModel
     * @param bool $separateAuthAndCapture
     *
     * @return PaymentIntent|null
     */
    public function createPaymentIntent($cartContextModel, $separateAuthAndCapture)
    {
        $stripePaymentIntent = null;
        try {
            $stripePaymentIntentParams = $this->buildPaymentIntentParams($cartContextModel, $separateAuthAndCapture);
            $stripePaymentIntentOptions = $this->buildPaymentIntentOptions($cartContextModel->cartId);
            $stripePaymentIntent = $this->createStripePaymentIntent($stripePaymentIntentParams, $stripePaymentIntentOptions);

            $idempotencyKey = new StripeIdempotencyKey();
            $idempotencyKey->updateIdempotencyKey($cartContextModel->cartId, $stripePaymentIntent);
            StripePaymentIntent::getOrCreatePaymentIntent($stripePaymentIntent);
        } catch (Exception $e) {
            StripeProcessLogger::logError('Create Payment Intent Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 'StripePaymentIntentService', $cartContextModel->cartId);
        }
        StripeProcessLogger::logInfo('Create Payment Intent Ending ' . json_encode($stripePaymentIntent), 'StripePaymentIntentService', $cartContextModel->cartId, $stripePaymentIntent->id);

        return $stripePaymentIntent;
    }

    /**
     * @param PaymentIntent $stripePaymentIntent
     * @param string $stripePaymentMethodId
     * @param string $returnUrl
     *
     * @return PaymentIntent|null
     */
    public function confirmPaymentIntent($stripePaymentIntent, $stripePaymentMethodId, $returnUrl)
    {
        $confirmedStripePaymentIntent = null;
        try {
            $confirmedStripePaymentIntent = $this->stripeClient->paymentIntents->confirm($stripePaymentIntent->id, [
                'confirmation_token' => $stripePaymentMethodId,
                'return_url' => $returnUrl,
            ]);
        } catch (Exception $e) {
            StripeProcessLogger::logError('Confirm Payment Intent Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 'StripePaymentIntentService', $stripePaymentIntent->metadata->id_cart, $stripePaymentIntent->id);
        }
        StripeProcessLogger::logInfo('Confirm Payment Intent Ending ' . json_encode($confirmedStripePaymentIntent), 'StripePaymentIntentService', $stripePaymentIntent->metadata->id_cart, $stripePaymentIntent->id);

        return $confirmedStripePaymentIntent;
    }

    /**
     * @param string|null $paymentIntentId
     *
     * @return PaymentIntent|null
     */
    public function getStripePaymentIntent($paymentIntentId)
    {
        if (!$paymentIntentId) {
            return null;
        }

        $stripePaymentIntent = null;
        try {
            $stripePaymentIntent = $this->stripeClient->paymentIntents->retrieve($paymentIntentId);
        } catch (ApiErrorException $e) {
            StripeProcessLogger::logInfo('Get Stripe Payment Intent => ' . $e->getMessage() . '-' . $e->getTraceAsString(), 'StripePaymentIntentService', null, $paymentIntentId);
        }

        return $stripePaymentIntent;
    }

    public function updateStripePaymentIntent($paymentIntentId, $stripePaymentIntentParams)
    {
        if (!$paymentIntentId) {
            return null;
        }

        $stripePaymentIntent = null;
        try {
            $stripePaymentIntent = $this->stripeClient->paymentIntents->update($paymentIntentId, $stripePaymentIntentParams);
        } catch (ApiErrorException $e) {
            StripeProcessLogger::logError('Update Stripe Payment Intent Error => ' . $e->getMessage() . '-' . $e->getTraceAsString(), 'updateStripePaymentIntent', null, $paymentIntentId);
        }

        return $stripePaymentIntent;
    }

    /**
     * @param CartContextModel $cartContextModel
     */
    public function buildBillingDetails($cartContextModel)
    {
        return [
            'billing_details' => [
                'address' => $cartContextModel->customerModel->address->__serialize(),
                'email' => $cartContextModel->customerModel->email,
                'name' => $cartContextModel->customerModel->name,
                'phone' => $cartContextModel->phone,
            ],
        ];
    }

    public function buildBillingDetailsExpressCheckout($customerModel, $phone)
    {
        return [
            'billing_details' => [
                'address' => $customerModel->address,
                'email' => $customerModel->email,
                'name' => $customerModel->name,
                'phone' => $phone,
            ],
        ];
    }
}
