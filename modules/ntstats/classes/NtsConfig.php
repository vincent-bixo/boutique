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

class NtsConfig extends ObjectModel
{
    const SET_TIME_LIMIT = 6000;
    const SET_MEMORY_LIMIT = 128;

    /** @var int id_shop */
    public $id_shop;

    /** @var int id_shop_group */
    public $id_shop_group;

    /** @var int nb_combinations_min_without_stock */
    public $nb_combinations_min_without_stock;

    /** @var int amount_customer_min_one_order */
    public $amount_customer_min_one_order;

    /** @var int amount_customer_min_orders */
    public $amount_customer_min_orders;

    /** @var bool group_product_reference */
    public $group_product_reference;

    /** @var bool autoload */
    public $autoload;

    /** @var bool receive_email_version */
    public $receive_email_version;

    /** @var string mail_version */
    public $mail_version;

    /** @var int automation_2nt_ip */
    public $automation_2nt_ip;

    /** @var string last_shop_url */
    public $last_shop_url;

    /** @var bool automation_2nt */
    public $automation_2nt;

    /** @var int automation_2nt_hours */
    public $automation_2nt_hours;

    /** @var int automation_2nt_minutes */
    public $automation_2nt_minutes;

    /** @var string mail_stock_alert */
    public $mail_stock_alert;

    /** @var int email_alert_threshold */
    public $email_alert_threshold;

    /** @var int email_alert_type */
    public $email_alert_type;

    /** @var int email_alert_active */
    public $email_alert_active;

    /** @var bool email_alert_send_empty */
    public $email_alert_send_empty;

    /** @var int default_period */
    public $default_period;

    /** @var bool dashboard_sales */
    public $dashboard_sales;

    /** @var bool dashboard_nb_orders */
    public $dashboard_nb_orders;

    /** @var bool increase_server_timeout */
    public $increase_server_timeout;

    /** @var int server_timeout_value */
    public $server_timeout_value;

    /** @var bool increase_server_memory */
    public $increase_server_memory;

    /** @var int server_memory_value */
    public $server_memory_value;

    /** @var int order_type_date */
    public $order_type_date;

    /** @var int order_date_state */
    public $order_date_state;

    /** @var int order_type_location */
    public $order_type_location;

    /** @var string return_valid_states */
    public $return_valid_states;

    /** @var string date_add */
    public $date_add;

    /** @var string date_upd */
    public $date_upd;

    const EMAIL_ALERT_TYPE_INCLUDED = 0;
    const EMAIL_ALERT_TYPE_CSV = 1;
    const EMAIL_ALERT_TYPE_EXCEL = 2;

    const EMAIL_ALERT_ACTIVE_ALL = -1;
    const EMAIL_ALERT_ACTIVE_NO = 0;
    const EMAIL_ALERT_ACTIVE_YES = 1;

    const ORDER_TYPE_DATE_INVOICE = 1;
    const ORDER_TYPE_DATE_STATE = 2;
    const ORDER_TYPE_DATE_ADD = 3;

    const ORDER_TYPE_LOCATION_INVOICE = 1;
    const ORDER_TYPE_LOCATION_DELIVERY = 2;

    const DEFAULT_PERIOD_LAST_MONTH = 1;
    const DEFAULT_PERIOD_LAST_THREE_MONTHS = 2;
    const DEFAULT_PERIOD_LAST_YEAR = 3;
    const DEFAULT_PERIOD_LAST_THREE_YEARS = 4;
    const DEFAULT_PERIOD_ALL_DATE = 5;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'nts_config',
        'primary' => 'id_nts_config',
        'multilang' => false,
        'multilang_shop' => false,
        'fields' => [
            'id_shop' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => '0',
            ],
            'id_shop_group' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => '0',
            ],
            'nb_combinations_min_without_stock' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                // No default value, if default value is 1, it is impossible to change the value to 0
            ],
            'amount_customer_min_one_order' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => '100',
            ],
            'amount_customer_min_orders' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => '50',
            ],
            'group_product_reference' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                // No default value, if default value is 1, it is impossible to change the value to 0
            ],
            'autoload' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                // No default value, if default value is 1, it is impossible to change the value to 0
            ],
            'receive_email_version' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'mail_version' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
            ],
            'automation_2nt_ip' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => '0',
            ],
            'last_shop_url' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isUrl',
            ],
            'automation_2nt' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'automation_2nt_hours' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'automation_2nt_minutes' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'mail_stock_alert' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
            ],
            'email_alert_threshold' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                // No default value, if default value is 3, it is impossible to change the value to 0
            ],
            'email_alert_type' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => self::EMAIL_ALERT_TYPE_INCLUDED,
            ],
            'email_alert_active' => [
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                // No default value, if default value is 1, it is impossible to change the value to 0
            ],
            'email_alert_send_empty' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'default_period' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => self::DEFAULT_PERIOD_LAST_MONTH,
            ],
            'dashboard_sales' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                // No default value, if default value is 1, it is impossible to change the value to 0
            ],
            'dashboard_nb_orders' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                // No default value, if default value is 1, it is impossible to change the value to 0
            ],
            'increase_server_timeout' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'server_timeout_value' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => self::SET_TIME_LIMIT,
            ],
            'increase_server_memory' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'default' => '0',
            ],
            'server_memory_value' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => self::SET_MEMORY_LIMIT,
            ],
            'order_type_date' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => self::ORDER_TYPE_DATE_INVOICE,
            ],
            'order_date_state' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => 0,
            ],
            'order_type_location' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'default' => self::ORDER_TYPE_LOCATION_INVOICE,
            ],
            'return_valid_states' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isJson',
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];

    public function add($auto_date = true, $null_values = false)
    {
        if (!$this->mail_version) {
            $this->mail_version = Configuration::get('PS_SHOP_EMAIL');
        } else {
            $this->mail_version = preg_replace('/\s/', '', $this->mail_version);
        }

        if (!$this->mail_stock_alert) {
            $this->mail_stock_alert = Configuration::get('PS_SHOP_EMAIL');
        } else {
            $this->mail_stock_alert = preg_replace('/\s/', '', $this->mail_stock_alert);
        }

        if ($this->id_shop === null || !is_int($this->id_shop)) {
            $this->id_shop = Context::getContext()->shop->id;
        }

        if ($this->id_shop_group === null || !is_int($this->id_shop_group)) {
            $this->id_shop_group = Context::getContext()->shop->id_shop_group;
        }

        if (!$this->last_shop_url) {
            $shop_domain = Tools::getCurrentUrlProtocolPrefix() . Tools::getHttpHost();
            $this->last_shop_url = $shop_domain . __PS_BASE_URI__;
        }

        if (!$this->automation_2nt_hours) {
            $this->automation_2nt_hours = mt_rand(2, 5); // Rand hour between 2 and 5
        }

        if (!$this->automation_2nt_minutes) {
            $this->automation_2nt_minutes = mt_rand(1, 59); // Rand minutes between 1 and 59
        }

        if (Validate::isSerializedArray($this->return_valid_states)) {
            $this->return_valid_states = json_encode(unserialize($this->return_valid_states));
        }

        return parent::add($auto_date, $null_values);
    }

    public function update($null_values = false)
    {
        if (!$this->mail_version) {
            $this->mail_version = Configuration::get('PS_SHOP_EMAIL');
        } else {
            $this->mail_version = preg_replace('/\s/', '', $this->mail_version);
        }

        if (!$this->mail_stock_alert) {
            $this->mail_stock_alert = Configuration::get('PS_SHOP_EMAIL');
        } else {
            $this->mail_stock_alert = preg_replace('/\s/', '', $this->mail_stock_alert);
        }

        if (!$this->last_shop_url) {
            $shop_domain = Tools::getCurrentUrlProtocolPrefix() . Tools::getHttpHost();
            $this->last_shop_url = $shop_domain . __PS_BASE_URI__;
        }

        if (Validate::isSerializedArray($this->return_valid_states)) {
            $this->return_valid_states = json_encode(unserialize($this->return_valid_states));
        }

        return parent::update($null_values);
    }

    /**
     * Get the config
     *
     * @param int $id_shop ID shop The shop you want the config for
     * @param int $id_shop_group ID shop group The group shop you want the config for
     *
     * @return array The config
     */
    public static function getConfig($id_shop, $id_shop_group, $id_lang = null)
    {
        if ($id_shop === null) {
            $id_shop = (int) Context::getContext()->shop->id;
        } else {
            $id_shop = (int) $id_shop;
        }

        if ($id_shop_group === null) {
            $id_shop_group = (int) Context::getContext()->shop->id_shop_group;
        } else {
            $id_shop_group = (int) $id_shop_group;
        }

        if ($id_lang === null) {
            $id_lang = (int) Context::getContext()->language->id;
        } else {
            $id_lang = (int) $id_lang;
        }

        /*$shop_context   = Shop::getContext();
        $req_shop       = '';

        if ($shop_context == Shop::CONTEXT_SHOP) {
            $req_shop = ' AND `id_shop` = '.(int)$id_shop;
        } elseif ($shop_context == Shop::CONTEXT_GROUP) {
            $req_shop = ' AND `id_shop_group` = '.Shop::getContextShopGroupID();
        }*/

        $config = Db::getInstance()->getRow('
            SELECT `id_nts_config`, `nb_combinations_min_without_stock`, `amount_customer_min_one_order`,
                `amount_customer_min_orders`, `group_product_reference`, `receive_email_version`, `mail_version`,
                `automation_2nt_ip`, `last_shop_url`, `automation_2nt`, `automation_2nt_hours`, `autoload`,
                `automation_2nt_minutes`, `mail_stock_alert`, `email_alert_threshold`, `email_alert_type`,
                `email_alert_active`, `email_alert_send_empty`, `dashboard_sales`, `dashboard_nb_orders`,
                `increase_server_timeout`, `server_timeout_value`, `increase_server_memory`, `server_memory_value`,
                `order_type_date`, `order_date_state`, `return_valid_states`, `order_type_location`, `default_period`
            FROM `' . _DB_PREFIX_ . 'nts_config` p
            WHERE `id_shop` = ' . $id_shop . '
            AND `id_shop_group` = ' . $id_shop_group . '
        ');

        if (!is_array($config)) {
            $fields = self::$definition['fields'];
            $order_return_states = OrderReturnState::getOrderReturnStates($id_lang);
            $list_id_order_return_state = [];

            foreach ($order_return_states as $ors) {
                if (isset($ors['id_order_return_state'])) {
                    $list_id_order_return_state[] = $ors['id_order_return_state'];
                }
            }

            $o_config = new NtsConfig();
            $o_config->nb_combinations_min_without_stock = 1;
            $o_config->amount_customer_min_one_order = $fields['amount_customer_min_one_order']['default'];
            $o_config->amount_customer_min_orders = $fields['amount_customer_min_orders']['default'];
            $o_config->group_product_reference = 1;
            $o_config->autoload = 1;
            $o_config->receive_email_version = 0;
            $o_config->mail_version = Configuration::get('PS_SHOP_EMAIL');
            $o_config->id_shop = $id_shop;
            $o_config->id_shop_group = $id_shop_group;
            $o_config->automation_2nt_ip = $fields['automation_2nt_ip']['default'];
            $o_config->last_shop_url = Tools::getCurrentUrlProtocolPrefix() . Tools::getHttpHost() . __PS_BASE_URI__;
            $o_config->automation_2nt = $fields['automation_2nt']['default'];
            $o_config->automation_2nt_hours = mt_rand(2, 5); // Rand hour between 2 and 5
            $o_config->automation_2nt_minutes = mt_rand(1, 59); // Rand minutes between 1 and 59
            $o_config->mail_stock_alert = Configuration::get('PS_SHOP_EMAIL');
            $o_config->email_alert_threshold = 3;
            $o_config->email_alert_type = $fields['email_alert_type']['default'];
            $o_config->email_alert_active = self::EMAIL_ALERT_ACTIVE_YES;
            $o_config->email_alert_send_empty = $fields['email_alert_send_empty']['default'];
            $o_config->default_period = $fields['default_period']['default'];
            $o_config->dashboard_sales = 1;
            $o_config->dashboard_nb_orders = 1;
            $o_config->increase_server_timeout = 0;
            $o_config->server_timeout_value = self::SET_TIME_LIMIT;
            $o_config->increase_server_memory = 0;
            $o_config->server_memory_value = self::SET_MEMORY_LIMIT;
            $o_config->order_type_date = (NtStats::useInvoice()) ? self::ORDER_TYPE_DATE_INVOICE : self::ORDER_TYPE_DATE_ADD;
            $o_config->order_date_state = $fields['order_date_state']['default'];
            $o_config->order_type_location = self::ORDER_TYPE_LOCATION_INVOICE;
            $o_config->return_valid_states = json_encode($list_id_order_return_state);

            $o_config->add();

            $config['id_nts_config'] = $o_config->id;

            $config = [
                'id_nts_config' => $o_config->id,
                'nb_combinations_min_without_stock' => $o_config->nb_combinations_min_without_stock,
                'amount_customer_min_one_order' => $o_config->amount_customer_min_one_order,
                'amount_customer_min_orders' => $o_config->amount_customer_min_orders,
                'group_product_reference' => $o_config->group_product_reference,
                'autoload' => $o_config->autoload,
                'receive_email_version' => $o_config->receive_email_version,
                'mail_version' => $o_config->mail_version,
                'automation_2nt_ip' => $o_config->automation_2nt_ip,
                'last_shop_url' => $o_config->last_shop_url,
                'automation_2nt' => $o_config->automation_2nt,
                'automation_2nt_hours' => $o_config->automation_2nt_hours,
                'automation_2nt_minutes' => $o_config->automation_2nt_minutes,
                'mail_stock_alert' => $o_config->mail_stock_alert,
                'email_alert_threshold' => $o_config->email_alert_threshold,
                'email_alert_type' => $o_config->email_alert_type,
                'email_alert_active' => $o_config->email_alert_active,
                'email_alert_send_empty' => $o_config->email_alert_send_empty,
                'default_period' => $o_config->default_period,
                'dashboard_sales' => $o_config->dashboard_sales,
                'dashboard_nb_orders' => $o_config->dashboard_nb_orders,
                'increase_server_timeout' => $o_config->increase_server_timeout,
                'server_timeout_value' => $o_config->server_timeout_value,
                'increase_server_memory' => $o_config->increase_server_memory,
                'server_memory_value' => $o_config->server_memory_value,
                'order_type_date' => $o_config->order_type_date,
                'order_date_state' => $o_config->order_date_state,
                'order_type_location' => $o_config->order_type_location,
                'return_valid_states' => $list_id_order_return_state,
            ];
        } else {
            if (!$config['order_type_date'] || !$config['return_valid_states'] || $config['return_valid_states'] == '' || $config['order_type_location']) {
                $o_config = new NtsConfig($config['id_nts_config']);

                if (!$config['order_type_date']) {
                    $o_config->order_type_date = $config['order_type_date'] = (NtStats::useInvoice()) ? self::ORDER_TYPE_DATE_INVOICE : self::ORDER_TYPE_DATE_ADD;
                }

                if (!$config['order_type_location']) {
                    $o_config->order_type_location = $config['order_type_location'] = self::ORDER_TYPE_LOCATION_INVOICE;
                }

                if (!$config['return_valid_states'] || $config['return_valid_states'] == '') {
                    $order_return_states = OrderReturnState::getOrderReturnStates($id_lang);
                    $list_id_order_return_state = [];

                    foreach ($order_return_states as $ors) {
                        if (isset($ors['id_order_return_state'])) {
                            $list_id_order_return_state[] = $ors['id_order_return_state'];
                        }
                    }

                    $o_config->return_valid_states = $config['return_valid_states'] = json_encode($list_id_order_return_state);
                }

                $o_config->update();
            }

            $config['mail_version'] = ($config['mail_version']) ? $config['mail_version'] : Configuration::get('PS_SHOP_EMAIL');
            $config['mail_stock_alert'] = ($config['mail_stock_alert']) ? $config['mail_stock_alert'] : Configuration::get('PS_SHOP_EMAIL');
            $config['last_shop_url'] = ($config['last_shop_url']) ? $config['last_shop_url'] : Tools::getCurrentUrlProtocolPrefix() . Tools::getHttpHost() . __PS_BASE_URI__;
            $config['return_valid_states'] = json_decode($config['return_valid_states'], true);
        }

        $config['id_shop'] = $id_shop;
        $config['id_shop_group'] = $id_shop_group;
        $config['payment_method'] = NtsConfigPaymentMethod::getConfigByIdConfig($config['id_nts_config']);
        $config['profil_countries'] = NtsConfigProfilCountries::getConfigByIdConfig($config['id_nts_config'], $id_lang);

        return $config;
    }

    public static function getValidOrderStates($id_lang)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT os.`id_order_state`, osl.`name`
            FROM `' . _DB_PREFIX_ . 'order_state` os
            LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = ' . (int) $id_lang . ')
            WHERE `logable` = 1
            AND `deleted` = 0
            ORDER BY `name` ASC
        ');

        return $result;
    }
}
