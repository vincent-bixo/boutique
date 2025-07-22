<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
class CartController extends CartControllerCore
{
    
    
    
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
    protected function processChangeProductInCart()
    {
        $result = parent::processChangeProductInCart();
        if (Module::isEnabled('wkwarehouses') && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') &&
            $this->id_product) {
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
    
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
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
