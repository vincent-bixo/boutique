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

class AdminWkwarehousesStockInstantStateController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->table = 'stock';
        $this->list_id = 'stock';
        $this->className = 'Stock';
        $this->tpl_list_vars['show_filter'] = true;
        $this->lang = false;
        $this->multishop_context = Shop::CONTEXT_ALL;

        $this->fields_list = array(
            'product_id' => array(
                'title' => $this->l('Product ID'),
                'type' => 'int',
                'havingFilter' => true,
                'filter_key' => 'p!id_product',
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'havingFilter' => true
            ),
            'reference' => array(
                'title' => $this->l('Reference'),
                'align' => 'center',
                'havingFilter' => true
            ),
            'ean13' => array(
                'title' => $this->l('EAN13'),
                'align' => 'center',
            ),
            'upc' => array(
                'title' => $this->l('UPC'),
                'align' => 'center',
            ),
            'price_te' => array(
                'title' => $this->l('Price (tax excl.)'),
                'orderby' => true,
                'search' => false,
                //'type' => 'price',
                //'currency' => true,
            ),
            'valuation' => array(
                'title' => $this->l('Valuation'),
                'orderby' => false,
                'search' => false,
                //'type' => 'price',
                //'currency' => true,
                'hint' => $this->l('Total value of the physical quantity. The sum (for all prices) is not available for all warehouses, please filter by warehouse.')
            ),
            'physical_quantity' => array(
                'title' => $this->l('Physical quantity'),
                'class' => 'fixed-width-xs',
                'align' => 'center',
                'orderby' => true,
                'search' => false
            ),
            /*'usable_quantity' => array(
                'title' => $this->l('Usable quantity'),
                'class' => 'fixed-width-xs',
                'align' => 'center',
                'orderby' => true,
                'search' => false,
            ),*/
        );
        if ($this->getCurrentCoverageWarehouse() != -1) {
            $this->fields_list['price_te']['type'] = 'price';
            $this->fields_list['price_te']['currency'] = true;
            $this->fields_list['valuation']['type'] = 'price';
            $this->fields_list['valuation']['currency'] = true;
        }

        $this->addRowAction('details');

        parent::__construct();
    }

    /**
     * AdminController::renderList() override
     * @see AdminController::renderList()
     */
    public function renderList()
    {
        $this->fields_list['real_quantity'] = array(
            'title' => $this->l('Real quantity'),
            'class' => 'fixed-width-xs',
            'align' => 'center',
            'orderby' => false,
            'search' => false,
            'hint' => '= ('.$this->l('Physical quantity - Customers orders').')',
        );

        // query
        $this->_select = '
            p.id_product as product_id,
            CAST(
                IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name SEPARATOR \', \')), pl.name) AS CHAR CHARACTER SET utf8
            ) as name,
            IFNULL(pa.reference, p.reference) as reference,
            IFNULL(pa.ean13, p.ean13) as ean13,
            IFNULL(pa.upc, p.upc) as upc,
            w.id_currency
        ';
        $this->_join = 'INNER JOIN `'._DB_PREFIX_.'product` p ON (p.id_product = a.id_product AND p.advanced_stock_management = 1)';
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'warehouse` w ON (w.id_warehouse = a.id_warehouse)';
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
            a.id_product = pl.id_product AND
            pl.id_lang = '.(int)$this->context->language->id.'
        )';
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.id_product_attribute = a.id_product_attribute)';
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pa.id_product_attribute = a.id_product_attribute)';
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute` atr ON (atr.id_attribute = pac.id_attribute)';
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (
            al.id_attribute = pac.id_attribute AND
            al.id_lang = '.(int)$this->context->language->id.'
        )';
        $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (
            agl.id_attribute_group = atr.id_attribute_group AND
            agl.id_lang = '.(int)$this->context->language->id.'
        )';
        $this->_group = 'GROUP BY a.id_product, a.id_product_attribute';

        $this->_orderBy = 'name';
        $this->_orderWay = 'ASC';

        if ($this->getCurrentCoverageWarehouse() != -1) {
            $this->_where .= ' AND a.id_warehouse = '.$this->getCurrentCoverageWarehouse();
            self::$currentIndex .= '&id_warehouse='.(int)$this->getCurrentCoverageWarehouse();
        }

        // toolbar btn
        $this->toolbar_btn = array();
        // disables link
        $this->list_no_link = true;

        $stock_instant_state_warehouses = StoreHouse::getWarehouses();
        array_unshift($stock_instant_state_warehouses, array('id_warehouse' => -1, 'name' => $this->l('All Warehouses')));

        // smarty
        $this->tpl_list_vars['stock_instant_state_warehouses'] = $stock_instant_state_warehouses;
        $this->tpl_list_vars['stock_instant_state_cur_warehouse'] = $this->getCurrentCoverageWarehouse();
        // adds ajax params
        $this->ajax_params = array('id_warehouse' => $this->getCurrentCoverageWarehouse());

        // displays help information
        $this->displayInformation('- '.$this->l('This interface allows you to display detailed information about your stock per warehouse.'));
        $this->displayInformation('- '.$this->l('Choose a warehouse to be able to make a CSV export.'));

        // sets toolbar
        $this->initToolbar();

        $list = parent::renderList();

        // if export requested
        if ((Tools::isSubmit('csv_quantities') || Tools::isSubmit('csv_prices')) &&
            (int)Tools::getValue('id_warehouse') != -1) {
            if (count($this->_list) > 0) {
                $this->renderCSV();
                die;
            } else {
                $this->displayWarning($this->l('There is nothing to export as CSV.'));
            }
        }

        return $list;
    }

    public function renderDetails()
    {
        if (Tools::isSubmit('id_stock')) {
            // if a product id is submit
            $this->list_no_link = true;
            $this->lang = false;
            $this->table = 'stock';
            $this->list_id = 'details';
            $this->tpl_list_vars['show_filter'] = false;
            $id_lang = (int)$this->context->language->id;
            $this->actions = array();
            $this->list_simple_header = true;
            $ids = explode('_', Tools::getValue('id_stock'));

            if (count($ids) != 2) {
                die;
            }

            $this->fields_list['warehouse'] = array(
                'title' => $this->l('Warehouse'),
                'class' => 'fixed-width-xs',
                'align' => 'center',
                'orderby' => false,
                'search' => false,
            );

            $id_product = $ids[0];
            $id_product_attribute = $ids[1];
            $id_warehouse = Tools::getValue('id_warehouse', -1);
            $this->_select = '
                p.id_product as product_id,
                IFNULL(pa.ean13, p.ean13) as ean13,
                IFNULL(pa.upc, p.upc) as upc,
                IFNULL(pa.reference, p.reference) as reference,
                IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name SEPARATOR \', \')), pl.name) as name,
                w.id_currency,
                a.price_te,
                wl.name as warehouse
            ';
            $this->_join = 'INNER JOIN `'._DB_PREFIX_.'product` p ON (p.id_product = a.id_product AND p.advanced_stock_management = 1)';
            $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'warehouse` AS w ON w.id_warehouse = a.id_warehouse';
            $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'warehouse_lang` wl ON (
                w.`id_warehouse` = wl.`id_warehouse` AND
                wl.`id_lang` = '.(int)$id_lang.'
            )';
            $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
                a.id_product = pl.id_product AND 
                pl.id_lang = '.(int)$id_lang.'
            )';
            $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (pac.id_product_attribute = a.id_product_attribute)';
            $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pa.id_product_attribute = a.id_product_attribute)';
            $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute` atr ON (atr.id_attribute = pac.id_attribute)';
            $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (
                al.id_attribute = pac.id_attribute AND
                al.id_lang = '.(int)$id_lang.'
            )';
            $this->_join .= ' LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (
                agl.id_attribute_group = atr.id_attribute_group AND 
                agl.id_lang = '.(int)$id_lang.'
            )';
            $this->_where = 'AND a.id_product = '.(int)$id_product.' AND a.id_product_attribute = '.(int)$id_product_attribute;
            if ($id_warehouse != -1) {
                $this->_where .= ' AND a.id_warehouse = '.(int)$id_warehouse;
            }

            $this->_orderBy = 'name';
            $this->_orderWay = 'ASC';

            $this->_group = 'GROUP BY a.price_te';

            self::$currentIndex = self::$currentIndex.'&id_stock='.Tools::getValue('id_stock').'&detailsstock';
            return parent::renderList();
        }
    }

    /**
     * AdminController::getList() override
     * @see AdminController::getList()
     */
    public function getList($id_lang, $order_by = null, $order_way = null, $start = 0, $limit = null, $id_lang_shop = false)
    {
        if (Tools::isSubmit('id_stock')) {
            parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);

            $nb_items = count($this->_list);

            for ($i = 0; $i < $nb_items; $i++) {
                $item = &$this->_list[$i];

                // gets quantities and valuation
                $query = new DbQuery();
                $query->select('physical_quantity');
                //$query->select('usable_quantity');
                $query->select('SUM(price_te * physical_quantity) as valuation');
                $query->from('stock');
                $query->where(
                    'id_stock = '.(int)$item['id_stock'].
                    ' AND id_product = '.(int)$item['id_product'].
                    ' AND id_product_attribute = '.(int)$item['id_product_attribute']
                );
                if ($this->getCurrentCoverageWarehouse() != -1) {
                    $query->where('id_warehouse = '.(int)$this->getCurrentCoverageWarehouse());
                }

                $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

                $item['physical_quantity'] = $res['physical_quantity'];
                //$item['usable_quantity'] = $res['usable_quantity'];
                $item['valuation'] = $res['valuation'];
                $item['real_quantity'] = $res['physical_quantity'] - (int)WorkshopAsm::getReservedQuantityByProductAndWarehouse(
                    $item['id_product'],
                    $item['id_product_attribute'],
                    ($this->getCurrentCoverageWarehouse() == -1 ? null : $this->getCurrentCoverageWarehouse())
                );
            }
        } else {
            if ((int)Tools::getValue('id_warehouse') != -1 && (Tools::isSubmit('csv_quantities') || Tools::isSubmit('csv_prices'))) {
                $limit = false;
            }

            $order_by_real_quantity = $order_by_valuation = false;

            if ($this->context->cookie->{$this->table.'Orderby'} == 'valuation') {
                unset($this->context->cookie->{$this->table.'Orderby'});
                $order_by_valuation = true;
            } elseif ($this->context->cookie->{$this->table.'Orderby'} == 'real_quantity') {
                unset($this->context->cookie->{$this->table.'Orderby'});
                $order_by_real_quantity = true;
            }

            parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);

            $nb_items = count($this->_list);
            $id_warehouse_search = $this->getCurrentCoverageWarehouse();

            for ($i = 0; $i < $nb_items; ++$i) {
                $item = &$this->_list[$i];

                //$item['price_te'] = 0;
                $item[$this->identifier] = $item['id_product'].'_'.$item['id_product_attribute'];

                // gets quantities and valuation
                $query = new DbQuery();
                $query->select('SUM(physical_quantity) as physical_quantity');
                //$query->select('SUM(usable_quantity) as usable_quantity');
                $query->select('SUM(price_te * physical_quantity) as valuation');
                $query->from('stock');
                $query->where('id_product = '.(int)$item['id_product'].' AND id_product_attribute = '.(int)$item['id_product_attribute']);
                if ($id_warehouse_search != -1) {
                    $query->where('id_warehouse = '.(int)$id_warehouse_search);
                }
                //var_dump($query->build());

                $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

                $item['physical_quantity'] = $res['physical_quantity'];
                //$item['usable_quantity'] = $res['usable_quantity'];

                // gets real_quantity depending on the warehouse
                $item['real_quantity'] = $res['physical_quantity'] - (int)WorkshopAsm::getReservedQuantityByProductAndWarehouse(
                    $item['id_product'],
                    $item['id_product_attribute'],
                    ($id_warehouse_search == -1 ? null : $id_warehouse_search)
                );

                // removes the valuation if the filter corresponds to 'all warehouses'
                if ($id_warehouse_search == -1) {
                    $item['valuation'] = 'N/A';
                    $item['price_te'] = '-';
                } else {
                    $item['valuation'] = $res['valuation'];
                }
            }

            if ($id_warehouse_search != -1 && $order_by_valuation) {
                usort($this->_list, array($this, 'valuationCmp'));
            } elseif ($order_by_real_quantity) {
                usort($this->_list, array($this, 'realQuantityCmp'));
            }
        }
    }

    /**
     * CMP
     *
     * @param array $n
     * @param array $m
     *
     * @return bool
     */
    public function valuationCmp($n, $m)
    {
        if ($this->context->cookie->{$this->table.'Orderway'} == 'desc') {
            return $n['valuation'] > $m['valuation'];
        } else {
            return $n['valuation'] < $m['valuation'];
        }
    }

    /**
     * CMP
     *
     * @param array $n
     * @param array $m
     *
     * @return bool
     */
    public function realQuantityCmp($n, $m)
    {
        if ($this->context->cookie->{$this->table.'Orderway'} == 'desc') {
            return $n['real_quantity'] > $m['real_quantity'];
        } else {
            return $n['real_quantity'] < $m['real_quantity'];
        }
    }

    /**
     * Exports CSV
     */
    public function renderCSV()
    {
        if (count($this->_list) <= 0) {
            return;
        }

        // sets warehouse id and warehouse name
        $id_warehouse = (int)Tools::getValue('id_warehouse');
        $warehouse_name = StoreHouse::getWarehouseNameById($id_warehouse);

        // if quantities requested
        if (Tools::isSubmit('csv_quantities')) {
            // filename
            $filename = $this->l('Instant status quantities in stock').'_'.$warehouse_name.'.csv';

            // header
            header('Content-type: text/csv');
            header('Cache-Control: no-store, no-cache must-revalidate');
            header('Content-disposition: attachment; filename="'.$filename);

            // puts keys
            $keys = array(
                'id_product',
                'id_product_attribute',
                'reference',
                'ean13',
                'upc',
                'name',
                'physical_quantity',
                'real_quantity'
            );
            echo sprintf("%s\n", implode(';', $keys));

            // puts rows
            foreach ($this->_list as $row) {
                $row_csv = array(
                    $row['id_product'],
                    $row['id_product_attribute'],
                    $row['reference'],
                    $row['ean13'],
                    $row['upc'],
                    $row['name'],
                    $row['physical_quantity'],
                    //$row['usable_quantity'],
                    $row['real_quantity']
                );
                // puts one row
                echo sprintf("%s\n", implode(';', array_map(array('CSVCore', 'wrap'), $row_csv)));
            }
        // if prices requested
        } elseif (Tools::isSubmit('csv_prices')) {
            // sets filename
            $filename = $this->l('Instant status stock prices').'_'.$warehouse_name.'.csv';

            // header
            header('Content-type: text/csv');
            header('Cache-Control: no-store, no-cache must-revalidate');
            header('Content-disposition: attachment; filename="'.$filename);

            // puts keys
            $keys = array(
                'id_product',
                'id_product_attribute',
                'reference',
                'ean13',
                'upc',
                'name',
                'price_te',
                'physical_quantity'
            );
            echo sprintf("%s\n", implode(';', $keys));

            foreach ($this->_list as $row) {
                $id_product = (int)$row['id_product'];
                $id_product_attribute = (int)$row['id_product_attribute'];

                // gets prices
                $query = new DbQuery();
                $query->select('s.price_te, SUM(s.physical_quantity) as physical_quantity');
                //$query->select('SUM(s.usable_quantity) as usable_quantity');
                $query->from('stock', 's');
                $query->leftJoin('warehouse', 'w', 'w.id_warehouse = s.id_warehouse');
                $query->where('s.id_product = '.$id_product.' AND s.id_product_attribute = '.$id_product_attribute);
                $query->where('s.id_warehouse = '.$id_warehouse);
                $query->groupBy('s.price_te');
                $datas = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

                // puts data
                foreach ($datas as $data) {
                    $row_csv = array(
                        $row['id_product'],
                        $row['id_product_attribute'],
                        $row['reference'],
                        $row['ean13'],
                        $row['upc'],
                        $row['name'],
                        $data['price_te'],
                        $data['physical_quantity'],
                        //$data['usable_quantity']
                    );
                    // puts one row
                    echo sprintf("%s\n", implode(';', array_map(array('CSVCore', 'wrap'), $row_csv)));
                }
            }
        }
    }

    /**
     * Gets the current warehouse used
     *
     * @return int id_warehouse
     */
    protected function getCurrentCoverageWarehouse()
    {
        $warehouse = -1; // all warehouses
        if ((int)Tools::getValue('id_warehouse')) {
            $warehouse = (int)Tools::getValue('id_warehouse');
        }
        return $warehouse;
    }

    public function init()
	{
        parent::init();
		$id_warehouse = $this->getCurrentCoverageWarehouse();
		if (!empty($id_warehouse) && $id_warehouse != -1) {
			self::$currentIndex .= '&id_warehouse='.$id_warehouse;
			$this->context->smarty->assign('current', self::$currentIndex);
		}
    }

    public function initContent()
    {
        if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $this->warnings[md5('PS_ADVANCED_STOCK_MANAGEMENT')] = $this->l('You need to activate advanced stock management before using this feature.');
            return false;
        }
        parent::initContent();
    }

    public function initProcess()
    {
        if (!Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $this->warnings[md5('PS_ADVANCED_STOCK_MANAGEMENT')] = $this->l('You need to activate advanced stock management before using this feature.');
            return false;
        }
        if (Tools::isSubmit('detailsproduct')) {
            $this->list_id = 'details';
        } else {
            $this->list_id = 'stock';
        }
        parent::initProcess();
    }

    /**
     * @see AdminController::initToolbar();
     */
    public function initToolbar()
    {
        if (Tools::isSubmit('id_warehouse') && (int)Tools::getValue('id_warehouse') != -1) {
            $controller_url = $this->context->link->getAdminLink('AdminWkwarehousesStockInstantState');
            $this->toolbar_btn['export-stock-state-quantities-csv'] = array(
                'short' => 'Export this list as CSV',
                'href' => $controller_url.'&csv_quantities&id_warehouse='.(int)$this->getCurrentCoverageWarehouse(),
                'desc' => $this->l('Export Quantities'),
                'class' => 'process-icon-export'
            );
            $this->toolbar_btn['export-stock-state-prices-csv'] = array(
                'short' => 'Export this list as CSV',
                'href' => $controller_url.'&csv_prices&id_warehouse='.(int)$this->getCurrentCoverageWarehouse(),
                'desc' => $this->l('Export Prices'),
                'class' => 'process-icon-export'
            );
        }
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function initPageHeaderToolbar()
    {
        $this->page_header_toolbar_title = $this->l('Instant stock status');
        $controller_url = $this->context->link->getAdminLink('AdminWkwarehousesStockInstantState');

        if ($this->display == 'details') {
            $this->page_header_toolbar_btn['back_to_list'] = array(
                'href' => $controller_url.(Tools::getValue('id_warehouse') ? '&id_warehouse='.Tools::getValue('id_warehouse') : ''),
                'desc' => $this->l('Back to list', null, null, false),
                'icon' => 'process-icon-back'
            );
        } elseif (Tools::isSubmit('id_warehouse') && (int)Tools::getValue('id_warehouse') != -1) {
            $this->page_header_toolbar_btn['export-stock-state-quantities-csv'] = array(
                'short' => $this->l('Export this list as CSV', null, null, false),
                'href' => $controller_url.'&csv_quantities&id_warehouse='.(int)$this->getCurrentCoverageWarehouse(),
                'desc' => $this->l('Export Quantities', null, null, false),
                'class' => 'process-icon-export'
            );
            $this->page_header_toolbar_btn['export-stock-state-prices-csv'] = array(
                'short' => $this->l('Export this list as CSV', null, null, false),
                'href' => $controller_url.'&csv_prices&id_warehouse='.(int)$this->getCurrentCoverageWarehouse(),
                'desc' => $this->l('Export Prices', null, null, false),
                'class' => 'process-icon-export'
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
   			$translated = $this->translator->trans($string, [], 'Modules.Wkwarehouses.Adminwkwarehousesstockinstantstatecontroller');
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
