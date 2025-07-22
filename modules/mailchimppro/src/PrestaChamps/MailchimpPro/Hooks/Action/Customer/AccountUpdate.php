<?php
/**
 * MailChimp
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 */

namespace PrestaChamps\MailchimpPro\Hooks\Action\Customer;

use Context;
use Customer;
use DrewM\MailChimp\MailChimp;
use PrestaChamps\MailchimpPro\Commands\CartSyncCommand;
use PrestaChamps\MailchimpPro\Commands\CustomerSyncCommand;

/**
 * Invoked when a customer updates its account successfully
 *
 * @package PrestaChamps\MailchimpPro\Hooks\Action\Customer
 */
class AccountUpdate
{
    protected $context;
    protected $customer;
    protected $mailchimp;

    /**
     * AccountUpdate constructor
     *
     * @param Customer $customer
     * @param MailChimp $mailchimp
     * @param Context $context
     */
    protected function __construct(Customer $customer, MailChimp $mailchimp, Context $context)
    {
        $this->context = $context;
        $this->customer = $customer;
        $this->mailchimp = $mailchimp;

        if ($customer->isGuest()) {
            $this->handleGuestCheckoutAbandonedMail();
        }
    }

    public static function run(Context $context, MailChimp $mailchimp, Customer $customer)
    {
        new static($customer, $mailchimp, $context);
    }

    protected function handleGuestCheckoutAbandonedMail()
    {
        $this->syncCustomer();
        $this->syncCart();
    }

    protected function syncCustomer()
    {
        $command = new CustomerSyncCommand($this->context, $this->mailchimp, array($this->customer->id));
        $command->setMethod(CustomerSyncCommand::SYNC_METHOD_PUT);
        $command->setSyncMode(CustomerSyncCommand::SYNC_MODE_REGULAR);
        $command->execute();
    }

    protected function syncCart()
    {
        if ($this->context->cart && $this->context->cart->nbProducts()) {
            $command = new CartSyncCommand($this->context, $this->mailchimp, array($this->context->cart->id));
            $command->setMethod(
                $this->getCartExists($this->context->cart->id)
                ? CartSyncCommand::SYNC_METHOD_PATCH
                : CartSyncCommand::SYNC_METHOD_POST
            );
            $command->setSyncMode(CartSyncCommand::SYNC_MODE_REGULAR);
            $command->execute();
        }
    }

    protected function getCartExists($cartId)
    {
        $this->mailchimp->get(
            "/ecommerce/stores/{$this->context->shop->id}/carts/{$cartId}",
            array('fields' => array('id'))
        );

        if ($this->mailchimp->success()) {
            return true;
        }

        return false;
    }
}
