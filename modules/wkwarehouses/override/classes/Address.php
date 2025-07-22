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

class Address extends AddressCore
{
    /*
    * When we load shipping method, avoid to select a delivery address that don't exist
    * in the delivery addresses collection of the products in cart
    */
    /*
    * module: wkwarehouses
    * date: 2023-10-16 09:44:10
    * version: 1.77.09
    */
    public static function getFirstCustomerAddressId($id_customer, $active = true)
    {
        $context = Context::getContext();
        if (!Module::isEnabled('wkwarehouses') || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') ||
            !$context->cookie->exists()) {
            return parent::getFirstCustomerAddressId($id_customer, $active);
        }
        $id_first_address = parent::getFirstCustomerAddressId($id_customer, $active);
        $cart = new Cart($context->cookie->id_cart);
        if (Configuration::get('WKWAREHOUSE_ALLOW_MULTI_ADDRESSES') &&
			Configuration::get('WKWAREHOUSE_ALLOW_MULTICARRIER_CART')) {
			if (Validate::isLoadedObject($cart) && count($cart->getWsCartRows()) > 1) {
				if ($cart->isMultiAddressDelivery()) {
					$addresses_ids = array();
					foreach ($cart->getAddressCollection() as $address) {
						$addresses_ids[] = (int)$address->id;
					}
					if (count($addresses_ids) > 0) {
						$addresses_ids = array_unique(array_filter($addresses_ids));
						if (!in_array($id_first_address, $addresses_ids)) {
							$id_first_address = current($addresses_ids);
						}
					}
				} else {
					$id_first_address = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
						'SELECT DISTINCT(id_address_delivery)
						 FROM `'._DB_PREFIX_.'cart_product`
						 WHERE `id_cart` = '.(int)$cart->id
					);
				}
			}
		} else {
			if (isset($context->customer)) {
				$addresses = $context->customer->getAddresses($context->language->id);
				if (count($addresses) > 1 && Validate::isLoadedObject($cart) && count($cart->getWsCartRows()) == 1 &&
					Tools::getIsset('action') && Tools::getValue('action') == 'add-to-cart') {
					$product_cart = $cart->getProducts(false, false, null, false);
					if ($product_cart) {
						$product = new Product($product_cart[0]['id_product'], false);
						if (Validate::isLoadedObject($product) && $product->advanced_stock_management) {
							if (!class_exists('WarehouseStock')) {
								require_once(dirname(__FILE__).'/../../modules/wkwarehouses/classes/Warehouse.php');
								require_once(dirname(__FILE__).'/../../modules/wkwarehouses/classes/WarehouseStock.php');
							}
							$result = WarehouseStock::productIsPresentInCart($cart->id, $product->id, $product_cart[0]['id_product_attribute']);
							if ($result && (int)$result['id_warehouse'] > 0) {
								foreach ($addresses as $address) {
									$id_address = (int)$address['id_address'];
									$context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT')); // to avoid error in context
									$carriers = WarehouseStock::getAvailableCarrierList(
										$product,
										$result['id_warehouse'],
										$id_address,
										$product_cart[0]['id_product_attribute'],
										$cart
									);
									if (count($carriers)) {
										$id_first_address = $id_address;
										break;
									}
								}
							}
						}
					}
				}
			}
		}
        return $id_first_address;
    }
}
