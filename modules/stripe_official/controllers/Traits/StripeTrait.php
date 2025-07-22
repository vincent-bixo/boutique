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

namespace StripeOfficial\Controllers\Traits;

use StripeOfficial\Classes\StripeProcessLogger;

trait StripeTrait
{
    protected function constructIntentData($context, $createCheckoutStatus, $captureMethod): array
    {
        $captureMethod = ('on' == $captureMethod || 1 == $captureMethod) ? 'manual' : 'automatic';

        $data = [
            'capture_method' => $captureMethod,
            'metadata' => [
                'id_cart' => $context->cart->id,
            ],
        ];

        if (false === $createCheckoutStatus) {
            if ('manual' === $captureMethod) {
                $data['capture_method'] = 'automatic';
                $data['payment_method_options'] = $this->getPaymentMethodOptions($captureMethod);
            }
            $data['automatic_payment_methods'] = ['enabled' => true];
        }

        return $data;
    }

    /**
     * @throws \Exception
     */
    protected function registerStripeEvent($intent, $eventCharge, $stripeEventStatus)
    {
        //        $stripeEventDate = new \DateTime();
        //        $dateCreated = (null != $eventCharge ? $eventCharge : $intent);
        //        $stripeEventDate = $stripeEventDate->setTimestamp($dateCreated->created);
        //        StripeProcessLogger::logInfo('register Stripe Event=> ' . $stripeEventStatus . ' --- ' . json_encode($dateCreated), 'Stripe - registerStripeEvent');
        //        $stripeEvent = new \StripeEvent();
        //        $stripeEvent = $stripeEvent->getEventByPaymentIntentNStatus($intent->id, $stripeEventStatus);
        //        $stripeEvent->setDateAdd($stripeEventDate->format('Y-m-d H:i:s'));
        //        if (null === $stripeEvent->id) {
        //            $stripeEvent->setIdPaymentIntent($intent->id);
        //            $stripeEvent->setStatus($stripeEventStatus);
        //            $stripeEvent->setIsProcessed(true);
        //            $stripeEvent->setFlowType('direct');
        //        }
        //
        //        return $stripeEvent->save();
    }
}
