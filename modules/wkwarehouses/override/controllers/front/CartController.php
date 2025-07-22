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

class CartController extends CartControllerCore
{
    protected function processChangeProductInCart()
    {
        $result = parent::processChangeProductInCart();

        if (Module::isEnabled('wkwarehouses') && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') &&
            $this->id_product) {
            // Check quantities in cart on every change
            $isAvailable = $this->areProductsAvailableInCart();
            if (true !== $isAvailable) {
                array_push(
                    $this->updateOperationError,
                    $isAvailable
                );
            }
        } else {
            return $result;
        }
    }

    /**
     * Check if the products in the cart are available.
     *
     * @return bool|string
     */
    protected function areProductsAvailableInCart()
    {
        $product = $this->context->cart->checkQuantities(true);

        if (true === $product || !is_array($product)) {
            return true;
        }
        if ($product['active']) {
            return $this->trans(
                'The item %product% in your cart is no longer available in this quantity. You cannot proceed with your order until the quantity is adjusted.',
                array('%product%' => $product['name']),
                'Shop.Notifications.Error'
            );
        }
        return $this->trans(
            'This product (%product%) is no longer available.',
            array('%product%' => $product['name']),
            'Shop.Notifications.Error'
        );
    }
}
