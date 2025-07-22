<?php
/**
* NOTICE OF LICENSE
*
* This file is part of the 'Wk Warehouses Management' module feature.
* Developped by Khoufi Wissem (2018).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/
if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderConfirmationController extends OrderConfirmationControllerCore
{
    public function initContent()
    {
        if (!Module::isEnabled('wkwarehouses') || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            return parent::initContent();
        }
        if (Configuration::isCatalogMode()) {
            Tools::redirect('index.php');
        }
        $order = new Order(Order::getIdByCartId((int)$this->id_cart));
        if (!Order::isOrderMultiWarehouses($order) && !Order::isOrderMultiCarriers($order)) {
            return parent::initContent();
        }
        
        $allOrders = array((int)$order->id);
        
        // Get all brothers orders (with the same reference)
        foreach ($order->getBrother() as $suborder) {
            $allOrders[] = (int)$suborder->id;
            // Update Total Order Prices
            $order->total_paid_real += $suborder->total_paid_real;
            $order->total_paid += $suborder->total_paid;
            $order->total_discounts += $suborder->total_discounts;
            $order->total_discounts_tax_incl += $suborder->total_discounts_tax_incl;
            $order->total_discounts_tax_excl += $suborder->total_discounts_tax_excl;
            $order->total_paid_tax_incl += $suborder->total_paid_tax_incl;
            $order->total_paid_tax_excl += $suborder->total_paid_tax_excl;
            $order->total_products += $suborder->total_products;
            $order->total_products_wt += $suborder->total_products_wt;
            $order->total_shipping += $suborder->total_shipping;
            $order->total_shipping_tax_incl += $suborder->total_shipping_tax_incl;
            $order->total_shipping_tax_excl += $suborder->total_shipping_tax_excl;
            $order->total_wrapping += $suborder->total_wrapping;
            $order->total_wrapping_tax_incl += $suborder->total_wrapping_tax_incl;
            $order->total_wrapping_tax_excl += $suborder->total_wrapping_tax_excl;
        }

        // Fix order payments amounts if orders have been paid directly from shop (credit card, paypal...)
        foreach ($allOrders as $orderID) {
            (new Order($orderID))->fixOrderPayment();
        }

        // Hide Shipping method block if multi-carriers
        $this->context->controller->addCSS(_MODULE_DIR_.'wkwarehouses/views/css/wkwarehouses.css', 'all');

        $register_form = $this
            ->makeCustomerForm()
            ->setGuestAllowed(false)
            ->fillWith(Tools::getAllValues());

        parent::initContent();

		if (class_exists('PrestaShop\PrestaShop\Adapter\Presenter\Order\OrderPresenter')) {
			$order_presenter = new PrestaShop\PrestaShop\Adapter\Presenter\Order\OrderPresenter();
		} else {
			$order_presenter = new PrestaShop\PrestaShop\Adapter\Order\OrderPresenter();
		}
        $this->context->smarty->assign(array(
            'HOOK_ORDER_CONFIRMATION' => $this->displayOrderConfirmation($order),
            'HOOK_PAYMENT_RETURN' => $this->displayPaymentReturn($order),
            'order' => $order_presenter->present($order),
            'register_form' => $register_form,
        ));

        if ($this->context->customer->is_guest) {
            /* If guest we clear the cookie for security reason */
            $this->context->customer->mylogout();
        }
        $this->setTemplate('checkout/order-confirmation');
    }
}
