<?php
/**
* NOTICE OF LICENSE
*
* This file is part of the 'WK Mass Suppliers & Warehouses Assignment For Products' module feature.
* Developped by Khoufi Wissem (2017).
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

class WkwarehousesProcessactionsModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        if (Tools::isSubmit('action')) {
            if (Tools::getIsset('id_product') && Tools::getValue('id_product')) {
                $id_product = (int)Tools::getValue('id_product');
                $allow_oosp = Product::isAvailableWhenOutOfStock(
                    StockAvailable::outOfStock($id_product)
                ); // Allow to order the product when out of stock?
                $product = new Product($id_product, false);
                $isProductAsm = (int)$product->advanced_stock_management;
            }
            if (Tools::getIsset('id_product_attribute')) {
                $id_product_attribute = (int)Tools::getValue('id_product_attribute');
            }
            /* Check product validity */
            if (isset($product) && !Validate::isLoadedObject($product)) {
                $this->ajaxDie(json_encode(array(
                    'hasError' => true,
                    'message' => $this->module->l('Product is not valid!', 'processactions'),
                )));
            }
			$cart = $this->context->cart;
			$isValidCart = Validate::isLoadedObject($cart);
			if (isset($id_product_attribute)) {
				$id_product_attribute = is_null($id_product_attribute) ? 0 : $id_product_attribute;
			}

            switch (Tools::getValue('action')) {
                case 'refreshWarehouseCart':
					$json_array = array();
					// Display the current delivery address in the right block of cart page
                    if (!Configuration::get('WKWAREHOUSE_ALLOW_MULTI_ADDRESSES')) {
						if (Validate::isLoadedObject($cart)) {
							if (Configuration::get('WKWAREHOUSE_DELIVERY_ADDRESS_INCART')) {
								$delivery_address = '';
								if ($cart->id_address_delivery) {
									$delivery_address = $this->context->customer->getSimpleAddress($cart->id_address_delivery);
									$delivery_address = AddressFormat::generateAddress(
										new Address($cart->id_address_delivery),
										array(),
										'<br>'
									);
								}
								$json_array['delivery_address'] = $delivery_address;
							}
							if ($cart->id_address_delivery) {
								$json_array['carriers_restrictions'] = $this->module->checkDeliveriesCustomerAddressOnCartListing();
							}
						}
                    }
					// Display warehouses informations for each A.S.M product
					if (Configuration::get('WKWAREHOUSE_ENABLE_INCART')) {
						$result = $this->module->initWarehousesInformationsOnCartListing();
						if ($result['asmProductsInCart'] && count($result['warehousesInfos'])) {
                            $json_array['warehouses_cart_details'] = $result['warehousesInfos'];
						}
					}
					/* Check up if there are multi-warehouses products in cart while the multi-warehouses is disabled in config page */
					if (!Configuration::get('WKWAREHOUSE_ALLOW_MULTIWH_CART') && $this->context->cart->id &&
						WarehouseStock::getNumberOfAsmProductsInCart($this->context->cart->id, true) > 1) {
                        $json_array['warehouses_restrictions'] = 1;
					}
					$this->ajaxDie(json_encode($json_array));
                    break;
                case 'assignProductInCart':
                case 'initWarehouseProductInCart':
                    $id_warehouse = null;

                    // IF Product handled by A.S.M system
                    if ($isProductAsm) {
                        $selected_warehouse = WarehouseStock::getAvailableWarehouseAndCartQuantity(
                            $product->id,
                            $id_product_attribute,
                            $this->context->cart
                        );
                        $id_warehouse = (int)$selected_warehouse['id_warehouse'];
                        $available_quantity = (int)$selected_warehouse['quantity'];
                    }
                    /********************************* CHECK IF NO MULTI-WAREHOUSES ALLOWED IN CART ***********************************/
                    if (!Configuration::get('WKWAREHOUSE_ALLOW_MULTIWH_CART') && !empty($id_warehouse)) {
                        $isMultiWarehousesInCart = $this->isMultiWarehousesInCart($id_product, $id_product_attribute, $id_warehouse);
                        if (!empty($isMultiWarehousesInCart)) {
                            /* Remove any trace from warehouse product cart table (Protection) */
                            WarehouseStock::removeProductFromWarehouseCart(
								$cart->id,
								$id_product,
								$id_product_attribute
							);
                            $this->ajaxDie(json_encode(array(
                                'multiWarehousesInCart' => (int)$isMultiWarehousesInCart['multiWarehousesInCart'],
                                'message' => sprintf(
                                    $this->module->l('It seems that you have chosen a product that is stored in different warehouse than the "%s"!', 'processactions'),
                                    $isMultiWarehousesInCart['warehouseName']
                                ),
                            )));
                        }
                    }
                    /*********************************** CHECK IF NO MULTI-CARRIERS ALLOWED IN CART **********************************/
                    if (!Configuration::get('WKWAREHOUSE_ALLOW_MULTICARRIER_CART')) {
                        $isMultiCarriersInCart = $this->isMultiCarriersInCart($id_product, $id_product_attribute, $id_warehouse);
                        if (($isMultiCarriersInCart === true) ||
                            ($isMultiCarriersInCart !== true && $isMultiCarriersInCart == 'NO_CARRIER')) {
                            $json_msg = $this->module->l('It seems that your items are not available for the same shipping method of your products in cart, please check the availability of your items!', 'processactions');
                        }
                        if ($isMultiCarriersInCart != false) {
                            /* Remove any trace from warehouse product cart table (Protection) */
                            WarehouseStock::removeProductFromWarehouseCart($cart->id, $id_product, $id_product_attribute);
                            $this->ajaxDie(json_encode(array(
                                'isMultiCarriersInCart' => $isMultiCarriersInCart,
                                'message' => $json_msg,
                            )));
                        }
                    }
                    /*********************************** CHECK IF NO MULTI-ADDRESSES ALLOWED IN CART **********************************/
                    if (!Configuration::get('WKWAREHOUSE_ALLOW_MULTI_ADDRESSES') && $isValidCart &&
                        $cart->nbProducts() == 0 && isset($selected_warehouse['id_address_delivery']) &&
                        (int)$selected_warehouse['id_address_delivery'] != $cart->id_address_delivery) {
                        WarehouseStock::updateCartDeliveryAddress(
                            (int)$cart->id,
                            (int)$selected_warehouse['id_address_delivery'],
                            true
                        );
                    }
                    /*****************************************************************************************************************/

                    // Reset delivery option to keep total shipping cost updated when displayed to user
                    WarehouseStock::resetDeliveryOption();

                    if ($isProductAsm) {
                        if (!empty($id_warehouse)) {
                            if (($available_quantity > 0 && !$allow_oosp) || $allow_oosp) {
                                $this->ajaxDie(json_encode(array(
                                    'id_warehouse' => $id_warehouse,
                                    'hasError' => false,
                                )));
                            } else {
                                $this->ajaxDie(json_encode(array(
                                    'hasError' => true,
                                    'message' => $this->module->l('It seems that there is not more available stock for that quantity!', 'processactions'),
                                )));
                            }
                        } else {// Product is handled by A.S.M but not assigned to any warehouse
                            $this->ajaxDie(json_encode(array(
                                'hasError' => false,
                            )));
                        }
                    } else {// Product is not handled by A.S.M system
                        $this->ajaxDie(json_encode(array(
                            'hasError' => false,
                        )));
                    }
                    break;
                /* WHEN CHANGING QUANTITY FROM QTY FIELD */
                /*case 'compareCartQtyWithWarehouseQty':
                    if ($isProductAsm) {// IF Product A.S.M
                        $selected_warehouse = WarehouseStock::getAvailableWarehouseAndCartQuantity(
                            $product->id,
                            $id_product_attribute,
                            $this->context->cart
                        );
                        $id_warehouse = (int)$selected_warehouse['id_warehouse'];
                        if (!empty($id_warehouse)) {
                            $available_quantity = (int)$selected_warehouse['quantity']; // we subtracted yet the cart quantity
                            $quantity_wanted = (int)Tools::getValue('quantity_wanted');

                            if (!$allow_oosp && ($available_quantity <= 0 || $available_quantity < $quantity_wanted)) {
                                $this->ajaxDie(json_encode(array(
                                    'hasError' => true,
                                    'message' => $this->module->l('It seems that there is not more available stock for that quantity!', 'processactions'),
                                )));
                            }
                        }
                    }
                   break;*/
                case 'getWarehouseAsCombination':
                    /* Check if product can be ordered: must be at least associated to one warehouse */
                    if ($isProductAsm && empty(WorkshopAsm::getAssociatedWarehousesArray($id_product, $id_product_attribute))) {
                        $this->ajaxDie(json_encode(array(
                            'msgError' => $this->module->l('Not yet available in any warehouse.', 'processactions'),
                            'hasError' => true,
                        )));
                    }

                    $allow_set_warehouse = (int)Configuration::get('WKWAREHOUSE_ALLOWSET_WAREHOUSE');
                    $display_best_warehouse = Configuration::get('WKWAREHOUSE_DISPLAY_SELECTED_WAREHOUSE');
                    if ($allow_set_warehouse || $display_best_warehouse) {
                        /* IF Product handled by A.S.M */
                        if ($isProductAsm) {
                            $warehouses_infos = WarehouseStock::warehousesDataOnProductPage(
                                $id_product,
                                true, // force sorting warehouses by stock priority
                                Configuration::get('WKWAREHOUSE_DISPLAY_SELECTED_LOCATION'),
                                Configuration::get('WKWAREHOUSE_DISPLAY_DELIVERYTIME'),
                                Configuration::get('WKWAREHOUSE_DISPLAY_COUNTRY')
                            );
							WarehouseStock::takeOffOutOfStockWarehouses($id_product, $warehouses_infos);
							WarehouseStock::takeOffDisabledWarehouses($warehouses_infos);

                            $selected_warehouse = WarehouseStock::getAvailableWarehouseAndCartQuantity(
                                $id_product,
                                $id_product_attribute,
                                $cart
                            );
                            // Check if we can deliver this product
                            if (!Configuration::get('WKWAREHOUSE_ALLOW_MULTI_ADDRESSES') &&
                                $isValidCart && $cart->id_address_delivery &&
								isset($selected_warehouse['has_carriers']) && !$selected_warehouse['has_carriers']) {
                                $this->ajaxDie(json_encode(array(
                                    'msgError' => sprintf(
                                        $this->module->l('This product can not be delivered by any carrier to %s', 'processactions'),
                                        $this->module->getCountryName($cart->id_address_delivery)
                                    ),
                                    'hasError' => true,
                                )));
                            }
                            // Check if product/combination is already in cart
                            $warehouse_cart = WarehouseStock::productIsPresentInCart(
                                $cart->id,
                                $id_product,
                                $id_product_attribute
                            );
                            $idWarehouseInCart = '';
                            if ($warehouse_cart && (int)$warehouse_cart['id_warehouse'] > 0 &&
                                $cart->containsProduct($id_product, $id_product_attribute)) {
                                $idWarehouseInCart = (int)$warehouse_cart['id_warehouse'];
                            }

                            $this->ajaxDie(json_encode(array(
                                'product_warehouses_select' => $warehouses_infos,
                                'selected_warehouse' => (!empty($selected_warehouse) ? (int)$selected_warehouse['id_warehouse'] : ''),
                                'idWarehouseInCart' => $idWarehouseInCart,
                            )));
                        }
                    }
                    break;
                case 'changeAddressDelivery':
                    $id_product = (int)Tools::getValue('id_product');
                    $id_product_attribute = (int)Tools::getValue('id_product_attribute');
                    $old_id_address_delivery = (int)Tools::getValue('old_id_address_delivery');
                    $new_id_address_delivery = (int)Tools::getValue('new_id_address_delivery');
                    $id_warehouse = (int)Tools::getValue('id_warehouse');

                    if (!count(WarehouseStock::getAvailableCarrierList(
						new Product($id_product, false),
						$id_warehouse,
						$new_id_address_delivery,
						$id_product_attribute
					))) {
                        die(json_encode(array(
                            'hasErrors' => true,
                            'error' => $this->module->l('It is not possible to deliver this product to the selected address.', 'processactions'),
                        )));
                    }
                    $cart->setProductAddressDelivery(
                        $id_product,
                        $id_product_attribute,
                        $old_id_address_delivery,
                        $new_id_address_delivery
                    );
                    die(json_encode(array(
                        'hasErrors' => false,
                    )));
                case 'changeWarehouseFromDropdownListInCart':
                    // IF Product handled by A.S.M
                    if ($isProductAsm) {
                        $id_warehouse = Tools::getValue('id_warehouse');
                        $warehouse = new StoreHouse($id_warehouse);

                        $hasError = false;
                        $msgError = '';
                        if (Validate::isLoadedObject($warehouse)) {
                            // iF we don't allow multi-addresses:
                            // - look for the a delivery address that match the selected warehouse country
                            // - iF product can be delivered by at least one carrier of the selected warehouse
                            // - check, if the other products in cart can be delivered at the new selected delivery address
                            // - if not, don't allow by warning. Otherwise, change the cart delivery address (cart and cart_product tables)
                            if (!Configuration::get('WKWAREHOUSE_ALLOW_MULTI_ADDRESSES') && $isValidCart && $cart->id_address_delivery) {
                                $new_id_address_delivery = WarehouseStock::getDeliveryAddressOfSelectedWarehouse(
                                    $product,
                                    $warehouse->id,
                                    $cart->id_address_delivery,
									$id_product_attribute
                                );
                                if (!$new_id_address_delivery) {
                                    $this->ajaxDie(json_encode(array(
                                        'hasError' => true,
                                        'msgError' => sprintf(
                                            $this->module->l('This product can not be delivered by any carrier to your address located at %s.', 'processactions'),
                                            $this->module->getCountryName($cart->id_address_delivery)
                                        ),
                                    )));
                                }
                                if ($new_id_address_delivery && $new_id_address_delivery > 0 && $cart->id_address_delivery != $new_id_address_delivery) {
                                    // Check for the other products if each product (with associated warehouse) can be delivered to new address
                                    $products = $cart->getProducts();
                                    if (is_array($products) && count($products) > 1) {
                                        foreach ($products as $row) {
                                            if (!in_array(array($product->id, $id_product_attribute), array($row['id_product'], $row['id_product_attribute']))) {
                                                $result = WarehouseStock::productIsPresentInCart($cart->id, $row['id_product'], $row['id_product_attribute']);
                                                $carriers = WarehouseStock::getAvailableCarrierList(
                                                    (new product($row['id_product'], false)),
                                                    ($result && (int)$result['id_warehouse'] > 0 ? (int)$result['id_warehouse'] : null),
                                                    $new_id_address_delivery,
													$row['id_product_attribute']
                                                );
                                                if (!count($carriers)) {
                                                    $this->ajaxDie(json_encode(array(
                                                        'hasError' => true,
                                                        'msgError' => sprintf(
                                                            $this->module->l('Warehouse can not be changed because some of your products in your cart can not be delivered to %s', 'processactions'),
                                                            $this->module->getCountryName($new_id_address_delivery)
                                                        ),
                                                    )));
                                                }
                                            }
                                        }
                                    }
                                    WarehouseStock::updateCartDeliveryAddress($cart->id, $new_id_address_delivery, true); // update cart
                                    WarehouseStock::updateCartProduct($cart->id, $new_id_address_delivery); // update products in cart
                                }
                            }
                            // IF OK, change warehouse
							if ($isValidCart) {
								WarehouseStock::updateProductWarehouseCart(
									$cart->id,
									$product->id,
									$id_product_attribute,
									$id_warehouse
								);
							}
                        } else {
                            $hasError = true;
                        }
                        $this->ajaxDie(json_encode(array(
                            'hasError' => $hasError,
                            'msgError' => $msgError,
                        )));
                    }
                    break;
                // Remove product from cart including the warehouse product table trace
                case 'removeWarehouseFromCart':
                    $cart->deleteProduct($id_product, $id_product_attribute);
                    $cart->update();

                    $this->module->hookActionObjectProductInCartDeleteAfter(array(
                        'id_cart' => $cart->id,
                        'id_product' => $id_product,
                        'id_product_attribute' => $id_product_attribute,
                    ));

                    $this->ajaxDie(json_encode(array(
                        'hasError' => false,
                    )));
                    break;
            }
        }
    }

    public function isMultiWarehousesInCart($id_product, $id_product_attribute, $add_id_warehouse)
    {
        $multiWarehousesInCart = array();
        $cartProducts = $this->context->cart->getProducts();

        if (is_array($cartProducts) && count($cartProducts) && $add_id_warehouse > 0) {
            $isProductInCart = $this->context->cart->containsProduct($id_product, $id_product_attribute);
            // Ensure that product to add is not in cart
            if (!is_array($isProductInCart) ||
                (is_array($isProductInCart) && isset($isProductInCart['quantity']) && $isProductInCart['quantity'] < 1)) {
                $warehouses_in_cart = array();
                /* get the warehouse of each product in cart using A.S.M */
                foreach ($cartProducts as $row) {
                    $productObj = new Product((int)$row['id_product'], false);
                    if (Validate::isLoadedObject($productObj) && $productObj->advanced_stock_management) {
                        $result = WarehouseStock::productIsPresentInCart(
                            $this->context->cart->id,
                            $productObj->id,
                            $row['id_product_attribute']
                        );
                        if ($result && isset($result['id_warehouse']) && $result['id_warehouse'] > 0) {
                            $warehouses_in_cart[] = (int)$result['id_warehouse'];
                        }
                    }
                }
                if (count($warehouses_in_cart)) {
                    $warehouses_in_cart = array_unique($warehouses_in_cart);
                    if (!in_array($add_id_warehouse, $warehouses_in_cart)) {
                        $multiWarehousesInCart['multiWarehousesInCart'] = 1;
                        $multiWarehousesInCart['warehouseName'] = (new StoreHouse($warehouses_in_cart[0], $this->context->language->id))->name;
                    }
                }
            }
        }
        return $multiWarehousesInCart;
    }

    /*
    * Check if no multi-carriers in cart
    * Return boolean
    */
    public function isMultiCarriersInCart($id_product, $id_product_attribute, $add_id_warehouse)
    {
        $multiCarriersInCart = false;
        $cartProducts = $this->context->cart->getProducts();

        if (is_array($cartProducts) && count($cartProducts)) {/* is there already product(s) in cart */
            $isProductInCart = $this->context->cart->containsProduct(
                $id_product,
                $id_product_attribute
            );
            // IF Product (to add) not in cart
            if (!is_array($isProductInCart) ||
                (is_array($isProductInCart) && isset($isProductInCart['quantity']) && $isProductInCart['quantity'] < 1)) {
                $product = new Product($id_product, false);
                /* look for the authorized carriers of new product to add in cart */
                if (!$product->advanced_stock_management) {
                    $new_available_carriers = WarehouseStock::getAvailableCarrierList(/* to get all associated carriers */
                        $product,
                        $add_id_warehouse,
                        $this->context->cart->id_address_delivery,
						$id_product_attribute
                    );
                } else {
                    $new_product_carriers_list = WarehouseStock::getCarriersByCustomerAddresses(
                        $product,
						$id_product_attribute,
                        $add_id_warehouse,
                        $this->context->cart->id_address_delivery
                    ); /* return an array array(1=>1, 3=>3, etc.) */
                    $new_available_carriers = $new_product_carriers_list['available_carriers'];
                }

                if (empty($new_available_carriers)) {
                    return 'NO_CARRIER'; /* no carrier for the new product to add for the selected delivery address! */
                }
                foreach ($cartProducts as $row) {
                    $productObj = new Product((int)$row['id_product'], false);
                    if (Validate::isLoadedObject($productObj)) {
                        /* get the carriers of each product in cart no matter if it's handled by ASM or not */
                        $result = WarehouseStock::productIsPresentInCart(
                            $this->context->cart->id,
                            $productObj->id,
                            $row['id_product_attribute']
                        );
                        /* is Product stored in warehouse ? */
                        $id_warehouse = ($result && (int)$result['id_warehouse'] > 0 ? (int)$result['id_warehouse'] : null);
                        $product_incart_carriers = WarehouseStock::getAvailableCarrierList(
                            $productObj,
                            $id_warehouse,
                            (int)$row['id_address_delivery'],
							$row['id_product_attribute']
                        );
                        /*
                        * array_intersect : return an array that contains the entries from array1 that are present in array2.
                        * if there is an intersection, continue looping to look for at least one product that does not have an intersection with.
                        * if there isn't an intersection, it means that product has its own carrier, so multi-carriers is true.
                        */
                        if (!array_intersect($new_available_carriers, $product_incart_carriers)) {
                            $multiCarriersInCart = true;
                            break;
                        }
                    }
                }
            }
        }
        return $multiCarriersInCart;
    }
}
