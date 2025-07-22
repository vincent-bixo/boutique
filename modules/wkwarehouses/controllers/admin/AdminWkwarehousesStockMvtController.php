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

class AdminWkwarehousesStockMvtController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->table = 'warehouse_stock_mvt';
        $this->className = 'WarehouseStockMvt';
        $this->identifier = 'id_stock_mvt';
        $this->lang = false;
        $this->multishop_context = Shop::CONTEXT_ALL;

        $this->list_no_link = true;
        $this->displayInformation('- '.$this->l('This interface allows you to display the stock movement for a selected warehouse.').'<br />');
        $this->displayInformation('- '.$this->l('Choose a warehouse to be able to make a CSV export.'));

        $this->fields_list = array(
            'id_product' => array(
                'title' => $this->l('Product ID'),
                'type' => 'int',
                'havingFilter' => true
            ),
            'product_name' => array(
                'title' => $this->l('Name'),
                'havingFilter' => true
            ),
            'product_reference' => array(
                'title' => $this->l('Reference'),
                'havingFilter' => true
            ),
            'product_ean13' => array(
                'title' => $this->l('EAN 13'),
                'havingFilter' => true
            ),
            'product_upc' => array(
                'title' => $this->l('UPC'),
                'havingFilter' => true
            ),
            'warehouse_name' => array(
                'title' => $this->l('Warehouse'),
                'havingFilter' => false,
                'orderby' => true,
                'search' => false,
            ),
            'sign' => array(
                'title' => $this->l('Sign'),
                'align' => 'center',
                'type' => 'select',
                'filter_key' => 'a!sign',
                'list' => array(
                    '1' => $this->l('Increase'),
                    '-1' => $this->l('Decrease'),
                ),
                'icon' => array(
                    -1 => array(
                        'src' => 'remove_stock.png',
                        'alt' => $this->l('Increase'),
                    ),
                    1 => array(
                        'src' => 'add_stock.png',
                        'alt' => $this->l('Decrease'),
                    )
                ),
                'class' => 'fixed-width-xs'
            ),
            'physical_quantity' => array(
                'title' => $this->l('Quantity'),
                'type' => 'int',
                'align' => 'center',
                'filter_key' => 'a!physical_quantity',
                'class' => 'fixed-width-sm'
            ),
            'price_te' => array(
                'title' => $this->l('Unit price').'<br />'.$this->l('(tax excl.)'),
                'type' => 'price',
                'currency' => true,
                'filter_key' => 'a!price_te'
            ),
            'reason' => array(
                'title' => $this->l('Label'),
                'havingFilter' => true
            ),
            'employee' => array(
                'title' => $this->l('Employee'),
                'havingFilter' => true
            ),
            'date_add' => array(
                'title' => $this->l('Date'),
                'type' => 'datetime',
                'filter_key' => 'a!date_add'
            ),
        );

        parent::__construct();
    }

    public function renderList()
    {
        // removes toolbar btn
        $this->toolbar_btn = array();

        // overrides select
        $this->_select = '
            pl.id_product,
            CAST(
                 CONCAT(pl.name, \' \', GROUP_CONCAT(IFNULL(al.name, \'\'), \'\')) AS CHAR CHARACTER SET utf8
            ) as product_name,
            CONCAT(a.employee_lastname, \' \', a.employee_firstname) as employee,
            mrl.name as reason,
            stock.reference as product_reference,
            stock.ean13 as product_ean13,
            stock.upc as product_upc,
            w.id_currency as id_currency,
            wl.name as warehouse_name
        ';

        // overrides join
        $this->_join = '
            INNER JOIN '._DB_PREFIX_.'stock stock ON a.id_stock = stock.id_stock
            LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
                stock.id_product = pl.id_product AND
                pl.id_lang = '.(int)$this->context->language->id.Shop::addSqlRestrictionOnLang('pl').'
            )
            LEFT JOIN `'._DB_PREFIX_.'stock_mvt_reason_lang` mrl ON (
                a.id_stock_mvt_reason = mrl.id_stock_mvt_reason AND
                mrl.id_lang = '.(int)$this->context->language->id.'
            )
            LEFT JOIN `'._DB_PREFIX_.'warehouse` w ON (w.id_warehouse = stock.id_warehouse)
            LEFT JOIN `'._DB_PREFIX_.'warehouse_lang` wl ON (
                w.`id_warehouse` = wl.`id_warehouse` AND
                wl.`id_lang` = '.(int)$this->context->language->id.'
            )
            LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (
                pac.id_product_attribute = stock.id_product_attribute
            )
            LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (
                al.id_attribute = pac.id_attribute AND 
                pac.id_product_attribute <> 0 AND 
                al.id_lang = '.(int)$this->context->language->id.'
            )
        ';
        // overrides group
        $this->_group = 'GROUP BY a.id_stock_mvt';

        // overrides where depending on the warehouse
        $id_warehouse = (int)$this->getCurrentWarehouseId();
        if ($id_warehouse > 0) {
            $this->_where = ' AND w.id_warehouse = '.$id_warehouse;
            self::$currentIndex .= '&id_warehouse='.$id_warehouse;
        }

        $this->_orderBy = 'a.date_add';
        $this->_orderWay = 'DESC';

        // sets the current warehouse
        $this->tpl_list_vars['current_warehouse'] = $this->getCurrentWarehouseId();

        // sets the list of warehouses
        $warehouses = StoreHouse::getWarehouses();
        array_unshift($warehouses, array('id_warehouse' => -1, 'name' => $this->l('All Warehouses')));
        $this->tpl_list_vars['list_warehouses'] = $warehouses;

        // sets toolbar
        $this->initToolbar();

        // renders list
        $list = parent::renderList();

        // if export requested
        if (Tools::isSubmit('csv')) {
            if (count($this->_list) > 0) {
                $this->renderCSV();
                die;
            } else {
                $this->displayWarning($this->l('There is nothing to export as a CSV.'));
            }
        }

        return $list;
    }

    /**
     * AdminController::getList() override
     * @see AdminController::getList()
     */
    public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        if (Tools::isSubmit('csv')) {
            $limit = false;
        }
        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);

        //If there is a field product_name in the list, check if this field is null and display standard message
        foreach ($this->fields_list as $key => $value) {
            if ($key == 'product_name') {
                $nb_items = count($this->_list);

                for ($i = 0; $i < $nb_items; ++$i) {
                    $item = &$this->_list[$i];
                    if (empty($item['product_name'])) {
                        $item['product_name'] = $this->l('The name of this product is not available. It may have been deleted from the system.');
                    }
                }
            }
        }
    }

    /**
     * Exports CSV
     */
    public function renderCSV()
    {
        if (!$this->_list) {
            return;
        }

        // header
        if (Tools::getValue('id_warehouse') != -1) {
            $filename = $this->l('Stock movement').'_'.StoreHouse::getWarehouseNameById((int)Tools::getValue('id_warehouse')).'.csv';
        } else {
            $filename = $this->l('Stock movement').'.csv';
        }
        header('Content-type: text/csv');
        header('Cache-Control: no-store, no-cache');
        header('Content-disposition: attachment; filename="'.$filename);

        // puts keys
        $keys = array(
            'product_name',
            'product_reference',
            'product_ean13',
            'product_upc',
            'id_order',
            //'id_supply_order',
            'emloyee_firstname',
            'employee_lastname',
            'physical_quantity',
            'sign',
            'price_te',
            'label',
            'date_add',
        );
        echo sprintf("%s\n", implode(';', $keys));

        // puts rows
        foreach ($this->_list as $row) {
            $row_csv = array(
                $row['product_name'],
                $row['product_reference'],
                $row['product_ean13'],
                $row['product_upc'],
                $row['id_order'],
                //$row['id_supply_order'],
                $row['employee_firstname'],
                $row['employee_lastname'],
                $row['physical_quantity'],
                $row['sign'],
                Tools::ps_round($row['price_te'], 2),
                $row['reason'],
                $row['date_add'],
            );
            // puts one row
            echo sprintf("%s\n", implode(';', array_map(array('CSVCore', 'wrap'), $row_csv)));
        }
    }

    /**
     * Gets the current warehouse for this controller
     *
     * @return int warehouse_id
     */
    protected function getCurrentWarehouseId()
    {
        static $warehouse = 0;
        if ($warehouse == 0) {
            $warehouse = -1;
            if ((int)Tools::getValue('id_warehouse')) {
                $warehouse = (int)Tools::getValue('id_warehouse');
            }
        }
        return $warehouse;
    }

    public function initContent()
    {
        if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $this->warnings[md5('PS_ADVANCED_STOCK_MANAGEMENT')] = $this->l('You need to activate advanced stock management before using this feature.');
            return false;
        }
        parent::initContent();
    }

    public function init()
	{
        parent::init();
		$id_warehouse = $this->getCurrentWarehouseId();
		if (!empty($id_warehouse) && $id_warehouse != -1) {
			self::$currentIndex .= '&id_warehouse='.$id_warehouse;
			$this->context->smarty->assign('current', self::$currentIndex);
		}
    }

    public function initProcess()
    {
        if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $this->warnings[md5('PS_ADVANCED_STOCK_MANAGEMENT')] = $this->l('You need to activate advanced stock management before using this feature.');
            return false;
        }
        parent::initProcess();
    }

    /**
     * @see AdminController::initToolbar();
     */
    public function initToolbar()
    {
        if (Tools::isSubmit('id_warehouse') && (int)Tools::getValue('id_warehouse') != -1) {
            $this->toolbar_btn['export-stock-mvt-csv'] = array(
                'short' => 'Export this list as CSV',
                'href' => $this->context->link->getAdminLink('AdminWkwarehousesStockMvt').'&csv&id_warehouse='.(int)$this->getCurrentWarehouseId(),
                'desc' => $this->l('Export (CSV)'),
                'imgclass' => 'export'
            );
        }
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_title = $this->l('Stock movement');

        if (Tools::isSubmit('id_warehouse') && (int)Tools::getValue('id_warehouse') != -1) {
            $this->page_header_toolbar_btn['export-stock-mvt-csv'] = array(
                'short' => $this->l('Export this list as CSV', null, null, false),
                'href' => $this->context->link->getAdminLink('AdminWkwarehousesStockMvt').'&csv&id_warehouse='.(int)$this->getCurrentWarehouseId(),
                'desc' => $this->l('Export (CSV)', null, null, false),
                'imgclass' => 'export'
            );
        }
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
   			$translated = $this->translator->trans($string, [], 'Modules.Wkwarehouses.Adminwkwarehousesstockmvtcontroller');
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
