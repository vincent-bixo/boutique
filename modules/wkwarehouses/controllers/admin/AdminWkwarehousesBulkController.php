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

class AdminWkwarehousesbulkController extends ModuleAdminController
{
	private $bulk_path;

    public function __construct()
    {
        require_once(dirname(__FILE__).'/../../classes/WorkshopBulk.php');

        $this->bootstrap = true;
        $this->display = 'view';
        $this->toolbar_title = $this->l('Actions in bulk');
        $this->bulk_path = _PS_MODULE_DIR_.'wkwarehouses/views/templates/admin/wkwarehousesbulk/helpers/view/';

        parent::__construct();

        $displayWarning = false;
		if (!Module::isInstalled('wkwarehouses') || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
			$displayWarning = true;
		} else {
			$moduleAsm = Module::getInstanceByName('wkwarehouses');
			if (!$moduleAsm->active) {
				$displayWarning = true;
			}
		}
        if ($displayWarning) {
            $this->displayWarning(
				$this->l('Warning: please enable the « Wk Warehouses Management » module to be able to use fully this feature.')
			);
        }
    }

    /*
     * Render General View
    */
    public function renderView()
    {
        $id_lang = (int)$this->context->language->id;
        $iso_code = $this->context->language->iso_code;
        $workshop = new WorkshopBulk();

        // Categories Tree
        $categories = Category::getCategories($id_lang, false);
        $current = current($categories);
        $current = $current[key($current)];
        $workshop->recurseWkcatProds($categories, $current, $current['infos']['id_category'], 0);

        // Prepare Attributes Filter (1st panel)
        $AttributesGroups = WorkshopBulk::getAttributesGroups();
        if (sizeof($AttributesGroups)) {
            foreach ($AttributesGroups as $k => $group) {
                // Get attributes for this group
                $AttributesGroups[$k]['attributes'] = AttributeGroup::getAttributes(
                    $id_lang,
                    $group['id_attribute_group']
                );
            }
        }

        // Get warehouses list
        $warehouses = StoreHouse::getWarehouses();

        $this->tpl_view_vars = array(
            'suppliers' => Supplier::getSuppliers(false, (int)$id_lang),
            'options_cats' => $workshop->options,
            'manufacturers' => Manufacturer::getManufacturers(),
            'attributes' => $AttributesGroups, // 1st panel: to filter products
            'warehouses' => $warehouses,
            'currency' => $this->context->currency,
            'tooltip_move' => $this->l('Select a warehouse from filters above to look for products you want to move them to another target warehouse.'),
            'tooltip_transfer_qty' => array(
				$this->l('This option is available only for Products :'),
                '- '.$this->l('without warehouses associations.'),
                '- '.$this->l('using "Normal stock managment".'),
                '- '.$this->l('using "Advanced stock managment" (that quantities in warehouses are not defined yet).'),
                '- '.$this->l('if you plan to move a product from one warehouse to another.')
			),
            'module_folder' => _MODULE_DIR_.$this->module->name,
            'lang_datatable' => (!in_array($iso_code, array('fr', 'en')) ? 'en' : $iso_code),
        );
        $this->base_tpl_view = 'form.tpl';

        return parent::renderView();
    }

    /*
    * Get filtered Products from database
    */
    public function ajaxProcessFilterProductsDatatables()
    {
        die(json_encode(
            WorkshopBulk::advSearchProducts((int)Tools::getValue('id_cat'), false)
        ));
    }

    /*
     * Display products of selected warehouses (multiple select - right panel)
    */
    public function ajaxProcessProductsWarehousesDatatables()
    {
        $ids_target = (Tools::getIsset('ids_target') ? Tools::getValue('ids_target') : '');

        if (!empty($ids_target) || (Tools::getIsset('id_brand') && Tools::getValue('id_brand'))) {
            die(json_encode(
                WorkshopBulk::advSearchProducts(false, $this->getAllSelectedWarehouses($ids_target))
            ));
        } else {// No data to render by default
            die(json_encode(array(
				'draw' => 0,
				'recordsTotal' => 0,
				'recordsFiltered' => 0,
				'data' => array()
			)));
        }
    }

    /*
     * Associate warehouses to product(s)
    */
    public function ajaxProcessBeginProcess()
    {
        $offset = (int)Tools::getValue('offset');
        $limit = (int)Tools::getValue('limit');
        $validateBefore = ((int)Tools::getValue('validateBefore') == 1);

        /*if ($offset === 0) {// Execute once at the beginning
        }*/
        $results = array();
        $this->processByGroups($offset, $limit, $results, $validateBefore);

        // Retrieve errors/warnings if any
        if (count($this->errors) > 0) {
            $results['errors'] = $this->errors;
        }
        if (count($this->warnings) > 0) {
            $results['warnings'] = $this->warnings;
        }
        if (count($this->informations) > 0) {
            $results['informations'] = $this->informations;
        }
        /*if (!$validateBefore && (bool)$results['isFinished']) {// update is finished
        }*/
        die(json_encode($results));
    }

    public function processByGroups($offset = false, $limit = false, &$results = null, $validateBefore = false)
    {
        $doneCount = 0;
        $process = Tools::getValue('process');

        if (method_exists('Db', 'disableCache')) {
            Db::getInstance()->disableCache();
        }
        switch ($process) {
            case 'associateWarehouses':
                $doneCount += $this->associateProducts($offset, $limit, $validateBefore);
                break;
            case 'reinitiateStock':
                $doneCount += $this->resetQuantitiesProducts($offset, $limit, $validateBefore);
                break;
            case 'removeAssociations':
                $doneCount += $this->removeWarehousesAssociations($offset, $limit, $validateBefore);
                break;
            case 'processAsmQuantities':
                $doneCount += $this->updateAsmQuantities($offset, $limit, $validateBefore);
                break;
            case 'processAddLocations':
                $doneCount += $this->addWarehousesLocations($offset, $limit, $validateBefore);
                break;
        }

        $this->clearSmartyCache();

        if ($results !== null) {
            $results['isFinished'] = ($doneCount < $limit);
            $results['doneCount'] = $offset + $doneCount;

            if ($offset === 0) {
                if (!Configuration::get('PS_STOCK_MANAGEMENT')) {
                    $this->errors[] = $this->l('Error: stock management is disabled.');
                }
                if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') &&
                    (Tools::getIsset('turn_on_depends_on_stock') || Tools::getIsset('turn_on_asm'))) {
                    $this->errors[] = $this->l('Warning: Advanced stock management is disabled!');
                }

                // compute total count only once
                if (Tools::getIsset('chk_products') && ($products_ids = Tools::getValue('chk_products'))) {
                    $count = count($products_ids);
                    $results['totalCount'] = $count;
                    if ($count == 0) {
                        $this->errors[] = $this->l('No products found to process!');
                    }
                }
            }
            if (!$results['isFinished']) {
                // Since we'll have to POST this array from ajax for the next call, we should care about its size.
                $results['nextPostSize'] = 1024*64; // 64KB more for the rest of the POST query.
                $results['postSizeLimit'] = Tools::getMaxUploadSize();
            }
        }

        $log_message = $this->l('Processing Products');
        if ($offset !== false && $limit !== false) {
            $log_message .= ' '.sprintf($this->l('(from %s to %s)'), $offset, $limit);
        }
        PrestaShopLogger::addLog($log_message, 1, null, 'Products', null, true, (int)$this->context->employee->id);

        if (method_exists('Db', 'enableCache')) {
            Db::getInstance()->enableCache();
        }
    }

    public function getAllSelectedWarehouses($ids_warehouses)
    {
		if (!empty($ids_warehouses)) {
			$warehouses_to_process = array();
			if (in_array('ALL', $ids_warehouses)) {// if all warehouses selected
				$warehouses = StoreHouse::getWarehouses();
				foreach ($warehouses as $row) {
					$warehouses_to_process[] = (int)$row['id_warehouse'];
				}
			} else {
				$warehouses_to_process = WorkshopBulk::castArray($ids_warehouses);
			}
			return $warehouses_to_process;
		}
		return false;
    }

    /*
     * Associate warehouses to product(s)
    */
    public function associateProducts($offset = false, $limit = false, $validateBefore = false)
    {
        $ids_target = explode(',', Tools::getValue('ids_target')); // Warehouses ids targets
        $products_ids = WorkshopBulk::castArray(Tools::getValue('chk_products'));
        $ps_asm = Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT');
        $id_shop = (int)$this->context->shop->id;

        $warehouses_to_associate = $this->getAllSelectedWarehouses($ids_target);

        // Prepare post data
        $transfer_qty_on = $move_on = false;
        if (Tools::getIsset('transfer_qty') && Tools::getValue('transfer_qty')) {
            $transfer_qty_on = true;
            $stock_manager = new WorkshopAsm();
        }
        if (Tools::getIsset('move_on') && Tools::getValue('move_on')) {
            $move_on = true;
        }
        if (Tools::getIsset('id_warehouse_from') && Tools::getValue('id_warehouse_from')) {
            $id_warehouse_from = Tools::getValue('id_warehouse_from');
        }
        /********************/

        // Load products with pagination
        $result = WorkshopBulk::getProductsFromDatabase($products_ids, $offset, $limit);

        // B E G I N   P R O C E S S
        $line_count = 0;
        foreach ($result as $row) {
            $line_count++;
            $product = new Product((int)$row['id_product'], false);

            $turn_asm_on = false;
            /*
            * Case when products are specified manually (using Normal Stock Management)
            * Turn on "A.S.M"
            */
            if ((Tools::getValue('turn_on_asm') && Tools::getIsset('turn_on_asm')) ||
                (Tools::getIsset('turn_on_depends_on_stock') && Tools::getValue('turn_on_depends_on_stock'))) {
                if (!Product::usesAdvancedStockManagement($product->id)) {
                    $product->setAdvancedStockManagement(1);
                }
                $turn_asm_on = true;
            }

            if (!$validateBefore) {
                // Get all id_product_attribute from ps_product_attribute table
                $products_attributes = Product::getProductAttributesIds($product->id);
                // Collect quantity for each product, combination
                if (!empty($products_attributes)) {
                    foreach ($products_attributes as &$attribute) {
                        $attribute['qty'] = (int)StockAvailable::getQuantityAvailableByProduct(
                            $product->id,
                            $attribute['id_product_attribute'],
                            $id_shop
                        );
                    }
                } else {
                    $products_attributes[] = array(
                        'id_product_attribute' => 0,
                        'qty' => (int)StockAvailable::getQuantityAvailableByProduct($product->id, 0, $id_shop)
                    );
                }

                // Can I transfer qty ?
                $productsInStock = WorkshopBulk::productsInStock($product->id);

                /*
                * --------------------
                * Associate warehouse(s) to products
                * Transfer also quantities
                */
                foreach ($warehouses_to_associate as $id_warehouse) {
                    $existWarehouse = StoreHouse::exists($id_warehouse);

                    if ($existWarehouse) {
                        foreach ($products_attributes as $product_attribute) {
                            $id_pa = (int)$product_attribute['id_product_attribute'];

                            // Create entry to product location for each attribute if not exists
                            $warehouse_location_entity = $this->getProductLocationClass();
                            $warehouse_location_entity->id_product = $product->id;
                            $warehouse_location_entity->id_product_attribute = $id_pa;
                            $warehouse_location_entity->id_warehouse = $id_warehouse;

                            $getProductLocation = StorehouseProductLocation::getProductLocation($product->id, $id_pa, $id_warehouse);

                            if ($getProductLocation !== false) {
                                $warehouse_location_entity->update();
                            } else {
                                $warehouse_location_entity->save();
                            }
                            /*
                            * Transfer (just copy stock, not move warehouse) and synchronize quantities
                            * IF "Normal stock"
                            */
                            if ($transfer_qty_on && !$move_on) {
                                if (Tools::getIsset('turn_on_depends_on_stock') || $turn_asm_on) {
                                    // Get wholesale price
									$productPrice = $product->wholesale_price;
									if ($id_pa) {
										$combination = new Combination((int)$id_pa);
										if ($combination->id_product == $product->id &&
											Validate::isLoadedObject($combination) && $combination->wholesale_price != '0.000000') {
											$productPrice = $combination->wholesale_price;
										}
									}

									if (!$productsInStock) {
										$stock_manager->addProduct(
											$product->id,
											$id_pa,
											(new StoreHouse($id_warehouse)),
											$product_attribute['qty']
										);
									}
                                }
                            }
                            // If moving product from one warehouse to another warehouse
                            // so stock will be transferred at the same time
                            if ($move_on) {
                                // if transfer quantity from warehouse to another warehouse
                                if (isset($id_warehouse_from) && !empty($id_warehouse_from) && is_numeric($id_warehouse_from)) {
                                    if ($transfer_qty_on) {
                                        WorkshopBulk::transferBetweenWarehouses(
                                            $product->id,
                                            $id_pa,
                                            (int)$id_warehouse_from,
                                            $id_warehouse
                                        );
                                    } else {
                                        if ($id_warehouse_from != $id_warehouse) {
                                            WorkshopBulk::removeFromProductLocation(
                                                $product->id,
                                                $id_pa,
                                                $id_warehouse_from
                                            );
                                        }
                                    }
                                }
                            }
                            /* Sync Prestashop quantities : usefull even if we want to fix quantities gap */
                            if ($transfer_qty_on && class_exists('WorkshopAsm')) {
                                WorkshopAsm::updatePhysicalProductAvailableQuantity($product->id);
                                $stock_manager->synchronize($product->id, $id_pa, null, array(), false, $id_warehouse);
                            }
                        }
                    }
                }
                /*
                * Clean warehouse product locations when moving product to another warehouse
                * Transfer quantity option enabled
                */
                if ($move_on && isset($id_warehouse_from) && is_numeric($id_warehouse_from) && $transfer_qty_on) {
                    // remove all associations of warehouse locations from
                    WorkshopBulk::removeFromProductLocation($product->id, null, $id_warehouse_from);
                }
            }
        }

        return $line_count;
    }

    /*
     * Reset Stock for Products selection
    */
    public function resetQuantitiesProducts($offset = false, $limit = false, $validateBefore = false)
    {
        $products_ids = WorkshopBulk::castArray(Tools::getValue('chk_products'));

        // Load products with pagination
        $result = WorkshopBulk::getProductsFromDatabase($products_ids, $offset, $limit);

        // B E G I N   P R O C E S S
        $line_count = 0;
        foreach ($result as $row) {
            $line_count++;

            if (!$validateBefore) {
                $product = new Product((int)$row['id_product'], false, (int)$this->context->language->id);

                // Get all product attributes
                $products_attributes = Product::getProductAttributesIds($product->id);
                if (empty($products_attributes)) {
                    $products_attributes[] = array('id_product_attribute' => 0);
                }

				// Sync with warehouses quantities using the appropriate hook
				foreach ($products_attributes as $product_attribute) {
					StockAvailable::setQuantity(
						$product->id,
						$product_attribute['id_product_attribute'],
						0,
						(int)$this->context->shop->id
					);
				}
            }
        }
        return $line_count;
    }

    /*
     * Increase/Decrease qty for selected products / target warehouses
    */
    public function updateAsmQuantities($offset = false, $limit = false, $validateBefore = false)
    {
        $products_ids = WorkshopBulk::castArray(Tools::getValue('chk_products'));
        $ids_target = explode(',', Tools::getValue('ids_target')); // Warehouses ids targets
        $warehouses_to_update = $this->getAllSelectedWarehouses($ids_target);
        $asm_qty = (int)Tools::getValue('asm_qty');
        $stock_manager = new WorkshopAsm();

        // Load products with pagination
        $result = WorkshopBulk::getProductsFromDatabase($products_ids, $offset, $limit);

        // B E G I N   P R O C E S S
        $line_count = 0;
        foreach ($result as $row) {
            $line_count++;
            $product = new Product((int)$row['id_product'], false);

            if ($validateBefore) {
                if (!$product->advanced_stock_management) {
                    $this->warnings[] = sprintf(
                        $this->l('The stock of product (%s) is not based on advanced stock management system!'),
                        $row['id_product']
                    );
                }
            }

            if (!$validateBefore) {
                $warehouses_collection = WorkshopBulk::getCollectionByWarehouses($product->id, null, $warehouses_to_update);
                foreach ($warehouses_collection as $awc) {
                    // Update stock
					$stock_manager->addProduct(
						$product->id,
						$awc->id_product_attribute,
						(new StoreHouse((int)$awc->id_warehouse)),
						$asm_qty
					);
					StockAvailable::updateQuantity(/* call hook also to sync auto quantities */
						$product->id,
						$awc->id_product_attribute,
						$asm_qty,
						(int)$this->context->shop->id,
						true// Add Mvt
					);
                }
                WorkshopBulk::updatePhysicalProductAvailableQuantity($product->id);
            }
        }
        return $line_count;
    }

    /*
     * Add location for selected products / target warehouses
    */
    public function addWarehousesLocations($offset = false, $limit = false, $validateBefore = false)
    {
        $products_ids = WorkshopBulk::castArray(Tools::getValue('chk_products'));
        $ids_warehouses = explode(',', Tools::getValue('ids_target')); // Warehouses ids targets
        $warehouses_to_update = $this->getAllSelectedWarehouses($ids_warehouses);
        $location = Tools::getValue('warehouse_location_label');

        // Load products with pagination
        $result = WorkshopBulk::getProductsFromDatabase($products_ids, $offset, $limit);

        // B E G I N   P R O C E S S
        $line_count = 0;
        foreach ($result as $row) {
            $line_count++;
            $product = new Product((int)$row['id_product'], false);
			$has_attributes = $product->hasAttributes();

            if (!$validateBefore) {
				/* if simple product*/
				if (!$has_attributes) {
					foreach ($ids_warehouses as $id_warehouse) {
						$wpl_id = (int)StorehouseProductLocation::getIdByProductAndWarehouse(
							$product->id,
							0,
							$id_warehouse
						);
						if (empty($wpl_id)) {/* create new record */
							$warehouse_location_entity = $this->getProductLocationClass();
							$warehouse_location_entity->id_product = (int)$product->id;
							$warehouse_location_entity->id_product_attribute = 0;
							$warehouse_location_entity->id_warehouse = (int)$id_warehouse;
							$warehouse_location_entity->location = pSQL($location);
							$warehouse_location_entity->save();
						} else {/* if already exist, update */
							$warehouse_location_entity = $this->getProductLocationClass($wpl_id);
							$warehouse_location_entity->location = pSQL($location);
							$warehouse_location_entity->update();
						}
					}
				} else {
				/* Else product with combinations */
					foreach ($ids_warehouses as $id_warehouse) {
						/* get all available product attributes combinations */
						$combinations_data = $product->getAttributeCombinations($this->context->language->id);
						$combinations = ObjectModel::hydrateCollection('Combination', $combinations_data);
						foreach ($combinations as $combination) {
							$wpl_id = (int)StorehouseProductLocation::getIdByProductAndWarehouse(
								$product->id,
								$combination->id,
								$id_warehouse
							);
							if (empty($wpl_id)) {/* create new record */
								$warehouse_location_entity = $this->getProductLocationClass();
								$warehouse_location_entity->id_product = (int)$product->id;
								$warehouse_location_entity->id_product_attribute = $combination->id;
								$warehouse_location_entity->id_warehouse = (int)$id_warehouse;
								$warehouse_location_entity->location = pSQL($location);
								$warehouse_location_entity->save();
							} else {/* if already exist, update */
								$warehouse_location_entity = $this->getProductLocationClass($wpl_id);
								$warehouse_location_entity->location = pSQL($location);
								$warehouse_location_entity->update();
							}
						}
					}
				}
            }
        }
        return $line_count;
    }

    public function getProductLocationClass($id_warehouse_product_location = null)
    {
		if (!is_null($id_warehouse_product_location)) {
			return new StorehouseProductLocation($id_warehouse_product_location);
		} else {
			return new StorehouseProductLocation();
		}
    }

    /*
     * Remove All Warehouses Associations (With stock) for selected products
    */
    public function removeWarehousesAssociations($offset = false, $limit = false, $validateBefore = false)
    {
        $products_ids = WorkshopBulk::castArray(Tools::getValue('chk_products'));
        $ids_target = explode(',', Tools::getValue('ids_target')); // Warehouses ids targets
        $warehouses_from_delete = (!in_array('ALL', $ids_target) ? WorkshopBulk::castArray($ids_target) : null);

        // Load products with pagination
        $result = WorkshopBulk::getProductsFromDatabase($products_ids, $offset, $limit);

        // B E G I N   P R O C E S S
        $line_count = 0;

        foreach ($result as $row) {
            $line_count++;

            if (!$validateBefore) {
                $product = new Product((int)$row['id_product'], false);

                $warehouses_collection = WorkshopBulk::getCollectionByWarehouses($product->id, null, $warehouses_from_delete);

                /** Delete warehouses associations for this product / target warehouses **/
                foreach ($warehouses_collection as $awc) {
                    /** PS >= 1.7.2 : Delete stocks for this product / warehouses **/
                    if ($product->advanced_stock_management) {
                        WorkshopBulk::deleteWarehouseQtyByProduct($product->id, null, $awc->id_warehouse);
                    }
                    $awc->delete();
                }
            }
        }
        return $line_count;
    }

    public function ajaxProcessInitFormLocations()
    {
        $product_id = (int)Tools::getValue('id_product');
        $template = Tools::getValue('template');
        $id_lang = (int)$this->context->language->id;
        $error = $custom_form = '';

        $product = new Product($product_id);

        if (Validate::isLoadedObject($product)) {
            // Get already associated warehouses
            $warehouses_collection = StorehouseProductLocation::getCollection($product->id);

            // Collect warehouses names
            if ($template == 'view') {
                $warehouses_names = array();
                foreach ($warehouses_collection as $awc) {
                    $warehouse = new StoreHouse($awc->id_warehouse, $id_lang);
                    $warehouses_names[] = $warehouse->name;
                }
                $this->context->smarty->assign(array(
                    'product_name' => $product->name[$id_lang],
                    'warehouses_names' => array_unique($warehouses_names),
                ));
            } else {
                // Get all id_product_attribute
                $attributes = $product->getAttributesResume($id_lang);
                if (empty($attributes)) {
                    $attributes[] = array(
                        'id_product' => $product->id,
                        'id_product_attribute' => 0,
                        'attribute_designation' => ''
                    );
                }

                $product_designation = array();
                foreach ($attributes as &$attribute) {
                    $product_designation[$attribute['id_product_attribute']] = rtrim(
                        $product->name[$id_lang].' - '.$attribute['attribute_designation'],
                        ' - '
                    );
                }

                // Collect stocks
                if ($template == 'stock') {
                    $stocks = array();
                    if ($product->advanced_stock_management) {
                        foreach ($warehouses_collection as $awc) {
                            $tmp = array(
                                'id_product' => (int)$product->id,
                                'id_product_attribute' => (int)$awc->id_product_attribute,
                                'id_warehouse' => (int)$awc->id_warehouse,
                            );
							$tmp['physical_quantity'] = (int)WorkshopAsm::getProductPhysicalQuantities(
								$product->id,
								$awc->id_product_attribute,
								$awc->id_warehouse
							);
							$tmp['available_quantity'] = (int)WarehouseStock::getAvailableQuantityByWarehouse(
								$product->id,
								$awc->id_product_attribute,
								$awc->id_warehouse
							);
                            array_push($stocks, $tmp);
                        }
                    }
                    $this->context->smarty->assign(array(
                        'stocks' => $stocks,
                        'use_asm' => $product->advanced_stock_management,
                    ));
                }

                // Get warehouses list
                $warehouses = StoreHouse::getWarehouses();

                $this->context->smarty->assign(array(
                    'id_lang' => $id_lang,
                    'product' => $product,
                    'attributes' => $attributes,
                    'link' => $this->context->link,
                    'warehouses' => $warehouses,
                    'associated_warehouses' => $warehouses_collection,
                    'product_designation' => $product_designation,
                ));
            }
            $custom_form = $this->context->smarty->fetch(
                $this->bulk_path.$template.'_locations_products.tpl'
            );
        } else {
            $error = $this->l('Error: invalid product');
        }
        die(json_encode(array(
            'html' => $custom_form,
            'error' => $error,
        )));
    }

    public function ajaxProcessInitFormLocation()
    {
        die(json_encode(array(
            'html' => $this->context->smarty->fetch(
                $this->bulk_path.'add_location.tpl'
            ),
        )));
    }

    public function ajaxProcessInitFormManageStock()
    {
        die(json_encode(array(
            'html' => $this->context->smarty->fetch(
                $this->bulk_path.'manage_stock.tpl'
            ),
        )));
    }

    /**
    * Post treatment for warehouses & locations
    */
    public function ajaxProcessProcessWarehousesAndLocations()
    {
        $error = '';

        if (Validate::isLoadedObject($product = new Product((int)$id_product = Tools::getValue('id_product')))) {
            // Get all id_product_attribute
            $attributes = $product->getAttributesResume($this->context->language->id);
            if (empty($attributes)) {
                $attributes[] = array(
                    'id_product_attribute' => 0,
                    'attribute_designation' => ''
                );
            }

            // Get warehouses list
            $warehouses = StoreHouse::getWarehouses();

            // Get already associated warehouses
            $warehouses_collection = StorehouseProductLocation::getCollection($product->id);

            $elements_to_manage = array();

            // get form inforamtion
            foreach ($attributes as $attribute) {
                foreach ($warehouses as $warehouse) {
                    $key = $warehouse['id_warehouse'].'_'.$product->id.'_'.$attribute['id_product_attribute'];
                    // get elements to manage
                    if (Tools::getIsset('check_warehouse_'.$key)) {
                        $location = Tools::getValue('location_warehouse_'.$key, '');
                        $elements_to_manage[$key] = $location;
                    }
                }
            }

            // Delete entry if necessary
            foreach ($warehouses_collection as $awc) {
                /** @var WarehouseProductLocation $awc */
                if (!array_key_exists($awc->id_warehouse.'_'.$awc->id_product.'_'.$awc->id_product_attribute, $elements_to_manage)) {
                    $awc->delete();
                }
            }

            // Manage locations
            foreach ($elements_to_manage as $key => $location) {
                $params = explode('_', $key);

				$wpl_id = (int)StorehouseProductLocation::getIdByProductAndWarehouse(
					$params[1],
					$params[2],
					$params[0]
				);
                if (empty($wpl_id)) {
                    // Create new record
                    $warehouse_location_entity = $this->getProductLocationClass();
                    $warehouse_location_entity->id_product = (int)$params[1];
                    $warehouse_location_entity->id_product_attribute = (int)$params[2];
                    $warehouse_location_entity->id_warehouse = (int)$params[0];
                    $warehouse_location_entity->location = pSQL($location);
                    $warehouse_location_entity->save();
                } else {
                    $warehouse_location_entity = $this->getProductLocationClass($wpl_id);
                    $location = pSQL($location);
                    if ($location != $warehouse_location_entity->location) {
                        $warehouse_location_entity->location = $location;
                        $warehouse_location_entity->update();
                    }
                }
            }
        } else {
            $error = $this->l('Error: invalid product');
        }

        die(json_encode(array(
            'error' => $error,
        )));
    }

    public function clearSmartyCache()
    {
        Tools::enableCache();
        Tools::clearCache($this->context->smarty);
        Tools::restoreCacheSettings();
    }

    public function initModal()
    {
        parent::initModal();

        $modal_content = $this->context->smarty->fetch(
            _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/modal_update_progress.tpl'
        );
        $this->modals[] = array(
             'modal_id' => 'processProgress',
             'modal_class' => 'modal-md',
             'modal_title' => $this->l('Updating your shop...'),
             'modal_content' => html_entity_decode($modal_content)
         );
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJqueryUI('ui.dialog');
        $this->addJqueryPlugin('chosen');

        $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/wkassignwarehouses.css');
        $this->addCSS(_MODULE_DIR_.$this->module->name.'/views/css/datatables.min.css');
        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/datatables.min.js');
        $this->addJS(_MODULE_DIR_.$this->module->name.'/views/js/wkassignwarehouses.min.js');
    }

    public function initToolbar()
    {
        parent::initToolbar();
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['back_to_dashboard'] = array(
            'href' => $this->context->link->getAdminLink('AdminWkwarehousesdash'),
            'desc' => $this->l('Dashboard', null, null, false),
            'icon' => 'process-icon-back'
        );
        parent::initPageHeaderToolbar();
    }

    /*
    * Method Translation Override For PS 1.7 and above
    */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if (method_exists('Context', 'getTranslator')) {
            $this->translator = Context::getContext()->getTranslator();
   			$translated = $this->translator->trans($string, [], 'Modules.Wkwarehouses.Adminwkwarehousesbulkcontroller');
            if ($translated !== $string) {
                return $translated;
            }
        }
        if ($class === null || $class == 'AdminTab') {
            $class = Tools::substr(get_class($this), 0, -10);
        } elseif (Tools::strtolower(Tools::substr($class, -10)) == 'controller') {
            $class = Tools::substr($class, 0, -10);
        }
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }
}
