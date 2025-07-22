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

class CustomerModel
{
    public $email;
    public $name;
    public $id;
    public $address;

    /**
     * @param Customer $customer
     * @param AddressModel $addressModel
     */
    public function __construct($name, $email, $id, $addressModel)
    {
        $this->name = $name ?: null;
        $this->email = $email ?: null;
        $this->id = $id ?: null;
        $this->address = $addressModel;
    }

    public static function getFromContext($context): self
    {
        $addressModel = AddressModel::getFromContext($context);
        $psCustomer = $context->customer;

        $name = $psCustomer->firstname . ' ' . $psCustomer->lastname ?: null;
        $email = $psCustomer->email ?: null;
        $id = $psCustomer->id ?: null;

        return new self($name, $email, $id, $addressModel);
    }

    public static function getFromExpressParams($expressParams, $context): self
    {
        $billingDetails = $expressParams['billingDetails'];
        $addressModel = AddressModel::getFromExpressParams($expressParams);
        $name = $billingDetails['name'] ?: null;
        $email = $billingDetails['email'] ?: null;
        $id = $context->customer ? $context->customer->id : null;

        return new self($name, $email, $id, $addressModel);
    }

    public function __serialize(): array
    {
        return [
            'email' => $this->email,
            'name' => $this->name,
            'id' => $this->id,
            'address' => $this->address->__serialize(),
        ];
    }

    public static function createPrestashopCustomer($expressParams)
    {
        $psCustomer = new Customer();
        $psCustomer->lastname = $expressParams['billingDetails']['name'];
        $psCustomer->firstname = $expressParams['billingDetails']['name'];
        $psCustomer->email = $expressParams['billingDetails']['email'];
        $psCustomer->passwd = md5(uniqid(mt_rand(0, mt_getrandmax()), true));
        $psCustomer->is_guest = 1;
        $psCustomer->active = 1;
        $psCustomer->logged = true;
        $psCustomer->add();

        return $psCustomer;
    }
}
