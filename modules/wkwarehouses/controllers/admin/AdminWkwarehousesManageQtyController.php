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

class AdminWkwarehousesManageQtyController extends ModuleAdminController
{
    public function __construct()
    {
        require_once(dirname(__FILE__).'/../../classes/Warehouse.php');
        require_once(dirname(__FILE__).'/../../classes/WarehouseProductLocation.php');
        require_once(dirname(__FILE__).'/../../classes/WarehousePagination.php');
        require_once(dirname(__FILE__).'/../../classes/WarehouseStock.php');
        require_once(dirname(__FILE__).'/../../classes/WorkshopAsm.php');

        $this->table = 'product';
        $this->className = 'Product';
        $this->list_id = 'product';
        $this->identifier = 'id_product';
        $this->lang = false;
        //$this->multishop_context = Shop::CONTEXT_ALL;
        $this->bootstrap = true;
        $this->toolbar_title = $this->l('Manage Warehouses, Locations and Stock For Products');
        $this->list_no_link = true;

        $this->context = Context::getContext();

        $this->_defaultOrderBy = 'id_product';
        $this->_defaultOrderWay = 'ASC';
        $id_lang = (int)$this->context->language->id;

        $this->_join .= '
            LEFT JOIN `'._DB_PREFIX_.'image` i ON (
                i.`id_product` = a.`id_product` '.(!Shop::isFeatureActive() ? ' AND i.cover = 1' : '').'
            )';

        if (isset($this->context->shop->id) && $this->context->shop->id) {
            $query_shop = ' = '.(int)$this->context->shop->id;
        } elseif (isset($this->context->shop->id_shop_group)) {
            $id_shops = ShopGroup::getShopsFromGroup($this->context->shop->id_shop_group);

            $array_shop = array();
            foreach ($id_shops as $key => $value) {
                unset($key);
                $array_shop[] = (int)$value['id_shop'];
            }
            $query_shop = ' IN ('.implode(',', $array_shop).')';
        } else {
            $query_shop = ' = '.(int)Configuration::get('PS_SHOP_DEFAULT');
        }

        $alias = 'ps';
        $this->_join .= ' JOIN `'._DB_PREFIX_.'product_shop` ps ON (
            a.`id_product` = ps.`id_product` AND ps.`id_shop`'.$query_shop.'
        )
        LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
            a.`id_product` = pl.`id_product` AND pl.`id_lang` = '.$id_lang.'
        )';
        if (Shop::isFeatureActive()) {
            $alias_image = 'image_shop';
            $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (
                '.$alias.'.`id_category_default` = cl.`id_category` AND 
                cl.`id_lang` = '.$id_lang.' AND cl.`id_shop`'.$query_shop.'
            )';
            if (Shop::getContext() == Shop::CONTEXT_SHOP) {
                $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'shop` shop ON (
                    shop.id_shop = '.(int)$this->context->shop->id.'
                ) ';
            } else {
                $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'shop` shop ON (shop.`id_shop`'.$query_shop.') ';
            }
            $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'image_shop` image_shop ON (
                image_shop.`id_image` = i.`id_image` AND 
                image_shop.`cover` = 1 AND image_shop.`id_shop`'.$query_shop.'
            )';
            $this->_select .= 'shop.name as shopname, ';
        } else {
            $alias_image = 'i';
            $this->_join .= '
                LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (
                    '.$alias.'.`id_category_default` = cl.`id_category` AND 
                    pl.`id_lang` = cl.`id_lang` AND cl.`id_shop`'.$query_shop.'
                ) ';
        }
        $this->_select .= 'ps.advanced_stock_management as asm, ';

        // Improve performance
        $comb_feature = Combination::isFeatureActive() ? true : false;

        /* Search products which warehouses quantities sum do not match the physical quantity */
        $product_type = $this->getCurrentProductsTypeFilter();
        /* Search by reference (product/combination) */
        $Filter_reference = Tools::getValue($this->table.'Filter_reference');
        if ($comb_feature && (!empty($Filter_reference) || (!empty($product_type) && isset($product_type)))) {
            /* Allowing to search also by product attribute reference in addition with product reference.
             *  see processFilter() function below : how to override product reference filter */
            $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (a.`id_product` = pa.`id_product`) ';
        }
        /*
        * --------------------------------------------------------
        * Process Custom Filters if submitted
        * --------------------------------------------------------
        */
        if (!empty($product_type)) {
            switch ($product_type) {
				/* Search products which warehouses quantities sum don't match the physical quantity */
                case 1:
                    $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'stock_available` sa ON (
                        sa.`id_product` = a.`id_product`
                        '.($comb_feature ? ' AND sa.`id_product_attribute` = IF(pa.`id_product_attribute` > 0, pa.`id_product_attribute`, 0)' : '').'
                        '.StockAvailable::addSqlShopRestriction(null, null, 'sa').'
                    ) ';
                    $this->_where .= ' AND ps.advanced_stock_management = 1 AND sa.physical_quantity <> (
                        SELECT SUM(s.`physical_quantity`) as total
                        FROM `'._DB_PREFIX_.'stock` s 
                        INNER JOIN '._DB_PREFIX_.'warehouse_product_location wpl ON (
                            wpl.id_product = s.id_product AND 
                            wpl.id_product_attribute = s.id_product_attribute AND 
                            wpl.id_warehouse = s.id_warehouse
                        )
                        WHERE s.`id_product` = a.`id_product`
                        '.($comb_feature ? ' AND s.`id_product_attribute` = IF(pa.`id_product_attribute` > 0, pa.`id_product_attribute`, 0)' : '').'
                    ) ';
                    break;
				/* Search products with/without combinations */
                case 2:
                case 3:
					$this->_where .= ' AND (
						SELECT COUNT(*) FROM `'._DB_PREFIX_.'product_attribute` pa
						'.Shop::addSqlAssociation('product_attribute', 'pa').'
						WHERE pa.`id_product` = a.id_product
					) '.($product_type == 2 ? '>' : '=').' 0';
                    break;
				/* Search products which warehouses reserved quantity don't match the global reserved quantity */
                case 4:
                    $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'stock_available` sa ON (
                        sa.`id_product` = a.`id_product`
                        '.($comb_feature ? ' AND sa.`id_product_attribute` = IF(pa.`id_product_attribute` > 0, pa.`id_product_attribute`, 0)' : '').'
                        '.StockAvailable::addSqlShopRestriction(null, null, 'sa').'
                    ) ';
                    $this->_where .= ' AND ps.advanced_stock_management AND sa.reserved_quantity <> (
						'.WorkshopAsm::getReservedQuery().'
                    ) ';
                    break;
            }
        }

        /* Search by suppliers */
        $id_suppliers = $this->getCurrentProvidersFilter();
        if (is_array($id_suppliers) && !empty($id_suppliers)) {
            $this->_where .= ' AND (
                SELECT COUNT(DISTINCT(sp.`id_supplier`)) FROM `'._DB_PREFIX_.'product_supplier` sp 
                WHERE sp.`id_product` = a.`id_product` AND sp.`id_supplier` IN ('.pSQL(implode(',', $id_suppliers)).')
            ) > 0 ';
        }
        /* Search by warehouses */
        $id_warehouses = $this->getCurrentWarehousesFilter();
        if (is_array($id_warehouses) && !empty($id_warehouses)) {
            $this->_where .= ' AND (
                SELECT COUNT(DISTINCT(wl.`id_warehouse`)) FROM `'._DB_PREFIX_.'warehouse_product_location` wl 
                WHERE wl.`id_product` = a.`id_product` AND wl.`id_warehouse` IN ('.pSQL(implode(',', $id_warehouses)).')
            ) > 0 ';
        }
        /* Search by category name */
        $Filter_category = Tools::getValue($this->table.'Filter_categoryName');
        if (!empty($Filter_category)) {
            $ids_categories_filter = array();
            $category = Category::searchByName($this->context->language->id, $Filter_category);
            foreach ($category as $cat) {
                $ids_categories_filter[] = (int)$cat['id_category'];
            }
            $this->_where .= ' AND a.`id_product` IN (
                SELECT cp.`id_product` FROM `'._DB_PREFIX_.'category_product` cp 
                WHERE cp.`id_category` IN ('.(!empty($ids_categories_filter) ? implode(',', $ids_categories_filter) : 0).')
            ) ';
        }
        /* Discare packs products */
        $this->_where .= ' AND a.`id_product` NOT IN (
            SELECT DISTINCT(`id_product_pack`) FROM `' . _DB_PREFIX_ . 'pack`
        ) AND state = 1 ';
        /* Discare virtual products */
        $this->_where .= ' AND a.`is_virtual` = 0 ';

        /* -------------------------------------------------------------*/
        $this->_join .= 'LEFT JOIN `'._DB_PREFIX_.'supplier` sp ON (a.`id_supplier` = sp.`id_supplier`)';
        $this->_select .= 'cl.name `categoryName`, '.$alias_image.'.`id_image`, pl.`name`, a.`reference`, 
        a.`id_product` AS id_prod, a.`id_product` as `image`, sp.`name` as `supplier_name`';
        $this->_group = 'GROUP BY a.id_product';

        $this->fields_list = array();
        $this->fields_list['id_product'] = array(
            'title' => 'ID',
            'align' => 'center',
            'type' => 'int',
            'class' => 'fixed-width-xs tr-product',
            'filter_key' => 'a!id_product',
        );
        $this->fields_list['image'] = array(
            'title' => 'Photo',
            'image' => 'p',
            'align' => 'center',
            'class' => 'text-center tr-product',
            'orderby' => false,
            'filter' => false,
            'search' => false,
        );
        $this->fields_list['name'] = array(
            'title' => $this->l('Name'),
            'class' => 'text-left tr-product',
            'filter_key' => 'pl!name'
        );
        $this->fields_list['reference'] = array(
            'title' => $this->l('Reference'),
            'class' => 'text-left tr-product',
            'width' => 80
        );
        $this->fields_list['supplier_name'] = array(
            'title' => $this->l('Supplier'),
            'class' => 'text-left tr-product',
            'width' => 100,
            'filter_key' => 'sp!name',
            'orderby' => false,
            'search' => false
        );
        $this->fields_list['categoryName'] = array(
            'title' => $this->l('Category'),
            'width' => 230,
            'class' => 'text-left tr-product',
            //'filter_key' => 'cl!name',
        );
        $this->fields_list['asm'] = array(
            'title' => $this->l('Use A.S.M'),
            'hint' => $this->l('Use the Advanced Stock Management System?'),
            'active' => 'default',
            'align' => 'center',
            'class' => 'tr-product',
            'width' => 70,
            'type' => 'bool',
            'filter_key' => 'ps!advanced_stock_management',
        );
        $this->fields_list['active'] = array(
            'title' => $this->l('Status'),
            'active' => 'status',
            'align' => 'text-center',
            'type' => 'bool',
            'class' => 'tr-product',
            'orderby' => false,
            'ajax' => true,
            'filter_key' => 'ps!active',
        );
        $this->fields_list['id_prod'] = array(
            'title' => '',
            'width' => 35,
            'align' => 'center',
            'callback' => 'viewProductAttributes',
            'orderby' => false,
            'class' => 'tr-product',
            'search' => false,
            'remove_onclick' => true
        );
        parent::__construct();
    }

    // Change product status from list directly
    public function ajaxProcessStatusProduct()
    {
        $id_product = (int)Tools::getValue('id_product');
        $obj = new Product($id_product, false);
        if (Validate::isLoadedObject($obj)) {
            $obj->active = $obj->active ? 0 : 1;
            if (!$obj->update()) {
                $result = array('success' => 0, 'text' => $this->l('An error occurred during updating product status!'));
            } else {
                $result = array('success' => 1, 'text' => $this->l('The status has been updated successfully.'));
            }
        } else {
            $result = array('success' => 0, 'text' => $this->l('Can not load product!'));
        }
        die(json_encode($result));
    }

    protected function getCurrentProductsTypeFilter()
    {
        $product_type = 0;
		if (Tools::getIsset($this->table.'Filter_product_type') && Tools::getValue($this->table.'Filter_product_type')) {
        	$product_type = (int)Tools::getValue($this->table.'Filter_product_type');
        }
        return $product_type;
    }

    public function getCurrentProvidersFilter()
    {
        $id_filters = '';
		if (Tools::getIsset($this->table.'Filter_providers') && Tools::getValue($this->table.'Filter_providers')) {
            $id_filters = Tools::getValue($this->table.'Filter_providers');
			if (!is_array($id_filters) && $id_filters) {
				$id_filters = explode(',', $id_filters);
			}
        }
        return (is_array($id_filters) ? array_filter($id_filters) : $id_filters);
    }

    public function getCurrentWarehousesFilter()
    {
        $id_filters = '';
        if ((Tools::getIsset($this->table.'Filter_warehouses') && Tools::getValue($this->table.'Filter_warehouses')) || Tools::getIsset('id_warehouse')) {
            if (Tools::getIsset('id_warehouse') && Tools::getValue('id_warehouse')) {
                $id_filters = array(0 => (int)Tools::getValue('id_warehouse'));
            } else {
                $id_filters = Tools::getValue($this->table.'Filter_warehouses');
				if (!is_array($id_filters) && $id_filters) {
                	$id_filters = explode(',', $id_filters);
				}
            }
        }
        return (is_array($id_filters) ? array_filter($id_filters) : $id_filters);
    }

	// Change the current url index (filters, paginations links, etc)
    public function init()
	{
        parent::init();
		if (!Tools::isSubmit('submitReset'.$this->table)) {
			$product_type = $this->getCurrentProductsTypeFilter();
			if (!empty($product_type)) {
				self::$currentIndex .= '&'.$this->table.'Filter_product_type='.(int)$product_type;
			}
			$providers_filter = $this->getCurrentProvidersFilter();
			if (!empty($providers_filter)) {
				self::$currentIndex .= '&'.$this->table.'Filter_providers='.(is_array($providers_filter) ? implode(',', $providers_filter) : $providers_filter);
			}
			$warehouses_filter = $this->getCurrentWarehousesFilter();
			if (!empty($warehouses_filter)) {
				self::$currentIndex .= '&'.$this->table.'Filter_warehouses='.(is_array($warehouses_filter) ? implode(',', $warehouses_filter) : $warehouses_filter);
			}
			if (!empty($product_type) || !empty($providers_filter) || !empty($warehouses_filter)) {
				$this->context->smarty->assign('current', self::$currentIndex);
			}
		}
    }

    public function viewProductAttributes($id_product, $only_table = false)
    {
        $ps_asm = Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT');
        $product = new Product((int)$id_product, false, (int)$this->context->language->id);
        $id_warehouses_filter = $this->getCurrentWarehousesFilter(); // return array
		// Pagination vars
		$limit = $offset = false;
		$use_pagination = Configuration::get('WKWAREHOUSE_PAGINATION_USE');
		if ($use_pagination) {
			$limit = Tools::getIsset('pagination_length') && Tools::getValue('pagination_length') ? Tools::getValue('pagination_length') : (int)Configuration::get('WKWAREHOUSE_PAGINATION_LIMIT');
			$numLinks = (int)Configuration::get('WKWAREHOUSE_PAGINATION_NUMBER_LINKS');
			$offset = Tools::getIsset('npage') && Tools::getValue('npage') ? Tools::getValue('npage') : false;
		}
		// ------------------

        // Get query with pagination
        $combinations_query = WorkshopAsm::getAttributeCombinationsQuery(
			$id_product,
			$id_warehouses_filter,
			$limit,
			$offset
		);
        $combinations = !empty($combinations_query) ? Db::getInstance()->executeS($combinations_query) : array();

		if ($use_pagination) {
			// Count all combinations
			$iFilteredTotal = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()', false);
			$pagination = new WarehousePagination(array(
				'perPage' => $limit,
				'currentPage' => (int)$offset,
				'firstLink' => $this->l('‹ First'),
				'lastLink' => $this->l('Last ›'),
				'showInfos' => true,
				'id_product' => $id_product,
				'numLinks' => (int)$numLinks,
				'totalRows' => $iFilteredTotal,
			));
			$this->context->smarty->assign(array(
				'pagination_length' => $limit,
				'pagination_lengths' => $this->module->pagination_lengths,
				'pagination_links' => $pagination->createPaginationLinks(),
				'pagination_infos' => str_replace(
					array('SHOWING' , 'TO', 'OF'),
					array($this->l('Showing'), $this->l('to'), $this->l('of')),
					$pagination->createPaginationLinks(true)
				),
			));
		}

        $this->filterProductsCombinations($product, $combinations, $id_warehouses_filter);

        $this->context->smarty->assign(array(
            'asm' => $ps_asm,
            'product_id' => $product->id,
            'combinations' => $combinations,
            'hasAttributes' => $product->hasAttributes(),
            'show_quantities' => WorkshopAsm::canManageQuantity(),
            'product_asm' => $product->advanced_stock_management,
            'link_product' => WorkshopAsm::getProductLink($product->id),
        ));
        return $this->fetchTemplate('/views/templates/admin/', ($only_table === true ? 'list_attributes_table' : 'list_attributes'));
    }

	// For pagination
    public function ajaxProcessLoadPaginateCombinations()
    {
		die($this->viewProductAttributes((int)Tools::getValue('id_product'), true));
    }

    protected function filterProductsCombinations($product, &$combinations, $id_warehouses_filter = null)
    {
        // For each combination, retrieve attributes names
        if (sizeof($combinations)) {
            foreach ($combinations as $key => $value) {
                $combination_name = '';
                $attributes = $product->getAttributeCombinationsById(
                    (int)$value['id_product_attribute'],
                    (int)$this->context->language->id
                );
                if ($attributes) {
                    foreach ($attributes as $attribute) {
                        $combination_name .= ' '.$attribute['group_name'].' : '.$attribute['attribute_name'].', ';
                    }
                }
                $combinations[$key]['name'] = rtrim($combination_name, ', ');
                $stock = WorkshopAsm::getAvailableStockByProduct(
                    $value['id_product'],
                    $value['id_product_attribute'],
                    $this->context->shop->id
                );
                $combinations[$key]['warehouses_qty_sum'] = $product->advanced_stock_management ? (int)WorkshopAsm::getProductPhysicalQuantities(
                    $value['id_product'],
                    $value['id_product_attribute'],
                    (!empty($id_warehouses_filter) ? $id_warehouses_filter : null)
                ) : 0;
                $combinations[$key]['physical_quantity'] = isset($stock['physical_quantity']) ? (int)$stock['physical_quantity'] : 0;
                $combinations[$key]['reserved_quantity'] = isset($stock['reserved_quantity']) ? (int)$stock['reserved_quantity'] : 0;
                $combinations[$key]['stock'] = isset($stock['quantity']) ? (int)$stock['quantity'] : 0;
            }
        } else {// simple product
            $stock = WorkshopAsm::getAvailableStockByProduct($product->id);
            $combinations[0]['id_product'] = $product->id;
            $combinations[0]['id_product_attribute'] = 0;
            $combinations[0]['name'] = $product->name;
            $combinations[0]['warehouses_qty_sum'] = (
				$product->advanced_stock_management ? (int)WorkshopAsm::getProductPhysicalQuantities($product->id, 0, (!empty($id_warehouses_filter) ? $id_warehouses_filter : null)) : 0
			);
            $combinations[0]['physical_quantity'] = isset($stock['physical_quantity']) ? (int)$stock['physical_quantity'] : 0;
            $combinations[0]['reserved_quantity'] = isset($stock['reserved_quantity']) ? (int)$stock['reserved_quantity'] : 0;
            $combinations[0]['stock'] = isset($stock['quantity']) ? (int)$stock['quantity'] : 0;
        }
    }

    public function ajaxProcessUpdateRealQty()
    {
        $id_product = (int)Tools::getValue('id_product');
        $id_product_attribute = (int)Tools::getValue('id_product_attribute');
        $qty = Tools::getValue('qty');
        $message = '';
        $result = true;

        if (Validate::isInt($qty)) {
            $old_qty = StockAvailable::getQuantityAvailableByProduct(
                $id_product,
                $id_product_attribute,
                (int)Context::getContext()->shop->id// don't change the context
            );
            if ($old_qty != $qty) {
                $shops = Shop::getContextListShopID();
                if (Shop::isFeatureActive()) {
                    foreach ($shops as $shop) {
                        StockAvailable::setQuantity(
                            $id_product,
                            $id_product_attribute,
                            (int)$qty,
                            (int)$shop
                        );
                    }
                } else {
                    StockAvailable::setQuantity(
                        $id_product,
                        $id_product_attribute,
                        (int)$qty,
                        (int)Context::getContext()->shop->id// don't change the context
                    );
                }
                // Sync stock (physical_quantity) according to reserved & available qty
                WorkshopAsm::updatePhysicalProductAvailableQuantity($id_product);
            } else {
                $result = 'same';
            }
        } else {
            $result = false;
            $message = $this->l('Available quantity is not valid!');
        }

        $stock = WorkshopAsm::getAvailableStockByProduct($id_product, $id_product_attribute);
        die(json_encode(array(
            'result' => $result,
            'error' => $message,
            'physical_quantity' => (int)$stock['physical_quantity'],
            'reserved_quantity' => (int)$stock['reserved_quantity'],
        )));
    }

    public function initContent()
    {
        $warehouses = StoreHouse::getWarehouses();
        if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $this->warnings[] = $this->l('You need to activate advanced stock management before using this feature.');
        } elseif (!$warehouses) {
            $this->warnings[] = $this->l('You must have at least one warehouse.');
        } else {
            // ==============================
            // Suppliers Filter
            // ==============================
            $selected_suppliers = $this->getCurrentProvidersFilter();
            $suppliers = Supplier::getSuppliers();
            foreach ($suppliers as &$s) {
                $s['is_selected'] = is_array($selected_suppliers) && in_array($s['id_supplier'], $selected_suppliers) ? 1 : 0;
            }
            // ==============================
            // Warehouses Filter
            // ==============================
            $selected_warehouses = $this->getCurrentWarehousesFilter();
            foreach ($warehouses as &$w) {
                $w['is_selected'] = is_array($selected_warehouses) && in_array($w['id_warehouse'], $selected_warehouses) ? 1 : 0;
            }
            // For Header tpl
            $this->tpl_list_vars = array(
                'providers' => $suppliers,
                'warehouses' => $warehouses,
                'has_bulk_actions' => false,
                'this_path' => _MODULE_DIR_.$this->module->name,
                'show_quantities' => WorkshopAsm::canManageQuantity(),
                'cron_lunched_by' => (function_exists('curl_init') ? '/usr/bin/curl' : 'php -f'),
                'warning_messages_html' => $this->module->showWarningMessage(false),
                'is_suppliers_selected' => is_array($selected_suppliers) && count($selected_suppliers) ? true : false,
                'is_warehouses_selected' => is_array($selected_warehouses) && count($selected_warehouses) ? true : false,
            );
        }
        parent::initContent();
    }

    public function renderList()
    {
        if (Tools::isSubmit('submitFilter')) {
            $this->processFilter();
        }
        if (Tools::getIsset($this->list_id.'ID_product') && Tools::getValue($this->list_id.'ID_product')) {
            //$this->_where .= ' AND a.`id_product` = '.(int)Tools::getValue($this->list_id.'ID_product').'';
            $_POST['productFilter_a!id_product'] = (int)Tools::getValue($this->list_id.'ID_product');
            $this->processFilter();
        }
        return parent::renderList();
    }

    public function getList($id_lang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        if (Tools::getValue($this->list_id.'Orderby')) {
            $orderBy = Tools::getValue($this->list_id.'Orderby');
        }
        if (Tools::getValue($this->list_id.'Orderway')) {
            $orderWay = Tools::getValue($this->list_id.'Orderway');
        }
		// In case you wish to control the pagination of the whole page
		//$this->_default_pagination = 5;
		//$this->_pagination = [5, 20, 100, 300, 1000];
        parent::getList($id_lang, $orderBy, $orderWay, $start, $limit, $this->context->shop->id);

        /****************************************************************/
        /*************** Export all A.S.M Products to Excel ***************/
        /****************************************************************/
        if (Tools::isSubmit('exportProductsExcel')) {
            // Original query to remove LIMIT condition if any.
            // Select also only A.S.M products (Improve selection process)
            $listsql = str_replace(array('WHERE', 'where'), 'WHERE a.advanced_stock_management = 1 AND ', $this->_listsql);
            $listsql = Tools::substr($listsql, 0, strpos($listsql, 'ORDER BY'));
            $list = Db::getInstance()->executeS($listsql);
            /* FOR TEST */
            //$list = $this->_list;
            //$newPhpExcel = false;
            if (count($list) > 0) {
                $excelParams = $this->initPHPExcel();
                $spreadsheet = ($excelParams['newPhpExcel'] ? new \PhpOffice\PhpSpreadsheet\Spreadsheet() : new PHPExcel());
                $sheet = $spreadsheet->getActiveSheet()->setTitle($this->l('warehouses stock'));

                /*************************************** HEADER ******************************************/
                $sheet->mergeCells('A1:H1');
                $sheet->getRowDimension('1')->setRowHeight(50);
                $sheet->setCellValue('A1', $this->l('WAREHOUSES STOCKS / PRODUCTS'));
                $style = $sheet->getStyle('A1:H1');
                $style->applyFromArray($excelParams['styleHeader']);
                /* End first line */
                /* Begin second line */
                $sheet->getRowDimension('2')->setRowHeight(35);
                $style = $sheet->getStyle('A2:H2');
                $style->applyFromArray($excelParams['stylesFooterHeader']);
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->setCellValue('A2', '#ID');
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->mergeCells('B2:E2');
                $sheet->setCellValue('B2', $this->l('PRODUCT NAME'));
                $sheet->getStyle('B2')->getAlignment()->setHorizontal($excelParams['horizontalLeft']);
                // Don't forget to set auto width to the merged cells
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('E')->setAutoSize(true);
                /**/
                $sheet->getColumnDimension('F')->setAutoSize(true);
                $sheet->setCellValue('F2', $this->l('REFERENCE'));
                $sheet->getColumnDimension('G')->setAutoSize(true);
                $sheet->setCellValue('G2', $this->l('DEFAULT SUPPLIER'));
                $sheet->getColumnDimension('H')->setAutoSize(true);
                $sheet->setCellValue('H2', $this->l('CATEGORY'));
                /************************************* END HEADER ****************************************/

                // Iterate list to write to excel
                $rowCount = 3;
                foreach ($list as $product) {
                    $this->buildProductsExcelBody($product, $rowCount, $sheet, $excelParams);
                }
                $sheet->getRowDimension($rowCount)->setRowHeight(29);
                $style = $sheet->getStyle('A'.$rowCount.':H'.$rowCount);
                $style->applyFromArray($excelParams['stylesFooterHeader']);

                // Write an .xlsx file
                if ($excelParams['newPhpExcel']) {
                    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                } else {
                    $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
                    $writer->setOffice2003Compatibility(true);
                }

                header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition:inline;filename=export-products-warehouses-'.date('Y-m-d_H-i-s').'.xlsx');
                $writer->save('php://output');
                die();
            } else {
                $this->displayWarning($this->l('There is no product(s) using advanced stock management to export!'));
            }
        }
    }

    public function buildProductsExcelBody($product, &$rowCount, $sheet, $excelParams, $warehouse = null)
    {
        $productObj = new Product($product['id_product'], false, $this->context->language->id);
        $id_warehouse = (!is_null($warehouse) ? (int)$warehouse['id_warehouse'] : null);

        $sheet->getRowDimension($rowCount)->setRowHeight(29);
        $style = $sheet->getStyle('A'.$rowCount.':'.'H'.$rowCount);
        $style->applyFromArray($excelParams['styleParentData']);
        /* Product informations */
        $sheet->setCellValue('A'.$rowCount, (int)$product['id_product'].' -');
        $sheet->mergeCells('B'.$rowCount.':E'.$rowCount);
        $sheet->setCellValue(
            'B'.$rowCount,
            pSQL(isset($product['name']) ? $product['name'] : $productObj->name)
            .(!is_null($id_warehouse) ? ' ('.$warehouse['name'].')' : '')
        );
        $sheet->getStyle('B'.$rowCount)->getAlignment()
        ->setHorizontal($excelParams['horizontalLeft']);
        $sheet->setCellValue('F'.$rowCount, isset($product['reference']) ? pSQL($product['reference']) : '');
        $sheet->setCellValue('G'.$rowCount, isset($product['supplier_name']) ? pSQL($product['supplier_name']) : '');
        $sheet->setCellValue('H'.$rowCount, isset($product['categoryName']) ? pSQL($product['categoryName']) : '');
        $rowCount++;

        /* Product or combinations informations stock */
        $hasCombinations = $productObj->hasAttributes();
        $sheet->getRowDimension($rowCount)->setRowHeight(24);
        $style = $sheet->getStyle('A'.$rowCount.':'.'H'.$rowCount);
        $style->applyFromArray($excelParams['styleProductAttributeHeader']);
        $sheet->setCellValue('A'.$rowCount, '');
        $sheet->setCellValue('B'.$rowCount, '#ID');
        $sheet->setCellValue('C'.$rowCount, ($hasCombinations ? $this->l('Combination') : $this->l('Name')));
        $sheet->getStyle('C'.$rowCount)->getAlignment()
        ->setHorizontal($excelParams['horizontalLeft']);
        $sheet->setCellValue('D'.$rowCount, $this->l('Reference'));
        $sheet->setCellValue('E'.$rowCount, $this->l('Reserved Qty'));
        $sheet->setCellValue('F'.$rowCount, $this->l('Available Qty'));
        $sheet->setCellValue('G'.$rowCount, $this->l('Physical Qty'));
        $sheet->setCellValue('H'.$rowCount, $this->l('Warehouses Qty'));
        $rowCount++;

        // Get all simple / combinations products
        $combinations_query = WorkshopAsm::getAttributeCombinationsQuery(
			$productObj->id,
			(!is_null($id_warehouse) ? array($id_warehouse) : null)
		);
        $combinations = !empty($combinations_query) ? Db::getInstance()->executeS($combinations_query) : array();

        $this->formatExportQuery($productObj, $combinations, $id_warehouse);

        foreach ($combinations as $item) {
            $sheet->getRowDimension($rowCount)->setRowHeight(18);
            $style = $sheet->getStyle('A'.$rowCount.':'.'H'.$rowCount);
            $style->applyFromArray($excelParams['styleProductAttributeData']);
            $sheet->setCellValue('A'.$rowCount, '');
            $sheet->setCellValue('B'.$rowCount, ($item['id_product_attribute'] > 0 ? $item['id_product_attribute'] : ''));
            $sheet->setCellValue('C'.$rowCount, $item['name']);
            $sheet->getStyle('C'.$rowCount)->getAlignment()
            ->setHorizontal($excelParams['horizontalLeft']);
            $sheet->setCellValue('D'.$rowCount, (isset($item['reference']) ? $item['reference'] : ''));
            $sheet->setCellValue('E'.$rowCount, (int)$item['reserved_quantity']);
            $sheet->setCellValue('F'.$rowCount, (int)$item['stock']);
            $sheet->setCellValue('G'.$rowCount, (int)$item['physical_quantity']);
            $sheet->getStyle('H'.$rowCount)->getFont()->setBold(true);
            $sheet->getStyle('H'.$rowCount)
            ->getFill()->getStartColor()->setARGB('FFd9e2ef');
            $sheet->setCellValue('H'.$rowCount, $item['warehouses_qty_sum']);

            // Warehouses stocks & locations (only for "Export stocks by products" button)
            if (is_null($id_warehouse)) {
                $rowCount++;
                $warehouses_collection = StoreHouse::getProductWarehouseList(
                    $productObj->id,
                    $item['id_product_attribute']
                );
                if ($warehouses_collection) {
                    $style = $sheet->getStyle('E'.$rowCount.':'.'H'.$rowCount);
                    $style->applyFromArray($excelParams['styleWarehousesHeader']);
                    $sheet->setCellValue('A'.$rowCount, '');
                    $sheet->setCellValue('B'.$rowCount, '');
                    $sheet->setCellValue('C'.$rowCount, '');
                    $sheet->setCellValue('D'.$rowCount, '');
                    $sheet->setCellValue('E'.$rowCount, $this->l('Warehouse'));
                    $sheet->setCellValue('F'.$rowCount, $this->l('Reserved Qty'));
                    $sheet->setCellValue('G'.$rowCount, $this->l('Physical Qty'));
                    $sheet->setCellValue('H'.$rowCount, $this->l('Available Qty'));
                    $rowCount++;

                    foreach ($warehouses_collection as $wc) {
                        $style = $sheet->getStyle('F'.$rowCount.':'.'H'.$rowCount);
                        $style->applyFromArray($excelParams['styleWarehousesData']);
                        $sheet->setCellValue('A'.$rowCount, '');
                        $sheet->setCellValue('B'.$rowCount, '');
                        $sheet->setCellValue('C'.$rowCount, '');
                        $sheet->setCellValue('D'.$rowCount, '');
                        $sheet->setCellValue('E'.$rowCount, $wc['name']);
                        $sheet->setCellValue('F'.$rowCount, (int)WorkshopAsm::getReservedQuantityByProductAndWarehouse(
                            $productObj->id,
                            $item['id_product_attribute'],
                            $wc['id_warehouse']
						));
                        $sheet->setCellValue('G'.$rowCount, (int)WorkshopAsm::getProductPhysicalQuantities(
                            $productObj->id,
                            $item['id_product_attribute'],
                            $wc['id_warehouse']
                        ));
                        $sheet->setCellValue('H'.$rowCount, (int)WarehouseStock::getAvailableQuantityByWarehouse(
                            $productObj->id,
                            $item['id_product_attribute'],
                            $wc['id_warehouse']
                        ));
                        $rowCount++;
                    }
                }
                $style = $sheet->getStyle('F'.($rowCount-1).':'.'H'.($rowCount-1));
                $style->applyFromArray($excelParams['styleFooter']);
            } else {
                $rowCount++;
            }
        }
    }

    protected function formatExportQuery($product, &$combinations, $id_warehouse = null)
    {
        // For each combination, retrieve attributes names
        if (sizeof($combinations)) {
            foreach ($combinations as $key => $value) {
                $combination_name = '';
                $attributes = $product->getAttributeCombinationsById(
                    (int)$value['id_product_attribute'],
                    (int)$this->context->language->id
                );
                if ($attributes) {
                    foreach ($attributes as $attribute) {
                        $combination_name .= ' '.$attribute['group_name'].' : '.$attribute['attribute_name'].', ';
                    }
                }
                $combinations[$key]['name'] = rtrim($combination_name, ', ');
                $combinations[$key]['warehouses_qty_sum'] = (int)WorkshopAsm::getProductPhysicalQuantities(
                    $value['id_product'],
                    $value['id_product_attribute']
                );
                $combinations[$key]['physical_quantity'] = (int)WorkshopAsm::getProductPhysicalQuantities(
                    $value['id_product'],
                    $value['id_product_attribute'],
					$id_warehouse
				);
                $combinations[$key]['reserved_quantity'] = (int)WorkshopAsm::getReservedQuantityByProductAndWarehouse(
                    $value['id_product'],
                    $value['id_product_attribute'],
                    $id_warehouse
                );
                $combinations[$key]['stock'] = (int)WarehouseStock::getAvailableQuantityByWarehouse(
                    $value['id_product'],
                    $value['id_product_attribute'],
                    $id_warehouse
				);
            }
        } else {// simple product
            $combinations[0]['id_product'] = $product->id;
            $combinations[0]['id_product_attribute'] = 0;
            $combinations[0]['name'] = $product->name;
            $combinations[0]['reference'] = $product->reference;
            $combinations[0]['warehouses_qty_sum'] = (int)WorkshopAsm::getProductPhysicalQuantities($product->id, 0);
            $combinations[0]['physical_quantity'] = (int)WorkshopAsm::getProductPhysicalQuantities($product->id, 0, $id_warehouse);
            $combinations[0]['reserved_quantity'] = (int)WorkshopAsm::getReservedQuantityByProductAndWarehouse($product->id, 0, $id_warehouse);
			$combinations[0]['stock'] = (int)WarehouseStock::getAvailableQuantityByWarehouse($product->id, 0, $id_warehouse);
        }
    }

    /*
    * Load form to manage warehouses quantities by product/combination
    */
    public function ajaxProcessLoadFormEditWarehousesQties()
    {
        $id_product = (int)Tools::getValue('id_product');
        $product = new Product($id_product, false, $this->context->language->id);

        if (Validate::isLoadedObject($product)) {
            $id_product_attribute = (Tools::getValue('id_product_attribute') ? (int)Tools::getValue('id_product_attribute') : 0);
            // Collect attributes name
            $attributes_name = '';
            if (!empty($id_product_attribute)) {
                $combination = new Combination($id_product_attribute);
                $attributes = $combination->getAttributesName((int)$this->context->language->id);
                foreach ($attributes as $attribute) {
                    $attributes_name .= $attribute['name'].' - ';
                }
                $attributes_name = rtrim($attributes_name, ' - ');
            }
            // Get warehouses & locations
			$warehouses = StoreHouse::getWarehouses(
				(Tools::getIsset('id_warehouses_filter') ? Tools::getValue('id_warehouses_filter') : null)
			);
            $warehouse_locations = StorehouseProductLocation::getCollection($id_product, $id_product_attribute);
            // Get warehouses ID & quantities
            $ids_warehouses = $quantity_locations = array();
            foreach ($warehouse_locations as $warehouse_location) {
                $id_warehouse = (int)$warehouse_location->id_warehouse;
                $ids_warehouses[] = $id_warehouse;
                if (WarehouseStock::productIsPresentInStock($id_product, $id_product_attribute, $id_warehouse)) {
                    $quantity_locations[$id_warehouse]['physical'] = WorkshopAsm::getProductPhysicalQuantities(
                        $id_product,
                        $id_product_attribute,
                        $id_warehouse
                    );
                }
                // Get the last unit price te
                $quantity_locations[$id_warehouse]['price_te'] = WarehouseStockMvt::getLastProductUnitPrice(
                    $id_product,
                    $id_product_attribute,
                    $id_warehouse
                );
                $quantity_locations[$id_warehouse]['reserved'] = WorkshopAsm::getReservedQuantityByProductAndWarehouse(
                    $id_product,
                    $id_product_attribute,
                    $id_warehouse
                );
                /*$quantity_locations[$id_warehouse]['available'] = (
                    $quantity_locations[$id_warehouse]['physical'] - $quantity_locations[$id_warehouse]['reserved']
                );*/
            }
            // Sync stock (physical_quantity) according to reserved & available qty
            WorkshopAsm::updatePhysicalProductAvailableQuantity($id_product);
            $stock = WorkshopAsm::getAvailableStockByProduct($id_product, $id_product_attribute);

            $this->context->smarty->assign(array(
                'warehouses' => $warehouses,
                'locations' => $ids_warehouses,
                'quantity_locations' => $quantity_locations,
                'id_product' => $id_product,
                'id_product_attribute' => $id_product_attribute,
                'product_name' => $product->name,
                'attributes_name' => $attributes_name,
                'currentQties' => $stock,
                'link' => new Link(),
                'currencies' => Currency::getCurrencies(false, true, true), // Get currencies list
                'warehouses_qty_sum' => (int)WorkshopAsm::getProductPhysicalQuantities($id_product, $id_product_attribute),
                'isPresentInStock' => WarehouseStock::productIsPresentInStock($id_product),
            ));
            die(json_encode(array(
                'content' => $this->context->smarty->fetch(
                    _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/edit_warehouses.tpl'
                )
            )));
        }
    }

    /*
    * Align warehouses quantities to the global physical quantity
    */
    public function ajaxProcessAlignWarehousesQties()
    {
        $id_product = (int)Tools::getValue('id_product');
        $product = new Product($id_product, false);

        if (Validate::isLoadedObject($product) && $product->advanced_stock_management) {
            $id_product_attribute = (int)Tools::getValue('id_product_attribute');
            $warehouses_qty_sum = (int)WorkshopAsm::getProductPhysicalQuantities($id_product, $id_product_attribute);

            $stock = WorkshopAsm::getAvailableStockByProduct($id_product, $id_product_attribute);
            $physical_qty = (int)$stock['physical_quantity'];

            if ($warehouses_qty_sum != $physical_qty) {
                $associated_warehouses = WorkshopAsm::getAssociatedWarehousesArray($id_product, $id_product_attribute);
                if (count($associated_warehouses) > 0) {
                    (new WorkshopAsm())->synchronize(
                        $id_product,
                        $id_product_attribute,
                        $physical_qty,
                        $associated_warehouses
                    );
                    die(json_encode(array(
                        'hasError' => false,
                        'msgSuccess' => $this->l('Warehouses quantities have been aligned successfully.'),
                    )));
                } else {
                    die(json_encode(array(
                        'hasError' => true,
                        'msgError' => $this->l('Error: This product is not yet associated to any warehouse!')
                    )));
                }
            }
        }
        die(json_encode(array(
            'hasError' => true,
            'msgError' => $this->l('Unknown error!')
        )));
    }

    /*
    * Edit stock in warehouse (depends on stock)
    */
    public function ajaxProcessSaveFormEditWarehousesQties()
    {
        $locations = Tools::getValue('locations');
        $json_return = array(
            'hasError' => false,
            'msgError' => false,
        );

        if (is_array($locations) && count($locations)) {
            $manager = new WorkshopAsm();

            foreach ($locations as $location) {
                $id_product = (int)$location['id_product'];
                $id_product_attribute = (int)$location['id_product_attribute'];
                $id_warehouse = (int)$location['id_warehouse'];
                $price_te = str_replace(',', '.', $location['price_te']);
                $id_currency = (int)$location['id_currency'];

                if (array_key_exists('location', $location) && (int)$location['location']) {
                    if (!is_numeric($price_te)) {
                        die(json_encode(array(
                            'hasError' => true,
                            'msgError' => $this->l('Error: The product price is not valid!')
                        )));
                    }
                    if ($id_currency <= 0 || (!($result = Currency::getCurrency($id_currency)) || empty($result))) {
                        die(json_encode(array(
                            'hasError' => true,
                            'msgError' => $this->l('Error: The selected currency is not valid!')
                        )));
                    }

                    // Convert price to default shop currency if needed
                    if ($id_currency != Configuration::get('PS_CURRENCY_DEFAULT')) {
                        // Convert the price from given currency to default currency
                        $price_te = Tools::convertPriceFull($price_te, new Currency($id_currency), new Currency(Configuration::get('PS_CURRENCY_DEFAULT')));
                    }

                    $update_available_stock = true;
                    // Qty to manage
                    $qty = $stock_gap = $location['quantity'];

                    if (Validate::isInt($qty) && $qty >= 0) {
                        /******************* IF DEFINING STOCK FOR A GIVEN WAREHOUSE **********************/
                        if ($location['action'] == 2) {
                            /*$product_stock = WorkshopAsm::getAvailableStockByProduct(
                                $id_product,
                                $id_product_attribute
                            ); // Get available qty in shop
                            $physical_quantity_in_warehouses = (int)WorkshopAsm::getProductPhysicalQuantities(
                                $id_product,
                                $id_product_attribute
                            ); // Get qty in all warehouses*/
                        }
                        /********************************************************************************************/

                        // Add warehouse association if not exists yet
                        if (!StorehouseProductLocation::getIdByProductAndWarehouse($id_product, $id_product_attribute, $id_warehouse)) {
                            $warehouse_location_entity = new StorehouseProductLocation();
                            $warehouse_location_entity->id_product = (int)$id_product;
                            $warehouse_location_entity->id_product_attribute = (int)$id_product_attribute;
                            $warehouse_location_entity->id_warehouse = (int)$id_warehouse;
                            $warehouse_location_entity->save();
                        }

                        $warehouse = new StoreHouse($id_warehouse);
                        // IF INCREASE STOCK
                        if ($location['action'] == 1 || $location['action'] == 2) {
                            $manager->addProduct(
                                $id_product,
                                $id_product_attribute,
                                $warehouse,
                                $qty,
                                $price_te
                            );
                        } elseif ($location['action'] == -1) {// IF DECREASE STOCK
                            $manager->removeProduct(
                                $id_product,
                                $id_product_attribute,
                                $warehouse,
                                $qty
                            );
                        }
                        /*
                        * Update qty & add movement After adding/removing warehouse qty
                        */
                        if ($update_available_stock) {
                            if ($location['action'] == -1) {
                                $stock_gap *= -1;
                            }
                            WorkshopAsm::updateQuantity(
                                $id_product,
                                $id_product_attribute,
                                $stock_gap,
                                Context::getContext()->shop->id,
                                true
                            );
                        }
                        WorkshopAsm::updatePhysicalProductAvailableQuantity($id_product);

                        // Get current updated quantities (for display)
                        $stock = WorkshopAsm::getAvailableStockByProduct($id_product, $id_product_attribute);
                        $physical_quantity_in_warehouses = (int)WorkshopAsm::getProductPhysicalQuantities(
                            $id_product,
                            $id_product_attribute
                        );
                        $json_return = array_merge($json_return, array(
                            'quantity' => (int)$stock['quantity'],
                            'physical_quantity' => (int)$stock['physical_quantity'],
                            'warehouses_quantity' => (int)$physical_quantity_in_warehouses,
                        ));
                    } else {
                        die(json_encode(array(
                            'hasError' => true,
                            'msgError' => $this->l('Error: invalid quantity!')
                        )));
                    }
                } else {// IF UNCHECKED, REMOVE WAREHOUSE ASSOCIATION AND STOCK TRACE
                    if (StorehouseProductLocation::getIdByProductAndWarehouse($id_product, $id_product_attribute, $id_warehouse)) {
                        // Can not remove association if product has been reserved for this warehouse
						if (WorkshopAsm::getReservedQuantityByProductAndWarehouse(
							$id_product,
							$id_product_attribute,
							$id_warehouse
						) > 0) {
							die(json_encode(array(
								'hasError' => true,
								'msgError' => $this->l('Action not allowed: product already reserved in this warehouse.'),
							)));
						}

						// Prepare warehouse quantity to remove
                        $qty_remove = (int)WorkshopAsm::getProductPhysicalQuantities(
                            $id_product,
                            $id_product_attribute,
                            $id_warehouse
                        );
                        $associated_warehouses_collection = StorehouseProductLocation::getCollection(
                            $id_product,
                            $id_product_attribute,
                            $id_warehouse
                        );
                        // Remove warehouse association, and then stock trace accordingly
                        foreach ($associated_warehouses_collection as $awc) {
                            $awc->delete();
                        }
                        // Remove warehouse quantity from stock_available to maintain synchronization
                        if ($qty_remove > 0) {
                            $qty_remove *= -1;
                            StockAvailable::updateQuantity(
                                $id_product,
                                $id_product_attribute,
                                $qty_remove,
                                Context::getContext()->shop->id,
                                true // Add movement
                            );
                        }
                        /*
                        * Sync quantities with the other warehouses to have
                        * the same quantity value as the global Prestashop physical quantity
                        */
                        if ($qty_remove < 0) {
                            $stock = WorkshopAsm::getAvailableStockByProduct($id_product, $id_product_attribute);
                            $global_physical_qty = (int)$stock['physical_quantity']; // Global physical quantity
                            $quantity_in_warehouses = (int)WorkshopAsm::getProductPhysicalQuantities(
                                $id_product,
                                $id_product_attribute
                            ); // Total physical quantity in all warehouses
                            // Sync if qties are differents
                            if ($global_physical_qty != $quantity_in_warehouses) {
                                $gap_qty = $global_physical_qty - $quantity_in_warehouses;
                                if ($gap_qty < 0) {
                                    $gap_qty *= -1;
                                }
                                // Update quantities according to stock priorities (from higher to lower)
                                $manager->updateAccordingDescWarehouseQtiesPriority(
                                    WorkshopAsm::getAssociatedWarehousesArray($id_product, $id_product_attribute),
                                    $id_product,
                                    $id_product_attribute,
                                    $gap_qty
                                );
                            }
                        }

                        $stock = WorkshopAsm::getAvailableStockByProduct($id_product, $id_product_attribute);
                        $physical_quantity_in_warehouses = (int)WorkshopAsm::getProductPhysicalQuantities(
                            $id_product,
                            $id_product_attribute
                        ); // Get global stock in all warehouses
                        die(json_encode(array(
                            'hasError' => false,
                            'msgSuccess' => $this->l('Warehouse product association has been deleted successfully (including stock)'),
                            'quantity' => (int)$stock['quantity'],
                            'physical_quantity' => (int)$stock['physical_quantity'],
                            'warehouses_quantity' => (int)$physical_quantity_in_warehouses,
                        )));
                    }
                }
            }
        } else {
            die(json_encode(array(
                'hasError' => true,
                'msgError' => $this->l('Error: location not defined!')
            )));
        }
        die(json_encode($json_return));
    }

    /*
    * Load warehouses associations / locations  for the current simple product / combination
    */
    public function ajaxProcessInitFormLocations()
    {
        $product_id = (int)Tools::getValue('id_product');
        $id_product_attribute = (int)Tools::getValue('id_product_attribute');
        $id_lang = (int)$this->context->language->id;
        $error = $custom_form = '';
        $attributes = $reserved_warehouses = array();

        $obj = new Product($product_id);

        if (Validate::isLoadedObject($obj) && $obj->id) {
			$attributes[] = array(
				'id_product' => $product_id,
				'id_product_attribute' => $id_product_attribute,
				'attribute_designation' => !empty($id_product_attribute) ? WorkshopAsm::getAttributesCombinationNames($id_product_attribute) : ''
			);

			$product_designation = array();
			foreach ($attributes as $attribute) {
				$product_designation[$attribute['id_product_attribute']] = rtrim(
					$obj->name[$id_lang].' - '.$attribute['attribute_designation'],
					' - '
				);
			}

			// Get warehouses
			$warehouses = StoreHouse::getWarehouses(
				(Tools::getIsset('id_warehouses_filter') ? Tools::getValue('id_warehouses_filter') : null)
			);
			// Get already associated warehouses
			$associated_warehouses_collection = StorehouseProductLocation::getCollection($product_id, $id_product_attribute);
            foreach ($associated_warehouses_collection as $awc) {
				if (WorkshopAsm::getReservedQuantityByProductAndWarehouse(
                    $product_id,
                    $id_product_attribute,
                    $awc->id_warehouse
                ) > 0) {
                	$reserved_warehouses[$id_product_attribute] = $awc->id_warehouse;
				}
            }

			$this->context->smarty->assign(array(
				'forall' => false,
				'product' => $obj,
				'id_lang' => $id_lang,
				'attributes' => $attributes,
				'link' => $this->context->link,
				'warehouses' => $warehouses,
				'id_product_attribute' => $id_product_attribute,
				'product_designation' => $product_designation,
				'reserved_warehouses' => $reserved_warehouses,
				'associated_warehouses' => $associated_warehouses_collection,
			));
			$custom_form = $this->context->smarty->fetch(
				_PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/locations_products.tpl'
			);
        } else {
            $error = $this->l('Error: invalid product');
        }
        die(json_encode(array(
            'html' => $custom_form,
            'error' => $error
        )));
    }

    /**
    * Post treatment for warehouses & locations for the current simple product / combination
    */
    public function ajaxProcessProcessWarehousesAndLocations()
    {
        $error = '';
        if (Validate::isLoadedObject($product = new Product((int)Tools::getValue('id_product'), false))) {
            WorkshopAsm::processWarehouses($product->id, (int)Tools::getValue('id_product_attribute'), true);
        } else {
            $error = $this->l('Error: invalid product');
        }
        die(json_encode(array(
            'error' => $error
        )));
    }

    /*
    * Load all warehouses associations / locations by product to manage them
    */
    public function ajaxProcessInitFormWarehouses()
    {
        $id_lang = (int)$this->context->language->id;
        $error = $custom_form = '';
        $reserved_warehouses = array();

        $obj = new Product((int)Tools::getValue('id_product'), false);
        if (Validate::isLoadedObject($obj)) {
			// Get all id_product_attribute
			$attributes = $obj->getAttributesResume($id_lang);
			if (empty($attributes)) {
				$attributes[] = array(
					'id_product' => $obj->id,
					'id_product_attribute' => 0,
					'attribute_designation' => ''
				);
			}

			$product_designation = array();
			foreach ($attributes as $attribute) {
				$product_designation[$attribute['id_product_attribute']] = rtrim(
					$obj->name[$id_lang].' - '.$attribute['attribute_designation'],
					' - '
				);
			}

			// Get warehouses
			$warehouses = StoreHouse::getWarehouses(
				(Tools::getIsset('id_warehouses_filter') ? Tools::getValue('id_warehouses_filter') : null)
			);
			// Get already associated warehouses
			$associated_warehouses_collection = StorehouseProductLocation::getCollection($obj->id);
			foreach ($associated_warehouses_collection as $awc) {
				if (WorkshopAsm::getReservedQuantityByProductAndWarehouse(
					$obj->id,
					$awc->id_product_attribute,
					$awc->id_warehouse
				) > 0) {
					$reserved_warehouses[$awc->id_product_attribute] = $awc->id_warehouse;
				}
			}

			$this->context->smarty->assign(array(
				'product' => $obj,
				'attributes' => $attributes,
				'warehouses' => $warehouses,
				'product_designation' => $product_designation,
				'reserved_warehouses' => $reserved_warehouses,
				'associated_warehouses' => $associated_warehouses_collection,
				'link' => $this->context->link,
				'id_lang' => $id_lang,
				'forall' => true,
			));
			$custom_form = $this->context->smarty->fetch(
				_PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/locations_products.tpl'
			);
        } else {
            $error = $this->l('Error: invalid product');
        }
        die(json_encode(array(
            'error' => $error,
            'html' => $custom_form,
        )));
    }

    /**
    * Post treatment for all warehouses & locations by product
    */
    public function ajaxProcessProcessAllWarehousesAndLocations()
    {
        $error = '';
        if (Validate::isLoadedObject($product = new Product((int)Tools::getValue('id_product')))) {
            WorkshopAsm::processWarehouses($product->id, null, true);
        } else {
            $error = $this->l('Error: invalid product');
        }
        die(json_encode(array(
            'error' => $error
        )));
    }

    public function postProcess()
    {
        if (Tools::getIsset('ajax')) {
            parent::postProcess();
        }
        // Enable / disable A.S.M for single product
        if (Tools::isSubmit('defaultproduct')) {
            $id_product = Tools::getValue('id_product');

            if (empty($id_product)) {
                $this->errors[] = $this->l('Product is required!');
            }
            // If no errors
            if (count($this->errors) == 0) {
                $product = new Product((int)$id_product, false);
                if (Validate::isLoadedObject($product)) {
                    $use_asm = $product->advanced_stock_management ? 0 : 1;
                    WorkshopAsm::setAdvancedStockManagement($id_product, $use_asm);
                    // Set warehouses quantities according to prestashop quantities
                    WorkshopAsm::setWarehousesQtiesAccordingPrestaQties($id_product, $use_asm);
                }
                Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
            }
        }
        // Export products by warehouses
        if (Tools::isSubmit('exportWarehousesExcel')) {
            $warehouses = StoreHouse::getWarehouses(null, false);
            if (count($warehouses) > 0) {
                $excelParams = $this->initPHPExcel();
                $spreadsheet = ($excelParams['newPhpExcel'] ? new \PhpOffice\PhpSpreadsheet\Spreadsheet() : new PHPExcel());
                $sheet = $spreadsheet->getActiveSheet()->setTitle($this->l('warehouses stock'));

                /*************************************** HEADER ******************************************/
                $sheet->mergeCells('A1:H1');
                $sheet->getRowDimension('1')->setRowHeight(50);
                $sheet->setCellValue('A1', $this->l('PRODUCTS STOCKS / WAREHOUSES'));
                $style = $sheet->getStyle('A1:H1');
                $style->applyFromArray($excelParams['styleHeader']);
                /* End first line */
                
                // Iterate list to write to excel
                $rowCount = 2;
                $count_asm_products = 0;
                foreach ($warehouses as $warehouse) {
                    /* Warehouse informations */
                    $sheet->getColumnDimension('A')->setAutoSize(true);
                    $sheet->getColumnDimension('B')->setAutoSize(true);
                    $sheet->getColumnDimension('C')->setAutoSize(true);
                    $sheet->getColumnDimension('D')->setAutoSize(true);
                    $sheet->getColumnDimension('E')->setAutoSize(true);
                    $sheet->getColumnDimension('F')->setAutoSize(true);
                    $sheet->getColumnDimension('G')->setAutoSize(true);
                    $sheet->getColumnDimension('H')->setAutoSize(true);

                    $sheet->getRowDimension($rowCount)->setRowHeight(35);
                    $style = $sheet->getStyle('A'.$rowCount.':'.'H'.$rowCount);
                    $style->applyFromArray($excelParams['stylesFooterHeader']);//styleParentData
                    $sheet->setCellValue('A'.$rowCount, (int)$warehouse['id_warehouse']);
                    $sheet->mergeCells('B'.$rowCount.':E'.$rowCount);
                    $sheet->setCellValue('B'.$rowCount, pSQL(Tools::strtoupper($warehouse['name'])));
                    $sheet->getStyle('B'.$rowCount)->getAlignment()
                    ->setHorizontal($excelParams['horizontalLeft']);
                    $sheet->mergeCells('F'.$rowCount.':H'.$rowCount);
                    $sheet->getStyle('F'.$rowCount)->getAlignment()->setHorizontal($excelParams['horizontalRight']);
                    $sheet->setCellValue('F'.$rowCount, pSQL($warehouse['reference']));
                    $rowCount++;

                    /* Get products by warehouse */
                    $associated_products_warehouse = StorehouseProductLocation::getProducts($warehouse['id_warehouse'], true);
                    if (count($associated_products_warehouse)) {
                        foreach ($associated_products_warehouse as $product) {
                            $this->buildProductsExcelBody($product, $rowCount, $sheet, $excelParams, $warehouse);
                            $count_asm_products++;
                        }
                    } else {// Display no products yet associated
                        $sheet->getRowDimension($rowCount)->setRowHeight(20);
                        $sheet->mergeCells('B'.$rowCount.':H'.$rowCount);
                        $sheet->setCellValue('B'.$rowCount, $this->l('No products were associated yet to this warehouse.'));
                        $sheet->getStyle('B'.$rowCount)->getAlignment()->setHorizontal($excelParams['horizontalLeft']);
                        $rowCount++;
                    }
                }
                // If at least there is an ASM product
                if ($count_asm_products > 0) {
                    // Footer
                    $sheet->getRowDimension($rowCount)->setRowHeight(29);
                    $style = $sheet->getStyle('A'.$rowCount.':H'.$rowCount);
                    $style->applyFromArray($excelParams['stylesFooterHeader']);
                    // Write an .xlsx file
                    if ($excelParams['newPhpExcel']) {
                        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                    } else {
                        $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
                        $writer->setOffice2003Compatibility(true);
                    }

                    header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    header('Content-Disposition:inline;filename=export-warehouses-products-'.date('Y-m-d_H-i-s').'.xlsx');
                    $writer->save('php://output');
                    die();
                } else {
                    $this->displayWarning($this->l('There is no product(s) using advanced stock management to export!'));
                }
            } else {
                $this->displayWarning($this->l('No warehouse has been created so far!'));
            }
        }
        parent::postProcess();
    }

    public function initPHPExcel()
    {
        $newPhpExcel = (class_exists('PhpOffice\PhpSpreadsheet\IOFactory') ? true : false);

        $horizontalLeft = ($newPhpExcel ? \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT : PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $horizontalRight = ($newPhpExcel ? \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT : PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $horizontalCenter = ($newPhpExcel ? \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER : PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $verticalCenter = ($newPhpExcel ? \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER : PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $borderThick = ($newPhpExcel ? \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK : PHPExcel_Style_Border::BORDER_THICK);
        $fillSolid = ($newPhpExcel ? \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID : PHPExcel_Style_Fill::FILL_SOLID);
        $borderThin = ($newPhpExcel ? \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN : PHPExcel_Style_Border::BORDER_THIN);
        $fillType = ($newPhpExcel ? 'fillType' : 'type');
        $borderStyle = ($newPhpExcel ? 'borderStyle' : 'style');

        return array(
            'newPhpExcel' => $newPhpExcel,
            'horizontalLeft' => $horizontalLeft,
            'horizontalRight' => $horizontalRight,
            'horizontalCenter' => $horizontalCenter,
            'verticalCenter' => $verticalCenter,
            'borderThick' => $borderThick,
            'fillSolid' => $fillSolid,
            'borderThin' => $borderThin,
            'fillType' => $fillType,
            'borderStyle' => $borderStyle,
            'styleHeader' => array(
                'font' => array('bold' => true, 'color' => array('argb' => 'FFFFFFFF'), 'name' => "Cooper Hewitt", 'size' => 16),
                'alignment' => array('horizontal' => $horizontalCenter, 'vertical' => $verticalCenter),
                'borders' => array(
                    'top' => array($borderStyle => $borderThick, 'color' => array('argb' => 'FFFF0000')),
                    'bottom' => array($borderStyle => $borderThin, 'color' => array('argb' => 'FF8D8D8D')),
                ),
                'fill' => array($fillType => $fillSolid, 'color' => array('argb' => 'FF666666'))
            ),
            'stylesFooterHeader' => array(
                'font' => array('bold' => true, 'color' => array('argb' => 'FFFFFFFF'), 'size' => 14),
                'alignment' => array('horizontal' => $horizontalCenter, 'vertical' => $verticalCenter),
                'fill' => array($fillType => $fillSolid, 'color' => array('argb' => 'FF000000')),
            ),
            'styleParentData' => array(
                'font' => array('bold' => true, 'color' => array('argb' => 'FF333333'), 'size' => 12),
                'alignment' => array('horizontal' => $horizontalCenter, 'vertical' => $verticalCenter),
                'fill' => array($fillType => $fillSolid, 'color' => array('argb' => 'FFDDDDDD')),
                'borders' => array(
                    'top' => array($borderStyle => $borderThin, 'color' => array('argb' => 'FFF1EEEE')),
                    'bottom' => array($borderStyle => $borderThin, 'color' => array('argb' => 'FF666666')),
                ),
            ),
            'styleProductAttributeHeader' => array(
                'font' => array('bold' => true, 'color' => array('argb' => 'FF333333'), 'size' => 11),
                'alignment' => array('horizontal' => $horizontalCenter, 'vertical' => $verticalCenter),
                'fill' => array($fillType => $fillSolid, 'color' => array('argb' => 'FFEFEFEF')),
            ),
            'styleProductAttributeData' => array(
                'alignment' => array('horizontal' => $horizontalCenter, 'vertical' => $verticalCenter),
                'fill' => array($fillType => $fillSolid, 'color' => array('argb' => 'FFF7F7F7')),
                'borders' => array(
                    'bottom' => array($borderStyle => $borderThin, 'color' => array('argb' => 'FFC3C4C4')),
                ),
            ),
            'styleWarehousesHeader' => array(
                'font' => array('bold' => true, 'color' => array('argb' => 'FF666666'), 'size' => 11),
                'alignment' => array('horizontal' => $horizontalCenter, 'vertical' => $verticalCenter),
                'fill' => array($fillType => $fillSolid, 'color' => array('argb' => 'FFF7E1E2')),
                'borders' => array(
                    'outline' => array($borderStyle => $borderThin, 'color' => array('argb' => 'FFEEEEEE')),
                ),
            ),
            'styleWarehousesData' => array(
                'font' => array('bold' => true, 'color' => array('argb' => 'FF666666'), 'size' => 10),
                'alignment' => array('horizontal' => $horizontalCenter, 'vertical' => $verticalCenter),
            ),
            'styleFooter' => array(
                'borders' => array(
                    'bottom' => array($borderStyle => $borderThin, 'color' => array('argb' => 'FFEEEEEE')),
                ),
            )
        );
    }

    public function processFilter()
    {
        if (!Tools::isSubmit('submitReset'.$this->list_id)) {
            parent::processFilter();
        }
        // Extend searching by reference
        $Filter_reference = trim(Tools::getValue($this->table.'Filter_reference'));
        if (!empty($Filter_reference)) {
            // Allowing to search also by attribute reference in addition with product reference
            if (Combination::isFeatureActive()) {
                $attribute_where = ' (pa.`reference` LIKE \'%'.pSQL($Filter_reference).'%\' OR a.`reference` LIKE \'%'.pSQL($Filter_reference).'%\')';
                $this->_filter = str_replace('`reference` LIKE \'%'.pSQL($Filter_reference).'%\'', $attribute_where, $this->_filter);
            }
        }
        // Remove useless filter " category name "
        $Filter_category = trim(Tools::getValue($this->table.'Filter_categoryName'));
        if (!empty($Filter_category)) {
            $this->_filter = str_replace('AND `categoryName` LIKE \'%'.pSQL($Filter_category).'%\'', '', $this->_filter);
        }
    }

    public function ajaxProcessDisableBulkAsmFromProducts()
    {
        $this->ajaxProcessSwitchBulkProductsToAsm(0);
    }

    public function ajaxProcessAlignReservedQties()
    {
        $offset = (int)Tools::getValue('offset');
        $limit = (int)Tools::getValue('limit');
        $validateBefore = ((int)Tools::getValue('validateBefore') == 1);

        if ($limit === 0) {
            PrestaShopLogger::addLog(// catch any error and save it to logs
                $this->l('Unable to process! Pagination Limit = 0!'),
                3, // severity
                null,
                'AlignReserverdQty',
                null,
                true,
                (int)$this->context->employee->id
            );
            $this->errors[] = $this->l('Unable to process! Please refresh page and try again!');
        }

        $results = array();
        try {
            $this->alignReservedQtyCollection($offset, $limit, $results, $validateBefore);
        } catch (Exception $e) {// catch any error and save it in logs
            PrestaShopLogger::addLog(
                WorkshopAsm::filterMessageException($e->getMessage()),
                3, // severity
                $e->getCode(),
                'AlignQties',
                null,
                true,
                (int)$this->context->employee->id
            );
        }

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

        if (isset($results['isFinished']) && (bool)$results['isFinished']) {/* finished */
            /* some process */
        }
        die(json_encode($results));
    }

    public function alignReservedQtyCollection($offset = false, $limit = false, &$results = null, $validateBefore = false)
    {
        $doneCount = 0;
        Db::getInstance()->disableCache();

        $doneCount += $this->fixWarehousesPrestashopReservedQuantities($offset, $limit, $validateBefore);

        if ($results !== null) {
            $results['isFinished'] = ($doneCount < $limit);
            if ($results['isFinished'] && !$validateBefore) {
                $this->clearSmartyCache();
            }
            $results['doneCount'] = $offset + $doneCount;

            if ($offset === 0) {
                // Compute total count only once
                $products_selection = Tools::getValue('productBox');
                if (Tools::getValue('bulk_for') == 'sel' && empty($products_selection)) {
                    $this->errors[] = $this->l('Please select at least one product/combination!');
                }
                $results['totalCount'] = count(WorkshopAsm::getProductsWithCombinationsFromDb());
            }
            if (!$results['isFinished']) {
                // Since we'll have to POST this array from ajax for the next call, we should care about its size.
                $results['nextPostSize'] = 1024*64; // 64KB more for the rest of the POST query.
                $results['postSizeLimit'] = Tools::getMaxUploadSize();
            }
        }
        Db::getInstance()->enableCache();
    }

    // Bulk alignement of warehouses reserved quantities to global Prestashop quantities
    public function fixWarehousesPrestashopReservedQuantities($offset = false, $limit = false, $validateBefore = false)
    {
        $line_count = 0;
        $result = WorkshopAsm::getProductsWithCombinationsFromDb($offset, $limit);
        foreach ($result as $row) {
            $line_count++;
            if (!$validateBefore) {
                $id_product = (int)$row['id_product'];
                $product = new Product($id_product, false);
                if (Validate::isLoadedObject($product)) {
                    $id_product_attribute = (int)$row['id_product_attribute'];

					// Get not assigned orders
					$orders = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
						'SELECT o.`id_order`, o.`id_carrier`, od.`id_order_detail`
						 FROM `'._DB_PREFIX_.'orders` o
						 '.Shop::addSqlAssociation('orders', 'o').'
                		 INNER JOIN `'._DB_PREFIX_.'order_detail` od ON o.id_order = od.id_order
						 WHERE 
						 od.`product_id` = '.$id_product.' AND 
						 od.`product_attribute_id` = '.$id_product_attribute.' AND
						 od.`id_warehouse` = 0'
					);
					if (count($orders)) {
						// Look for the best warehouse ID
						$associated_warehouses = WorkshopAsm::getAssociatedWarehousesArray(
							$id_product,
							$id_product_attribute
						);
						// IF one warehouse, get it
						$id_warehouse_order = count($associated_warehouses) == 1 ? (int)$associated_warehouses[0] : 0;
						foreach ($orders as $order) {
							if (empty($id_warehouse_order)) {
								foreach ($associated_warehouses as $id_storehouse) {/* Else get the one that order carrier match with one of the warehouse carriers*/
									$carriers = (new StoreHouse($id_storehouse))->getCarriers(true);/* Be aware: look by reference ID, Not carrier ID */
									if (in_array((int)(new Carrier($order['id_carrier']))->id_reference, $carriers)) {
										$id_warehouse_order = $id_storehouse;
										break;
									}
								}
							}
							if (empty($id_warehouse_order)) {/* Else get always the first warehouse */
								$id_warehouse_order = (int)$associated_warehouses[0];
							}
							if ($id_warehouse_order) {
        						$order_detail = new OrderDetail((int)$order['id_order_detail']);
                				if (Validate::isLoadedObject($order_detail)) {
									Db::getInstance()->execute(
										'UPDATE `'._DB_PREFIX_.'order_detail` SET `id_warehouse` = '.(int)$id_warehouse_order.' 
										 WHERE `id_order_detail` = '.(int)$order['id_order_detail']
									);
									/*$order_detail->id_warehouse = (int)$id_warehouse_order;
									$order_detail->save();*/
								}
							}
						}
					}
                }
            }
        }
        return $line_count;
    }

    public function collectProductBox()
    {
        $products_selection = array();
        if (Tools::getIsset('productBox')) {
            foreach (Tools::getValue('productBox') as $key) {
                $ids = explode('_', $key);
                $products_selection[] = (int)$ids[0];
            }
            if (count($products_selection)) {
                $products_selection = array_unique($products_selection);
            }
        }
        return $products_selection;
    }

    public function ajaxProcessSwitchBulkProductsToAsm($asm = 1)
    {
        $offset = (int)Tools::getValue('offset');
        $limit = (int)Tools::getValue('limit');
        $validateBefore = ((int)Tools::getValue('validateBefore') == 1);

        if ($limit === 0) {
            PrestaShopLogger::addLog(// catch any error and save it to logs
                $this->l('Unable to process! Pagination Limit = 0!'),
                3, // severity
                null,
                'ProductsToAsm',
                null,
                true,
                (int)$this->context->employee->id
            );
            $this->errors[] = $this->l('Unable to process! Please refresh page and try again!');
        }

        $results = array();
        try {
            $this->switchByCollection($asm, $offset, $limit, $results, $validateBefore);
        } catch (Exception $e) {// catch any error and save it in logs
            PrestaShopLogger::addLog(
                WorkshopAsm::filterMessageException($e->getMessage()),
                3, // severity
                $e->getCode(),
                'ProductsToAsm',
                null,
                true,
                (int)$this->context->employee->id
            );
        }

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

        if (isset($results['isFinished']) && (bool)$results['isFinished']) {/* finished */
            /* some process */
        }
        die(json_encode($results));
    }

    public function switchByCollection($asm, $offset = false, $limit = false, &$results = null, $validateBefore = false)
    {
        $doneCount = 0;

        Db::getInstance()->disableCache();

        $doneCount += $this->applyAsm($asm, $offset, $limit, $validateBefore);

        if ($results !== null) {
            $results['isFinished'] = ($doneCount < $limit);
            if ($results['isFinished'] && !$validateBefore) {
                $this->clearSmartyCache();
            }
            $results['doneCount'] = $offset + $doneCount;

            if ($offset === 0) {
                // Compute total count only once
                $products_selection = $this->collectProductBox();
                if (Tools::getValue('bulk_for') == 'sel' && empty($products_selection)) {/* if for selected products from list */
                    $this->errors[] = $this->l('Please select at least one product/combination!');
                }
                $results['totalCount'] = count(WorkshopAsm::getSimpleProductsFromDb($products_selection));
            }
            if (!$results['isFinished']) {
                // Since we'll have to POST this array from ajax for the next call, we should care about its size.
                $results['nextPostSize'] = 1024*64; // 64KB more for the rest of the POST query.
                $results['postSizeLimit'] = Tools::getMaxUploadSize();
            }
        }

        if (!$validateBefore && $limit !== 0) {
            $log_message = $this->l('Switching to ASM: processing products');
            if ($offset !== false && $limit !== false) {
                $log_message .= ' '.sprintf($this->l('from %s to %s'), $offset, $limit);
            }
            PrestaShopLogger::addLog(
                $log_message,
                1, // info
                null,
                'ProductsToAsm',
                null,
                true,
                (int)$this->context->employee->id
            );
        }
        Db::getInstance()->enableCache();
    }

    // Bulk switch products to advanced stock management system
    public function applyAsm($asm, $offset = false, $limit = false, $validateBefore = false)
    {
        $line_count = 0;
        $result = WorkshopAsm::getSimpleProductsFromDb($this->collectProductBox(), $offset, $limit);
        foreach ($result as $row) {
            $line_count++;
            if (!$validateBefore) {
                $id_product = (int)$row['id_product'];
                $product = new Product($id_product, false);
                if (Validate::isLoadedObject($product) &&
                    (($asm && !$product->advanced_stock_management) || (!$asm && $product->advanced_stock_management))) {
                    WorkshopAsm::setAdvancedStockManagement($id_product, $asm);
                    // Set warehouses quantities according to prestashop quantities
                    WorkshopAsm::setWarehousesQtiesAccordingPrestaQties($id_product, $asm);
                }
            }
        }
        return $line_count;
    }

    public function ajaxProcessAlignQtiesToWarehouses()
    {
        $this->ajaxProcessAlignQtiesToPrestashop();
    }

    public function ajaxProcessAlignQtiesToPrestashop()
    {
        $offset = (int)Tools::getValue('offset');
        $limit = (int)Tools::getValue('limit');
        $validateBefore = ((int)Tools::getValue('validateBefore') == 1);

        if ($limit === 0) {
            PrestaShopLogger::addLog(// catch any error and save it to logs
                $this->l('Unable to process! Pagination Limit = 0!'),
                3, // severity
                null,
                'AlignQties',
                null,
                true,
                (int)$this->context->employee->id
            );
            $this->errors[] = $this->l('Unable to process! Please refresh page and try again!');
        }

        $results = array();
        try {
            $this->alignByCollection($offset, $limit, $results, $validateBefore);
        } catch (Exception $e) {// catch any error and save it in logs
            PrestaShopLogger::addLog(
                WorkshopAsm::filterMessageException($e->getMessage()),
                3, // severity
                $e->getCode(),
                'AlignQties',
                null,
                true,
                (int)$this->context->employee->id
            );
        }

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

        if (isset($results['isFinished']) && (bool)$results['isFinished']) {/* finished */
            /* some process */
        }
        die(json_encode($results));
    }

    public function alignByCollection($offset = false, $limit = false, &$results = null, $validateBefore = false)
    {
        $doneCount = 0;

        Db::getInstance()->disableCache();

        $doneCount += WorkshopAsm::alignQuantities($offset, $limit, $validateBefore);

        if ($results !== null) {
            $results['isFinished'] = ($doneCount < $limit);
            if ($results['isFinished'] && !$validateBefore) {
                $this->clearSmartyCache();
            }
            $results['doneCount'] = $offset + $doneCount;

            if ($offset === 0) {
                // Compute total count only once
                $products_selection = Tools::getValue('productBox');
                if (Tools::getValue('bulk_for') == 'sel' && empty($products_selection)) {
                    $this->errors[] = $this->l('Please select at least one product/combination!');
                }
                $results['totalCount'] = count(WorkshopAsm::getProductsWithCombinationsFromDb());
            }
            if (!$results['isFinished']) {
                // Since we'll have to POST this array from ajax for the next call, we should care about its size.
                $results['nextPostSize'] = 1024*64; // 64KB more for the rest of the POST query.
                $results['postSizeLimit'] = Tools::getMaxUploadSize();
            }
        }

        if (!$validateBefore && $limit !== 0) {
            $log_message = $this->l('Align Quantities: processing products');
            if ($offset !== false && $limit !== false) {
                $log_message .= ' '.sprintf($this->l('from %s to %s'), $offset, $limit);
            }
            PrestaShopLogger::addLog(
                $log_message,
                1, // info
                null,
                'AlignQties',
                null,
                true,
                (int)$this->context->employee->id
            );
        }
        Db::getInstance()->enableCache();
    }

    public function clearSmartyCache()
    {
        Tools::enableCache();
        Tools::clearCache($this->context->smarty);
        Tools::restoreCacheSettings();
    }

    public function initToolbar()
    {
        return false;
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJqueryPlugin(array('cooki-plugin'));
    }

    public function initBreadcrumbs($tab_id = null, $tabs = null)
    {
        parent::initBreadcrumbs();
    }

    public function fetchTemplate($path, $name, $extension = false)
    {
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_.$this->module->name.$path.$name.'.'.($extension ? $extension : 'tpl')
        );
    }

    public function initModal()
    {
        parent::initModal();

        $modal_content = $this->context->smarty->fetch(
            _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/modal_update_progress.tpl'
        );
        $this->modals[] = array(
            'modal_id' => 'importProgress',
            'modal_class' => 'modal-md',
            'modal_title' => $this->l('Updating your shop...'),
            'modal_content' => html_entity_decode($modal_content)
        );
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['help_link'] = array(
            'href' => 'javascript:void(0);',
            'desc' => '',
            'icon' => 'process-icon-help',
            'js' => 'toggleWindow(\'help\');',
        );
        $this->page_header_toolbar_btn['export_warehouses'] = array(
            'href' => self::$currentIndex.'&exportWarehousesExcel&token='.$this->token,
            'desc' => $this->l('Export stocks by warehouses', null, null, false),
            'icon' => 'process-icon-export'
        );
        $this->page_header_toolbar_btn['export_products'] = array(
            'href' => self::$currentIndex.'&exportProductsExcel&token='.$this->token,
            'desc' => $this->l('Export stocks by products', null, null, false),
            'icon' => 'process-icon-export'
        );
        $this->page_header_toolbar_btn['check_all'] = array(
            'href' => 'javascript:checkAllBox(\'product\');',
            'desc' => $this->l('Check/Uncheck All'),
            'icon' => 'process-icon-toggle-on'
        );
        $this->page_header_toolbar_btn['btn_bulk'] = array(
            'href' => 'javascript:void(0);',
            'desc' => $this->l('Bulk Actions'),
            'icon' => 'process-icon-duplicate',
            'js' => 'toggleWindow(\'bulk\');',
        );
        $this->page_header_toolbar_btn['back_to_dashboard'] = array(
            'href' => $this->context->link->getAdminLink('AdminWkwarehousesdash'),
            'desc' => $this->l('Dashboard', null, null, false),
            'icon' => 'process-icon-back'
        );
        parent::initPageHeaderToolbar();
    }

    /*
    * Method Translation Override For PS 1.7
    */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if (method_exists('Context', 'getTranslator')) {
            $this->translator = Context::getContext()->getTranslator();
   			$translated = $this->translator->trans($string, [], 'Modules.Wkwarehouses.Adminwkwarehousesmanageqtycontroller');
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
