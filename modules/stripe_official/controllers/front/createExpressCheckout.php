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

class stripe_officialCreateExpressCheckoutModuleFrontController extends ModuleFrontController
{
    /**
     * @var StripePaymentIntentService
     */
    private $stripePaymentIntentService;

    /**
     * @param string|null $secretKey
     */
    public function __construct($secretKey = null)
    {
        parent::__construct();
        $secretKey = $secretKey ?: Stripe_official::getSecretKey();
        $this->stripePaymentIntentService = new StripePaymentIntentService($secretKey);
    }

    public function postProcess()
    {
        $elementData = [];

        $values = @Tools::file_get_contents('php://input');
        $content = json_decode($values, true);
        $stripe_express_amount = $content['stripeTotalAmount'];
        $currencyIsoCode = $content['currencyIsoCode'];
        $customerModel = (object) $content['customerModel'];
        $reference = $content['stripeReference'];
        $cartId = $content['stripeCartId'];
        $phone = $content['stripePhone'];

        try {
            $elementData = $this->stripePaymentIntentService->buildPaymentIntentParamsExpressCheckout($stripe_express_amount, $currencyIsoCode, $customerModel, $reference, $cartId);
            $elementData['mode'] = 'payment';
            $billingDetails = $this->stripePaymentIntentService->buildBillingDetailsExpressCheckout($customerModel, $phone);
        } catch (ApiErrorException $e) {
            StripeProcessLogger::logError('Retrieve Stripe Account Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 'hookDisplayProductActions', $this->context->cart->id);
        } catch (PrestaShopException $e) {
            StripeProcessLogger::logError('Retrieve Prestashop State Error => ' . $e->getMessage() . ' - ' . $e->getTraceAsString(), 'hookDisplayProductActions', $this->context->cart->id);
        }

        echo json_encode([
            'expressElement' => $elementData,
            'express_billing_details' => $billingDetails,
            'confirmationReturnUrl' => $this->context->link->getModuleLink('stripe_official', 'orderConfirmationReturn', ['cartId' => $this->context->cart->id], true),
        ]);
        exit;
    }
}
