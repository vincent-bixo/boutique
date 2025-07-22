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
*  @author		KHOUFI Wissem - K.W
*  @copyright 	2024 Khoufi Wissem
*  @license   	http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  @version   	1.85.40; PSCompatiblity 1.7.3 and Greater
*/
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Stock\StockManager;
use PrestaShop\PrestaShop\Adapter\StockManager as StockManagerAdapter;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Domain\Order\CancellationActionType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Wkwarehouses extends Module
{
    const CONFIG_KEY = 'WKWAREHOUSE_';
	public static $access_after_add_hook = true;
    public $mail_folder;

	// Passing data between classes
	// https://www.coderrr.com/php-passing-data-between-classes/
    public static function setAccessAfterAddHook($data)
	{
        return self::$access_after_add_hook = $data;
    }

    public static function getAccessAfterAddHook()
	{
        return self::$access_after_add_hook;
    }

    public function __construct()
    {
        require_once(dirname(__FILE__).'/classes/Warehouse.php');
        require_once(dirname(__FILE__).'/classes/WarehouseProductLocation.php');
        require_once(dirname(__FILE__).'/classes/WorkshopAsm.php');
        require_once(dirname(__FILE__).'/classes/WarehouseStock.php');
        require_once(dirname(__FILE__).'/classes/WarehouseStockMvt.php');

        $this->name = 'wkwarehouses';
        $this->tab = 'administration';
        $this->version = '1.85.40';
        $this->author = 'Khoufi Wissem';
        $this->need_instance = 0;
        $this->module_key = '5038543a31e6ce1ed934927c19d32bb8';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Wk Warehouses Management');
        $this->description = $this->l('Manage warehouses, their locations and quantities in stock for your products.');
        $this->confirmUninstall = $this->l('All products using already advanced stock management will switch to normal stock management and lose accordingly warehouses stocks configurations. Are you sure you want to uninstall? ');
        $this->mail_folder = _PS_MODULE_DIR_.$this->name.'/mails/';
        //  Min version of PrestaShop on which the module can be installed
		$this->ps_versions_compliancy = array(
			'min' => '1.7.2',
			'max' => _PS_VERSION_,
		);

        /* Add to cart button allowed pages */
        $this->listing_pages = array(
            'category',
            'manufacturer',
            'supplier',
            'search',
            'index', // Homepage
            'searchiqit', // IQIT Warehouse theme
            'retrieveproducts', // wk search products plus module search controller (1.7)
            'findproducts', // wk advanced search by categories module search controller (1.7)
        );
        /* Overrides list */
        $this->my_overrides = array(
            0 => array(
                'source' => _PS_MODULE_DIR_.$this->name.'/override/controllers/admin/AdminProductsController.php',
                'target' => _PS_OVERRIDE_DIR_.'controllers/admin/AdminProductsController.php',
                'targetdir' => _PS_OVERRIDE_DIR_.'controllers/admin/'
            ),
            1 => array(
                'source' => _PS_MODULE_DIR_.$this->name.'/override/controllers/front/OrderConfirmationController.php',
                'target' => _PS_OVERRIDE_DIR_.'controllers/front/OrderConfirmationController.php',
                'targetdir' => _PS_OVERRIDE_DIR_.'controllers/front/'
            ),
            2 => array(
                'source' => _PS_MODULE_DIR_.$this->name.'/override/controllers/front/CartController.php',
                'target' => _PS_OVERRIDE_DIR_.'controllers/front/CartController.php',
                'targetdir' => _PS_OVERRIDE_DIR_.'controllers/front/'
            ),
            3 => array(
                'source' => _PS_MODULE_DIR_.$this->name.'/override/classes/order/Order.php',
                'target' => _PS_OVERRIDE_DIR_.'classes/order/Order.php',
                'targetdir' => _PS_OVERRIDE_DIR_.'classes/order/'
            ),
            4 => array(
                'source' => _PS_MODULE_DIR_.$this->name.'/override/classes/Cart.php',
                'target' => _PS_OVERRIDE_DIR_.'classes/Cart.php',
                'targetdir' => _PS_OVERRIDE_DIR_.'classes/'
            ),
            5 => array(
                'source' => _PS_MODULE_DIR_.$this->name.'/override/classes/Address.php',
                'target' => _PS_OVERRIDE_DIR_.'classes/Address.php',
                'targetdir' => _PS_OVERRIDE_DIR_.'classes/'
            ),
            6 => array(
                'source' => _PS_MODULE_DIR_.$this->name.'/override/classes/stock/StockManager.php',
                'target' => _PS_OVERRIDE_DIR_.'classes/stock/StockManager.php',
                'targetdir' => _PS_OVERRIDE_DIR_.'classes/stock/'
            ),
            7 => array(
                'source' => _PS_MODULE_DIR_.$this->name.'/override/classes/checkout/DeliveryOptionsFinder.php',
                'target' => _PS_OVERRIDE_DIR_.'classes/checkout/DeliveryOptionsFinder.php',
                'targetdir' => _PS_OVERRIDE_DIR_.'classes/checkout/'
            ),
            8 => array(
                'source' => _PS_MODULE_DIR_.$this->name.'/override/classes/stock/Warehouse.php',
                'target' => _PS_OVERRIDE_DIR_.'classes/stock/Warehouse.php',
                'targetdir' => _PS_OVERRIDE_DIR_.'classes/stock/'
            ),
        );
        if (version_compare(_PS_VERSION_, '1.7.7.0', '<')) {
            $this->my_overrides[] = array(
                'source' => _PS_MODULE_DIR_.$this->name.'/override/controllers/admin/AdminOrdersController.php',
                'target' => _PS_OVERRIDE_DIR_.'controllers/admin/AdminOrdersController.php',
                'targetdir' => _PS_OVERRIDE_DIR_.'controllers/admin/',
            );
        }

        /* T A B S */
        $this->my_tabs = array(
            0 => array(
                'name' => array(
                    'en' => 'Wk Warehouses Management',
                    'fr' => 'WK Gestion Entrepôts'
                ),
                'className' => 'AdminParentWkwarehousesconf',
                'id_parent' => 0,
                'is_tool' => 0,
                'is_hidden' => 0,
                'ico' => 0
            ),
            1 => array(
                'name' => array(
                    'en' => 'Dashboard',
                    'fr' => 'Tableau De Bord'
                ),
                'className' => 'AdminWkwarehousesdash',
                'id_parent' => 0,
                'is_tool' => 0,
                'is_hidden' => 0,
                'ico' => 0
            ),
            2 => array(
                'name' => array(
                    'en' => 'Manage Warehouses',
                    'fr' => 'Gestion Entrepôts'
                ),
                'className' => 'AdminManageWarehouses',
                'id_parent' => -1,
                'is_tool' => 0,
                'is_hidden' => 0,
                'ico' => 'warehouses.png'
            ),
            3 => array(
                'name' => array(
                    'en' => 'Manage Products/Warehouses',
                    'fr' => 'Gestion Produits/Entrepôts'
                ),
                'className' => 'AdminWkwarehousesManageQty',
                'id_parent' => -1,
                'is_tool' => 0,
                'is_hidden' => 0,
                'ico' => 'stock.png'
            ),
            4 => array(
                'name' => array(
                    'en' => 'Stock Movements',
                    'fr' => 'Mouvements de Stock'
                ),
                'className' => 'AdminWkwarehousesStockMvt',
                'id_parent' => -1,
                'is_tool' => 0,
                'is_hidden' => 0,
                'ico' => 'movement.png'
            ),
            5 => array(
                'name' => array(
                    'en' => 'Instant Stock Status',
                    'fr' => 'État Instantané du Stock'
                ),
                'className' => 'AdminWkwarehousesStockInstantState',
                'id_parent' => -1,
                'is_tool' => 0,
                'is_hidden' => 0,
                'ico' => 'instant.png'
            ),
            6 => array(
                'name' => array(
                    'en' => 'Orders/Warehouses Assignments',
                    'fr' => 'Associations Entrepôts/Commandes'
                ),
                'className' => 'AdminWkwarehousesOrders',
                'id_parent' => -1,
                'is_tool' => 0,
                'is_hidden' => 0,
                'ico' => 'assign-order.png'
            ),
            7 => array(
                'name' => array(
                    'en' => 'Cron',
                ),
                'className' => 'AdminWkwarehousestaskrun',
                'id_parent' => -1,
                'is_tool' => 0,
                'is_hidden' => 1,
            ),
            8 => array(
                'name' => array(
                    'en' => 'Actions in bulk',
                    'fr' => 'Actions en masse'
                ),
                'className' => 'AdminWkwarehousesbulk',
                'id_parent' => -1,
                'is_tool' => 1,
                'ico' => 'w-bulk.png'
            ),
        );
        /* CONFIG PARAMETERS NAMES */
        $this->keyInfos = array(
            'USE_ASM_NEW_PRODUCT' => 'int',
            'STOCKPRIORITY_INC' => 'int',
            'ON_DELIVERY_SLIP' => 'int',
            'STOCKPRIORITY_DEC' => 'int',
            'PAGINATION_USE' => 'int',
            'PAGINATION_LIMIT' => 'int',
            'DISPLAY_STOCK_INFOS' => 'int',
            'PAGINATION_NUMBER_LINKS' => 'int',
            'WAREHOUSES_INCART' => 'int',
            'DISPLAY_STOCK_ICON' => 'int',
            'LOCATIONS_INCART' => 'int',
            'DELIVERYTIMES_INCART' => 'int',
            'QUANTITIES_INCART' => 'int',
            'DISPLAY_DELIVERIES_TIME' => 'int',
            'DISPLAY_LOCATION' => 'int',
            'ALLOW_MULTIWH_CART' => 'int',
            'LOCATION_ORDER_PAGE' => 'int',
            'ALLOWSET_WAREHOUSE' => 'int',
            'CHANGE_ORDER_WAREHOUSE' => 'int',
            'STOCKSINFOS_ORDER_PAGE' => 'int',
            'DISPLAY_SELECTED_WAREHOUSE' => 'int',
            //'NO_SPLIT_ORDERS' => 'int',
            'DISPLAY_WAREHOUSE_NAME' => 'int',
            'DISPLAY_SELECTED_LOCATION' => 'int',
            'ENABLE_FONTAWESOME' => 'int',
            'DISPLAY_SELECTED_STOCK' => 'int',
            'WAREHOUSEINFOS_POSITION' => 'string',
            'DISPLAY_DELIVERYTIME' => 'int',
            'ENABLE_INCART' => 'int',
            'POSITION_INCART' => 'string',
            'ALLOW_MULTICARRIER_CART' => 'int',
            'ALLOW_MULTICARRIER_CHOICE' => 'int',
            'PRODUCT_NAME_SHIPMENT_PART' => 'int',
            'MODE_MULTICARRIER_CHOICE' => 'string',
            'WH_NAME_SHIPMENT_PART' => 'int',
            'PRODUCT_NOT_ASM_GET_BEST_CARRIERS' => 'int',
            'SENDMAIL_EACH_EMPLOYEE' => 'int',
            'DISPLAY_COUNTRIES' => 'int',
            'WAY_FIX_QUANTITIES' => 'string',
            'DISPLAY_COUNTRY' => 'int',
            'COUNTRIES_INCART' => 'int',
            'ALLOW_MULTI_ADDRESSES' => 'int',
            'DELIVERY_ADDRESS_INCART' => 'int',
            'SHOW_OUTOFSTOCK' => 'int',
        );
        $this->pagination_lengths = array(
            array('id' => 5, 'name' => 5),
            array('id' => 20, 'name' => 20),
            array('id' => 50, 'name' => 50),
            array('id' => 100, 'name' => 100),
            array('id' => 300, 'name' => 300),
            array('id' => 500, 'name' => 500),
            array('id' => 1000, 'name' => 1000),
        );
    }

    public function install($install = true)
    {
        if (!version_compare(_PS_VERSION_, '1.7.2.0', '>=')) {
            $this->_errors[] = $this->l('This module can not be installed on Prestashop version less than 1.7.2!');
            return false;
        } else {
            // Prepare override cart file according to PS version to avoid warning declaration in debug mode
            $override_cart_file = _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR
			.'override'.DIRECTORY_SEPARATOR
			.'classes'.DIRECTORY_SEPARATOR.'Cart.php';
            if (version_compare(_PS_VERSION_, '1.7.7.0', '<=')) {
				$override_cart_content = Tools::file_get_contents($override_cart_file);
				$override_cart_content = str_replace(
					'public function getPackageShippingCost($id_carrier = null, $use_tax = true, Country $default_country = null, $product_list = null, $id_zone = null, bool $keepOrderPrices = false)',
					'public function getPackageShippingCost($id_carrier = null, $use_tax = true, Country $default_country = null, $product_list = null, $id_zone = null)',
					$override_cart_content
				);
				$override_cart_content = str_replace(
					'$shipping_cost = parent::getPackageShippingCost($id_carrier, $use_tax, $default_country, $product_list, $id_zone, $keepOrderPrices);',
					'$shipping_cost = parent::getPackageShippingCost($id_carrier, $use_tax, $default_country, $product_list, $id_zone);',
					$override_cart_content
				);
            	file_put_contents($override_cart_file, ''.$override_cart_content);
            }
        	if (version_compare(_PS_VERSION_, '1.7.6.0', '>=')) {
            	$path = _PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'override'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR;
                copy($path.'PaymentModule1760.php', $path.'PaymentModule.php');
			}
            if (!parent::install() ||
                !Configuration::updateGlobalValue('WKWAREHOUSE_LAST_VERSION', $this->version) ||
                //!$this->registerHook('actionProductSave') ||
                //!$this->registerHook('actionAdminControllerSetMedia') ||
                !$this->registerHook('actionValidateOrder') ||
                !$this->registerHook('actionProductUpdate') ||
                !$this->registerHook('actionObjectUpdateAfter') ||
                !$this->registerHook('actionCartUpdateQuantityBefore') ||
                !$this->registerHook('actionProductDelete') ||
				!$this->registerHook('actionProductCancel') ||
                !$this->registerHook('actionObjectAddAfter') ||
                !$this->registerHook('actionObjectDeleteAfter') ||
                !$this->registerHook('actionAdminDeleteBefore') ||
                !$this->registerHook('actionCartSave') ||
                !$this->registerHook('actionOrderStatusPostUpdate') ||
                !$this->registerHook('actionObjectProductInCartDeleteAfter') ||
                !$this->registerHook('actionAttributeCombinationDelete') ||
                !$this->registerHook('actionUpdateQuantity') ||
                !$this->registerHook('actionGetProductPropertiesAfter') ||
                !$this->registerHook('actionGetProductPropertiesBefore') ||
                !$this->registerHook('actionSetInvoice') ||
                !$this->registerHook('actionOrderEdited') ||
                !$this->registerHook('displayHeader') ||
                !$this->registerHook('displayAdminOrder') ||
                !$this->registerHook('displayProductAdditionalInfo') ||
                !$this->registerHook('displayAdminProductsExtra') ||
                !$this->registerHook('displayPDFDeliverySlip') ||
                !$this->registerHook('displayProductExtraContent') ||
                !$this->registerHook('displayReassurance') ||
                !$this->registerHook('displayProductPriceBlock') ||
                !$this->registerHook('displayBackOfficeHeader')) {
                return false;
            }
            // Fix Prestashop bug (does not call to ActionCartUpdateQuantityBefore Hook while it's present in Cart.php)
            $this->fixActionCartUpdateQuantityBeforeHook();

            Configuration::updateValue('PS_ADVANCED_STOCK_MANAGEMENT', 1); // Activate A.S.M
            Configuration::updateValue('WKWAREHOUSE_DEFAULT_NEW_PRODUCT', 0);
            Configuration::updateValue('WKWAREHOUSE_USE_ASM_NEW_PRODUCT', 0);
            Configuration::updateValue('WKWAREHOUSE_ON_DELIVERY_SLIP', 0);
            Configuration::updateValue('WKWAREHOUSE_DISPLAY_STOCK_INFOS', 0);
            Configuration::updateValue('WKWAREHOUSE_PAGINATION_USE', 0);
            Configuration::updateValue('WKWAREHOUSE_PAGINATION_LIMIT', 20);
            Configuration::updateValue('WKWAREHOUSE_PAGINATION_NUMBER_LINKS', 10);
            Configuration::updateValue('WKWAREHOUSE_DISPLAY_STOCK_ICON', 0);
            Configuration::updateValue('WKWAREHOUSE_PRIORITY', '');
            Configuration::updateValue('WKWAREHOUSE_ALLOW_MULTIWH_CART', 1);
            Configuration::updateValue('WKWAREHOUSE_ALLOW_MULTICARRIER_CART', 1);
            Configuration::updateValue('WKWAREHOUSE_SENDMAIL_EACH_EMPLOYEE', 0);
            Configuration::updateValue('WKWAREHOUSE_ALLOW_MULTICARRIER_CHOICE', 0);
            Configuration::updateValue('WKWAREHOUSE_PRIORITY_DECREASE', '');
            Configuration::updateValue('WKWAREHOUSE_MODE_MULTICARRIER_CHOICE', 'carriers-combinations');
            Configuration::updateValue('WKWAREHOUSE_PRODUCT_NAME_SHIPMENT_PART', 0);
            Configuration::updateValue('WKWAREHOUSE_WH_NAME_SHIPMENT_PART', 1);
            Configuration::updateValue('WKWAREHOUSE_PRODUCT_NOT_ASM_GET_BEST_CARRIERS', 1);
            Configuration::updateValue('WKWAREHOUSE_ALLOWSET_WAREHOUSE', 0);
            Configuration::updateValue('WKWAREHOUSE_ALLOW_MULTI_ADDRESSES', 0);
            Configuration::updateValue('WKWAREHOUSE_DISPLAY_DELIVERIES_TIME', 0);
            Configuration::updateValue('WKWAREHOUSE_WAY_FIX_QUANTITIES', 'alignQtiesToPrestashop');
            Configuration::updateValue('WKWAREHOUSE_DISPLAY_SELECTED_WAREHOUSE', 0);
            Configuration::updateValue('WKWAREHOUSE_DISPLAY_COUNTRIES', 1);
            Configuration::updateValue('WKWAREHOUSE_ENABLE_FONTAWESOME', 1);
            Configuration::updateValue('WKWAREHOUSE_DISPLAY_LOCATION', 0);
            Configuration::updateValue('WKWAREHOUSE_LOCATION_ORDER_PAGE', 0);
            Configuration::updateValue('WKWAREHOUSE_WAREHOUSEINFOS_POSITION', 'afterCart');
            Configuration::updateValue('WKWAREHOUSE_STOCKSINFOS_ORDER_PAGE', 0);
            Configuration::updateValue('WKWAREHOUSE_POSITION_INCART', 'belowProductName');
            Configuration::updateValue('WKWAREHOUSE_STOCKPRIORITY_INC', 1);
            Configuration::updateValue('WKWAREHOUSE_STOCKPRIORITY_DEC', 1);
            Configuration::updateValue('WKWAREHOUSE_CHANGE_ORDER_WAREHOUSE', 0);
            Configuration::updateValue('WKWAREHOUSE_WAREHOUSES_INCART', 0);
            Configuration::updateValue('WKWAREHOUSE_DELIVERYTIMES_INCART', 0);
            Configuration::updateValue('WKWAREHOUSE_DISPLAY_SELECTED_LOCATION', 0);
            Configuration::updateValue('WKWAREHOUSE_LOCATIONS_INCART', 0);
            Configuration::updateValue('WKWAREHOUSE_DISPLAY_WAREHOUSE_NAME', 1);
            Configuration::updateValue('WKWAREHOUSE_DISPLAY_SELECTED_STOCK', 1);
            Configuration::updateValue('WKWAREHOUSE_DISPLAY_DELIVERYTIME', 0);
            Configuration::updateValue('WKWAREHOUSE_QUANTITIES_INCART', 0);
            Configuration::updateValue('WKWAREHOUSE_ENABLE_INCART', 0);
            Configuration::updateValue('WKWAREHOUSE_DISPLAY_COUNTRIES', 1);
            Configuration::updateValue('WKWAREHOUSE_DISPLAY_COUNTRY', 0);
            Configuration::updateValue('WKWAREHOUSE_COUNTRIES_INCART', 0);
            Configuration::updateValue('WKWAREHOUSE_DELIVERY_ADDRESS_INCART', 0);
            Configuration::updateValue('WKWAREHOUSE_SHOW_OUTOFSTOCK', 1);
            Configuration::updateValue('WKWAREHOUSE_SECURE_KEY', md5(_COOKIE_KEY_.time()));

            if ($install) {
                // Remove Added Tabs when asm is activated
                $classeNames_tabs = array(
                    'AdminStock',
                    'AdminWarehouses',
                    'AdminParentStockManagement',
                    'AdminStockMvt',
                    'AdminStockInstantState',
                    'AdminStockCover',
                    'AdminSupplyOrders',
                    'AdminStockConfiguration'
                );
                foreach ($classeNames_tabs as $classname) {
                    $tab = Tab::getInstanceFromClassName($classname);
                    if (Validate::isLoadedObject($tab)) {
                        $tab->delete();
                    }
                }
                $this->installDB();
                $this->installTabs();
            }

            if (version_compare(_PS_VERSION_, '1.7.6.0', '<')) {
                // Patch for front cart controller (make updateOperationError accessible from override)
                $fileOverride = _PS_ROOT_DIR_.'/controllers/front/CartController.php';
                if (file_exists($fileOverride)) {
                    $cartContent = Tools::file_get_contents($fileOverride);
                    $cartContent = str_replace(
                        'private $updateOperationError = array();',
                        'protected $updateOperationError = array();',
                        $cartContent
                    );
                    file_put_contents($fileOverride, $cartContent);
                }
                // Patch for PaymentModule class
                // Fix Major Prestashop Bug : when splitted orders, differents carriers but
                // the carrier name is the same in order confirmation emails!
                $fileOverride = _PS_ROOT_DIR_.'/classes/PaymentModule.php';
                if (file_exists($fileOverride)) {
                    $paymentContent = Tools::file_get_contents($fileOverride);
                    $paymentContent = str_replace(
                        array('$invoice = new Address((int) $order->id_address_invoice);', '$invoice = new Address((int)$order->id_address_invoice);'),
                        '$invoice = new Address((int)$order->id_address_invoice);
                        $carrier = $order->id_carrier ? new Carrier($order->id_carrier) : false;',
                        $paymentContent
                    );
                    file_put_contents($fileOverride, $paymentContent);
                }

				// Handle admin product page template (.twig) to avoid internal error
				$mainFolder = _PS_ROOT_DIR_.'/src/PrestaShopBundle/Resources/views/Admin/Product';
				$folderInclude = $mainFolder.'/Include';
				if (is_dir($folderInclude)) {/* Espacially for PS 1.7.3 and less versions */
					copy(
						_PS_MODULE_DIR_.$this->name.'/views/templates/form-warehouse-combination.html.twig',
						$folderInclude.'/form-warehouse-combination.html.twig'
					);
				}
				$shipping_form_path1 = $folderInclude.'/form_shipping.html.twig';
				$shipping_form_path2 = $mainFolder.'/ProductPage/Forms/form_shipping.html.twig';
				$shipping_form_path = '';
				if (file_exists($shipping_form_path1)) {
					$shipping_form_path = $shipping_form_path1;
				} else {
					$shipping_form_path = $shipping_form_path2;
				}
				if (file_exists($shipping_form_path)) {
					$content = Tools::file_get_contents($shipping_form_path);
					if (preg_match('#^(.*)\{% if asm_globally_activated and isNotVirtual and isChecked %}.*\{% endif %}[^\n]*(.*)$#s', $content, $m)) {
						$specific_before = $m[1];
						$specific_after = $m[2];
						$newContent = '';
						if ($specific_before) {
							$newContent .= $specific_before;
						}
						if ($specific_after) {
							$newContent .= $specific_after;
						}
						if (!empty($newContent)) {
							file_put_contents($shipping_form_path, $newContent);
						}
					}
				}
            }
            if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
         		$product_form_path = _PS_ROOT_DIR_.'/modules/'.$this->name.'/views/PrestaShop/Admin/Product/ProductPage/';
               rename($product_form_path.'_product.html.twig', $product_form_path.'product.html.twig');
			}
            // Sometimes, override admin controller folder is missing
            if (!is_dir(dirname(__FILE__).'/../../override/controllers/admin/')) {
                mkdir(dirname(__FILE__).'/../../override/controllers/admin/', 0755, true);
            }
            return true;
        }
    }

    public function reset()
    {
        if (!$this->uninstall(false)) {
            return false;
        }
        if (!$this->install(false)) {
            return false;
        }
        return true;
    }

    /**
     * Activate current module without installing overrides
     * @param bool $force_all If true, enable module for all shop
     * @return bool
     */
    public function enable($force_all = false)
    {
        // Retrieve all shops where the module is enabled
        $list = Shop::getContextListShopID();
        if (!$this->id || !is_array($list)) {
            return false;
        }
        $sql = 'SELECT `id_shop` FROM `' . _DB_PREFIX_ . 'module_shop`
                	WHERE `id_module` = '.(int)$this->id.(!$force_all ? ' AND `id_shop` IN(' . implode(', ', $list) . ')' : '');

        // Store the results in an array
        $items = array();
        if ($results = Db::getInstance($sql)->executeS($sql)) {
            foreach ($results as $row) {
                $items[] = $row['id_shop'];
            }
        }
        Configuration::updateValue('PS_ADVANCED_STOCK_MANAGEMENT', 1); // Activate A.S.M
        // Install overrides
        if ($this->getOverrides() != null) {
            try {
                $this->installOverrides();
            } catch (Exception $e) {
                $this->_errors[] = Context::getContext()
                ->getTranslator()
                ->trans('Unable to install override: %s', [$e->getMessage()], 'Admin.Modules.Notification');
                $this->uninstallOverrides();
                return false;
            }
        }
        // Enable module in the shop where it is not enabled yet
        foreach ($list as $id) {
            if (!in_array($id, $items)) {
                Db::getInstance()->insert('module_shop', array(
                    'id_module' => $this->id,
                    'id_shop' => $id,
                ));
            }
        }
        return true;
    }

    /**
     * Desactivate current module without uninstalling overrides
     * @param bool $force_all If true, disable module for all shop
     * @return bool
     */
    public function disable($force_all = false)
    {
        $result = true;
        if ($this->getOverrides() != null) {
            $result &= $this->uninstallOverrides();
        }
        // Disable module for all shops
        Configuration::updateValue('PS_ADVANCED_STOCK_MANAGEMENT', 0); // Disable A.S.M
        return Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'module_shop` 
             WHERE `id_module` = ' . (int) $this->id . ' '
            .(!$force_all ? ' AND `id_shop` IN('.implode(', ', Shop::getContextListShopID()).')' : '')
        );
    }

    public function fixActionCartUpdateQuantityBeforeHook()
    {
        if (!Hook::getIdByName('actionCartUpdateQuantityBefore')) {
            $hook = new Hook(); // Create new hook
            $hook->name = 'actionCartUpdateQuantityBefore';
            $hook->title = $hook->name;
            $hook->description = 'Added from Wk Warehouses Management module';
            $hook->position = true;
            $hook->save();
            $this->registerHook('actionCartUpdateQuantityBefore'); // then link to our module
        }
    }

    public function installTabs()
    {
        $id_parent = null;
        foreach ($this->my_tabs as $k => $tab) {
            $tab_name = $tab['name'];
            $obj = new Tab();
            foreach (Language::getLanguages() as $lang) {
                if (!isset($tab_name[$lang['iso_code']])) {
                    $obj->name[$lang['id_lang']] = $tab_name['en'];
                } else {
                    $obj->name[$lang['id_lang']] = $tab_name[$lang['iso_code']];
                }
            }
            $obj->class_name = $tab['className'];

            // Process Parent ID
            if ($k == 0) {// First tab
                $parent_tab = Tab::getIdFromClassName('IMPROVE');
                if (property_exists($obj, 'icon')) {
                    $obj->icon = 'home';
                }
            } else {
                $parent_tab = is_null($id_parent) ? $tab['id_parent'] : $id_parent;
            }
            $obj->id_parent = (int)$parent_tab;
            // End processing parent ID

            $disabled_controllers = array(
                'AdminWkwarehousestaskrun',
            );
            $obj->active = (in_array($obj->class_name, $disabled_controllers) ? 0 : 1);

            $obj->module = $this->name;
            if ($obj->add()) {
                if ($k == 0) {// Get the ID of the first tab that will be the parent ID of the next tabs
                    $id_parent = (int)$obj->id;
                }
            }
        }
        return true;
	}

    public function installDB()
    {
        require_once(dirname(__FILE__).'/install/install.php');

        $result = true;
        // Export already existant warehouses names to the new table
        $result &= StoreHouse::exportWarehousesLanguages();

        return $result;
    }

    public function loadSQLFile($sql_file)
    {
        $sql_content = Tools::file_get_contents($sql_file);
        $sql_content = str_replace('PREFIX_', _DB_PREFIX_, $sql_content);
        $sql_content = str_replace('_SQLENGINE_', _MYSQL_ENGINE_, $sql_content);
        $sql_requests = preg_split("/;\s*[\r\n]+/", $sql_content);
        $result = true;
        foreach ($sql_requests as $request) {
            if (!empty($request)) {
                $result &= Db::getInstance()->execute(trim($request));
            }
        }
        return $result;
    }

    public function uninstall($uninstall = true)
    {
        if (!parent::uninstall()) {
            return false;
        }
        if ($uninstall) {
            Configuration::updateValue('PS_ADVANCED_STOCK_MANAGEMENT', 0); // Reset A.S.M
            // Delete all module config. parameters
            Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'configuration` WHERE `name` LIKE "WKWAREHOUSE_%" ');
            $this->uninstallTabs();
            // Disable advanced stock management from products
            WorkshopAsm::setAdvancedStockManagement();
        }
        return true;
    }

    public function uninstallTabs()
    {
        $tabs = Tab::getCollectionFromModule($this->name);
        foreach ($tabs as $tab) {
            $tab->delete();
        }
    }

    public function hookActionBeforeCartUpdateQty($data)
    {
        return $this->hookActionCartUpdateQuantityBefore($data);
    }

    /*
    * BO Order Management: only one carrier is allowed when adding product in cart
    */
    public function hookActionCartUpdateQuantityBefore($data)
    {
        if (!$this->active || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') ||
            !isset($data['product']) || !$data['product'] instanceof Product) {
            return;
        }
        // Management from BO
        if (defined('_PS_ADMIN_DIR_')) {
            $request = $this->getAdminControllerNameAndAction();
            if (in_array($request['admin_action'], array(
                'updateQty', // adding product during creating order
                'addProductOnOrder', // adding product during editing order PS < 1.7.7.x
                'addProductAction', // adding product during creating / editing order PS >= 1.7.7.x
            ))) {
                $id_product = (int)$data['product']->id;
                $id_product_attribute = (int)$data['id_product_attribute'];
                $product_asm = (int)$data['product']->advanced_stock_management;
                $id_address_delivery = $order = null;

                $idOrder = Tools::getIsset('id_order') ? Tools::getValue('id_order') : null;
                if (is_null($idOrder) && isset($request['orderId'])) {
                    $idOrder = $request['orderId'];
                }
                if ($idOrder && in_array($request['admin_action'], array('addProductOnOrder', 'addProductAction'))) {
                    $order = new Order((int)$idOrder);
                }

                $access = false;
                if (($data['product']->hasAttributes() && $id_product_attribute) ||
                    (!$data['product']->hasAttributes() && empty($id_product_attribute))) {
                    $access = true;
                }
                
                // Test if ASM product is at least associated to one warehouse
                if ($product_asm && $access) {
                    if (empty(WorkshopAsm::getAssociatedWarehousesArray($id_product, $id_product_attribute))) {
                        $errors = $this->l('Error: this product is handled by the advanced stock management system but not yet associated with any warehouse!');
                        $this->useAjaxDieError(
                            $id_product,
                            $id_product_attribute,
                            $request['admin_action'],
                            $errors,
                            $order
                        );
                    }
                }

                $result = WarehouseStock::productIsPresentInCart(
                    $this->context->cart->id,
                    $id_product,
                    $id_product_attribute
                );
                /* is Product stored in warehouse ? */
                $id_warehouse = null;
                if ($product_asm && $result && (int)$result['id_warehouse'] > 0) {
                    $id_warehouse = (int)$result['id_warehouse'];
                }

                // BO Orders Management (EDIT): Adding new product
                // Because PS create new cart, so we need to use the original cart ID to check carrier availability
                if ($request['admin_controller'] == 'AdminOrders' &&
                    in_array($request['admin_action'], array('addProductOnOrder', 'addProductAction')) &&
                    Validate::isLoadedObject($order)) {
                    if (Tools::getIsset('add_product_warehouse')) {
                        $id_warehouse = Tools::getValue('add_product_warehouse');
                        $id_warehouse = (int)(is_array($id_warehouse) ? current($id_warehouse) : $id_warehouse);
                    }
                    $id_address_delivery = (int)$order->id_address_delivery;
                }
                if (empty($id_warehouse)) {
                    $selected_warehouse = WarehouseStock::getAvailableWarehouseAndCartQuantity(
                        $id_product,
                        $id_product_attribute,
                        $this->context->cart
                    );
					if ($selected_warehouse) {
                    	$id_warehouse = (int)$selected_warehouse['id_warehouse'];
					}
                }
                if (empty($id_address_delivery)) {
                    $id_address_delivery = $this->context->cart->id_address_delivery;
                }

                // Check carrier availability
                $carriers = WarehouseStock::getAvailableCarrierList(
					$data['product'],
					$id_warehouse,
					$id_address_delivery,
					$id_product_attribute
				);

                if (empty($carriers)) {
                    $errors = sprintf(
                        $this->l('Error: this product can not be delivered to the selected delivery address %s'),
                        (Validate::isLoadedObject($order) ? ' '.$this->l('by the selected carrier !') : '')
                    );
                    $this->useAjaxDieError($id_product, $id_product_attribute, $request['admin_action'], $errors, $order);
                }
            }
            return;
        }
    }

    public function useAjaxDieError($id_product, $id_product_attribute, $action, $errors, $order)
    {
        if (in_array($action, array('addProductOnOrder', 'addProductAction')) && Validate::isLoadedObject($order)) {
            // Remove specific price created during process
            $this->removeSpecificPrice($id_product, $id_product_attribute, $order);
            // Throw an exception (an error)
            if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
                throw new Exception($errors);
            } else {
                die(json_encode(array('error' => $errors)));
            }
        } else {
            if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
                throw new Exception($errors);
            } else {
                die(json_encode(
                    array_merge((new AdminCartsControllerCore())->ajaxReturnVars(), array('errors' => array($errors)))
                ));
            }
        }
    }

    public function removeSpecificPrice($id_product, $id_product_attribute, $order)
    {
        $initial_product_price_tax_incl = Product::getPriceStatic(
            $id_product,
            true, // use tax
            (!empty($id_product_attribute) ? (new Combination($id_product_attribute))->id : null),
            2,
            null,
            false,
            true,
            1,
            false,
            $order->id_customer,
            $this->context->cart->id,
            $order->{Configuration::get('PS_TAX_ADDRESS_TYPE', null, null, $order->id_shop)}
        );
        $price_tax_incl = Tools::getIsset('product_price_tax_incl') ? Tools::getValue('product_price_tax_incl') : Tools::getValue('price_tax_incl');
        $quantity = (int)(Tools::getIsset('product_quantity') ? Tools::getValue('product_quantity') : Tools::getValue('quantity'));

        if ($price_tax_incl != $initial_product_price_tax_incl) {
            /* be aware, $this->context->cart is regarding the new cart created specially for the new product on order */
            $specific_price = SpecificPrice::getSpecificPrice(
                $id_product,
                $this->context->cart->id_shop,
                $this->context->cart->id_currency,
                0, //id_country
                $this->context->cart->id_shop_group,
                $quantity,
                $id_product_attribute,
                $this->context->cart->id_customer,
                $this->context->cart->id
            );
            /* that mean specific price has been created */
            if ($specific_price && (float)$specific_price['price'] == (float)$initial_product_price_tax_incl) {
                /* so, remove it to avoid future error */
                $specific_price_obj = new SpecificPrice((int)$specific_price['id_specific_price']);
                $specific_price_obj->delete();
            }
        }
    }

    public function hookAddProduct($params)
    {
        $this->_clearCache('*');
    }

    public function hookUpdateProduct($params)
    {
        if (!isset($params['product'])) {
            return;
        }
        $this->_clearCache('*');
    }

    public function hookDeleteProduct($params)
    {
        $this->_clearCache('*');
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        if (!$this->active || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            return;
        }
        // Need to select a store
        if (Shop::isFeatureActive() &&
            in_array($this->context->shop->getContext(), array(Shop::CONTEXT_GROUP, Shop::CONTEXT_ALL))) {
            return $this->displayError(
                $this->l('You are in multishop environment. To use the module, you must select a shop.')
            );
        } else {
            $id_product = (int)$params['id_product'];
            $obj = new Product($id_product, false);

            if (Validate::isLoadedObject($obj)) {
                $this->_clearCache('*');
                return $this->initAdminProductTabWarehouses($obj);
            } else {
                return $this->displayError($this->l('You must save this product before adding warehouses.'));
            }
        }
    }

    /*
    * BackOffice Product Settings In Product Tab
    */
    private function initAdminProductTabWarehouses($obj)
    {
        $this->context->smarty->assign(array(
            'prod' => $obj,
            'link' => new Link(),
            'use_asm' => Configuration::get('WKWAREHOUSE_USE_ASM_NEW_PRODUCT'),
            'isPack' => !empty($obj->id) ? Pack::isPack($obj->id) : false,
        ));
        return $this->display(__FILE__, 'views/templates/admin/product_tab.tpl');
    }

    /*
    * How to get id_product_attribute on product page
    * https://stackoverflow.com/questions/56061282/prestashop-1-7-how-to-get-id-product-attribute-on-product-page
    */
    public function hookDisplayProductAdditionalInfo($params)
    {
        if (!$this->active || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            return;
        }
        $this->page_name = Dispatcher::getInstance()->getController();
        if ($this->page_name == 'product') {
            $product = new Product((int)$params['product']['id'], false);
			if (Validate::isLoadedObject($product) && $product->advanced_stock_management) {
				$this->context->smarty->assign(array(
					'id_product_attribute' => $params['product']['id_product_attribute'],
				));
				return $this->fetch(
					'module:wkwarehouses/views/templates/hook/product_combination.tpl'
				);
			}
        }
    }

    private function setProductProperties($product)
    {
        $id_product_attribute = (!empty($product['id_product_attribute']) ? (int)$product['id_product_attribute'] : null);
        if (Combination::isFeatureActive() && $id_product_attribute === null) {
            if (isset($product['cache_default_attribute']) && !empty($product['cache_default_attribute'])) {
                $id_product_attribute = $product['cache_default_attribute'];
            } else {
                $id_product_attribute = Product::getDefaultAttribute(
                    $product['id_product'],
                    Product::isAvailableWhenOutOfStock($product['out_of_stock'])
                );
            }
        }
        return $id_product_attribute;
    }

    /***********************************************/
    /***** Fix delivery address if cart empty *****/
    /**********************************************/
    public function hookActionGetProductPropertiesBefore($params)
    {
		// From FO
        if ($this->active && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && !defined('_PS_ADMIN_DIR_')) {
            $this->page_name = Dispatcher::getInstance()->getController();
			// IF Product page and cart loaded and valid
            if ($this->page_name == 'product' && Tools::getIsset('id_product') && Tools::getValue('id_product') &&
                ($params['product']['id_product'] == Tools::getValue('id_product')) &&
                Validate::isLoadedObject($this->context->cart) && $this->context->cart->id_address_delivery) {
                // Get all delivery addresses
                $addresses = $this->context->customer->getAddresses($this->context->language->id);

				$hasProducts = method_exists('Cart', 'hasProducts') ? $this->context->cart->hasProducts() : $this->context->cart->hasRealProducts();
                if (!$hasProducts && count($addresses) > 1) {
                    // Instanciate product
                    $product = new Product((int)Tools::getValue('id_product'), false);
                    if (Validate::isLoadedObject($product)) {
                        $id_product_attribute = (int)$this->setProductProperties($params['product']);

                        $id_warehouse = null;
                        if ($product->advanced_stock_management &&
                            !empty(WorkshopAsm::getAssociatedWarehousesArray($product->id, $id_product_attribute))) {
                            // Get the warehouse with enough quantity
                            $id_warehouse = WorkshopAsm::findWarehousePriority(array(), true, $product->id, $id_product_attribute, 'desc');
                        }
                        // Begin to checkup carriers with the default cart delivery address
                        $carriers = WarehouseStock::getAvailableCarrierList(
                            $product,
                            $id_warehouse,
                            $this->context->cart->id_address_delivery,
							$id_product_attribute
                        );
                        if (count($carriers) == 0) {
                            foreach ($addresses as $address) {
                                $carriers = WarehouseStock::getAvailableCarrierList(
                                    $product,
                                    $id_warehouse,
                                    $address['id_address'],
									$id_product_attribute
                                );
                                if (count($carriers) && $this->context->cart->id_address_delivery != $address['id_address']) {
                                    $this->context->cart->id_address_delivery = $address['id_address'];
                                    $this->context->cart->id_address_invoice = $address['id_address'];
                                    $this->context->cart->save();
                                    /*WarehouseStock::updateCartDeliveryAddress(
                                        $this->context->cart->id,
                                        $address['id_address'],
                                        true
                                    );*/
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /*
    * - This hook is very important as that it allows to adjust/override the product/combination quantity
    *   according to the right warehouse quantity
    * - Executed only from product page or products listing page
    */
    public function hookActionGetProductPropertiesAfter($params)
    {
        if ($this->active && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && !defined('_PS_ADMIN_DIR_')) {
            $this->page_name = Dispatcher::getInstance()->getController();
            $inListing = in_array($this->page_name, $this->listing_pages);
            if (($this->page_name == 'product' && Tools::getIsset('id_product') && $params['product']['id_product'] == Tools::getValue('id_product')) ||// from product page
                ($inListing && $params['product']['id_product'] > 0)) {// From listing pages
                $id_product = Tools::getIsset('id_product') ? Tools::getValue('id_product') : $params['product']['id_product'];

                $product = new Product((int)$id_product, false);
                if (Validate::isLoadedObject($product)) {
                    $id_product_attribute = (int)$this->setProductProperties($params['product']);
                    if ($product->advanced_stock_management) {
                        /*
                         * iF Product is handled by A.S.M but not store in any warehouse yet or is Pack :
                         *  - Make product as out-of-stock
                         *  - Display message to customer in product page: Not available in any warehouse
                        */
                        if (empty(WorkshopAsm::getAssociatedWarehousesArray($product->id, $id_product_attribute)) ||
                            Pack::isPack($product->id)) {
                            $params['product']['quantity'] = 0;
                            $params['product']['quantity_all_versions'] = 0;
                            $params['product']['allow_oosp'] = 0; // Force disabling adding product to cart
                            $params['product']['cart_quantity'] = 0;
                            $params['product']['quantity_wanted'] = 0;
                            $params['product']['quantity_available'] = 0;
                            $params['product']['available_for_order'] = 0;
                        } else {
                            $selected_warehouse = WarehouseStock::getAvailableWarehouseAndCartQuantity(
                                $product->id,
                                $id_product_attribute
                            );
                            if ($selected_warehouse && $selected_warehouse['id_warehouse'] > 0) {
                                $has_carriers = $inListing ? true : $selected_warehouse['has_carriers'];
								$available_quantity = $selected_warehouse['quantity'];
            					$allow_oosp = Product::isAvailableWhenOutOfStock(StockAvailable::outOfStock($id_product));

                        		$cart = $this->context->cart;
                        		if (Validate::isLoadedObject($cart) && $cart->nbProducts() &&
									!$allow_oosp && ($cart_product = $cart->containsProduct($product->id, $id_product_attribute))) {
									$available_quantity -= $cart_product['quantity'];
								}
								if ((!$allow_oosp && ($available_quantity <= 0 || !$has_carriers)) || !$product->available_for_order) {
									$params['product']['quantity'] = 0;
									$params['product']['quantity_all_versions'] = 0;
									$params['product']['available_for_order'] = 0;
								} else {
									$params['product']['quantity'] = $has_carriers ? $available_quantity : 0;
									$params['product']['quantity_all_versions'] = $has_carriers ? $available_quantity : 0;
									$params['product']['available_for_order'] = 1;
									$params['product']['id_warehouse'] = $selected_warehouse['id_warehouse'];
								}
                            } else {
								$params['product']['quantity'] = 0;
								$params['product']['quantity_all_versions'] = 0;
								$params['product']['available_for_order'] = 0;
							}
                        }
                    } else {/* Not A.S.M */
                        if (!$inListing && !Configuration::get('WKWAREHOUSE_ALLOW_MULTICARRIER_CART')) {
                            $product_carriers = WarehouseStock::getCarriersByCustomerAddresses($product, $id_product_attribute);
                            if (!count($product_carriers['available_carriers'])) {
                                $params['product']['quantity'] = 0;
                                $params['product']['quantity_all_versions'] = 0;
								$params['product']['cart_quantity'] = 0;
								$params['product']['quantity_wanted'] = 0;
								$params['product']['quantity_available'] = 0;
								$params['product']['available_for_order'] = 0;
                            }
                        }
                    }
                }
            }
        }
    }

	/*
	* If the native Prestashop embedded attributes is removed by a PRICK developer theme
	* generate our own to avoid product attribute selection problem.
	* product-details is found in catalog/_partials/product-details.tpl
	* Fix also Creative Elements module issues
	*/
    public function hookDisplayProductPriceBlock($params)
    {
        if (!$this->active || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            return;
        }
		if (Tools::getIsset('id_product') && Tools::getValue('id_product') &&
			isset($params['product']) && $params['product'] && $params['type'] == 'before_price' &&
			class_exists('PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductLazyArray') &&
			method_exists('PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductLazyArray', 'getEmbeddedAttributes')) {
			$embedded_array = $params['product']->getEmbeddedAttributes();
			$this->context->smarty->assign(array(
				'embedded_array' => $embedded_array,
			));
			return $this->display(__FILE__, '/product_price_block.tpl');
		}
	}

    public function hookDisplayHeader()
    {
        if (!$this->active || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            return;
        }

        $allow_multicarriers_cart = (int)Configuration::get('WKWAREHOUSE_ALLOW_MULTICARRIER_CART');
        $this->page_name = Dispatcher::getInstance()->getController();
        $js_version = 'v='.time();
        // If product page
        if ($this->page_name == 'product' && Configuration::get('PS_STOCK_MANAGEMENT') &&
            Tools::getIsset('id_product') && Tools::getValue('id_product')) {
            $allow_multiwarehouses_cart = (int)Configuration::get('WKWAREHOUSE_ALLOW_MULTIWH_CART');
            $allow_set_warehouse = (int)Configuration::get('WKWAREHOUSE_ALLOWSET_WAREHOUSE');
            $display_best_warehouse = Configuration::get('WKWAREHOUSE_DISPLAY_SELECTED_WAREHOUSE');
			$this->context->controller->registerJavascript(
				'module-'.$this->name.'-common',
				'modules/'.$this->name.'/views/js/common.min.js',
				array('version' => $js_version) // prioriy hight to load always after all others scripts
			);

            $id_product = (int)Tools::getValue('id_product');
            $product = new Product($id_product, false);

            if (Validate::isLoadedObject($product)) {
                $this->context->controller->addJqueryPlugin('fancybox');
                if (!$product->advanced_stock_management) {
                    if (!$allow_multicarriers_cart) {
						$this->context->controller->registerJavascript(
							'module-'.$this->name.'-product-not-asm',
							'modules/'.$this->name.'/views/js/product-not-asm.min.js',
							array('version' => $js_version)
						);
                        return ($this->display(__FILE__, '/product_header.tpl'));
                    }
                } else {
                    if (Configuration::get('WKWAREHOUSE_WAREHOUSEINFOS_POSITION') != 'none' ||
                        !$allow_multicarriers_cart || !$allow_multiwarehouses_cart || $display_best_warehouse || $allow_set_warehouse) {
						$this->context->controller->registerJavascript(
							'module-'.$this->name.'-product',
							'modules/'.$this->name.'/views/js/product.min.js',
							array('version' => $js_version)
						);

                        // Load JS & CSS files
                        if (Configuration::get('WKWAREHOUSE_ENABLE_FONTAWESOME')) {// Font Awesome 5.11.2
                            $this->context->controller->addCSS($this->_path.'views/css/fontawesome.css', 'all');
                        }
                        $this->context->controller->addCSS($this->_path.'views/css/solid.css', 'all');
                        $this->context->controller->addCSS($this->_path.'views/css/product.css', 'all');

						$warehouses_infos = WarehouseStock::warehousesDataOnProductPage($id_product);
						WarehouseStock::takeOffOutOfStockWarehouses($id_product, $warehouses_infos);
						WarehouseStock::takeOffDisabledWarehouses($warehouses_infos);

						$this->context->smarty->assign('product_stocks_list', $warehouses_infos);
						$this->setCommonSmartyVarsProductPage();

                        $this->context->smarty->assign('module_dir', $this->_path);
                        return $this->display(__FILE__, '/product_header.tpl');
                    }
                }
            }
        }

        // On product list pages
        if (in_array($this->page_name, $this->listing_pages)) {
            $this->context->controller->addJqueryPlugin('fancybox');
            $this->context->controller->registerJavascript(
                'module-'.$this->name.'-product-list',
                'modules/'.$this->name.'/views/js/product_list.min.js',
                array('position' => 'bottom', 'priority' => 99999999) // prioriy hight to load always after all others scripts
            );
            Media::addJsDefL('process_cart_url', $this->context->link->getModuleLink($this->name, 'processactions'));
            Media::addJsDefL('txt_ok', $this->l('Ok'));
        }

        // On cart page
        if ($this->page_name == 'cart' &&
			WarehouseStock::getNumberOfAsmProductsInCart($this->context->cart->id) > 0) {
			$this->context->controller->registerJavascript(
				'module-'.$this->name.'-cart',
				'modules/'.$this->name.'/views/js/cart.min.js',
				array('version' => $js_version)
			);
            /* Check up for every product if it can be delivered to the customer address */
            if (!Configuration::get('WKWAREHOUSE_ALLOW_MULTI_ADDRESSES') &&
                $this->context->cart->id_address_delivery) {
                $this->context->smarty->assign(array(
                    'delivery_address' => $this->getCountryName($this->context->cart->id_address_delivery),
                    'carriers_restrictions' => $this->checkDeliveriesCustomerAddressOnCartListing(),
                ));
            }
            /* Check up if there are multi-warehouses products in cart while the multi-warehouses is disabled in config page */
			if (!Configuration::get('WKWAREHOUSE_ALLOW_MULTIWH_CART') && $this->context->cart->id) {
				if (WarehouseStock::getNumberOfAsmProductsInCart($this->context->cart->id, true) > 1) {
					Media::addJsDef(array(
						'warehouses_restrictions' => 1,
					));
            		Media::addJsDefL('txt_multi_warehouses_not_allowed', $this->l('The multi-warehouses is not allowed within the cart!'));
				}
			}
            /* Display warehouses informations for each A.S.M product */
            if (Configuration::get('WKWAREHOUSE_ENABLE_INCART')) {
                $result = $this->initWarehousesInformationsOnCartListing();
                if ($result['asmProductsInCart'] && count($result['warehousesInfos'])) {
                    $this->context->smarty->assign('warehouses_cart_details', $result['warehousesInfos']);
                }
            }
            $this->context->smarty->assign(array(
                'link' => new Link(),
                'deliver_address_incart' => Configuration::get('WKWAREHOUSE_DELIVERY_ADDRESS_INCART') && !Configuration::get('WKWAREHOUSE_ALLOW_MULTI_ADDRESSES'),
            ));
            return $this->display($this->_path, 'views/templates/hook/cart_products.tpl');
        }

        // Checkout page and multi-shipping (multi-carriers) option enabled
        if ($this->page_name == 'order' && $allow_multicarriers_cart &&
            WarehouseStock::isMultiShipping($this->context->cart)) {// <= is multishipping?
			$this->context->controller->registerJavascript(
				'module-'.$this->name.'-order',
				'modules/'.$this->name.'/views/js/order.min.js',
				array('version' => $js_version, 'position' => 'bottom', 'priority' => 99999999)
			);
            $this->context->controller->addJqueryPlugin('fancybox');
            $this->context->controller->addCSS($this->_path.'views/css/wkwarehouses.css', 'all');

            /* Check delivery address of each product in cart and try to fix it if possible */
            WarehouseStock::assignRightDeliveryAddressToEachProductInCart($this->context->cart);

            if (class_exists('PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter')) {
                $presenter = new PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter();
            } else {
                $presenter = new PrestaShop\PrestaShop\Adapter\Cart\CartPresenter();
            }
            if (class_exists('PrestaShop\PrestaShop\Adapter\Presenter\Object\ObjectPresenter')) {
                $object_presenter = new PrestaShop\PrestaShop\Adapter\Presenter\Object\ObjectPresenter();
            } else {
                $object_presenter = new PrestaShop\PrestaShop\Adapter\ObjectPresenter();
            }

            $presented_cart = $presenter->present($this->context->cart);
            $id_lang = (int)$this->context->language->id;

            if (count($presented_cart['products']) > 0) {
                $cart_collection = array();

                /***** Generate delivery addresses list related to each product ******/
                /***********************************************************************/
                if (Configuration::get('WKWAREHOUSE_ALLOW_MULTI_ADDRESSES')) {
                    foreach ($presented_cart['products'] as $cart_line) {
                        $id_product = (int)$cart_line['id_product'];
                        $id_product_attribute = (int)$cart_line['id_product_attribute'];
                        $id_address_delivery = (int)$cart_line['id_address_delivery'];
                        $product = new Product($id_product, false);

                        $product_tmp = array();
                        $product_tmp['id_product'] = $id_product;
                        $product_tmp['id_product_attribute'] = $id_product_attribute;
                        $product_tmp['id_address_delivery'] = $id_address_delivery;
                        $format = array('cart', 'default');
                        $product_tmp['image'] = $cart_line['cover']['bySize'][$format[0].'_'.$format[1]]['url'];
                        $product_tmp['url'] = $cart_line['url'];
                        $product_tmp['has_discount'] = $cart_line['has_discount'];
                        $product_tmp['name'] = $cart_line['name'];
                        $product_tmp['discount_type'] = $cart_line['discount_type'];
                        $product_tmp['regular_price'] = $cart_line['regular_price'];
                        $product_tmp['discount_percentage_absolute'] = $cart_line['discount_percentage_absolute'];
                        $product_tmp['discount_to_display'] = $cart_line['discount_to_display'];
                        $product_tmp['price'] = $cart_line['price'];
                        $product_tmp['unit_price_full'] = $cart_line['unit_price_full'];
                        $attributes = array();
                        foreach ($cart_line['attributes'] as $k => $attribute) {
                            array_push($attributes, $k.': '.$attribute);
                        }
                        $product_tmp['attributes'] = $attributes;

                        /* Get all customer delivery addresses */
                        $addresses = $this->context->customer->getAddresses($id_lang);

                        $id_warehouse = 0;
                        $result = WarehouseStock::productIsPresentInCart(
                            $this->context->cart->id,
                            $id_product,
                            $id_product_attribute
                        );
                        if ($result && $result['id_warehouse'] > 0 && $product->advanced_stock_management) {
                            $id_warehouse = (int)$result['id_warehouse'];
                            /* Look for the customer addresses that match with the warehouse */
                            $warehouse = new StoreHouse($id_warehouse, $id_lang);

                            if (Validate::isLoadedObject($warehouse) && Address::isCountryActiveById($warehouse->id_address)) {
                                $product_tmp['warehouse_name'] = $warehouse->name;

                                $wa = Address::getCountryAndState($warehouse->id_address);
                                $warehouse_country = new Country($wa['id_country'], $id_lang);
    
                                /* Add warehouse country informations */
                                $product_tmp['warehouse_country_name'] = $warehouse_country->name;
                                /* Get the warehouse zone */
                                $id_zone = $warehouse_country->id_zone;
                            }
                        } else {
                            /* Handled by Normal stock management */
                            $carriers_list = WarehouseStock::getAvailableCarrierList(
								$product,
								null,
								$id_address_delivery,
								$id_product_attribute
							);
                            if (empty($carriers_list)) {/* product can not be delivered to that delivery address */
                                $id_zone = 0;
                                if (count($product->getCarriers())) {
                                    // Get the best carrier according to its assigned zones && propose it to user
                                    $best_carrier = WarehouseStock::getBestAvailableProductCarrier($product->id);
                                    if ($best_carrier) {
                                        $id_zone = $best_carrier['id_zone'];
                                        // Get all countries
                                        if (Configuration::get('PS_RESTRICT_DELIVERED_COUNTRIES')) {
                                            $availableCountries = Carrier::getDeliveredCountries($id_lang, true, true);
                                        } else {
                                            $availableCountries = Country::getCountries($id_lang, true);
                                        }
                                        $countries_by_zone = array();
                                        foreach ($availableCountries as $country) {
                                            $countryObject = new Country($country['id_country'], $id_lang);
                                            if ($countryObject->id_zone == $id_zone) {
                                                $countries_by_zone[] = $countryObject->name;
                                            }
                                        }
                                        $product_tmp['best_zone'] = count($countries_by_zone) ? implode(', ', $countries_by_zone) : '';
                                    }
                                }
                            } else {
                                $wa = Address::getCountryAndState($id_address_delivery);
                                $id_zone = (new Country($wa['id_country']))->id_zone;
                            }
                        }

                        /* Available delivery addresses for each product */
                        foreach ($addresses as $k => $address) {
                            $id_address_zone = Address::getZoneById((int)$address['id_address']);
                            if (isset($id_zone) && $id_address_zone != $id_zone) {
                                unset($addresses[$k]);
                            }
                        }
                        /* Prepare the default delivery selected address */
                        foreach ($addresses as &$addr) {
                            $addr['selected'] = ($addr['id_address'] == $id_address_delivery ? 1 : 0);
                        }
                        $product_tmp['address_list'] = $addresses;
                        $product_tmp['id_warehouse'] = (int)$id_warehouse;
                        array_push($cart_collection, $product_tmp);
                    }
                }

                /***** Shipping methods according to the available delivery addresses in cart ****/
                /*************************************************************************************/
                $include_taxes = !Product::getTaxCalculationMethod((int)$this->context->cart->id_customer) && (int)Configuration::get('PS_TAX');
                $display_taxes_label = (Configuration::get('PS_TAX') && !Configuration::get('AEUC_LABEL_TAX_INC_EXC'));
				// Get the default selected carrier for each delivery address
				$selected_delivery_option = $this->context->cart->getDeliveryOption(null, false, false);
				if (Configuration::get('WKWAREHOUSE_MODE_MULTICARRIER_CHOICE') == 'carriers-combinations') {
					$delivery_option_list = $this->context->cart->getDeliveryOptionList(); // it's overrided
				} else {
					// group packages by warehouse, for each warehouse, look for its carriers
					$delivery_options_available = $this->context->cart->getMyDeliveryOptionList();
					/* Example of generated delivery_option_list grouped by warehouse
					Array
					(
						[10] => Array
							(
								[3 (warehouse ID)] => Array
									(
										[1,3, (carriers of the warehouse)] => Array
											(
												[carrier_list] => Array
													(
														[1] => Array
															(
																[price_with_tax] => 0
																[price_without_tax] => 0
																[logo] => 
															)
														[3] => Array
															(
																[price_with_tax] => 5
																[price_without_tax] => 5
																[logo] => 
															)
													)
												[unique_carrier] => 
											)
									)
								[1 (warehouse ID)] => Array
									(
										[6,(carriers of the warehouse)] => Array
											(
												[carrier_list] => Array
													(
														[6] => Array
															(
																[price_with_tax] => 7
																[price_without_tax] => 7
																[logo] => /ps/ps812/img/s/6.jpg
															)
					
													)
												[unique_carrier] => 1
											)
									)
							)
					)
				}*/
				}

				// Check if there is available carrier(s)
				if (count($selected_delivery_option)) {
					$carriers_in_cart = array();
					foreach ($selected_delivery_option as $delivery_option) {
						$carriers_in_cart = array_merge($carriers_in_cart, array_filter(explode(',', $delivery_option)));
					}
					/* If no carrier */
					if (empty($carriers_in_cart)) {
						$delivery_option_list = $selected_delivery_option = array();
					}
				}

                // Generate new delivery options list (just for display),
				// March 2024: If we allow choosing one carrier among a list of carriers by warehouse
				if (Configuration::get('WKWAREHOUSE_MODE_MULTICARRIER_CHOICE') == 'carriers-warehouses') {
					// For each warehouse, look for its carriers options :
					$warehouses_names = array();
					foreach ($delivery_options_available as $id_address => $packages) {
						$selected_carriers = array();
						if (isset($selected_delivery_option[(int)$id_address])) {
							$selected_carriers = array_filter(explode(',', $selected_delivery_option[(int)$id_address]));
						}
						$d = 0; // index to look for the default carrier in $selected_carriers for each $delivery_option
						foreach ($packages as $id_warehouse_key => $delivery_option) {
							foreach ($delivery_option as $key => $value) {
								foreach ($value['carrier_list'] as $id_carrier => &$data) {
                        			$carrier_instance = array_merge($data, $object_presenter->present(new Carrier($id_carrier)));
									// is it default?
									$delivery_options_available[$id_address][$id_warehouse_key][$key]['carrier_list'][$id_carrier]['selected'] = 0;
									if ($id_carrier == $selected_carriers[$d]) {
										$delivery_options_available[$id_address][$id_warehouse_key][$key]['carrier_list'][$id_carrier]['selected'] = 1;
									}
									// logo
									if (file_exists(_PS_SHIP_IMG_DIR_.$id_carrier.'.jpg')) {
										$delivery_options_available[$id_address][$id_warehouse_key][$key]['carrier_list'][$id_carrier]['logo'] = _THEME_SHIP_DIR_.$id_carrier.'.jpg';
									} else {
										$delivery_options_available[$id_address][$id_warehouse_key][$key]['carrier_list'][$id_carrier]['logo'] = false;
									}
									// name
									$delivery_options_available[$id_address][$id_warehouse_key][$key]['carrier_list'][$id_carrier]['name'] = $carrier_instance['name'];
									// delay
									$delivery_options_available[$id_address][$id_warehouse_key][$key]['carrier_list'][$id_carrier]['delay'] = $carrier_instance['delay'][$id_lang];
									// price
									if ($this->isFreeShipping($this->context->cart, $carrier_instance)) {
										$price = $this->trans('Free', array(), 'Shop.Theme.Checkout');
									} else {
										if ($include_taxes) {
											if ($display_taxes_label) {
												$price = $this->trans(
													'%price% tax incl.',
													array('%price%' => (new PriceFormatter())->format($carrier_instance['price_with_tax'])),
													'Shop.Theme.Checkout'
												);
											}
										} else {
											if ($display_taxes_label) {
												$price = $this->trans(
													'%price% tax excl.',
													array('%price%' => (new PriceFormatter())->format($carrier_instance['price_without_tax'])),
													'Shop.Theme.Checkout'
												);
											}
										}
									}
									$delivery_options_available[$id_address][$id_warehouse_key][$key]['carrier_list'][$id_carrier]['price'] = $price;
									// extra content: If carrier related to a module, check for additionnal data to display
									$delivery_options_available[$id_address][$id_warehouse_key][$key]['carrier_list'][$id_carrier]['extraContent'] = '';
									if ($carrier_instance['is_module']) {
										if ($moduleId = Module::getModuleIdByName($carrier['external_module_name'])) {
                							$carrier_instance['instance'] = new Carrier($id_carrier); // add carrier object needed by the hook (product_list is missing! under test)
											$delivery_options_available[$id_address][$id_warehouse_key][$key]['carrier_list'][$id_carrier]['extraContent'] = Hook::exec(
												'displayCarrierExtraContent',
												array('carrier' => $carrier_instance),
												$moduleId
											);
										}
									}
									// collect warehouses names
									$id_warehouse = (int)rtrim($id_warehouse_key,',');
									$warehouses_names[$id_warehouse] = (new StoreHouse($id_warehouse, $id_lang))->name;
								}
								$d++;
							}
						}
					}
					Media::addJsDef(array(
						'warehouses_names' => $warehouses_names,
					));
				} else {
                // Generate new delivery options list (just for display)
				// For each package, get the best carrier (best price, range, weight, etc.)
                	$delivery_options_available = $methods_shipping_collection = array();
					if (isset($delivery_option_list) && count($delivery_option_list)) {
						foreach ($delivery_option_list as $id_address_delivery => $by_address) {
							if (isset($delivery_option_list[$id_address_delivery])) {
								$carriers_available = array();
	
								$package_multi_carriers = false;
								foreach ($by_address as $id_carriers_list => $carriers_list) {
									// IF some products must be delivered to the same address
									// but not delivered by the same carrier (each product has its own carrier => no intersection)
									if (count(array_filter(explode(',', $id_carriers_list))) > 1) {
										$package_multi_carriers = true;
									}
									foreach ($carriers_list as $carriers) {
										// iF we're processing carrier_list index from array
										if (is_array($carriers)) {
											/* default carrier in delivery_option */
											$selected_carrier = 0;
											if (isset($selected_delivery_option[(int)$id_address_delivery])) {
												$selected_carrier = $selected_delivery_option[(int)$id_address_delivery];
											}
											/* collect carriers names, delays, logos before */
											if ($package_multi_carriers) {
												$carriers_table = array();
												foreach ($carriers as $id_carrier => $carrier) {
													if ($id_carrier) {
														$carrier = array_merge($carrier, $object_presenter->present(new Carrier($id_carrier)));

														// Warehouse collection to be displayed below carrier name
														$warehouses_names = array();
														$before_name = '';
														$product_list = $carrier['product_list'];
														$show_pn = (int)Configuration::get('WKWAREHOUSE_PRODUCT_NAME_SHIPMENT_PART');
														$show_wn = (int)Configuration::get('WKWAREHOUSE_WH_NAME_SHIPMENT_PART');
														if ($show_pn || $show_wn) {
															if (count($product_list) == 1) {
																$prod = current($carrier['product_list']);
																if (!empty(current($prod['warehouse_list']))) {
																	$warehouses_names[] = ($show_pn ? '- '.$prod['name'].' ' : '').(
																		$show_wn ? '('.(new StoreHouse(current($prod['warehouse_list']), $id_lang))->name.')' : ''
																	);
																} else {
																	$warehouses_names[] = ($show_pn ? '- '.$prod['name'] : '');
																}
															} else {
																/* despite of knowing that it can not be more than one warehouse, but do collect for security */
																foreach ($product_list as $prod) {
																	$id_warehouse_carrier = current($prod['warehouse_list']);
																	if (!empty($id_warehouse_carrier)) {
																		$warehouses_names[] = ($show_pn ? '- '.$prod['name'].' ' : '').(
																			$show_wn ? '('.(new StoreHouse($id_warehouse_carrier, $id_lang))->name.')' : ''
																		);
																	} else {
																		$warehouses_names[] = ($show_pn ? '- '.$prod['name'] : '');
																	}
																}
																if (!empty($warehouses_names) && count($warehouses_names) != count($product_list)) {
																	$before_name = $this->l('Some products are delivered from').' ';
																}
															}
															$warehouses_names = array_filter($warehouses_names);
														}
														$extraContent = '';
														if ($carrier['is_module']) {
															if ($moduleId = Module::getModuleIdByName($carrier['external_module_name'])) {
																$extraContent = Hook::exec('displayCarrierExtraContent', array('carrier' => $carrier), $moduleId);
															}
														}
														$carriers_table[] = array(
															'name' => $carrier['name'].' ('.(new PriceFormatter())->format($carrier['price_with_tax']).')',
															'delay' => $carrier['delay'][$id_lang],
															'logo' => $carrier['logo'],
															'warehouse_name' => !empty($warehouses_names) && count($warehouses_names) ? $before_name.implode('<br />', $warehouses_names) : '',
															'extraContent' => $extraContent,
														);
													}
												}
											}
											/* loop carriers */
											foreach ($carriers as $id_carrier => $carrier) {
												if ($id_carrier) {
													$carrier = array_merge($carrier, $object_presenter->present($carrier['instance']));
													$delay = $carrier['delay'][$id_lang];
													unset($carrier['instance'], $carrier['delay']);
													// delay
													$carrier['delay'] = $delay;
													// price
													if ($this->isFreeShipping($this->context->cart, $carriers_list)) {
														$carrier['price'] = $this->trans('Free', array(), 'Shop.Theme.Checkout');
													} else {
														if ($include_taxes) {
															$carrier['price'] = (new PriceFormatter())->format($carriers_list['total_price_with_tax']);
															if ($display_taxes_label) {
																$carrier['price'] = $this->trans(
																	'%price% tax incl.',
																	array('%price%' => $carrier['price']),
																	'Shop.Theme.Checkout'
																);
															}
														} else {
															$carrier['price'] = (new PriceFormatter())->format($carriers_list['total_price_without_tax']);
															if ($display_taxes_label) {
																$carrier['price'] = $this->trans(
																	'%price% tax excl.',
																	array('%price%' => $carrier['price']),
																	'Shop.Theme.Checkout'
																);
															}
														}
													}
													// label
													if (count($carriers) > 1) {
														$carrier['label'] = $carrier['price'];
													} else {
														$carrier['label'] = $carrier['name'].' - '.$carrier['delay'].' - '.$carrier['price'];
													}
													// If carrier related to a module, check for additionnal data to display
													$carrier['extraContent'] = '';
													if (!$package_multi_carriers) {
														if ($carrier['is_module']) {
															if ($moduleId = Module::getModuleIdByName($carrier['external_module_name'])) {
																$carrier['extraContent'] = Hook::exec('displayCarrierExtraContent', array('carrier' => $carrier), $moduleId);
															}
														}
													}
													// Which one has to be selected by default
													$carrier['selected'] = 0;
													if ($selected_carrier == $id_carriers_list) {
														$carrier['selected'] = 1;
														array_push($methods_shipping_collection, $carrier);
													}
													if ($package_multi_carriers) {
														if (isset($carriers_table) && count($carriers_table)) {
															$carrier['carriers_table'] = $carriers_table;
														}
													}
													// IF products being delivered to the same address but from different carriers
													$carriers_available[$id_carriers_list] = $carrier;
												}
											}
										}
									}
								}
								$delivery_options_available[$id_address_delivery] = $carriers_available;
							}
						}
					}
				}
                // IF "Enable final summary" is enabled from "Order Settings" preferences page
                if (Configuration::get('PS_FINAL_SUMMARY_ENABLED') && count($selected_delivery_option) >= 1 &&
					isset($methods_shipping_collection) && count($methods_shipping_collection)) {
                    Media::addJsDef(array(
                        'methods_shipping_collection' => $methods_shipping_collection,
                    ));
                }

                $link = new \Link();
                // For delivery addresses checkout tab
                Media::addJsDefL('txt_delivery_addresses', $this->l('Delivery addresses'));
                Media::addJsDefL('txt_choose_addresses', $this->l('Ship to multiple addresses'));
                Media::addJsDefL('txt_warehouse', $this->l('Warehouse'));
                Media::addJsDefL(
					'txt_incomplete_addresses',
					$this->l('Delivery addresses selections are required! May be you need to create new delivery address.')
				);
                Media::addJsDefL('txt_incomplete_carriers', $this->l('Carriers selections are required!'));
                Media::addJsDefL('txt_no_carrier', $this->l('No carriers are available for the selected address!'));
                // For shipping method checkout tab
                Media::addJsDefL('txt_choose_shipping_adress', $this->l('Choose the shipping option for this address:'));
                Media::addJsDefL('txt_choose_shipping', $this->l('Choose the shipping option'));
                Media::addJsDefL('txt_countries_zone', $this->l('Delivery Countries'));
                Media::addJsDefL('txt_country_zone', $this->l('Delivery Country'));
                Media::addJsDefL('txt_delivery_where', $this->l('This product can be delivered to:'));
                Media::addJsDefL('txt_products_not_asm', $this->l('Web products'));
                // Common
                Media::addJsDefL('txt_ok', $this->l('Ok'));
                Media::addJsDef(array(
                    // For delivery addresses checkout tab
                    'mode_multi_carriers_choice' => Configuration::get('WKWAREHOUSE_MODE_MULTICARRIER_CHOICE'),
                    'cart_wkwarehouses_url' => $link->getModuleLink($this->name, 'processactions'),
                    // For shipping method checkout tab
                    'delivery_option' => current($selected_delivery_option),
					'delivery_option_list' => $delivery_options_available,
					'address_collection' => $this->context->cart->getAddressCollection(),
                ));
				if (Configuration::get('WKWAREHOUSE_ALLOW_MULTI_ADDRESSES')) {
					Media::addJsDef(array(
						'cart_collection' => $cart_collection,
						'delivery_cart_id' => $this->context->cart->id_address_delivery,
					));
				}
            }
        }
        // Check always if multi-shipping, if that's so set id_delivery_address to 0
        WarehouseStock::isMultiShipping($this->context->cart);
    }

    private function isFreeShipping($cart, array $carrier)
    {
        $free_shipping = false;

        if ($carrier['is_free']) {
            $free_shipping = true;
        } else {
            foreach ($cart->getCartRules() as $rule) {
                if ($rule['free_shipping'] && !$rule['carrier_restriction']) {
                    $free_shipping = true;
                    break;
                }
            }
        }
        return $free_shipping;
    }

    public function getCountryName($id_address_delivery)
    {
        return (new Country((new Address($id_address_delivery))->id_country, $this->context->language->id))->name;
    }

    public function checkDeliveriesCustomerAddressOnCartListing()
    {
        $carriers_restrictions = array();
        $cart = $this->context->cart;
        if (Validate::isLoadedObject($cart) && $cart->nbProducts()) {
            foreach ($cart->getProducts() as $row) {/* checkup for all products in cart */
                $id_product = (int)$row['id_product'];
                $id_product_attribute = (int)$row['id_product_attribute'];

                $product = new Product($id_product, false);
                if (Validate::isLoadedObject($product)) {
                    $result = WarehouseStock::productIsPresentInCart($cart->id, $id_product, $id_product_attribute);

                    $carriers_list = WarehouseStock::getAvailableCarrierList(
                        $product,
                        ($result && $result['id_warehouse'] > 0 ? (int)$result['id_warehouse'] : null),
                        $cart->id_address_delivery,
						$id_product_attribute
                    );
                    if (empty($carriers_list)) {
                        $carriers_restrictions[$id_product.'_'.$id_product_attribute] = 1;
                    }
                }
            }
        }
        return $carriers_restrictions;
    }

    public function initWarehousesInformationsOnCartListing()
    {
        if (!Configuration::get('WKWAREHOUSE_WAREHOUSES_INCART') && !Configuration::get('WKWAREHOUSE_LOCATIONS_INCART')) {
            return;
        }
        $id_lang = (int)$this->context->language->id;
        $asmProductsInCart = false;
        $warehouses_infos = array();

        $cart = $this->context->cart;
        if ($cart && $cart->nbProducts()) {
            $cartProducts = $cart->getProducts();
            if (is_array($cartProducts)) {
                foreach ($cartProducts as $row) {
                    $id_product = (int)$row['id_product'];
                    $id_product_attribute = (int)$row['id_product_attribute'];

                    $product = new Product($id_product, false);
                    if (Validate::isLoadedObject($product) && $product->advanced_stock_management) {
                        /* IF At least, one product uses A.S.M, continue */
                        $asmProductsInCart = true;
                        $result = WarehouseStock::productIsPresentInCart($cart->id, $id_product, $id_product_attribute);
                        if ($result && $result['id_warehouse'] > 0) {
                            $id_warehouse = (int)$result['id_warehouse'];

                            $warehouse = new StoreHouse($id_warehouse, $id_lang);
                            if (Validate::isLoadedObject($warehouse)) {
                                $country_address = Address::getCountryAndState($warehouse->id_address);

                                $warehouses_infos[$id_product.'_'.$id_product_attribute] = array(
                                    'name' => $warehouse->name,
                                    'delivery_time' => $warehouse->delivery_time,
                                    'location' => $warehouse->getProductLocation($id_product, $id_product_attribute, $id_warehouse),
                                    'quantity' => WarehouseStock::getAvailableQuantityByWarehouse(
                                        $id_product,
                                        $id_product_attribute,
                                        $id_warehouse
                                    ),
                                    'country' => (new Country($country_address['id_country'], $id_lang))->name,
                                );
                            }
                        }
                    }
                }
            }
        }
        return array(
            'asmProductsInCart' => $asmProductsInCart,
            'warehousesInfos' => $warehouses_infos,
        );
    }

    public function setCommonSmartyVarsProductPage()
    {
        $this->context->smarty->assign(array(
            'link' => new Link(),
            'warehouses_txt' => $this->setTitleExtraProductContent(),
        ));
    }

    /**
     * Display warehouses informations on product page tab
     */
    public function hookDisplayProductExtraContent($params)
    {
        if ($this->active && Configuration::get('WKWAREHOUSE_WAREHOUSEINFOS_POSITION') == 'extraContent' &&
            Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $extraContent = (new PrestaShop\PrestaShop\Core\Product\ProductExtraContent());
            if (!isset($params['product'])) {
                return $extraContent;
            }
            $product = new Product((int)$params['product']->id, false);

            if (Validate::isLoadedObject($product) && $product->advanced_stock_management) {
                $warehouses_infos = WarehouseStock::warehousesDataOnProductPage($product->id);
				WarehouseStock::takeOffDisabledWarehouses($warehouses_infos);

                if (count($warehouses_infos)) {
                    $this->setCommonSmartyVarsProductPage();
                    $this->context->smarty->assign('product_stocks_list', $warehouses_infos);

                    $extraContent->setTitle(
                        $this->setTitleExtraProductContent(false)
                    )->setContent(
                        $this->display($this->_path, 'views/templates/hook/product_header.tpl')
                    )->addAttr(
                        array('class' => 'warehousesExtraTabContent')
                    );
                    return array($extraContent);
                }
            }
        }
    }

    public function setTitleExtraProductContent($with_detailed = true)
    {
        $regarding = array();
        if (Configuration::get('WKWAREHOUSE_DISPLAY_STOCK_INFOS')) {
            $regarding[] = $this->l('Stock');
        }
        if (Configuration::get('WKWAREHOUSE_DISPLAY_LOCATION')) {
            $regarding[] = $this->l('Locations');
        }
        if (Configuration::get('WKWAREHOUSE_DISPLAY_DELIVERIES_TIME')) {
            $regarding[] = $this->l('Delivery times');
        }
        if (Configuration::get('WKWAREHOUSE_DISPLAY_COUNTRIES')) {
            $regarding[] = $this->l('Countries');
        }
        return $this->l('Warehouses').($with_detailed ? ' ('.implode(', ', $regarding).')' : '');
    }

    /**
     * Hook action called when product is saved
     */
    public function hookActionProductUpdate($params)
    {
        if ($this->active && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $id_product = $this->getProductSID($params);
            if (Tools::getIsset('form')) {
                $product_form = Tools::getValue('form');

                if (!in_array((int)$product_form['step1']['type_product'], array(1, 2))) {/* Not pack & not Virtual*/
                    $use_asm = (Tools::getIsset('field_asm') ? Tools::getValue('field_asm') : 0);
                    // Set advanced stock management for new product
                    WorkshopAsm::setAdvancedStockManagement($id_product, $use_asm);
            		Configuration::updateValue('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT', '');
					if (Configuration::get('WKWAREHOUSE_DEFAULT_NEW_PRODUCT') != 0 &&
						count(WorkshopAsm::getAssociatedWarehousesArray($id_product)) == 0) {
						WorkshopAsm::processWarehouses($id_product, null);
					}
                }
            }
        }
        return true;
    }

    public function hookActionObjectDeleteAfter($params)
    {
        if (!$this->active || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            return;
        }
        if (isset($params['object']) && is_object($params['object'])) {
            // After deleting an address
            if (Configuration::get('WKWAREHOUSE_ALLOW_MULTICARRIER_CART') &&
                Validate::isLoadedObject($this->context->cart)) {
                if ($params['object'] instanceof Address) {
                    Db::getInstance()->execute(
                        'UPDATE `'._DB_PREFIX_.'cart_product`
                         SET `id_address_delivery` = 0
                         WHERE `id_cart` = '.(int)$this->context->cart->id.' AND `id_address_delivery` = '.(int)$params['object']->id
                    );
                }
            }
            // Edit Order: deleting product
            if ($params['object'] instanceof OrderDetail) {
                $request = $this->getAdminControllerNameAndAction();
                if ($request['admin_controller'] == 'AdminOrders' && $request['admin_action'] == 'deleteProductAction') {
                    $order_detail = $params['object'];
                    if ($order_detail->id_warehouse) {
                        if ((new Product($order_detail->product_id, false))->advanced_stock_management) {
                            Configuration::updateValue(
                                'WKWAREHOUSE_ORDERDETAIL_DELETED',
                                json_encode(array(
                                    $order_detail->id => array(
                                        'product_id' => $order_detail->product_id,
                                        'product_attribute_id' => $order_detail->product_attribute_id,
                                        'id_warehouse' => $order_detail->id_warehouse,
                                    )
                                ))
                            );
                        }
                    }
                }
            }
        }
        return true;
    }

    /*
    * Hook executed after an object is updated
    */
    public function hookActionObjectUpdateAfter(array $params)
    {
        if ($this->active && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') &&
            isset($params['object']) && is_object($params['object'])) {
            /*
            * When product in order has been canceled or refunded, synchronize PS and Warehouses Qties
            * * * OrderDetail object has been updated
            * * * Specifically, when product_quantity_refunded field has been updated
            * PS 1.7.7.x makes synchronization automatically when it's about cancelling product action
            */
            if (defined('_PS_ADMIN_DIR_') && $params['object'] instanceof OrderDetail) {
                $request = $this->getAdminControllerNameAndAction();

                if ($request['admin_controller'] == 'AdminOrders') {
                    if (Tools::isSubmit('cancelProduct') || // PS < 1.7.7.x
                        Tools::isSubmit('partialRefund') || // PS < 1.7.7.x
                        ($request['admin_action'] == 'standardRefundAction') || // PS >= 1.7.7.x
                        ($request['admin_action'] == 'partialRefundAction' && isset($request['cancel_product']['restock']) && $request['cancel_product']['restock'])) {// PS >= 1.7.7.x
                        $order_detail = new OrderDetail((int)$params['object']->id);
                        $order = new Order($order_detail->id_order);

                        if (Validate::isLoadedObject($order) &&
                            Validate::isLoadedObject($order_detail) && $order_detail->id_warehouse) {
                            $product = new Product($order_detail->product_id, false);
                            if ($product->advanced_stock_management) {/* Product is A.S.M ? */
                                WorkshopAsm::updatePhysicalProductAvailableQuantity(
                                    $order_detail->product_id,
                                    $order->id_shop
                                );
                                (new WorkshopAsm())->synchronize(
                                    (int)$order_detail->product_id,
                                    (int)$order_detail->product_attribute_id,
                                    null,
                                    array(),
                                    false,
                                    $order_detail->id_warehouse
                                );
                            }
                        }
                    }
                }
            }
            if ($params['object'] instanceof Address) {
                if (Tools::getIsset('delete') && Tools::getValue('delete')) {/* if delete action from frontoffice form */
                    $this->hookActionObjectDeleteAfter($params);
                }
            }
        }
        return true;
    }

    /**
     * Hook that is fired after an object has been created in the db.
     * Useful when adding new product, new combination, etc.
     */
    public function hookActionObjectAddAfter(array $params)
    {
        // Can we use this hook?
        if ($this->active && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') &&
            isset($params['object']) && is_object($params['object'])) {
            // After adding new product
			// getAccessAfterAddHook (default true) created especially to not allow Wk Stock Manager access this hook
            if ($params['object'] instanceof Product && self::getAccessAfterAddHook()) {
                $id_product = (int)$params['object']->id;
                if (!empty($id_product) && $params['object']->state != Product::STATE_TEMP) {
					/* IF we're coming from product form, otherwise use default setting */
					$use_asm = (Tools::getIsset('field_asm') ? Tools::getValue('field_asm') : Configuration::get('WKWAREHOUSE_USE_ASM_NEW_PRODUCT'));
					/* Set advanced stock management for new product */
					WorkshopAsm::setAdvancedStockManagement($id_product, $use_asm);
					/* Set default Warehouse, synchronize quantities */
					if (Configuration::get('WKWAREHOUSE_DEFAULT_NEW_PRODUCT') != 0) {
            			Configuration::updateValue('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT', '');
						WorkshopAsm::processWarehouses($id_product, null);
					}
                }
            }
            // After adding new combination
            if ($params['object'] instanceof Combination) {
                $default_warehouse = Configuration::get('WKWAREHOUSE_DEFAULT_NEW_PRODUCT');
                if (!empty($default_warehouse)) {
                    $id_combination = (int)$params['object']->id;
                    if (!empty($id_combination)) {
                        $combination = new Combination($id_combination);

                        $wpl_id = (int)StorehouseProductLocation::getIdByProductAndWarehouse(
                            (int)$combination->id_product,
                            (int)$id_combination,
                            (int)$default_warehouse
                        );
                        if (empty($wpl_id)) {
                            // Create new warehouse association
                            $warehouse_location_entity = new StorehouseProductLocation();
                            $warehouse_location_entity->id_product = (int)$combination->id_product;
                            $warehouse_location_entity->id_product_attribute = (int)$id_combination;
                            $warehouse_location_entity->id_warehouse = (int)$default_warehouse;
                            $warehouse_location_entity->location = '';
                            if ($warehouse_location_entity->save()) {
                                // Because product has combinations, so remove the useless warehouse association with product attribute 0
                                $awc = StorehouseProductLocation::getCollection($combination->id_product, 0);
                                foreach ($awc as $wc) {
                                    $wc->delete();
                                }
                            }
                        }
                    }
                }
            	Configuration::updateValue('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT', '');
            }
            /*
            * IF creating order from BO & product is associated to warehouse(s) but without any stock
            * PS >= 1.7.7.x: if we're adding A.S.M product from Edit order page, save warehouse ID
            */
            if ($params['object'] instanceof OrderDetail) {
                if (defined('_PS_ADMIN_DIR_')) {
                    $order_detail = new OrderDetail((int)$params['object']->id);
                    $product = new Product((int)$order_detail->product_id, false);
                    if (Validate::isLoadedObject($order_detail) && Validate::isLoadedObject($product) &&
                        $product->advanced_stock_management) {
                        // If we are adding product from Edit order page (PS >= 1.7.7.x)
                        if (!$order_detail->id_warehouse) {
                            $request = $this->getAdminControllerNameAndAction();
                            if (!empty($request) && $request['admin_controller'] == 'AdminOrders' &&
                                $request['admin_action'] == 'addProductAction') {
                                /* Get the warehouse ID of this product auto. saved when adding product to cart */
                                $result = WarehouseStock::productIsPresentInCart(
                                    (new Order($order_detail->id_order))->id_cart,
                                    $order_detail->product_id,
                                    $order_detail->product_attribute_id
                                );
                                if ($result && isset($result['id_warehouse']) && $result['id_warehouse'] > 0) {
                                    $order_detail->id_warehouse = (int)$result['id_warehouse'];
                                    $order_detail->save();
                                }
                            }
                        }
                    }
                }
            }
            // After adding new address
            if ($params['object'] instanceof Address) {
                $id_address = (int)$params['object']->id;
                $cart = $this->context->cart;
                if (!empty($id_address) && Validate::isLoadedObject($cart)) {
                    WarehouseStock::assignRightDeliveryAddressToEachProductInCart($cart, $id_address);
                }
            }
        }
        return true;
    }

    /*
     * This hook is called before a product is deleted
    */
    public function hookActionAdminDeleteBefore($params)
    {
        if ($this->active && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            if (isset($params['product_id'])) {// when single product to remove
                $id_product = $params['product_id'];
            } elseif (isset($params['product_list_id'])) {// when bulk remove
                $id_product = $params['product_list_id'][0];
            }
            if (isset($id_product) && !empty($id_product)) {
                // Give ablility to delete product by disabling A.S.M for product
                WorkshopAsm::setAdvancedStockManagement($id_product, 0);
            }
        }
        return true;
    }

    /*
     * This hook is called when a product is deleted
    */
    public function hookActionProductDelete($params)
    {
        if ($this->active && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $id_product = $this->getProductSID($params);
            if (!empty($id_product)) {
                foreach (StorehouseProductLocation::getCollection($id_product) as $awc) {
                    $awc->delete();
                }
            }
        }
        return true;
    }

    /*
     * This hook is called after a combination is deleted
    */
    public function hookActionAttributeCombinationDelete($params)
    {
        if ($this->active && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $id_product_attribute = (int)$params['id_product_attribute'];
            if (!empty($id_product_attribute)) {
                foreach (StorehouseProductLocation::getCollection(null, $id_product_attribute) as $awc) {
                    $awc->delete();
                }
            }
        }
        return true;
    }

    // This hook is called after a product is removed from a cart
    public function hookActionObjectProductInCartDeleteAfter($params)
    {
        if (!$this->active || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            return;
        }
        $id_cart = (int)$params['id_cart'];
        $id_product = (int)$params['id_product'];
        if ($id_cart && $id_product) {
            $id_product_attribute = (int)$params['id_product_attribute'];
            // Remove trace from warehouse cart table
            WarehouseStock::removeProductFromWarehouseCart($id_cart, $id_product, $id_product_attribute);

            // Fix the right delivery address in cart if it remains only one product in cart
			$cartProducts = $this->context->cart->getProducts();
            if ($this->context->cookie->id_customer &&
				is_array($cartProducts) && count($cartProducts) == 1) {/* logged in ? */
                $last_product = WarehouseStock::getLastCartProduct($id_cart, $id_product, $id_product_attribute);
                if ($last_product) {
                    WarehouseStock::updateCartDeliveryAddress(
                        $id_cart,
                        $last_product['id_address_delivery'],
                        false
                    );
                }
            }
			// Check if cart is existant and does not contains any products, delete the cart
			$hasProducts = method_exists('Cart', 'hasProducts') ? $this->context->cart->hasProducts() : $this->context->cart->hasRealProducts();
			if (!defined('_PS_ADMIN_DIR_') && !$hasProducts) {
				$this->context->cart->delete();
			}
        }
    }

    /*
    * Use it only when we're adding product to CART
    */
    public function hookActionCartSave($params)
    {
        if (!$this->active) {
            return false;
        }
        $cart = $this->context->cart;

        if (defined('_PS_ADMIN_DIR_')) {
            $id_product_attribute = $id_product = 0;
            if (Tools::getIsset('add_product')) {
                $add_product = Tools::getValue('add_product');
                $id_product = $add_product['product_id'];
                $id_product_attribute = $add_product['product_attribute_id'];
            }
            if (Tools::getIsset('product_id')) {
                $id_product = Tools::getValue('product_id');
            }
            if (Tools::getIsset('combination_id')) {
                $id_product_attribute = Tools::getValue('combination_id');
            }
            if (Tools::getIsset('productId')) {
                $id_product = Tools::getValue('productId');
            }
            if (Tools::getIsset('attributeId')) {
                $id_product_attribute = Tools::getValue('attributeId');
            }
        } else {
            $id_product = Tools::getValue('id_product');
        }
        $product = new Product((int)$id_product, false);

        $actionCartSave = false;

        // BO order page management
        if (defined('_PS_ADMIN_DIR_')) {
            $request = $this->getAdminControllerNameAndAction();

            // Order From BO > Add product
            if (Validate::isLoadedObject($product) && $product->advanced_stock_management &&
                in_array($request['admin_action'], array('addProductOnOrder', 'addProductAction'))) {
                $actionCartSave = true;
            }
            /*
            * BO Order Management: Remove also product from warehouse cart
            ** - remove product from cart during creating Order
            ** - remove product from order during editing Order
            */
            if (in_array($request['admin_action'], array('deleteProduct', 'deleteProductAction'))) {
                /* When removing product from order page, the ids of product and combination are not provided */
                if (empty($id_product) && $request['admin_controller'] == 'AdminOrders' &&
                    $request['admin_action'] == 'deleteProductAction' && isset($request['orderDetailId'])) {
                    $order_detail = new OrderDetail((int)$request['orderDetailId']);
                    if (Validate::isLoadedObject($order_detail)) {
                        $id_product = (int)$order_detail->product_id;
                        $id_product_attribute = (int)$order_detail->product_attribute_id;
                    }
                }
				 // New backoffice order
                if ($request['admin_controller'] == 'AdminCarts' && $request['admin_action'] == 'deleteProductAction') {
					if (isset($request['productId'])) {
                    	$id_product = (int)$request['productId'];
					}
					if (isset($request['attributeId'])) {
                    	$id_product_attribute = (int)$request['attributeId'];
					}
                }
                $this->hookActionObjectProductInCartDeleteAfter(array(
                    'id_cart' => Validate::isLoadedObject($cart) ? $cart->id : $request['cartId'],
                    'id_product' => $id_product,
                    'id_product_attribute' => $id_product_attribute,
                ));
                return;
            }
        }

        if (Tools::getIsset('add')) {// Shopping cart (From FO) || Tools::getIsset('update')
            $actionCartSave = true;
        }

        if ($actionCartSave && Validate::isLoadedObject($cart) && Validate::isLoadedObject($product)) {
            if (!isset($id_product_attribute)) {
                $group = Tools::getIsset('group') ? Tools::getValue('group') : '';
                $id_product_attribute = (!empty($group) ? (int)Product::getIdProductAttributeByIdAttributes($product->id, $group) : 0);
                if (Tools::getIsset('id_product_attribute')) {
                    $id_product_attribute = (int)Tools::getValue('id_product_attribute');
                }
            }
            // IF A.S.M
            if ($product->advanced_stock_management) {
                /* Select the best warehouse (according to stock and carrier) */
                $selected_warehouse = WarehouseStock::getAvailableWarehouseAndCartQuantity(
                    $product->id,
                    $id_product_attribute,
                    $cart
                );
                if ($selected_warehouse && $selected_warehouse['id_warehouse'] > 0) {
                    /* Add - Update module cart table */
                    WarehouseStock::updateProductWarehouseCart(
                        $cart->id,
                        $product->id,
                        $id_product_attribute,
                        $selected_warehouse['id_warehouse']
                    );
                    /* Set the right delivery address for the added product in cart */
                    $new_id_address_delivery = (int)$selected_warehouse['id_address_delivery'];
                }
            } else {
                // IF Not A.S.M Product but we allow multi-carriers
                // Look for at least one common carrier, if not found, let our module handle
                if (Configuration::get('WKWAREHOUSE_ALLOW_MULTICARRIER_CART')) {
                    $product_carriers = WarehouseStock::getCarriersByCustomerAddresses(
						$product,
						$id_product_attribute
					);
                    $product_carriers = $product_carriers['available_carriers'];
                    if (count($product_carriers)) {
                        $cart_delivery_option = $cart->getDeliveryOption();
                        if (count($cart_delivery_option)) {
                            $carriers_in_cart = array();
                            foreach ($cart_delivery_option as $delivery_option) {
                                $carriers_in_cart = array_merge($carriers_in_cart, array_filter(explode(',', $delivery_option)));
                            }
                            $carriers_in_cart = array_unique($carriers_in_cart);
                            $product_carriers = array_values($product_carriers);
                            /* IF there is not at least one common carrier */
                            if (!array_intersect($product_carriers, $carriers_in_cart)) {
                                $product_carriers = WarehouseStock::getCarriersByCustomerAddresses(
									$product,
									$id_product_attribute
								);
                                if (count($product_carriers['available_carriers'])) {
                                    $new_id_address_delivery = (int)$product_carriers['id_address_delivery'];
                                }
                            }
                        }
                    }
                }
            }

            // Change to the right delivery address
            if (isset($new_id_address_delivery) &&
                ((isset($this->context->cookie->id_customer) && $this->context->cookie->id_customer) || !empty($cart->id_customer))) {
                $last_product = WarehouseStock::getLastCartProduct($cart->id, $product->id, $id_product_attribute);
                if ($last_product) {
                    $old_id_address_delivery = (int)$last_product['id_address_delivery'];
                    if ($new_id_address_delivery > 0) {
                        $cart->setProductAddressDelivery(
                            $product->id,
                            $id_product_attribute,
                            $old_id_address_delivery,
                            $new_id_address_delivery
                        );
                    } else {
                        WarehouseStock::updateCartProduct($cart->id, $new_id_address_delivery, $product->id, $id_product_attribute);
                    }
					$cartProducts = $cart->getProducts();
                    if (is_array($cartProducts) && count($cartProducts) == 1) {
                        WarehouseStock::updateCartDeliveryAddress($cart->id, $new_id_address_delivery, true);
                    }
                }
            }
        }
        // Validate cart integrity
        if (!Tools::getIsset('action')) {
            WarehouseStock::checkCartIntegrity($cart);
        }
    }

    public function hookActionSetInvoice($params)
    {
        if (!$this->active || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') || !($order = $params['Order'])) {
            return false;
        }
        if ($params['use_existing_payment'] && (Order::isOrderMultiWarehouses($order) || Order::isOrderMultiCarriers($order))) {
            $order_invoice = $params['OrderInvoice'];

            $id_order_payments = Db::getInstance()->executeS(
                'SELECT DISTINCT op.id_order_payment
                 FROM `'._DB_PREFIX_.'order_payment` op
                 INNER JOIN `'._DB_PREFIX_.'orders` o ON (o.reference = op.order_reference)
                 LEFT JOIN `'._DB_PREFIX_.'order_invoice_payment` oip ON (oip.id_order_payment = op.id_order_payment)
                 WHERE (oip.id_order != '.(int)$order_invoice->id_order.' OR oip.id_order IS NULL) AND 
                 o.id_order = '.(int)$order_invoice->id_order
            );
            if (count($id_order_payments)) {
                foreach ($id_order_payments as $order_payment) {
                    Db::getInstance()->execute(
                        'DELETE FROM `'._DB_PREFIX_.'order_invoice_payment`
                         WHERE
                            `id_order_invoice` = '.(int)$order_invoice->id.' AND
                            `id_order_payment` = '.(int)$order_payment['id_order_payment'].' AND
                            `id_order` = '.(int)$order_invoice->id_order
                    );
                }
                Cache::clean('order_invoice_paid_*'); // Clear cache
            }
        }
    }

    /*
    * Check up assigned warehouse for each product of placed order
    * Save mvt after validating order (Prestashop don't)
    */
    public function hookActionValidateOrder($params)
    {
        if (!$this->active || !($order = $params['order'])) {
            return false;
        }
        // IF A.S.M enabled
        if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $productList = $order->getProducts();
            $id_order_carrier = $order->id_carrier; // Have Order Carrier
            $id_cart = $order->id_cart; // Init cart
			$id_warehouses_notifications = array();

            foreach ($productList as $product) {
                $product_id = (int)$product['product_id'];
                $productObj = new Product($product_id, false);

                if (Validate::isLoadedObject($productObj) && $productObj->advanced_stock_management) {
                    $id_order_detail = (int)$product['id_order_detail'];
                    $product_attribute_id = (int)$product['product_attribute_id'];
                    $product_warehouse_id = (int)$product['id_warehouse'];

                    /* Get the warehouse ID of this product in module cart table */
                    $result = WarehouseStock::productIsPresentInCart($id_cart, $productObj->id, $product_attribute_id);
                    if ($result && isset($result['id_warehouse']) && $result['id_warehouse'] > 0) {
                        $id_warehouse_priority = (int)$result['id_warehouse'];
                    } else {
                        /* Look for the warehouse according to the priorities parameters */
                        $id_warehouse_priority = WorkshopAsm::findWarehousePriority(
							array(),
							true,
							$product_id,
							$product_attribute_id,
							'desc'
						);
                    }
                    /* IF warehouse priority */
                    if ($id_warehouse_priority) {
                        $update = false;
                        if ($result) {
                            $update = true;
                        } else {
                            if (empty($id_order_carrier) && empty($product_warehouse_id)) {
                                $update = true;
                            } else {
                                // Carriers of prior warehouse
                                $warehouse_carrier_list = (new StoreHouse($id_warehouse_priority))->getCarriers(true);
                                $id_reference_order_carrier = (new Carrier($order->id_carrier))->id_reference;

                                // If warehouse not assigned by order
                                // Don't assign warehouse to product since the order carrier don't
                                // match any of the prior warehouse carriers
                                if (empty($product_warehouse_id)) {
                                    if (in_array($id_reference_order_carrier, $warehouse_carrier_list)) {
                                        $update = true;
                                    }
                                } else {
                                    if ($product_warehouse_id != $id_warehouse_priority) {
                                        if (in_array($id_reference_order_carrier, $warehouse_carrier_list)) {
                                            $update = true;
                                        }
                                    }
                                }
                            }
                        }
                        // Update order detail with the new warehouse id
                        if ($update && $id_order_detail && $id_warehouse_priority) {
                            $order_detail = new OrderDetail($id_order_detail);
                            $order_detail->id_warehouse = (int)$id_warehouse_priority;
                            if ($order_detail->update()) {
								// collect warehouses ID to send mail notifications to their manager
								$id_warehouses_notifications[] = (int)$id_warehouse_priority;
							}
                        }
                    }
                    // Save stock movement
                    $this->saveMovement(
                        $productObj,
                        $product_attribute_id,
                        $product['product_quantity'] * -1, // because this is order, so decrease stock
                        array(
                            'id_order' => $order->id,
                            'id_stock_mvt_reason' => Configuration::get('PS_STOCK_CUSTOMER_ORDER_REASON')
                        )
                    );
                }
            }

			// Send a mail notification for each warehouse manager
			if (Configuration::get('WKWAREHOUSE_SENDMAIL_EACH_EMPLOYEE') && !empty($id_warehouses_notifications)) {
				$id_warehouses_notifications = array_unique($id_warehouses_notifications);

				// Getting differents vars
				$context = Context::getContext();
				$id_lang = (int)$context->language->id;

				$id_shop = (int)$context->shop->id;
				$currency = $params['currency'];
				$customer = $params['customer'];
				$configuration = Configuration::getMultiple(array(
						'PS_SHOP_EMAIL',
						'PS_MAIL_METHOD',
						'PS_MAIL_SERVER',
						'PS_MAIL_USER',
						'PS_MAIL_PASSWD',
						'PS_SHOP_NAME',
						'PS_MAIL_COLOR',
					), $id_lang, null, $id_shop
				);
				$delivery = new Address((int)$order->id_address_delivery);
				$invoice = new Address((int)$order->id_address_invoice);
				$order_date_text = Tools::displayDate($order->date_add);
				$carrier = new Carrier((int)$order->id_carrier);
				$message = WorkshopAsm::getAllMessages($order->id);
				if (!$message || empty($message)) {
					$message = $this->l('No message');
				}

				$items_table = '';

				$customized_datas = Product::getAllCustomizedDatas((int)$params['cart']->id);
				Product::addCustomizationPrice($productList, $customized_datas);
				foreach ($productList as $key => $product) {
					$unit_price = Product::getTaxCalculationMethod($customer->id) == PS_TAX_EXC ? $product['product_price'] : $product['product_price_wt'];
					// Formulate customization text
					$customization_text = '';
					if (isset($customized_datas[$product['product_id']][$product['product_attribute_id']][$order->id_address_delivery][$product['id_customization']])) {
						foreach ($customized_datas[$product['product_id']][$product['product_attribute_id']][$order->id_address_delivery][$product['id_customization']] as $customization) {
							if (isset($customization[Product::CUSTOMIZE_TEXTFIELD])) {
								foreach ($customization[Product::CUSTOMIZE_TEXTFIELD] as $text) {
									$customization_text .= $text['name'] . ': ' . $text['value'] . '<br />';
								}
								$customization_text .= '---<br />';
							}
							if (isset($customization[Product::CUSTOMIZE_FILE])) {
								$customization_text .= count($customization[Product::CUSTOMIZE_FILE]).' '.$this->l('image(s)').'<br />';
								$customization_text .= '---<br />';
							}
						}
						if (method_exists('Tools', 'rtrimString')) {
							$customization_text = Tools::rtrimString($customization_text, '---<br />');
						} else {
							$customization_text = preg_replace('/---<br \/>$/', '', $customization_text);
						}
					}

                    $this->context->smarty->assign(array(
                        'key' => $key,
                        'product' => $product,
                        'unit_price' => $this->displayPrice($unit_price, $currency->iso_code),
                        'total_unit_price' => $this->displayPrice(($unit_price * $product['product_quantity']), $currency->iso_code),
                        'customization_text' => $customization_text,
                        'url' => $context->link->getProductLink($product['product_id']),
                        'warehouse_name' => (!empty($product['id_warehouse']) ? (new StoreHouse((int)$product['id_warehouse'], $id_lang))->name: ''),
                    ));
                    $items_table .= $this->display(__FILE__, 'views/templates/hook/product_order_line.tpl');
				}
				foreach ($params['order']->getCartRules() as $discount) {
                    $this->context->smarty->assign(array(
                        'discount_value' => $this->displayPrice($discount['value'], $currency->iso_code),
                        'discount_name' => $discount['name'],
                    ));
                    $items_table .= $this->display(__FILE__, 'views/templates/hook/product_voucher_line.tpl');
				}
				if ($delivery->id_state) {
					$delivery_state = new State((int)$delivery->id_state);
				}
				if ($invoice->id_state) {
					$invoice_state = new State((int)$invoice->id_state);
				}

				if (Product::getTaxCalculationMethod($customer->id) == PS_TAX_EXC) {
					$total_products = $order->getTotalProductsWithoutTaxes();
				} else {
					$total_products = $order->getTotalProductsWithTaxes();
				}

				$order_state = $params['orderStatus'];

				// Filling-in vars for email
				$template_vars = array(
					'{firstname}' => $customer->firstname,
					'{lastname}' => $customer->lastname,
					'{email}' => $customer->email,
					'{delivery_block_txt}' => WorkshopAsm::getFormatedAddress($delivery, "\n"),
					'{invoice_block_txt}' => WorkshopAsm::getFormatedAddress($invoice, "\n"),
					'{delivery_block_html}' => WorkshopAsm::getFormatedAddress(
						$delivery, '<br />', array(
							'firstname' => '%s',
							'lastname' => '%s',
						)
					),
					'{invoice_block_html}' => WorkshopAsm::getFormatedAddress(
						$invoice, '<br />', array(
							'firstname' => '%s',
							'lastname' => '%s',
						)
					),
					'{delivery_company}' => $delivery->company,
					'{delivery_firstname}' => $delivery->firstname,
					'{delivery_lastname}' => $delivery->lastname,
					'{delivery_address1}' => $delivery->address1,
					'{delivery_address2}' => $delivery->address2,
					'{delivery_city}' => $delivery->city,
					'{delivery_postal_code}' => $delivery->postcode,
					'{delivery_country}' => $delivery->country,
					'{delivery_state}' => isset($delivery_state->name) ? $delivery_state->name : '',
					'{delivery_phone}' => $delivery->phone ? $delivery->phone : $delivery->phone_mobile,
					'{delivery_other}' => $delivery->other,
					'{invoice_company}' => $invoice->company,
					'{invoice_firstname}' => $invoice->firstname,
					'{invoice_lastname}' => $invoice->lastname,
					'{invoice_address2}' => $invoice->address2,
					'{invoice_address1}' => $invoice->address1,
					'{invoice_city}' => $invoice->city,
					'{invoice_postal_code}' => $invoice->postcode,
					'{invoice_country}' => $invoice->country,
					'{invoice_state}' => isset($invoice_state->name) ? $invoice_state->name : '',
					'{invoice_phone}' => $invoice->phone ? $invoice->phone : $invoice->phone_mobile,
					'{invoice_other}' => $invoice->other,
					'{order_name}' => $order->reference,
					'{order_status}' => $order_state->name,
					'{shop_name}' => $configuration['PS_SHOP_NAME'],
					'{date}' => $order_date_text,
					'{carrier}' => (($carrier->name == '0') ? $configuration['PS_SHOP_NAME'] : $carrier->name),
					'{payment}' => Tools::substr($order->payment, 0, 32),
					'{items}' => $items_table,
					'{total_paid}' => $this->displayPrice($order->total_paid, $currency->iso_code),
					'{total_products}' => $this->displayPrice($total_products, $currency->iso_code),
					'{total_discounts}' => $this->displayPrice($order->total_discounts, $currency->iso_code),
					'{total_shipping}' => $this->displayPrice($order->total_shipping, $currency->iso_code),
					'{total_shipping_tax_excl}' => $this->displayPrice($order->total_shipping_tax_excl, $currency->iso_code),
					'{total_shipping_tax_incl}' => $this->displayPrice($order->total_shipping_tax_incl, $currency->iso_code),
					'{total_tax_paid}' => $this->displayPrice(
						$order->total_paid_tax_incl - $order->total_paid_tax_excl,
						$currency->iso_code
					),
					'{total_wrapping}' => $this->displayPrice($order->total_wrapping, $currency->iso_code),
					'{currency}' => $currency->sign,
					'{gift}' => (bool)$order->gift,
					'{gift_message}' => $order->gift_message,
					'{message}' => $message,
				);
				// Shop iso
				$iso = Language::getIsoById((int)Configuration::get('PS_LANG_DEFAULT'));
				// Default language
				$mail_id_lang = $id_lang;
				$mail_iso = $iso;

            	foreach ($id_warehouses_notifications as $warehouse_id) {
					$wh = new StoreHouse((int)$warehouse_id);
                	if (Validate::isLoadedObject($wh)) {
						// Use the merchant (administrator) lang
						$result = Db::getInstance()->executeS('
							SELECT `id_lang`, `email`, `firstname`, `lastname` FROM `'._DB_PREFIX_.'employee`
							WHERE `id_employee` = '.(int)$wh->id_employee
						);
						if ($result) {
							$user_iso = Language::getIsoById((int)$result[0]['id_lang']);
							if ($user_iso) {
								$mail_id_lang = (int)$result[0]['id_lang'];
								$mail_iso = $user_iso;
							}
							$merchant_mail = $result[0]['email'];
							$merchant_name = $result[0]['firstname'].' '.$result[0]['lastname'];
							$template_vars['{warehouse}'] = $wh->name[$mail_id_lang];

							WorkshopAsm::copyEmailTmpl('new_order', $this->mail_folder, $mail_id_lang);

							if ($merchant_mail) {
								Mail::send(
									$mail_id_lang,
									'new_order',
									Mail::l('New order', $mail_id_lang).' '.$order->reference,
									$template_vars,
									$merchant_mail,
									$merchant_name,
									$configuration['PS_SHOP_EMAIL'],
									$configuration['PS_SHOP_NAME'],
									null,
									null,
									$this->mail_folder,
									false,
									$order->id_shop
								);
							}
						}
					}
				}
			}
        }
    }

    public function displayPrice($price, $iso_code = null)
    {
        if (!is_numeric($price)) {
            return $price;
        }
        if (method_exists('Tools', 'getContextLocale')) {
            return Tools::getContextLocale(Context::getContext())->formatPrice($price, $iso_code);
        } else {
            return Tools::displayPrice($price);
        }
    }

    public function saveMovement($product, $productAttributeId, $deltaQuantity, $params = array())
    {
        if ($deltaQuantity != 0) {
            $stockAvailable = (new StockManagerAdapter())->getStockAvailableByProduct($product, $productAttributeId);

            $employee = new Employee(1); // super admin

            $mvt_params = array(
                'id_stock' => (int)$stockAvailable->id,
                'id_order' => (int)$params['id_order'],
                'id_stock_mvt_reason' => (int)$params['id_stock_mvt_reason'],
                'id_employee' => (int)$employee->id,
                'employee_firstname' => $employee->firstname,
                'employee_lastname' => $employee->lastname,
                'physical_quantity' => abs($deltaQuantity),
                'date_add' => date('Y-m-d H:i:s'),
                'sign' => -1,
                'price_te' => 0.000000,
                'last_wa' => 0.000000,
                'current_wa' => 0.000000,
            );
            // Add the cart rule to the cart
            if (!Db::getInstance()->insert('stock_mvt', $mvt_params)) {
                return false;
            }
        }
    }

    /*
    * Called after applying the new status to customer order.
    *
    * 'newOrderStatus' => (object)OrderState,
    * 'id_order' => (int)Order ID,
    */
    public function hookActionOrderStatusPostUpdate($params)
    {
        if ($this->active && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $new_os = $params['newOrderStatus'];
            $order = new Order((int)$params['id_order']);

            if (Validate::isLoadedObject($order) && Validate::isLoadedObject($new_os)) {
        		if (method_exists('Order', 'fixOrderPayment')) {
                	$order->fixOrderPayment(); // if order has been paid from front directly
				}
                $this->synchronizeProductsOrder($order);
            }
        }
    }

    /*
    * IF order product has been changed (quantity change or product deletion)
    */
    public function hookActionOrderEdited($params)
    {
        if ($this->active && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $order = $params['order'];
            if (Validate::isLoadedObject($order)) {
                $request = $this->getAdminControllerNameAndAction();

                if ($request['admin_controller'] == 'AdminOrders') {
                    /* if product quantity has been changed */
                    if ($request['admin_action'] == 'updateProductAction') {
                        $this->synchronizeProductsOrder($order);
                    }
                    /* if product has been deleted from order */
                    if ($request['admin_action'] == 'deleteProductAction' && Configuration::get('WKWAREHOUSE_ORDERDETAIL_DELETED')) {
                        $deleted_order_detail_id = (int)$request['orderDetailId'];
                        $deleted_product = json_decode(
							Configuration::get('WKWAREHOUSE_ORDERDETAIL_DELETED'),
							true// true: return an array
						);
                        if (isset($deleted_product[$deleted_order_detail_id])) {
                            $product = $deleted_product[$deleted_order_detail_id];
                            if ($product['product_id'] && $product['id_warehouse']) {
                                WorkshopAsm::updatePhysicalProductAvailableQuantity($product['product_id']);
                                (new WorkshopAsm())->synchronize(
                                    $product['product_id'],
                                    $product['product_attribute_id'],
                                    null,
                                    array(),
                                    false,
                                    $product['id_warehouse']
                                );
                                Configuration::deleteByName('WKWAREHOUSE_ORDERDETAIL_DELETED');
                            }
                        }
                    }
                }
            }
        }
    }

    // Sync stock of all products of a given order
    public function synchronizeProductsOrder($order)
    {
        WorkshopAsm::updatePhysicalProductAvailableQuantity(null, $order->id_shop, $order->id);

        foreach ($order->getProductsDetail() as $product) {
            if ((new Product($product['product_id'], false))->advanced_stock_management && !empty($product['id_warehouse'])) {
                (new WorkshopAsm())->synchronize(
                    (int)$product['product_id'],
                    (int)$product['product_attribute_id'],
                    null,
                    array(),
                    false,
                    $product['id_warehouse']
                );
            }
        }
    }
    
    public function hookActionUpdateQuantity($params)
    {
        if ($this->active && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $id_product = $this->getProductSID($params);
            $id_product_attribute = isset($params['id_product_attribute']) ? $params['id_product_attribute'] : null;

            if (!Pack::isPack($id_product)) {// don't process pack : not yet, may be later!
                $product = new Product((int)$id_product, false);

                if (Validate::isLoadedObject($product) && $product->advanced_stock_management) {
                    $stockAvailable = (new StockManagerAdapter())->getStockAvailableByProduct($product, $id_product_attribute);

                    if ($stockAvailable->id) {
                        // Get associated warehouses
                        $associated_warehouses = WorkshopAsm::getAssociatedWarehousesArray(
                            $id_product,
                            $id_product_attribute
                        );
                        // If product is stored in warehouse(s)
                        if (count($associated_warehouses) > 0) {
                            $this->synchronizeWarehousesQty(
                                $associated_warehouses,
                                $product->id,
                                ($product->hasAttributes() && !empty($id_product_attribute) ? $id_product_attribute : 0),
								(isset($params['delta_quantity']) ? $params['delta_quantity'] : null)
                            );
                        }
                    }
                }
            }
        }
    }

    protected function getAdminControllerNameAndAction()
    {
        $params = array();
        if (!Tools::getIsset('controller')) {
            $request = $this->getBackofficeRequestParameters();
            if (!is_null($request)) {
                $params['admin_controller'] = $request->get('_legacy_controller');
                $controller_param = $request->get('_controller');
				$double_points_contains = strpos($controller_param, '::');
				if ($double_points_contains !== false) {
					$controller_array = explode('::', $controller_param);
				} else {
					$controller_array = explode(':', $controller_param);
				}
                $params['admin_action'] = $controller_array[1];
                if ($request->get('cartId')) {
                    $params['cartId'] = $request->get('cartId');
                }
                if ($request->get('orderDetailId')) {
                    $params['orderDetailId'] = $request->get('orderDetailId');
                }
                if ($request->get('orderId')) {
                    $params['orderId'] = $request->get('orderId');
                }
                // Merge also all sent post variables
                if (is_array($request->request->all())) {
                    $params = array_merge($params, $request->request->all());
                }
            }
        } else {
            $params['admin_controller'] = Tools::getValue('controller');
            $params['admin_action'] = Tools::getValue('action');
        }
        return $params;
    }

    protected function getBackofficeRequestParameters()
    {
        try {
            $kernel = ${'GLOBALS'}['kernel'];
            if (!is_null($kernel)) {
                if (version_compare(_PS_VERSION_, '1.7.4.0', '>=')) {
                    $request = $kernel->getContainer()->get('request_stack')->getCurrentRequest();
                } else {
                    $request = $kernel->getContainer()->get('request');
                }
                if (!is_object($request)) {
                    return null;
                }
                return $request;
            }
        } catch (Exception $e) {
            return null;
        }
        return null;
    }

    /*
     * Check reserved quantity, if not empty, add it to available qty to have the real physical stock
     * stock_available.quantity: available stock in your shop
     * stock_available.reserved_quantity: if there is customer order, this quantity is reserved
     * stock_available.physical_quantity (quantity in warehouse): stock_available.quantity + stock_available.reserved_quantity
     */
    public function synchronizeWarehousesQty(
		$associated_warehouses,
		$id_product,
		$id_product_attribute,
		$delta_quantity
	) {
        $authorizeSyncQty = $authorizePhysicalUpdateQty = true;

        if (defined('_PS_ADMIN_DIR_')) {
            $request = $this->getAdminControllerNameAndAction();
        }

        // CASES WHERE SYNC NOT AUTHORIZED
        if (Tools::getIsset('controller') || (isset($request) && !empty($request))) {
            /* IF from payment page (FO) */
            if (Tools::getIsset('module') && Validate::isLoadedObject($this->context->cart)) {
                foreach (Module::getPaymentModules() as $module) {
                    if ($module['name'] == Tools::getValue('module')) {
                        $authorizeSyncQty = false;
                		$result = WarehouseStock::productIsPresentInCart($this->context->cart->id, $id_product, $id_product_attribute);
                        if ($result && $result['id_warehouse'] > 0) {
							if (empty($delta_quantity)) {
								$cart_product = $this->context->cart->containsProduct($id_product, $id_product_attribute);
								if ($cart_product) {
									$delta_quantity = isset($cart_product['quantity']) ? (int)$cart_product['quantity'] : 0;
								}
							}
							// Save movement
							if ($delta_quantity < 0) {
								$delta_quantity *= -1;
							}
							if (!empty($delta_quantity) && $delta_quantity > 0 && Validate::isUnsignedInt($delta_quantity)) {
								$stocks = WarehouseStock::getStocksRows($id_product, $id_product_attribute, $result['id_warehouse']);
								if ($stocks) {
									$stock = current($stocks);
									if ($stock['id_stock']) {
										// Don't synchronize quantities but save order movement
										$stock_mvt = new WarehouseStockMvt();
										$stock_mvt->hydrate(array(
											'id_stock' => (int)$stock['id_stock'],
											'id_order' => (int)Order::getIdByCartId($this->context->cart->id),
											'id_stock_mvt_reason' => Configuration::get('PS_STOCK_CUSTOMER_ORDER_REASON'),
											'price_te' => (new WorkshopAsm())->getUnitWarehousePrice(
												$id_product,
												$id_product_attribute,
												$result['id_warehouse']
											),
											'sign' => -1,
											'physical_quantity' => (int)$delta_quantity,
											'id_employee' => 0,
										));
										$stock_mvt->add();
										break;
									}
								}
							}
						}
                    }
                }
            }
            /*
            ** IF creating new customer's order From BO
            ** OR
            ** Adding / Editing / deleting product of order from BO
            */
            if (isset($request) && $request['admin_controller'] == 'AdminOrders') {
                if (Tools::isSubmit('submitAddOrder') || // PS < 1.7.7.x : Create order from BO
                    Tools::isSubmit('cart_summary') || // Ps >= 1.7.7.x : Create order from BO
                    Tools::isSubmit('submitState') || // PS < 1.7.7.x : Change order status
                    in_array(
                        $request['admin_action'],
                        array(
                            'addProductOnOrder', // PS < 1.7.7.x : Edit order: Add new product
                            'updateProductAction', // PS >= 1.7.7.x : From Order Edit page (decrease qty)
                            'deleteProductAction', // PS >= 1.7.7.x : From Order Edit page (delete product)
                            'updateStatusAction', // PS >= 1.7.7.x : Change order status
                        )
                    )) {
                    $authorizeSyncQty = false;
                }
                // Ps >= 1.7.7.x : Adding product from edit order page
                if ($request['admin_action'] == 'addProductAction') {
                    $authorizePhysicalUpdateQty = false;
                }
            }
            /* IF order is being created from RockPos (External module) */
            if (Tools::getValue('controller') == 'sales' && Tools::getValue('module') == 'hspointofsalepro') {
                if (Tools::getIsset('action') && Tools::getValue('action') == 'order') {
                    $authorizeSyncQty = false;
                }
            }
        }

        // IF SYNC IS AUTHORIZED
        if ($authorizeSyncQty) {
            /* Sync Prestashop quantities */
            if ($authorizePhysicalUpdateQty) {
                WorkshopAsm::updatePhysicalProductAvailableQuantity($id_product);
            }

            $id_warehouse = null;
            $productIsPresentRestrict = true;
            $synchronizeIncreaseProduct = true; /* addProduct function */

            /* IF PRODUCT STORED IN ONE WAREHOUSE */
            if (count($associated_warehouses) == 1) {
                $id_warehouse = (int)$associated_warehouses[0];
                $productIsPresentRestrict = false; /* !Important, be able to create stock in database if not exists */
            } else {
            /* IF PRODUCT STORED IN MULTIPLE WAREHOUSES => USE PRIORITY */
                $is_present = WarehouseStock::productIsPresentInStock(
                    $id_product,
                    (empty($id_product_attribute) ? null : $id_product_attribute)
                );
                if (!$is_present) {
                    /* Use only warehouses priority, no need for stock priority */
                    $id_warehouse = WorkshopAsm::findWarehousePriority($associated_warehouses, false);
                    $productIsPresentRestrict = false;
                } else {
                    /* Compare the two physical quantities (Prestashop & Warehouses) */
                    $physical_quantity_in_warehouses = (int)WorkshopAsm::getProductPhysicalQuantities(
                        $id_product,
                        $id_product_attribute
                    );
                    $stock_infos = WorkshopAsm::getAvailableStockByProduct($id_product, $id_product_attribute);
                    $delta_qty = (int)($stock_infos['physical_quantity'] - $physical_quantity_in_warehouses);

                    if ($delta_qty <= 0) {
                        $synchronizeIncreaseProduct = false;
                        /* If decrease qty, can be decreased from many warehouses */
                        if ($delta_qty < 0) {
                            $delta_qty *= -1; /* need to be positive always */
                            (new WorkshopAsm())->updateAccordingDescWarehouseQtiesPriority(
                                $associated_warehouses,
                                $id_product,
                                $id_product_attribute,
                                $delta_qty
                            );
                        }
                    }
                }
            }
            if ($synchronizeIncreaseProduct) {
                (new WorkshopAsm())->synchronize(
                    $id_product,
                    $id_product_attribute,
                    null,
                    $associated_warehouses,
                    $productIsPresentRestrict,
                    $id_warehouse
                );
            }
        }
    }

    public function getProductSID($params)
    {
        if (isset($params['product']->id)) {
            return $params['product']->id;
        } elseif (isset($params['id_product'])) {
            return $params['id_product'];
        } elseif (isset($params['product'])) {
            return $params['product']['id_product'];
        } else {
            return false;
        }
    }

    public function hookDisplayPDFDeliverySlip($params)
    {
        if (!$this->active) {
            return;
        }
        if (Configuration::get('WKWAREHOUSE_ON_DELIVERY_SLIP')) {
            $order = new Order((int)$params['object']->id_order);

            if (Validate::isLoadedObject($order)) {
                $order_details = $order->getProducts(); // Order's products

                if ($order_details) {
                    foreach ($order_details as $key => $order_detail) {
                        if (!empty($order_detail['id_warehouse'])) {
                            $warehouse = new StoreHouse((int)$order_detail['id_warehouse'], $this->context->language->id);
                            if (Validate::isLoadedObject($warehouse)) {
                                $order_details[$key]['warehouse_name'] = $warehouse->name;
                                $order_details[$key]['warehouse_location'] = $warehouse->getProductLocation(
                                    $order_detail['product_id'],
                                    $order_detail['product_attribute_id'],
                                    $warehouse->id
                                );
                            } else {
                                unset($order_details[$key]);
                            }
                        } else {
                            unset($order_details[$key]);
                        }
                    }
                    if (isset($order_details) && $order_details) {
                        $this->context->smarty->assign(array(
                            'order_details' => $order_details,
                            'link' => $this->context->link,
                        ));
                        return $this->display(__file__, 'delivery_slip.tpl');
                    }
                }
            }
        }
    }

    /*
    * Display new elements in the Back Office, tab AdminOrder
    * This hook launches modules when the AdminOrder tab is displayed in the Back Office
    */
    public function hookDisplayAdminOrder($params)
    {
        if (!$this->active) {
            return;
        }
        if (Configuration::get('WKWAREHOUSE_LOCATION_ORDER_PAGE')) {
            $id_order = (int)$params['id_order'];
            $order = new Order($id_order);

            if (Validate::isLoadedObject($order)) {
                $canChangeWarehouse = (int)Configuration::get('WKWAREHOUSE_CHANGE_ORDER_WAREHOUSE');
                $orderLocations = array();
                $orderDetails = $order->getProducts(); // Order's products

                foreach ($orderDetails as $orderDetail) {
                    $id_product = (int)$orderDetail['product_id'];
                    $id_product_attribute = (int)$orderDetail['product_attribute_id'];
                    $id_warehouse = (int)$orderDetail['id_warehouse'];
                    $warehouses_locations = array();

                    $product = new Product($id_product, false);
                    if (Validate::isLoadedObject($product)) {
                        $assigned_warehouses = array();
                        $associated_warehouses = WorkshopAsm::getAssociatedWarehousesArray(
                            $id_product,
                            $id_product_attribute
                        );
                        if (empty($id_warehouse)) {
                            $id_warehouse = (int)WorkshopAsm::findWarehousePriority(
                                $associated_warehouses,
                                true,
                                $id_product,
                                $id_product_attribute
                            );
                        }

                        $assigned_warehouses[] = array('id_warehouse' => $id_warehouse);
                        foreach ($assigned_warehouses as $warehouse) {
                            $wh = new StoreHouse($warehouse['id_warehouse'], $this->context->language->id);
                            if (Validate::isLoadedObject($wh)) {
                                // Let me set a warehouse ?
                                if ($canChangeWarehouse) {
                                    $warehouseList = StoreHouse::getWarehouses($associated_warehouses, false);
									if (count($warehouseList)) {
										foreach ($warehouseList as $k => &$row) {
											$id_storehouse = (int)$row['id_warehouse'];
											// Be aware: look by reference ID, Not carrier ID
											/*$carriers = (new StoreHouse($id_storehouse))->getCarriers(true);
											if (in_array((int)(new Carrier($order->id_carrier))->id_reference, $carriers)) {*/
											$row['is_default'] = ($id_storehouse == $wh->id ? 1 : 0);
											/*} else {
												unset($warehouseList[$k]);
											}*/
										}
									}
                                }
                                $warehouses_locations[] = array(
                                    'name' => trim($wh->name),
                                    'location' => trim($wh->getProductLocation($id_product, $id_product_attribute, $wh->id)),
                                    'warehouseList' => isset($warehouseList) ? $warehouseList : array(),
                                );
                            }
                        }
                    }
                    $orderLocations[$orderDetail['id_order_detail']] = $warehouses_locations;
                }
                
                if (version_compare(_PS_VERSION_, '1.7.7', '>=')) {
                    $this->context->smarty->assign(array(
                        'orderLocations' => $orderLocations,
                    ));
                    return $this->display(__FILE__, 'views/templates/hook/admin_order.tpl');
                } else {
                    Media::addJsDef(array(
                        'orderLocations' => $orderLocations,
                    ));
                }
            }
        }
    }

    public function getContent()
    {
        if (Tools::isSubmit('submitWarehouseForm')) {
        	$output_errors = array();
            Configuration::updateValue(
                'PS_ADVANCED_STOCK_MANAGEMENT',
                (int)Tools::getValue('PS_ADVANCED_STOCK_MANAGEMENT')
            );
            Configuration::updateValue(
                'WKWAREHOUSE_DEFAULT_NEW_PRODUCT',
                (int)Tools::getValue('WKWAREHOUSE_DEFAULT_NEW_PRODUCT')
            );
			// Check for errors
			if (Tools::getIsset('WKWAREHOUSE_PAGINATION_NUMBER_LINKS') &&
				is_numeric(Tools::getValue('WKWAREHOUSE_PAGINATION_NUMBER_LINKS')) == false) {
                $output_errors[] = '- '.$this->l('Pagination links number: Invalid number !');
            }
			if (Tools::getValue('WKWAREHOUSE_MODE_MULTICARRIER_CHOICE') == false) {
                $output_errors[] = '- '.$this->l('Choose how to present carriers (See checkout page settings).');
			}
			// If no errors
			if (empty($output_errors)) {
				foreach ($this->keyInfos as $key => $type) {
					$dbKey = static::CONFIG_KEY.$key;
					$dbValue = Tools::getValue($dbKey);
					Configuration::updateValue($dbKey, ($type == 'int' ? (int)$dbValue : $dbValue));
				}

				$warehouseBox = Tools::getValue('warehouseBox');
				if (is_array($warehouseBox) && count($warehouseBox)) {
					Configuration::updateValue('WKWAREHOUSE_PRIORITY', implode(',', $warehouseBox));
				}
				$warehouseBox = Tools::getValue('warehouseDecreaseBox');
				if (is_array($warehouseBox) && count($warehouseBox)) {
					Configuration::updateValue('WKWAREHOUSE_PRIORITY_DECREASE', implode(',', $warehouseBox));
				}

				Tools::redirectAdmin(
					AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&conf=6'
				);
				exit();
			}
        } elseif (Tools::getIsset('dismissRating')) {// Rating process
            WorkshopAsm::cleanBuffer();
            Configuration::updateValue('WKWAREHOUSE_DISMISS_RATING', 1);
            die;
        }
		$suffix = '<';
		$prefix = '>';
		$antislash = '/';
        return (
			!empty($output_errors) ? $this->displayError(implode($suffix.str_replace(' ', '', 'b r').$prefix, $output_errors)) : ''
		).$this->showWarningMessage().$this->prepareTabsHeader().$this->renderForm()
		.$suffix.$antislash.str_replace(' ', '', 'd i v').$prefix;
    }

    public function prepareTabsHeader()
    {
        return $this->display(__FILE__, 'views/templates/admin/_configure/helpers/form/tabs.tpl');
    }

    public function renderForm()
    {
        $radioOptions = array(
            array('id' => 'active_on', 'value' => 1, 'label' => $this->l('Enabled')),
            array('id' => 'active_off', 'value' => 0, 'label' => $this->l('Disabled'))
        );
        $submitBtn = array('title' => $this->l('Save'), 'class' => 'btn btn-default pull-right');

        $warehouse_list = StoreHouse::getWarehouses();
        $warehouse_no = array(array('id_warehouse' => 0, 'name' => $this->l('No default warehouse')));
        $warehouse_list = array_merge($warehouse_no, $warehouse_list);

        // G E N E R A L   S E T T I N G S
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('General Settings'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable advanced stock management'),
                    'class' => 't',
                    'name' => 'PS_ADVANCED_STOCK_MANAGEMENT',
                    'desc' => $this->l('Allows you to manage warehouses / locations / physical stock for your products.'),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Default warehouse on new products'),
                    'class' => 'fixed-width-xxl',
                    'name' => 'WKWAREHOUSE_DEFAULT_NEW_PRODUCT',
                    'desc' => $this->l('Automatically set a default warehouse when new product is created').'.',
                    'options' => array(
                        'query' => $warehouse_list,
                        'id' => 'id_warehouse',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Use advanced stock on new products'),
                    'name' => 'WKWAREHOUSE_USE_ASM_NEW_PRODUCT',
                    'desc' => $this->l('Use by default the advanced stock management system when new product is created').'.',
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable FONT AWESOME'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_ENABLE_FONTAWESOME',
                    'desc' => array(
						$this->l('Activate FONT AWESOME library which is responsible of showing icons.'),
                    	$this->l('Disable if your theme is already using this library to avoid conflicts.')
					),
                    'values' => $radioOptions
                ),
            ),
            'submit' => $submitBtn
        );

        // B A C K O F F I C E   S E T T I N G S
        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Backoffice Display Settings'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'free',
                    'label' => $this->l('Order Details Page Settings'),
                    'name' => 'option_settings'
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display warehouses, locations infos'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_LOCATION_ORDER_PAGE',
                    'desc' => $this->l('Display for each ordered product the assigned warehouse and location.'),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Set / Change warehouse association'),
                    'class' => 't',
                    'disabled' => Configuration::get('WKWAREHOUSE_LOCATION_ORDER_PAGE') ? false : true,
                    'name' => 'WKWAREHOUSE_CHANGE_ORDER_WAREHOUSE',
                    'desc' => array(
						$this->l('Let me change the warehouse association of each product using advanced stock management from the list directly.'),
                   		$this->l('The warehouses list will be loaded according to the already associated warehouses and the assigned order carrier.'),
					),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Show warehouses quantities during product search'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_STOCKSINFOS_ORDER_PAGE',
                    'desc' => array(
						$this->l('Display for a selected product/combination the detailed informations of quantities in each warehouse during creating/editing an order.'),
                   		$this->l('This happen during searching product through the autocomplete system.'),
					),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'free',
                    'label' => $this->l('Delivery Slip Settings'),
                    'name' => 'option_settings'
                ),
                array(
                    'type' => 'switch', //Activate to insert the location of each product of an order in the warehouse as additional information in the delivery note (PDF)
                    'label' => $this->l('Insert the product location'),
                    'name' => 'WKWAREHOUSE_ON_DELIVERY_SLIP',
                    'desc' => $this->l('Enable to insert the location of each product in warehouse of customer order as additional information in Delivery Slip PDF document').'.',
                    'hint' => $this->l('Remember that you can generate delivery slip only when customer order take the "Processing in progress" status').'.',
                    'values' => $radioOptions
                ),
            ),
            'submit' => $submitBtn
        );
		// For pagination
        if (Combination::isFeatureActive()) {
			$this->fields_form[1]['form']['input'][] = array(
				'type' => 'free',
				'label' => $this->l('‹‹ Manage Products/Warehouses ›› page Settings'),
				'name' => 'option_settings'
			);
			$this->fields_form[1]['form']['input'][] = array(
				'type' => 'switch',
				'label' => $this->l('Use pagination for combinations list'),
				'name' => 'WKWAREHOUSE_PAGINATION_USE',
				'desc' => $this->l('Enable this option to speed up the loading of the page when there are a lot of combinations to display per product.'),
				'values' => $radioOptions
			);
			$this->fields_form[1]['form']['input'][] = array(
				'type' => 'select',
				'label' => $this->l('Rows number in pagination'),
				'name' => 'WKWAREHOUSE_PAGINATION_LIMIT',
				'options' => array(
					'query' => $this->pagination_lengths,
					'id' => 'id',
					'name' => 'name'
				),
				'desc' => $this->l('Choose the number of combinations to display per product.'),
			);
			$this->fields_form[1]['form']['input'][] = array(
				'type' => 'text',
				'label' => $this->l('Pagination links number'),
				'name' => 'WKWAREHOUSE_PAGINATION_NUMBER_LINKS',
                'class' => 'fixed-width-sm',
				'required' => true,
				'desc' => $this->l('Define the number of pagination links to display in order to facilitate the navigation.'),
			);
		}

        // I N C R E A S E   P R I O R I T Y   S E T T I N G S
        $this->fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->l('Define priority in case of an increase of stock (If product is stored in different warehouses)'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'free',
                    'label' => $this->l('These options are used only if it\'s about a supply / replenishment / increase / etc. movements.'),
                    'name' => 'option_warnings'
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Use stock priority first'),
                    'name' => 'WKWAREHOUSE_STOCKPRIORITY_INC',
                    'desc' => $this->l('If enabled, it will be the warehouse with less stock that will be selected').'.',
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'priority_increase',
                    'label' => $this->l('Warehouses priority'),
                    'name' => 'priority',
                ),
            ),
            'submit' => $submitBtn
        );

        // D E C R E A S E   P R I O R I T Y   S E T T I N G S
        $this->fields_form[3]['form'] = array(
            'legend' => array(
                'title' => $this->l('The priority in case of a decrease of stock (If product is stored in different warehouses)'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'free',
                    'label' => $this->l('These priority parameters are used only if it\'s about a movement decrease of stock').'.'
                    .'\n'.$this->l('If Frontoffice movements such as cart / order placement / etc., the priority parameters will be applied unless you give to your customers the ability to choose the target warehouse  from a list').'.',
                    'name' => 'option_warnings'
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Use stock priority first'),
                    'name' => 'WKWAREHOUSE_STOCKPRIORITY_DEC',
                    'desc' => $this->l('If enabled, it will be the warehouse with enough stock that will be selected').'.',
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'priority_decrease',
                    'label' => $this->l('Warehouses priority'),
                    'name' => 'priority',
                ),
            ),
            'submit' => $submitBtn
        );

        // P R O D U C T   P A G E   S E T T I N G S
        $this->fields_form[4]['form'] = array(
            'legend' => array(
                'title' => $this->l('Product Page Display Settings').' (Frontoffice)',
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Show out of stock warehouses'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_SHOW_OUTOFSTOCK',
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'free',
                    'label' => $this->l('Warehouse Infos Display Settings'),
                    'name' => 'option_settings'
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Position'),
                    'name' => 'WKWAREHOUSE_WAREHOUSEINFOS_POSITION',
                    'options' => array(
                        'query' => array(
                            array('id' => 'afterCart', 'name' => $this->l('Just after cart button')),
                            array(
                                'id' => 'extraContent',
                                'name' => $this->l('Product tabs (displayProductExtraContent hook)'),
                            ),
                            array('id' => 'none', 'name' => $this->l('None')),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display locations'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_DISPLAY_LOCATION',
                    'desc' => $this->l('Display the location information in each warehouse.'),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display available quantities'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_DISPLAY_STOCK_INFOS',
                    'desc' => $this->l('Display the stored available quantity in each warehouse.'),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'label' => '',
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_DISPLAY_STOCK_ICON',
                    'desc' => $this->l('Display icon instead of the warehouse quantity.'),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display deliveries times'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_DISPLAY_DELIVERIES_TIME',
                    'desc' => $this->l('Display the delivery time of each warehouse.'),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display countries'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_DISPLAY_COUNTRIES',
                    'desc' => $this->l('Display country of each warehouse.'),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'free',
                    'label' => $this->l('Warehouses as combination'),
                    'name' => 'option_settings'
                ),
                array(
                    'type' => 'switch',
                    'class' => 't',
                    'disabled' => Configuration::get('WKWAREHOUSE_DISPLAY_SELECTED_WAREHOUSE') ? true : false,
                    'name' => 'WKWAREHOUSE_ALLOWSET_WAREHOUSE',
                    'label' => $this->l('Allow choosing warehouse'),
                    'desc' => $this->l('Allow your visitors and customers choosing a warehouse from a dropdown list (like a combination).'),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'class' => 't',
                    'disabled' => Configuration::get('WKWAREHOUSE_ALLOWSET_WAREHOUSE') ? true : false,
                    'name' => 'WKWAREHOUSE_DISPLAY_SELECTED_WAREHOUSE',
                    'label' => $this->l('Display the best warehouse'),
                    'desc' => array(
						$this->l('Display to your visitors and customers automatically the best selected warehouse.'),
                    	$this->l('Disable the option above to enable handling this feature.')
					),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'free',
                    'label' => '',
                    'name' => 'separator'
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display warehouse name'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_DISPLAY_WAREHOUSE_NAME',
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display location'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_DISPLAY_SELECTED_LOCATION',
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display available quantity'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_DISPLAY_SELECTED_STOCK',
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display delivery time'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_DISPLAY_DELIVERYTIME',
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display country'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_DISPLAY_COUNTRY',
                    'values' => $radioOptions
                ),
            ),
            'submit' => $submitBtn
        );

        // C A R T   S E T T I N G S
        $this->fields_form[5]['form'] = array(
            'legend' => array(
                'title' => $this->l('Shopping Cart Page Settings'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'free',
                    'label' => $this->l('Cart product informations Settings (Left block)'),
                    'name' => 'option_settings'
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_ENABLE_INCART',
                    'desc' => $this->l('Display the warehouse informations of each product using the advanced stock management system in cart.'),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Position'),
                    'class' => 'fixed-width-xxl',
                    'name' => 'WKWAREHOUSE_POSITION_INCART',
                    'options' => array(
                        'query' => array(
                            array('id' => 'belowProductName', 'name' => $this->l('Just below the product name')),
                            array('id' => 'belowCartLine', 'name' => $this->l('Just below product cart line')),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display warehouses names'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_WAREHOUSES_INCART',
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display locations'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_LOCATIONS_INCART',
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display available quantities'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_QUANTITIES_INCART',
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display deliveries times'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_DELIVERYTIMES_INCART',
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display countries'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_COUNTRIES_INCART',
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'free',
                    'label' => $this->l('Summary cart (Right block)'),
                    'name' => 'option_settings'
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Display the delivery address'),
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_DELIVERY_ADDRESS_INCART',
                    'desc' => array(
						$this->l('If enabled, this option allows your customers to see the current delivery address in the right block.'),
                    	$this->l('Available only if you do not allow the multi-delivery addresses.')
					),
                    'values' => $radioOptions
                ),
            ),
            'submit' => $submitBtn
        );

        // C H E C K O U T   S E T T I N G S
        $this->fields_form[6]['form'] = array(
            'legend' => array(
                'title' => $this->l('Checkout Page Settings'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_ALLOW_MULTIWH_CART',
                    'label' => $this->l('Allow multi-warehouses'),
                    'desc' => array(
						'- '.$this->l('Allow adding products of different warehouses during checkout process.'),
                    	'- '.$this->l('If option disabled, you can add only products that are stored in the same warehouse.'),
                    	'- '.$this->l('Note: Product can only be ordered from one warehouse.')
					),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_ALLOW_MULTICARRIER_CART',
                    'label' => $this->l('Allow multi-carriers'),
                    'disabled' => Configuration::get('WKWAREHOUSE_ALLOW_MULTIWH_CART') ? false : true,
                    'desc' => array(
						$this->l('If enabled, this option allow adding products of different carriers during checkout process.'),
                    	$this->l('Finally, it could convert the customer\'s cart into one or more orders.')
					),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_SENDMAIL_EACH_EMPLOYEE',
                    'label' => $this->l('Send an email to each warehouse manager'),
                    'desc' => array(
						$this->l('If enabled, send an order summary notification by email for each warehouse manager after a new order is placed.'),
                    	$this->l('The employee manager can be set for each warehouse through "Manage Warehouses" section, so each one can handle his orders.'),
					),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'free',
                    'label' => $this->l('If multi-carriers and multi-warehouses options above are enabled and no common carrier between warehouses during checkout'),
                    'name' => 'option_settings'
                ),
                array(
                    'type' => 'switch',
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_ALLOW_MULTICARRIER_CHOICE',
                    'label' => $this->l('Allow multiple choice of carriers for each warehouse (package)'),
                    'desc' => array(
						$this->l('If enabled, this lets your customer choosing for each warehouse the carrier he wants among a list during checkout process.'),
                    	$this->l('In other words, in the selection part of shipment, the customer can choose for each package the carrier that suits his need from a list.'),
						$this->l('Otherwise, it will be only combinations of the best carrier of each package (best price and grade).'),
					),
                    'values' => $radioOptions
                ),
                array(
					'type' => 'radio',
					'label' => $this->l('Choose how to present carriers'),
					'name' => 'WKWAREHOUSE_MODE_MULTICARRIER_CHOICE',
					'class' => 'shipment-mode',
					'values' => WorkshopAsm::scanFolder('shipment'),
                    'desc' => array(
						$this->l('The "Carriers by warehouses" feature is available only if you allow multiple choice of carriers for each warehouse (option just above).'),
					),
                ),
                array(
                    'type' => 'switch',
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_PRODUCT_NAME_SHIPMENT_PART',
                    'label' => $this->l('Show products names below each carrier'),
                    'disabled' => Configuration::get('WKWAREHOUSE_ALLOW_MULTIWH_CART') && Configuration::get('WKWAREHOUSE_ALLOW_MULTICARRIER_CART') ? false : true,
                    'desc' => array(
						$this->l('If enabled, display below each carrier name the related products (names) during checkout process.'),
                    	$this->l('In other words, in the selection part of shipment, the customer can see the products that will be sent by each carrier.'),
						$this->l('This option is available only if you choose to present carriers as combinations.'),
					),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_WH_NAME_SHIPMENT_PART',
                    'label' => $this->l('Show warehouse name below each carrier'),
                    'disabled' => Configuration::get('WKWAREHOUSE_ALLOW_MULTIWH_CART') && Configuration::get('WKWAREHOUSE_ALLOW_MULTICARRIER_CART') ? false : true,
                    'desc' => array(
						$this->l('If enabled, during checkout process in the selection part of shipment, display below each carrier the warehouse name from whence the order will leave.'),
						$this->l('Available only if you choose to present carriers as combinations.'),
					),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'switch',
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_PRODUCT_NOT_ASM_GET_BEST_CARRIERS',
                    'label' => $this->l('Get the best carriers for Non-ASM Products'),
                    'desc' => array(
						$this->l('If enabled, during checkout process in the selection part of shipment, make available only the best carriers (best price and grade) for non advanced stock management products.'),
						$this->l('Otherwise, customer can choose among a list the best carrier that suits his need.'),
					),
                    'values' => $radioOptions
                ),
                array(
                    'type' => 'free',
                    'label' => $this->l('Multi delivery addresses'),
                    'name' => 'option_settings'
                ),
                array(
                    'type' => 'switch',
                    'class' => 't',
                    'name' => 'WKWAREHOUSE_ALLOW_MULTI_ADDRESSES',
                    'label' => $this->l('Allow'),
                    'desc' => array(
						$this->l('If enabled, this option allows your customers to ship orders to multiple addresses.'),
                    	$this->l('Therefore, during payment process, in the selection part of addresses, the customer can choose a delivery address for each package.'),
						$this->l('Available only if you allow the multi-carriers option above.'),
					),
                    'values' => $radioOptions
                ),
            ),
            'submit' => $submitBtn
        );

        // Cron jobs  S E T T I N G S
        $this->fields_form[7]['form'] = array(
            'legend' => array(
                'title' => $this->l('Cron Jobs Settings'),
                'icon' => 'icon-time'
            ),
            'input' => array(
                array(
                    'type' => 'cronjob_fix_infos',
                    'label' => '',
                    'name' => 'cronjob_infos',
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('How to fix quantities'),
                    'name' => 'WKWAREHOUSE_WAY_FIX_QUANTITIES',
                    'class' => 'fixed-width-wide',
                    'options' => array(
                        'query' => array(
                            array(
                                'value' => 'alignQtiesToPrestashop',
                                'name' => $this->l('Fix warehouses quantities to be the same as the Prestashop physical quantities')
                            ),
                            array(
                                'value' => 'alignQtiesToWarehouses',
                                'name' => $this->l('Fix Prestashop quantities to be the same as the warehouses quantities')
                            ),
                        ),
                        'id' => 'value',
                        'name' => 'name'
                    ),
                    'identifier' => 'id',
                    'desc' => $this->l('Choose how you want to fix the gap between the warehouses and Prestashop quantities.')
                ),
                array(
                    'type' => 'cronjob_fix_asm',
                    'label' => $this->l('Cron infos'),
                    'name' => 'cronjob',
                ),
            ),
            'submit' => array('class' => 'btn btn-default pull-right', 'title' => $this->l('Save'))
        );

        // M O D U L E S   L I N K S
        $this->fields_form[8]['form'] = array(
            'legend' => array(
                'title' => $this->l('Other Related Modules'),
                'icon' => 'icon-link'
            ),
            'input' => array(array('type' => 'free', 'label' => '', 'name' => 'other_modules_tab'))
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = (
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') :
            0
        );
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitWarehouseForm';
        $helper->name_controller = 'formConfigWarehouses';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
        '&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        // Prepare warehouses positions priorities
        array_shift($warehouse_list);
        $warehouse_list_increase = array();
        if (Configuration::get('WKWAREHOUSE_PRIORITY')) {
            $ids_warehouses = explode(',', Configuration::get('WKWAREHOUSE_PRIORITY'));
            foreach ($ids_warehouses as $id_warehouse) {
                $warehouse = new StoreHouse($id_warehouse, $this->context->language->id);
                if (Validate::isLoadedObject($warehouse) && !$warehouse->deleted) {
                    $warehouse_item = array(
                        'id_warehouse' => $warehouse->id,
                        'name' => $warehouse->reference.' - '.$warehouse->name
                    );
                    array_push($warehouse_list_increase, $warehouse_item);
                }
            }
        }
        if (empty($warehouse_list_increase)) {
            $warehouse_list_increase = $warehouse_list;
        }
        $warehouse_list_decrease = array();
        if (Configuration::get('WKWAREHOUSE_PRIORITY_DECREASE')) {
            $ids_warehouses = explode(',', Configuration::get('WKWAREHOUSE_PRIORITY_DECREASE'));
            foreach ($ids_warehouses as $id_warehouse) {
                $warehouse = new StoreHouse($id_warehouse, $this->context->language->id);
                if (Validate::isLoadedObject($warehouse) && !$warehouse->deleted) {
                    $warehouse_item = array(
                        'id_warehouse' => $warehouse->id,
                        'name' => $warehouse->reference.' - '.$warehouse->name
                    );
                    array_push($warehouse_list_decrease, $warehouse_item);
                }
            }
        }
        if (empty($warehouse_list_decrease)) {
            $warehouse_list_decrease = $warehouse_list;
        }
        /*****************************************/

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'warehouses_increase' => $warehouse_list_increase,
            'warehouses_decrease' => $warehouse_list_decrease,
            'cron_url' =>  $this->getCronUrl(),
            'cron_lunched_by' => (function_exists('curl_init') ? 'curl'.(Configuration::get('PS_SSL_ENABLED') ? ' -k' : null) : 'php -f'),
            'module_path' => $this->_path,
        );
        return $helper->generateForm($this->fields_form);
    }

    public function getCronUrl()
    {
        $admin_folder = str_replace(_PS_ROOT_DIR_.'/', '', basename(_PS_ADMIN_DIR_));
        if (version_compare(_PS_VERSION_, '1.7', '<') == true) {
            $path = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.$admin_folder.'/';
            $curl_url = $path.Context::getContext()->link->getAdminLink('AdminWkwarehousestaskrun', true);
        } else {
            $curl_url = Context::getContext()->link->getAdminLink('AdminWkwarehousestaskrun', true);
        }
		$curl_url .= '&secure_key='.Configuration::get('WKWAREHOUSE_SECURE_KEY');
		return $curl_url;
    }

    public function ajaxProcessCheckExpression()
    {
        try {
            $json = $this->getJsonScheduleExpression();
            if (!$json) {
                throw new Exception('Not valid');
            }
            die(json_encode($json));
        } catch (Exception $e) {
            die(json_encode(array(
                'error' => $e->getMessage()
            )));
        }
    }

    public function getJsonScheduleExpression($planification = null)
    {
        include_once(dirname(__FILE__).'/libs/Schedule/CrontabValidator.php');
        include_once(dirname(__FILE__).'/libs/Schedule/CronSchedule.php');
        include_once(dirname(__FILE__).'/libs/Schedule/csd_parser.php');

        $json = array();
        $expression = (Tools::getIsset('expression') ? Tools::getValue('expression') : $planification);
        $expression = trim($expression);
        $expression = preg_replace('#\s+#', ' ', $expression);
        $expression = WorkshopAsm::convertSpecialExpression($expression);

        $validator = new CrontabValidator();
        if (!$validator->isExpressionValid($expression)) {
            return false;
        }

        $parser = new csd_parser($expression);

        $expressionByPart = explode(' ', $expression);

        $expression = $expression.' *';
        $schedule = CronSchedule::fromCronString($expression);

        $json['human_description'] = $schedule->asNaturalLanguage();
        $json['next_run'] = date($this->context->language->date_format_full, $parser->get());
        $json['expression']['min'] = $expressionByPart[0];
        $json['expression']['hour'] = $expressionByPart[1];
        $json['expression']['day_of_month'] = $expressionByPart[2];
        $json['expression']['month'] = $expressionByPart[3];
        $json['expression']['day_of_week'] = $expressionByPart[4];
        return $json;
    }

    public function getConfigFieldsValues()
    {
        $configs_array = array();
        foreach ($this->keyInfos as $key => $type) {
            unset($type);
            $dbKey = static::CONFIG_KEY.$key;
            $configs_array[$dbKey] = Tools::getValue($dbKey, Configuration::get($dbKey));
        }
        return array_merge($configs_array, array(
            'PS_ADVANCED_STOCK_MANAGEMENT' => Tools::getValue('PS_ADVANCED_STOCK_MANAGEMENT', Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')),
            'WKWAREHOUSE_DEFAULT_NEW_PRODUCT' => Tools::getValue('WKWAREHOUSE_DEFAULT_NEW_PRODUCT', Configuration::get('WKWAREHOUSE_DEFAULT_NEW_PRODUCT')),
            'option_settings' => '',
            'option_warnings' => '',
            'separator' => '',
            'other_modules_tab' => $this->otherModulesTab(),
        ));
    }

    public function otherModulesTab()
    {
        $this->context->smarty->assign(array(
			'module_folder' => $this->_path,
            'iso_code' => $this->context->language->iso_code,
		));
        return $this->display(__FILE__, 'views/templates/admin/other_modules_tab.tpl');
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (!$this->active) {
            return;
        }
        // Force loading jquery before
        $this->context->controller->addjQuery();

        $ctrl = Tools::strtolower(Tools::getValue('controller'));
        $allowed_controllers = array(
            'adminwkwarehousesmanageqty',
            'adminwkwarehousesdash',
            'adminwkwarehousesorders',
        );
        $isConfigPage = ($ctrl == 'adminmodules' && Tools::getValue('configure') == $this->name ? true : false);

        // Add warehouses behaviours to product sheet to be able to edit quantities by product/combination
        if ($isConfigPage || $ctrl == 'adminproducts' || in_array($ctrl, $allowed_controllers)) {
            // Load css
            $this->context->controller->addCSS($this->_path.'views/css/wkwarehouses-admin.css', 'all');
            if ($isConfigPage || in_array($ctrl, $allowed_controllers)) {
                if ($isConfigPage) {
                    $this->context->controller->addJqueryUI(array('ui.tabs', 'ui.tooltip'));
               }
                $this->context->controller->addJS($this->_path.'views/js/wkwarehouses-admin.min.js');
                Media::addJsDefL('trans_syntax_error', $this->l('Syntax error'));
            }
        }
        if (in_array($ctrl, array('adminwkwarehousesstockmvt', 'adminwkwarehousesstockinstantstate'))) {
            // Load css
            $this->context->controller->addCSS($this->_path.'views/css/wkwarehouses-admin.css', 'all');
        }

        if (in_array($ctrl, array('adminorders', 'adminmanagewarehouses', 'adminwkwarehousesorders'))) {
            $this->context->controller->addJS($this->_path.'views/js/wkwarehouses-admin.min.js');
            if ($ctrl == 'adminorders' || $ctrl == 'adminwkwarehousesorders') {
                Media::addJsDefL('txt_no_warehouse', $this->l('No warehouse'));
                Media::addJsDefL('txt_location', $this->l('Location'));
                Media::addJsDefL('txt_warehouse', $this->l('Warehouse'));
                Media::addJsDef(array(
                    'canChangeWarehouse' => (int)Configuration::get('WKWAREHOUSE_CHANGE_ORDER_WAREHOUSE'),
                    'showProductWarehousesQuantities' => (int)Configuration::get('WKWAREHOUSE_STOCKSINFOS_ORDER_PAGE'),
                    'admin_warehouses_orders_url' => $this->context->link->getAdminLink('AdminWkwarehousesOrders'),
                ));
            }
        }
        if (in_array($ctrl, array('adminwkwarehousesmanageqty', 'adminwkwarehousesorders'))) {
            return $this->display(__FILE__, 'views/templates/admin/commun_header.tpl');
        }
    }

	/*
	 * Prestashop 1.7.7.0 and greater
     * If the order has been shipped, then the available action will be return product.
     * If the order has been paid but not shipped yet, then the available action will be standard refund.
     * In all other cases, the available action is cancel product.
	 * The Partial refund button is available once a payment was made on the order, it can exist jointly with other refunds, and it exists even if the “Product returns” option was not enabled.
	*/
    public function hookActionProductCancel($params)
    {
        if (!$this->active || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            return;
        }
        $order = $params['order'];
        $order_detail = new OrderDetail($params['id_order_detail']);
        if (!$order_detail || !Validate::isLoadedObject($order_detail)) {
            return;
        }
        if (!(new Product((int)$order_detail->product_id, false))->advanced_stock_management) {
            return;
        }

		$action = $params['action'];
		$restockRefundedProducts = false;

		// Partial Refund | Standard Refund | Return Product
		$cancel_quantity = '';
		if (Tools::getIsset('cancel_product') && Tools::getValue('cancel_product')) {
			$cancel_product_array = Tools::getValue('cancel_product');
			// should we reinject?
			if ($action === CancellationActionType::PARTIAL_REFUND || $action === CancellationActionType::RETURN_PRODUCT) {
				$restockRefundedProducts = (int)$cancel_product_array['restock'];
			} elseif ($action === CancellationActionType::STANDARD_REFUND) {
				$restockRefundedProducts = (int)$cancel_product_array['selected_'.$order_detail->id];
			}
			// quantity to refund
			$cancel_quantity = (int)$cancel_product_array['quantity_'.$order_detail->id];
		}

		// Reinject quantity
		if (($action === CancellationActionType::PARTIAL_REFUND && (!$order->hasBeenDelivered() || $restockRefundedProducts)) ||// partial refund
			($action === CancellationActionType::STANDARD_REFUND && $restockRefundedProducts) ||// standard refund
			($action === CancellationActionType::RETURN_PRODUCT && $restockRefundedProducts)) {// return product refund
			/* process */
			$qty_cancel_product = isset($params['cancel_quantity']) ? $params['cancel_quantity'] : $cancel_quantity;
			(new WorkshopAsm())->reinjectRefundedQuantity($order_detail, $qty_cancel_product);
		}
    }

    public function hookDisplayReassurance()
    {
        if (!$this->active) {
            return;
        }
        // Display the current delivery address in the right block of cart page
        if (Configuration::get('WKWAREHOUSE_DELIVERY_ADDRESS_INCART') &&
            !Configuration::get('WKWAREHOUSE_ALLOW_MULTI_ADDRESSES')) {
            $cart = $this->context->cart;
            if (Dispatcher::getInstance()->getController() == 'cart' &&
				Validate::isLoadedObject($cart) && $cart->id_address_delivery) {
                $delivery_address = $this->context->customer->getSimpleAddress($cart->id_address_delivery);
                $this->context->smarty->assign(
                    'delivery_address',
                    $delivery_address ? AddressFormat::generateAddress(new Address($cart->id_address_delivery), array(), '<br>') : ''
                );
                return $this->display(__FILE__, 'displayRightColumnCart.tpl');
            }
        }
    }

    public function showWarningMessage($display_rate = true)
    {
        if (Configuration::get('WKWAREHOUSE_DISMISS_RATING') != 1 &&
            WorkshopAsm::getNbDaysModuleUsage() >= 2 && $display_rate) {
            $this->context->smarty->assign('show_rating_block', true);
        }

        $missing_overrides = array();
        if (!Configuration::get('PS_DISABLE_OVERRIDES')) {// If override allowed
            foreach ($this->my_overrides as $override) {
                if (!file_exists($override['target'])) {
                    $missing_overrides[] = $override;
                }
            }
        }
        $this->context->smarty->assign(array(
            'missing_overrides' => $missing_overrides,
            'link' => $this->context->link,
        ));
        return $this->display(__FILE__, 'views/templates/admin/messages_info.tpl');
    }

    /**
     * Install overrides files
     *
     * @return bool
     */
    public function installMyOverrides()
    {
        foreach ($this->my_overrides as $override) {
            if (!file_exists($override['target']) && is_writable($override['targetdir'])) {
                if (!Tools::copy($override['source'], $override['target'])) {
                    //throw new Exception(Tools::displayError('Can not copy '.$override['source'].' to '.$override['target']));
                    return false;
                }
            }
        }
        $this->emptyClassIndexCache();
    }

    /**
     * Uninstall overrides files
     *
     * @return bool
     */
    public function uninstallMyOverrides()
    {
        foreach ($this->my_overrides as $override) {
            if (file_exists($override['target'])) {
                // If the same file
                if (crc32(Tools::file_get_contents($override['target'])) == crc32(Tools::file_get_contents($override['source']))) {
                    unlink($override['target']);
                }
            }
        }
        $this->emptyClassIndexCache();
    }

    public function emptyClassIndexCache()
    {
        $cache_dir = (_PS_MODE_DEV_ ? 'dev' : 'prod');

        if (file_exists(_PS_ROOT_DIR_.'/app/cache/'.$cache_dir.'/class_index.php')) {
            @unlink(_PS_ROOT_DIR_.'/app/cache/'.$cache_dir.'/class_index.php');
        }
        if (file_exists(_PS_ROOT_DIR_.'/var/cache/'.$cache_dir.'/class_index.php')) {
            @unlink(_PS_ROOT_DIR_.'/var/cache/'.$cache_dir.'/class_index.php');
        }
    }
}
