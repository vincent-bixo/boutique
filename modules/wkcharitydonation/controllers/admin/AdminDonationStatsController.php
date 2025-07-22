<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.txt
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to a newer
 * versions in the future. If you wish to customize this module for your needs
 * please refer to CustomizationPolicy.txt file inside our module for more information.
 *
 * @author Webkul IN
 * @copyright Since 2010 Webkul
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminDonationStatsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->list_no_link = true;
        $this->table = 'wk_donation_stats';
        parent::__construct();
        $this->toolbar_title = $this->l('Donations Statistics');
        $this->_defaultOrderBy = 'id_donation_info';
        $this->identifier = 'id_donation_info';
        $this->_group = 'GROUP BY a.id_donation_info';

        $this->_select .= ' COUNT(DISTINCT a.`id_customer`) as `total_customer` ,';
        $this->_select .= ' COUNT(DISTINCT a.`id_order`) as `total_order`,';
        $this->_select .= ' IF(di.`active`, "' . $this->l('Active') . '", IF(IFNULL(di.`active`, \'1\'), "' .
        $this->l('Deleted') . '", "' . $this->l('Inactive') . '")) as status, ';
        $this->_select .= ' IF(di.`active`, 1, 0) badge_success, IF(di.`active`, 0, 1) badge_danger, ';
        $this->_select .= '  ROUND(SUM(CASE ';
        foreach (Currency::getCurrencies(false) as $currency) {
            $this->_select .= '  WHEN od.`id_currency` = ' . $currency['id_currency'] . '
            THEN od.`total_paid_tax_incl` / ' . $currency['conversion_rate'];
        }
        $this->_select .= ' END), 2) as `total_amount` ,';
        $this->_select .= ' self.`name` as latest_name ';

        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'orders` od ON (od.`id_order` = a.`id_order`)';
        $this->_join .= ' LEFT JOIN (SELECT * FROM `' . _DB_PREFIX_ . 'wk_donation_stats` WHERE id_donation_stats
        IN (SELECT MAX(id_donation_stats) FROM  `' . _DB_PREFIX_ . 'wk_donation_stats` GROUP BY id_donation_info)) self
        ON (self.`id_donation_info` = a.`id_donation_info`)';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'order_detail` ord
        ON CONCAT(ord.`id_order`, ord.`product_id`) = CONCAT(a.`id_order`, a.`id_product`)';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'wk_donation_info` di
        ON (di.`id_donation_info` = a.`id_donation_info`)';
        $this->_where .= ' AND a.id_shop IN (' . implode(',', Shop::getContextListShopID()) . ')';
        if (Tools::getIsset('viewwk_donation_stats')) {
            $this->list_id = 'donation_stats_customer';
        } else {
            $this->list_id = 'wk_donation_stats';
        }

        $this->allow_export = false;
        $this->addRowAction('view');
    }

    public function initDonationStats()
    {
        $this->_select .= ', sh.`name` as wk_donation_info_shop_name';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'wk_donation_stats` wds ON (wds.`id_donation_info` = a.`id_donation_info`)';
        $this->_join .= 'LEFT JOIN `' . _DB_PREFIX_ . 'shop` sh ON (sh.`id_shop` = wds.`id_shop`)';
        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            $this->_where .= ' AND a.id_shop IN (' . implode(',', Shop::getContextListShopID()) . ')';
        }

        $statusList = [
            $this->l('Active') => $this->l('Active'),
            $this->l('Inactive') => $this->l('Inactive'),
            $this->l('Deleted') => $this->l('Deleted'),
        ];
        $this->toolbar_title = $this->l('Donation statistics');

        $this->fields_list = [
            'id_donation_info' => [
                'title' => $this->l('ID'),
                'class' => 'fixed-width-xs',
                'align' => 'center',
            ],
            'latest_name' => [
                'title' => $this->l('Donation name'),
                'align' => 'center',
                'filter_key' => 'self!name',
                'havingFilter' => true,
                'callback' => 'displayDonationlink',
            ],
            'total_amount' => [
                'title' => $this->l('Total donation amount'),
                'type' => 'price',
                'align' => 'center',
                'currency' => true,
                'havingFilter' => true,
                'callback' => 'getTotalDonationAmount',
            ],
            'total_customer' => [
                'title' => $this->l('Total no. of customer'),
                'class' => 'fixed-width-xs',
                'align' => 'center',
                'havingFilter' => true,
                'hint' => $this->l('Total number of customer that have donated in a campaign'),
            ],
            'total_order' => [
                'title' => $this->l('Total no. of order'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'type' => 'datetime',
                'hint' => $this->l('Total number of donation recieved in a campaign'),
                'filter_key' => 'a!date_add',
            ],
            'status' => [
                'title' => $this->l('Donation status'),
                'align' => 'center',
                'type' => 'select',
                'list' => $statusList,
                'hint' => $this->l('Donation current status'),
                'badge_success' => true,
                'badge_danger' => true,
                'class' => 'fixed-width-xs',
                'havingFilter' => true,
                'filter_key' => 'status',
            ],
        ];

        if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) {
            $this->fields_list['wk_donation_info_shop_name'] = [
                'title' => $this->l('Shop'),
                'havingFilter' => true,
                'align' => 'center',
            ];
        }

        if (Tools::isSubmit('submitReset' . $this->list_id)) {
            $this->processResetFilters($this->list_id);
        } elseif (Tools::getValue('submitFilter' . $this->list_id)) {
            $this->toolbar_title = '';
        }
        $this->processFilter();

        return parent::renderList();
    }

    public function getTotalDonationAmount($name, $row)
    {
        if ($name) {
            $objDonationInfo = new WkDonationInfo();
            $total_amount = WkDonationInfo::displayPrice($objDonationInfo->getTotalDonationAmount($row['id_donation_info']));

            return $total_amount;
        }
    }

    public function displayDonationLink($name, $row)
    {
        if (Validate::isLoadedObject($objDonationInfo = new WkDonationInfo($row['id_donation_info']))) {
            $this->context->smarty->assign([
                'displayText' => $name,
                'displayLink' => $this->context->link->getAdminLink(
                    'AdminManageDonation'
                ) . '&id_donation_info=' . $objDonationInfo->id . '&updatewk_donation_info',
            ]);

            return $this->context->smarty->fetch(
                _PS_MODULE_DIR_ . $this->module->name .
                '/views/templates/admin/donation_stats/helpers/_partials/display-link.tpl'
            );
        } else {
            return $name;
        }
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function renderList()
    {
        $list = $this->initDonationStats();
        $list .= $this->initNewDonationsList();

        return $list;
    }

    public function initNewDonationsList()
    {
        unset($this->fields_list, $this->_select, $this->_join, $this->_filterHaving, $this->_having);
        $this->filter = false;
        $this->toolbar_title = $this->l('Nonfunded donation campaigns');
        $this->table = 'wk_donation_info';
        $this->className = 'WkDonationInfo';
        $this->identifier = 'id_donation_info';

        $this->_select = ' dl.`name`, IF(a.`expiry_date`, a.`expiry_date`, "' . $this->l('No expiry') . '") as expiry, ';
        $this->_select .= ' IF(IFNULL(a.`active`, \'0\'), "' . $this->l('Active') . '", "' . $this->l('Inactive') . '")
        as status, ';
        $this->_select .= ' IF(a.`active`, 1, 0) badge_success, IF(a.`active`, 0, 1) badge_danger, ';

        $this->_join = ' LEFT JOIN `' . _DB_PREFIX_ . 'wk_donation_info_lang` dl
        ON (dl.`id_donation_info` = a.`id_donation_info`)';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'wk_donation_stats` ds
        ON (ds.`id_donation_info` = a.`id_donation_info`)';
        $this->_where = ' AND ds.`id_donation_info` IS NULL AND a.`is_global` = 0
        AND dl.`id_lang` = ' . (int) $this->context->language->id;
        $this->_orderBy = null;
        $this->list_id = 'newDonationList';

        if (Shop::getContext() == Shop::CONTEXT_SHOP) {
            Shop::addTableAssociation('wk_donation_info', ['type' => 'shop', 'primary' => 'id_donation_info']);
        }

        if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) {
            $this->_select .= 'sh.`name` as wk_donation_info_shop_name';
            $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'wk_donation_info_shop` wdis ON (wdis.`id_donation_info` = a.`id_donation_info`)';
            $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'shop` sh ON (sh.`id_shop` = wdis.`id_shop`)';
        }

        $this->fields_list = [
            'id_donation_info' => [
                'title' => $this->l('ID'),
                'class' => 'fixed-width-xs',
                'align' => 'center',
                'search' => false,
            ],
            'name' => [
                'title' => $this->l('Donation name'),
                'align' => 'center',
                'callback' => 'displayDonationlink',
                'search' => false,
            ],
            'price_type' => [
                'title' => $this->l('Price type'),
                'align' => 'center',
                'hint' => $this->l('\'Fixed\' means donation amount is fixed, \'By customer\' means donation amount can be entered by customer'),
                'search' => false,
                'callback' => 'getPriceType',
            ],
            'price' => [
                'title' => $this->l('Price'),
                'align' => 'center',
                'type' => 'price',
                'search' => false,
            ],
            'date_add' => [
                'title' => $this->l('Date created'),
                'class' => 'fixed-width-xs',
                'align' => 'text-right',
                'type' => 'datetime',
                'search' => false,
            ],
            'expiry' => [
                'title' => $this->l('Expiry date'),
                'class' => 'fixed-width-xs',
                'align' => 'text-right',
                'type' => 'datetime',
                'search' => false,
            ],
            'status' => [
                'title' => $this->l('Donation status'),
                'hint' => $this->l('Donation current status'),
                'align' => 'center',
                'badge_success' => true,
                'badge_danger' => true,
                'class' => 'fixed-width-xs',
                'search' => false,
            ],
        ];

        if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) {
            $this->fields_list['wk_donation_info_shop_name'] = [
                'title' => $this->l('Shop'),
                'havingFilter' => true,
                'align' => 'center',
                'search' => false,
            ];
        }
        $this->actions = [];
        $this->processResetFilters($this->list_id);

        return parent::renderList();
    }

    public function processFilter()
    {
        Hook::exec('action' . $this->controller_name . 'ListingFieldsModifier', [
            'fields' => &$this->fields_list,
        ]);

        if (!isset($this->list_id)) {
            $this->list_id = $this->table;
        }

        $prefix = $this->getCookieFilterPrefix();
        if (isset($this->list_id)) {
            foreach ($_POST as $key => $value) {
                if ($value === '|') {
                    if ($id = Tools::getValue('id_donation_info')) {
                        $url = self::$currentIndex . '&token=' . $this->token . '&' . $this->identifier . '=' . $id . '&view' . $this->table;
                    } else {
                        $url = self::$currentIndex . '&token=' . $this->token;
                    }
                    Tools::redirectAdmin($url);
                }
                if ($value === '') {
                    unset($this->context->cookie->{$prefix . $key});
                } elseif (stripos($key, $this->list_id . 'Filter_') === 0) {
                    $this->context->cookie->{$prefix . $key} = !is_array($value) ? $value : json_encode($value);
                } elseif (stripos($key, 'submitFilter') === 0) {
                    $this->context->cookie->$key = !is_array($value) ? $value : json_encode($value);
                }
            }

            foreach ($_GET as $key => $value) {
                if (stripos($key, $this->list_id . 'Filter_') === 0) {
                    $this->context->cookie->{$prefix . $key} = !is_array($value) ? $value : json_encode($value);
                } elseif (stripos($key, 'submitFilter') === 0) {
                    $this->context->cookie->$key = !is_array($value) ? $value : json_encode($value);
                }
                if (stripos($key, $this->list_id . 'Orderby') === 0 && Validate::isOrderBy($value)) {
                    if ($value === '' || $value == $this->_defaultOrderBy) {
                        unset($this->context->cookie->{$prefix . $key});
                    } else {
                        $this->context->cookie->{$prefix . $key} = $value;
                    }
                } elseif (stripos($key, $this->list_id . 'Orderway') === 0 && Validate::isOrderWay($value)) {
                    if ($value === '' || $value == $this->_defaultOrderWay) {
                        unset($this->context->cookie->{$prefix . $key});
                    } else {
                        $this->context->cookie->{$prefix . $key} = $value;
                    }
                }
            }
        }

        $filters = $this->context->cookie->getFamily($prefix . $this->list_id . 'Filter_');
        $definition = false;
        if (isset($this->className) && $this->className) {
            $definition = ObjectModel::getDefinition($this->className);
        }

        foreach ($filters as $key => $value) {
            /* Extracting filters from $_POST on key filter_ */
            if ($value != null && !strncmp($key, $prefix . $this->list_id . 'Filter_', 7 + Tools::strlen($prefix . $this->list_id))) {
                $key = Tools::substr($key, 7 + Tools::strlen($prefix . $this->list_id));
                /* Table alias could be specified using a ! eg. alias!field */
                $tmp_tab = explode('!', $key);
                $filter = count($tmp_tab) > 1 ? $tmp_tab[1] : $tmp_tab[0];

                if ($field = $this->filterToField($key, $filter)) {
                    $type = (array_key_exists('filter_type', $field) ? $field['filter_type'] : (array_key_exists('type', $field) ? $field['type'] : false));
                    if (($type == 'date' || $type == 'datetime') && is_string($value)) {
                        $value = json_decode($value, true);
                    }
                    $key = isset($tmp_tab[1]) ? $tmp_tab[0] . '.`' . $tmp_tab[1] . '`' : '`' . $tmp_tab[0] . '`';

                    // Assignment by reference
                    if (array_key_exists('tmpTableFilter', $field)) {
                        $sql_filter = &$this->_tmpTableFilter;
                    } elseif (array_key_exists('havingFilter', $field)) {
                        $sql_filter = &$this->_filterHaving;
                    } else {
                        $sql_filter = &$this->_filter;
                    }

                    /* Only for date filtering (from, to) */
                    if (is_array($value)) {
                        if (isset($value[0]) && !empty($value[0])) {
                            if (!Validate::isDate($value[0])) {
                                $this->errors[] = $this->trans('The \'From\' date format is invalid (YYYY-MM-DD)', [], 'Admin.Notifications.Error');
                            } else {
                                $sql_filter .= ' AND ' . pSQL($key) . ' >= \'' . pSQL(Tools::dateFrom($value[0])) . '\'';
                            }
                        }

                        if (isset($value[1]) && !empty($value[1])) {
                            if (!Validate::isDate($value[1])) {
                                $this->errors[] = $this->trans('The \'To\' date format is invalid (YYYY-MM-DD)', [], 'Admin.Notifications.Error');
                            } else {
                                $sql_filter .= ' AND ' . pSQL($key) . ' <= \'' . pSQL(Tools::dateTo($value[1])) . '\'';
                            }
                        }
                    } else {
                        $sql_filter .= ' AND ';
                        $check_key = ($key == $this->identifier || $key == '`' . $this->identifier . '`');
                        $alias = ($definition && !empty($definition['fields'][$filter]['shop'])) ? 'sa' : 'a';

                        if ($type == 'int' || $type == 'bool') {
                            $sql_filter .= (($check_key || $key == '`active`') ? $alias . '.' : '') . pSQL($key) . ' = ' . (int) $value . ' ';
                        } elseif ($type == 'decimal') {
                            $sql_filter .= ($check_key ? $alias . '.' : '') . pSQL($key) . ' = ' . (float) $value . ' ';
                        } elseif ($type == 'select') {
                            $sql_filter .= ($check_key ? $alias . '.' : '') . pSQL($key) . ' = \'' . pSQL($value) . '\' ';
                        } elseif ($type == 'price') {
                            $value = (float) str_replace(',', '.', $value);
                            $sql_filter .= ($check_key ? $alias . '.' : '') . pSQL($key) . ' = ' . pSQL(trim($value)) . ' ';
                        } else {
                            $sql_filter .= ($check_key ? $alias . '.' : '') . pSQL($key) . ' LIKE \'%' . pSQL(trim($value)) . '%\' ';
                        }
                    }
                }
            }
        }
    }

    public function getPriceType($row)
    {
        if ($row == WkDonationInfo::WK_DONATION_PRICE_TYPE_FIXED) {
            return $this->l('Fixed');
        } elseif ($row == WkDonationInfo::WK_DONATION_PRICE_TYPE_CUSTOMER) {
            return $this->l('By customer');
        }
    }

    public function displayViewLink($token, $id)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_view.tpl');
        if (Tools::getisset('viewwk_donation_stats')) {
            foreach ($this->_list as $row) {
                if ($id == $row['id_donation_stats']) {
                    $idOrder = $row['id_order'];
                    break;
                }
            }
            if (_PS_VERSION_ >= '1.7') {
                if (_PS_VERSION_ >= '1.7.6.0') {
                    $order_link = $this->context->link->getAdminLink(
                        'AdminOrders',
                        true,
                        ['vieworder' => 1, 'id_order' => (int) $idOrder]
                    );
                } else {
                    $order_link = $this->context->link->getAdminLink(
                        'AdminOrders',
                        true,
                        '',
                        ['vieworder' => 1, 'id_order' => (int) $idOrder]
                    );
                }
            } else {
                $order_link = $this->context->link->getAdminLink('AdminOrders') . '&id_order=' . $idOrder . '&vieworder';
            }

            $tpl->assign([
                'href' => $order_link,
                'action' => $this->l('View Order'),
            ]);
        } else {
            $tpl->assign([
                'href' => self::$currentIndex . '&' . $this->identifier . '=' . $id . '&view' . $this->table .
                '&token=' . ($token != null ? $token : $this->token),
                'action' => $this->l('View'),
            ]);
        }

        return $tpl->fetch();
    }

    public function renderKpis()
    {
        $objDonationInfo = new WkDonationInfo();
        $kpis = [];

        $helper = new HelperKpi();
        $helper->id = 'box-total-donation-orders';
        $helper->icon = 'icon-shopping-cart';
        $helper->color = 'color1';
        $helper->title = $this->l('Total Donations');
        $helper->value = $objDonationInfo->getTotalDonationCount();
        $kpis[] = $helper->generate();

        $helper = new HelperKpi();
        $helper->id = 'box-total-donation-amount';
        $helper->icon = 'icon-money';
        $helper->color = 'color3';
        $helper->title = $this->l('Total Donation Amount');
        $helper->value = WkDonationInfo::displayPrice($objDonationInfo->getTotalDonationAmount());
        $kpis[] = $helper->generate();

        $helper = new HelperKpi();
        $helper->id = 'box-total-donation-customer';
        $helper->icon = 'icon-user';
        $helper->color = 'color4';
        $helper->title = $this->l('Total Customers');
        $helper->value = $objDonationInfo->getTotalCustomerCount();
        $kpis[] = $helper->generate();

        $helper = new HelperKpiRow();
        $helper->refresh = false;
        $helper->kpis = $kpis;

        return $helper->generate();
    }

    public function renderView()
    {
        if ($idDonation = Tools::getValue('id_donation_info')) {
            $this->table = 'wk_donation_stats';
            $this->identifier = 'id_donation_stats';

            $this->_orderBy = 'id_order';
            $this->_orderWay = 'DESC';
            $this->_group = '';

            $this->_select = ' CONCAT(cu.`firstname`,\' \', cu.`lastname`) as `customer_name` ,
            ord.`total_price_tax_incl` as `amount` ,od.`id_currency` as `id_currency` , ';
            $this->_select .= 'IF(os.`paid`, 1, 0) badge_success , IF(os.`paid`, 0, 1) badge_danger, ';
            $this->_select .= 'IF(IFNULL(os.`paid`, \'0\'), "' . $this->l('Payment received') . '",
            IF(os.`id_order_state` =  \'6\', "' . $this->l('Cancled') . '",
            IF(os.`id_order_state` =  \'7\', "' . $this->l('Refunded') . '",
            IF(os.`id_order_state` =  \'8\', "' . $this->l('Payment Error') . '",
            "' . $this->l('Payment awaiting') . '")))) as order_status, ';

            $this->_join = ' LEFT JOIN `' . _DB_PREFIX_ . 'orders` od ON (od.`id_order` = a.`id_order`)';
            $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'customer` cu ON (od.`id_customer` = cu.`id_customer`)';
            $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = od.`current_state`)';
            $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'order_detail` ord
            ON CONCAT(ord.`id_order`, ord.`product_id`)= CONCAT(a.`id_order`, a.`id_product`)';

            $this->_where = ' AND a.`id_donation_info` = ' . (int) $idDonation;
            if (Shop::getContext() == Shop::CONTEXT_SHOP) {
                $this->_where .= ' AND a.id_shop = ' . Context::getContext()->shop->id;
            }

            if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) {
                $this->_select .= 'sh.`name` as wk_donation_info_shop_name';
                $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'shop` sh ON (sh.`id_shop` = a.`id_shop`)';
            }

            $this->initDonationOrderList();

            return parent::renderList();
        }
    }

    public function initDonationOrderList()
    {
        $this->status_array = [
            $this->l('Payment received') => $this->l('Payment received'),
            $this->l('Payment awaiting') => $this->l('Payment awaiting'),
            $this->l('Cancled') => $this->l('Cancled'),
            $this->l('Refunded') => $this->l('Refunded'),
            $this->l('Payment Error') => $this->l('Payment Error'),
        ];

        if ($idDonation = Tools::getValue('id_donation_info')) {
            $this->fields_list = [
                'id_order' => [
                    'title' => $this->l('Order ID'),
                    'align' => 'center',
                    'havingFilter' => true,
                    'filter_key' => 'a!id_order',
                    'class' => 'fixed-width-xs',
                ],
                'customer_name' => [
                    'title' => $this->l('Customer name'),
                    'align' => 'center',
                    'havingFilter' => true,
                    'callback' => 'displayCustomerName',
                ],
                'amount' => [
                    'title' => $this->l('Amount donated'),
                    'type' => 'price',
                    'havingFilter' => true,
                    'currency' => true,
                    'callback' => 'displayDonationAmount',
                    'align' => 'center',
                ],
                'order_status' => [
                    'title' => $this->l('Payment status'),
                    'type' => 'select',
                    'align' => 'center',
                    'list' => $this->status_array,
                    'havingFilter' => true,
                    'filter_key' => 'order_status',
                    'badge_success' => true,
                    'badge_danger' => true,
                ],
                'date_add' => [
                    'title' => $this->l('Donation date'),
                    'class' => 'fixed-width-xs',
                    'align' => 'text-right',
                    'type' => 'datetime',
                    'filter_key' => 'a!date_add',
                ],
            ];

            if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_ALL) {
                $this->fields_list['wk_donation_info_shop_name'] = [
                    'title' => $this->l('Shop'),
                    'havingFilter' => true,
                    'align' => 'center',
                ];
            }

            if (Validate::isLoadedObject($objDonationInfo = new WkDonationInfo((int) $idDonation))) {
                $this->toolbar_title = $objDonationInfo->name[$this->context->language->id] . ' > ' . $this->l('View');
            } else {
                $donationName = $objDonationInfo->getDonationNameFromStats((int) $idDonation);
                $this->toolbar_title = $donationName . ' > ' . $this->l('View');
            }

            self::$currentIndex = self::$currentIndex . '&viewwk_donation_stats&id_donation_info=' . (int) $idDonation;
            $objDonationInfo = new WkDonationInfo();
            $this->context->smarty->assign([
                'stats_page' => 'viewwk_donation_stats',
                'total_amount' => WkDonationInfo::displayPrice($objDonationInfo->getTotalDonationAmount($idDonation)),
                'total_donations' => $objDonationInfo->getTotalDonationCount($idDonation),
                'total_customer' => $objDonationInfo->getTotalCustomerCount($idDonation),
            ]);
        }
    }

    public function displayCustomerName($name, $row)
    {
        if (_PS_VERSION_ >= '1.7') {
            if (_PS_VERSION_ >= '1.7.6.0') {
                $customer_link = $this->context->link->getAdminLink(
                    'AdminCustomers',
                    true,
                    ['viewcustomer' => 1, 'id_customer' => (int) $row['id_customer']],
                    []
                );
            } else {
                $customer_link = $this->context->link->getAdminLink(
                    'AdminCustomers',
                    true,
                    '',
                    ['viewcustomer' => 1, 'id_customer' => (int) $row['id_customer']]
                );
            }
        } else {
            $customer_link = $this->context->link->getAdminLink('AdminCustomers') . '&id_customer=' .
            $row['id_customer'] . '&viewcustomer';
        }

        $this->context->smarty->assign([
            'displayText' => $name,
            'displayLink' => $customer_link,
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->module->name .
            '/views/templates/admin/donation_stats/helpers/_partials/display-link.tpl'
        );
    }

    public function displayDonationAmount($amount, $data)
    {
        return WkDonationInfo::displayPrice($amount, (int) $data['id_currency']);
    }

    protected function filterToField($key, $filter)
    {
        if (Tools::getIsset('viewwk_donation_stats')
        ) {
            $this->initDonationOrderList();
        }

        return parent::filterToField($key, $filter);
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitResetdonation_stats_customer')) {
            $this->processResetFilters();
        }
        parent::postProcess();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setmedia($isNewTheme);
        $this->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/admin/wk_manage_donation.css');
    }
}
