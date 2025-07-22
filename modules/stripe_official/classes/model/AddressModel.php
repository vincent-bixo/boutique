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

class AddressModel
{
    public $city;
    public $countryIso;
    public $street;
    public $zipCode;
    public $state;

    public function __construct($city, $countryIso, $street, $zipCode, $state)
    {
        $this->city = $city ?: null;
        $this->countryIso = $countryIso ?: null;
        $this->street = $street ?: null;
        $this->zipCode = $zipCode ?: null;
        $this->state = $state ?: null;
    }

    public static function getFromContext($context): self
    {
        $psAddress = new Address($context->cart->id_address_delivery);
        $psState = new State($psAddress->id_state);
        $psCountryIso = Country::getIsoById($psAddress->id_country);

        $city = $psAddress->city ?: null;
        $countryIso = $psCountryIso ?: null;
        $street = $psAddress->address1 ?: null;
        $zipCode = $psAddress->postcode ?: null;
        $state = $psState->iso_code ?: null;

        return new self($city, $countryIso, $street, $zipCode, $state);
    }

    public static function getFromExpressParams($expressParams)
    {
        $shippingAddress = $expressParams['shippingAddress'];
        $city = $shippingAddress['address']['city'] ?: null;
        $countryIso = $shippingAddress['address']['country'] ?: null;
        $street = $shippingAddress['address']['line1'] ?: null;
        $zipCode = $shippingAddress['address']['postal_code'] ?: null;
        $state = $shippingAddress['address']['state'] ?: null;

        return new self($city, $countryIso, $street, $zipCode, $state);
    }

    public function __serialize()
    {
        return [
            'city' => $this->city,
            'country' => $this->countryIso,
            'line1' => $this->street,
            'postal_code' => $this->zipCode,
            'state' => $this->state,
        ];
    }

    public static function createPrestashopAddress($expressParams, $context, $customerId)
    {
        $customerModel = CustomerModel::getFromExpressParams($expressParams, $context);

        // check if an identical address exists
        $existingAddressId = Db::getInstance()->getValue(
            'SELECT id_address FROM ' . _DB_PREFIX_ . 'address WHERE
                id_customer = ' . (int) $customerId . ' AND
                lastname = "' . pSQL($customerModel->name) . '" AND
                firstname = "' . pSQL($customerModel->name) . '" AND
                address1 = "' . pSQL($customerModel->address->street) . '" AND
                postcode = "' . pSQL($customerModel->address->zipCode) . '" AND
                city = "' . pSQL($customerModel->address->city) . '" AND
                id_country = ' . (int) Country::getByIso($customerModel->address->countryIso, true) . ' AND
                deleted = 0'
        );

        if ($existingAddressId) {
            // if matching address exists, return it
            return new Address($existingAddressId);
        }

        $psAddress = new Address();
        $psAddress->id_country = (int) Country::getByIso($customerModel->address->countryIso, true);
        $psAddress->id_customer = $customerId;
        $psAddress->alias = 'My Address';
        $psAddress->lastname = $customerModel->name;
        $psAddress->firstname = $customerModel->name;
        $psAddress->address1 = $customerModel->address->street;
        $psAddress->postcode = $customerModel->address->zipCode;
        $psAddress->city = $customerModel->address->city;
        $psAddress->phone = $expressParams['billingDetails']['phone'] ?? $expressParams['shippingAddress']['phone'] ?? null;
        $psAddress->save();

        return $psAddress;
    }
}
