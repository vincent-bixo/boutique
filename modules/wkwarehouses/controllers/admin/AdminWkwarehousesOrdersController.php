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

class AdminWkwarehousesOrdersController extends ModuleAdminController
{
    const FILTER_WAREHOUSE = 'id_filter_warehouse';

	public function __construct()
    {
        require_once(dirname(__FILE__).'/../../classes/Warehouse.php');
        require_once(dirname(__FILE__).'/../../classes/WarehouseProductLocation.php');
        require_once(dirname(__FILE__).'/../../classes/WorkshopAsm.php');
        require_once(dirname(__FILE__).'/../../classes/WarehouseStock.php');

        $this->table = 'order';
        $this->className = 'Order';
        $this->list_id = 'order';
        $this->lang = false;
        $this->explicitSelect = true;
        $this->bootstrap = true;
        $this->deleted = false;
        $this->context = Context::getContext();
        $this->list_no_link = true;
        $this->use_asm = Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT');
        $this->warehouses = array();

        if ($this->use_asm) {
            $warehouses = StoreHouse::getWarehouses();
            if (empty($warehouses)) {
                $this->errors[] = $this->l('You must have at least one warehouse.');
            }
        }

        // Delivery countries
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS(
            'SELECT DISTINCT c.id_country, cl.`name`
             FROM `'._DB_PREFIX_.'orders` o
             '.Shop::addSqlAssociation('orders', 'o').'
             INNER JOIN `'._DB_PREFIX_.'address` a ON a.id_address = o.id_address_delivery
             INNER JOIN `'._DB_PREFIX_.'country` c ON a.id_country = c.id_country
             INNER JOIN `'._DB_PREFIX_.'country_lang` cl ON (
                c.`id_country` = cl.`id_country` AND 
                cl.`id_lang` = '.(int)$this->context->language->id.'
             )
             ORDER BY cl.name ASC'
        );
        $country_array = array();
        foreach ($result as $row) {
            $country_array[$row['id_country']] = $row['name'];
        }

        $statuses_array = array();
        $statuses = OrderState::getOrderStates((int)$this->context->language->id);
        foreach ($statuses as $status) {
            $statuses_array[$status['id_order_state']] = $status['name'];
        }

        $this->fields_list = array(
            'id_order' => array(
                'title' => 'ID',
                'class' => 'text-center fixed-width-sm tbody-order',
            ),
            'reference' => array(
                'title' => $this->l('Reference'),
                'class' => 'text-center fixed-width-md tbody-order',
                'prefix' => '<b>',
                'suffix' => '</b>',
            ),
            'customer' => array(
                'title' => $this->l('Customer'),
                'class' => 'text-left tbody-order',
                'tmpTableFilter' => true
            ),
            'osname' => array(
                'title' => $this->l('Status'),
                'type' => 'select',
                'color' => 'color',
                'class' => 'text-left tbody-order',
                'list' => $statuses_array,
                'filter_key' => 'os!id_order_state',
                'filter_type' => 'int',
                'order_key' => 'osname'
            ),
            'cname' => array(
                'title' => $this->l('Delivery'),
                'type' => 'select',
                'class' => 'text-left tbody-order',
                'list' => $country_array,
                'filter_key' => 'country!id_country',
                'filter_type' => 'int',
                'order_key' => 'cname'
            ),
            'total_paid_tax_incl' => array(
                'title' => $this->l('Total'),
                'class' => 'text-center fixed-width-md tbody-order',
                'prefix' => '<b>',
                'suffix' => '</b>',
                'type' => 'price',
                'currency' => true
            ),
            'date_add' => array(
                'title' => $this->l('Date'),
                'class' => 'fixed-width-lg tbody-order',
                'align' => 'right',
                'type' => 'datetime',
                'filter_key' => 'a!date_add'
            ),
            'carrier_name' => array(
                'title' => $this->l('Carrier'),
                'callback' => 'carrier',
                'class' => 'text-left fixed-width-lg tbody-order',
                'filter_key' => 'ca!name',
            ),
            'id_product_detail' => array(
                'title' => '',
                'align' => 'center',
                'class' => 'tbody-order',
                'callback' => 'viewProducts',
                'orderby' => false,
                'search' => false,
                'remove_onclick' => true
            )
        );
        parent::__construct();
    }

	// Change the current url index (filters, paginations links, etc)
    public function init()
	{
        parent::init();
		if (!Tools::isSubmit('submitResetorder')) {
			$update_current_index = false;
			if (Tools::getIsset($this->table.'Filter_product_q') && Tools::getValue($this->table.'Filter_product_q')) {
				self::$currentIndex .= '&'.$this->table.'Filter_product_q='.(int)Tools::getValue($this->table.'Filter_product_q');
				$update_current_index = true;
			}
			if (Tools::getIsset($this->table.'Filter_combination_q') && Tools::getValue($this->table.'Filter_combination_q')) {
				self::$currentIndex .= '&'.$this->table.'Filter_combination_q='.(int)Tools::getValue($this->table.'Filter_combination_q');
				$update_current_index = true;
			}
			if (Tools::getIsset($this->table.'Filter_order_status') && Tools::getValue($this->table.'Filter_order_status')) {
				self::$currentIndex .= '&'.$this->table.'Filter_order_status='.(int)Tools::getValue($this->table.'Filter_order_status');
				$update_current_index = true;
			}
			if (Tools::getIsset($this->table.'Filter_warehouse_status') && Tools::getValue($this->table.'Filter_warehouse_status')) {
				self::$currentIndex .= '&'.$this->table.'Filter_warehouse_status='.(int)Tools::getValue($this->table.'Filter_warehouse_status');
				$update_current_index = true;
			}
			if ($update_current_index) {
				$this->context->smarty->assign('current', self::$currentIndex);
			}
		}
    }

    public function renderList()
    {
        $id_lang = $this->context->language->id;
        $this->_select = '
            a.id_currency,
            a.id_order AS id_product_detail,        
            CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `customer`,
            osl.`name` AS `osname`,
            os.`color`,
            ca.name as carrier_name,
            country_lang.name as cname,
            IF((SELECT COUNT(so.id_order) FROM `'._DB_PREFIX_.'orders` so WHERE so.id_customer = a.id_customer) > 1, 0, 1) as new
			';
        $this->_join = '
            LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = a.`id_customer`)
            INNER JOIN `'._DB_PREFIX_.'address` address ON (address.id_address = a.id_address_delivery)
            INNER JOIN `'._DB_PREFIX_.'country` country ON (address.id_country = country.id_country)
            INNER JOIN `'._DB_PREFIX_.'country_lang` country_lang ON (
                country.`id_country` = country_lang.`id_country` AND 
                country_lang.`id_lang` = '.(int)$id_lang.'
            )
            LEFT JOIN `'._DB_PREFIX_.'carrier` ca ON (ca.`id_carrier` = a.`id_carrier`)
			LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = a.`current_state`)
			LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (
				os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)$id_lang.'
			)
			';
        $this->_where = $this->getQueryShopList();
		// Filter by product without warehouse association
		if (Tools::getIsset($this->table.'Filter_warehouse_status') && Tools::getValue($this->table.'Filter_warehouse_status')) {
        	$this->_where .= ' AND (
				 	SELECT COUNT(*) FROM `'._DB_PREFIX_.'order_detail` od WHERE od.`id_order` = a.`id_order` AND id_warehouse = 0
				 ) > 0
			';
		}
		// Filter by order status (look for orders not delivered, not shipped, not canceled and with no error)
		if (Tools::getIsset($this->table.'Filter_order_status') && Tools::getValue($this->table.'Filter_order_status')) {
            $this->_where .= ' AND (
                SELECT COUNT(*) FROM `'._DB_PREFIX_.'order_history` oh 
            	INNER JOIN `' . _DB_PREFIX_ . 'order_state` so ON so.`id_order_state` = oh.`id_order_state`
                WHERE oh.`id_order` = a.`id_order` AND 
				so.delivery = 1 AND 
				so.shipped = 1 
            ) = 0 AND (
                SELECT COUNT(*) FROM `'._DB_PREFIX_.'order_history` oh 
                WHERE oh.`id_order` = a.`id_order` AND 
				oh.`id_order_state` IN ('.Configuration::get('PS_OS_CANCELED').', '.Configuration::get('PS_OS_ERROR').')
            ) = 0 			
			';
		}
		// Filter by warehouse or/and product
		$id_warehouse = $this->getWarehouseFilter();
		if (!empty($id_warehouse) ||
			(Tools::getIsset($this->table.'Filter_product_q') && Tools::getValue($this->table.'Filter_product_q')) ||
			(Tools::getIsset($this->table.'Filter_warehouse_status') && Tools::getValue($this->table.'Filter_warehouse_status'))) {
			// Get product ID
			if (Tools::getIsset($this->table.'Filter_product_q') && Tools::getValue($this->table.'Filter_product_q')) {
				$product_id = Tools::replaceAccentedChars(urldecode(Tools::getValue($this->table.'Filter_product_q')));
			}
			// Get product attribute ID
			if (Tools::getIsset($this->table.'Filter_product_q') && Tools::getValue($this->table.'Filter_product_q') &&
				Tools::getIsset($this->table.'Filter_combination_q') && Tools::getValue($this->table.'Filter_combination_q')) {
				$product_attribute_id = Tools::replaceAccentedChars(urldecode(Tools::getValue($this->table.'Filter_combination_q')));
			}
            $this->_where .= ' AND (
                SELECT COUNT(*) FROM `'._DB_PREFIX_.'order_detail` od 
                WHERE od.`id_order` = a.`id_order`'
				.(!empty($id_warehouse) ? ' AND od.`id_warehouse` = '.(int)$id_warehouse : '')
				.(isset($product_id) && $product_id ? ' AND od.`product_id` = '.(int)$product_id : '')
				.(isset($product_attribute_id) && $product_attribute_id ? ' AND od.`product_attribute_id` = '.(int)$product_attribute_id : '')
            .') > 0 ';
		}

        $this->_orderBy = 'date_add';
        $this->_orderWay = 'DESC';

        return parent::renderList();
    }

    /* // Executed after renderList function
	public function getList($id_lang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        //unset($id_lang_shop);
        $this->context = Context::getContext();
        if (Tools::getValue($this->list_id.'Orderby')) {
            $orderBy = Tools::getValue($this->list_id.'Orderby');
        }
        if (Tools::getValue($this->list_id.'Orderway')) {
            $orderWay = Tools::getValue($this->list_id.'Orderway');
        }
        parent::getList($id_lang, $orderBy, $orderWay, $start, $limit, $this->context->shop->id);
		// know sum of reserved quantities of a given product for all orders
		$sum = 0;
		foreach ($this->_list as $order) {
			$products = (new Order($order['id_order']))->getProducts();
			foreach ($products as $k => $product) {
				// Filter each order products by product filter
				if (Tools::getIsset($this->table.'Filter_product_q') && Tools::getValue($this->table.'Filter_product_q')) {
					if ($product['product_id'] != (int)urldecode(Tools::getValue($this->table.'Filter_product_q'))) {
						unset($products[$k]);
						continue;
					}
				}
				$sum += $product['product_quantity'];
			}
		}
    }*/

    public function viewProducts($id_order)
    {
        $order = new Order((int)$id_order);
        if (!Validate::isLoadedObject($order)) {
            return '';
        }
		$order_hasBeenDelivered = $order->hasBeenDelivered();
		$order_hasBeenShipped = $order->hasBeenShipped();
        // Get all products related to the current order
        $products = $order->getProducts();
        foreach ($products as $k => &$product) {
            $id_product = (int)$product['product_id'];
            $id_product_attribute = (int)$product['product_attribute_id'];
			// If simple product: Filter each order products by product filter
			if (Tools::getIsset($this->table.'Filter_product_q') && Tools::getValue($this->table.'Filter_product_q')) {
				if ($id_product != (int)urldecode(Tools::getValue($this->table.'Filter_product_q'))) {
					unset($products[$k]);
					continue;
				}
			}
			// If combination: Filter each order products by product filter
			if (Tools::getIsset($this->table.'Filter_product_q') && Tools::getValue($this->table.'Filter_product_q') &&
				Tools::getIsset($this->table.'Filter_combination_q') && Tools::getValue($this->table.'Filter_combination_q')) {
				if ($id_product == (int)urldecode(Tools::getValue($this->table.'Filter_product_q')) &&
					$id_product_attribute != (int)urldecode(Tools::getValue($this->table.'Filter_combination_q'))) {
					unset($products[$k]);
					continue;
				}
			}
			// Filter by product without warehouse association
			if (Tools::getIsset($this->table.'Filter_warehouse_status') && Tools::getValue($this->table.'Filter_warehouse_status')) {
				if ($product['id_warehouse'] != 0) {
					unset($products[$k]);
					continue;
				}
			}
			// Filter each order by warehouse
			$id_warehouse = $this->getWarehouseFilter();
			// If warehouse not match the filter, unset it
			if (!empty($id_warehouse) && $product['id_warehouse'] != $id_warehouse) {
				unset($products[$k]);
				continue;
			}
			// Available qty = physical - reserved quantity
            $product['in_stock'] = (int)WarehouseStock::getAvailableQuantityByWarehouse(
				$id_product,
				$id_product_attribute,
				$product['id_warehouse']
			);
            $product['product_link'] = WorkshopAsm::getProductLink($id_product);

            // Warehouses list according to the selected carrier
            $order_id_warehouse = (int)$product['id_warehouse'];
			/* if order not yet delivered or shipped and order not canceled or error occured */
            if (!$order_hasBeenDelivered || !$order_hasBeenShipped || !in_array($order->current_state, array(Configuration::get('PS_OS_CANCELED'), Configuration::get('PS_OS_ERROR')))) {
				$associatedWarehouseList = array();
				/* Get associated warehouses list */
				$product_associated_warehouses = WorkshopAsm::getAssociatedWarehousesArray($id_product, $id_product_attribute);
				if (!empty($product_associated_warehouses)) {
					$associatedWarehouseList = StoreHouse::getWarehouses(
						$product_associated_warehouses,
						false
					);
				}
				/* Filter the warehouses list according to the order carrier */
                if (count($associatedWarehouseList)) {
                    foreach ($associatedWarehouseList as $k => &$row) {
                        $id_storehouse = (int)$row['id_warehouse'];
                        //$carriers = (new StoreHouse($id_storehouse))->getCarriers(true);
                        //if (in_array((int)(new Carrier($order->id_carrier))->id_reference, $carriers)) {
                        $row['is_default'] = ($order_id_warehouse && $id_storehouse == $order_id_warehouse ? 1 : 0);
                        /*} else {
                            unset($associatedWarehouseList[$k]);
                        }*/
                    }
                    $product['warehouses_list'] = $associatedWarehouseList;
                }
            }
        }

        $this->context->smarty->assign(array(
            'order' => $order,
            'order_link' => WorkshopAsm::getOrderLink($id_order),
            'products' => $products,
            'use_asm' => $this->use_asm,
        ));
        return $this->fetchTemplate(
            '/views/templates/admin/wkwarehouses_orders/helpers/list/',
            'product_details'
        );
    }

    public function carrier($carrier_name)
    {
        return (!empty($carrier_name) ? $carrier_name : '');
    }

    public function ajaxProcessAutoCompleteProductFilter()
    {
        $id_lang = $this->context->language->id;
        $query = Tools::getValue('q', false);
        if (!$query || $query == '' || Tools::strlen($query) < 1) {
            die();
        }
        if ($pos = strpos($query, ' (ref:')) {
            $query = Tools::substr($query, 0, $pos);
        }

        $items = Db::getInstance()->executeS(
            'SELECT p.`id_product`, pl.`link_rewrite`, p.`reference`, pl.`name` pname
             FROM `'._DB_PREFIX_.'product` p
             '.Shop::addSqlAssociation('product', 'p').'
             LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
                pl.id_product = p.id_product AND 
                pl.id_lang = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
             )
             WHERE (
				p.id_product = \''.pSQL($query).'\' OR 
				pl.name LIKE \'%'.pSQL($query).'%\' OR 
				p.reference LIKE \'%'.pSQL($query).'%\'
			 )
			 GROUP BY p.id_product'
        );
        $results = array();
        if ($items) {
            foreach ($items as $item) {
				$results[] = array(
					'id_product' => $item['id_product'],
					'pname' => trim($item['pname']),
					'combinations' => $this->getAttributesNames($item['id_product']),
				);
            }
        }
        die(json_encode($results));
    }

    public function getAttributesNames($id_product)
    {
		$combinations = array();
		if (Combination::isFeatureActive()) {
			$product = new Product($id_product, false);
			if ($product->hasAttributes()) {
				// Attributes
				$attributes = $product->getAttributesGroups($this->context->language->id);
				foreach ($attributes as $attribute) {
					$id_pa = (int)$attribute['id_product_attribute'];
					if (!isset($combinations[$id_pa]['attributes'])) {
						$combinations[$id_pa]['attributes'] = ' - ';
					}
					$combinations[$id_pa]['attributes'] .= $attribute['attribute_name'].' - ';
					$combinations[$id_pa]['id_product_attribute'] = $id_pa;
					// is it selected?
					$combinations[$id_pa]['is_selected'] = 0;
					if (Tools::getIsset($this->table.'Filter_combination_q') && Tools::getValue($this->table.'Filter_combination_q') == $id_pa) {
						$combinations[$id_pa]['is_selected'] = 1;
					}
				}
			}
		}
		return $combinations;
    }

    public function ajaxProcessDisplayOrderProductWarehouses()
    {
		$quantity_locations = array();
        $product = new Product((int)Tools::getValue('id_product'), false);
        if (Validate::isLoadedObject($product) && $product->advanced_stock_management) {
            $id_product_attribute = (int)Tools::getValue('id_combination');
            $associated_wh_collection = StorehouseProductLocation::getCollection($product->id, $id_product_attribute);
			if ($associated_wh_collection) {
				foreach ($associated_wh_collection as $warehouse_location) {
					$id_warehouse = (int)$warehouse_location->id_warehouse;
					$tmp = array();
					$tmp['name'] = (new StoreHouse($id_warehouse, $this->context->language->id))->name;
					$tmp['quantity'] = (int)WarehouseStock::getAvailableQuantityByWarehouse(
						$product->id,
						$id_product_attribute,
						$id_warehouse
					);
					array_push($quantity_locations, $tmp);
				}
			}
        }
		$this->context->smarty->assign(array(
			'quantity_locations' => $quantity_locations,
		));
		die(json_encode(array(
			'content' => $this->context->smarty->fetch(
				_PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/product_stock_warehouses.tpl'
			)
		)));
    }

    public function ajaxProcessUpdateOrderWarehouse($id_product_order_detail = null, $json = true)
    {
        $id_order_detail = Tools::getIsset('id_order_detail') && Tools::getValue('id_order_detail') ? (int)Tools::getValue('id_order_detail') : $id_product_order_detail;
        $id_warehouse = (int)Tools::getValue('id_warehouse');
        $order_detail = new OrderDetail($id_order_detail);

        if (empty($id_warehouse) && $json) {
            die(json_encode(array(
                'hasError' => true,
                'msgError' => $this->l('The warehouse selection is required!')
            )));
        }

        if (Validate::isLoadedObject($order_detail)) {
            /*$order = new Order((int)$order_detail->id_order);
            if (Validate::isLoadedObject($order)) {
                if ($json && ($order->hasBeenDelivered() || $order->hasBeenShipped())) {
					die(json_encode(array(
						'hasError' => true,
						'msgError' => $this->l('You can not change the warehouse while the order is already shipped or delivered. Try to change the order status then try again!')
					)));
				}
            }*/

			//$carriers = (new StoreHouse($id_warehouse))->getCarriers(true);
			//if (in_array((int)(new Carrier($order->id_carrier))->id_reference, $carriers)) {
			if ($id_warehouse != $order_detail->id_warehouse) {
				// Add warehouse association if not exists yet
				if (!StorehouseProductLocation::getIdByProductAndWarehouse(
					$order_detail->product_id,
					$order_detail->product_attribute_id,
					$id_warehouse
				)) {
					$warehouse_location_entity = new StorehouseProductLocation();
					$warehouse_location_entity->id_product = (int)$order_detail->product_id;
					$warehouse_location_entity->id_product_attribute = (int)$order_detail->product_attribute_id;
					$warehouse_location_entity->id_warehouse = (int)$id_warehouse;
					$warehouse_location_entity->save();
				}
				$order_detail->id_warehouse = (int)$id_warehouse;
				$order_detail->save();
				if ($json) {
					die(json_encode(array(
						'hasError' => false,
						'msgOk' => $this->l('Warehouse has been assigned successfully!')
					)));
				}
			}/* else {
				if ($json) {
					die(json_encode(array(
						'hasError' => true,
						'msgError' => $this->l('Error: the carrier of the order does not match any of the selected warehouse carriers!')
					)));
				}
			}*/
		} else {
			if ($json) {
				die(json_encode(array(
					'hasError' => true,
					'msgError' => $this->l('Error: order detail does not exist!')
				)));
			}
		}
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitBulkWarehouseAssignement')) {
            $productBox = Tools::getValue('productBox');
            $id_warehouse = Tools::getValue('id_warehouse');

            if (empty($productBox)) {
                $this->errors[] = $this->l('Please select at least one product!');
            }
            if (empty($id_warehouse)) {
                $this->errors[] = $this->l('Warehouse selection is required!');
            }
            // If no errors
            if (count($this->errors) == 0) {
                foreach ($productBox as $id_order_detail) {
                    $this->ajaxProcessUpdateOrderWarehouse($id_order_detail, false);
                }
                Tools::redirectAdmin(self::$currentIndex.'&conf=4&token='.$this->token);
            }
        }
        parent::postProcess();
    }

    public function fetchTemplate($path, $name, $extension = false)
    {
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_.$this->module->name.$path.$name.'.'.($extension ? $extension : 'tpl')
        );
    }

    public function initContent()
    {
		$cookie = $this->context->cookie;
		$warehouses = StoreHouse::getWarehouses();
		// ==============================
		// Warehouses Filter
		// ==============================
		$id_warehouse = $this->getWarehouseFilter();
		foreach ($warehouses as &$s) {
			$s['is_selected'] = 0;
			if (!empty($id_warehouse) && $s['id_warehouse'] == $id_warehouse) {
				$s['is_selected'] = 1;
			}
		}
		$cookie->{self::FILTER_WAREHOUSE} = $id_warehouse;
		$cookie->Filter_id_warehouse = (!empty($id_warehouse) ? 1 : 0);

		$combinations = array();
		if (Tools::getIsset($this->table.'Filter_product_q') && Tools::getValue($this->table.'Filter_product_q')) {
			$product_id = Tools::replaceAccentedChars(urldecode(Tools::getValue($this->table.'Filter_product_q')));
			$combinations = $this->getAttributesNames($product_id);			
		}

		// For Header tpl
		$this->tpl_list_vars = array(
			'warehouses' => $warehouses,
			'combinations' => $combinations,
			'filter_warehouse' => self::FILTER_WAREHOUSE,
			'this_path' => _MODULE_DIR_.$this->module->name,
			'is_warehouse_filter' => $cookie->Filter_id_warehouse,
		);
		parent::initContent();
    }

    /*
    * Function must return array of selected warehouse from filter
    */
    public function getWarehouseFilter()
    {
        $id_filter = '';
        if (Tools::getIsset($this->list_id.self::FILTER_WAREHOUSE)) {
            $id_filter = Tools::getValue($this->list_id.self::FILTER_WAREHOUSE);
            // If empty value submitted
            if (!$id_filter) {
                unset($this->context->cookie->{self::FILTER_WAREHOUSE});
                unset($this->context->cookie->Filter_id_warehouse);
                return false;
            }
        } elseif (!empty($this->context->cookie->{self::FILTER_WAREHOUSE}) &&
			isset($this->context->cookie->{self::FILTER_WAREHOUSE})) {
            $id_filter = $this->context->cookie->{self::FILTER_WAREHOUSE};
        }
        return $id_filter;
    }

    public function processResetFilters($list_id = null)
    {
        $prefix = str_replace(array('admin', 'controller'), '', Tools::strtolower(get_class($this)));
        $filters = $this->context->cookie->getFamily($prefix.$this->list_id.'Filter_');

        foreach ($filters as $cookie_key => $filter) {
            if (strncmp($cookie_key, $prefix.$this->list_id.'Filter_', 7 + Tools::strlen($prefix.$this->list_id)) == 0) {
                $key = Tools::substr($cookie_key, 7 + Tools::strlen($prefix.$this->list_id));
                /* Table alias could be specified using a ! eg. alias!field */
                $tmp_tab = explode('!', $key);
                $key = (count($tmp_tab) > 1 ? $tmp_tab[1] : $tmp_tab[0]);
                unset($this->context->cookie->$cookie_key);
            }
        }

        if (isset($this->context->cookie->{'submitFilter'.$this->list_id})) {
            unset($this->context->cookie->{'submitFilter'.$this->list_id});
        }
        if (isset($this->context->cookie->{$prefix.$this->list_id.'Orderby'})) {
            unset($this->context->cookie->{$prefix.$this->list_id.'Orderby'});
        }
        if (isset($this->context->cookie->{$prefix.$this->list_id.'Orderway'})) {
            unset($this->context->cookie->{$prefix.$this->list_id.'Orderway'});
        }

        // Reset Custom Filters
        unset($this->context->cookie->{self::FILTER_WAREHOUSE});
        unset($this->context->cookie->Filter_id_warehouse);

        unset($_POST);
        $this->_filter = false;
        unset($this->_filterHaving);
        unset($this->_having);
    }

    public function getQueryShop()
    {
        $query = '';
        $shop_context = Shop::getContext();
        $context = Context::getContext();

        if (isset($this->context->shop->id) && ($shop_context != Shop::CONTEXT_ALL || ($context->controller->multishop_context_group != false && $shop_context != Shop::CONTEXT_GROUP))) {
            $query = ' AND id_shop = '.(int)$this->context->shop->id;
        } elseif (isset($this->context->shop->id_shop_group)) {
            $id_shops = ShopGroup::getShopsFromGroup($this->context->shop->id_shop_group);

            $array_shop = array();
            foreach ($id_shops as $id_shop) {
                $array_shop[] = (int)$id_shop['id_shop'];
            }
            $query = ' AND id_shop IN ('.pSQL(implode(',', $array_shop)).')';
        }
        return $query;
    }
    
    public function getQueryShopList()
    {
        $query = '';
        $shop_context = Shop::getContext();
        $context = Context::getContext();

        if (isset($this->context->shop->id) && ($shop_context != Shop::CONTEXT_ALL || ($context->controller->multishop_context_group != false && $shop_context != Shop::CONTEXT_GROUP))) {
            $query = ' AND a.id_shop = '.(int)$this->context->shop->id;
        } elseif (isset($this->context->shop->id_shop_group)) {
            $id_shops = ShopGroup::getShopsFromGroup($this->context->shop->id_shop_group);

            $array_shop = array();
            foreach ($id_shops as $id_shop) {
                $array_shop[] = (int)$id_shop['id_shop'];
            }
            $query = ' AND a.id_shop IN ('.pSQL(implode(',', $array_shop)).')';
        }
        return $query;
    }

    public function ajaxProcessLoadWarehousesForm()
    {
        $this->context->smarty->assign(array(
            'link' => new Link(),
			'warehouses' => StoreHouse::getWarehouses(),
        ));
        die(json_encode(array(
            'content' => $this->context->smarty->fetch(
                _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/warehouse-form.tpl'
            )
        )));
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJqueryPlugin(array('autocomplete', 'cooki-plugin'));
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_btn['check_all'] = array(
            'href' => 'javascript:checkAllBox(\'order\');',
            'desc' => $this->l('Check/Uncheck All'),
            'icon' => 'process-icon-toggle-on'
        );
        $this->page_header_toolbar_btn['assign_warehouse'] = array(
            'href' => 'javascript:void(0);',
            'desc' => $this->l('Assign warehouse'),
            'icon' => 'process-icon-edit',
			'js' => 'assignWarehouseAction();',
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
   			$translated = $this->translator->trans($string, [], 'Modules.Wkwarehouses.Adminwkwarehousesorderscontroller');
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
