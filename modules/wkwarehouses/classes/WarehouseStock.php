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

/**
 * Represents the products kept in warehouses
 */
class WarehouseStock extends ObjectModel
{
    public $id_warehouse;
    public $id_product;
    public $id_product_attribute;
    public $reference;
    public $ean13;
    public $isbn;
    public $upc;

    /** @var int the physical quantity in stock for the current product in the current warehouse */
    public $physical_quantity;

    /** @var int the usable quantity (for sale) of the current physical quantity */
    public $usable_quantity;

    /** @var int the unit price without tax for the current product */
    public $price_te;

    public static $definition = array(
        'table' => 'stock',
        'primary' => 'id_stock',
        'fields' => array(
            'id_warehouse' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'reference' => array('type' => self::TYPE_STRING, 'validate' => 'isReference'),
            'ean13' => array('type' => self::TYPE_STRING, 'validate' => 'isEan13'),
            'isbn' => array('type' => self::TYPE_STRING, 'validate' => 'isIsbn'),
            'upc' => array('type' => self::TYPE_STRING, 'validate' => 'isUpc'),
            'physical_quantity' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'usable_quantity' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'price_te' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
        ),
    );

    protected $webserviceParameters = array(
        'fields' => array(
            'id_warehouse' => array('xlink_resource' => 'warehouses'),
            'id_product' => array('xlink_resource' => 'products'),
            'id_product_attribute' => array('xlink_resource' => 'combinations'),
            'real_quantity' => array('getter' => 'getWsRealQuantity', 'setter' => false),
        ),
        'hidden_fields' => array(),
    );

    public function update($null_values = false)
    {
        $this->getProductInformations();

        return parent::update($null_values);
    }

    public function add($autodate = true, $null_values = false)
    {
        $this->getProductInformations();

        return parent::add($autodate, $null_values);
    }

    public function delete()
    {
        if (parent::delete()) {
            // Remove stock movements
            WarehouseStockMvt::deleteStockMvt($this->id);
            return true;
        }
        return false;
    }

    /**
     * Get reference, ean13 , isbn and upc of the current product
     * Stores it in stock for stock_mvt integrity and history purposes
     */
    protected function getProductInformations()
    {
        // if combinations
        if ((int)$this->id_product_attribute > 0) {
            $query = new DbQuery();
            $query->select('reference, ean13, isbn, upc');
            $query->from('product_attribute');
            $query->where('id_product = '.(int)$this->id_product);
            $query->where('id_product_attribute = '.(int)$this->id_product_attribute);
            $rows = Db::getInstance()->executeS($query);
            if (!is_array($rows)) {
                return;
            }
            foreach ($rows as $row) {
                $this->reference = Validate::isReference($row['reference']) ? $row['reference'] : '';
                $this->ean13 = Validate::isEan13($row['ean13']) ? $row['ean13'] : '';
                $this->isbn = Validate::isIsbn($row['isbn']) ? $row['isbn'] : '';
                $this->upc = Validate::isUpc($row['upc']) ? $row['upc'] : '';
            }
        } else {
            // else, simple product
            $product = new Product((int)$this->id_product);
            if (Validate::isLoadedObject($product)) {
                $this->reference = Validate::isReference($product->reference) ? $product->reference : '';
                $this->ean13 = Validate::isEan13($product->ean13) ? $product->ean13 : '';
                $this->isbn = Validate::isIsbn($product->isbn) ? $product->isbn : '';
                $this->upc = Validate::isUpc($product->upc) ? $product->upc : '';
            }
        }
    }

    /**
     * Webservice : used to get the real quantity of a product
     */
    public function getWsRealQuantity()
    {
        $manager = StockManagerFactory::getManager();
        $quantity = $manager->getProductRealQuantities(
            $this->id_product,
            $this->id_product_attribute,
            $this->id_warehouse,
            true
        );
        return $quantity;
    }

    public static function deleteWarehouseStock($id_product = null, $id_product_attribute = null, $id_warehouse = null)
    {
        if (is_null($id_product) && is_null($id_product_attribute) && is_null($id_warehouse)) {
            return false;
        }
        return Db::getInstance()->execute(
            'DELETE FROM '._DB_PREFIX_.'stock 
             WHERE 1'
             .(!is_null($id_product) ? ' AND `id_product` = '.(int)$id_product : '')
             .(!is_null($id_product_attribute) ? ' AND `id_product_attribute` = '.(int)$id_product_attribute : '')
             .(!is_null($id_warehouse) ? ' AND `id_warehouse` = '.(int)$id_warehouse : '')
        );
    }

    /**
     * Reset all product quantities
     */
    public static function resetProductStock($id_product, $id_product_attribute = null)
    {
        return Db::getInstance()->update(
            'stock',
            array('physical_quantity' => 0, 'usable_quantity' => 0),
            '`id_product` = '.(int)$id_product.' AND `id_product_attribute` = '.(int)$id_product_attribute
        );
    }

    public static function getStocksRows($id_product, $id_product_attribute = null, $id_warehouse = null)
    {
        return Db::getInstance()->executeS(
            'SELECT *
             FROM '._DB_PREFIX_.'stock
             WHERE 1'
             .(!empty($id_product) ? ' AND `id_product` = '.(int)$id_product : '')
             .(!is_null($id_product_attribute) ? ' AND `id_product_attribute` = '.(int)$id_product_attribute : '')
             .(!is_null($id_warehouse) ? ' AND `id_warehouse` = '.(int)$id_warehouse : '')
        );
    }

    public static function productIsPresentInStock($id_product, $id_product_attribute = null, $id_warehouse = null)
    {
        if (empty($id_product)) {
            return false;
        }
        $result = Db::getInstance()->executeS(
            'SELECT s.`id_stock` 
             FROM '._DB_PREFIX_.'stock s
             INNER JOIN '._DB_PREFIX_.'warehouse_product_location wpl ON (
                wpl.id_product = s.id_product AND 
                wpl.id_product_attribute = s.id_product_attribute AND 
                wpl.id_warehouse = s.id_warehouse
             )
             INNER JOIN `'._DB_PREFIX_.'warehouse` w ON (w.id_warehouse = wpl.id_warehouse)
             WHERE s.`id_product` = '.(int)$id_product
             .(!defined('_PS_ADMIN_DIR_') ? ' AND w.`active` = 1 ' : '')
             .(!is_null($id_product_attribute) ? ' AND s.`id_product_attribute` = '.(int)$id_product_attribute : '')
             .(!empty($id_warehouse) ? ' AND s.`id_warehouse` = '.(int)$id_warehouse : '')
        );
        return (is_array($result) && !empty($result) ? true : false);
    }

    public static function getWarehousesAvailableQuantities($id_product, $id_product_attribute = null)
    {
        // Get quantities of all associated warehouses
        $quantities_warehouses_sort = WorkshopAsm::getProductPhysicalQuantities(
            $id_product,
            $id_product_attribute,
            null,
            true
        );
        // Look for the reserved quantity from stock_available table
        $stock_infos = WorkshopAsm::getAvailableStockByProduct(
            $id_product,
            $id_product_attribute
        );
        $reserved_quantity = isset($stock_infos['reserved_quantity']) ? (int)$stock_infos['reserved_quantity'] : 0;
        $physical_quantity = isset($stock_infos['physical_quantity']) ? (int)$stock_infos['physical_quantity'] : 0;
        $available_quantity = isset($stock_infos['quantity']) ? (int)$stock_infos['quantity'] : 0;

        foreach ($quantities_warehouses_sort as &$data) {
            if ($reserved_quantity == 0) {
                $data['reserved_quantity'] = 0;
                $data['available_quantity'] = $data['physical_quantity'];
            } else {
                $data['reserved_quantity'] = WorkshopAsm::getReservedQuantityByProductAndWarehouse(
                    $id_product,
                    $id_product_attribute,
                    $data['id_warehouse']
                );
                //$data['available_quantity'] = 0; // old
                $data['available_quantity'] = (int)$data['physical_quantity'] - (int)$data['reserved_quantity'];
            }
        }
		// no need to this as long we have the physical and reserved quantity
        /* Process & find for each associated warehouse its available quantities && reserved quantities
        if (count($quantities_warehouses_sort) && $available_quantity > 0 && $reserved_quantity > 0) {
            unset($data);
            // Sort multidimensional array by reserved quantities
            $array_quantities_sort = array();
            foreach ($quantities_warehouses_sort as $data) {
                $array_quantities_sort[] = $data['reserved_quantity'];
            }
            array_multisort($array_quantities_sort, SORT_DESC, $quantities_warehouses_sort);
            // End sort------------------
            unset($data);

            $sum_warehouses_qty = 0;
            foreach ($quantities_warehouses_sort as &$data) {
                $gap = (int)$data['physical_quantity'] - (int)$data['reserved_quantity'];
                $sum_warehouses_qty += (int)$data['physical_quantity'];

                if ($gap >= 0) {
                    if ($reserved_quantity > 0) {
                        if ($data['reserved_quantity'] == 0) {
                            $diff = $data['physical_quantity'] - $reserved_quantity;
                            if ($diff < 0) {
                                $diff *= -1;
                            }
                            // Process negative quantities (Warehouses qties)
                            if ($sum_warehouses_qty > $physical_quantity) {
                                $diff -= ($sum_warehouses_qty - $physical_quantity);
                            }
                            $data['available_quantity'] = (int)$diff;
                            $reserved_quantity -= $data['physical_quantity'];
                        } else {
                            // Exclude warehouses that contain reserved quantities
                            if ($data['physical_quantity'] && $reserved_quantity >= $data['reserved_quantity']) {
                                if ($data['physical_quantity'] >= $reserved_quantity) {
                                    $gap = $data['physical_quantity'] - $reserved_quantity;
                                    // Process negative quantities (Warehouses qties)
                                    if ($sum_warehouses_qty > $physical_quantity) {
                                        $gap -= ($sum_warehouses_qty - $physical_quantity);
                                    }
                                    $reserved_quantity = 0;
                                } else {
                                    $gap = $data['physical_quantity'] - $data['reserved_quantity'];
                                    $reserved_quantity -= $data['reserved_quantity'];
                                }
                            } else {
                                $gap = $data['reserved_quantity'] - $reserved_quantity;
                                if ($gap < 0) {
                                    $gap *= -1;
                                }
                                $reserved_quantity -= $gap;
                            }
                            $data['available_quantity'] = (int)$gap;
                        }
                    } else {
                        // Process negative quantities (Warehouses qties)
                        if ($sum_warehouses_qty > $physical_quantity) {
                            $gap = $data['physical_quantity'] - ($sum_warehouses_qty - $physical_quantity);
                        } else {
                            $gap = (int)$data['physical_quantity'];
                        }
                        $data['available_quantity'] = (int)$gap;
                    }
                } else {
                    $data['available_quantity'] = 0;
                    $reserved_quantity -= $data['physical_quantity'];
                }
            }
        }*/
        
        // Sort now multidimensional array
        if (count($quantities_warehouses_sort)) {
            $array_sort = array();
            // IF stock priority, sort the warehousse with enough stock first
            if (Configuration::get('WKWAREHOUSE_STOCKPRIORITY_DEC')) {
                foreach ($quantities_warehouses_sort as $warehouse_array) {
                    $array_sort[] = $warehouse_array['available_quantity'];
                }
                array_multisort($array_sort, SORT_DESC, $quantities_warehouses_sort);
            } else {
                // IF warehouses priority
                if (Configuration::get('WKWAREHOUSE_PRIORITY_DECREASE')) {
                    $ids_warehouses = array_map(
                        'intval',
                        explode(',', Configuration::get('WKWAREHOUSE_PRIORITY_DECREASE'))
                    );
                    foreach ($quantities_warehouses_sort as $warehouse_array) {
                        $array_sort[] = (int)$warehouse_array['id_warehouse'];
                    }
                    // Return an array containing all values of ids_warehouses
                    // that are present in array_sort array. keys are preserved
                    // Because $quantities_warehouses_sort and $ids_warehouses arrays must have the same length
                    $ids_warehouses = array_intersect($ids_warehouses, $array_sort);
                    usort($quantities_warehouses_sort, self::sortArrayBasedOnAnotherArray($ids_warehouses));
                    //array_multisort($ids_warehouses, SORT_ASC, $quantities_warehouses_sort);
                }
            }
        }
        return $quantities_warehouses_sort;
    }

    public static function sortArrayBasedOnAnotherArray($ids_warehouses)
    {
        return function ($a, $b) use ($ids_warehouses) {
            foreach ($ids_warehouses as $value) {
                if ($value == $a['id_warehouse']) {
                    return 0;
                }
                if ($value == $b['id_warehouse']) {
                    return 1;
                }
            }
        };
    }

    public static function productIsPresentInCart($id_cart, $id_product, $id_product_attribute = null)
    {
        if (empty($id_cart) || empty($id_product)) {
            return false;
        }
        $query = new DbQuery();
        $query->select('wc.*');
        $query->from('warehouse_cart_product', 'wc');
        $query->innerJoin(
            'warehouse_product_location',
            'wpl',
            'wpl.id_product = wc.id_product AND 
             wpl.id_product_attribute = wc.id_product_attribute AND 
             wpl.id_warehouse = wc.id_warehouse'
        );
        $query->innerJoin(
            'cart_product',
            'cp',
            'cp.id_product = wc.id_product AND 
             cp.id_product_attribute = wc.id_product_attribute AND 
             cp.id_cart = wc.id_cart'
        );
        $query->where('wc.id_cart = '.(int)$id_cart);
        $query->where('wc.id_product = '.(int)$id_product);
        if (!is_null($id_product_attribute)) {
            $query->where('wc.id_product_attribute = '.(int)$id_product_attribute);
        }
        return Db::getInstance()->getRow($query);
    }

    public static function getAvailableQuantityByWarehouse($id_product, $id_product_attribute, $id_warehouse)
    {
        // Now look for available quantity by warehouse
        $available_warehouses_quantities = self::getWarehousesAvailableQuantities(
            $id_product,
            $id_product_attribute
        );
        $available_quantity_by_warehouse = 0;
        foreach ($available_warehouses_quantities as $data) {
            if ($data['id_warehouse'] == $id_warehouse) {
                $available_quantity_by_warehouse = (int)$data['available_quantity'];
                break;
            }
        }
        return $available_quantity_by_warehouse;
    }

    /*
    * IF Log in && customer has created his first delivery address
    */
    public static function assignRightDeliveryAddressToEachProductInCart($cart, $new_id_address_delivery = null)
    {
        if (Configuration::get('WKWAREHOUSE_ALLOW_MULTICARRIER_CART')) {
            $context = Context::getContext();

            if (($context->customer->isLogged() && Validate::isLoadedObject($context->customer)) ||// From FO
                (isset($context->cart) && !empty($context->cart->id_customer))) {// From BO
                foreach ($cart->getProducts() as $row) {
                    $id_product = (int)$row['id_product'];

                    $product = new Product($id_product, false);
                    if (Validate::isLoadedObject($product)) {
                        $id_product_attribute = (int)$row['id_product_attribute'];
                        $old_id_address_delivery = (int)$row['id_address_delivery'];
                        // Warehouse ?
                        $result = self::productIsPresentInCart($cart->id, $id_product, $id_product_attribute);
                        $id_warehouse = ($result && (int)$result['id_warehouse'] > 0 ? (int)$result['id_warehouse'] : null);
                        // Look for the carriers related to the old delivery address before.
                        // if OK, no need to replace it
                        // if NO, replace it with new created delivery address
                        $set_address = false;
                        if ($old_id_address_delivery) {
                            $old_carriers = self::getAvailableCarrierList(
                                $product,
                                $id_warehouse,
                                $old_id_address_delivery,
								$id_product_attribute
                            );
                            if (empty($old_carriers)) {
                                $set_address = true;
                            }
                        } else {
                            $set_address = true;
                        }
                        $id_address_delivery = $new_id_address_delivery;
                        // The following code is usefull to look for the best delivery address if the new one is null
                        // Used to check and fix all delivery addresses of cart products
                        if (is_null($new_id_address_delivery) && $set_address) {
                            $new_carriers = self::getCarriersByCustomerAddresses(
								$product,
								$id_product_attribute,
								$id_warehouse
							);
                            $id_address_delivery = (int)$new_carriers['id_address_delivery'];
                        }

                        if ($set_address) {
                            if ($id_address_delivery > 0) {
                                // But check before if there is carrier(s) for the created address
                                $new_carriers = self::getAvailableCarrierList(
									$product,
									$id_warehouse,
									$id_product_attribute,
									$id_address_delivery
								);
                                if (count($new_carriers)) {
                                    $cart->setProductAddressDelivery(
                                        $id_product,
                                        $id_product_attribute,
                                        $old_id_address_delivery,
                                        $id_address_delivery
                                    );
                                }
                            } else {
                                self::updateCartProduct(
                                    $cart->id,
                                    $id_address_delivery,
                                    $product->id,
                                    $id_product_attribute
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    public static function getDeliveryAddressOfSelectedWarehouse(
		$product,
		$id_warehouse,
		$id_address_delivery = null,
		$id_product_attribute
	){
        // First, look if product can be delivered by any carrier of the selected warehouse to the same cart delivery address
        if (!empty($id_address_delivery)) {
            $carriers = self::getAvailableCarrierList($product, $id_warehouse, $id_address_delivery, $id_product_attribute);
            if (count($carriers)) {
                return $id_address_delivery;
            }
        }
        // IF NOT, look if it's possible to another delivery address
        $addresses = Context::getContext()->customer->getAddresses(Context::getContext()->language->id);
        if (count($addresses) > 1) {
            foreach ($addresses as $address) {
                $id_address_delivery = (int)$address['id_address'];
                $carriers = self::getAvailableCarrierList(
					$product,
					$id_warehouse,
					$id_address_delivery,
					$id_product_attribute
				);
                if (count($carriers)) {
                    return $id_address_delivery;
                }
            }
        }
        return false;
    }

    public static function getCarriersByCustomerAddresses(
        $product,
		$id_product_attribute = null,
        $id_warehouse = null,
        $id_address_delivery = null
    ) {
        $context = Context::getContext();
        $carriers = array();

        if (Configuration::get('WKWAREHOUSE_ALLOW_MULTICARRIER_CART')) {
            if ((Validate::isLoadedObject($context->customer) && $context->customer->isLogged()) || // Connected
                (isset($context->cart) && !empty($context->cart->id_customer))) {// Not Connected
                /* iF we allow only one delivery address */
                if (!Configuration::get('WKWAREHOUSE_ALLOW_MULTI_ADDRESSES') &&
                    Validate::isLoadedObject($context->cart) && $context->cart->id_address_delivery) {
                    // Use the default delivery address
                    $id_address_delivery = (int)$context->cart->id_address_delivery;
                    $carriers = self::getAvailableCarrierList(
						$product,
						$id_warehouse,
						$id_address_delivery,
						$id_product_attribute
					);
                } else {
                    /* Get all customer addresses in order to look for the best carriers */
                    $addresses = $context->customer->getAddresses($context->language->id);
                    if (count($addresses) > 1) {
                        foreach ($addresses as $address) {
                            $id_address_delivery = (int)$address['id_address'];
                            $carriers = self::getAvailableCarrierList(
								$product,
								$id_warehouse,
								$id_address_delivery,
								$id_product_attribute
							);
                            if (count($carriers)) {
                                break;
                            }
                        }
                    } else {
                        $carriers = self::getAvailableCarrierList(
							$product,
							$id_warehouse,
							$id_address_delivery,
							$id_product_attribute
						);
                    }
                }
            } else {
                $carriers = self::getAvailableCarrierList(
					$product,
					$id_warehouse,
					$id_address_delivery,
					$id_product_attribute
				);
            }
        } else {
			if (Validate::isLoadedObject($context->customer)) {
				$addresses = $context->customer->getAddresses($context->language->id);
				if (count($addresses) > 1) {
					foreach ($addresses as $address) {
						$id_address_delivery = (int)$address['id_address'];
						$carriers = self::getAvailableCarrierList(
							$product,
							$id_warehouse,
							$id_address_delivery,
							$id_product_attribute
						);
						if (count($carriers)) {
							break;
						}
					}
				} else {
					$carriers = self::getAvailableCarrierList(
						$product,
						$id_warehouse,
						$id_address_delivery,
						$id_product_attribute
					);
				}
			} else {
            	$carriers = self::getAvailableCarrierList(
					$product,
					$id_warehouse,
					$id_address_delivery,
					$id_product_attribute
				);
			}
		}
        return array(
            'id_address_delivery' => empty($carriers) ? 0 : $id_address_delivery,
            'available_carriers' => $carriers,
        );
    }

    public static function getAvailableWarehouseAndCartQuantity(
        $id_product,
        $id_product_attribute,
        $cart = null,
		$update_warehouse_cart = false
    ) {
        $selected_warehouse = array();
        $cart = is_null($cart) ? Context::getContext()->cart : $cart;

        $product = new Product($id_product, false);
        if (Validate::isLoadedObject($product) && $product->advanced_stock_management) {
            // Allow to order the product when out of stock ?
            $allow_oosp = Product::isAvailableWhenOutOfStock(StockAvailable::outOfStock($id_product));
            // Check cart
            $id_address_delivery = null;
			$wanted_quantity = Tools::getIsset('quantity_wanted') && Tools::getValue('quantity_wanted') ? (int)Tools::getValue('quantity_wanted') : 0;
			$isCartValidated = Validate::isLoadedObject($cart);
            if ($isCartValidated) {
                $result = self::productIsPresentInCart($cart->id, $id_product, $id_product_attribute);
                $cart_product = $cart->containsProduct($product->id, $id_product_attribute);
                if ($cart_product) {// && !$original_qty
                    $wanted_quantity = isset($cart_product['quantity']) ? (int)$cart_product['quantity'] : 0;
                }
                /* Init delivery address */
                $id_address_delivery = $cart->id_address_delivery;
            }
            // Check warehouse
            $id_warehouse = null;
            if (isset($result) && $result && (int)$result['id_warehouse'] > 0) {
                $id_warehouse = (int)$result['id_warehouse'];
            } elseif (Tools::getIsset('id_warehouse') && Tools::getValue('id_warehouse')) {
                $id_warehouse = (int)Tools::getValue('id_warehouse');
            } elseif (Tools::getIsset('add_product_warehouse') && Tools::getValue('add_product_warehouse')) {
                $id_warehouse = (int)Tools::getValue('add_product_warehouse'); // Add new order from BO
            } else {
                // this part is used when:
                // - user is not logged in (visitor),
                // - on loading product and cart empty,
                // - when [warehouses dropdown list or best warehouse alert] is not loaded (disabled from config page)
                $warehouses_infos = self::warehousesDataOnProductPage($product->id, true, false, false, false);
                WarehouseStock::takeOffDisabledWarehouses($warehouses_infos);
                if ($warehouses_infos) {
                    /* get associated warehouses for the current product/combination */
                    $warehouses_info = $warehouses_infos[$id_product_attribute];
                    if (!empty($warehouses_info)) {
						/* if we don't allow multi-warehouses in cart */
						$unique_warehouse_in_cart = 0;
						if (!Configuration::get('WKWAREHOUSE_ALLOW_MULTIWH_CART')) {
							$cartProducts = $cart->getProducts();
							if (is_array($cartProducts) && count($cartProducts)) {
								// take the first product to look for its selected warehouse
								foreach ($cartProducts as $row) {
									if ($row['id_product'] != $product->id) {/* more check */
										$productObj = new Product((int)$row['id_product'], false);
										if (Validate::isLoadedObject($productObj) && $productObj->advanced_stock_management) {
											$result = WarehouseStock::productIsPresentInCart(
												$cart->id,
												$productObj->id,
												$row['id_product_attribute']
											);
											// ID warehouse to follow by the others products to add in cart
											if ($result && isset($result['id_warehouse']) && $result['id_warehouse'] > 0) {
												$unique_warehouse_in_cart = (int)$result['id_warehouse'];
												break;
											}
										}
									}
								}
							}
						}
                        foreach ($warehouses_info as $info) {/* loop all sorted warehouses (by stock) to look for the one with carrier */
                            $id_warehouse = (int)$info['id_warehouse'];
                            $available_quantity = (int)$info['available_quantity'];

                            if ((!$allow_oosp && $available_quantity > 0 && $available_quantity >= $wanted_quantity) || $allow_oosp) {
                                $warehouse_carriers = self::getCarriersByCustomerAddresses(/* look for the best carrier */
                                    $product,
									$id_product_attribute,
                                    $id_warehouse,
                                    $id_address_delivery
                                );
                                if (isset($warehouse_carriers['available_carriers']) && count($warehouse_carriers['available_carriers'])) {
                                    $carriers = $warehouse_carriers['available_carriers'];
                                    $id_address_delivery = (int)$warehouse_carriers['id_address_delivery'];
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            // Get secure if warehouse not found, look by stock|warehouses priorities
            if (empty($id_warehouse)) {
                $id_warehouse = WorkshopAsm::findWarehousePriority(array(), true, $id_product, $id_product_attribute, 'desc');
                if (empty($id_warehouse)) {/* not yet associated to any warehouse ? */
                    $selected_warehouse['id_warehouse'] = 0;
                    $selected_warehouse['quantity'] = (int)StockAvailable::getQuantityAvailableByProduct(
                        $id_product,
                        $id_product_attribute
                    );
                    //$selected_warehouse['quantity'] -= $wanted_quantity;
                    $selected_warehouse['id_address_delivery'] = $cart->id_address_delivery;
                }
            } else {
				if (!isset($available_quantity)) {
					$available_quantity = self::getAvailableQuantityByWarehouse(
						$id_product,
						$id_product_attribute,
						$id_warehouse
					);
				}
                if (!isset($warehouses_infos)) {
                    //$available_quantity -= $wanted_quantity;
					// Check always the warehouse quantity compared to the wanted quantity
					if ($isCartValidated && $cart->nbProducts() && !Configuration::get('WKWAREHOUSE_STOCKPRIORITY_DEC') &&
						!$allow_oosp && $available_quantity > 0 && $available_quantity < $wanted_quantity) {
						// Check before whether it's worth to look for another warehouse with enough quantity or not
						$storehouses_infos = self::warehousesDataOnProductPage($product->id, true, false, false, false);
						WarehouseStock::takeOffDisabledWarehouses($storehouses_infos);
						if ($storehouses_infos) {
							$storehouse_info = $storehouses_infos[$id_product_attribute];
							if (!empty($storehouse_info)) {
								foreach ($storehouse_info as $info) {
									if ($info['available_quantity'] > 0 && $info['available_quantity'] >= $wanted_quantity) {
										$storehouse_carriers = self::getCarriersByCustomerAddresses(
											$product,
											$id_product_attribute,
											$info['id_warehouse'],
											$id_address_delivery
										);
										if (count($storehouse_carriers['available_carriers'])) {
											self::removeProductFromWarehouseCart($cart->id, $id_product, $id_product_attribute);						
											//$cart->deleteProduct($id_product, $id_product_attribute);
											//$cart->update();
											 return self::getAvailableWarehouseAndCartQuantity(
												$id_product,
												$id_product_attribute,
												null,
												true
											);
										}
									}
								}
							}
						}
					}
                    /* to get right carriers and delivery address */
                    $available_carriers = self::getCarriersByCustomerAddresses(
						$product,
						$id_product_attribute,
						$id_warehouse,
						$id_address_delivery
					);
                    if (count($available_carriers['available_carriers'])) {
                        $carriers = $available_carriers['available_carriers'];
                        $id_address_delivery = (int)$available_carriers['id_address_delivery'];
                    }
                }
				if ($update_warehouse_cart && $isCartValidated && !$allow_oosp) {
					self::updateProductWarehouseCart(
						$cart->id,
						$id_product,
						$id_product_attribute,
						$id_warehouse
					);
				}
                $selected_warehouse['id_warehouse'] = (int)$id_warehouse;

                //$addresses = self::getCustomerDeliveryAddresses();
                $selected_warehouse['id_address_delivery'] = isset($carriers) && count($carriers) ? (int)$id_address_delivery : 0;
                if ((!$allow_oosp && $available_quantity > 0 && $available_quantity >= $wanted_quantity) || $allow_oosp) {
                    // As long as customer not yet create any delivery address OR user is guest, allow adding to cart
                    $selected_warehouse['quantity'] = (int)$available_quantity;
                    $selected_warehouse['has_carriers'] = isset($carriers) ? count($carriers) : 0;
                } else {
                    $selected_warehouse['quantity'] = 0;
                    $selected_warehouse['has_carriers'] = 0;
                }
            }
        }
        return $selected_warehouse;
    }

    public function getCustomerDeliveryAddresses()
    {
        $context = Context::getContext();
        $addresses = array();
        if (Validate::isLoadedObject($context->customer) && $context->customer->isLogged()) {
            $addresses = $context->customer->getAddresses($context->language->id);
        }
        return $addresses;
    }

    public static function updateProductWarehouseCart($id_cart, $id_product, $id_product_attribute, $id_warehouse)
    {
        if ($id_cart && $id_warehouse) {
            Db::getInstance()->execute(
                'INSERT INTO `'._DB_PREFIX_.'warehouse_cart_product` (
                    `id_cart`, `id_product`, `id_product_attribute`, `id_warehouse`, `date_add`
                 )
                 VALUES (
                    '.(int)$id_cart.', '.(int)$id_product.', '.(int)$id_product_attribute.', '.(int)$id_warehouse.', "'.date('Y-m-d H:i:s').'"
                 )
                 ON DUPLICATE KEY UPDATE `id_warehouse` = VALUES(id_warehouse), `date_add` = VALUES(date_add)'
            );
        }
    }

    public static function removeProductFromWarehouseCart($id_cart, $id_product, $id_product_attribute)
    {
        return Db::getInstance()->delete(
            'warehouse_cart_product',
            '`id_cart` = '.(int)$id_cart.' AND 
             `id_product` = '.(int)$id_product.' AND 
             `id_product_attribute` = '.(int)$id_product_attribute
        );
    }

    /*
    * BO order: when changing delivery address, check carrier availability for that address for each product in cart
    */
    public static function checkAvailabilityCarriersInCart($cart)
    {
        if (Validate::isLoadedObject($cart) && $cart->nbProducts()) {
            foreach ($cart->getProducts() as $row) {
                $id_product = (int)$row['id_product'];
                $id_product_attribute = (int)$row['id_product_attribute'];

                $product = new Product($id_product, false);
                if (Validate::isLoadedObject($product)) {
                    $result = WarehouseStock::productIsPresentInCart($cart->id, $id_product, $id_product_attribute);
                    $id_warehouse = null;
                    if ($product->advanced_stock_management && $result && (int)$result['id_warehouse'] > 0) {
                        $id_warehouse = (int)$result['id_warehouse'];
                    }
                    if (empty(self::getAvailableCarrierList($product, $id_warehouse, null, $id_product_attribute))) {
                        $cart->deleteProduct($id_product, $id_product_attribute);
                        $cart->update();
                    }
                }
            }
        }
    }

    /*
    * Check then validate informations integrity when accessing cart page
    */
    public static function checkCartIntegrity($cart)
    {
        if (Validate::isLoadedObject($cart) && $cart->nbProducts()) {
            foreach ($cart->getProducts() as $row) {
                $id_product = (int)$row['id_product'];
                $id_product_attribute = (int)$row['id_product_attribute'];

                $product = new Product($id_product, false);
                if (Validate::isLoadedObject($product) && $product->advanced_stock_management) {
                    if (!WarehouseStock::productIsPresentInCart($cart->id, $id_product, $id_product_attribute)) {
                        /* IF Not found ! Fix with the best warehouse and best carrier */
                        $warehouses_infos = self::warehousesDataOnProductPage($product->id, true, false, false, false);
                        WarehouseStock::takeOffDisabledWarehouses($warehouses_infos);
                        if (!empty($warehouses_infos)) {
                            /* get associated warehouses for the current product/combination */
                            $warehouses_info = $warehouses_infos[$id_product_attribute];
                            $hasCarrier = false;
                            if (!empty($warehouses_info)) {
                                /* allow to order the product when out of stock? */
                                $allow_oosp = Product::isAvailableWhenOutOfStock(StockAvailable::outOfStock($id_product));
                                /* loop all best warehouses to look for the one that has carrier */
                                foreach ($warehouses_info as $info) {
                                    $id_warehouse = (int)$info['id_warehouse'];
                                    //$available_quantity = (int)$info['available_quantity'] - (int)$row['cart_quantity'];
                                    $available_quantity = (int)$info['available_quantity'];

                                    /* look for the best carrier */
                                    if ((!$allow_oosp && $available_quantity >= $row['cart_quantity']) || $allow_oosp) {
                                        $warehouse_carriers = self::getCarriersByCustomerAddresses(
                                            $product,
											$id_product_attribute,
                                            $id_warehouse,
                                            $cart->id_address_delivery
                                        );
                                        if (count($warehouse_carriers['available_carriers'])) {
                                            $hasCarrier = true;
                                            break;
                                        }
                                    }
                                }
                            }
                            if (!$hasCarrier) {
                                /* remove product from cart */
								if ($cart->containsProduct($id_product, $id_product_attribute)) {
									$cart->deleteProduct($id_product, $id_product_attribute);
									$cart->update();
								}
                            }
                        }
                        if (isset($hasCarrier) && $hasCarrier) {/* add it to the warehouse cart table */
                            self::updateProductWarehouseCart(
                                $cart->id,
                                $id_product,
                                $id_product_attribute,
                                $id_warehouse
                            );
                        }
                    }
                }
            }
        }
    }

    public static function warehousesDataOnProductPage(
        $id_product,
        $display_stock = null,
        $display_locations = null,
        $display_delivery_time = null,
        $display_countries = null
    ) {
        if (is_null($display_stock)) {
            $display_stock = Configuration::get('WKWAREHOUSE_DISPLAY_STOCK_INFOS');
        }
        if (is_null($display_locations)) {
            $display_locations = Configuration::get('WKWAREHOUSE_DISPLAY_LOCATION');
        }
        if (is_null($display_delivery_time)) {
            $display_delivery_time = Configuration::get('WKWAREHOUSE_DISPLAY_DELIVERIES_TIME');
        }
        if (is_null($display_countries)) {
            $display_countries = Configuration::get('WKWAREHOUSE_DISPLAY_COUNTRIES');
        }
        $warehouses_infos = array();

        // Collect All (stock & locations informations)
        if ($display_stock || $display_locations || $display_delivery_time || $display_countries) {
            // Get warehouses locations collections
            $associated_warehouses_collection = StorehouseProductLocation::getCollection($id_product);
			if ($associated_warehouses_collection) {
				foreach ($associated_warehouses_collection as $awc) {
					if (isset($id_pa_processed) && $id_pa_processed == $awc->id_product_attribute) {
						continue;
					}
					$quantities_warehouses_sort = array();
					// If product is using A.S.M, display stock
					if ($display_stock) {
						/* Get available && reserved quantities of each warehouse */
						$quantities_warehouses_sort = self::getWarehousesAvailableQuantities(
							$awc->id_product,
							$awc->id_product_attribute
						);
					}
					// Collect warehouses locations && delivery times && countries (if options are enabled)
					if ($display_locations || $display_delivery_time || $display_countries) {
						if (empty($quantities_warehouses_sort)) {
							$quantities_warehouses_sort = StoreHouse::getProductWarehouseList(
								$awc->id_product,
								$awc->id_product_attribute,
								true // only with stock association
							);
						}
					}
					$warehouses_infos[$awc->id_product_attribute] = $quantities_warehouses_sort;
					$id_pa_processed = $awc->id_product_attribute;
				}
			}
        }
        return $warehouses_infos;
    }

    public static function takeOffOutOfStockWarehouses($id_product, &$warehouses_infos)
    {
        // Don't show out of stock warehouses
		// _PS_ADMIN_DIR_ : allow adding out-of-stock products from backoffice only
		// when "Show out of stock warehouses" option is disabled from config page
        if (!defined('_PS_ADMIN_DIR_') && !Configuration::get('WKWAREHOUSE_SHOW_OUTOFSTOCK') && count($warehouses_infos)) {
            foreach ($warehouses_infos as $id_product_attribute => $warehouse_infos) {
                foreach ($warehouse_infos as $key => $info) {
                    if (isset($info['available_quantity'])) {
                        $available_quantity = (int)$info['available_quantity'];
                    } else {
                        $available_quantity = self::getAvailableQuantityByWarehouse(
                            $id_product,
                            $id_product_attribute,
                            $info['id_warehouse']
                        );
                    }
                    if ($available_quantity <= 0) {
                        unset($warehouses_infos[$id_product_attribute][$key]);
                    }
                }
				// reconstruct warehouses_infos indexes
				if ($warehouses_infos) {
					foreach ($warehouses_infos as $id_product_attribute => $warehouse_infos) {
						$warehouses_infos[$id_product_attribute] = array_values($warehouse_infos);
					}
				}
            }
        }
    }

    public static function takeOffDisabledWarehouses(&$warehouses_infos)
    {
        if (!defined('_PS_ADMIN_DIR_') && StoreHouse::hasHiddenWarehouses() > 0) {
            foreach ($warehouses_infos as $k => $infos) {
                $reindex_keys = false;
                foreach ($infos as $key => $info) {
                    if (!(new StoreHouse((int)$info['id_warehouse']))->active) {
                        unset($warehouses_infos[$k][$key]);
                        $reindex_keys = true;
                    }
                }
                /* use array_values to re index the array numerically */
                if ($reindex_keys) {
                    $warehouses_infos[$k] = array_values($warehouses_infos[$k]);
                }
            }
        }
    }

    /**
     * Get last Product in Cart.
     *
     * @return bool|mixed Database result
     */
    public static function getLastCartProduct($id_cart, $id_product = null, $id_product_attribute = null)
    {
        return Db::getInstance()->getRow(
            'SELECT *
             FROM `'._DB_PREFIX_.'cart_product`
             WHERE `id_cart` = '.(int)$id_cart
             .(!is_null($id_product) ? ' AND `id_product` = '.(int)$id_product : '')
             .(!is_null($id_product_attribute) ? ' AND `id_product_attribute` = '.(int)$id_product_attribute : '')
             .' ORDER BY `date_add` DESC'
        );
    }

    public static function updateCartProduct(
        $id_cart,
        $new_id_address_delivery,
        $id_product = null,
        $id_product_attribute = null
    ) {
        Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'cart_product` SET
             `id_address_delivery` = '.(int)$new_id_address_delivery
             .' WHERE `id_cart` = '.(int)$id_cart
             .(!is_null($id_product) ? ' AND `id_product` = '.(int)$id_product : '')
             .(!is_null($id_product_attribute) ? ' AND `id_product_attribute` = '.(int)$id_product_attribute : '')
        );
    }

    public static function updateCartDeliveryAddress(
        $id_cart,
        $new_id_address_delivery,
        $update_invoice_address = false
    ) {
        Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'cart` SET
             `id_address_delivery` = '.(int)$new_id_address_delivery
             .($update_invoice_address ? ', `id_address_invoice` = '.(int)$new_id_address_delivery : '')
             .' WHERE `id_cart` = '.(int)$id_cart
        );
    }

    public static function getNumberOfAsmProductsInCart($id_cart, $count_warehouses = false)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT COUNT('.($count_warehouses ? 'DISTINCT(`id_warehouse`)' : '*').')
             FROM `'._DB_PREFIX_.'warehouse_cart_product` wc
             INNER JOIN `'._DB_PREFIX_.'cart_product` cp ON (
                cp.`id_product` = wc.`id_product` AND 
                cp.`id_product_attribute` = wc.`id_product_attribute` AND
				cp.`id_cart` = wc.`id_cart`
             )
             WHERE wc.`id_cart` = '.(int)$id_cart
        );
    }

    public static function isMultiShipping($cart)
    {
        if (Configuration::get('WKWAREHOUSE_ALLOW_MULTICARRIER_CART')) {
            $products = $cart->getProducts();
            /*// if no ASM products in cart
            if (self::getNumberOfAsmProductsInCart($cart->id) <= 0) {
                return false;
            }*/
            if (is_array($products) && count($products) > 1) {
                $multiCarriersInCart = false;
                $common_carriers = array();
                foreach ($products as $product) {
                    $result = self::productIsPresentInCart(
                        $cart->id,
                        $product['id_product'],
                        $product['id_product_attribute']
                    );

                    $carriers_list = self::getAvailableCarrierList(
                        (new Product($product['id_product'], false)),
                        ($result && $result['id_warehouse'] > 0 ? (int)$result['id_warehouse'] : null),
                        (int)$product['id_address_delivery'],/* according to the right assigned delivery address */
						$product['id_product_attribute']
                    );
                    // No carrier? => this product needs its own delivery address to be created so propose to customer
                    if (empty($carriers_list)) {
                        $multiCarriersInCart = true;
                        break;
                    }
                    if (empty($common_carriers)) {
                        $common_carriers = $carriers_list;
                        continue;/* loop and ignore the next code */
                    }
                    if (!array_intersect($common_carriers, $carriers_list)) {
                        $multiCarriersInCart = true;
                        break;
                    } else {
                        $common_carriers = array_intersect($common_carriers, $carriers_list);
                    }
                }
                if ($multiCarriersInCart) {
                    /*if (Configuration::get('WKWAREHOUSE_ALLOW_MULTI_ADDRESSES')) {
                        // Ensure Shipping Cost (with its own tax) For Each Product in cart
                        Db::getInstance()->execute(
                            'UPDATE `'._DB_PREFIX_.'cart` SET `id_address_delivery` = 0 WHERE `id_cart` = '.(int)$cart->id
                        );
                    }*/
                    return true;
                }
            }
        }
        return false;
    }

    public static function getAvailableCarrierList(
        Product $product,
        $id_warehouse,
        $id_address_delivery = null,
        $id_product_attribute = null,
        $cart = null,
        &$error = array()
    ) {
        static $ps_country_default = null;

        if ($ps_country_default === null) {
            $ps_country_default = Configuration::get('PS_COUNTRY_DEFAULT');
        }
        $id_shop = Context::getContext()->shop->id;
        if (null === $cart) {
            $cart = Context::getContext()->cart;
        }
        if (null === $error || !is_array($error)) {
            $error = array();
        }
        if ($id_warehouse) {
            $warehouse = new StoreHouse($id_warehouse);
        }

        $id_address = (int)(($id_address_delivery !== null && $id_address_delivery != 0) ? $id_address_delivery : $cart->id_address_delivery);
        if ($id_address) {
            $id_zone = Address::getZoneById($id_address);
            // Check the country of the address is activated
            if (!Address::isCountryActiveById($id_address)) {
                return array();
            }
        } else {
            // If visitor
            /*if (isset(Context::getContext()->cookie) && Context::getContext()->cookie &&
				isset(Context::getContext()->cookie->iso_code_country) && Context::getContext()->cookie->iso_code_country){
                $country = new Country(Country::getByIso(Context::getContext()->cookie->iso_code_country));
                $id_zone = $country->id_zone;
            } else*/if (isset($warehouse)) {
                $id_address = (int)$warehouse->id_address;
                $id_zone = Address::getZoneById($id_address);
                if (!Address::isCountryActiveById($id_address)) {
                    $id_zone = null;
                }
            }
            if (!isset($id_zone)) {/* get the default */
                $country = new Country($ps_country_default);
                $id_zone = $country->id_zone;
            }
        }

        // Does the product is linked with carriers?
        $cache_id = 'WarehouseStock::getAvailableCarrierList_'.(int)$product->id.'-'.(int)$id_shop;
        if (!Cache::isStored($cache_id)) {
            $query = new DbQuery();
            $query->select('id_carrier');
            $query->from('product_carrier', 'pc');
            $query->innerJoin(
                'carrier',
                'c',
                'c.id_reference = pc.id_carrier_reference AND c.deleted = 0 AND c.active = 1'
            );
            $query->where('pc.id_product = '.(int)$product->id);
            $query->where('pc.id_shop = '.(int)$id_shop);

            $carriers_for_product = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
            Cache::store($cache_id, $carriers_for_product);
        } else {
            $carriers_for_product = Cache::retrieve($cache_id);
        }

        $carrier_list = array();
        if (!empty($carriers_for_product)) {
            //the product is linked with carriers
            foreach ($carriers_for_product as $carrier) { //check if the linked carriers are available in current zone
                if (Carrier::checkCarrierZone($carrier['id_carrier'], $id_zone)) {
                    $carrier_list[$carrier['id_carrier']] = $carrier['id_carrier'];
                }
            }
            if (empty($carrier_list)) {
                return array();
            }//no linked carrier are available for this zone
        }

        // The product is not directly linked with a carrier
        // Get all the carriers linked to a warehouse
        if (isset($warehouse)) {
            $warehouse_carrier_list = $warehouse->getCarriers();
        }
        $available_carrier_list = array();
        $cache_id = 'WarehouseStock::getAvailableCarrierList_getCarriersForOrder_'.(int)$id_zone.'-'.(int)$cart->id;
        if (!Cache::isStored($cache_id)) {
            $customer = new Customer($cart->id_customer);
            $carrier_error = array();
            $carriers = Carrier::getCarriersForOrder($id_zone, $customer->getGroups(), $cart, $carrier_error);
            Cache::store($cache_id, array($carriers, $carrier_error));
        } else {
            list($carriers, $carrier_error) = Cache::retrieve($cache_id);
        }

        $error = array_merge($error, $carrier_error);

        foreach ($carriers as $carrier) {
            $available_carrier_list[$carrier['id_carrier']] = $carrier['id_carrier'];
        }

        if ($carrier_list) {
            $carrier_list = array_intersect($available_carrier_list, $carrier_list);
        } else {
            $carrier_list = $available_carrier_list;
        }

        if (isset($warehouse_carrier_list)) {
            $carrier_list = array_intersect($carrier_list, $warehouse_carrier_list);
        }

		// Added 16 Oct 2023
        $cart_quantity = 0;
        $cart_weight = 0;
		if ($id_product_attribute) {
			foreach ($cart->getProducts(false, false) as $cart_product) {
				if ($cart_product['id_product'] == $product->id && $cart_product['id_product_attribute'] == $id_product_attribute) {
					$cart_quantity += $cart_product['cart_quantity'];
					if (isset($cart_product['weight_attribute']) && $cart_product['weight_attribute'] > 0) {
						$cart_weight += ($cart_product['weight_attribute'] * $cart_product['cart_quantity']);
					} else {
						$cart_weight += ($cart_product['weight'] * $cart_product['cart_quantity']);
					}
					break;
				}
			}
		} else {
			foreach ($cart->getProducts(false, false) as $cart_product) {
				if ($cart_product['id_product'] == $product->id) {
					$cart_quantity += $cart_product['cart_quantity'];
				}
				if (isset($cart_product['weight_attribute']) && $cart_product['weight_attribute'] > 0) {
					$cart_weight += ($cart_product['weight_attribute'] * $cart_product['cart_quantity']);
				} else {
					$cart_weight += ($cart_product['weight'] * $cart_product['cart_quantity']);
				}
			}
		}
		// -----------------------

        if ($product->width > 0 ||
            $product->height > 0 ||
            $product->depth > 0 ||
            $product->weight > 0 ||
            $cart_weight > 0
        ) {
            foreach ($carrier_list as $key => $id_carrier) {
                $carrier = new Carrier($id_carrier);

                // Get the sizes of carrier and product and sort them to check if the carrier can take the product.
                $carrier_sizes = array((int)$carrier->max_width, (int)$carrier->max_height, (int)$carrier->max_depth);
                $product_sizes = array((int)$product->width, (int)$product->height, (int)$product->depth);
                rsort($carrier_sizes, SORT_NUMERIC);
                rsort($product_sizes, SORT_NUMERIC);

                if (($carrier_sizes[0] > 0 && $carrier_sizes[0] < $product_sizes[0]) ||
                    ($carrier_sizes[1] > 0 && $carrier_sizes[1] < $product_sizes[1]) ||
                    ($carrier_sizes[2] > 0 && $carrier_sizes[2] < $product_sizes[2])) {
                    $error[$carrier->id] = Carrier::SHIPPING_SIZE_EXCEPTION;
                    unset($carrier_list[$key]);
                }

                if ($carrier->max_weight > 0 &&
                    ($carrier->max_weight < $product->weight * $cart_quantity || $carrier->max_weight < $cart_weight)) {
                    $error[$carrier->id] = Carrier::SHIPPING_WEIGHT_EXCEPTION;
                    unset($carrier_list[$key]);
                }
            }
        }
        return $carrier_list;
    }

    /*
    * Get the best carrier For not ASM product
    * Usefullif product in cart can't find an appropriate delivery address (not yet created)
    * Propose the found zone to the guest/customer
    */
    public static function getBestAvailableProductCarrier($id_product)
    {
        $id_shop = Context::getContext()->shop->id;
        $cart = Context::getContext()->cart;
        $customer = new Customer($cart->id_customer);
        $groups = $customer->getGroups();
        $product = new Product($id_product);

        // Does the product is linked with carriers?
        $cache_id = 'WarehouseStock::getBestAvailableProductCarrier_'.(int)$id_product.'-'.(int)$id_shop;
        if (!Cache::isStored($cache_id)) {
            $query = new DbQuery();
            $query->select('id_carrier');
            $query->from('product_carrier', 'pc');
            $query->innerJoin(
                'carrier',
                'c',
                'c.id_reference = pc.id_carrier_reference AND c.deleted = 0 AND c.active = 1'
            );
            $query->where('pc.id_product = '.(int)$id_product);
            $query->where('pc.id_shop = '.(int)$id_shop);
            $carriers_for_product = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
            Cache::store($cache_id, $carriers_for_product);
        } else {
            $carriers_for_product = Cache::retrieve($cache_id);
        }
        $carrier_list = array();
        if (!empty($carriers_for_product)) {
            foreach ($carriers_for_product as $carrier) {
                $carrier_list[$carrier['id_carrier']] = $carrier['id_carrier'];
            }
        }

        // Calculate now for each carrier its shipping cost and select the cheapest one
        $best_carrier = array();
        if (!empty($carrier_list)) {
            $carriers_prices = array();
            foreach ($carrier_list as $id_carrier) {
                $carriers_zones = (new Carrier($id_carrier))->getZones();
                $shipping_prices = array();
                foreach ($carriers_zones as $carrier_zone) {
                    if ($carrier_zone['active']) {
                        $id_zone = (int)$carrier_zone['id_zone'];
                        $available_carrier_list = array();
                        foreach (Carrier::getCarriersForOrder($id_zone, $groups, $cart) as $carrier) {
                            $available_carrier_list[$carrier['id_carrier']] = $carrier['id_carrier'];
                        }

                        $common_carrier = array_intersect($available_carrier_list, array($id_carrier => $id_carrier));
                        if (is_array($common_carrier)) {
                            $cost = self::getShippingPriceForProduct($product, $id_zone, $id_carrier, $cart->id_currency);
                            if ($cost !== false) {
                                $shipping_prices[] = array(
                                    'id_zone' => $id_zone,
                                    'name_zone' => $carrier_zone['name'],
                                    'shipping_price' => $cost
                                );
                            }
                        }
                    }
                }
                if (count($shipping_prices)) {/* get the best shipping price */
                    array_multisort(array_column($shipping_prices, 'shipping_price'), SORT_ASC, $shipping_prices);
                    $carriers_prices[] = array_merge(
                        array('id_carrier' => (int)$id_carrier),
                        current($shipping_prices)
                    );
                }
            }
            if (count($carriers_prices)) {/* get the best carrier that have best shipping price */
                array_multisort(array_column($carriers_prices, 'shipping_price'), SORT_ASC, $carriers_prices);
                $best_carrier = $carriers_prices[key($carriers_prices)];
            }
        }
        return $best_carrier;
    }

	/*
	* Check carriers for each package (according to the total weight and price for the products inside each package).
	* This check has been removed from Carrier::getCarriersForOrder function and moved here.
	* This is accessible only if the order is multi-packages otherwise it has no sense.
	*/
    public static function getCarriersForPackageOrder($package_list, $cart)
    {
        $context = Context::getContext();
        if (isset($context->currency)) {
            $id_currency = $context->currency->id;
        }
        foreach ($package_list as $id_address => $packages) {
			foreach ($packages as $id_package => $package) {
				$package_carriers = $package['carrier_list'];
				$product_list = $package['product_list'];
				foreach ($package_carriers as $k => $id_carrier) {
					$carrier = new Carrier((int)$id_carrier);
					$shipping_method = $carrier->getShippingMethod();
					if ($shipping_method != Carrier::SHIPPING_METHOD_FREE) {
						// If out-of-range behavior carrier is set on "Desactivate carrier"
						if ($carrier->range_behavior) {
							// Get id zone
							if (!isset($id_zone)) {
								$id_zone = (int)Country::getIdZone($context->country->id);
							}
							// Get only carriers that have a range compatible with products in this package
							if ($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT && 
								(!Carrier::checkDeliveryPriceByWeight($id_carrier, $cart->getTotalWeight($product_list), $id_zone))) {
								unset($package_carriers[$k]);
								continue;
							}
							if ($shipping_method == Carrier::SHIPPING_METHOD_PRICE &&
								(!Carrier::checkDeliveryPriceByPrice($id_carrier, $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING, $product_list), $id_zone, $id_currency))) {
								unset($package_carriers[$k]);
								continue;
							}
						}
					}
				}
				$package_list[$id_address][$id_package]['carrier_list'] = $package_carriers;
			}
        }
        return $package_list;
    }

    public static function resetDeliveryOption()
    {
        $cart = Context::getContext()->cart;
        if (Validate::isLoadedObject($cart) && !empty($cart->delivery_option)) {
            Db::getInstance()->execute(
                'UPDATE `'._DB_PREFIX_.'cart` SET 
                `delivery_option` = \'\',
                `id_carrier` = 0
                 WHERE `id_cart` = '.(int)$cart->id
            );
        }
    }

    /*
    * Get shipping cost by carrier and zone for a given product
    */
    public static function getShippingPriceForProduct($product, $id_zone, $id_carrier, $id_currency)
    {
        $carrier = new Carrier($id_carrier);

        if (($carrier->max_width > 0 && $product->width > $carrier->max_width) ||
            ($carrier->max_height > 0 && $product->height > $carrier->max_height) ||
            ($carrier->max_depth > 0 && $product->depth > $carrier->max_depth) ||
            ($carrier->max_weight > 0 && $product->weight > $carrier->max_weight)) {
            return false;
        }

        if ($carrier->shipping_method == 0) {
            if (Configuration::get('PS_SHIPPING_METHOD') == 1) {
                if ($carrier->range_behavior == 1 && !Carrier::checkDeliveryPriceByWeight($id_carrier, $product->weight, $id_zone)) {
                    return false;
                }
                $price = $carrier->getDeliveryPriceByWeight($product->weight, $id_zone);
            } else {
                if ($carrier->range_behavior == 1 && !Carrier::checkDeliveryPriceByPrice(
                    $id_carrier,
                    $product->price,
                    $id_zone,
                    $id_currency
                )) {
                    return false;
                }
                $price = $carrier->getDeliveryPriceByPrice($product->price, $id_zone);
            }
        } elseif ($carrier->shipping_method == 1) {
            if ($carrier->range_behavior == 1 && !Carrier::checkDeliveryPriceByWeight($id_carrier, $product->weight, $id_zone)
            ) {
                return false;
            }
            $price = $carrier->getDeliveryPriceByWeight($product->weight, $id_zone);
        } elseif ($carrier->shipping_method == 2) {
            if ($carrier->range_behavior == 1 && !Carrier::checkDeliveryPriceByPrice(
                $id_carrier,
                $product->price,
                $id_zone,
                $id_currency
            )) {
                return false;
            }
            $price = $carrier->getDeliveryPriceByPrice($product->price, $id_zone);
        } else {// shipping free
            return 0;
        }

        if ($carrier->shipping_handling == 1) {
            $price += Configuration::get('PS_SHIPPING_HANDLING');
        }
        $taxrate = Tax::getCarrierTaxRate($id_carrier);
        $price += $price * $taxrate / 100;

        return $price;
    }

    public static function getUniquePackageList($packageList)
    {
        $packages_addresses_affected = array();
        foreach ($packageList as $id_address => $packages) {
            if (count($packages) > 1) {
                $packages_warehouses = $packages_carriers = array();
                $carriersCommunArray = array();
                $i = 1;
                foreach ($packages as $id_package => $package) {
                    if (!empty($package['id_warehouse'])) {
                        $packages_warehouses[] = (int)$package['id_warehouse'];
                    }
                    if ($i == 1) {
                        $packages_carriers = $package['carrier_list'];
                    } else {// Look for common carrier automatically for all products
                        $carriersCommunArray = array_intersect(
                            !empty($packages_carriers) ? $packages_carriers : $carriersCommunArray,
                            $package['carrier_list']
                        );
                        unset($packages_carriers);
                    }
                    $i++;
                }
                // if warehouse(s) and common carrier
                if (!empty($packages_warehouses) && count($packages_warehouses) >= 1) {
                    $carriersCommunArray = array_filter(array_unique($carriersCommunArray));
                    if (!empty($carriersCommunArray)) {
                        $packages_addresses_affected[$id_address] = $carriersCommunArray;
                    }
                }
            }
        }
        // If product(s) stored in warehouses, continue
        if (!empty($packages_addresses_affected)) {
			if (count($packages_addresses_affected)) {// generate one package
				foreach ($packages_addresses_affected as $id_address => $common_carriers) {
					$packages = $packageList[$id_address];
					$packageList[$id_address][0]['carrier_list'] = $common_carriers;
					$packageList[$id_address][0]['warehouse_list'] = array();
					$packageList[$id_address][0]['id_warehouse'] = 0;
					foreach ($packages as $id_package => $package) {
						// Assign the warehouse ID (if there is) regarding the first package
						if ($id_package == 0) {
							foreach ($package['product_list'] as $k => $product) {
								if (isset($packages[0]['id_warehouse']) && $packages[0]['id_warehouse']) {
									$packageList[$id_address][0]['product_list'][$k]['id_warehouse'] = $packages[0]['id_warehouse'];
								}
							}
						} else {
							// Merge all other products of the other packages inti the first package
							foreach ($package['product_list'] as $product) {
								// Affect the warehouse ID (if there is)
								if (isset($packages[$id_package]['id_warehouse']) && $packages[$id_package]['id_warehouse']) {
									$product['id_warehouse'] = $packages[$id_package]['id_warehouse'];
								}
								$packageList[$id_address][0]['product_list'][] = $product;
							}
							unset($packageList[$id_address][$id_package]);
						}
					}
					if (empty($packageList[$id_address][0]['id_carrier']) && !empty($common_carriers)) {
						$packageList[$id_address][0]['id_carrier'] = $common_carriers[key($common_carriers)];
					}
				}
			}
        }
        return $packageList;
    }

/*
    public static function getBestProductCarrierForOrder($id_zone, $groups = null, $cart = null, &$error = array())
    {
        $context = Context::getContext();
        $id_lang = $context->language->id;
        if (null === $cart) {
            $cart = $context->cart;
        }
        if (isset($context->currency)) {
            $id_currency = $context->currency->id;
        }

        if (is_array($groups) && !empty($groups)) {
            $result = Carrier::getCarriers($id_lang, true, false, (int) $id_zone, $groups, self::PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
        } else {
            $result = Carrier::getCarriers($id_lang, true, false, (int) $id_zone, array(Configuration::get('PS_UNIDENTIFIED_GROUP')), self::PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE);
        }
        $results_array = array();

        foreach ($result as $k => $row) {
            $carrier = new Carrier((int) $row['id_carrier']);
            $shipping_method = $carrier->getShippingMethod();
            if ($shipping_method != Carrier::SHIPPING_METHOD_FREE) {
                // Get only carriers that are compliant with shipping method
                if (($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT && $carrier->getMaxDeliveryPriceByWeight($id_zone) === false)) {
                    $error[$carrier->id] = Carrier::SHIPPING_WEIGHT_EXCEPTION;
                    unset($result[$k]);

                    continue;
                }
                if (($shipping_method == Carrier::SHIPPING_METHOD_PRICE && $carrier->getMaxDeliveryPriceByPrice($id_zone) === false)) {
                    $error[$carrier->id] = Carrier::SHIPPING_PRICE_EXCEPTION;
                    unset($result[$k]);

                    continue;
                }

                // If out-of-range behavior carrier is set to "Deactivate carrier"
                if ($row['range_behavior']) {
                    // Get id zone
                    if (!$id_zone) {
                        $id_zone = (int) Country::getIdZone(Country::getDefaultCountryId());
                    }

                    // Get only carriers that have a range compatible with cart
                    if ($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT
                        && (!Carrier::checkDeliveryPriceByWeight($row['id_carrier'], $cart->getTotalWeight(), $id_zone))) {
                        $error[$carrier->id] = Carrier::SHIPPING_WEIGHT_EXCEPTION;
                        unset($result[$k]);

                        continue;
                    }

                    if ($shipping_method == Carrier::SHIPPING_METHOD_PRICE
                        && (!Carrier::checkDeliveryPriceByPrice($row['id_carrier'], $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING), $id_zone, $id_currency))) {
                        $error[$carrier->id] = Carrier::SHIPPING_PRICE_EXCEPTION;
                        unset($result[$k]);

                        continue;
                    }
                }
            }

            $row['name'] = ((string) ($row['name']) != '0' ? $row['name'] : Carrier::getCarrierNameFromShopName());
            $row['price'] = (($shipping_method == Carrier::SHIPPING_METHOD_FREE) ? 0 : $cart->getPackageShippingCost((int) $row['id_carrier'], true, null, null, $id_zone));
            $row['price_tax_exc'] = (($shipping_method == Carrier::SHIPPING_METHOD_FREE) ? 0 : $cart->getPackageShippingCost((int) $row['id_carrier'], false, null, null, $id_zone));
            $row['img'] = file_exists(_PS_SHIP_IMG_DIR_ . (int) $row['id_carrier'] . '.jpg') ? _THEME_SHIP_DIR_ . (int) $row['id_carrier'] . '.jpg' : '';

            // If price is false, then the carrier is unavailable (carrier module)
            if ($row['price'] === false) {
                unset($result[$k]);

                continue;
            }
            $results_array[] = $row;
        }

        // if we have to sort carriers by price
        $prices = array();
        if (Configuration::get('PS_CARRIER_DEFAULT_SORT') == Carrier::SORT_BY_PRICE) {
            foreach ($results_array as $r) {
                $prices[] = $r['price'];
            }
            if (Configuration::get('PS_CARRIER_DEFAULT_ORDER') == Carrier::SORT_BY_ASC) {
                array_multisort($prices, SORT_ASC, SORT_NUMERIC, $results_array);
            } else {
                array_multisort($prices, SORT_DESC, SORT_NUMERIC, $results_array);
            }
        }

        return $results_array;
    }
    */
}
