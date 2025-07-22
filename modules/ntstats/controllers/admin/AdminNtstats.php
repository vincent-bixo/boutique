<?php
/**
 * 2013-2024 2N Technologies
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@2n-tech.com so we can send you a copy immediately.
 *
 * @author    2N Technologies <contact@2n-tech.com>
 * @copyright 2013-2024 2N Technologies
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

define('CONFIGURE_NTCRON', 'https://ntcron.2n-tech.com/app/configure.php?');
define('CONFIGURE_NTVERSION', 'https://version.2n-tech.com/set_email_version.php?source=bqbh');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminNtstatsController extends ModuleAdminController
{
    const PAGE = 'adminntstats';

    private $nts;

    public function __construct()
    {
        $this->display = 'view';
        $this->bootstrap = true;
        // $this->multishop_context    = Shop::CONTEXT_ALL;
        $this->context = Context::getContext();

        parent::__construct();

        if (version_compare(_PS_VERSION_, '1.6.0.12', '>=') === true) {
            $this->meta_title = ['NT ' . $this->l('Stats', self::PAGE)];
        } else {
            $this->meta_title = 'NT ' . $this->l('Stats', self::PAGE);
        }

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }

        $this->nts = new NtStats();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJqueryUI('ui.datepicker');

        $module_path = $this->module->getPathUri();

        $this->addCSS(
            [
                $module_path . 'views/css/style.css?' . $this->module->version,
            ],
            'all',
            null,
            false
        );

        $this->addJS(
            [
                $module_path . 'views/js/script.js?' . $this->module->version,
            ],
            false
        );

        $this->addCSS([
            $module_path . 'lib/fontawesome/css/all.min.css',
            $module_path . 'lib/DataTables-1.11.3/DataTables-1.11.3/css/jquery.dataTables.min.css',
            $module_path . 'lib/DataTables-1.11.3/ColReorder-1.5.4/css/colReorder.dataTables.min.css',
        ]);

        $this->addJS([
            $module_path . 'lib/DataTables-1.11.3/DataTables-1.11.3/js/jquery.dataTables.min.js',
            $module_path . 'lib/DataTables-1.11.3/natural.js',
            $module_path . 'lib/DataTables-1.11.3/ColReorder-1.5.4/js/dataTables.colReorder.min.js',
            $module_path . 'lib/chartjs-2.9.3/node_modules/chart.js/dist/Chart.js',
        ]);

        $this->addjQueryPlugin([
            'select2',
        ]);

        return true;
    }

    public function ajaxPreprocess()
    {
        $shop = $this->context->shop;
        $id_lang = $this->context->language->id;

        switch (Shop::getContext()) {
            case Shop::CONTEXT_SHOP:
                $a_config = NtsConfig::getConfig($shop->id, $shop->id_shop_group, $id_lang);
                break;
            case Shop::CONTEXT_GROUP:
                $a_config = NtsConfig::getConfig(0, Shop::getContextShopGroupID(), $id_lang); // $shop->id_shop_group may not the correct group in context_group
                break;
            case Shop::CONTEXT_ALL:
                $a_config = NtsConfig::getConfig(0, 0, $id_lang);
                break;
            default:
                $a_config = NtsConfig::getConfig($shop->id, $shop->id_shop_group, $id_lang);
        }

        if ($a_config['increase_server_timeout']) {
            $server_timeout_value = $a_config['server_timeout_value'];

            if (!$server_timeout_value || $server_timeout_value <= 0) {
                $server_timeout_value = NtsConfig::SET_TIME_LIMIT;
            }

            set_time_limit($server_timeout_value);
        }

        if ($a_config['increase_server_memory']) {
            $server_memory_value = $a_config['server_memory_value'];

            if (!$server_memory_value || $server_memory_value <= 0) {
                $server_memory_value = NtsConfig::SET_MEMORY_LIMIT;
            }

            ini_set('memory_limit', $server_memory_value . 'M');
        }

        if (Tools::isSubmit('type_list')) {
            if (Tools::isSubmit('export')) {
                if (Tools::getValue('type_list') == 'total_sales') {
                    $this->exportTotalSale();
                } elseif (Tools::getValue('type_list') == 'total_categories_sales') {
                    $this->exportTotalCategoriesSale();
                } elseif (Tools::getValue('type_list') == 'total_products_sales') {
                    $this->exportTotalProductsSales();
                } elseif (Tools::getValue('type_list') == 'total_manufacturers_sales') {
                    $this->exportTotalManufacturersSales();
                } elseif (Tools::getValue('type_list') == 'total_payment_methods_sales') {
                    $this->exportTotalPaymentMethodsSales();
                } elseif (Tools::getValue('type_list') == 'total_combinations_sales') {
                    $this->exportTotalCombinationsSales();
                } elseif (Tools::getValue('type_list') == 'total_countries_sales') {
                    $this->exportTotalCountriesSales();
                } elseif (Tools::getValue('type_list') == 'compare_total_sales') {
                    $this->exportCompareTotalSale();
                } elseif (Tools::getValue('type_list') == 'compare_total_categories_sales') {
                    $this->exportCompareTotalCategoriesSale();
                } elseif (Tools::getValue('type_list') == 'compare_total_products_sales') {
                    $this->exportCompareTotalProductsSale();
                } elseif (Tools::getValue('type_list') == 'compare_total_manufacturers_sales') {
                    $this->exportCompareTotalManufacturersSale();
                } elseif (Tools::getValue('type_list') == 'compare_total_payment_methods_sales') {
                    $this->exportCompareTotalPaymentMethodsSale();
                } elseif (Tools::getValue('type_list') == 'compare_total_combinations_sales') {
                    $this->exportCompareTotalCombinationsSale();
                } elseif (Tools::getValue('type_list') == 'compare_total_countries_sales') {
                    $this->exportCompareTotalCountriesSale();
                } elseif (Tools::getValue('type_list') == 'product') {
                    $this->exportProducts();
                } elseif (Tools::getValue('type_list') == 'products_with_out_stock_combination') {
                    $this->exportProductsWithOutStockCombinations();
                } elseif (Tools::getValue('type_list') == 'product_with_combinations_without_enough_stock') {
                    $this->exportProductsWithCombinationsWithoutEnoughStock();
                } elseif (Tools::getValue('type_list') == 'combination') {
                    $this->exportCombinations();
                } elseif (Tools::getValue('type_list') == 'combination_unsold_with_stock') {
                    $this->exportCombinationsUnsoldWithStock();
                } elseif (Tools::getValue('type_list') == 'carriers') {
                    $this->exportCarriers();
                } elseif (Tools::getValue('type_list') == 'manufacturers') {
                    $this->exportManufacturers();
                } elseif (Tools::getValue('type_list') == 'customer_single_order_amount') {
                    $this->exportCustomerSingleOrderAmount();
                } elseif (Tools::getValue('type_list') == 'customer_orders_amount') {
                    $this->exportCustomerOrdersAmount();
                } elseif (Tools::getValue('type_list') == 'cartrules') {
                    $this->exportCartrules();
                } elseif (Tools::getValue('type_list') == 'orders') {
                    $this->exportOrders();
                } elseif (Tools::getValue('type_list') == 'categories') {
                    $this->exportCategories();
                } elseif (Tools::getValue('type_list') == 'customers') {
                    $this->exportCustomers();
                } elseif (Tools::getValue('type_list') == 'customers_products') {
                    $this->exportCustomersProducts();
                } elseif (Tools::getValue('type_list') == 'customers_products_details') {
                    $this->exportCustomersProductsDetails();
                } elseif (Tools::getValue('type_list') == 'duration_statuses') {
                    $this->exportDurationStatuses();
                } elseif (Tools::getValue('type_list') == 'customers_orders_details') {
                    $this->exportCustomersOrdersDetails();
                }
            } else {
                if (Tools::getValue('type_list') == 'total_sales') {
                    $this->getTotalSale();
                } elseif (Tools::getValue('type_list') == 'total_categories_sales') {
                    $this->getTotalCategoriesSale();
                } elseif (Tools::getValue('type_list') == 'total_products_sales') {
                    $this->getTotalProductsSale();
                } elseif (Tools::getValue('type_list') == 'total_manufacturers_sales') {
                    $this->getTotalManufacturersSale();
                } elseif (Tools::getValue('type_list') == 'total_payment_methods_sales') {
                    $this->getTotalPaymentMethodsSale();
                } elseif (Tools::getValue('type_list') == 'total_combinations_sales') {
                    $this->getTotalCombinationsSale();
                } elseif (Tools::getValue('type_list') == 'total_countries_sales') {
                    $this->getTotalCountriesSale();
                } elseif (Tools::getValue('type_list') == 'compare_total_sales') {
                    $this->getCompareTotalSale();
                } elseif (Tools::getValue('type_list') == 'compare_total_categories_sales') {
                    $this->getCompareTotalCategoriesSale();
                } elseif (Tools::getValue('type_list') == 'compare_total_products_sales') {
                    $this->getCompareTotalProductsSale();
                } elseif (Tools::getValue('type_list') == 'compare_total_manufacturers_sales') {
                    $this->getCompareTotalManufacturersSale();
                } elseif (Tools::getValue('type_list') == 'compare_total_payment_methods_sales') {
                    $this->getCompareTotalPaymentMethodsSale();
                } elseif (Tools::getValue('type_list') == 'compare_total_combinations_sales') {
                    $this->getCompareTotalCombinationsSale();
                } elseif (Tools::getValue('type_list') == 'compare_total_countries_sales') {
                    $this->getCompareTotalCountriesSale();
                } elseif (Tools::getValue('type_list') == 'product') {
                    $this->getProducts();
                } elseif (Tools::getValue('type_list') == 'products_with_out_stock_combination') {
                    $this->getProductsWithOutStockCombinations();
                } elseif (Tools::getValue('type_list') == 'product_with_combinations_without_enough_stock') {
                    $this->getProductsWithCombinationsWithoutEnoughStock();
                } elseif (Tools::getValue('type_list') == 'combination') {
                    $this->getCombinations();
                } elseif (Tools::getValue('type_list') == 'combination_unsold_with_stock') {
                    $this->getCombinationsUnsoldWithStock();
                } elseif (Tools::getValue('type_list') == 'carriers') {
                    $this->getCarriers();
                } elseif (Tools::getValue('type_list') == 'manufacturers') {
                    $this->getManufacturers();
                } elseif (Tools::getValue('type_list') == 'customer_single_order_amount') {
                    $this->getCustomerSingleOrderAmount();
                } elseif (Tools::getValue('type_list') == 'customer_orders_amount') {
                    $this->getCustomerOrdersAmount();
                } elseif (Tools::getValue('type_list') == 'cartrules') {
                    $this->getCartrules();
                } elseif (Tools::getValue('type_list') == 'orders') {
                    $this->getOrders();
                } elseif (Tools::getValue('type_list') == 'categories') {
                    $this->getCategories();
                } elseif (Tools::getValue('type_list') == 'customers') {
                    $this->getCustomers();
                } elseif (Tools::getValue('type_list') == 'customers_products') {
                    $this->getCustomersProducts();
                } elseif (Tools::getValue('type_list') == 'customers_products_details') {
                    $this->getCustomersProductsDetails();
                } elseif (Tools::getValue('type_list') == 'duration_statuses') {
                    $this->getDurationStatuses();
                } elseif (Tools::getValue('type_list') == 'customers_orders_details') {
                    $this->getCustomersOrdersDetails();
                }
            }
        } elseif (Tools::isSubmit('config_id_nts_config')) {
            $this->saveConfig();
        } elseif (Tools::isSubmit('save_automation')) {
            $this->saveAutomation();
        } elseif (Tools::isSubmit('get_categories_list')) {
            exit(json_encode(['list' => NtStats::getListCategories()]));
        } elseif (Tools::isSubmit('get_select_list_combinations') && Tools::isSubmit('id_product')) {
            $id_product = Tools::getValue('id_product');
            $display_combinations_ordered = 0;
            $list = [];

            if (Tools::isSubmit('display_combinations_ordered')) {
                $display_combinations_ordered = Tools::getValue('display_combinations_ordered');
            }

            if ($id_product) {
                $list = NtStats::getListCombinations($id_product, $display_combinations_ordered);
            }

            exit(json_encode(['list' => $list]));
        } elseif (Tools::isSubmit('get_select_list_products')) {
            $id_category = [];
            $display_products_simple = 1;
            $display_products_combinations = 1;
            $display_products_ordered = 0;
            $list = [];

            if (Tools::isSubmit('id_category')) {
                $id_category = Tools::getValue('id_category');
            }

            if (Tools::isSubmit('display_products_simple')) {
                $display_products_simple = Tools::getValue('display_products_simple');
            }

            if (Tools::isSubmit('display_products_combinations')) {
                $display_products_combinations = Tools::getValue('display_products_combinations');
            }

            if (Tools::isSubmit('display_products_ordered')) {
                $display_products_ordered = Tools::getValue('display_products_ordered');
            }

            if (!$display_products_ordered) {
                $list = NtStats::getListProducts($id_category, $display_products_simple, $display_products_combinations);
            } else {
                $list = NtStats::getListOrderProducts($id_category, $display_products_simple, $display_products_combinations);
            }

            exit(json_encode(['list' => $list]));
        } elseif (Tools::isSubmit('get_select_list_feature_values')) {
            $id_feature = [];

            if (Tools::isSubmit('id_feature')) {
                $id_feature = Tools::getValue('id_feature');
            }

            $list = NtStats::getListFeatureValues($id_feature);

            exit(json_encode(['list' => $list]));
        } elseif (Tools::isSubmit('save_table_config')) {
            if (Tools::isSubmit('config') && Tools::isSubmit('name')) {
                $table_name = trim(Tools::getValue('name'));
                $config = trim(Tools::getValue('config'));

                if ($config && $table_name) {
                    $old_config = NtsTablesConfig::getByName($table_name);

                    if (isset($old_config['id_nts_tables_config']) && $old_config['id_nts_tables_config']) {
                        if (isset($old_config['config'])) {
                            $a_old_config = json_decode($old_config['config'], true);
                            $a_config = json_decode($config, true);

                            $a_old_config['time'] = 0;
                            $a_config['time'] = 0;

                            if (!isset($a_old_config['childRows'])) {
                                $a_old_config['childRows'] = [];
                            }

                            if (!isset($a_config['childRows'])) {
                                $a_config['childRows'] = [];
                            }

                            $temp_old_config = json_encode($a_old_config);
                            $temp_config = json_encode($a_config);

                            if ($temp_old_config != $temp_config) {
                                $nts_tables_config = new NtsTablesConfig($old_config['id_nts_tables_config']);
                                $nts_tables_config->config = $config;

                                if (!$nts_tables_config->update()) {
                                    exit(json_encode(['result' => false]));
                                }
                            }
                        }
                    } else {
                        $nts_tables_config = new NtsTablesConfig();
                        $nts_tables_config->name = $table_name;
                        $nts_tables_config->config = $config;

                        if (!$nts_tables_config->add()) {
                            exit(json_encode(['result' => false]));
                        }
                    }
                }
            }

            exit(json_encode(['result' => true]));
        } elseif (Tools::isSubmit('get_table_config')) {
            if (Tools::isSubmit('name')) {
                $table_name = trim(Tools::getValue('name'));
                $config = '{}';

                if ($table_name) {
                    $old_config = NtsTablesConfig::getByName($table_name);

                    if (isset($old_config['config'])) {
                        $config = $old_config['config'];
                    }
                }
            }

            exit(json_encode(['config' => $config]));
        }
    }

    public function renderView()
    {
        $shop = $this->context->shop;
        $id_lang = $this->context->language->id;
        $employee_id_profile = $this->context->employee->id_profile;
        $physic_path_modules = realpath(_PS_ROOT_DIR_ . '/modules') . '/';
        $shop_type = $shop->getContextType();
        $shop_name = $this->l('All shops', self::PAGE);
        $super_admin = ($employee_id_profile == _PS_ADMIN_PROFILE_) ? true : false;

        if ($shop_type == Shop::CONTEXT_SHOP) {
            $shop_name = $shop->name;
        } elseif ($shop_type == Shop::CONTEXT_GROUP) {
            $o_shop_group = new ShopGroup($shop->id_shop_group);
            $shop_name = $o_shop_group->name;
        }

        $http_context = stream_context_create(
            ['http' => [
                'timeout' => 1,
            ],
            ]
        );

        $available_version = Tools::file_get_contents(NtStats::URL_VERSION, false, $http_context, 1);

        // version_compare return -1 if first version is smaller than the second,
        // 0 if they are equals and 1 if the second is smaller than the first
        // $available_version < $this->nts->version
        if (version_compare($this->nts->version, $available_version) == 1) {
            $available_version = 0; // Make sur the test in smarty will display the right thing
        }

        // Add IP for maintenance mode
        $this->nts->setMaintenanceIP();

        $domain_use = Tools::getHttpHost();
        $protocol = Tools::getCurrentUrlProtocolPrefix();
        $shop_domain = $protocol . $domain_use;
        $base_uri = $shop->getBaseURI();
        $shop_url = $shop_domain . __PS_BASE_URI__;

        if ($base_uri == '/') {
            $base_uri = '';
        }

        $url_modules = $shop_url . 'modules/';
        $url_cron = $url_modules . $this->nts->name . '/crons';
        $documentation = $url_modules . $this->nts->name . '/readme_en.pdf';
        $changelog = $url_modules . $this->nts->name . '/changelog.txt';
        $ajax_loader = $url_modules . $this->nts->name . '/views/img/ajax-loader.gif';
        $documentation_name = 'readme_en.pdf';

        if (Tools::file_exists_cache(
            $physic_path_modules . $this->nts->name . '/readme_' . $this->context->language->iso_code . '.pdf'
        )
        ) {
            $documentation = $url_modules . $this->nts->name . '/readme_' . $this->context->language->iso_code . '.pdf';
            $documentation_name = 'readme_' . $this->context->language->iso_code . '.pdf';
        }

        $display_translate_tab = true;
        $translate_lng = [];
        $translate_files = glob($physic_path_modules . $this->nts->name . '/translations/*.php');

        foreach ($translate_files as $trslt_file) {
            $translate_lng[] = basename($trslt_file, '.php');
        }

        if (in_array($this->context->language->iso_code, $translate_lng)) {
            $display_translate_tab = false;
        }

        $our_modules = [
            [
                'link' => ($this->context->language->iso_code == 'fr') ? NtStats::BCK_FLL_LINK_FR : NtStats::BCK_FLL_LINK,
                'name' => 'NT ' . $this->l('Backup And Restore', self::PAGE),
                'desc' => $this->l('Backup your prestashop site and easily restore it wherever you want', self::PAGE),
                'logo' => 'logo_ntbr.png',
            ],
            [
                'link' => ($this->context->language->iso_code == 'fr') ? NtStats::BCK_LGHT_LINK_FR : NtStats::BCK_LGHT_LINK,
                'name' => 'NT ' . $this->l('Backup And Restore Light', self::PAGE),
                'desc' => $this->l('Backup your prestashop site and easily restore it wherever you want', self::PAGE),
                'logo' => 'logo_ntbr.png',
            ],
            [
                'link' => ($this->context->language->iso_code == 'fr') ? NtStats::GEOLOC_LINK_FR : NtStats::GEOLOC_LINK,
                'name' => 'NT ' . $this->l('Geolocation', self::PAGE),
                'desc' => $this->l('Precisely geolocate on a map the postal addresses of customers, stores, manufacturers, suppliers and warehouses', self::PAGE),
                'logo' => 'logo_ntgeoloc.png',
            ],
            [
                'link' => ($this->context->language->iso_code == 'fr') ? NtStats::REDUC_LINK_FR : NtStats::REDUC_LINK,
                'name' => 'NT ' . $this->l('Reduction', self::PAGE),
                'desc' => $this->l('Easy and fast massive discount', self::PAGE),
                'logo' => 'logo_ntreduc.png',
            ],
            [
                'link' => ($this->context->language->iso_code == 'fr') ? NtStats::DEB_LINK_FR : '',
                'name' => 'NT ' . $this->l('DEB', self::PAGE),
                'desc' => $this->l('Create a DEB file you can import on Prodouane', self::PAGE),
                'logo' => 'logo_ntdeb.png',
            ],
        ];

        if ($this->context->language->iso_code == 'fr') {
            $link_contact = NtStats::CONTACT_LINK_FR;
            $link_rate = NtStats::RATE_LINK_FR;
        } else {
            $link_contact = NtStats::CONTACT_LINK;
            $link_rate = NtStats::RATE_LINK;
        }

        switch (Shop::getContext()) {
            case Shop::CONTEXT_SHOP:
                $a_config = NtsConfig::getConfig($shop->id, $shop->id_shop_group, $id_lang);
                break;
            case Shop::CONTEXT_GROUP:
                $a_config = NtsConfig::getConfig(0, Shop::getContextShopGroupID(), $id_lang); // $shop->id_shop_group may not the correct group in context_group
                break;
            case Shop::CONTEXT_ALL:
                $a_config = NtsConfig::getConfig(0, 0, $id_lang);
                break;
            default:
                $a_config = NtsConfig::getConfig($shop->id, $shop->id_shop_group, $id_lang);
        }

        $today = date('Y-m-d');
        $date_to = date('Y-m-d', mktime(23, 59, 59, date('m'), date('j'), date('Y')));
        $date_from_prev = date('Y-m-d', mktime(0, 0, 0, date('m') - 1, date('j'), date('Y') - 1));
        $date_to_prev = date('Y-m-d', mktime(23, 59, 59, date('m'), date('j'), date('Y') - 1));

        if ($a_config['default_period'] == NtsConfig::DEFAULT_PERIOD_ALL_DATE) {
            $date_from = '0000-00-00';
        } elseif ($a_config['default_period'] == NtsConfig::DEFAULT_PERIOD_LAST_THREE_YEARS) {
            $date_from = date('Y-m-d', mktime(0, 0, 0, date('m'), date('j'), date('Y') - 3));
        } elseif ($a_config['default_period'] == NtsConfig::DEFAULT_PERIOD_LAST_YEAR) {
            $date_from = date('Y-m-d', mktime(0, 0, 0, date('m'), date('j'), date('Y') - 1));
        } elseif ($a_config['default_period'] == NtsConfig::DEFAULT_PERIOD_LAST_THREE_MONTHS) {
            $date_from = date('Y-m-d', mktime(0, 0, 0, date('m') - 3, date('j'), date('Y')));
        } else {
            $date_from = date('Y-m-d', mktime(0, 0, 0, date('m') - 1, date('j'), date('Y')));
        }

        if (Configuration::get('NTSTATS_NEW_VERSION_MSG')) {
            $display_new_version_msg = true;
            Configuration::deleteByName('NTSTATS_NEW_VERSION_MSG');
        } else {
            $display_new_version_msg = false;
        }

        $this->nts->writeCronFiles(false);

        if ($this->nts->checkValidIp()) {
            $activate_2nt_automation = true;
        } else {
            $activate_2nt_automation = false;
        }

        $shop_url_changed = false;

        if ($shop_url != $a_config['last_shop_url']) {
            $shop_url_changed = true;
        }

        $php_version = Tools::substr(phpversion(), 0, 3);
        $enable_excel = true;

        if (version_compare($php_version, '7.1', '<')) {
            $enable_excel = false;
        }

        if (Tools::isSubmit('nttab')) {
            $nttab = Tools::getValue('nttab');
        } else {
            $nttab = 'nt_tab0';
        }

        $this->tpl_view_vars = [
            'link_contact' => $link_contact,
            'link_rate' => $link_rate,
            'config' => $a_config,
            'changelog' => $changelog,
            'documentation' => $documentation,
            'documentation_name' => $documentation_name,
            'display_translate_tab' => $display_translate_tab,
            'version' => $this->nts->version,
            'available_version' => $available_version,
            'ajax_loader' => $ajax_loader,
            'today' => $today,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'date_from_prev' => $date_from_prev,
            'date_to_prev' => $date_to_prev,
            'our_modules' => $our_modules,
            'list_categories' => [],
            'list_products' => NtStats::getListProducts(),
            'list_products_with_combinations' => NtStats::getListProducts([], false, true),
            'list_order_cart_rules' => NtStats::getListOrderCartRules(),
            'list_order_products' => NtStats::getListOrderProducts(),
            'list_order_products_with_combinations' => NtStats::getListOrderProducts([], false, true),
            'list_order_manufacturers' => NtStats::getListOrderManufacturers(),
            'list_carriers' => NtStats::getListCarriers(),
            'list_manufacturers' => NtStats::getListManufacturers(),
            'list_countries' => NtStats::getListOrderCountries(),
            'list_all_countries' => NtStats::getListAllCountries(),
            'list_groups' => NtStats::getListGroups(),
            'list_features' => NtStats::getListFeatures(),
            'list_payments' => NtsConfigPaymentMethod::getPaymentMethodByIdConfig($a_config['id_nts_config']),
            'use_invoice' => NtStats::useInvoice(),
            'activate_2nt_automation' => $activate_2nt_automation,
            'current_hour' => date('H:i:s'),
            'time_zone' => date_default_timezone_get(),
            'shop_url_changed' => $shop_url_changed,
            'url_cron' => $url_cron,
            'path_cron' => str_replace('\\', '/', $physic_path_modules) . $this->nts->name . '/crons',
            'secure_key' => $this->nts->secure_key,
            'email_alert_type_included' => NtsConfig::EMAIL_ALERT_TYPE_INCLUDED,
            'period_last_month' => NtsConfig::DEFAULT_PERIOD_LAST_MONTH,
            'period_last_three_months' => NtsConfig::DEFAULT_PERIOD_LAST_THREE_MONTHS,
            'period_last_year' => NtsConfig::DEFAULT_PERIOD_LAST_YEAR,
            'period_last_three_years' => NtsConfig::DEFAULT_PERIOD_LAST_THREE_YEARS,
            'period_all_date' => NtsConfig::DEFAULT_PERIOD_ALL_DATE,
            'email_alert_type_csv' => NtsConfig::EMAIL_ALERT_TYPE_CSV,
            'email_alert_type_excel' => NtsConfig::EMAIL_ALERT_TYPE_EXCEL,
            'email_alert_active_all' => NtsConfig::EMAIL_ALERT_ACTIVE_ALL,
            'email_alert_active_no' => NtsConfig::EMAIL_ALERT_ACTIVE_NO,
            'email_alert_active_yes' => NtsConfig::EMAIL_ALERT_ACTIVE_YES,
            'order_type_date_invoice' => NtsConfig::ORDER_TYPE_DATE_INVOICE,
            'order_type_date_state' => NtsConfig::ORDER_TYPE_DATE_STATE,
            'order_type_date_add' => NtsConfig::ORDER_TYPE_DATE_ADD,
            'order_type_location_invoice' => NtsConfig::ORDER_TYPE_LOCATION_INVOICE,
            'order_type_location_delivery' => NtsConfig::ORDER_TYPE_LOCATION_DELIVERY,
            'enable_excel' => $enable_excel,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'shop_name' => $shop_name,
            'super_admin' => $super_admin,
            'list_order_return_states' => OrderReturnState::getOrderReturnStates($id_lang),
            'list_order_states' => NtsConfig::getValidOrderStates($id_lang),
            'display_new_version_msg' => $display_new_version_msg,
            'nttab' => $nttab,
        ];

        return parent::renderView();
    }

    public function updShopUrl()
    {
        // Current shop
        $id_shop = Context::getContext()->shop->id;
        $id_shop_group = Context::getContext()->shop->id_shop_group;
        $id_lang = Context::getContext()->language->id;

        // Get config
        switch (Shop::getContext()) {
            case Shop::CONTEXT_SHOP:
                $a_config = NtsConfig::getConfig($id_shop, $id_shop_group, $id_lang);
                break;
            case Shop::CONTEXT_GROUP:
                $a_config = NtsConfig::getConfig(0, Shop::getContextShopGroupID(), $id_lang); // $id_shop_group may not the correct group in context_group
                break;
            case Shop::CONTEXT_ALL:
                $a_config = NtsConfig::getConfig(0, 0, $id_lang);
                break;
            default:
                $a_config = NtsConfig::getConfig($id_shop, $id_shop_group, $id_lang);
        }

        $result = true;
        $shop_domain = Tools::getCurrentUrlProtocolPrefix() . Tools::getHttpHost();
        $shop_url = $shop_domain . __PS_BASE_URI__;
        $old_shop_url = (isset($a_config['last_shop_url'])) ? $a_config['last_shop_url'] : '';
        $automation = (isset($a_config['automation_2nt'])) ? $a_config['automation_2nt'] : 0;
        $hours = (isset($a_config['automation_2nt_hours'])) ? $a_config['automation_2nt_hours'] : 0;
        $minutes = (isset($a_config['automation_2nt_minutes'])) ? $a_config['automation_2nt_minutes'] : 0;
        $origin = $this->nts->name;

        // Disable automation for old url
        if ($old_shop_url && $automation) {
            // Call the 2NT cron url
            $url = CONFIGURE_NTCRON
            . 'site=' . urlencode((string) $old_shop_url)
            . '&o=' . Tools::strtoupper((string) $origin)
            . '&enable=0'
            . '&h=' . $hours
            . '&m=' . $minutes
            . '&fuseau_h=' . urlencode((string) date_default_timezone_get())
            . '&securekey=' . urlencode((string) $this->nts->secure_key)
            . '&id_config=' . (int) $a_config['id_nts_config'];

            $ntcron_result = Tools::file_get_contents($url);

            $result = ($ntcron_result == 'OK');
        }

        $o_config = new NtsConfig($a_config['id_nts_config']);
        $o_config->last_shop_url = $shop_url;

        // We try to update the configuration
        if (!$o_config->update()) {
            $result = false;
        }

        // Enable automation for new url
        if ($result && $automation) {
            // Call the 2NT cron url
            $url = CONFIGURE_NTCRON
            . 'site=' . urlencode((string) $shop_url)
            . '&o=' . Tools::strtoupper((string) $origin)
            . '&enable=1'
            . '&h=' . $hours
            . '&m=' . $minutes
            . '&fuseau_h=' . urlencode((string) date_default_timezone_get())
            . '&securekey=' . urlencode((string) $this->nts->secure_key)
            . '&id_config=' . (int) $a_config['id_nts_config'];

            $ntcron_result = Tools::file_get_contents($url);

            $result = ($ntcron_result == 'OK');
        }

        exit(json_encode(['result' => $result]));
    }

    public function saveAutomation()
    {
        $automation_2nt_ip = (int) Tools::getValue('automation_2nt_ip');
        $result = false;
        $update_maintenance_ip = false;

        // Current shop
        $id_shop = Context::getContext()->shop->id;
        $id_shop_group = Context::getContext()->shop->id_shop_group;
        $id_lang = Context::getContext()->language->id;

        // Get config
        switch (Shop::getContext()) {
            case Shop::CONTEXT_SHOP:
                $a_config = NtsConfig::getConfig($id_shop, $id_shop_group, $id_lang);
                break;
            case Shop::CONTEXT_GROUP:
                $a_config = NtsConfig::getConfig(0, Shop::getContextShopGroupID(), $id_lang); // $id_shop_group may not the correct group in context_group
                break;
            case Shop::CONTEXT_ALL:
                $a_config = NtsConfig::getConfig(0, 0, $id_lang);
                break;
            default:
                $a_config = NtsConfig::getConfig($id_shop, $id_shop_group, $id_lang);
        }

        $o_config = new NtsConfig($a_config['id_nts_config']);

        // If not in localhost (so automation can be activated)
        if (Tools::isSubmit('automation_2nt')
            && Tools::isSubmit('automation_2nt_hours')
            && Tools::isSubmit('automation_2nt_minutes')
        ) {
            $automation_2nt = (int) (bool) Tools::getValue('automation_2nt');
            $automation_2nt_hours = (int) Tools::getValue('automation_2nt_hours');
            $automation_2nt_minutes = (int) Tools::getValue('automation_2nt_minutes');

            // If something change
            if ($a_config['automation_2nt'] != $automation_2nt
                || $a_config['automation_2nt_hours'] != $automation_2nt_hours
                || $a_config['automation_2nt_minutes'] != $automation_2nt_minutes
            ) {
                // Call the 2NT cron url
                $shop_domain = Tools::getCurrentUrlProtocolPrefix() . Tools::getHttpHost();
                $shop_url = $shop_domain . __PS_BASE_URI__;
                $origin = $this->nts->name;

                $url = CONFIGURE_NTCRON
                . 'site=' . urlencode((string) $shop_url)
                . '&o=' . Tools::strtoupper($origin)
                . '&enable=' . $automation_2nt
                . '&h=' . $automation_2nt_hours
                . '&m=' . $automation_2nt_minutes
                . '&fuseau_h=' . urlencode((string) date_default_timezone_get())
                . '&securekey=' . urlencode((string) $this->nts->secure_key)
                . '&id_config=' . (int) $a_config['id_nts_config'];

                $ntcron_result = Tools::file_get_contents($url);

                $result = ($ntcron_result == 'OK');

                if ($result) {
                    // Update with the new values
                    $o_config->automation_2nt = $automation_2nt;
                    $o_config->automation_2nt_hours = $automation_2nt_hours;
                    $o_config->automation_2nt_minutes = $automation_2nt_minutes;

                    if (!$o_config->update()) {
                        $result = false;
                    }

                    if ($automation_2nt) {
                        $update_maintenance_ip = true;
                    }
                }
            } else {
                $result = true;
            }
        } else {
            $result = true;
        }

        if ($o_config->automation_2nt_ip != $automation_2nt_ip) {
            $o_config->automation_2nt_ip = $automation_2nt_ip;

            if (!$o_config->update()) {
                $result = false;
            }

            $update_maintenance_ip = true;
        }

        if ($result && $update_maintenance_ip) {
            // Update automation IP in maintenance
            $this->nts->setMaintenanceIP();
        }

        exit(json_encode(['result' => $result]));
    }

    public function exportFile($headers, $data)
    {
        if (Tools::isSubmit('type')) {
            $type = Tools::getValue('type');
        } else {
            $type = 'csv';
        }

        $shop_name = '';
        $tab_name = '';

        if (Tools::isSubmit('shop_name')) {
            $shop_name = Tools::getValue('shop_name');
        }

        if (Tools::isSubmit('tab_name')) {
            $tab_name = preg_replace('/[^a-zA-Z0-9-._]/i', '_', Tools::replaceAccentedChars(trim(Tools::getValue('tab_name'))));
        }

        $filename = $shop_name . ($shop_name ? ' - ' : '') . $tab_name . ($tab_name ? ' - ' : '') . date('Y.m.d_H.i.s');

        if (Tools::strtolower($type) == 'xls') {
            if (version_compare(_PS_VERSION_, '1.7.6.0', '<') === true) {
                $physic_path_modules = realpath(_PS_ROOT_DIR_ . '/modules') . '/' . $this->nts->name . '/';
                // require_once $physic_path_modules.'lib/phpspreadsheet-1.18.0/vendor/autoload.php';
                require_once $physic_path_modules . 'lib/phpspreadsheet-1.12.0/vendor/autoload.php';
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $line_index = 1;
            $column_index = 1;

            // Put the header
            foreach ($headers as $header) {
                $sheet->setCellValueByColumnAndRow($column_index, $line_index, $header);
                ++$column_index;
            }

            // Put the data
            foreach ($data as $fields) {
                $column_index = 1;
                ++$line_index;

                foreach ($fields as $field) {
                    $sheet->setCellValueByColumnAndRow($column_index, $line_index, $field);
                    ++$column_index;
                }
            }

            // Auto size all columns
            $highest_column_letter = $sheet->getHighestColumn();

            for ($column_letter = 'A'; $column_letter <= $highest_column_letter; ++$column_letter) {
                $sheet->getColumnDimension($column_letter)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);

            header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $writer->save('php://output');

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            header('Content-Type: text/csv');
            $file = fopen('php://output', 'w');

            // Put the header
            fputcsv($file, $headers, ';');

            // Put the data
            foreach ($data as $fields) {
                fputcsv($file, $fields, ';');
            }

            fclose($file);
        }

        exit;
    }

    public function getTotalSale()
    {
        $date_from = Tools::getValue('total_sales_date_from');
        $date_to = Tools::getValue('total_sales_date_to');
        $id_group = Tools::getValue('total_sales_id_group');
        $data = $this->nts->getTotalSales($date_from, $date_to, $id_group);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportTotalSale()
    {
        $date_from = Tools::getValue('total_sales_date_from');
        $date_to = Tools::getValue('total_sales_date_to');
        $id_group = Tools::getValue('total_sales_id_group');

        $header = [
            $this->l('Days', self::PAGE),
            $this->l('Nb orders', self::PAGE),
            $this->l('Product', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Product VAT', self::PAGE),
            $this->l('Shipping', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Shipping VAT', self::PAGE),
            $this->l('Shipping refund', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Shipping refund VAT', self::PAGE),
            $this->l('Product refund', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Product refund VAT', self::PAGE),
            $this->l('Discount', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Discount VAT', self::PAGE),
            $this->l('Purchase cost', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Margin', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Margin', self::PAGE) . ' %',
            $this->l('Sales', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Average cart', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
        ];

        $data = $this->nts->getTotalSales($date_from, $date_to, $id_group, true);

        $this->exportFile($header, $data);
    }

    public function getCompareTotalSale()
    {
        $date_from_p1 = Tools::getValue('compare_total_sales_date_from_period1');
        $date_to_p1 = Tools::getValue('compare_total_sales_date_to_period1');
        $date_from_p2 = Tools::getValue('compare_total_sales_date_from_period2');
        $date_to_p2 = Tools::getValue('compare_total_sales_date_to_period2');
        $id_group = Tools::getValue('compare_total_sales_id_group');
        $data = [];
        $data[] = NtStats::getCompareTotalSales($date_from_p1, $date_to_p1, $id_group);
        $data[] = NtStats::getCompareTotalSales($date_from_p2, $date_to_p2, $id_group);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCompareTotalSale()
    {
        $date_from_p1 = Tools::getValue('compare_total_sales_date_from_period1');
        $date_to_p1 = Tools::getValue('compare_total_sales_date_to_period1');
        $date_from_p2 = Tools::getValue('compare_total_sales_date_from_period2');
        $date_to_p2 = Tools::getValue('compare_total_sales_date_to_period2');
        $id_group = Tools::getValue('compare_total_sales_id_group');

        $header = [
            $this->l('From', self::PAGE),
            $this->l('To', self::PAGE),
            $this->l('Nb orders', self::PAGE),
            $this->l('Product', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Shipping', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Shipping refund', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Product refund', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Discount', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Purchase cost', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Margin', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Sales', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Average cart', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
        ];

        $data = [];
        $data[] = NtStats::getCompareTotalSales($date_from_p1, $date_to_p1, $id_group, true);
        $data[] = NtStats::getCompareTotalSales($date_from_p2, $date_to_p2, $id_group, true);

        $this->exportFile($header, $data);
    }

    public function getTotalCategoriesSale()
    {
        $date_from = Tools::getValue('total_categories_sales_date_from');
        $date_to = Tools::getValue('total_categories_sales_date_to');
        $id_category = Tools::getValue('total_categories_sales_id_category');
        $id_group = Tools::getValue('total_categories_sales_id_group');
        $data = $this->nts->getTotalCategoriesSales($date_from, $date_to, $id_category, $id_group);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportTotalCategoriesSale()
    {
        $date_from = Tools::getValue('total_categories_sales_date_from');
        $date_to = Tools::getValue('total_categories_sales_date_to');
        $id_category = Tools::getValue('total_categories_sales_id_category');
        $id_group = Tools::getValue('total_categories_sales_id_group');

        $header = [
            $this->l('Month', self::PAGE),
            $this->l('Qty sold', self::PAGE),
            $this->l('Product', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Purchase cost', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Discount', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Qty returned', self::PAGE),
            $this->l('Refund', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Margin', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Qty returned', self::PAGE) . ' (%)',
            $this->l('Refund amount', self::PAGE) . ' (%)',
        ];

        $data = $this->nts->getTotalCategoriesSales($date_from, $date_to, $id_category, $id_group, true);

        $this->exportFile($header, $data);
    }

    public function getCompareTotalCategoriesSale()
    {
        $date_from_p1 = Tools::getValue('compare_total_categories_sales_date_from_period1');
        $date_to_p1 = Tools::getValue('compare_total_categories_sales_date_to_period1');
        $date_from_p2 = Tools::getValue('compare_total_categories_sales_date_from_period2');
        $date_to_p2 = Tools::getValue('compare_total_categories_sales_date_to_period2');
        $id_category = Tools::getValue('compare_total_categories_sales_id_category');
        $id_group = Tools::getValue('compare_total_categories_sales_id_group');
        $data = [];
        $data[] = NtStats::getCompareTotalCategoriesSales($date_from_p1, $date_to_p1, $id_category, $id_group);
        $data[] = NtStats::getCompareTotalCategoriesSales($date_from_p2, $date_to_p2, $id_category, $id_group);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCompareTotalCategoriesSale()
    {
        $date_from_p1 = Tools::getValue('compare_total_categories_sales_date_from_period1');
        $date_to_p1 = Tools::getValue('compare_total_categories_sales_date_to_period1');
        $date_from_p2 = Tools::getValue('compare_total_categories_sales_date_from_period2');
        $date_to_p2 = Tools::getValue('compare_total_categories_sales_date_to_period2');
        $id_category = Tools::getValue('compare_total_categories_sales_id_category');
        $id_group = Tools::getValue('compare_total_categories_sales_id_group');

        $header = [
            $this->l('From', self::PAGE),
            $this->l('To', self::PAGE),
            $this->l('Qty sold', self::PAGE),
            $this->l('Product', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Purchase cost', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Discount', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Qty returned', self::PAGE),
            $this->l('Refund', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Margin', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Qty returned', self::PAGE) . ' (%)',
            $this->l('Refund amount', self::PAGE) . ' (%)',
        ];

        $data = [];
        $data[] = NtStats::getCompareTotalCategoriesSales($date_from_p1, $date_to_p1, $id_category, $id_group, true);
        $data[] = NtStats::getCompareTotalCategoriesSales($date_from_p2, $date_to_p2, $id_category, $id_group, true);

        $this->exportFile($header, $data);
    }

    public function getTotalProductsSale()
    {
        $date_from = Tools::getValue('total_products_sales_date_from');
        $date_to = Tools::getValue('total_products_sales_date_to');
        $id_group = Tools::getValue('total_products_sales_id_group');
        $id_category = Tools::getValue('total_products_sales_id_category');
        $id_manufacturer = Tools::getValue('total_products_sales_id_manufacturer');
        $id_country_invoice = Tools::getValue('total_products_sales_id_country_invoice');
        $id_feature = Tools::getValue('total_products_sales_id_feature');
        $id_feature_value = Tools::getValue('total_products_sales_id_feature_value');
        $id_product = Tools::getValue('total_products_sales_id_product');
        $product_simple = Tools::getValue('total_products_sales_products_simple');

        $data = $this->nts->getTotalProductsSales(
            $date_from,
            $date_to,
            $id_category,
            $id_manufacturer,
            $id_country_invoice,
            $id_product,
            $id_group,
            $id_feature,
            $id_feature_value,
            $product_simple
        );

        exit(json_encode(['data_list' => $data]));
    }

    public function exportTotalProductsSales()
    {
        $date_from = Tools::getValue('total_products_sales_date_from');
        $date_to = Tools::getValue('total_products_sales_date_to');
        $id_group = Tools::getValue('total_products_sales_id_group');
        $id_category = Tools::getValue('total_products_sales_id_category');
        $id_manufacturer = Tools::getValue('total_products_sales_id_manufacturer');
        $id_country_invoice = Tools::getValue('total_products_sales_id_country_invoice');
        $id_feature = Tools::getValue('total_products_sales_id_feature');
        $id_feature_value = Tools::getValue('total_products_sales_id_feature_value');
        $id_product = Tools::getValue('total_products_sales_id_product');
        $product_simple = Tools::getValue('total_products_sales_products_simple');

        $header = [
            $this->l('Reference', self::PAGE),
            $this->l('Name', self::PAGE),
            $this->l('Qty sold', self::PAGE),
            $this->l('Qty current', self::PAGE),
            $this->l('Need', self::PAGE),
            $this->l('Stock duration', self::PAGE),
            $this->l('Sellout', self::PAGE) . ' (%)',
            $this->l('Qty sold', self::PAGE) . ' (%)',
            $this->l('Product', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Qty returned', self::PAGE),
            $this->l('Nb customers', self::PAGE),
            $this->l('Nb customers', self::PAGE) . ' (%)',
            $this->l('Refund', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Qty returned', self::PAGE) . ' (%)',
            $this->l('Refund amount', self::PAGE) . ' (%)',
            $this->l('Cost', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Order discount', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Unit margin', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Margin', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Margin', self::PAGE) . ' (%)',
        ];

        $data = $this->nts->getTotalProductsSales(
            $date_from,
            $date_to,
            $id_category,
            $id_manufacturer,
            $id_country_invoice,
            $id_product,
            $id_group,
            $id_feature,
            $id_feature_value,
            $product_simple,
            true
        );

        $this->exportFile($header, $data);
    }

    public function getCompareTotalProductsSale()
    {
        $date_from_p1 = Tools::getValue('compare_total_products_sales_date_from_period1');
        $date_to_p1 = Tools::getValue('compare_total_products_sales_date_to_period1');
        $date_from_p2 = Tools::getValue('compare_total_products_sales_date_from_period2');
        $date_to_p2 = Tools::getValue('compare_total_products_sales_date_to_period2');
        $id_group = Tools::getValue('compare_total_products_sales_id_group');
        $id_category = Tools::getValue('compare_total_products_sales_id_category');
        $id_manufacturer = Tools::getValue('compare_total_products_sales_id_manufacturer');
        $id_feature = Tools::getValue('compare_total_products_sales_id_feature');
        $id_feature_value = Tools::getValue('compare_total_products_sales_id_feature_value');
        $id_product = Tools::getValue('compare_total_products_sales_id_product');
        $product_simple = Tools::getValue('compare_total_products_sales_products_simple');

        $data = [];

        $data[] = NtStats::getCompareTotalProductsSales(
            $date_from_p1,
            $date_to_p1,
            $id_product,
            $id_group,
            $id_category,
            $id_manufacturer,
            $id_feature,
            $id_feature_value,
            $product_simple
        );

        $data[] = NtStats::getCompareTotalProductsSales(
            $date_from_p2,
            $date_to_p2,
            $id_product,
            $id_group,
            $id_category,
            $id_manufacturer,
            $id_feature,
            $id_feature_value,
            $product_simple
        );

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCompareTotalProductsSale()
    {
        $date_from_p1 = Tools::getValue('compare_total_products_sales_date_from_period1');
        $date_to_p1 = Tools::getValue('compare_total_products_sales_date_to_period1');
        $date_from_p2 = Tools::getValue('compare_total_products_sales_date_from_period2');
        $date_to_p2 = Tools::getValue('compare_total_products_sales_date_to_period2');
        $id_category = Tools::getValue('compare_total_products_sales_id_category');
        $id_manufacturer = Tools::getValue('compare_total_products_sales_id_manufacturer');
        $id_feature = Tools::getValue('compare_total_products_sales_id_feature');
        $id_feature_value = Tools::getValue('compare_total_products_sales_id_feature_value');
        $id_product = Tools::getValue('compare_total_products_sales_id_product');
        $id_group = Tools::getValue('compare_total_products_sales_id_group');
        $product_simple = Tools::getValue('compare_total_products_sales_products_simple');

        $header = [
            $this->l('From', self::PAGE),
            $this->l('To', self::PAGE),
            $this->l('Qty sold', self::PAGE),
            $this->l('Product', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Qty returned', self::PAGE),
            $this->l('Refund', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Purchase cost', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Discount', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Margin', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Qty returned', self::PAGE) . ' (%)',
            $this->l('Refund amount', self::PAGE) . ' (%)',
        ];

        $data = [];

        $data[] = NtStats::getCompareTotalProductsSales(
            $date_from_p1,
            $date_to_p1,
            $id_product,
            $id_group,
            $id_category,
            $id_manufacturer,
            $id_feature,
            $id_feature_value,
            $product_simple,
            true
        );

        $data[] = NtStats::getCompareTotalProductsSales(
            $date_from_p2,
            $date_to_p2,
            $id_product,
            $id_group,
            $id_category,
            $id_manufacturer,
            $id_feature,
            $id_feature_value,
            $product_simple,
            true
        );

        $this->exportFile($header, $data);
    }

    public function getTotalManufacturersSale()
    {
        $date_from = Tools::getValue('total_manufacturers_sales_date_from');
        $date_to = Tools::getValue('total_manufacturers_sales_date_to');
        $id_manufacturer = Tools::getValue('total_manufacturers_sales_id_manufacturer');
        $id_group = Tools::getValue('total_manufacturers_sales_id_group');
        $data = $this->nts->getTotalManufacturersSales($date_from, $date_to, $id_manufacturer, $id_group);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportTotalManufacturersSales()
    {
        $date_from = Tools::getValue('total_manufacturers_sales_date_from');
        $date_to = Tools::getValue('total_manufacturers_sales_date_to');
        $id_manufacturer = Tools::getValue('total_manufacturers_sales_id_manufacturer');
        $id_group = Tools::getValue('total_manufacturers_sales_id_group');

        $header = [
            $this->l('Name', self::PAGE),
            $this->l('Qty sold', self::PAGE),
            $this->l('Qty sold', self::PAGE) . ' (%)',
            $this->l('Nb customers', self::PAGE),
            $this->l('Nb customers', self::PAGE) . ' (%)',
            $this->l('Amount', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Qty returned', self::PAGE),
            $this->l('Refund', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Discount', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Purchase cost', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Margin', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Qty returned', self::PAGE) . ' (%)',
            $this->l('Refund amount', self::PAGE) . ' (%)',
        ];

        $data = $this->nts->getTotalManufacturersSales($date_from, $date_to, $id_manufacturer, $id_group, true);

        $this->exportFile($header, $data);
    }

    public function getCompareTotalManufacturersSale()
    {
        $date_from_p1 = Tools::getValue('compare_total_manufacturers_sales_date_from_period1');
        $date_to_p1 = Tools::getValue('compare_total_manufacturers_sales_date_to_period1');
        $date_from_p2 = Tools::getValue('compare_total_manufacturers_sales_date_from_period2');
        $date_to_p2 = Tools::getValue('compare_total_manufacturers_sales_date_to_period2');
        $id_manufacturer = Tools::getValue('compare_total_manufacturers_sales_id_manufacturer');
        $id_group = Tools::getValue('compare_total_manufacturers_sales_id_group');
        $data = [];
        $data[] = NtStats::getCompareTotalManufacturersSales(
            $date_from_p1,
            $date_to_p1,
            $id_manufacturer,
            $id_group
        );
        $data[] = NtStats::getCompareTotalManufacturersSales(
            $date_from_p2,
            $date_to_p2,
            $id_manufacturer,
            $id_group
        );

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCompareTotalManufacturersSale()
    {
        $date_from_p1 = Tools::getValue('compare_total_manufacturers_sales_date_from_period1');
        $date_to_p1 = Tools::getValue('compare_total_manufacturers_sales_date_to_period1');
        $date_from_p2 = Tools::getValue('compare_total_manufacturers_sales_date_from_period2');
        $date_to_p2 = Tools::getValue('compare_total_manufacturers_sales_date_to_period2');
        $id_manufacturer = Tools::getValue('compare_total_manufacturers_sales_id_manufacturer');
        $id_group = Tools::getValue('compare_total_manufacturers_sales_id_group');

        $header = [
            $this->l('From', self::PAGE),
            $this->l('To', self::PAGE),
            $this->l('Qty sold', self::PAGE),
            $this->l('Manufacturer', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Qty returned', self::PAGE),
            $this->l('Refund', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Discount', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Purchase cost', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Margin', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Qty returned', self::PAGE) . ' (%)',
            $this->l('Refund amount', self::PAGE) . ' (%)',
        ];

        $data = [];
        $data[] = NtStats::getCompareTotalManufacturersSales($date_from_p1, $date_to_p1, $id_manufacturer, $id_group, true);
        $data[] = NtStats::getCompareTotalManufacturersSales($date_from_p2, $date_to_p2, $id_manufacturer, $id_group, true);

        $this->exportFile($header, $data);
    }

    public function getTotalPaymentMethodsSale()
    {
        $date_from = Tools::getValue('total_payment_methods_sales_date_from');
        $date_to = Tools::getValue('total_payment_methods_sales_date_to');
        $payment_method = Tools::getValue('total_payment_methods_sales_payment_method');
        $id_group = Tools::getValue('total_payment_methods_sales_id_group');
        $data = $this->nts->getTotalPaymentMethodsSales($date_from, $date_to, $payment_method, $id_group);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportTotalPaymentMethodsSales()
    {
        $date_from = Tools::getValue('total_payment_methods_sales_date_from');
        $date_to = Tools::getValue('total_payment_methods_sales_date_to');
        $payment_method = Tools::getValue('total_payment_methods_sales_payment_method');
        $id_group = Tools::getValue('total_payment_methods_sales_id_group');

        $header = [
            $this->l('Name', self::PAGE),
            $this->l('Nb customers', self::PAGE),
            $this->l('Nb customers', self::PAGE) . ' (%)',
            $this->l('Amount tax incl.', self::PAGE),
        ];

        $data = $this->nts->getTotalPaymentMethodsSales($date_from, $date_to, $payment_method, $id_group, true);

        $this->exportFile($header, $data);
    }

    public function getCompareTotalPaymentMethodsSale()
    {
        $date_from_p1 = Tools::getValue('compare_total_payment_methods_sales_date_from_period1');
        $date_to_p1 = Tools::getValue('compare_total_payment_methods_sales_date_to_period1');
        $date_from_p2 = Tools::getValue('compare_total_payment_methods_sales_date_from_period2');
        $date_to_p2 = Tools::getValue('compare_total_payment_methods_sales_date_to_period2');
        $payment_method = Tools::getValue('compare_total_payment_methods_sales_payment_method');
        $id_group = Tools::getValue('compare_total_payment_methods_sales_id_group');
        $data = [];
        $data[] = NtStats::getCompareTotalPaymentMethodsSales(
            $date_from_p1,
            $date_to_p1,
            $payment_method,
            $id_group
        );
        $data[] = NtStats::getCompareTotalPaymentMethodsSales(
            $date_from_p2,
            $date_to_p2,
            $payment_method,
            $id_group
        );

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCompareTotalPaymentMethodsSale()
    {
        $date_from_p1 = Tools::getValue('compare_total_payment_methods_sales_date_from_period1');
        $date_to_p1 = Tools::getValue('compare_total_payment_methods_sales_date_to_period1');
        $date_from_p2 = Tools::getValue('compare_total_payment_methods_sales_date_from_period2');
        $date_to_p2 = Tools::getValue('compare_total_payment_methods_sales_date_to_period2');
        $payment_method = Tools::getValue('compare_total_payment_methods_sales_payment_method');
        $id_group = Tools::getValue('compare_total_payment_methods_sales_id_group');

        $header = [
            $this->l('From', self::PAGE),
            $this->l('To', self::PAGE),
            $this->l('Amount tax incl.', self::PAGE),
        ];

        $data = [];
        $data[] = NtStats::getCompareTotalPaymentMethodsSales($date_from_p1, $date_to_p1, $payment_method, $id_group, true);
        $data[] = NtStats::getCompareTotalPaymentMethodsSales($date_from_p2, $date_to_p2, $payment_method, $id_group, true);

        $this->exportFile($header, $data);
    }

    public function getTotalCombinationsSale()
    {
        $date_from = Tools::getValue('total_combinations_sales_date_from');
        $date_to = Tools::getValue('total_combinations_sales_date_to');
        $id_category = Tools::getValue('total_combinations_sales_id_category');
        $id_manufacturer = Tools::getValue('total_combinations_sales_id_manufacturer');
        $id_country_invoice = Tools::getValue('total_combinations_sales_id_country_invoice');
        $id_feature = Tools::getValue('total_combinations_sales_id_feature');
        $id_feature_value = Tools::getValue('total_combinations_sales_id_feature_value');
        $id_product = Tools::getValue('total_combinations_sales_id_product');
        $id_combination = Tools::getValue('total_combinations_sales_id_combination');
        $id_group = Tools::getValue('total_combinations_sales_id_group');
        $simple = Tools::getValue('total_combinations_sales_simple');

        $data = $this->nts->getTotalCombinationsSales(
            $date_from,
            $date_to,
            $id_category,
            $id_manufacturer,
            $id_country_invoice,
            $id_product,
            $id_combination,
            $id_group,
            $id_feature,
            $id_feature_value,
            $simple
        );

        exit(json_encode(['data_list' => $data]));
    }

    public function exportTotalCombinationsSales()
    {
        $date_from = Tools::getValue('total_combinations_sales_date_from');
        $date_to = Tools::getValue('total_combinations_sales_date_to');
        $id_category = Tools::getValue('total_combinations_sales_id_category');
        $id_manufacturer = Tools::getValue('total_combinations_sales_id_manufacturer');
        $id_country_invoice = Tools::getValue('total_combinations_sales_id_country_invoice');
        $id_feature = Tools::getValue('total_combinations_sales_id_feature');
        $id_feature_value = Tools::getValue('total_combinations_sales_id_feature_value');
        $id_product = Tools::getValue('total_combinations_sales_id_product');
        $id_combination = Tools::getValue('total_combinations_sales_id_combination');
        $id_group = Tools::getValue('total_combinations_sales_id_group');
        $simple = Tools::getValue('total_combinations_sales_simple');

        $header = [
            $this->l('Reference', self::PAGE),
            $this->l('Product reference', self::PAGE),
            $this->l('Name', self::PAGE),
            $this->l('Qty sold', self::PAGE),
            $this->l('Qty current', self::PAGE),
            $this->l('Need', self::PAGE),
            $this->l('Combination', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Qty returned', self::PAGE),
            $this->l('Refund', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Qty returned', self::PAGE) . ' (%)',
            $this->l('Refund amount', self::PAGE) . ' (%)',
            $this->l('Cost', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Order discount', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Unit margin', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Margin', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Margin', self::PAGE) . ' (%)',
        ];

        $data = $this->nts->getTotalCombinationsSales(
            $date_from,
            $date_to,
            $id_category,
            $id_manufacturer,
            $id_country_invoice,
            $id_product,
            $id_combination,
            $id_group,
            $id_feature,
            $id_feature_value,
            $simple,
            true
        );

        $this->exportFile($header, $data);
    }

    public function getCompareTotalCombinationsSale()
    {
        $date_from_p1 = Tools::getValue('compare_total_combinations_sales_date_from_period1');
        $date_to_p1 = Tools::getValue('compare_total_combinations_sales_date_to_period1');
        $date_from_p2 = Tools::getValue('compare_total_combinations_sales_date_from_period2');
        $date_to_p2 = Tools::getValue('compare_total_combinations_sales_date_to_period2');
        $id_category = Tools::getValue('compare_total_combinations_sales_id_category');
        $id_manufacturer = Tools::getValue('compare_total_combinations_sales_id_manufacturer');
        $id_feature = Tools::getValue('compare_total_combinations_sales_id_feature');
        $id_feature_value = Tools::getValue('compare_total_combinations_sales_id_feature_value');
        $id_product = Tools::getValue('compare_total_combinations_sales_id_product');
        $id_combination = Tools::getValue('compare_total_combinations_sales_id_combination');
        $id_group = Tools::getValue('compare_total_combinations_sales_id_group');
        $simple = Tools::getValue('compare_total_combinations_sales_simple');
        $data = [];
        $data[] = NtStats::getCompareTotalCombinationsSales(
            $date_from_p1,
            $date_to_p1,
            $id_product,
            $id_combination,
            $id_group,
            $id_category,
            $id_manufacturer,
            $id_feature,
            $id_feature_value,
            $simple
        );
        $data[] = NtStats::getCompareTotalCombinationsSales(
            $date_from_p2,
            $date_to_p2,
            $id_product,
            $id_combination,
            $id_group,
            $id_category,
            $id_manufacturer,
            $id_feature,
            $id_feature_value,
            $simple
        );

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCompareTotalCombinationsSale()
    {
        $date_from_p1 = Tools::getValue('compare_total_combinations_sales_date_from_period1');
        $date_to_p1 = Tools::getValue('compare_total_combinations_sales_date_to_period1');
        $date_from_p2 = Tools::getValue('compare_total_combinations_sales_date_from_period2');
        $date_to_p2 = Tools::getValue('compare_total_combinations_sales_date_to_period2');
        $id_category = Tools::getValue('compare_total_combinations_sales_id_category');
        $id_manufacturer = Tools::getValue('compare_total_combinations_sales_id_manufacturer');
        $id_feature = Tools::getValue('compare_total_combinations_sales_id_feature');
        $id_feature_value = Tools::getValue('compare_total_combinations_sales_id_feature_value');
        $id_product = Tools::getValue('compare_total_combinations_sales_id_product');
        $id_combination = Tools::getValue('compare_total_combinations_sales_id_combination');
        $id_group = Tools::getValue('compare_total_combinations_sales_id_group');
        $simple = Tools::getValue('compare_total_combinations_sales_simple');

        $header = [
            $this->l('From', self::PAGE),
            $this->l('To', self::PAGE),
            $this->l('Qty sold', self::PAGE),
            $this->l('Combination', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Qty returned', self::PAGE),
            $this->l('Refund', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Qty returned', self::PAGE) . ' (%)',
            $this->l('Refund amount', self::PAGE) . ' (%)',
            $this->l('Purchase cost', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Discount', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Margin', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
        ];

        $data = [];
        $data[] = NtStats::getCompareTotalCombinationsSales(
            $date_from_p1,
            $date_to_p1,
            $id_product,
            $id_combination,
            $id_group,
            $id_category,
            $id_manufacturer,
            $id_feature,
            $id_feature_value,
            $simple,
            true
        );
        $data[] = NtStats::getCompareTotalCombinationsSales(
            $date_from_p2,
            $date_to_p2,
            $id_product,
            $id_combination,
            $id_group,
            $id_category,
            $id_manufacturer,
            $id_feature,
            $id_feature_value,
            $simple,
            true
        );

        $this->exportFile($header, $data);
    }

    public function getTotalCountriesSale()
    {
        $date_from = Tools::getValue('total_countries_sales_date_from');
        $date_to = Tools::getValue('total_countries_sales_date_to');
        $id_country = Tools::getValue('total_countries_sales_id_country');
        $id_group = Tools::getValue('total_countries_sales_id_group');
        $data = $this->nts->getTotalCountriesSales($date_from, $date_to, $id_country, $id_group);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportTotalCountriesSales()
    {
        $date_from = Tools::getValue('total_countries_sales_date_from');
        $date_to = Tools::getValue('total_countries_sales_date_to');
        $id_country = Tools::getValue('total_countries_sales_id_country');
        $id_group = Tools::getValue('total_countries_sales_id_group');

        $header = [
            $this->l('Country', self::PAGE),
            $this->l('Nb orders', self::PAGE),
            $this->l('Nb products sold', self::PAGE),
            $this->l('Products sold', self::PAGE) . ' (%)',
            $this->l('Nb products returned', self::PAGE),
            $this->l('Products returned', self::PAGE) . ' (%)',
            $this->l('Nb customers', self::PAGE),
            $this->l('Nb customers', self::PAGE) . ' (%)',
            $this->l('Product', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Shipping', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Shipping refund', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Product refund', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Discount', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Purchase cost', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Margin', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Sales', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Taxes', self::PAGE),
            $this->l('Average cart', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
        ];

        $data = $this->nts->getTotalCountriesSales($date_from, $date_to, $id_country, $id_group, true);

        $this->exportFile($header, $data);
    }

    public function getCompareTotalCountriesSale()
    {
        $date_from_p1 = Tools::getValue('compare_total_countries_sales_date_from_period1');
        $date_to_p1 = Tools::getValue('compare_total_countries_sales_date_to_period1');
        $date_from_p2 = Tools::getValue('compare_total_countries_sales_date_from_period2');
        $date_to_p2 = Tools::getValue('compare_total_countries_sales_date_to_period2');
        $id_country = Tools::getValue('compare_total_countries_sales_id_country');
        $id_group = Tools::getValue('compare_total_countries_sales_id_group');
        $data = [];
        $data[] = NtStats::getCompareTotalCountriesSales($date_from_p1, $date_to_p1, $id_country, $id_group);
        $data[] = NtStats::getCompareTotalCountriesSales($date_from_p2, $date_to_p2, $id_country, $id_group);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCompareTotalCountriesSale()
    {
        $date_from_p1 = Tools::getValue('compare_total_countries_sales_date_from_period1');
        $date_to_p1 = Tools::getValue('compare_total_countries_sales_date_to_period1');
        $date_from_p2 = Tools::getValue('compare_total_countries_sales_date_from_period2');
        $date_to_p2 = Tools::getValue('compare_total_countries_sales_date_to_period2');
        $id_country = Tools::getValue('compare_total_countries_sales_id_country');
        $id_group = Tools::getValue('compare_total_countries_sales_id_group');

        $header = [
            $this->l('From', self::PAGE),
            $this->l('To', self::PAGE),
            $this->l('Nb orders', self::PAGE),
            $this->l('Product', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Shipping', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Shipping refund', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Product refund', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Discount', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Purchase cost', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Margin', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Sales', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Taxes', self::PAGE),
            $this->l('Average cart', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
        ];

        $data = [];
        $data[] = NtStats::getCompareTotalCountriesSales($date_from_p1, $date_to_p1, $id_country, $id_group, true);
        $data[] = NtStats::getCompareTotalCountriesSales($date_from_p2, $date_to_p2, $id_country, $id_group, true);

        $this->exportFile($header, $data);
    }

    public function getProducts()
    {
        $already_sold = Tools::getValue('product_already_sold');
        $with_stock = Tools::getValue('product_with_stock');
        $with_stock_mvt = Tools::getValue('product_with_stock_mvt');
        $with_combination = Tools::getValue('product_with_combination');
        $with_stock_out_combination = Tools::getValue('product_with_out_stock_combination');
        $with_image = Tools::getValue('product_with_image');
        $with_cover_image = Tools::getValue('product_with_cover_image');
        $active = Tools::getValue('product_active');
        $with_ean13 = Tools::getValue('product_with_ean13');
        $id_group = Tools::getValue('product_id_group');
        $id_category = Tools::getValue('product_id_category');
        $id_manufacturer = Tools::getValue('product_id_manufacturer');
        $id_feature = Tools::getValue('product_id_feature');
        $id_feature_value = Tools::getValue('product_id_feature_value');

        $data = $this->nts->getProducts(
            $already_sold,
            $with_stock,
            $with_stock_mvt,
            $with_combination,
            $with_stock_out_combination,
            $with_image,
            $with_cover_image,
            $active,
            $with_ean13,
            $id_group,
            $id_category,
            $id_manufacturer,
            $id_feature,
            $id_feature_value
        );

        exit(json_encode(['data_list' => $data]));
    }

    public function exportProducts()
    {
        $already_sold = Tools::getValue('product_already_sold');
        $with_stock = Tools::getValue('product_with_stock');
        $with_stock_mvt = Tools::getValue('product_with_stock_mvt');
        $with_combination = Tools::getValue('product_with_combination');
        $with_stock_out_combination = Tools::getValue('product_with_out_stock_combination');
        $with_image = Tools::getValue('product_with_image');
        $with_cover_image = Tools::getValue('product_with_cover_image');
        $active = Tools::getValue('product_active');
        $with_ean13 = Tools::getValue('product_with_ean13');
        $id_group = Tools::getValue('product_id_group');
        $id_category = Tools::getValue('product_id_category');
        $id_manufacturer = Tools::getValue('product_id_manufacturer');
        $id_feature = Tools::getValue('product_id_feature');
        $id_feature_value = Tools::getValue('product_id_feature_value');

        $header = [
            $this->l('ID', self::PAGE),
            $this->l('Reference', self::PAGE),
            $this->l('Name', self::PAGE),
            $this->l('Combinations out of stock', self::PAGE),
            $this->l('Total combinations', self::PAGE),
            $this->l('Unit purchase price tax excl.', self::PAGE),
            $this->l('Unit price tax excl.', self::PAGE),
            $this->l('Unit margin tax excl.', self::PAGE),
            $this->l('Qty', self::PAGE),
            $this->l('Stock purchase value tax excl.', self::PAGE),
            $this->l('Stock value tax excl.', self::PAGE),
            $this->l('Stock margin tax excl.', self::PAGE),
            $this->l('Qty sold', self::PAGE),
            $this->l('Qty returned', self::PAGE),
            $this->l('Total refunded', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Ean13', self::PAGE),
            $this->l('Active', self::PAGE),
            $this->l('Abandoned cart', self::PAGE),
            $this->l('Creation date', self::PAGE),
        ];

        $data = $this->nts->getProducts(
            $already_sold,
            $with_stock,
            $with_stock_mvt,
            $with_combination,
            $with_stock_out_combination,
            $with_image,
            $with_cover_image,
            $active,
            $with_ean13,
            $id_group,
            $id_category,
            $id_manufacturer,
            $id_feature,
            $id_feature_value,
            true
        );

        $this->exportFile($header, $data);
    }

    public function getProductsWithOutStockCombinations()
    {
        $active = Tools::getValue('products_with_out_stock_combination_active');

        $data = NtStats::getProductsWithOutStockCombinations($active);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportProductsWithOutStockCombinations()
    {
        $active = Tools::getValue('products_with_out_stock_combination_active');

        $header = [
            $this->l('Reference', self::PAGE),
            $this->l('Name', self::PAGE),
            $this->l('Active', self::PAGE),
        ];

        $data = NtStats::getProductsWithOutStockCombinations($active, true);

        $this->exportFile($header, $data);
    }

    public function getProductsWithCombinationsWithoutEnoughStock()
    {
        $active = Tools::getValue('product_with_combinations_without_enough_stock_active');
        $nb_combinations_min_without_stock = Tools::getValue('product_nb_combinations_min_without_stock');

        $data = NtStats::getProductsWithCombinationsWithoutEnoughStock($nb_combinations_min_without_stock, $active);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportProductsWithCombinationsWithoutEnoughStock()
    {
        $active = Tools::getValue('product_with_combinations_without_enough_stock_active');
        $nb_combinations_min_without_stock = Tools::getValue('product_nb_combinations_min_without_stock');

        $header = [
            $this->l('Reference', self::PAGE),
            $this->l('Name', self::PAGE),
            $this->l('Qty', self::PAGE),
            $this->l('Nb combinations with stock', self::PAGE),
            $this->l('Nb combinations total', self::PAGE),
        ];

        $data = NtStats::getProductsWithCombinationsWithoutEnoughStock($nb_combinations_min_without_stock, $active, true);

        $this->exportFile($header, $data);
    }

    public function getCombinations()
    {
        $id_product = Tools::getValue('combination_id_product');
        $already_sold = Tools::getValue('combination_already_sold');
        $with_stock = Tools::getValue('combination_with_stock');
        $min_quantity = Tools::getValue('combination_min_quantity');
        $max_quantity = Tools::getValue('combination_max_quantity');
        $active = Tools::getValue('combination_active');
        $id_group = Tools::getValue('combination_id_group');
        $id_category = Tools::getValue('combination_id_category');
        $id_manufacturer = Tools::getValue('combination_id_manufacturer');
        $id_feature = Tools::getValue('combination_id_feature');
        $id_feature_value = Tools::getValue('combination_id_feature_value');

        $data = NtStats::getCombinations(
            $id_product,
            $already_sold,
            $with_stock,
            $min_quantity,
            $max_quantity,
            $active,
            $id_group,
            $id_category,
            $id_manufacturer,
            $id_feature,
            $id_feature_value
        );

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCombinations()
    {
        $id_product = Tools::getValue('combination_id_product');
        $already_sold = Tools::getValue('combination_already_sold');
        $with_stock = Tools::getValue('combination_with_stock');
        $min_quantity = Tools::getValue('combination_min_quantity');
        $max_quantity = Tools::getValue('combination_max_quantity');
        $active = Tools::getValue('combination_active');
        $id_group = Tools::getValue('combination_id_group');
        $id_category = Tools::getValue('combination_id_category');
        $id_manufacturer = Tools::getValue('combination_id_manufacturer');
        $id_feature = Tools::getValue('combination_id_feature');
        $id_feature_value = Tools::getValue('combination_id_feature_value');

        $header = [
            $this->l('Reference', self::PAGE),
            $this->l('Name', self::PAGE),
            $this->l('Combination', self::PAGE),
            $this->l('Unit purchase price tax excl.', self::PAGE),
            $this->l('Unit price tax excl.', self::PAGE),
            $this->l('Unit margin tax excl.', self::PAGE),
            $this->l('Qty', self::PAGE),
            $this->l('Stock purchase value tax excl.', self::PAGE),
            $this->l('Stock value tax excl.', self::PAGE),
            $this->l('Stock margin tax excl.', self::PAGE),
            $this->l('Qty sold', self::PAGE),
            $this->l('Qty returned', self::PAGE),
            $this->l('Ean13', self::PAGE),
            $this->l('Active', self::PAGE),
        ];

        $data = NtStats::getCombinations(
            $id_product,
            $already_sold,
            $with_stock,
            $min_quantity,
            $max_quantity,
            $active,
            $id_group,
            $id_category,
            $id_manufacturer,
            $id_feature,
            $id_feature_value,
            true
        );

        $this->exportFile($header, $data);
    }

    public function getCombinationsUnsoldWithStock()
    {
        $data = NtStats::getCombinationsUnsoldWithStock();

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCombinationsUnsoldWithStock()
    {
        $header = [
            $this->l('Reference', self::PAGE),
            $this->l('Name', self::PAGE),
            $this->l('Combination', self::PAGE),
            $this->l('Qty', self::PAGE),
            $this->l('Ean13', self::PAGE),
        ];

        $data = NtStats::getCombinationsUnsoldWithStock(true);

        $this->exportFile($header, $data);
    }

    public function getCarriers()
    {
        $date_from = Tools::getValue('carriers_date_from');
        $date_to = Tools::getValue('carriers_date_to');
        $id_carrier = Tools::getValue('carriers_id_carrier');
        $id_group = Tools::getValue('carriers_id_group');
        $data = NtStats::getCarriers($date_from, $date_to, $id_carrier, $id_group);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCarriers()
    {
        $date_from = Tools::getValue('carriers_date_from');
        $date_to = Tools::getValue('carriers_date_to');
        $id_carrier = Tools::getValue('carriers_id_carrier');
        $id_group = Tools::getValue('carriers_id_group');

        $header = [
            $this->l('Month', self::PAGE),
            $this->l('Carrier', self::PAGE),
            $this->l('Orders', self::PAGE),
            $this->l('Amount', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Orders', self::PAGE) . ' (%)',
            $this->l('Amount', self::PAGE) . ' (%)',
        ];

        $data = NtStats::getCarriers($date_from, $date_to, $id_carrier, $id_group, true);

        $this->exportFile($header, $data);
    }

    public function getManufacturers()
    {
        $date_from = Tools::getValue('manufacturers_date_from');
        $date_to = Tools::getValue('manufacturers_date_to');
        $id_manufacturer = Tools::getValue('manufacturers_id_manufacturer');
        $id_group = Tools::getValue('manufacturers_id_group');
        $data = NtStats::getManufacturers($date_from, $date_to, $id_manufacturer, $id_group);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportManufacturers()
    {
        $date_from = Tools::getValue('manufacturers_date_from');
        $date_to = Tools::getValue('manufacturers_date_to');
        $id_manufacturer = Tools::getValue('manufacturers_id_manufacturer');
        $id_group = Tools::getValue('manufacturers_id_group');

        $header = [
            $this->l('Month', self::PAGE),
            $this->l('Manufacturer', self::PAGE),
            $this->l('Orders', self::PAGE),
            $this->l('Amount', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Orders', self::PAGE) . ' (%)',
            $this->l('Amount', self::PAGE) . ' (%)',
        ];

        $data = NtStats::getManufacturers($date_from, $date_to, $id_manufacturer, $id_group, true);

        $this->exportFile($header, $data);
    }

    public function getCustomerSingleOrderAmount()
    {
        $amount_customer_min_one_order = Tools::getValue('customer_amount_customer_min_one_order');
        $data = NtStats::getCustomerSingleOrderAmount($amount_customer_min_one_order);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCustomerSingleOrderAmount()
    {
        $amount_customer_min_one_order = Tools::getValue('customer_amount_customer_min_one_order');

        $header = [
            $this->l('Email', self::PAGE),
            $this->l('Firstname', self::PAGE),
            $this->l('Lastname', self::PAGE),
            $this->l('ID', self::PAGE),
            $this->l('Max amount', self::PAGE) . ' (' . $this->l('Tax incl.', self::PAGE) . ')',
        ];

        $data = NtStats::getCustomerSingleOrderAmount($amount_customer_min_one_order, true);

        $this->exportFile($header, $data);
    }

    public function getCustomerOrdersAmount()
    {
        $amount_customer_min_orders = Tools::getValue('customer_amount_customer_min_orders');
        $data = NtStats::getCustomerOrdersAmount($amount_customer_min_orders);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCustomerOrdersAmount()
    {
        $amount_customer_min_orders = Tools::getValue('customer_amount_customer_min_orders');

        $header = [
            $this->l('Email', self::PAGE),
            $this->l('Firstname', self::PAGE),
            $this->l('Lastname', self::PAGE),
            $this->l('ID', self::PAGE),
            $this->l('Nb orders', self::PAGE),
            $this->l('Total amount', self::PAGE) . ' (' . $this->l('Tax incl.', self::PAGE) . ')',
            $this->l('Average amount per order', self::PAGE) . ' (' . $this->l('Tax incl.', self::PAGE) . ')',
        ];

        $data = NtStats::getCustomerOrdersAmount($amount_customer_min_orders, true);

        $this->exportFile($header, $data);
    }

    public function getCartrules()
    {
        $date_from = Tools::getValue('cartrules_date_from');
        $date_to = Tools::getValue('cartrules_date_to');
        $data = NtStats::getCartrules($date_from, $date_to);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCartrules()
    {
        $date_from = Tools::getValue('cartrules_date_from');
        $date_to = Tools::getValue('cartrules_date_to');

        $header = [
            $this->l('Month', self::PAGE),
            $this->l('Name', self::PAGE),
            $this->l('Qty used', self::PAGE),
            $this->l('Amount tax excl.', self::PAGE),
            $this->l('Amount tax incl.', self::PAGE),
            $this->l('Code', self::PAGE),
            $this->l('Free carrier', self::PAGE),
            $this->l('Total orders tax excl.', self::PAGE),
            $this->l('Total orders tax incl.', self::PAGE),
        ];

        $data = NtStats::getCartrules($date_from, $date_to, true);

        $this->exportFile($header, $data);
    }

    public function getOrders()
    {
        $date_from = Tools::getValue('orders_date_from');
        $date_to = Tools::getValue('orders_date_to');
        $id_cart_rule = Tools::getValue('orders_id_cart_rule');
        $id_product = Tools::getValue('orders_id_product');
        $id_group = Tools::getValue('orders_id_group');
        $id_category = Tools::getValue('orders_id_category');
        $payment_method = Tools::getValue('orders_payment_method');
        $data = $this->nts->getOrders($date_from, $date_to, $id_product, $id_group, $id_category, $payment_method, $id_cart_rule);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportOrders()
    {
        $date_from = Tools::getValue('orders_date_from');
        $date_to = Tools::getValue('orders_date_to');
        $id_cart_rule = Tools::getValue('orders_id_cart_rule');
        $id_product = Tools::getValue('orders_id_product');
        $id_group = Tools::getValue('orders_id_group');
        $id_category = Tools::getValue('orders_id_category');
        $payment_method = Tools::getValue('orders_payment_method');

        $use_invoice = NtStats::useInvoice();

        $header = [
            $this->l('ID', self::PAGE),
            $this->l('Reference', self::PAGE),
            $this->l('Total tax excl.', self::PAGE),
            $this->l('Total tax incl.', self::PAGE),
            $this->l('Total VAT', self::PAGE),
            $this->l('Products tax excl.', self::PAGE),
            $this->l('Products tax incl.', self::PAGE),
            $this->l('Products VAT', self::PAGE),
            $this->l('Discount tax excl.', self::PAGE),
            $this->l('Discount tax incl.', self::PAGE),
            $this->l('Discount VAT', self::PAGE),
            $this->l('Shipping tax excl.', self::PAGE),
            $this->l('Shipping tax incl.', self::PAGE),
            $this->l('Shipping VAT', self::PAGE),
            $this->l('Wrapping tax excl.', self::PAGE),
            $this->l('Wrapping tax incl.', self::PAGE),
            $this->l('Wrapping VAT', self::PAGE),
            $this->l('Ecotax tax excl.', self::PAGE),
            $this->l('Ecotax tax incl.', self::PAGE),
            $this->l('Ecotax VAT', self::PAGE),
            $this->l('Cost', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Gross profit before discounts', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Net profit tax excl.', self::PAGE),
            $this->l('Gross margin before discounts', self::PAGE) . '%',
            $this->l('Net margin tax excl.', self::PAGE) . '%',
            $this->l('Cart rules names', self::PAGE),
            $this->l('Free carrier', self::PAGE),
            $this->l('Invoice number', self::PAGE),
            ($use_invoice) ? $this->l('Invoice date', self::PAGE) : $this->l('Validity date', self::PAGE),
            $this->l('Payment date', self::PAGE),
            $this->l('Payment method', self::PAGE),
            $this->l('Customer', self::PAGE),
            $this->l('Postcode', self::PAGE),
            $this->l('City', self::PAGE),
            $this->l('Country', self::PAGE),
            $this->l('State', self::PAGE),
        ];

        $data = $this->nts->getOrders($date_from, $date_to, $id_product, $id_group, $id_category, $payment_method, $id_cart_rule, true);

        $this->exportFile($header, $data);
    }

    public function getCategories()
    {
        $date_from = Tools::getValue('categories_date_from');
        $date_to = Tools::getValue('categories_date_to');
        $id_category = Tools::getValue('categories_id_category');
        $id_group = Tools::getValue('categories_id_group');
        $data = $this->nts->getCategories($date_from, $date_to, $id_category, $id_group);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCategories()
    {
        $date_from = Tools::getValue('categories_date_from');
        $date_to = Tools::getValue('categories_date_to');
        $id_category = Tools::getValue('categories_id_category');
        $id_group = Tools::getValue('categories_id_group');

        $header = [
            $this->l('Name', self::PAGE),
            $this->l('Nb products', self::PAGE),
            $this->l('Nb products (%)', self::PAGE),
            $this->l('Nb customers', self::PAGE),
            $this->l('Nb customers (%)', self::PAGE),
            $this->l('Total tax excl.', self::PAGE),
            $this->l('Refund', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Purchase cost', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Discount', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Margin', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
            $this->l('Average amount tax excl.', self::PAGE),
        ];

        $data = $this->nts->getCategories($date_from, $date_to, $id_category, $id_group, true);

        $this->exportFile($header, $data);
    }

    public function getCustomers()
    {
        $date_from = Tools::getValue('customers_date_from');
        $date_to = Tools::getValue('customers_date_to');
        $id_group = Tools::getValue('customers_id_group');
        $data = $this->nts->getCustomers($date_from, $date_to, $id_group);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCustomers()
    {
        $date_from = Tools::getValue('customers_date_from');
        $date_to = Tools::getValue('customers_date_to');
        $id_group = Tools::getValue('customers_id_group');

        $header = [
            $this->l('Customer', self::PAGE),
            $this->l('Customer ID', self::PAGE),
            $this->l('Nb valid orders', self::PAGE),
            $this->l('Nb invalid orders', self::PAGE),
            $this->l('Nb products', self::PAGE),
            $this->l('Orders tax excl.', self::PAGE),
            $this->l('Average order tax excl.', self::PAGE),
            $this->l('Average product qty', self::PAGE),
            $this->l('Average nb days between orders', self::PAGE),
            $this->l('Profit tax excl', self::PAGE),
        ];

        $data = $this->nts->getCustomers($date_from, $date_to, $id_group, true);

        $this->exportFile($header, $data);
    }

    public function getCustomersOrdersDetails()
    {
        $date_from = Tools::getValue('customers_orders_details_date_from');
        $date_to = Tools::getValue('customers_orders_details_date_to');
        $id_group = Tools::getValue('customers_orders_details_id_group');
        $sort_by = Tools::getValue('customers_orders_details_sort_by');
        $sort_direction = Tools::getValue('customers_orders_details_sort_direction');
        $min_valid_order = Tools::getValue('customers_orders_details_min_valid_order');
        $max_valid_order = Tools::getValue('customers_orders_details_max_valid_order');
        $min_total_tax_excl = Tools::getValue('customers_orders_details_min_total_tax_excl');
        $max_total_tax_excl = Tools::getValue('customers_orders_details_max_total_tax_excl');
        $min_nb_total_products = Tools::getValue('customers_orders_details_min_nb_total_products');
        $max_nb_total_products = Tools::getValue('customers_orders_details_max_nb_total_products');
        $min_nb_products = Tools::getValue('customers_orders_details_min_nb_products');
        $max_nb_products = Tools::getValue('customers_orders_details_max_nb_products');

        $data = $this->nts->getCustomersOrdersDetails(
            $date_from,
            $date_to,
            $id_group,
            $sort_by,
            $sort_direction,
            $min_valid_order,
            $max_valid_order,
            $min_total_tax_excl,
            $max_total_tax_excl,
            $min_nb_total_products,
            $max_nb_total_products,
            $min_nb_products,
            $max_nb_products
        );

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCustomersOrdersDetails()
    {
        $date_from = Tools::getValue('customers_orders_details_date_from');
        $date_to = Tools::getValue('customers_orders_details_date_to');
        $id_group = Tools::getValue('customers_orders_details_id_group');
        $sort_by = Tools::getValue('customers_orders_details_sort_by');
        $sort_direction = Tools::getValue('customers_orders_details_sort_direction');
        $min_valid_order = Tools::getValue('customers_orders_details_min_valid_order');
        $max_valid_order = Tools::getValue('customers_orders_details_max_valid_order');
        $min_total_tax_excl = Tools::getValue('customers_orders_details_min_total_tax_excl');
        $max_total_tax_excl = Tools::getValue('customers_orders_details_max_total_tax_excl');
        $min_nb_total_products = Tools::getValue('customers_orders_details_min_nb_total_products');
        $max_nb_total_products = Tools::getValue('customers_orders_details_max_nb_total_products');
        $min_nb_products = Tools::getValue('customers_orders_details_min_nb_products');
        $max_nb_products = Tools::getValue('customers_orders_details_max_nb_products');

        $use_invoice = NtStats::useInvoice();

        $header = [
            $this->l('Customer', self::PAGE),
            $this->l('Nb valid orders', self::PAGE),
            $this->l('Nb invalid orders', self::PAGE),
            $this->l('Total tax excl.', self::PAGE),
            $this->l('Total tax incl.', self::PAGE),
            $this->l('Nb total products', self::PAGE),
            $this->l('Reference', self::PAGE),
            $this->l('Order tax excl.', self::PAGE),
            $this->l('Order tax incl.', self::PAGE),
            ($use_invoice) ? $this->l('Invoice date', self::PAGE) : $this->l('Validity date', self::PAGE),
            $this->l('Payment date', self::PAGE),
            $this->l('Payment method', self::PAGE),
            $this->l('State', self::PAGE),
            $this->l('Nb products', self::PAGE),
            $this->l('Product reference', self::PAGE),
            $this->l('Product name', self::PAGE),
            $this->l('Product qty', self::PAGE),
            $this->l('Product price', self::PAGE) . ' (' . $this->l('Tax excl.', self::PAGE) . ')',
        ];

        $data = $this->nts->getCustomersOrdersDetails(
            $date_from,
            $date_to,
            $id_group,
            $sort_by,
            $sort_direction,
            $min_valid_order,
            $max_valid_order,
            $min_total_tax_excl,
            $max_total_tax_excl,
            $min_nb_total_products,
            $max_nb_total_products,
            $min_nb_products,
            $max_nb_products,
            true
        );

        $this->exportFile($header, $data);
    }

    public function getCustomersProducts()
    {
        $date_from = Tools::getValue('customers_products_date_from');
        $date_to = Tools::getValue('customers_products_date_to');
        $id_group = Tools::getValue('customers_products_id_group');
        $id_manufacturer = Tools::getValue('customers_products_id_manufacturer');
        $id_feature = Tools::getValue('customers_products_id_feature');
        $id_feature_value = Tools::getValue('customers_products_id_feature_value');
        $id_category = Tools::getValue('customers_products_id_category');
        $id_product = Tools::getValue('customers_products_id_product');
        $id_combination = Tools::getValue('customers_products_id_combination');

        $data = $this->nts->getCustomersProducts(
            $date_from,
            $date_to,
            $id_group,
            $id_manufacturer,
            $id_feature,
            $id_feature_value,
            $id_category,
            $id_product,
            $id_combination
        );

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCustomersProducts()
    {
        $date_from = Tools::getValue('customers_products_date_from');
        $date_to = Tools::getValue('customers_products_date_to');
        $id_group = Tools::getValue('customers_products_id_group');
        $id_manufacturer = Tools::getValue('customers_products_id_manufacturer');
        $id_feature = Tools::getValue('customers_products_id_feature');
        $id_feature_value = Tools::getValue('customers_products_id_feature_value');
        $id_category = Tools::getValue('customers_products_id_category');
        $id_product = Tools::getValue('customers_products_id_product');
        $id_combination = Tools::getValue('customers_products_id_combination');

        $header = [
            $this->l('Customer', self::PAGE),
            $this->l('Customer ID', self::PAGE),
            $this->l('Products qty', self::PAGE),
            $this->l('Products tax excl.', self::PAGE),
            $this->l('Last order date of those products', self::PAGE),
            $this->l('Social title', self::PAGE),
            $this->l('Age', self::PAGE),
            $this->l('City of the most used delivery address', self::PAGE),
            $this->l('Country of the most used delivery address', self::PAGE),
            $this->l('City of the most used invoice address', self::PAGE),
            $this->l('Country of the most used invoice address', self::PAGE),
        ];

        $data = $this->nts->getCustomersProducts(
            $date_from,
            $date_to,
            $id_group,
            $id_manufacturer,
            $id_feature,
            $id_feature_value,
            $id_category,
            $id_product,
            $id_combination,
            true
        );

        $this->exportFile($header, $data);
    }

    public function getCustomersProductsDetails()
    {
        $date_from = Tools::getValue('customers_products_details_date_from');
        $date_to = Tools::getValue('customers_products_details_date_to');
        $id_group = Tools::getValue('customers_products_details_id_group');
        $id_manufacturer = Tools::getValue('customers_products_details_id_manufacturer');
        $id_feature = Tools::getValue('customers_products_details_id_feature');
        $id_feature_value = Tools::getValue('customers_products_details_id_feature_value');
        $id_category = Tools::getValue('customers_products_details_id_category');
        $id_product = Tools::getValue('customers_products_details_id_product');
        $id_combination = Tools::getValue('customers_products_details_id_combination');

        $data = $this->nts->getCustomersProductsDetails(
            $date_from,
            $date_to,
            $id_group,
            $id_manufacturer,
            $id_feature,
            $id_feature_value,
            $id_category,
            $id_product,
            $id_combination
        );

        exit(json_encode(['data_list' => $data]));
    }

    public function exportCustomersProductsDetails()
    {
        $date_from = Tools::getValue('customers_products_details_date_from');
        $date_to = Tools::getValue('customers_products_details_date_to');
        $id_group = Tools::getValue('customers_products_details_id_group');
        $id_manufacturer = Tools::getValue('customers_products_details_id_manufacturer');
        $id_feature = Tools::getValue('customers_products_details_id_feature');
        $id_feature_value = Tools::getValue('customers_products_details_id_feature_value');
        $id_category = Tools::getValue('customers_products_details_id_category');
        $id_product = Tools::getValue('customers_products_details_id_product');
        $id_combination = Tools::getValue('customers_products_details_id_combination');

        $use_invoice = NtStats::useInvoice();

        $header = [
            $this->l('Customer', self::PAGE),
            $this->l('Customer ID', self::PAGE),
            $this->l('Social title', self::PAGE),
            $this->l('Age', self::PAGE),
            $this->l('Order', self::PAGE),
            $this->l('Total tax excl.', self::PAGE),
            $this->l('VAT', self::PAGE),
            $this->l('Total tax incl.', self::PAGE),
            $this->l('City of the delivery address', self::PAGE),
            $this->l('Country of the delivery address', self::PAGE),
            $this->l('City of the invoice address', self::PAGE),
            $this->l('Country of the invoice address', self::PAGE),
            ($use_invoice) ? $this->l('Invoice date', self::PAGE) : $this->l('Validity date', self::PAGE),
            $this->l('Payment date', self::PAGE),
            $this->l('Payment method', self::PAGE),
            $this->l('State', self::PAGE),
            $this->l('Product reference', self::PAGE),
            $this->l('Product name', self::PAGE),
            $this->l('Product qty', self::PAGE),
            $this->l('Product price tax excl.', self::PAGE),
            $this->l('Product VAT', self::PAGE),
            $this->l('Product price tax incl.', self::PAGE),
        ];

        $data = $this->nts->getCustomersProductsDetails(
            $date_from,
            $date_to,
            $id_group,
            $id_manufacturer,
            $id_feature,
            $id_feature_value,
            $id_category,
            $id_product,
            $id_combination,
            true
        );

        $this->exportFile($header, $data);
    }

    public function getDurationStatuses()
    {
        $date_from = Tools::getValue('statuses_date_from');
        $date_to = Tools::getValue('statuses_date_to');
        $data = $this->nts->getDurationStatuses($date_from, $date_to);

        exit(json_encode(['data_list' => $data]));
    }

    public function exportDurationStatuses()
    {
        $date_from = Tools::getValue('statuses_date_from');
        $date_to = Tools::getValue('statuses_date_to');

        $header = [
            $this->l('Name', self::PAGE),
            $this->l('Nb orders', self::PAGE),
            $this->l('Average duration', self::PAGE),
        ];

        $data = $this->nts->getDurationStatuses($date_from, $date_to, true);

        $this->exportFile($header, $data);
    }

    public function saveConfig()
    {
        $id_nts_config = (int) Tools::getValue('config_id_nts_config');
        $nb_combinations_min_without_stock = (int) Tools::getValue('config_nb_combinations_min_without_stock');
        $amount_customer_min_one_order = (int) Tools::getValue('config_amount_customer_min_one_order');
        $amount_customer_min_orders = (int) Tools::getValue('config_amount_customer_min_orders');
        $group_product_reference = (int) Tools::getValue('config_group_product_reference');
        $autoload = (int) Tools::getValue('config_autoload');
        $receive_email_version = (int) Tools::getValue('config_receive_email_version');
        $email_alert_threshold = (int) Tools::getValue('config_email_alert_threshold');
        $email_alert_type = (int) Tools::getValue('config_email_alert_type');
        $email_alert_active = (int) Tools::getValue('config_email_alert_active');
        $email_alert_send_empty = (int) Tools::getValue('config_email_alert_send_empty');
        $default_period = (int) Tools::getValue('config_default_period');
        $dashboard_sales = (int) Tools::getValue('config_dashboard_sales');
        $dashboard_nb_orders = (int) Tools::getValue('config_dashboard_nb_orders');
        $increase_server_timeout = (int) Tools::getValue('config_increase_server_timeout');
        $server_timeout_value = (int) Tools::getValue('config_server_timeout_value');
        $increase_server_memory = (int) Tools::getValue('config_increase_server_memory');
        $server_memory_value = (int) Tools::getValue('config_server_memory_value');
        $order_type_date = (int) Tools::getValue('config_order_type_date');
        $order_date_state = (int) Tools::getValue('config_order_date_state');
        $order_type_location = (int) Tools::getValue('config_order_type_location');
        $return_valid_states = Tools::getValue('config_return_valid_states');
        $mail_version = Tools::getValue('config_mail_version');
        $mail_stock_alert = Tools::getValue('config_mail_stock_alert');
        $config_payment_method = Tools::getValue('config_payment_method');
        $config_profil_countries = Tools::getValue('config_profil_countries');

        if ($id_nts_config) {
            $o_nts_config = new NtsConfig($id_nts_config);
        } else {
            $o_nts_config = new NtsConfig();
        }

        $old_receive_email_version = $o_nts_config->receive_email_version;
        $old_mail_version = $o_nts_config->mail_version;

        switch (Shop::getContext()) {
            case Shop::CONTEXT_SHOP:
                $o_nts_config->id_shop = Context::getContext()->shop->id;
                $o_nts_config->id_shop_group = Context::getContext()->shop->id_shop_group;
                break;
            case Shop::CONTEXT_GROUP:
                $o_nts_config->id_shop = 0;
                $o_nts_config->id_shop_group = Shop::getContextShopGroupID(); // Context::getContext()->shop->id_shop_group may not the correct group in context_group
                break;
            case Shop::CONTEXT_ALL:
                $o_nts_config->id_shop = 0;
                $o_nts_config->id_shop_group = 0;
                break;
        }

        $o_nts_config->nb_combinations_min_without_stock = $nb_combinations_min_without_stock;
        $o_nts_config->amount_customer_min_one_order = $amount_customer_min_one_order;
        $o_nts_config->amount_customer_min_orders = $amount_customer_min_orders;
        $o_nts_config->group_product_reference = $group_product_reference;
        $o_nts_config->autoload = $autoload;
        $o_nts_config->receive_email_version = $receive_email_version;
        $o_nts_config->mail_version = $mail_version;
        $o_nts_config->mail_stock_alert = $mail_stock_alert;
        $o_nts_config->email_alert_threshold = $email_alert_threshold;
        $o_nts_config->email_alert_type = $email_alert_type;
        $o_nts_config->email_alert_active = $email_alert_active;
        $o_nts_config->email_alert_send_empty = $email_alert_send_empty;
        $o_nts_config->default_period = $default_period;
        $o_nts_config->dashboard_sales = $dashboard_sales;
        $o_nts_config->dashboard_nb_orders = $dashboard_nb_orders;
        $o_nts_config->increase_server_timeout = $increase_server_timeout;
        $o_nts_config->server_timeout_value = $server_timeout_value;
        $o_nts_config->increase_server_memory = $increase_server_memory;
        $o_nts_config->server_memory_value = $server_memory_value;
        $o_nts_config->order_type_date = $order_type_date;
        $o_nts_config->order_date_state = $order_date_state;
        $o_nts_config->order_type_location = $order_type_location;
        $o_nts_config->return_valid_states = json_encode($return_valid_states);

        if (!$o_nts_config->save()) {
            $result = 0;
        } else {
            $result = 1;

            // Save the payment method
            foreach ($config_payment_method as $payment_method => $display_name) {
                $infos = NtsConfigPaymentMethod::getByPaymentMethod($payment_method, $o_nts_config->id);

                if (isset($infos['id_nts_config_payment_method']) && $infos['id_nts_config_payment_method']) {
                    $o_config_pm = new NtsConfigPaymentMethod($infos['id_nts_config_payment_method']);
                } else {
                    $o_config_pm = new NtsConfigPaymentMethod();
                    $o_config_pm->id_nts_config = $o_nts_config->id;
                    $o_config_pm->payment_method = $payment_method;
                }

                $o_config_pm->display_name = $display_name;

                if ($display_name != '') {
                    if (!$o_config_pm->save()) {
                        $result = 0;
                    }
                } elseif ($o_config_pm->id) {
                    if (!$o_config_pm->delete()) {
                        $result = 0;
                    }
                }
            }

            // Save the profils countries limit
            if (is_array($config_profil_countries) && count($config_profil_countries)) {
                foreach ($config_profil_countries as $id_profil => $list_id_countries) {
                    $infos = NtsConfigProfilCountries::getByIdProfil($id_profil, $o_nts_config->id);

                    if (isset($infos['id_nts_config_profil_countries']) && $infos['id_nts_config_profil_countries']) {
                        $o_config_pc = new NtsConfigProfilCountries($infos['id_nts_config_profil_countries']);
                    } else {
                        $o_config_pc = new NtsConfigProfilCountries();
                        $o_config_pc->id_nts_config = $o_nts_config->id;
                        $o_config_pc->id_profil = $id_profil;
                    }

                    if (!is_array($list_id_countries) || !count($list_id_countries)) {
                        if (!$o_config_pc->delete()) {
                            $result = 0;
                        }
                    } else {
                        $o_config_pc->id_countries = json_encode($list_id_countries);

                        if (!$o_config_pc->save()) {
                            $result = 0;
                        }
                    }
                }
            } else {
                if (!NtsConfigProfilCountries::deleteAll()) {
                    $result = 0;
                }
            }

            // If the config change, upd in NtVersion
            if ($old_receive_email_version != $o_nts_config->receive_email_version || $old_mail_version != $o_nts_config->mail_version) {
                $shop_domain = Tools::getCurrentUrlProtocolPrefix() . Tools::getHttpHost();
                $shop_url = $shop_domain . __PS_BASE_URI__;
                $context = Context::getContext();
                $code_lang = str_replace('-', '_', Tools::strtolower($context->language->locale));

                $url = CONFIGURE_NTVERSION . '&email=' . urlencode((string) $o_nts_config->mail_version) . '&site=' . urlencode((string) $shop_url) . '&code_lang=' . urlencode((string) $code_lang);

                if ($o_nts_config->receive_email_version) {
                    // Save email in NtVersion
                    $url .= '&activate=1';
                } else {
                    // Delete email in NtVersion
                    $url .= '&activate=0';
                }

                $ntversion_result = Tools::file_get_contents($url);

                if ($ntversion_result != 'OK') {
                    if ($o_nts_config->receive_email_version) {
                        // Email was not added
                        $o_nts_config->receive_email_version = false;
                    } else {
                        // Email was not deleted
                        $o_nts_config->receive_email_version = true;
                    }

                    $o_nts_config->save();
                    $result = 0;
                }
            }
        }

        exit(json_encode(['result' => $result]));
    }

    public function initToolBarTitle()
    {
        $this->toolbar_title = 'NT ' . $this->l('Stats', self::PAGE);
    }

    /**
     * assign default action in page_header_toolbar_btn smarty var, if they are not set.
     * uses override to specifically add, modify or remove items
     */
    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
    }

    /**
     * assign default action in toolbar_btn smarty var, if they are not set.
     * uses override to specifically add, modify or remove items
     */
    public function initToolbar()
    {
        // Do nothing, ao the parent is not call
    }
}
