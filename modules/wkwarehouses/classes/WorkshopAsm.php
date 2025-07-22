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

use PrestaShop\PrestaShop\Adapter\ServiceLocator;

class WorkshopAsm
{
    /*
    * Save Warehouses to db
    */
    public static function processWarehouses($id_product, $id_product_attribute = null, $form = false)
    {
        if (Validate::isLoadedObject($product = new Product((int)$id_product), false)) {
            $attributes = array();
            if (!is_null($id_product_attribute)) {
                $attributes[] = array(
                    'id_product_attribute' => $id_product_attribute,
                    'attribute_designation' => ''
                );
            } else {
                // Get all id_product_attribute
                $attributes = $product->getAttributesResume(Context::getContext()->language->id);
                if (empty($attributes)) {
                    $attributes[] = array(
                        'id_product_attribute' => 0,
                        'attribute_designation' => ''
                    );
                }
            }
            // Get warehouses
			if ($form) {// get form information
            	$warehouses = StoreHouse::getWarehouses();
			}
            // Get already associated warehouses
            $awc = StorehouseProductLocation::getCollection($product->id, $id_product_attribute);

            $elements_to_manage = array();
            $default_warehouse = (int)Configuration::get('WKWAREHOUSE_DEFAULT_NEW_PRODUCT');

            foreach ($attributes as $attribute) {
                if ($form) {// get form information
                    foreach ($warehouses as $warehouse) {
                        $key = $warehouse['id_warehouse'].'_'.$product->id.'_'.$attribute['id_product_attribute'];
                        // get elements to manage
                        if (Tools::getIsset('check_warehouse_'.$key)) {
                            $location = Tools::getValue('location_warehouse_'.$key, '');
                            $elements_to_manage[$key] = $location;
                        }
                    }
                } else {
                    // Executed from any other places
                    $key = $default_warehouse.'_'.$product->id.'_'.$attribute['id_product_attribute'];
                    // Get elements to manage
                    $elements_to_manage[$key] = '';
                }
            }
            // Delete entry if necessary
            foreach ($awc as $wc) {
                if (!array_key_exists($wc->id_warehouse.'_'.$wc->id_product.'_'.$wc->id_product_attribute, $elements_to_manage)) {
                    $wc->delete();
                }
            }
            // Manage associations
            foreach ($elements_to_manage as $key => $location) {
                $params = explode('_', $key);

                $id_location = (int)StorehouseProductLocation::getIdByProductAndWarehouse(
                    (int)$params[1],
                    (int)$params[2],
                    (int)$params[0]
                );
                if (empty($id_location)) {// Create
                    $warehouse_location_entity = new StorehouseProductLocation();
                    $warehouse_location_entity->id_warehouse = (int)$params[0];
                    $warehouse_location_entity->id_product = (int)$params[1];
                    $warehouse_location_entity->id_product_attribute = (int)$params[2];
                    $warehouse_location_entity->location = pSQL($location);
                    $warehouse_location_entity->save();
                } else {// Update
                    $warehouse_location_entity = new StorehouseProductLocation($id_location);
                    if (pSQL($location) != $warehouse_location_entity->location) {
                        $warehouse_location_entity->location = pSQL($location);
                        $warehouse_location_entity->update();
                    }
                }
            }
        }
    }

    /*
     * For a given product, synchronize Stock::physical_quantities with StockAvailable::quantity
     *
     * The sum of quantities in warehouses must be equal and aligned to the prestashop available quantity
     * The sync is for increase/decrease quantities
     *
     * Important! Can be used for negative values
    */
    public function synchronize(
        $id_product,
        $id_product_attribute = null,
        $physical_qty = null,
        $associated_warehouses = array(),
        $productIsPresentRestrict = true,
        $id_warehouse = null
    ) {
        if (empty($associated_warehouses)) {
            $associated_warehouses = self::getAssociatedWarehousesArray($id_product, $id_product_attribute);
        }
        // IF warehouses are available
        if (count($associated_warehouses) >= 1) {
            if (empty($physical_qty)) {
                $stock_infos = self::getAvailableStockByProduct($id_product, $id_product_attribute);
                $physical_qty = isset($stock_infos['physical_quantity']) ? (int)$stock_infos['physical_quantity'] : 0;
            }
            // Calculate the sum of warehouses quantities
            $physical_quantity_in_warehouses = (int)self::getProductPhysicalQuantities(
                $id_product,
                $id_product_attribute
            );
            $delta_qty = (int)($physical_qty - $physical_quantity_in_warehouses); // Quantity Gap

            // If product present in warehouse stock
            if ((WarehouseStock::productIsPresentInStock($id_product, $id_product_attribute) && $productIsPresentRestrict) ||
                !$productIsPresentRestrict) {
                if ($delta_qty != 0) {
                    if (!empty($id_warehouse)) {
                        $id_warehouse_priority = (int)$id_warehouse;
                    } elseif (count($associated_warehouses) > 1) {
                        $id_warehouse_priority = (int)self::findWarehousePriority(
                            $associated_warehouses,
                            true,
                            $id_product,
                            $id_product_attribute
                        );
                    } else {
                        $id_warehouse_priority = (int)$associated_warehouses[0];
                    }
                    if (!empty($id_warehouse_priority)) {
                        $this->addProduct(
                            $id_product,
                            $id_product_attribute,
                            (new StoreHouse($id_warehouse_priority)),
                            $delta_qty
                        );
                    }
                }
            }
        }
    }

    public static function alignPsQuantitiesToWarehousesQuantity($id_product, $id_product_attribute = null)
    {
        // If product present in warehouse stock
        if (WarehouseStock::productIsPresentInStock($id_product, $id_product_attribute)) {
            // Warehouses quantities SUM
            self::updatePhysicalProductAvailableQuantity($id_product);
            $physical_quantity_in_warehouses = (int)self::getProductPhysicalQuantities(
                $id_product,
                $id_product_attribute
            );
            // Prestashop quantity
            $stock_available = self::getAvailableStockByProduct($id_product, $id_product_attribute);
            // Calculate Gap
            $delta_qty = (int)$physical_quantity_in_warehouses - (int)$stock_available['physical_quantity'];
            if ($delta_qty != 0) {
                self::updateQuantity(
                    $id_product,
                    $id_product_attribute,
                    $delta_qty,
                    Context::getContext()->shop->id
                );
                self::updatePhysicalProductAvailableQuantity($id_product);
            }
        }
    }

    /*
    * Sync stock (physical_quantity) according to reserved & available qty
    */
    public static function updatePhysicalProductAvailableQuantity($id_product = null, $id_shop = null, $id_order = null)
    {
        if (empty($id_shop)) {
            $id_shop = Context::getContext()->shop->id;
        }
        (new PrestaShop\PrestaShop\Adapter\StockManager())->updatePhysicalProductQuantity(
            (int)$id_shop,
            (int)Configuration::get('PS_OS_ERROR'),
            (int)Configuration::get('PS_OS_CANCELED'),
            $id_product,
            $id_order
        );
    }

    /**
     * For a given product, find and return warehouse priority
     */
    public static function findWarehousePriority(
        $associated_warehouses = array(),
        $force_stock_priority = true,
        $id_product = null,
        $id_product_attribute = null,
        $mvt_way = 'asc', // increase mvt quantity by default
        $return_id = true
    ) {
        if (empty($associated_warehouses)) {
            $associated_warehouses = self::getAssociatedWarehousesArray(
                $id_product,
                $id_product_attribute
            );
        }

        $id_warehouse_priority = 0;
        $stock_priority = ($mvt_way == 'asc' ? Configuration::get('WKWAREHOUSE_STOCKPRIORITY_INC') : Configuration::get('WKWAREHOUSE_STOCKPRIORITY_DEC'));
        $warehouses_priorities_ids = ($mvt_way == 'asc' ? Configuration::get('WKWAREHOUSE_PRIORITY') : Configuration::get('WKWAREHOUSE_PRIORITY_DECREASE'));

        // If it is a stock priority first, look for warehouse that has less|enough stock
        if ($force_stock_priority && $stock_priority) {
            $sorted_warehouses = self::findStockPriority(
                $associated_warehouses,
                $id_product,
                $id_product_attribute,
                $mvt_way
            );
            if (is_array($sorted_warehouses) && count($sorted_warehouses)) {
                if ($return_id) {// Get the key of the first element (which is the warehouse ID)
                    return (int)key($sorted_warehouses);
                } else {// Return an array
                    return $sorted_warehouses;
                }
            }
        }

        // If it's priority based on warehouses
        if (empty($id_warehouse_priority) || !$stock_priority) {
            if ($warehouses_priorities_ids) {
                $sorted_warehouses = explode(',', $warehouses_priorities_ids);
                // If frontoffice, take off disabled warehouses
                if (!defined('_PS_ADMIN_DIR_')) {
                    foreach ($sorted_warehouses as $k => $id_warehouse) {
                        if (!(new StoreHouse((int)$id_warehouse))->active) {
                            unset($sorted_warehouses[$k]);
                        }
                    }
                }
                if ($return_id) {
                    foreach ($sorted_warehouses as $id_warehouse) {
                        if (in_array($id_warehouse, $associated_warehouses)) {
                            return $id_warehouse;
                        }
                    }
                } else {
                    return $sorted_warehouses;
                }
            }
        }
    }

    /**
     * For a given product, find and return stock priority
     */
    public static function findStockPriority($associated_warehouses, $id_product, $id_product_attribute, $way = 'asc')
    {
        if (empty($associated_warehouses)) {
            return;
        }

        $warehouses_stocks = array();
        // collect qty for each warehouse
        foreach ($associated_warehouses as $id_warehouse) {
            $warehouses_stocks[$id_warehouse] = (int)self::getProductPhysicalQuantities(
                $id_product,
                $id_product_attribute,
                $id_warehouse
            );
        }
        if (count($warehouses_stocks)) {
            if ($way == 'asc') {
                asort($warehouses_stocks); // Sort Array ascending according to values
            } else {
                arsort($warehouses_stocks); // Sort Array descending according to values
            }
            return $warehouses_stocks; // Return array
        }
    }

    /*
    * Update stock according to the priority
    * Used for only decrease of quantity (warehouse by warehouse)
    * Based only on quantites in warehouse (from higher to lower)
    */
    public function updateAccordingDescWarehouseQtiesPriority(
        $associated_warehouses,
        $id_product,
        $id_product_attribute,
        $delta_qty,
        $id_main_warehouse = null
    ) {
        $sorted_warehouses = self::findWarehousePriority(
            $associated_warehouses,
            true,
            $id_product,
            $id_product_attribute,
            'desc',
            false// return an array
        ); // return an array containing the warehouses ID ordered by predefined priorities
        /*
        * If all warehouses are out of stock including the main,
        * Or, the main warehouse is in stock,
        * No need to loop all warehouses, just decrease the quantity from the main warehouse.
        * The main warehouse is the one that has been assigned during placing customer order or by user from BO
        */
        if (count($sorted_warehouses) && !empty($id_main_warehouse)) {
            $qty_main_stock = (int)self::getProductPhysicalQuantities(//physical qty for the main warehouse
                $id_product,
                $id_product_attribute,
                $id_main_warehouse
            );
            if (max($sorted_warehouses) <= 0 || $qty_main_stock >= $delta_qty) {
                $this->removeProduct(
                    $id_product,
                    $id_product_attribute,
                    (new StoreHouse((int)$id_main_warehouse)),
                    $delta_qty
                );
                return;
            }
        }

		// add the quantity information for each warehouse if priorities are based on warehouses list
		// because if priority is based on stock, the stock information is alreay there (see findWarehousePriority function)
		if (is_array($sorted_warehouses) && count($sorted_warehouses)) {
			$warehouses_stocks = array();
			foreach ($sorted_warehouses as $id_warehouse) {
				$warehouses_stocks[$id_warehouse] = (int)self::getProductPhysicalQuantities(
					$id_product,
					$id_product_attribute,
					$id_warehouse
				);
			}
			$sorted_warehouses = $warehouses_stocks;
		}

        $gap_qty = 0;
        foreach ($sorted_warehouses as $id_warehouse => $qty_in_warehouse) {
            $warehouse = new StoreHouse((int)$id_warehouse);

            if ($gap_qty < 0) {// Need to be positive always
                $gap_qty *= -1;
                $delta_qty = $gap_qty;
            }

            if ($qty_in_warehouse > 0) {
                $gap_qty = $qty_in_warehouse - $delta_qty;
                if ($gap_qty >= 0) {
                    $this->removeProduct(
                        $id_product,
                        $id_product_attribute,
                        $warehouse,
                        $delta_qty
                    );
                    break;
                } else {
                    $this->removeProduct(
                        $id_product,
                        $id_product_attribute,
                        $warehouse,
                        $qty_in_warehouse
                    );
                }
            } else {
                $gap_qty = $qty_in_warehouse - $delta_qty;
                /*
                * If main warehouse, bound the negative qty
                */
                if (!empty($id_main_warehouse)) {
                    $warehouse = new StoreHouse((int)$id_main_warehouse);
                    $qty_main_warehouse = (int)self::getProductPhysicalQuantities(
                        $id_product,
                        $id_product_attribute,
                        $id_main_warehouse
                    );
                    $gap_qty = $qty_main_warehouse - $delta_qty;
                }
                if ($gap_qty < 0) {
                    $gap_qty *= -1;
                }
                $this->removeProduct(
                    $id_product,
                    $id_product_attribute,
                    $warehouse,
                    $gap_qty
                );
                break;
            }
        }
    }

    /*
    * Get already associated warehouses and return array
    */
    public static function getAssociatedWarehousesArray($id_product, $id_product_attribute = null)
    {
        $associated_warehouses = array();
        $associated_wh_collection = StorehouseProductLocation::getCollection($id_product, $id_product_attribute);
		if ($associated_wh_collection) {
			foreach ($associated_wh_collection as $awc) {
				$associated_warehouses[] = (int)$awc->id_warehouse;
			}
		}
        return $associated_warehouses;
    }
    
    public static function setAdvancedStockManagement($id_product = null, $value = 0)
    {
        Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'product_shop`
             SET `advanced_stock_management` = '.(int)$value.'
             '.(!empty($id_product) ? 'WHERE `id_product` = '.(int)$id_product.Shop::addSqlRestriction() : '')
        );
        Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'product`
             SET `advanced_stock_management` = '.(int)$value.'
             '.(!empty($id_product) ? 'WHERE `id_product` = '.(int)$id_product : '')
        );
        // Remove stock (if ASM set to 0), but keep warehouses associations
        if ($value == 0) {
            self::removeStock($id_product);
            return true;
        }
    }

    public static function removeStock($id_product, $id_product_attribute = null, $id_warehouse = null)
    {
        $stocks = WarehouseStock::getStocksRows($id_product, $id_product_attribute, $id_warehouse);
        if ($stocks) {
            foreach ($stocks as $stock) {
                (new WarehouseStock($stock['id_stock']))->delete();
            }
        }
    }

    public static function getAvailableStockByProduct($id_product, $id_product_attribute = null, $id_shop = null)
    {
        if (!Validate::isUnsignedId($id_product)) {
            return false;
        }
        $query = new DbQuery();
        $query->select('id_stock_available, quantity, physical_quantity, reserved_quantity');
        $query->from('stock_available');
        $query->where('id_product = '.(int)$id_product);
        if ($id_product_attribute !== null) {
            $query->where('id_product_attribute = '.(int)$id_product_attribute);
        }
        $query = StockAvailable::addSqlShopRestriction($query, $id_shop);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
    }

    /**************************************************************************
    * -------------------------- S T O C K   M A N A G E R ------------------------
    **************************************************************************/

    /**
     * @see add Product to ps_stock table
     *
     * @param int $id_product
     * @param int $id_product_attribute
     * @param Warehouse $warehouse
     * @param int $quantity
     * @param float $price_te
     * @param bool $is_usable
     *
     * @return bool
     * @throws PrestaShopException
     */
    public function addProduct(
        $id_product,
        $id_product_attribute,
        $warehouse,
        $quantity,
        $price_te = null,
        $is_usable = false
    ) {
        if (!Validate::isLoadedObject($warehouse) || !$id_product) {
            return false;
        }

        $stock_exists = false;
		$price_te = $this->getUnitWarehousePrice($id_product, $id_product_attribute, $warehouse->id, $price_te);

        // Prepare saving movement
        $context = Context::getContext();
        $mvt_params = array(
            'id_stock' => null,
            'id_stock_mvt_reason' => $quantity < 0 ? Configuration::get('PS_STOCK_MVT_DEC_REASON_DEFAULT') : Configuration::get('PS_STOCK_MVT_INC_REASON_DEFAULT'),
            'price_te' => $price_te,
            'last_wa' => null,
            'current_wa' => null,
            'id_employee' => isset($context->employee) && (int)$context->employee->id ? (int)$context->employee->id : 0,
            'employee_firstname' => isset($context->employee) && $context->employee->firstname ? $context->employee->firstname : '',
            'employee_lastname' => isset($context->employee) && $context->employee->lastname ? $context->employee->lastname : '',
        );

        // switch on MANAGEMENT_TYPE
        switch ($warehouse->management_type) {
            // case CUMP mode
            case 'WA':
                $stock_collection = self::getStockCollection($id_product, $id_product_attribute, $warehouse->id);
                // If this product is already in stock
                if (count($stock_collection) > 0) {
                    $stock_exists = true;

                    /** @var Stock $stock */
                    // for a warehouse using WA, there is one and only one stock for a given product
                    $stock = $stock_collection->current();

                    // calculates WA price
                    $last_wa = $stock->price_te;
                    $current_wa = $price_te;
                    if (($stock->physical_quantity + $quantity) > 0) {
                        $this->calculateWA($stock, $quantity, $price_te);
                    }
                    
                    // Prepare saving movement
                    $mvt_params['id_stock'] = $stock->id;
                    $mvt_params['last_wa'] = $last_wa;
                    $mvt_params['current_wa'] = $current_wa;

                    $stock_params = array(
                        'physical_quantity' => ($stock->physical_quantity + (int)$quantity),
                        'price_te' => $current_wa,
                        'usable_quantity' => ($is_usable ? ($stock->usable_quantity + (int)$quantity) : $stock->usable_quantity),
                        'id_warehouse' => (int)$warehouse->id,
                    );
                    // saves stock in warehouse
                    $stock->hydrate($stock_params);
                    $stock->update();
                } else {
                    $mvt_params['last_wa'] = 0;
                    $mvt_params['current_wa'] = $price_te;
                }
                break;
            // case FIFO / LIFO mode
            case 'FIFO':
            case 'LIFO':
                $stock_collection = self::getStockCollection(
                    $id_product,
                    $id_product_attribute,
                    $warehouse->id,
                    $price_te
                );
                // if this product is already in stock
                if (count($stock_collection) > 0 && (int)$quantity != 0) {
                    $stock_exists = true;

                    /** @var Stock $stock */
                    // there is one and only one stock for a given product in a warehouse and at the current unit price
                    $stock = $stock_collection->current();
                    $stock_params = array(
                        'physical_quantity' => ($stock->physical_quantity + (int)$quantity),
                        'usable_quantity' => ($is_usable ? ($stock->usable_quantity + (int)$quantity) : $stock->usable_quantity),
                    );
                    // updates stock in warehouse
                    $stock->hydrate($stock_params);
                    $stock->update();
                }
                break;
            default:
                return false;
        }
        if (!$stock_exists) {
            $stock = new WarehouseStock();
            $stock_params = array(
                'id_product_attribute' => (int)$id_product_attribute,
                'id_product' => (int)$id_product,
                'physical_quantity' => (int)$quantity,
                'price_te' => (float)$price_te,
                'usable_quantity' => ($is_usable ? (int)$quantity : 0),
                'id_warehouse' => (int)$warehouse->id
            );
            // Saves stock in warehouse
            $stock->hydrate($stock_params);
            $stock->add();
            $mvt_params['id_stock'] = $stock->id;
        }

        // Saves stock mvt
        if (isset($mvt_params['id_stock']) && !empty($mvt_params['id_stock']) && $quantity != 0) {
            $stock_mvt = new WarehouseStockMvt();

            $mvt_params['sign'] = 1;
            if ($quantity < 0) {
                $quantity *= -1;
                $mvt_params['sign'] = -1;
            }
            $mvt_params['physical_quantity'] = $quantity;
			if (Validate::isUnsignedInt($quantity)) {
				$stock_mvt->hydrate($mvt_params);
				$stock_mvt->add();
			}
        }
        return true;
    }

    public function getUnitWarehousePrice($id_product, $id_product_attribute, $id_warehouse, $price_te = null)
	{
        if (is_null($price_te)) {
            $price_te = WarehouseStockMvt::getLastProductUnitPrice(
				$id_product,
				$id_product_attribute,
				$id_warehouse
			);
        }
        if ($price_te == 0) {
            // Be carefull, if price = 0, that may cause error below in calculateWA function
            $price_te = Product::getPriceStatic($id_product, false, 0, 6, null, false, false);
        }
        if (!Validate::isPrice($price_te)) {
            $price_te = 1; // put 1 to avoid error
        }
        return round((float)$price_te, 6);
	}

    /**
     * R E S E T   S T O C K
     *
     * @param int           $id_product
     * @param int|null      $id_product_attribute
     * @param Warehouse     $warehouse
     * @param int           $quantity
     * @param bool          $is_usable
     * @param int           $ignore_pack
     *
     * @return array
     * @throws PrestaShopException
     */
    public function removeProduct(
        $id_product,
        $id_product_attribute,
        StoreHouse $warehouse,
        $quantity,
        $is_usable = true,
        $ignore_pack = 0
    ) {
        $return = array();
        if (!Validate::isLoadedObject($warehouse) || !$quantity || !$id_product) {
            return $return;
        }

        // Special case of a pack
        if (Pack::isPack((int)$id_product) && !$ignore_pack) {
            /* Process pack product */
        } else {
            // gets total quantities in stock for the current product for the current warehouse
            $stock_collection = self::getStockCollection($id_product, $id_product_attribute, $warehouse->id);
            $stock_collection->getAll();

            // check if the collection is loaded
            if (count($stock_collection) <= 0) {
                // if not, save a stock in the given warehouse with 0 quantity
                $build_stock = new WarehouseStock();
                $build_stock->hydrate(array(
                    'id_product' => (int)$id_product,
                    'id_product_attribute' => (int)$id_product_attribute,
                    'id_warehouse' => (int)$warehouse->id,
                    'physical_quantity' => 0,
                    'price_te' => (float)$this->getUnitWarehousePrice($id_product, $id_product_attribute, $warehouse->id),
                    'usable_quantity' => 0
                ));
                $build_stock->add();
                $stock_collection = self::getStockCollection($id_product, $id_product_attribute, $warehouse->id);
                $stock_collection->getAll();
            }

            // Switch on MANAGEMENT TYPE
            switch ($warehouse->management_type) {
                // case CUMP mode
                case 'WA':
                    /** @var Stock $stock */
                    // There is one and only one stock for a given product in a warehouse in this mode
                    $stock = $stock_collection->current();
                    $context = Context::getContext();

                    $stockParams = array(
                        'physical_quantity' => $stock->physical_quantity - $quantity,
                        'usable_quantity' => (
                            $is_usable ? ($stock->usable_quantity - (int)$quantity) : $stock->usable_quantity
                        )
                    );

                    /** @var \WarehouseStock $stock */
                    $stock->hydrate($stockParams);
                    $stock->update();

                    $return[$stock->id]['quantity'] = $quantity;
                    $return[$stock->id]['price_te'] = $stock->price_te;

                    if ($quantity < 0) {
                        $quantity *= -1;
                    }
                    // Saves stock mvt
                    if (!empty($stock->id) && $quantity != 0 && Validate::isUnsignedInt($quantity)) {
						$movementParams = array(
							'id_stock' => $stock->id,
							'physical_quantity' => $quantity,
							'id_stock_mvt_reason' => Configuration::get('PS_STOCK_MVT_DEC_REASON_DEFAULT'),
							'price_te' => $stock->price_te,
							'last_wa' => $stock->price_te,
							'current_wa' => $stock->price_te,
							'id_employee' => isset($context->employee) && (int)$context->employee->id ? (int)$context->employee->id : 0,
							'employee_firstname' => isset($context->employee) && $context->employee->firstname ? $context->employee->firstname : '',
							'employee_lastname' => isset($context->employee) && $context->employee->lastname ? $context->employee->lastname : '',
							'sign' => -1
						);
                        $stockMovement = new WarehouseStockMvt();
                        $stockMovement->hydrate($movementParams);
                        $stockMovement->save();
                    }
                    break;
                case 'LIFO':
                case 'FIFO':
                    break;
            }
        }
        return $return;
    }

    /**
     * Get Physical Quantity by Product (ps_stock)
     */
    public static function getProductPhysicalQuantities(
        $id_product,
        $id_product_attribute,
        $ids_warehouse = null,
        $full = false
    ) {
        if (!is_null($ids_warehouse)) {
            // in case $ids_warehouse is not an array
            if (!is_array($ids_warehouse)) {
                $ids_warehouse = array($ids_warehouse);
            }
            // casts for security reason
            $ids_warehouse = array_map('intval', $ids_warehouse);
            if (!count($ids_warehouse)) {
                return 0;
            }
        } else {
            $ids_warehouse = array();
        }
        $id_lang = (int)Context::getContext()->language->id;

        $query = new DbQuery();
        if (!$full) {// return the sum
            $query->select('SUM(s.physical_quantity)');
        } else {// return for each warehouse its stored quantity, name & location
            $query->select(
                's.id_warehouse, s.physical_quantity, wl.name, wl.delivery_time, wpl.location, w.id_address'
            );
        }
        $query->from('stock', 's');
        $query->leftJoin('warehouse', 'w', 'w.id_warehouse = s.id_warehouse');
        /*if (!defined('_PS_ADMIN_DIR_')) {
            $query->where('w.active = 1');
        }*/
        if ($full) {
            $query->leftJoin(
                'warehouse_lang',
                'wl',
                'w.`id_warehouse` = wl.`id_warehouse` AND `id_lang` = '.$id_lang
            );
        }
        $query->innerJoin(
            'warehouse_product_location',
            'wpl',
            'wpl.id_product = s.id_product AND 
             wpl.id_product_attribute = s.id_product_attribute AND 
             wpl.id_warehouse = s.id_warehouse'
        );
        $query->where('s.id_product = '.(int)$id_product);
        if ($id_product_attribute != 0) {
            $query->where('s.id_product_attribute = '.(int)$id_product_attribute);
        }
        if (count($ids_warehouse)) {
            $query->where('s.id_warehouse IN ('.implode(', ', $ids_warehouse).')');
        }
        if ($full) {
            $query->groupBy('s.id_warehouse');

            $result = Db::getInstance()->executeS($query);
            foreach ($result as &$data) {
                $country_address = Address::getCountryAndState($data['id_address']);
                $data['country'] = (new Country($country_address['id_country'], $id_lang))->name;
            }
            return $result;
        } else {
            return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
        }
    }

    /*
    * Look for the SUM of reserved quantity from orders by product/combination/warehouse
    */
    public static function getReservedQuantityByProductAndWarehouse($id_product, $id_product_attribute, $id_warehouse)
    {
        if (Validate::isInt($id_product)) {
			$client_orders_qty = 0;
            $query = new DbQuery();
            $query->select('od.product_quantity, od.product_quantity_refunded');
            $query->from('order_detail', 'od');
            $query->leftjoin('orders', 'o', 'o.id_order = od.id_order');
            $query->leftJoin('order_history', 'oh', 'oh.id_order = o.id_order AND oh.id_order_state = o.current_state');
            $query->leftJoin('order_state', 'os', 'os.id_order_state = oh.id_order_state');
            $query->where('od.product_id = '.(int)$id_product);
            $query->where('od.product_attribute_id = '.(int)$id_product_attribute);
            if (!empty($id_warehouse)) {
                $query->where('od.id_warehouse = '.(int)$id_warehouse);
            }
            $query->where('os.shipped != 1');
            $query->where(
                'o.valid = 1 OR (
                    os.id_order_state != '.(int)Configuration::get('PS_OS_ERROR').' AND 
                    os.id_order_state != '.(int)Configuration::get('PS_OS_CANCELED').'
                )'
            );
            $query->groupBy('od.id_order_detail');
            //echo '<pre>';print_r($query->build());
            $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
            if (count($res)) {
                foreach ($res as $row) {
                    $client_orders_qty += ($row['product_quantity'] - $row['product_quantity_refunded']);
                }
            }
			return $client_orders_qty;
        }
    }

    /**
     * For a given stock, calculates its new WA (Weighted Average) price based on the new quantities and price
     * Formula : (physicalStock * lastCump + quantityToAdd * unitPrice) / (physicalStock + quantityToAdd)
     *
     * @param Stock|PrestaShopCollection $stock
     * @param int $quantity
     * @param float $price_te
     * @return int WA
     */
    protected function calculateWA(WarehouseStock $stock, $quantity, $price_te)
    {
        return (float)Tools::ps_round(
            ((($stock->physical_quantity * $stock->price_te) + ($quantity * $price_te)) / ($stock->physical_quantity + $quantity)),
            6
        );
    }

    /**
     * For a given product, retrieves the stock collection
     *
     * @param int $id_product
     * @param int $id_product_attribute
     * @param int $id_warehouse Optional
     * @param int $price_te Optional
     * @return PrestaShopCollection Collection of WarehouseStock
     */
    public static function getStockCollection(
        $id_product,
        $id_product_attribute,
        $id_warehouse = null,
        $price_te = null
    ) {
        $stocks = new PrestaShopCollection('WarehouseStock');
        $stocks->where('id_product', '=', (int)$id_product);
        $stocks->where('id_product_attribute', '=', (int)$id_product_attribute);
        if (!empty($id_warehouse)) {
            $stocks->where('id_warehouse', '=', (int)$id_warehouse);
        }
        if ($price_te) {
            $stocks->where('price_te', '=', (float)$price_te);
        }

        return $stocks;
    }

    /**
     * For a given product, gets the last stock mvt
     *
     * @param int $id_product
     * @param int $id_product_attribute Use 0 if the product does not have attributes
     * @return bool|array
     */
    public static function getLastStockMvt($id_stock_available)
    {
        $query = new DbQuery();

        $query->select('sm.*');
        $query->from('stock_mvt', 'sm');
        $query->where('id_stock = '.(int)$id_stock_available);
        $query->orderBy('date_add DESC');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
    }

    public static function getAttributeCombinationsQuery(
		$id_product,
		$id_warehouses_filter = null,
		$limit = false,
		$offset = false
	) {
        if (!Combination::isFeatureActive()) {
            return array();
        }
        return 
            'SELECT SQL_CALC_FOUND_ROWS
			 DISTINCT(pa.`id_product_attribute`), pa.*
             FROM `'._DB_PREFIX_.'product_attribute` pa
             '.Shop::addSqlAssociation('product_attribute', 'pa').'
             WHERE pa.`id_product` = '.(int)$id_product.'
             '.(!empty($id_warehouses_filter) && is_array($id_warehouses_filter) ? ' AND (
                SELECT COUNT(DISTINCT(wl.`id_warehouse`)) FROM `'._DB_PREFIX_.'warehouse_product_location` wl 
                WHERE wl.`id_product` = pa.`id_product` AND wl.`id_product_attribute` = pa.`id_product_attribute` 
                '.(!is_null($id_warehouses_filter) ? ' AND wl.`id_warehouse` IN ('.pSQL(implode(',', $id_warehouses_filter)).')' : '').'
             ) > 0' : '').'
             ORDER BY pa.`id_product_attribute`
			 '.($limit != false ? 'LIMIT '.($offset !== false ? $offset.',' : '').$limit : '');
    }

    public static function getWholesalePrice($id_product, $id_product_attribute = null)
    {
        $shop_id = Context::getContext()->shop->id;
        if (Shop::isFeatureActive() && empty($shop_id)) {
            $shop_context = Shop::getContext();
            // if we are in all shops context or in group shop context
            if ($shop_context == Shop::CONTEXT_ALL || $shop_context == Shop::CONTEXT_GROUP) {
                $shop_id = Context::getContext()->employee->getDefaultShopID();
            }
        }

        $product = new Product($id_product, false, null, $shop_id);
        $wholesale_price = $product->wholesale_price;

        // Search original wholesale price
        if (!empty($id_product_attribute)) {
            $combination = new Combination((int)$id_product_attribute, null, $shop_id);
            if ($combination->id_product != $id_product) {
                return false;
            }
            if ($combination && $combination->wholesale_price != '0.000000') {
                $wholesale_price = $combination->wholesale_price;
            }
        }
        return $wholesale_price;
    }

    /**
     * For a given id_product and id_product_attribute sets the quantity available
     * This function is the same as copied from \src\Core\Stock\StockManager.php
     * We copied and execute this function from here to avoid executing the "actionUpdateQuantity" hook
     *
     * @param $id_product
     * @param $id_product_attribute
     * @param $quantity
     * @param null $id_shop
     * @param bool $add_movement
     * @param array $params Optional
     * @return bool
     */
    public static function updateQuantity(
        $id_product,
        $id_product_attribute,
        $delta_quantity,
        $id_shop = null,
        $add_movement = true,
        $params = array()
    ) {
        if (!Validate::isUnsignedId($id_product)) {
            return false;
        }
        $product = new Product((int)$id_product);
        if (!Validate::isLoadedObject($product)) {
            return false;
        }

        // We should call the needed classes with Symfony dependency injection
        // instead of the Homemade Service Locator
        $serviceLocator = new ServiceLocator();
        $stockManager = ServiceLocator::get('\\PrestaShop\\PrestaShop\\Core\\Stock\\StockManager');
        $packItemsManager = $serviceLocator::get('\\PrestaShop\\PrestaShop\\Adapter\\Product\\PackItemsManager');
        $cacheManager = $serviceLocator::get('\\PrestaShop\\PrestaShop\\Adapter\\CacheManager');

        $availableStockManager = $serviceLocator::get('\\PrestaShop\\PrestaShop\\Adapter\\StockManager');
        $stockAvailable = $availableStockManager->getStockAvailableByProduct($product, $id_product_attribute, $id_shop);

        // Update quantity of the pack products
        if ($packItemsManager->isPack($product)) {
            // The product is a pack
            $stockManager->updatePackQuantity($product, $stockAvailable, $delta_quantity, $id_shop);
        } else {
            // The product is not a pack
            $stockAvailable->quantity = $stockAvailable->quantity + $delta_quantity;
            $stockAvailable->update();

            // Decrease case only: the stock of linked packs should be decreased too.
            if ($delta_quantity < 0) {
                // The product is not pack, but the product combination is part of a pack (use of isPacked, not isPack)
                if ($packItemsManager->isPacked($product, $id_product_attribute)) {
                    $stockManager->updatePacksQuantityContainingProduct(
                        $product,
                        $id_product_attribute,
                        $stockAvailable,
                        $id_shop
                    );
                }
            }
        }

        // Prepare movement and save it
        if (true === $add_movement && 0 != $delta_quantity) {
            $stockManager->saveMovement($product->id, $id_product_attribute, $delta_quantity, $params);
        }
        $cacheManager->clean('StockAvailable::getQuantityAvailableByProduct_'.(int)$product->id.'*');
    }

    public static function canManageQuantity()
    {
        $show_quantities = true;
        $shop_context = Shop::getContext();
        $shop_group = new ShopGroup((int)Shop::getContextShopGroupID());

        if (Shop::isFeatureActive()) {
			if (version_compare(_PS_VERSION_, '1.7', '>=')) {
				if ($shop_context === Shop::CONTEXT_GROUP) {
					$show_quantities = false;
				}
			} else {
				// if we are in all shops context, it's not possible to manage quantities at this level
				if ($shop_context == Shop::CONTEXT_ALL) {
					$show_quantities = false;
				// if we are in group shop context
				} elseif ($shop_context == Shop::CONTEXT_GROUP) {
					// if quantities are not shared between shops of the group, it's not possible to manage them at group level
					if (!$shop_group->share_stock) {
						$show_quantities = false;
					}
				} elseif ($shop_group->share_stock) {// if we are in shop context
					// if quantities are shared between shops of the group, it's not possible to manage them for a given shop
					$show_quantities = false;
				}
			}
        }
        return $show_quantities;
    }

    public static function implodeKey($glue = '&', $pieces = array())
    {
        $attributes_str = array();
        foreach ($pieces as $attribute => $value) {
            $attributes_str[] = $attribute.'='.$value;
        }

        return implode($glue, $attributes_str);
    }

    public static function getAttributesCombinationNames($id_product_attribute)
    {
        // Collect attributes name
        $attributes_name = '';
        if (!empty($id_product_attribute)) {
            $combination = new Combination($id_product_attribute);
            $attributes = $combination->getAttributesName((int)Context::getContext()->language->id);
            foreach ($attributes as $attribute) {
                $attributes_name .= $attribute['name'].' - ';
            }
            $attributes_name = rtrim($attributes_name, ' - ');
        }
        return $attributes_name;
    }

    public static function getSimpleProductsFromDb($products_selection, $offset = false, $limit = false)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT p.`id_product`
             FROM `'._DB_PREFIX_.'product` p
            '.Shop::addSqlAssociation('product', 'p')
            .(!empty($products_selection) ? ' WHERE p.`id_product` IN ('.implode(',', $products_selection).')' : '')
            .($offset !== false && $limit ? ' LIMIT '.$offset.','.$limit : '')
        );
    }

    public static function getProductsWithCombinationsFromDb($offset = false, $limit = false)
    {
        $products_selection = array();
        if (Tools::getValue('bulk_for') == 'sel' &&
            Tools::getIsset('productBox') && Tools::getValue('productBox')) {
            foreach (Tools::getValue('productBox') as $productBox) {
                $products_selection[] = "'".$productBox."'";
            }
        }
        $query = new DbQuery();
        $query->select(
            'a.`id_product`,
             pa.`id_product_attribute`'
        );
        $query->from('product', 'a'); // a: do not change
        $query->join(Shop::addSqlAssociation('product', 'a'));
        $query->leftJoin('product_attribute', 'pa', 'a.`id_product` = pa.`id_product`');
        $query->where('product_shop.`advanced_stock_management` = 1');
        if (count($products_selection)) {
            $query->where('CONCAT(a.`id_product`, \'_\', IFNULL(pa.`id_product_attribute`, 0)) IN ('.implode(',', $products_selection).')');
        }
        $query->groupBy('a.`id_product`, pa.`id_product_attribute`');
        /* Paging */
        if ($offset !== false && $limit) {
            $query->limit((int)$limit, (int)$offset);
        }
        //echo '<pre>'.$query->build();
        //exit();
        return Db::getInstance()->executeS($query);
    }

    public static function getReservedQuery()
    {
		return '
			SELECT COALESCE(SUM(od.`product_quantity` - od.`product_quantity_refunded`), 0)
			FROM `'._DB_PREFIX_.'order_detail` od
			WHERE od.product_id = a.`id_product` AND 
			od.product_attribute_id = IF(pa.`id_product_attribute` > 0, pa.`id_product_attribute`, 0) AND 
			od.id_warehouse <> 0 AND
			od.`id_order` IN (
				SELECT o.`id_order`
				FROM `'._DB_PREFIX_.'orders` o
				LEFT JOIN `'._DB_PREFIX_.'order_history` `oh` ON oh.id_order = o.id_order AND oh.id_order_state = o.current_state
				LEFT JOIN `'._DB_PREFIX_.'order_state` `os` ON os.id_order_state = oh.id_order_state
				WHERE o.id_order = od.id_order AND
				os.`shipped` != 1 AND (
					o.valid = 1 OR (
					os.id_order_state != '.(int)Configuration::get('PS_OS_ERROR').' AND 
					os.id_order_state != '.(int)Configuration::get('PS_OS_CANCELED').'
				))
			)
		';
	}

    /*
    * Set warehouses quantities according to prestashop quantities
    * For simple product or combinations
    */
    public static function setWarehousesQtiesAccordingPrestaQties($id_product, $use_asm)
    {
        // Get all id_product_attribute from ps_product_attribute table
        $products_attributes = Product::getProductAttributesIds($id_product);
        if (empty($products_attributes)) {
            $products_attributes[] = array(
                'id_product_attribute' => 0,
            );
        }
        foreach ($products_attributes as $product_attribute) {
            if ($use_asm) {
                (new WorkshopAsm())->synchronize(
                    $id_product,
                    $product_attribute['id_product_attribute'],
                    null,
                    array(),
                    false
                );
            } else {
                self::removeStock($id_product, $product_attribute['id_product_attribute']);
            }
        }
        if (!$use_asm) {
            $associated_warehouses_collection = StorehouseProductLocation::getCollection($id_product);
			if ($associated_warehouses_collection) {
				foreach ($associated_warehouses_collection as $awc) {
					$awc->delete();
				}
			}
        }
    }

    public static function filterMessageException($message)
    {
        return str_replace(array('<br>', '<br/>', '<br />'), ' - ', $message);
    }

    public static function getProductLink($id_product)
    {
        $url = '';
        if (!empty($id_product)) {
            $vars_queries = array(
                'id_product' => $id_product,
                'updateproduct' => 1
            );
            $url = (
                !version_compare(_PS_VERSION_, '1.7', '>=') ?
                Context::getContext()->link->getAdminLink('AdminProducts').'&'.self::implodeKey('&', $vars_queries) :
                Context::getContext()->link->getAdminLink('AdminProducts', true, $vars_queries)
            );
        }
        return $url;
    }

    public static function getOrderLink($id_order)
    {
        $url = '';
        if (!empty($id_order)) {
            $vars_queries = array(
                'id_order' => $id_order,
                'vieworder' => 1
            );
            $url = (
                version_compare(_PS_VERSION_, '1.7.7', '>=') ?
                Context::getContext()->link->getAdminLink('AdminOrders', true, $vars_queries) :
                Context::getContext()->link->getAdminLink('AdminOrders').'&'.self::implodeKey('&', $vars_queries)
            );
        }
        return $url;
    }

    public static function alignQuantities($offset = false, $limit = false, $validateBefore = false, $action = null)
    {
        $line_count = 0;
		if (Tools::getIsset('action') && Tools::getValue('action')) {
			$action = Tools::getValue('action');
		}

        $result = self::getProductsWithCombinationsFromDb($offset, $limit);
        foreach ($result as $row) {
            $line_count++;
            if (!$validateBefore) {
                $id_product = (int)$row['id_product'];
                $product = new Product($id_product, false);
                if (Validate::isLoadedObject($product)) {
                    $id_product_attribute = (int)$row['id_product_attribute'];

                    // Improvement: Add stock trace if not yet to avoid future errors
                    $associated_warehouses = self::getAssociatedWarehousesArray(
                        $id_product,
                        $id_product_attribute
                    );
                    if (count($associated_warehouses) >= 1) {
                        foreach ($associated_warehouses as $id_warehouse) {
                            if (!WarehouseStock::productIsPresentInStock($id_product, $id_product_attribute, $id_warehouse)) {
                                // Add stock trace (define 0 as default quantity)
                                (new WorkshopAsm())->addProduct(
                                    $id_product,
                                    $id_product_attribute,
                                    (new StoreHouse($id_warehouse)),
                                    0
                                );
                            }
                        }
                    }

					switch ($action) {
						case 'alignQtiesToPrestashop':
							// Set warehouses quantities according to prestashop quantities
							(new WorkshopAsm())->synchronize(
								$id_product,
								$id_product_attribute
							);
							break;
						case 'alignQtiesToWarehouses':
							// Set prestashop quantities according to warehouses quantities
							self::alignPsQuantitiesToWarehousesQuantity($id_product, $id_product_attribute);
							break;
					}
                }
            }
        }
        return $line_count;
    }

    public static function convertSpecialExpression($specialExpression)
    {
        $expression = $specialExpression;
        switch ($specialExpression) {
            case '@yearly':
                $expression = '0 0 1 1 *';
                break;
            case '@annually':
                $expression = '0 0 1 1 *';
                break;
            case '@monthly':
                $expression = '0 0 1 * *';
                break;
            case '@weekly':
                $expression = '0 0 * * 0';
                break;
            case '@daily':
                $expression = '0 0 * * *';
                break;
            case '@midnight':
                $expression = '0 0 * * *';
                break;
            case '@hourly':
                $expression = '0 * * * *';
                break;
        }
        return $expression;
    }

    public function reinjectRefundedQuantity($order_detail, $qty_cancel_product, $delete = false)
    {
        $id_lang = (int)Context::getContext()->language->id;
        $id_shop = (int)$order_detail->id_shop;
        $id_product = (int)$order_detail->product_id;
        $product = new Product($id_product, false, $id_lang, $id_shop);

        if (!Module::isEnabled('wkwarehouses') || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') ||
            !class_exists('WorkshopAsm') || !$product->advanced_stock_management) {
            return false;
        }

        $reinjectable_quantity = (int)$order_detail->product_quantity - (int)$order_detail->product_quantity_reinjected;
        $quantity_to_reinject = $qty_cancel_product > $reinjectable_quantity ? $reinjectable_quantity : $qty_cancel_product;

        if ($order_detail->id_warehouse != 0) {
            $id_product_attribute = (int)$order_detail->product_attribute_id;
            // Update shop quantities
            StockAvailable::updateQuantity(
                $id_product,
                $id_product_attribute,
                $quantity_to_reinject,
                $id_shop,
                true,
                array(
                    'id_order' => $order_detail->id_order,
                    'id_stock_mvt_reason' => Configuration::get('PS_STOCK_CUSTOMER_RETURN_REASON')
                )
            );
            if (class_exists('PrestaShop\PrestaShop\Adapter\StockManager')) {
                (new PrestaShop\PrestaShop\Adapter\StockManager())->updatePhysicalProductQuantity(
                    $id_shop,
                    (int)Configuration::get('PS_OS_ERROR'),
                    (int)Configuration::get('PS_OS_CANCELED'),
                    null,
                    (int)$order_detail->id_order
                );
            }
            if ($delete) {
                $order_detail->delete();
                /* Very important to sync product to update correct quantities in stock_available table */
                if (class_exists('PrestaShop\PrestaShop\Adapter\StockManager')) {
                    (new PrestaShop\PrestaShop\Adapter\StockManager())->updatePhysicalProductQuantity(
                        (int)$id_shop,
                        (int)Configuration::get('PS_OS_ERROR'),
                        (int)Configuration::get('PS_OS_CANCELED'),
                        $id_product
                    );
                }
                $this->synchronize(
                    (int)$order_detail->product_id,
                    (int)$order_detail->product_attribute_id,
                    null,
                    array(),
                    false,
                    $order_detail->id_warehouse
                );
            }
        } else {
            $this->errors[] = $this->trans('This product cannot be re-stocked.', array(), 'Admin.Orderscustomers.Notification');
        }
    }

    /*
     * Generate correctly the address for an email
     */
    public static function getFormatedAddress(Address $address, $line_sep, $fields_style = [])
    {
        return AddressFormat::generateAddress($address, ['avoid' => []], $line_sep, ' ', $fields_style);
    }

    public static function getAllMessages($id)
    {
        $messages = Db::getInstance()->executeS(
			'SELECT `message`
			 FROM `'._DB_PREFIX_.'message`
			 WHERE `id_order` = ' . (int) $id . '
			 ORDER BY `id_message` ASC'
		);
        $result = array();
        foreach ($messages as $message) {
            $result[] = $message['message'];
        }
        return implode('<br/>', $result);
    }

    public static function copyEmailTmpl($tmpl, $mails_dir, $id_lang = null)
    {
        if (empty($id_lang)) {
            $id_lang = (int)Context::getContext()->language->id;
        }
        $iso = Language::getIsoById($id_lang);

        if (!in_array($iso, array('fr', 'en'))) {
            if (!file_exists($mails_dir.$iso.'/'.$tmpl.'.txt') || !file_exists($mails_dir.$iso.'/'.$tmpl.'.html')) {
                if (!is_dir($mails_dir.$iso)) {
                    mkdir($mails_dir.$iso); // 0777 : default chmod for the mkdir function
                }
                copy($mails_dir.'en/'.$tmpl.'.txt', $mails_dir.$iso.'/'.$tmpl.'.txt');
                copy($mails_dir.'en/'.$tmpl.'.html', $mails_dir.$iso.'/'.$tmpl.'.html');
                copy($mails_dir.'en/index.php', $mails_dir.$iso.'/index.php');
            }
        }
    }

    public static function getBestCarriersForNotAsmProducts($country, $package, $cart)
	{
		$ids_carriers_tmp = $carriers_instance = array();
		$best_price = $best_grade = null;
		// For each carrier of the package, look for the best carriers according to the best price and grade
		foreach ($package['carrier_list'] as $id_carrier) {
			if (!isset($carriers_instance[$id_carrier])) {
				$carriers_instance[$id_carrier] = new Carrier($id_carrier);
			}
			if (Configuration::get('WKWAREHOUSE_PRODUCT_NOT_ASM_GET_BEST_CARRIERS')) {
				$price_with_tax = $cart->getPackageShippingCost($id_carrier, true, $country, $package['product_list']);
				$price_without_tax = $cart->getPackageShippingCost($id_carrier, false, $country, $package['product_list']);
				if (null === $best_price || $price_with_tax < $best_price) {
					$best_price = $price_with_tax;
					$ids_carriers_tmp[] = $id_carrier;
				}
				$grade = $carriers_instance[$id_carrier]->grade;
				if (null === $best_grade || $grade > $best_grade) {
					$best_grade = $grade;
					$ids_carriers_tmp[] = $id_carrier;
				}
			} else {
				$ids_carriers_tmp[] = $id_carrier;
			}
		}
		return $ids_carriers_tmp;
	}

    public static function generateCombinations($arrays, $i = 0)
	{
		if (!isset($arrays[$i])) {
			return array();
		}
		if ($i == count($arrays) - 1) {
			return $arrays[$i];
		}
		// get combinations from subsequent arrays
		$tmp = self::generateCombinations($arrays, $i + 1);
		$result = array();
		// concat each array from tmp with each element from $arrays[$i]
		// !Important: Preserve the keys indexes ($index and $index_tmp are added by K.W)
		foreach ($arrays[$i] as $index => $v) {
			foreach ($tmp as $index_tmp => $t) {
				if (is_array($t)) {
					$result[] = array($index=> $v) + $t; // array_merge replaced by (+) to preserve the keys
				} else {
					$result[] = array($index => $v, $index_tmp => $t);
				}
			}
		}
		return $result;
	}

    public static function scanFolder($folder, $way = 'ASC')
    {
        $cursors = array();
        $files = scandir(self::getPath($folder));
        if ($way == 'ASC') {
            sort($files);
        } else {
            rsort($files);
        }
        foreach ($files as $k => &$filename) {
            //exclude .  ..  .svn and index.php and all hidden files
            if (preg_match('/^\..*|index\.php/i', $filename) || is_dir(self::getPath($folder).$filename)) {
                unset($files[$k]);
            } else {
                array_push(
                    $cursors,
                    array(
                        'id' => $filename,
                        'value' => str_replace('.'.Tools::strtolower(pathinfo($filename, PATHINFO_EXTENSION)), '', $filename),
                        'label' => _MODULE_DIR_.'wkwarehouses/views/img/'.$folder.'/'.$filename,
                    )
                );
            }
        }
        return $cursors;
    }

    public static function getPath($folder, $file = '')
    {
        return _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'modules'
        .DIRECTORY_SEPARATOR.'wkwarehouses'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'img'
        .DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.$file;
    }

    public static function isAdminAuth()
    {
        $connected = false;
        Context::getContext()->cookie = new Cookie('psAdmin');

        if (Context::getContext()->cookie->id_employee > 0) {
            Context::getContext()->employee = new Employee(Context::getContext()->cookie->id_employee);
            if (Context::getContext()->employee->isLoggedBack()) {
                $connected = true;
            }
        }
        return $connected;
    }

    public static function getSuperAdminEmployeeByProfile($id_profile, $active_only = false)
    {
        return Db::getInstance()->executeS(
            'SELECT *
             FROM `'._DB_PREFIX_.'employee`
             WHERE `id_profile` = '.(int)$id_profile.' '.($active_only ? ' AND `active` = 1' : '').'
             ORDER BY id_employee ASC LIMIT 1'
        );
    }

    public static function getNbDaysModuleUsage()
    {
        return (int)Db::getInstance()->getValue(
            'SELECT DATEDIFF(NOW(), date_add)
             FROM '._DB_PREFIX_.'configuration
             WHERE name = \''.pSQL('WKWAREHOUSE_LAST_VERSION').'\'
             ORDER BY date_add ASC'
        );
    }

    public static function cleanBuffer()
    {
        if (ob_get_length() > 0) {
            ob_clean();
        }
    }
}
