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

use PrestaShop\PrestaShop\Core\Domain\Order\Exception\OrderException;
use StripeOfficial\Classes\StripeProcessLogger;

if (!defined('_PS_VERSION_')) {
    exit;
}

class stripe_officialCreateIntentModuleFrontController extends ModuleFrontController
{
    /**
     * @var StripePaymentIntentService
     */
    private $stripePaymentIntentService;
    /**
     * @var PrestashopBuildOrderService
     */
    private $prestashopBuildOrderService;

    /**
     * @var PrestashopOrderService
     */
    private $prestashopOrderService;

    /**
     * @param string|null $secretKey
     */
    public function __construct($secretKey = null)
    {
        parent::__construct();
        $secretKey = $secretKey ?: Stripe_official::getSecretKey();
        $this->stripePaymentIntentService = new StripePaymentIntentService($secretKey);
        $this->prestashopBuildOrderService = new PrestashopBuildOrderService($this->context, $this->module, $secretKey);
        $this->prestashopOrderService = new PrestashopOrderService($this->context, $this->module, $secretKey);
    }

    /**
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     * @throws OrderException
     */
    public function postProcess()
    {
        $values = @Tools::file_get_contents('php://input');
        $content = json_decode($values, true);
        $cartId = $content['cartId'];
        $cart = new Cart($cartId);
        $this->context->cart = $cart;
        $this->context->cart->update();

        StripeProcessLogger::logInfo('Content for Express Checkout => ' . json_encode($content), 'createIntent', $cartId);
        $amount = $content['amount'];
        $amount = $amount + $content['event']['shippingRate']['amount'];
        $currencyIso = $content['currency'];
        $expressParams = $content['event'];
        $psCustomer = $this->context->customer;

        $countryId = (int) Country::getByIso($expressParams['shippingAddress']['address']['country'], true);
        if (!$countryId) {
            echo json_encode(['error' => true, 'message' => 'Shipping country unavailable']);
            exit;
        }

        if (!isset($psCustomer->id) || !$psCustomer->id) {
            $psCustomer = CustomerModel::createPrestashopCustomer($expressParams);
            $psAddress = AddressModel::createPrestashopAddress($expressParams, $this->context, $psCustomer->id);
            $this->context->customer = $psCustomer;
            $this->context->cart->id_customer = $psCustomer->id;
            $this->context->cart->secure_key = $psCustomer->secure_key;
            $this->context->cart->id_address_invoice = $psAddress->id;
            $this->context->cart->id_address_delivery = $psAddress->id;
            $this->context->cart->id_guest = Context::getContext()->cookie->id_guest;
            $this->context->cart->id_currency = Context::getContext()->cookie->id_currency;
            $this->context->cart->update();

            $psGuest = new Guest(Context::getContext()->cookie->id_guest);
            $psGuest->id_customer = $psCustomer->id;
            $psGuest->update();
        } else {
            $psAddress = $this->checkCustomerShippingAddress($expressParams, $this->context);
        }

        $this->context->cookie->id_customer = $psCustomer->id;
        $this->context->cookie->customer_lastname = $psAddress->lastname;
        $this->context->cookie->customer_firstname = $psAddress->firstname;
        $this->context->cookie->logged = 1;
        $this->context->cookie->check_cgv = 1;
        $this->context->cookie->is_guest = 1;
        $this->context->cookie->passwd = $psCustomer->passwd;
        $this->context->cookie->email = $psCustomer->email;
        $this->context->cookie->id_cart = $cart->id;
        $this->context->cart->id_address_invoice = $psAddress->id;
        $this->context->cart->id_address_delivery = $psAddress->id;
        $this->context->cart->id_carrier = $expressParams['shippingRate']['id'];
        $delivery_option[$psAddress->id] = $expressParams['shippingRate']['id'] . ',';
        $delivery_option = json_encode($delivery_option);
        $this->context->cart->update();
        $cart = $this->context->cart;
        $cart->update();

        $this->updatePrestashopCart($delivery_option, $cartId);

        /** Get fresh cart data and reinitialize cart context */
        $cart = new Cart($cartId);
        $this->context->cart = $cart;
        $this->context->cart->update();

        $this->updatePrestashopCartProduct($psAddress->id, $cartId);
        $contextModel = ProductContextModel::getFromExpressParams($expressParams, $amount, $currencyIso, $this->context);
        StripeProcessLogger::logInfo('getFromExpressParams => ' . json_encode($contextModel), 'createIntent', $cartId);
        $separateAuthAndCapture = Configuration::get(Stripe_official::CATCHANDAUTHORIZE);
        $stripePaymentIntent = $this->stripePaymentIntentService->createPaymentIntent($contextModel, $separateAuthAndCapture);

        $newOrderFlow = !(int) Configuration::get(Stripe_official::ORDER_FLOW);
        if ($newOrderFlow) {
            $psStripePaymentIntent = new StripePaymentIntent();
            $psStripePaymentIntent->findByIdPaymentIntent($stripePaymentIntent->id);

            $cartContextModel = CartContextModel::getFromContext($this->context);
            StripeProcessLogger::logInfo('cartContextModel => ' . json_encode($cartContextModel), 'createIntent', $cartId);
            $orderModel = $this->prestashopBuildOrderService->buildAndCreatePrestashopOrder($psStripePaymentIntent, $stripePaymentIntent, $cartContextModel);

            $this->prestashopOrderService->createPsStripePayment($stripePaymentIntent, $orderModel);
            $this->stripePaymentIntentService->updateStripePaymentIntent($stripePaymentIntent->id, ['description' => $orderModel->orderReference]);
        }

        $redirectUrl = $this->context->link->getModuleLink(
            'stripe_official',
            'orderConfirmationReturn',
            ['cartId' => $cartId],
            true
        );

        echo json_encode(['intent' => $stripePaymentIntent, 'stripe_express_return_url' => $redirectUrl]);

        exit;
    }

    public static function checkCustomerShippingAddress($expressParams, $context)
    {
        $psAddress = new Address(Address::getFirstCustomerAddressId($context->customer->id));
        $countryId = (int) Country::getByIso($expressParams['shippingAddress']['address']['country'], true);
        if (!$psAddress->id) {
            $psAddress = AddressModel::createPrestashopAddress($expressParams, Context::getContext(), $context->customer->id);
        } elseif (
            $psAddress->id_country !== $countryId
            || $psAddress->postcode !== $expressParams['shippingAddress']['address']['postal_code']
            || $psAddress->city !== $expressParams['shippingAddress']['address']['city']
            || $psAddress->address1 !== $expressParams['shippingAddress']['address']['line1']
        ) {
            $psAddress = AddressModel::createPrestashopAddress($expressParams, Context::getContext(), $context->customer->id);
        }

        return $psAddress;
    }

    public function updatePrestashopCartProduct($addressDeliveryId, $cartId)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'cart_product`
        SET `id_address_delivery` = ' . (int) $addressDeliveryId . '
        WHERE  `id_cart` = ' . (int) $cartId;

        StripeProcessLogger::logInfo('Update Prestashop Cart Product => ' . $sql, 'createIntent', $cartId);
        Db::getInstance()->execute($sql);
    }

    public function updatePrestashopCart($delivery_option, $cartId)
    {
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'cart
        SET delivery_option = \'' . $delivery_option . '\'
        WHERE id_cart = ' . (int) $cartId;

        Db::getInstance()->execute($sql);
    }
}
