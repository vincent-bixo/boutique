<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
class Cart extends CartCore
{
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /*
    * module: wkwarehouses
    * date: 2024-11-29 01:44:32
    * version: 1.85.40
    */

    
    
    
    
    
    
    
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
    public function checkQuantities($returnProductOnFailure = false)
    {
        if (!Module::isEnabled('wkwarehouses') || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            return parent::checkQuantities($returnProductOnFailure);
        }
        if (Configuration::isCatalogMode() && !defined('_PS_ADMIN_DIR_')) {
            return false;
        }
        if (!class_exists('StoreHouse')) {
            require_once(dirname(__FILE__).'/../../modules/wkwarehouses/classes/Warehouse.php');
            require_once(dirname(__FILE__).'/../../modules/wkwarehouses/classes/WarehouseStock.php');
            require_once(dirname(__FILE__).'/../../modules/wkwarehouses/classes/WorkshopAsm.php');
        }
        WarehouseStock::isMultiShipping($this);
        foreach ($this->getProducts() as $product) {
            $id_product = (int)$product['id_product'];
            $id_product_attribute = (int)$product['id_product_attribute'];
            if (!isset($product['allow_oosp'])) {
                $product['allow_oosp'] = Product::isAvailableWhenOutOfStock($product['out_of_stock']);
            }
            if (!$this->allow_seperated_package && !$product['allow_oosp'] &&
                $product['advanced_stock_management'] &&
                ($delivery = $this->getDeliveryOption()) && !empty($delivery)) {
                
                if (empty(WorkshopAsm::getAssociatedWarehousesArray($id_product, $id_product_attribute))) {
                    return $returnProductOnFailure ? $product : false;
                }
                $result = WarehouseStock::getAvailableWarehouseAndCartQuantity($id_product, $id_product_attribute, $this);
                $product['stock_quantity'] = ($result ? (int)$result['quantity'] : 0);
            }
            if (!$product['active'] ||
                !$product['available_for_order'] ||
                (!$product['allow_oosp'] && $product['stock_quantity'] < $product['cart_quantity'])) {
                return $returnProductOnFailure ? $product : false;
            }
            if (!$product['allow_oosp'] && version_compare(_PS_VERSION_, '1.7.3.2', '>=') === true) {
                $productQuantity = Product::getQuantity(
                    $id_product,
                    $id_product_attribute,
                    null,
                    $this,
                    $product['id_customization']
                );
                if ($productQuantity < 0) {
                    return $returnProductOnFailure ? $product : false;
                }
            }
        }
        return true;
    }
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
    public function getCarriersIntersection($packages_carriers, $id_package)
    {
        $array_search = $packages_carriers[$id_package];
        $target = $id_package - 1;
        if ($target == -1) {
            return null;
        }
        for ($i = $target; $i >= 0; --$i) {
            if (array_intersect($array_search, $packages_carriers[$i])) {
                return $i;
            }
        }
        return false;
    }
    
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
    public function getPackageList($flush = false)
    {
        if (Module::isEnabled('wkwarehouses') && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
			if (!class_exists('WarehouseStock')) {
				require_once(dirname(__FILE__).'/../../modules/wkwarehouses/classes/Warehouse.php');
				require_once(dirname(__FILE__).'/../../modules/wkwarehouses/classes/WarehouseStock.php');
				require_once(dirname(__FILE__).'/../../modules/wkwarehouses/classes/WorkshopAsm.php');
			}
			WarehouseStock::checkCartIntegrity($this);
			if (WarehouseStock::getNumberOfAsmProductsInCart($this->id) > 0) {
				$package_list = $this->getMyPackageList($flush);
				
				$package_list = WarehouseStock::getUniquePackageList($package_list);
				$multi_packages = false;
				if ($package_list) {
					foreach ($package_list as $id_address => $packages) {
						if (count($packages) > 1) {
							$multi_packages = true;
							break;
						}
					}
				}
				if ($multi_packages) {
					$sorted_packages_list = array();
					foreach ($package_list as $id_address => $packages) {
						if (count($packages) > 1) {
							$packages_warehouses = array_column($packages, 'warehouse_list');
							$ids_warehouses = array();
							foreach ($packages_warehouses as $array_warehouse) {
								$ids_warehouses[] = (int)key($array_warehouse);
							}
							if (count($packages) == count($ids_warehouses)) {
								$sorted_packages_list[$id_address] = $package_list[$id_address];
							} else {
								$packages_carriers = array_column($packages, 'carrier_list');
								krsort($packages_carriers); // sort by keys desc
								foreach ($packages_carriers as $id_package => $carriers) {
									$index = $this->getCarriersIntersection($packages_carriers, $id_package);
									if ($index === false || $index === null) {
										$id_package = ($index === null ? 0 : $id_package);
										if (!isset($sorted_packages_list[$id_address][$id_package])) {
											$sorted_packages_list[$id_address][$id_package] = $package_list[$id_address][$id_package];
										}
									} else {
										if (isset($sorted_packages_list[$id_address][$id_package])) {
											$package_index = $sorted_packages_list[$id_address][$id_package];
											unset($sorted_packages_list[$id_address][$id_package]);
										} else {
											$package_index = $packages[$id_package];
										}
										$commonCarriers = array_intersect($packages[$index]['carrier_list'], $package_index['carrier_list']);
										$sorted_packages_list[$id_address][$index]['product_list'] = array_merge($packages[$index]['product_list'], $package_index['product_list']);
										$sorted_packages_list[$id_address][$index]['carrier_list'] = $commonCarriers;
										$sorted_packages_list[$id_address][$index]['warehouse_list'] = array_intersect($packages[$index]['warehouse_list'], $package_index['warehouse_list']);
										$sorted_packages_list[$id_address][$index]['id_warehouse'] = 0;
										$packages_carriers[$index] = $commonCarriers;
									}
								}
								ksort($sorted_packages_list[$id_address]); // sort asc
							}
						} else {
							$sorted_packages_list[$id_address] = $package_list[$id_address];
						}
					}
					$package_list = $sorted_packages_list;
					$package_list = WarehouseStock::getCarriersForPackageOrder($package_list, $this);
				}
				if (isset($package_list)) {
					$packageList = $package_list;
				}
			} else {
				$packageList = parent::getPackageList($flush);
			}
		} else {
        	$packageList = parent::getPackageList($flush);
		}
        return $packageList;
    }
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
    protected static $cachePackageList = [];
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
    public function getMyPackageList($flush = false)
    {
        $cache_key = (int)$this->id . '_' . (int)$this->id_address_delivery;
        if (isset(static::$cachePackageList[$cache_key]) && static::$cachePackageList[$cache_key] !== false && !$flush) {
            return static::$cachePackageList[$cache_key];
        }
        $product_list = $this->getProducts($flush);
        $warehouse_count_by_address = array();
        foreach ($product_list as &$product) {
            if ((int)$product['id_address_delivery'] == 0 || !Configuration::get('WKWAREHOUSE_ALLOW_MULTI_ADDRESSES')) {
                $product['id_address_delivery'] = (int)$this->id_address_delivery;
				if (!Configuration::get('WKWAREHOUSE_ALLOW_MULTI_ADDRESSES')) {
					Db::getInstance()->execute(
						'UPDATE `'._DB_PREFIX_.'cart_product` SET
						 `id_address_delivery` = '.(int)$this->id_address_delivery
						 .' WHERE `id_cart` = '.(int)$this->id
					);
				}
            }
            if (!isset($warehouse_count_by_address[$product['id_address_delivery']])) {
                $warehouse_count_by_address[$product['id_address_delivery']] = array();
            }
            $product['warehouse_list'] = array();
            if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && (int)$product['advanced_stock_management'] == 1) {
                $rs = WarehouseStock::productIsPresentInCart(
                    $this->id,
                    $product['id_product'],
                    $product['id_product_attribute']
                );
                if ($rs) {
                    $warehouse_list = array(0 => array('id_warehouse' => (int)$rs['id_warehouse']));
                } else {
                    $warehouse_list = StoreHouse::getProductWarehouseList(
						$product['id_product'],
						$product['id_product_attribute'],
						false
					);
                }
                $warehouse_in_stock = array();
                foreach ($warehouse_list as $key => $warehouse) {
                    $product_real_quantities = WarehouseStock::getAvailableQuantityByWarehouse(
                        $product['id_product'],
                        $product['id_product_attribute'],
                        $warehouse['id_warehouse']
                    );
                    if ($product_real_quantities > 0 || Pack::isPack((int)$product['id_product'])) {
                        $warehouse_in_stock[] = $warehouse;
                    }
                }
                if (!empty($warehouse_in_stock)) {
                    $warehouse_list = $warehouse_in_stock;
                    $product['in_stock'] = true;
                } else {
                    $product['in_stock'] = false;
                }
            } else {
                $warehouse_list = array(0 => array('id_warehouse' => 0));
                $product['in_stock'] = StockAvailable::getQuantityAvailableByProduct($product['id_product'], $product['id_product_attribute']) > 0;
            }
            foreach ($warehouse_list as $warehouse) {
                $product['warehouse_list'][$warehouse['id_warehouse']] = $warehouse['id_warehouse'];
                if (!isset($warehouse_count_by_address[$product['id_address_delivery']][$warehouse['id_warehouse']])) {
                    $warehouse_count_by_address[$product['id_address_delivery']][$warehouse['id_warehouse']] = 0;
                }
                ++$warehouse_count_by_address[$product['id_address_delivery']][$warehouse['id_warehouse']];
            }
        }
        unset($product);
        arsort($warehouse_count_by_address);
        $grouped_by_warehouse = array();
        foreach ($product_list as &$product) {
            if (!isset($grouped_by_warehouse[$product['id_address_delivery']])) {
                $grouped_by_warehouse[$product['id_address_delivery']] = array(
                    'in_stock' => array(),
                    'out_of_stock' => array(),
                );
            }
            $product['carrier_list'] = array();
            $id_warehouse = 0;
            foreach ($warehouse_count_by_address[$product['id_address_delivery']] as $id_war => $val) {
                if (array_key_exists((int)$id_war, $product['warehouse_list'])) {
                    $product['carrier_list'] = array_replace(
                        $product['carrier_list'],
                        WarehouseStock::getAvailableCarrierList(
							new Product($product['id_product'], false),
							$id_war,
							$product['id_address_delivery'],
							$product['id_product_attribute'],
							$this
						)
                    );
                    if (!$id_warehouse) {
                        $id_warehouse = (int)$id_war;
                    }
                }
            }
            if (!isset($grouped_by_warehouse[$product['id_address_delivery']]['in_stock'][$id_warehouse])) {
                $grouped_by_warehouse[$product['id_address_delivery']]['in_stock'][$id_warehouse] = array();
                $grouped_by_warehouse[$product['id_address_delivery']]['out_of_stock'][$id_warehouse] = array();
            }
            if (!$this->allow_seperated_package) {
                $key = 'in_stock';
            } else {
                $key = $product['in_stock'] ? 'in_stock' : 'out_of_stock';
                $product_quantity_in_stock = StockAvailable::getQuantityAvailableByProduct($product['id_product'], $product['id_product_attribute']);
                if ($product['in_stock'] && $product['cart_quantity'] > $product_quantity_in_stock) {
                    $out_stock_part = $product['cart_quantity'] - $product_quantity_in_stock;
                    $product_bis = $product;
                    $product_bis['cart_quantity'] = $out_stock_part;
                    $product_bis['in_stock'] = 0;
                    $product['cart_quantity'] -= $out_stock_part;
                    $grouped_by_warehouse[$product['id_address_delivery']]['out_of_stock'][$id_warehouse][] = $product_bis;
                }
            }
            if (empty($product['carrier_list'])) {
                $product['carrier_list'] = array(0 => 0);
            }
            $grouped_by_warehouse[$product['id_address_delivery']][$key][$id_warehouse][] = $product;
        }
        unset($product);
        $grouped_by_carriers = array();
        foreach ($grouped_by_warehouse as $id_address_delivery => $products_in_stock_list) {
            if (!isset($grouped_by_carriers[$id_address_delivery])) {
                $grouped_by_carriers[$id_address_delivery] = array(
                    'in_stock' => array(),
                    'out_of_stock' => array(),
                );
            }
            foreach ($products_in_stock_list as $key => $warehouse_list) {
                if (!isset($grouped_by_carriers[$id_address_delivery][$key])) {
                    $grouped_by_carriers[$id_address_delivery][$key] = array();
                }
                foreach ($warehouse_list as $id_warehouse => $product_list) {
                    if (!isset($grouped_by_carriers[$id_address_delivery][$key][$id_warehouse])) {
                        $grouped_by_carriers[$id_address_delivery][$key][$id_warehouse] = array();
                    }
                    foreach ($product_list as $product) {
                        $package_carriers_key = implode(',', $product['carrier_list']);
                        if (!isset($grouped_by_carriers[$id_address_delivery][$key][$id_warehouse][$package_carriers_key])) {
                            $grouped_by_carriers[$id_address_delivery][$key][$id_warehouse][$package_carriers_key] = array(
                                'product_list' => array(),
                                'carrier_list' => $product['carrier_list'],
                                'warehouse_list' => $product['warehouse_list'],
                            );
                        }
                        $grouped_by_carriers[$id_address_delivery][$key][$id_warehouse][$package_carriers_key]['product_list'][] = $product;
                    }
                }
            }
        }
        $package_list = array();
        foreach ($grouped_by_carriers as $id_address_delivery => $products_in_stock_list) {
            if (!isset($package_list[$id_address_delivery])) {
                $package_list[$id_address_delivery] = array(
                    'in_stock' => array(),
                    'out_of_stock' => array(),
                );
            }
            foreach ($products_in_stock_list as $key => $warehouse_list) {
                if (!isset($package_list[$id_address_delivery][$key])) {
                    $package_list[$id_address_delivery][$key] = array();
                }
                $carrier_count = array();
                foreach ($warehouse_list as $id_warehouse => $products_grouped_by_carriers) {
                    foreach ($products_grouped_by_carriers as $data) {
                        foreach ($data['carrier_list'] as $id_carrier) {
                            if (!isset($carrier_count[$id_carrier])) {
                                $carrier_count[$id_carrier] = 0;
                            }
                            ++$carrier_count[$id_carrier];
                        }
                    }
                }
                arsort($carrier_count);
                foreach ($warehouse_list as $id_warehouse => $products_grouped_by_carriers) {
                    if (!isset($package_list[$id_address_delivery][$key][$id_warehouse])) {
                        $package_list[$id_address_delivery][$key][$id_warehouse] = array();
                    }
                    foreach ($products_grouped_by_carriers as $data) {
                        foreach ($carrier_count as $id_carrier => $rate) {
                            if (array_key_exists($id_carrier, $data['carrier_list'])) {
                                if (!isset($package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier])) {
                                    $package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier] = array(
                                        'carrier_list' => $data['carrier_list'],
                                        'warehouse_list' => $data['warehouse_list'],
                                        'product_list' => array(),
                                    );
                                }
                                $package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]['carrier_list'] =
                                    array_intersect($package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]['carrier_list'], $data['carrier_list']);
                                $package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]['product_list'] =
                                    array_merge($package_list[$id_address_delivery][$key][$id_warehouse][$id_carrier]['product_list'], $data['product_list']);
                                break;
                            }
                        }
                    }
                }
            }
        }
        $final_package_list = array();
        foreach ($package_list as $id_address_delivery => $products_in_stock_list) {
            if (!isset($final_package_list[$id_address_delivery])) {
                $final_package_list[$id_address_delivery] = array();
            }
            foreach ($products_in_stock_list as $key => $warehouse_list) {
                foreach ($warehouse_list as $id_warehouse => $products_grouped_by_carriers) {
                    foreach ($products_grouped_by_carriers as $data) {
                        $final_package_list[$id_address_delivery][] = array(
                            'product_list' => $data['product_list'],
                            'carrier_list' => $data['carrier_list'],
                            'warehouse_list' => $data['warehouse_list'],
                            'id_warehouse' => $id_warehouse,
                        );
                    }
                }
            }
        }
        static::$cachePackageList[$cache_key] = $final_package_list;
        return $final_package_list;
    }
    
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
    public function getDeliveryOptionList(Country $default_country = null, $flush = false)
    {
		if (!Configuration::get('WKWAREHOUSE_ALLOW_MULTICARRIER_CHOICE')) {
            return parent::getDeliveryOptionList($default_country, $flush);
		} else {
			if (!class_exists('WarehouseStock')) {
				require_once(dirname(__FILE__).'/../../modules/wkwarehouses/classes/WarehouseStock.php');
			}
			if (!class_exists('WorkshopAsm')) {
				require_once(dirname(__FILE__).'/../../modules/wkwarehouses/classes/WorkshopAsm.php');
			}
			if (Configuration::get('WKWAREHOUSE_ALLOW_MULTICARRIER_CART') &&
				!Configuration::get('WKWAREHOUSE_ALLOW_MULTI_ADDRESSES') &&
				WarehouseStock::getNumberOfAsmProductsInCart($this->id) >= 2 &&
				WarehouseStock::isMultiShipping($this)) {
				$multi_delivery_option_list = $this->getMultiCarriersOptionList($default_country, $flush);
				if (!$multi_delivery_option_list) {
					return parent::getDeliveryOptionList($default_country, $flush);
				}
				return $multi_delivery_option_list;
			} else {
            	return parent::getDeliveryOptionList($default_country, $flush);
			}
		}
    }
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
    public function getMultiCarriersOptionList($default_country, $flush)
    {
		if (isset(static::$cacheDeliveryOptionList[$this->id]) && !$flush) {
			return static::$cacheDeliveryOptionList[$this->id];
		}
		$delivery_option_list = array();
		$carriers_price = array();
		$carrier_collection = array();
		$package_list = $this->getPackageList($flush);
		foreach ($package_list as $id_address => $packages) {
			$delivery_option_list[$id_address] = array();
			$carriers_price[$id_address] = array();
			$carriers_instance = array();
			if (count($packages) == 1) {
				return false;
			}
			$country = $id_address ? new Country((new Address($id_address))->id_country) : $default_country;
			$carriers_package_lists = $packages_carriers = array();
			$packages_tmp = $packages;
			$i = 1;
			foreach ($packages_tmp as $id_package => $package) {
				$carriers_list_tmp = array();
				if ((int)$package['id_warehouse'] > 0) {
					foreach ($package['carrier_list'] as $id_carrier) {
						$carriers_list_tmp[$i] = (int)$id_carrier;
						$i++;
					}
					array_push($carriers_package_lists, $carriers_list_tmp);
				} else {
					$ids_carriers_tmp = WorkshopAsm::getBestCarriersForNotAsmProducts($country, $package, $this);
					if (count($ids_carriers_tmp) > 0) {
						$ids_carriers_tmp = array_unique($ids_carriers_tmp);
						foreach ($ids_carriers_tmp as $id_carrier) {
							$carriers_list_tmp[$i] = (int)$id_carrier;
							$i++;
						}
						array_push($carriers_package_lists, $carriers_list_tmp);
					}
				}
				if ($carriers_list_tmp) {// Re-assign the carrier_list
					$packages_tmp[$id_package]['carrier_list'] = $carriers_list_tmp;
				}
			}
			
			$all_carriers_combinations = WorkshopAsm::generateCombinations($carriers_package_lists);
			foreach ($packages_tmp as $id_package => $package) {
				$carriers_price[$id_address][$id_package] = array();
				foreach ($package['carrier_list'] as $index => $id_carrier) {
					if (!isset($carriers_instance[$id_carrier])) {
						$carriers_instance[$id_carrier] = new Carrier($id_carrier);
					}
					if (!isset($carriers_price[$id_address][$id_package][$id_carrier])) {
						$carriers_price[$id_address][$id_package][$id_carrier] = array(
							'without_tax' => $this->getPackageShippingCost((int)$id_carrier, false, $country, $package['product_list']),
							'with_tax' => $this->getPackageShippingCost((int)$id_carrier, true, $country, $package['product_list']),
						);
					}
					$packages_carriers[$index][$id_carrier] = $id_package;// set package ID according to the index and id carrier
				}
			}
        	unset($packages_tmp);
			foreach ($all_carriers_combinations as $carriers_combination) {
				$key = '';
				$carriers_list = array();
				foreach ($carriers_combination as $index => $id_carrier) {// now we need the index to look for the package ID
					$key .= $id_carrier . ',';
					if (!isset($carriers_list[$id_carrier])) {
						$carriers_list[$id_carrier] = array(
							'price_with_tax' => 0,
							'price_without_tax' => 0,
							'package_list' => array(),
							'product_list' => array(),
						);
					}
					$id_package = $packages_carriers[$index][$id_carrier];// now we need the index to look for the package ID
					$carriers_list[$id_carrier]['price_with_tax'] += $carriers_price[$id_address][$id_package][$id_carrier]['with_tax'];
					$carriers_list[$id_carrier]['price_without_tax'] += $carriers_price[$id_address][$id_package][$id_carrier]['without_tax'];
					$carriers_list[$id_carrier]['package_list'][] = $id_package;
					$carriers_list[$id_carrier]['product_list'] = array_merge(
						$carriers_list[$id_carrier]['product_list'],
						$packages[$id_package]['product_list']
					);
					$carriers_list[$id_carrier]['instance'] = $carriers_instance[$id_carrier];
				}
				$delivery_option_list[$id_address][$key] = array(
					'carrier_list' => $carriers_list,
					'is_best_price' => false,
					'is_best_grade' => false,
					'unique_carrier' => (count($carriers_list) <= 1),
				);
			}
		}
		$cart_rules = CartRule::getCustomerCartRules(Context::getContext()->cookie->id_lang, Context::getContext()->cookie->id_customer, true, true, false, $this, true);
		$result = false;
		if ($this->id) {
			$result = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'cart_cart_rule WHERE id_cart = '.(int)$this->id);
		}
		$cart_rules_in_cart = array();
		if (is_array($result)) {
			foreach ($result as $row) {
				$cart_rules_in_cart[] = $row['id_cart_rule'];
			}
		}
		$total_products_wt = $this->getOrderTotal(true, Cart::ONLY_PRODUCTS);
		$total_products = $this->getOrderTotal(false, Cart::ONLY_PRODUCTS);
		$free_carriers_rules = array();
		foreach ($cart_rules as $cart_rule) {
			$total_price = $cart_rule['minimum_amount_tax'] ? $total_products_wt : $total_products;
			if ($cart_rule['free_shipping'] && $cart_rule['carrier_restriction'] &&
				in_array($cart_rule['id_cart_rule'], $cart_rules_in_cart) &&
				$cart_rule['minimum_amount'] <= $total_price) {
				$cr = new CartRule((int) $cart_rule['id_cart_rule']);
				if (Validate::isLoadedObject($cr) &&
					$cr->checkValidity(Context::getContext(), in_array((int) $cart_rule['id_cart_rule'], $cart_rules_in_cart), false, false)) {
					$carriers = $cr->getAssociatedRestrictions('carrier', true, false);
					if (is_array($carriers) && count($carriers) && isset($carriers['selected'])) {
						foreach ($carriers['selected'] as $carrier) {
							if (isset($carrier['id_carrier']) && $carrier['id_carrier']) {
								$free_carriers_rules[] = (int) $carrier['id_carrier'];
							}
						}
					}
				}
			}
		}
		foreach ($delivery_option_list as $id_address => $delivery_option) {
			foreach ($delivery_option as $key => $value) {
				$total_price_with_tax = 0;
				$total_price_without_tax = 0;
				$total_price_without_tax_with_rules = 0;
				$position = 0;
				foreach ($value['carrier_list'] as $id_carrier => $data) {
					$total_price_with_tax += $data['price_with_tax'];
					$total_price_without_tax += $data['price_without_tax'];
					$total_price_without_tax_with_rules = (in_array($id_carrier, $free_carriers_rules)) ? 0 : $total_price_without_tax;
					if (!isset($carrier_collection[$id_carrier])) {
						$carrier_collection[$id_carrier] = new Carrier($id_carrier);
					}
					$delivery_option_list[$id_address][$key]['carrier_list'][$id_carrier]['instance'] = $carrier_collection[$id_carrier];
					if (file_exists(_PS_SHIP_IMG_DIR_ . $id_carrier . '.jpg')) {
						$delivery_option_list[$id_address][$key]['carrier_list'][$id_carrier]['logo'] = _THEME_SHIP_DIR_ . $id_carrier . '.jpg';
					} else {
						$delivery_option_list[$id_address][$key]['carrier_list'][$id_carrier]['logo'] = false;
					}
					$position += $carrier_collection[$id_carrier]->position;
				}
				$delivery_option_list[$id_address][$key]['total_price_with_tax'] = $total_price_with_tax;
				$delivery_option_list[$id_address][$key]['total_price_without_tax'] = $total_price_without_tax;
				$delivery_option_list[$id_address][$key]['is_free'] = !$total_price_without_tax_with_rules ? true : false;
				$delivery_option_list[$id_address][$key]['position'] = $position / count($value['carrier_list']);
			}
		}
		foreach ($delivery_option_list as &$array) {
			uasort($array, array('Cart', 'sortDeliveryOptionList'));
		}
		Hook::exec(
			'actionFilterDeliveryOptionList',
			array(
				'delivery_option_list' => &$delivery_option_list,
			)
		);
		static::$cacheDeliveryOptionList[$this->id] = $delivery_option_list;
		return static::$cacheDeliveryOptionList[$this->id];
	}
    
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
    public function getMyDeliveryOptionList(Country $default_country = null, $flush = false)
    {
        $delivery_option_list = array();
        $package_list = $this->getPackageList($flush);
        foreach ($package_list as $id_address => $packages) {
            $delivery_option_list[$id_address] = array();
            $carriers_price = array();
            $country = $id_address ? new Country((new Address($id_address))->id_country) : $default_country;
            foreach ($packages as $id_package => $package) {
				$key = '';
				$best_price_carriers = $best_price_carrier = array();
				if ((int)$package['id_warehouse'] == 0) {
					$package_carrier_list_tmp = WorkshopAsm::getBestCarriersForNotAsmProducts(
						$country,
						$package,
						$this
					);
					if (count($package_carrier_list_tmp)) {
						$package['carrier_list'] = array_unique($package_carrier_list_tmp);
					}
				}
                foreach ($package['carrier_list'] as $id_carrier) {
					$key .= $id_carrier . ',';
                	$best_price_carriers[] = $id_carrier;
                    $carriers_price[$id_carrier] = array(
                        'without_tax' => $this->getPackageShippingCost((int)$id_carrier, false, $country, $package['product_list']),
                        'with_tax' => $this->getPackageShippingCost((int)$id_carrier, true, $country, $package['product_list']),
                    );
                }
				foreach ($best_price_carriers as $id_carrier) {
					$best_price_carrier[$id_carrier]['price_with_tax'] = $carriers_price[$id_carrier]['with_tax'];
					$best_price_carrier[$id_carrier]['price_without_tax'] = $carriers_price[$id_carrier]['without_tax'];
				}
				$delivery_option_list[$id_address][$package['id_warehouse'].','][$key] = array(
					'carrier_list' => $best_price_carrier,
					'unique_carrier' => (count($best_price_carrier) <= 1),
				);
            }
        }
        return $delivery_option_list;
    }
    
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
    public function updateAddressId($id_address, $id_address_new)
    {
        if (!Module::isEnabled('wkwarehouses') || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            return parent::updateAddressId($id_address, $id_address_new);
        }
        if (!class_exists('WarehouseStock')) {
            require_once(dirname(__FILE__).'/../../modules/wkwarehouses/classes/WarehouseStock.php');
        }
        $to_update = false;
        if (!isset($this->id_address_invoice) || $this->id_address_invoice == $id_address) {
            $to_update = true;
            $this->id_address_invoice = $id_address_new;
        }
        if (!isset($this->id_address_delivery) || $this->id_address_delivery == $id_address) {
            $to_update = true;
            $this->id_address_delivery = $id_address_new;
        }
        if ($to_update) {
            $this->update();
        }
        if (!WarehouseStock::isMultiShipping($this)) {
            return parent::updateAddressId($id_address, $id_address_new);
        }
    }
    
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
    public function duplicate()
    {
        if (!Module::isEnabled('wkwarehouses') || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') ||
            !Configuration::get('WKWAREHOUSE_ALLOW_MULTIWH_CART') ||
            !Configuration::get('WKWAREHOUSE_ALLOW_MULTICARRIER_CART')) {
            return parent::duplicate();
        }
        if (!class_exists('StoreHouse')) {
            require_once(dirname(__FILE__).'/../../modules/wkwarehouses/classes/Warehouse.php');
            require_once(dirname(__FILE__).'/../../modules/wkwarehouses/classes/WarehouseStock.php');
        }
        $id_old_cart = $this->id;
        if (WarehouseStock::getNumberOfAsmProductsInCart($id_old_cart) <= 0) {
            return parent::duplicate();
        }
        $duplication = parent::duplicate();
        $duplicated_cart = null;
        if ($duplication && Validate::isLoadedObject($duplication['cart']) && $duplication['success']) {
            $duplicated_cart = $duplication['cart'];
            $action = Tools::getIsset('action') ? Tools::getValue('action') : '';
			foreach ($duplicated_cart->getProducts() as $data) {
				if (empty(WorkshopAsm::getAssociatedWarehousesArray($data['id_product'], $data['id_product_attribute']))) {
					$duplicated_cart->deleteProduct($data['id_product'], $data['id_product_attribute']);
					$duplicated_cart->update();
				}
			}
            
            if ($action == 'duplicateOrder' && Tools::getValue('tab') == 'AdminCarts') {
                
                $order_duplicate = new Order((int)Tools::getValue('id_order'));
                if (Validate::isLoadedObject($order_duplicate) && count($order_duplicate->getBrother()) >= 1) {
                    $order_products = $order_duplicate->getProductsDetail();
                    $order_products_array = array();
                    foreach ($order_products as $row) {
                        array_push($order_products_array, $row['product_id'].'_'.$row['product_attribute_id']);
                    }
                    foreach ($duplicated_cart->getProducts() as $data) {
                        if (!in_array($data['id_product'].'_'.$data['id_product_attribute'], $order_products_array)) {
                            $duplicated_cart->deleteProduct($data['id_product'], $data['id_product_attribute']);
                            $duplicated_cart->update();
                        }
                    }
                }
            }
            if ((!empty($action) && !in_array($action, array('addProductOnOrder', 'deleteProductLine'))) || empty($action)) {
                foreach ($duplicated_cart->getProducts() as $product) {
                    $id_product = (int)$product['id_product'];
                    $productObj = new Product($id_product, false);
                    if (Validate::isLoadedObject($productObj) && $productObj->advanced_stock_management) {
                        $id_product_attribute = (int)$product['id_product_attribute'];
                        $rs = WarehouseStock::productIsPresentInCart($id_old_cart, $id_product, $id_product_attribute);
                        if ($rs && (int)$rs['id_warehouse'] > 0) {
                            $id_warehouse = (int)$rs['id_warehouse'];
                            $warehouse = new StoreHouse($id_warehouse);
                            if (Validate::isLoadedObject($warehouse)) {
                                WarehouseStock::updateProductWarehouseCart(
                                    $duplicated_cart->id,
                                    $id_product,
                                    $id_product_attribute,
                                    $id_warehouse
                                );
                            }
                        }
                    }
                }
            }
            if (Tools::getIsset('action') && Tools::getValue('action') == 'duplicateOrder' &&
                Tools::getValue('tab') == 'AdminCarts') {
                WarehouseStock::checkAvailabilityCarriersInCart($duplicated_cart);
            }
            if (Tools::getIsset('submitReorder')) {
                WarehouseStock::assignRightDeliveryAddressToEachProductInCart($duplicated_cart);
            }
        }
        return array('cart' => $duplicated_cart, 'success' => $duplication['success']);
    }
    /*
    * module: orderfees_shipping
    * date: 2024-12-07 01:54:03
    * version: 1.23.11
    */
    public function getPackageShippingCost(
        $id_carrier = null,
        $use_tax = true,
        Country $default_country = null,
        $product_list = null,
        $id_zone = null,
        bool $keepOrderPrices = false
    ) {
        if ($this->isVirtualCart()) {
            return 0;
        }
        
        static $cache = [];
        static $module = null;
        
        if ($module === null) {
            $module = Module::getInstanceByName('orderfees_shipping');
        }
        
        $cache_key = crc32(json_encode(func_get_args()));
        
        if (!isset($cache[$cache_key])) {
            $total = 0;
            $return = false;
            $cache[$cache_key] = false;
            Hook::exec('actionCartGetPackageShippingCost', array(
                'object' => &$this,
                'id_carrier' => &$id_carrier,
                'use_tax' => &$use_tax,
                'default_country' => &$default_country,
                'product_list' => &$product_list,
                'id_zone' => &$id_zone,
                'keepOrderPrices' => &$keepOrderPrices,
                'total' => &$total,
                'return' => &$return
            ));
            if ($return) {
                $cache[$cache_key] = ($total !== false ? (float) Tools::ps_round((float) $total, 2) : false);
            } else {
                $shipping_cost = parent::getPackageShippingCost(
                    $id_carrier,
                    $use_tax,
                    $default_country,
                    $product_list,
                    $id_zone,
                    $keepOrderPrices
                );
                if ($shipping_cost !== false) {
                    $cache[$cache_key] = $shipping_cost + (float) Tools::ps_round((float) $total, 2);
                }
            }
        }
        
        return $cache[$cache_key];
    }
    
    /*
    * module: orderfees_shipping
    * date: 2024-12-07 01:54:03
    * version: 1.23.11
    */
    public function getTotalWeight($products = null)
    {
        $total_weight = 0;
        $return = false;
        
        Hook::exec('actionCartGetTotalWeight', array(
            'object' => &$this,
            'products' => &$products,
            'total_weight' => &$total_weight,
            'return' => &$return
        ));
        
        if ($return) {
            return $total_weight;
        }
        
        return parent::getTotalWeight($products) + $total_weight;
    }
}
