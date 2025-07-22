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

require_once dirname(__FILE__) . '/lib/apparatus/NtsApparatus.php';
require_once dirname(__FILE__) . '/classes/NtsConfig.php';
require_once dirname(__FILE__) . '/classes/NtsConfigPaymentMethod.php';
require_once dirname(__FILE__) . '/classes/NtsConfigProfilCountries.php';
require_once dirname(__FILE__) . '/classes/NtsTablesConfig.php';

class NtStats extends Module
{
    const MODULE_NAME = 'ntstats';

    const TAB_2NT = 'NTModules';
    const NAME_TAB_2NT = 'NT Modules';
    const TAB_MODULE = 'AdminNtstats';
    const NAME_TAB = 'NtStats';

    const CONTACT_LINK = 'https://addons.prestashop.com/en/write-to-developper?id_product=48694';
    const CONTACT_LINK_FR = 'https://addons.prestashop.com/fr/ecrire-au-developpeur?id_product=48694';
    const RATE_LINK = 'http://addons.prestashop.com/en/ratings.php';
    const RATE_LINK_FR = 'http://addons.prestashop.com/fr/ratings.php';
    const GEOLOC_LINK = 'https://addons.prestashop.com/en/international-localization/18661-nt-geoloc-precisely-locate-your-customers-and-stores.html';
    const GEOLOC_LINK_FR = 'https://addons.prestashop.com/fr/international-localisation/18661-nt-geoloc-geolocalisez-precisement-vos-clients.html';
    const REDUC_LINK = 'https://addons.prestashop.com/en/promotions-gifts/11404-nt-reduction-easy-and-fast-massive-discount.html';
    const REDUC_LINK_FR = 'https://addons.prestashop.com/fr/promotions-cadeaux/11404-nt-reduction-promotions-en-masse-facile-et-rapide.html';
    const BCK_FLL_LINK = 'https://addons.prestashop.com/en/data-migration-backup/20130-nt-backup-and-restore.html';
    const BCK_FLL_LINK_FR = 'https://addons.prestashop.com/fr/migration-donnees-sauvegarde/20130-nt-sauvegarde-et-restaure.html';
    const BCK_LGHT_LINK = 'https://addons.prestashop.com/en/data-migration-backup/45979-nt-backup-and-restore-light.html';
    const BCK_LGHT_LINK_FR = 'https://addons.prestashop.com/fr/migration-donnees-sauvegarde/45979-nt-sauvegarde-et-restaure-light.html';
    const DEB_LINK_FR = 'https://addons.prestashop.com/fr/comptabilite-facturation/27107-nt-deb.html';
    const URL_VERSION = 'https://version.2n-tech.com/ntstats.txt';
    const INSTALL_SQL_FILE = 'sql/install.sql';
    const UNINSTALL_SQL_FILE = 'sql/uninstall.sql';
    const IPV4_NTCRON = '188.165.241.158,94.23.43.136';
    const IPV6_NTCRON = '2001:41d0:2:bc9e::,2001:41d0:2:2c88::';
    const PERM_DIR = '0755';
    const PERM_FILE = '0644';
    const URL_SERVICE_IP_EXTERNE = 'https://rkx.fr/ip.php';
    const NT_URL_OPERATION = 'https://tm.2n-tech.com/set_operation.php?';

    // Module operation
    const OP_INSTALL = 1;
    const OP_UPGRADE = 2;
    const OP_UNINSTALL = 3;

    public function __construct()
    {
        $this->name = 'ntstats';
        $this->tab = 'analytics_stats';
        $this->version = '4.16.2';
        $this->author = '2N Technologies';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => '8.2.99'];
        $this->module_key = 'c9c92742167e68937b73a6d8b29d0f70';
        $this->secure_key = Tools::encrypt($this->name);

        parent::__construct();

        $this->displayName = $this->l('2N Technologies Statistics');
        $this->description = $this->l('Displays statistics for your store');

        $this->tabs[] = [
            'parent_class' => self::TAB_2NT,
            'parent_name' => self::NAME_TAB_2NT,
            'tab_class' => self::TAB_MODULE,
            'tab_name' => self::NAME_TAB,
        ];

        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            $this->tabs[] = [
                'parent_class' => 'AdminParentOrders',
                'parent_name' => self::NAME_TAB_2NT,
                'tab_class' => self::TAB_MODULE . 'Tab',
                'tab_name' => self::NAME_TAB,
            ];
        } else {
            $this->tabs[] = [
                'parent_class' => 'AdminParentStats',
                'parent_name' => self::NAME_TAB_2NT,
                'tab_class' => self::TAB_MODULE . 'Tab',
                'tab_name' => self::NAME_TAB,
            ];
        }
    }

    /**
     * Execute a SQL file
     *
     * @param string $file_path The path of the SQL file
     *
     * @return bool Success or failure of the operation
     */
    public function executeFile($file_path)
    {
        // Check if the file exists
        if (!file_exists($file_path)) {
            return Tools::displayError('Error : no sql file !');
        } elseif (!$sql = Tools::file_get_contents($file_path)) {// Get file content
            return Tools::displayError('Error : there is a problem with your install sql file !');
        }

        $sql_replace = str_replace(['PREFIX_', 'ENGINE_TYPE'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sql);
        $sql = preg_split("/;\s*[\r\n]+/", trim($sql_replace));

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute(trim($query))) {
                return Tools::displayError('Error : this query doesn\'t work ! ' . $query);
            }
        }

        return true;
    }

    /**
     * @see Module::install()
     */
    public function install()
    {
        // Make sure the database was removed
        if ($this->executeFile(dirname(__FILE__) . '/' . self::UNINSTALL_SQL_FILE) !== true) {
            return false;
        }

        // Create new data base table
        if ($this->executeFile(dirname(__FILE__) . '/' . self::INSTALL_SQL_FILE) !== true) {
            return false;
        }

        $module_id = Configuration::get('NTSTATS_ID');

        // Check module ID exists
        if (!$module_id || $module_id == '') {
            // Unique value for all shop that should not be remove or modify
            if (!Configuration::updateGlobalValue('NTSTATS_ID', $this->createModuleId())) {
                $this->_errors[] = $this->l('The configuration cannot be created: Module ID cannot be created.');

                return false;
            }
        }

        $install_on_tab = true;
        /* Install on tab */
        foreach ($this->tabs as $tab) {
            if (!$this->installOnTab($tab['tab_class'], $tab['tab_name'], $tab['parent_class'], $tab['parent_name'])) {
                $install_on_tab = false;
            }
        }

        if (!$install_on_tab) {
            PrestaShopLogger::addLog($this->l('The module cannot be install on its tabs.'), 3);
            $this->_errors[] = $this->l('The module cannot be install on its tabs.');

            return false;
        }

        // Create file with all the varibles need for crons
        $this->deleteCronFiles(); // Delete them first if they already exists
        $this->writeCronFiles(true);

        if (!parent::install()) {
            Tools::displayError('Error: there is a problem with your module installation');

            return false;
        }

        if (!$this->registerHook('dashboardZoneOne')) {
            Tools::displayError('Error: could not register to dashboard zone 1');

            return false;
        }

        if (!$this->registerHook('actionAdminControllerSetMedia')) {
            Tools::displayError('Error: could not register to add media to dashboard');

            return false;
        }

        $this->setOperation(self::OP_INSTALL);

        return true;
    }

    /**
     * @see Module::uninstall()
     */
    public function uninstall()
    {
        /* Delete Back-office tab */
        foreach ($this->tabs as $tab) {
            $this->uninstallTab($tab['tab_class']);
        }

        /* Delete the database table */
        $this->executeFile(dirname(__FILE__) . '/' . self::UNINSTALL_SQL_FILE);

        $this->deleteCronFiles();

        if (parent::uninstall()) {
            $this->setOperation(self::OP_UNINSTALL);

            return true;
        }

        return false;
    }

    public function uninstallTab($tab_class)
    {
        $img_tab_path = _PS_ROOT_DIR_ . '/img/t/';
        $module_path = _PS_MODULE_DIR_ . '/' . $this->name . '/';
        $id_tab = Tab::getIdFromClassName($tab_class);

        if ($id_tab) {
            $tab = new Tab((int) $id_tab);
            $id_parent = $tab->id_parent;
            $parent_tab = new Tab((int) $id_parent);

            if (file_exists($img_tab_path . $tab->class_name . ' . gif')) {
                unlink($img_tab_path . $tab->class_name . ' . gif');
            }

            $tab->delete();

            if (Tab::getNbTabs($id_parent) <= 0 && $parent_tab->class_name == self::TAB_2NT) {
                $tab_parent = new Tab((int) $id_parent);
                $img = $tab_parent->class_name . ' . gif';

                if (file_exists($img_tab_path . $img)) {
                    unlink($img_tab_path . $img);
                }

                if (version_compare(_PS_VERSION_, '1.6', '<') && file_exists($module_path . $img)) {
                    unlink($module_path . $img);
                }

                $tab_parent->delete();
            }
        }
    }

    /**
     * Install the module in a tab
     *
     * @param string $tab_class Tab class
     * @param string $tab_name Tab name
     * @param string $tab_parent_class Tab parent's class
     * @param string $tab_parent_name Tab parent's name
     *
     * @return bool
     */
    public function installOnTab($tab_class, $tab_name, $tab_parent_class, $tab_parent_name = '')
    {
        $img_tab_path = _PS_ROOT_DIR_ . '/img/t/';
        $module_path = _PS_MODULE_DIR_ . $this->name . '/';

        if (version_compare(_PS_VERSION_, '1.6', '>')) {
            $logo_path = $module_path . 'views/img/tab_logo_grey.png';
        } else {
            $logo_path = $module_path . 'views/img/tab_logo_color.png';
        }

        $id_tab_parent = Tab::getIdFromClassName($tab_parent_class);

        /* If the parent tab does not exist yet, create it */
        if (!$id_tab_parent) {
            $tab_parent = new Tab();
            $tab_parent->class_name = $tab_parent_class;
            $tab_parent->module = $this->name;
            $tab_parent->id_parent = 0;

            foreach (Language::getLanguages(false) as $lang) {
                $tab_parent->name[(int) $lang['id_lang']] = $tab_parent_name;
            }

            if (!$tab_parent->save()) {
                $this->_errors[] = sprintf($this->l('Unable to create the "%s" tab'), $tab_parent_class);

                return false;
            }

            $id_tab_parent = $tab_parent->id;
        }

        if (!file_exists($img_tab_path . $tab_parent_class . ' . gif')) {
            if (version_compare(_PS_VERSION_, '1.5.5.0', '>=') === true) {
                $copy = Tools::copy($logo_path, $img_tab_path . $tab_parent_class . ' . gif');
            } else {
                // Tools::copy does not exists before Prestashop 1.5.5.0
                $copy = copy($logo_path, $img_tab_path . $tab_parent_class . ' . gif');
            }

            if (!$copy) {
                $this->_errors[] = sprintf($this->l('Unable to copy logo.gif in %s'), $img_tab_path);
            }
        }

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            if (!file_exists($module_path . $tab_parent_class . ' . gif')) {
                if (version_compare(_PS_VERSION_, '1.5.5.0', '>=') === true) {
                    $copy = Tools::copy($logo_path, $module_path . $tab_parent_class . ' . gif');
                } else {
                    // Tools::copy does not exists before Prestashop 1.5.5.0
                    $copy = copy($logo_path, $module_path . $tab_parent_class . ' . gif');
                }

                if (!$copy) {
                    $this->_errors[] = sprintf($this->l('Unable to copy logo.gif in %s'), $module_path);
                }
            }
        }

        /* If the tab does not exist yet, create it */
        if (!Tab::getIdFromClassName($tab_class)) {
            $tab = new Tab();
            $tab->class_name = $tab_class;
            $tab->module = $this->name;
            $tab->id_parent = (int) $id_tab_parent;

            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = $tab_name;
            }

            if (version_compare(_PS_VERSION_, '1.7', '>=') === true && $tab_class == self::TAB_MODULE) {
                $tab->icon = 'timeline';
            }

            if (!$tab->save()) {
                $this->_errors[] = sprintf($this->l('Unable to create the "%s" tab'), $tab_class);

                return false;
            }
        }

        if (file_exists($logo_path)) {
            if (!file_exists($img_tab_path . $tab_class . ' . gif')) {
                if (version_compare(_PS_VERSION_, '1.5.5.0', '>=') === true) {
                    $copy = Tools::copy($logo_path, $img_tab_path . $tab_class . ' . gif');
                } else {
                    // Tools::copy does not exists before Prestashop 1.5.5.0
                    $copy = copy($logo_path, $img_tab_path . $tab_class . ' . gif');
                }

                if (!$copy) {
                    $this->_errors[] = sprintf($this->l('Unable to copy logo.gif in %s'), $img_tab_path);
                }
            }

            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                if (!file_exists($module_path . $tab_class . ' . gif')) {
                    if (version_compare(_PS_VERSION_, '1.5.5.0', '>=') === true) {
                        $copy = Tools::copy($logo_path, $module_path . $tab_class . ' . gif');
                    } else {
                        // Tools::copy does not exists before Prestashop 1.5.5.0
                        $copy = copy($logo_path, $module_path . $tab_class . ' . gif');
                    }

                    if (!$copy) {
                        $this->_errors[] = sprintf($this->l('Unable to copy logo.gif in %s'), $module_path);
                    }
                }
            }
        }

        return true;
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink(self::TAB_MODULE));
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (get_class($this->context->controller) == 'AdminDashboardController') {
            $a_config = self::getConfig();

            if ($a_config['dashboard_sales'] || $a_config['dashboard_nb_orders']) {
                if (method_exists($this->context->controller, 'addJquery')) {
                    $this->context->controller->addJquery();
                }

                $this->context->controller->addJS([
                    $this->_path . 'views/js/dashboard.js',
                    $this->_path . 'lib/chartjs-2.9.3/node_modules/chart.js/dist/Chart.js',
                ]);

                $this->context->controller->addCSS([
                    $this->_path . 'views/css/dashboard.css',
                ]);
            }
        }
    }

    public function hookDashboardZoneOne($params)
    {
        $a_config = self::getConfig();

        if (!$a_config['dashboard_sales'] && !$a_config['dashboard_nb_orders']) {
            return '';
        }

        $list_sales = [];
        $list_nb_orders = [];

        for ($year = date('Y') - 2; $year <= date('Y'); ++$year) {
            $data = NtStats::getDashboardData($year);

            $list_sales[$year] = $data['list_sales'];
            $list_nb_orders[$year] = $data['list_nb_orders'];
        }

        $this->context->smarty->assign(
            [
                'list_sales' => $list_sales,
                'list_nb_orders' => $list_nb_orders,
                'enable_dashboard_sales' => $a_config['dashboard_sales'],
                'enable_dashboard_nb_orders' => $a_config['dashboard_nb_orders'],
            ]
        );

        return $this->display(__FILE__, 'dashboard_zone_one.tpl');
    }

    public static function lg($message)
    {
        if (!is_string($message)) {
            $message = print_r($message, true);
        } else {
            $message = html_entity_decode($message, ENT_COMPAT, 'UTF-8');
        }

        $path = _PS_MODULE_DIR_ . self::MODULE_NAME . '/log.txt';

        if (!($file = fopen($path, 'a+'))) {
            return false;
        }

        if (fwrite($file, date('Y-m-d H:i:s') . ' ' . $message . "\n") === false) {
            return false;
        }

        if (!fclose($file)) {
            return false;
        }

        return true;
    }

    public function deleteCronFiles()
    {
        $physic_path_modules = NtsApparatus::getRealPath(_PS_ROOT_DIR_ . '/modules') . '/';
        $physic_path_cron = $physic_path_modules . $this->name . '/crons';
        $list_files = glob($physic_path_cron . '/email_alert_*_' . $this->secure_key . '.php');

        foreach ($list_files as $email_alert_cron_files) {
            unlink($email_alert_cron_files);
        }
    }

    public function writeCronFiles($install)
    {
        $physic_path_modules = NtsApparatus::getRealPath(_PS_ROOT_DIR_ . '/modules') . '/';
        $shop_domain = Tools::getCurrentUrlProtocolPrefix() . Tools::getHttpHost();
        $url_modules = $shop_domain . __PS_BASE_URI__ . 'modules/';
        $url_cron = $url_modules . $this->name . '/crons';
        $physic_path_cron = $physic_path_modules . $this->name . '/crons';
        $list_crons = [];
        $shop = Context::getContext()->shop;

        switch (Shop::getContext()) {
            case Shop::CONTEXT_SHOP:
                $file_key = $shop->id_shop_group . '_' . $shop->id . '_' . $this->secure_key;
                $params = 'secure_key=' . $this->secure_key . '&id_shop_group=' . $shop->id_shop_group . '&id_shop=' . $shop->id;
                break;
            case Shop::CONTEXT_GROUP:
                // $shop->id_shop_group may not the correct group in context_group
                $id_shop_group = Shop::getContextShopGroupID();
                $file_key = $id_shop_group . '_0_' . $this->secure_key;
                $params = 'secure_key=' . $this->secure_key . '&id_shop_group=' . $id_shop_group . '&id_shop=0';
                break;
            case Shop::CONTEXT_ALL:
                $file_key = '0_0_' . $this->secure_key;
                $params = 'secure_key=' . $this->secure_key . '&id_shop_group=0&id_shop=0';
                break;
        }

        $content_email_alert = '<?php ';
        $content_email_alert .= 'header("Location: ' . $url_cron . '/email_alert.php?' . $params . '"); ';
        $content_email_alert .= 'exit();';

        $list_crons[] = [
            'file_path' => $physic_path_cron . '/email_alert_' . $file_key . '.php',
            'content' => $content_email_alert,
        ];

        $content_email_alert_curl = '<?php ';
        $content_email_alert_curl .= '$curl_handle=curl_init(); ';
        $content_email_alert_curl .= 'curl_setopt($curl_handle,CURLOPT_FOLLOWLOCATION, true); ';
        $content_email_alert_curl .= 'curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, true); ';
        $content_email_alert_curl .= 'curl_setopt($curl_handle,CURLOPT_MAXREDIRS, 10000); ';
        $content_email_alert_curl .= 'curl_setopt($curl_handle, CURLOPT_URL, "' . $url_cron . '/email_alert.php?' . $params . '"); ';
        $content_email_alert_curl .= '$result = curl_exec($curl_handle); ';
        $content_email_alert_curl .= 'curl_close($curl_handle); ';
        $content_email_alert_curl .= 'if (empty($result) || !$result) ';
        $content_email_alert_curl .= 'echo "An error occured during email alert"; ';
        $content_email_alert_curl .= 'else ';
        $content_email_alert_curl .= 'echo "Email alert sent";';

        $list_crons[] = [
            'file_path' => $physic_path_cron . '/email_alert_curl_' . $file_key . '.php',
            'content' => $content_email_alert_curl,
        ];

        foreach ($list_crons as $cron) {
            $file_path = $cron['file_path'];
            $content = $cron['content'];

            if (NtsApparatus::checkFileExists($file_path)) {
                $old_content = Tools::file_get_contents($file_path);

                if ($old_content != $content) {
                    unlink($file_path);
                }
            }

            if (!NtsApparatus::checkFileExists($file_path)) {
                $file = fopen($file_path, 'w+');
                fwrite($file, $content);
                fclose($file);

                try {
                    if (chmod($file_path, octdec(self::PERM_FILE)) !== true) {
                        if (!$install) {
                            self::lg(
                                sprintf(
                                    $this->l('The file "%1$s" permission cannot be updated to %2$d'),
                                    $file_path,
                                    self::PERM_FILE
                                )
                            );
                        }
                    }
                } catch (Throwable $t) {
                    // Executed only in PHP 7, will not match in PHP 5
                    if (!$install) {
                        self::lg(
                            sprintf(
                                $this->l('The file "%1$s" permission cannot be updated to %2$d'),
                                $file_path,
                                self::PERM_FILE
                            )
                        );

                        self::lg($t->getMessage(), true);
                    }
                } catch (Exception $e) {
                    // Executed only in PHP 5, will not be reached in PHP 7
                    if (!$install) {
                        self::lg(
                            sprintf(
                                $this->l('The file "%1$s" permission cannot be updated to %2$d'),
                                $file_path,
                                self::PERM_FILE
                            )
                        );

                        self::lg($e->getMessage(), true);
                    }
                }
            }
        }
    }

    /**
     * Set IP in maintenance mode
     */
    public function setMaintenanceIP()
    {
        // Find IP
        $ip = $_SERVER['REMOTE_ADDR'];

        // Current shop
        $id_shop = Context::getContext()->shop->id;
        $id_shop_group = Context::getContext()->shop->id_shop_group;
        $a_config = self::getConfig();

        // Is IP already in the list ?
        $ip_list = Configuration::get('PS_MAINTENANCE_IP', null, $id_shop_group, $id_shop);
        $array_ip_list = ($ip_list) ? explode(',', $ip_list) : [];
        $array_ipv4_list = explode(',', self::IPV4_NTCRON);
        $array_ipv6_list = explode(',', self::IPV6_NTCRON);

        if (!in_array($ip, $array_ip_list)) {
            $array_ip_list[] = $ip;
        }

        if (!$a_config['automation_2nt_ip']) { // Add IPv4 and IPv6
            foreach ($array_ipv4_list as $ipv4) {
                if (!in_array($ipv4, $array_ip_list)) { // Add IPv4
                    $array_ip_list[] = $ipv4;
                }
            }

            foreach ($array_ipv6_list as $ipv6) {
                if (!in_array($ipv6, $array_ip_list)) { // Add IPv6
                    $array_ip_list[] = $ipv6;
                }
            }
        } elseif ($a_config['automation_2nt_ip'] == 1) { // Add only IPv4
            foreach ($array_ipv4_list as $ipv4) {
                if (!in_array($ipv4, $array_ip_list)) { // Add IPv4
                    $array_ip_list[] = $ipv4;
                }
            }

            foreach ($array_ipv6_list as $ipv6) {
                if (in_array($ipv6, $array_ip_list)) { // Remove IPv6
                    $key = array_search($ipv6, $array_ip_list);
                    unset($array_ip_list[$key]);
                }
            }
        } elseif ($a_config['automation_2nt_ip'] == 2) { // Add only IPv6
            foreach ($array_ipv6_list as $ipv6) {
                if (!in_array($ipv6, $array_ip_list)) { // Add IPv6
                    $array_ip_list[] = $ipv6;
                }
            }

            foreach ($array_ipv4_list as $ipv4) {
                if (in_array($ipv4, $array_ip_list)) { // Remove IPv4
                    $key = array_search($ipv4, $array_ip_list);
                    unset($array_ip_list[$key]);
                }
            }
        } else { // Add neither IPv4 nor IPv6
            foreach ($array_ipv4_list as $ipv4) {
                if (in_array($ipv4, $array_ip_list)) { // Remove IPv4
                    $key = array_search($ipv4, $array_ip_list);
                    unset($array_ip_list[$key]);
                }
            }

            foreach ($array_ipv6_list as $ipv6) {
                if (in_array($ipv6, $array_ip_list)) { // Remove IPv6
                    $key = array_search($ipv6, $array_ip_list);
                    unset($array_ip_list[$key]);
                }
            }
        }

        // We need to add IP
        $new_list = implode(',', $array_ip_list);
        Configuration::updateValue('PS_MAINTENANCE_IP', $new_list, false, $id_shop_group, $id_shop);
    }

    public function createModuleId()
    {
        $prefix = (self::isPrestaEdition()) ? 'EDT' : 'STD';

        return $prefix . '_' . Tools::passwdGen(30);
    }

    public function setOperation($operation)
    {
        // if (self::checkValidIp()) {
        $module_id = Configuration::get('NTSTATS_ID');

        // Check module ID exists
        if (!$module_id || $module_id == '') {
            Configuration::updateGlobalValue('NTSTATS_ID', $this->createModuleId());

            $module_id = Configuration::get('NTSTATS_ID');
        }

        if ($module_id && $module_id != '') {
            // Call the operation url
            $url = self::NT_URL_OPERATION
            . 'module=' . urlencode((string) $this->name)
            . '&id_module=' . urlencode((string) $module_id)
            . '&version=' . urlencode((string) $this->version)
            . '&operation=' . $operation;

            try {
                Tools::file_get_contents($url);
            } catch (Throwable $t) {
                $this->log($t->getMessage());
            } catch (Exception $ex) {
                $this->log($ex->getMessage());
            }
        }
        // }
    }

    public static function isPrestaEdition()
    {
        return Module::isEnabled('smb_edition');
    }

    public static function checkValidIp()
    {
        $domain_use = Tools::getHttpHost();

        $ip = $domain_use;
        // If the domain is not an IP, find the IP of the domain
        if (!filter_var($domain_use, FILTER_VALIDATE_IP)) {
            // $ip = gethostbyname($domain_use);

            if (strpos($ip, 'localhost') === false) {
                $ip = filter_var(Tools::file_get_contents(self::URL_SERVICE_IP_EXTERNE), FILTER_VALIDATE_IP);

                if ($ip === false) {
                    $ip = false;
                }
            } else {
                $ip = false;
            }
        }

        $special_ip_range = [
            '0.0.0.0/8',
            '10.0.0.0/8',
            '100.64.0.0/10',
            '127.0.0.0/8',
            '169.254.0.0/16',
            '172.16.0.0/12',
            '192.0.0.0/24',
            '192.0.2.0/24',
            '192.88.99.0/24',
            '192.168.0.0/16',
            '198.18.0.0/15',
            '198.51.100.0/24',
            '203.0.113.0/24',
            '224.0.0.0/4',
            '240.0.0.0/4',
            '255.255.255.255/32',
            '::/128',
            '::1/128',
            '::ffff:0:0/96',
            '0100::/64',
            '2000::/3',
            '2001::/32',
            '2001:2::/48',
            '2001:10::/28',
            '2001:db8::/32',
            '2002::/16',
            'fc00::/7',
            'fe80::/10',
            'ff00::/8',
        ];

        if ($ip) {
            foreach ($special_ip_range as $range) {
                $is_ip_in_range = self::ipInRange($ip, $range);
                if ($is_ip_in_range !== false) {
                    return false;
                }
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * ip_in_range.php - Function to determine if an IP is located in a
     *                   specific range as specified via several alternative
     *                   formats.
     *
     * Network ranges can be specified as:
     * 1. Wildcard format:     1.2.3.*
     * 2. CIDR format:         1.2.3/24  OR  1.2.3.4/255.255.255.0
     * 3. Start-End IP format: 1.2.3.0-1.2.3.255
     *
     * Return value BOOLEAN : ip_in_range($ip, $range);
     *
     * Copyright 2008: Paul Gregg <pgregg@pgregg.com>
     * 10 January 2008
     * Version: 1.2
     *
     * Source website: http://www.pgregg.com/projects/php/ip_in_range/
     * Version 1.2
     *
     * This software is Donationware - if you feel you have benefited from
     * the use of this tool then please consider a donation. The value of
     * which is entirely left up to your discretion.
     * http://www.pgregg.com/donate/
     *
     * Please do not remove this header, or source attibution from this file.
     */

    // ip_in_range
    // This function takes 2 arguments, an IP address and a "range" in several
    // different formats.
    // Network ranges can be specified as:
    // 1. Wildcard format:     1.2.3.*
    // 2. CIDR format:         1.2.3/24  OR  1.2.3.4/255.255.255.0
    // 3. Start-End IP format: 1.2.3.0-1.2.3.255
    // The function will return true if the supplied IP is within the range.
    // Note little validation is done on the range inputs - it expects you to
    // use one of the above 3 formats.
    public static function ipInRange($ip, $range)
    {
        $range_without_mask = explode('/', $range);
        // If the range AND the ip to test are ipv6
        if (isset($range_without_mask[0])
            && filter_var($range_without_mask[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
            && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
        ) {
            // return self::ipv6InRange($ip, $range);
            return false; // The ipv6 test is not working for now
        } elseif ((isset($range_without_mask[0]) && filter_var($range_without_mask[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
            || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)// If the range OR the ip to test are ipv6
        ) {
            return false;
        }

        if (strpos($range, '/') !== false) {
            // $range is in IP/NETMASK format
            list($range, $netmask) = explode('/', $range, 2);
            if (strpos($netmask, ' . ') !== false) {
                // $netmask is a 255.255.0.0 format
                $netmask = str_replace('*', '0', $netmask);
                $netmask_dec = ip2long($netmask);

                return (ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec);
            } else {
                // $netmask is a CIDR size block
                // fix the range argument
                $x = explode(' . ', $range);
                while (count($x) < 4) {
                    $x[] = '0';
                }
                list($a, $b, $c, $d) = $x;
                $range = sprintf('%u.%u.%u.%u', empty($a) ? '0' : $a, empty($b) ? '0' : $b, empty($c) ? '0' : $c, empty($d) ? '0' : $d);
                $range_dec = ip2long($range);
                $ip_dec = ip2long($ip);

                // Strategy 1 - Create the netmask with 'netmask' 1s and then fill it to 32 with 0s
                // $netmask_dec = bindec(str_pad('', $netmask, '1') . str_pad('', 32-$netmask, '0'));

                // Strategy 2 - Use math to create it
                $wildcard_dec = pow(2, 32 - $netmask) - 1;
                $netmask_dec = ~$wildcard_dec;

                return ($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec);
            }
        } else {
            // range might be 255.255.*.* or 1.2.3.0-1.2.3.255
            if (strpos($range, '*') !== false) { // a.b.*.* format
                // Just convert to A-B format by setting * to 0 for A and 255 for B
                $lower = str_replace('*', '0', $range);
                $upper = str_replace('*', '255', $range);
                $range = "$lower-$upper";
            }

            if (strpos($range, '-') !== false) { // A-B format
                list($lower, $upper) = explode('-', $range, 2);
                $lower_dec = (float) sprintf('%u', ip2long($lower));
                $upper_dec = (float) sprintf('%u', ip2long($upper));
                $ip_dec = (float) sprintf('%u', ip2long($ip));

                return ($ip_dec >= $lower_dec) && ($ip_dec <= $upper_dec);
            }

            // echo 'Range argument is not in 1.2.3.4/24 or 1.2.3.4/255.255.255.0 format';
            if ($ip == $range) {
                return true;
            }

            return false;
        }
    }

    // Determine whether the IPV6 address is within range.
    // $ip is the IPV6 address in decimal format to check if
    // its within the IP range created by the cloudflare IPV6 address, $range_ip.
    // $ip and $range_ip are converted to full IPV6 format.
    // Returns true if the IPV6 address, $ip,  is within the range from $range_ip.  False otherwise.
    public static function ipv6InRange($ip, $range_ip)
    {
        $pieces = explode('/', $range_ip, 2);
        $left_piece = isset($pieces[0]) ? $pieces[0] : '';
        // $right_piece = isset($pieces[1])?$pieces[1]:'';
        // Extract out the main IP pieces
        $ip_pieces = explode('::', $left_piece, 2);
        $main_ip_piece = isset($ip_pieces[0]) ? $ip_pieces[0] : '';
        $last_ip_piece = isset($ip_pieces[1]) ? $ip_pieces[1] : '';
        // Pad out the shorthand entries.
        $main_ip_pieces = explode(':', $main_ip_piece);
        foreach ($main_ip_pieces as $key => $val) {
            $val = $val; // Prevent warning "Unused variable" from validator
            $main_ip_pieces[$key] = str_pad($main_ip_pieces[$key], 4, '0', STR_PAD_LEFT);
        }
        // Create the first and last pieces that will denote the IPV6 range.
        $first = $main_ip_pieces;
        $last = $main_ip_pieces;
        // Check to see if the last IP block (part after ::) is set
        $last_piece = '';
        $size = count($main_ip_pieces);
        if (trim($last_ip_piece) != '') {
            $last_piece = str_pad($last_ip_piece, 4, '0', STR_PAD_LEFT);

            // Build the full form of the IPV6 address considering the last IP block set
            for ($i = $size; $i < 7; ++$i) {
                $first[$i] = '0000';
                $last[$i] = 'ffff';
            }
            $main_ip_pieces[7] = $last_piece;
        } else {
            // Build the full form of the IPV6 address
            for ($i = $size; $i < 8; ++$i) {
                $first[$i] = '0000';
                $last[$i] = 'ffff';
            }
        }
        // Rebuild the final long form IPV6 address
        $first = self::ip2long6(implode(':', $first));
        $last = self::ip2long6(implode(':', $last));
        $in_range = ($ip >= $first && $ip <= $last);

        return $in_range;
    }

    public static function ip2long6($ip)
    {
        if (substr_count($ip, '::')) {
            $ip = str_replace('::', str_repeat(':0000', 8 - substr_count($ip, ':')) . ':', $ip);
        }

        $ip = explode(':', $ip);
        $r_ip = '';
        foreach ($ip as $v) {
            $r_ip .= str_pad(base_convert($v, 16, 2), 16, 0, STR_PAD_LEFT);
        }

        return base_convert($r_ip, 2, 10);
    }

    public static function whereShop($prefix, $group = true)
    {
        $shop_context = Shop::getContext();
        $req_shop = '';

        if ($shop_context == Shop::CONTEXT_SHOP) {
            $req_shop = ' AND ' . pSQL($prefix) . '.`id_shop` = ' . (int) Context::getContext()->shop->id;
        } elseif ($group && $shop_context == Shop::CONTEXT_GROUP) {
            $req_shop = ' AND ' . pSQL($prefix) . '.`id_shop_group` = ' . (int) Shop::getContextShopGroupID(); // Context::getContext()->shop->id_shop_group may not the correct group in context_group
        }

        return $req_shop;
    }

    public static function whereShopStockAvailable($prefix)
    {
        $req_shop = '';
        $context = Context::getContext();

        if (Shop::getContext() == Shop::CONTEXT_GROUP) {
            $shop_group = Shop::getContextShopGroup();
        } else {
            $shop_group = $context->shop->getGroup();
        }

        $shop = $context->shop;

        // if quantities are shared between shops of the group
        if ($shop_group->share_stock) {
            $req_shop = ' AND ' . pSQL($prefix) . '.`id_shop_group` = ' . (int) $shop_group->id . ' AND ' . pSQL($prefix) . '.`id_shop` = 0';
        } else {
            $req_shop = ' AND ' . pSQL($prefix) . '.`id_shop` = ' . (int) $shop->id . ' AND ' . pSQL($prefix) . '.`id_shop_group` = 0';
        }

        return $req_shop;
    }

    public static function reqDateValid($prefix_order)
    {
        $a_config = self::getConfig();

        if ($a_config['order_type_date'] == NtsConfig::ORDER_TYPE_DATE_INVOICE) {
            return pSQL($prefix_order) . '.`invoice_date`';
        } elseif ($a_config['order_type_date'] == NtsConfig::ORDER_TYPE_DATE_ADD) {
            return pSQL($prefix_order) . '.`date_add`';
        } elseif ($a_config['order_type_date'] == NtsConfig::ORDER_TYPE_DATE_STATE && $a_config['order_date_state'] > 0) {
            return '
                (
                    SELECT MIN(oh.`date_add`)
                    FROM `' . _DB_PREFIX_ . 'order_history` oh
                    WHERE oh.`id_order` = ' . pSQL($prefix_order) . '.`id_order`
                    AND oh.`id_order_state` = ' . (int) $a_config['order_date_state'] . '
                )
            ';
        } else {
            $use_invoice = self::useInvoice();

            if ($use_invoice) {
                return $prefix_order . '.`invoice_date`';
            } else {
                return '
                    (
                        SELECT MIN(oh.`date_add`)
                        FROM `' . _DB_PREFIX_ . 'order_history` oh
                        JOIN `' . _DB_PREFIX_ . 'order_state` os ON oh.`id_order_state` = os.`id_order_state`
                        WHERE oh.`id_order` = ' . pSQL($prefix_order) . '.`id_order`
                        AND os.`logable` = 1
                    )
                ';
            }
        }
    }

    public static function reqLocationValid($prefix_order)
    {
        $a_config = self::getConfig();

        if ($a_config['order_type_location'] == NtsConfig::ORDER_TYPE_LOCATION_DELIVERY) {
            return pSQL($prefix_order) . '.`id_address_delivery`';
        }

        return pSQL($prefix_order) . '.`id_address_invoice`';
    }

    public static function reqReturnValid($prefix_return_order)
    {
        $a_config = self::getConfig();

        if (is_array($a_config['return_valid_states']) && count($a_config['return_valid_states'])) {
            return ' AND ' . pSQL($prefix_return_order) . '.`state` IN (' . implode(',', $a_config['return_valid_states']) . ')';
        }

        return '';
    }

    public static function useInvoice()
    {
        $state_with_invoice = (int) Db::getInstance()->getValue('
            SELECT COUNT(*)
            FROM `' . _DB_PREFIX_ . 'order_state`
            WHERE `invoice` = 1
        ');

        if ($state_with_invoice < 1 || !Configuration::get('PS_INVOICE')) {
            return false;
        }

        return true;
    }

    public static function getListOrderCartRules()
    {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT cr.`id_cart_rule`, IFNULL(cr.`code`, "") AS code, crl.`name`
            FROM `' . _DB_PREFIX_ . 'cart_rule` cr
            JOIN `' . _DB_PREFIX_ . 'cart_rule_lang` crl ON crl.`id_cart_rule` = cr.`id_cart_rule`
                AND crl.`id_lang` = ' . $id_lang . '
            JOIN `' . _DB_PREFIX_ . 'order_cart_rule` ocr ON ocr.`id_cart_rule` = cr.`id_cart_rule`
            JOIN `' . _DB_PREFIX_ . 'orders` o ON ocr.`id_order` = o.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . self::reqLocationValid('o') . '
            WHERE o.`valid` = 1
            ' . self::whereShop('o') . '
            ' . self::getWhereProfileCountrie('a') . '
            ORDER BY crl.`name`, cr.`code`
        ');

        // To force natural sorting by keys
        foreach ($data as $dt) {
            $list[Tools::strtolower(Tools::replaceAccentedChars($dt['name'] . '_' . $dt['code']))] = $dt;
        }

        array_multisort(array_keys($list), SORT_ASC, SORT_NATURAL, $list);

        return $list;
    }

    public static function getListOrderProducts($id_category = [], $display_products_simple = true, $display_products_combinations = true)
    {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;

        $categories = [];

        if ($id_category) {
            foreach ($id_category as $id_cat) {
                $o_category = new Category($id_cat);
                $children = $o_category->getAllChildren();
                $categories[] = $id_cat;

                foreach ($children as $child) {
                    $categories[] = (int) $child->id;
                }
            }
        }

        $where = '';
        $join = '';

        // Only display combinations
        if (!$display_products_simple) {
            $join .= '
                JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` pas ON p.`id_product` = pas.`id_product`
            ';
        } elseif (!$display_products_combinations) {// Only display simple products (no combination)
            $where .= '
                AND p.`id_product` NOT IN(
                    SELECT pas.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product_attribute_shop` pas
                )
            ';
        }

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT p.`id_product`, p.`reference`, pl.`name`
            FROM `' . _DB_PREFIX_ . 'product` p
            JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.`id_product` = p.`id_product`
                AND pl.`id_lang` = ' . $id_lang . ' ' . self::whereShop('pl', false) . '
            JOIN `' . _DB_PREFIX_ . 'order_detail` od ON p.`id_product` = od.`product_id`
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . self::reqLocationValid('o') . '
            ' . $join . '
            WHERE o.`valid` = 1
            ' . $where . '
            ' . self::whereShop('o') . '
            ' . self::getWhereProfileCountrie('a') . '
            ' . (($id_category) ? ' AND p.`id_product` IN(
                SELECT cp.`id_product`
                FROM `' . _DB_PREFIX_ . 'category_product` cp
                WHERE cp.`id_category` IN (' . self::protectIntArraySQL($categories) . ')
                )
            ' : '') . '
            ORDER BY p.`reference`, pl.`name`
        ');

        // To force natural sorting by keys
        foreach ($data as $dt) {
            $list[Tools::strtolower(Tools::replaceAccentedChars($dt['reference'] . '_' . $dt['name']))] = $dt;
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        return $list;
    }

    public static function getListProducts($id_category = [], $display_products_simple = true, $display_products_combinations = true)
    {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;

        $categories = [];

        if ($id_category) {
            foreach ($id_category as $id_cat) {
                $o_category = new Category($id_cat);
                $children = $o_category->getAllChildren();
                $categories[] = $id_cat;

                foreach ($children as $child) {
                    $categories[] = (int) $child->id;
                }
            }
        }

        $where = '';
        $join = '';

        // Only display combinations
        if (!$display_products_simple) {
            $join .= '
                JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` pas ON p.`id_product` = pas.`id_product`
            ';
        } elseif (!$display_products_combinations) {// Only display simple products (no combination)
            $where .= '
                AND p.`id_product` NOT IN(
                    SELECT pas.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product_attribute_shop` pas
                )
            ';
        }

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT p.`id_product`, p.`reference`, pl.`name`
            FROM `' . _DB_PREFIX_ . 'product` p
            JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.`id_product` = p.`id_product`
                AND pl.`id_lang` = ' . $id_lang . ' ' . self::whereShop('pl', false) . '
            ' . $join . '
            WHERE p.`active` = 1
            ' . $where . '
            ' . (($id_category) ? ' AND p.`id_product` IN(
                SELECT cp.`id_product`
                FROM `' . _DB_PREFIX_ . 'category_product` cp
                WHERE cp.`id_category` IN (' . self::protectIntArraySQL($categories) . ')
                )
            ' : '') . '
            ORDER BY p.`reference`, pl.`name`
        ');

        // To force natural sorting by keys
        foreach ($data as $dt) {
            $list[Tools::strtolower(Tools::replaceAccentedChars($dt['reference'] . '_' . $dt['name']))] = $dt;
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        return $list;
    }

    public static function getListCombinations($id_product = [], $display_combinations_ordered = false)
    {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;

        $where = '';

        if ($id_product) {
            $where .= ' AND pa.`id_product` IN (' . pSQL(implode(',', $id_product)) . ')';
        }

        if ($display_combinations_ordered) {
            $where .= ' AND pa.`id_product_attribute` IN (
                SELECT od.`product_attribute_id`
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . self::reqLocationValid('o') . '
                WHERE o.`valid` = 1
                ' . self::whereShop('o') . '
                ' . self::getWhereProfileCountrie('a') . '
            )';
        }

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT pa.`id_product_attribute`, pa.`id_product`,
                IF(pa.`reference` IS NULL OR pa.`reference` = "", p.`reference`, pa.`reference`) AS reference, pl.`name`,
                GROUP_CONCAT(DISTINCT CONCAT(IFNULL(agl.`name`, ""), " - ", IFNULL(al.`name`, "")) ORDER BY agl.`name`, al.`name` SEPARATOR ", ") AS combination
            FROM `' . _DB_PREFIX_ . 'product_attribute` pa
            JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` pas ON pas.`id_product_attribute` = pa.`id_product_attribute`
                ' . self::whereShop('pas', false) . '
            JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.`id_product` = pa.`id_product`
                AND pl.`id_lang` = ' . $id_lang . ' ' . self::whereShop('pl', false) . '
            JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac
                ON pac.`id_product_attribute` = pa.`id_product_attribute`
            JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
            JOIN `' . _DB_PREFIX_ . 'attribute_shop` ash ON ash.`id_attribute` = a.`id_attribute`
                ' . self::whereShop('ash', false) . '
            JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON al.`id_attribute` = a.`id_attribute`
                AND al.`id_lang` = ' . $id_lang . '
            JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON agl.`id_attribute_group` = a.`id_attribute_group`
                AND agl.`id_lang` = ' . $id_lang . '
            JOIN `' . _DB_PREFIX_ . 'attribute_group_shop` ags ON ags.`id_attribute_group` = a.`id_attribute_group`
                ' . self::whereShop('ags', false) . '
            JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = pa.`id_product`
            WHERE 1 = 1
            ' . $where . '
            GROUP BY pa.`id_product_attribute`
            ORDER BY reference, pl.`name`, combination
        ');

        // To force natural sorting by keys
        foreach ($data as $dt) {
            $key = Tools::strtolower(Tools::replaceAccentedChars($dt['reference'] . '_' . $dt['name'] . '_' . $dt['combination'] . '_' . $dt['id_product_attribute']));

            $list[$key] = [
                'id_product_attribute' => $dt['id_product_attribute'],
                'id_product' => $dt['id_product'],
                'reference' => $dt['reference'],
                'name' => $dt['name'],
                'combination' => $dt['combination'],
            ];
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        return $list;
    }

    public static function getListOrderManufacturers()
    {
        $list = [];

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT p.`id_manufacturer`, m.`name`
            FROM `' . _DB_PREFIX_ . 'product` p
            JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON m.`id_manufacturer` = p.`id_manufacturer`
            JOIN `' . _DB_PREFIX_ . 'order_detail` od ON p.`id_product` = od.`product_id`
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . self::reqLocationValid('o') . '
            WHERE o.`valid` = 1
            ' . self::whereShop('o') . '
            ' . self::getWhereProfileCountrie('a') . '
            ORDER BY m.`name`
        ');

        // To force natural sorting by keys
        foreach ($data as $dt) {
            $list[Tools::strtolower(Tools::replaceAccentedChars($dt['name']))] = $dt;
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        return $list;
    }

    public static function getConfig()
    {
        $id_lang = Context::getContext()->language->id;
        $id_shop_group = Context::getContext()->shop->id_shop_group;
        $id_shop = Context::getContext()->shop->id;

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

        return $a_config;
    }

    public static function getWhereProfileCountrie($prefix)
    {
        $a_config = self::getConfig();
        $id_profile = Context::getContext()->employee->id_profile;
        $where_profile_countries = '';

        if ($id_profile != _PS_ADMIN_PROFILE_
            && is_array($a_config['profil_countries'][$id_profile]['id_countries'])
            && count($a_config['profil_countries'][$id_profile]['id_countries'])
        ) {
            $where_profile_countries = ' AND ' . $prefix . '.`id_country` IN (' . self::protectIntArraySQL($a_config['profil_countries'][$id_profile]['id_countries']) . ')';
        }

        return $where_profile_countries;
    }

    public static function getListOrderCountries()
    {
        $id_lang = (int) Context::getContext()->language->id;

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT cl.`name`, cl.`id_country`
            FROM `' . _DB_PREFIX_ . 'orders` o
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . self::reqLocationValid('o') . '
            JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON a.`id_country` = cl.`id_country`
                AND cl.`id_lang` = ' . $id_lang . '
            WHERE o.`valid` = 1
            ' . self::whereShop('o') . '
            ' . self::getWhereProfileCountrie('cl') . '
            ORDER BY cl.`name`
        ');

        return $data;
    }

    public static function getListAllCountries()
    {
        $id_lang = (int) Context::getContext()->language->id;

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT cl.`name`, cl.`id_country`
            FROM `' . _DB_PREFIX_ . 'country` c
            JOIN `' . _DB_PREFIX_ . 'country_shop` cs ON c.`id_country` = cs.`id_country`
            JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON c.`id_country` = cl.`id_country`
            WHERE cl.`id_lang` = ' . $id_lang . '
            ' . self::whereShop('cs', false) . '
            ORDER BY cl.`name`
        ');

        return $data;
    }

    public static function getListGroups()
    {
        $id_lang = (int) Context::getContext()->language->id;

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT gl.`name`, gl.`id_group`
            FROM `' . _DB_PREFIX_ . 'group_shop` gs
            JOIN `' . _DB_PREFIX_ . 'group_lang` gl ON gs.`id_group` = gl.`id_group` AND gl.`id_lang` = ' . $id_lang . '
            WHERE 1 = 1
            ' . self::whereShop('gs', false) . '
            ORDER BY gl.`name`
        ');

        return $data;
    }

    public static function getListFeatures()
    {
        $id_lang = (int) Context::getContext()->language->id;

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT fl.`name`, fl.`id_feature`
            FROM `' . _DB_PREFIX_ . 'feature` f
            JOIN `' . _DB_PREFIX_ . 'feature_shop` fs ON fs.`id_feature` = f.`id_feature`
            JOIN `' . _DB_PREFIX_ . 'feature_product` fp ON fp.`id_feature` = f.`id_feature`
            JOIN `' . _DB_PREFIX_ . 'feature_lang` fl ON fs.`id_feature` = fl.`id_feature` AND fl.`id_lang` = ' . $id_lang . '
            WHERE 1 = 1
            ' . self::whereShop('fs', false) . '
            ORDER BY f.`position`
        ');

        return $data;
    }

    public static function getListFeatureValues($id_feature = [])
    {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;
        $p_id_feature = self::protectIntArraySQL($id_feature);

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT fp.`id_feature`, fl.`name`, f.`position`, fp.`id_feature_value`, fvl.`value`
            FROM `' . _DB_PREFIX_ . 'feature_product` fp
            JOIN `' . _DB_PREFIX_ . 'feature` f ON fp.`id_feature` = f.`id_feature`
            JOIN `' . _DB_PREFIX_ . 'feature_lang` fl ON fl.`id_feature` = fp.`id_feature`
                AND fl.`id_lang` = ' . $id_lang . '
            JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl ON fvl.`id_feature_value` = fp.`id_feature_value`
                AND fvl.`id_lang` = ' . $id_lang . '
            WHERE fvl.`value` <> ""
            ' . (($id_feature) ? ' AND fp.`id_feature` IN(' . $p_id_feature . ')
            ' : '') . '
            ORDER BY f.`position`, fvl.`value`
        ');

        // To force natural sorting by keys
        foreach ($data as $dt) {
            $feature_key = $dt['position'] . '_' . Tools::strtolower(Tools::replaceAccentedChars($dt['name']));

            if (!isset($list[$feature_key])) {
                $list[$feature_key] = [
                    'name' => $dt['name'],
                    'values' => [],
                ];
            }

            $list[$feature_key]['values'][Tools::strtolower(Tools::replaceAccentedChars($dt['value']))] = $dt;
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        foreach ($list as &$item) {
            array_multisort(array_keys($item['values']), SORT_NATURAL, $item['values']);
        }

        return $list;
    }

    public static function getListCarriers()
    {
        $list = [];

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT c.`id_carrier`, c.`name`
            FROM `' . _DB_PREFIX_ . 'carrier` c
            JOIN `' . _DB_PREFIX_ . 'carrier_shop` cs ON cs.`id_carrier` = c.`id_carrier` ' . self::whereShop('cs', false) . '
            WHERE c.`active` = 1
            AND c.`deleted` = 0
            ORDER BY c.`name`
        ');

        // To force natural sorting by keys
        foreach ($data as $dt) {
            if ($dt['name'] == '0') {
                $dt['name'] = Configuration::get('PS_SHOP_NAME');
            }

            $list[Tools::strtolower(Tools::replaceAccentedChars($dt['name']))] = $dt;
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        return $list;
    }

    public static function getListManufacturers()
    {
        $list = [];

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT m.`id_manufacturer`, m.`name`
            FROM `' . _DB_PREFIX_ . 'manufacturer` m
            JOIN `' . _DB_PREFIX_ . 'manufacturer_shop` ms ON ms.`id_manufacturer` = m.`id_manufacturer` ' . self::whereShop('ms', false) . '
            WHERE m.`active` = 1
            ORDER BY m.`name`
        ');

        // To force natural sorting by keys
        foreach ($data as $dt) {
            $list[Tools::strtolower(Tools::replaceAccentedChars($dt['name']))] = $dt;
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        return $list;
    }

    public static function getListCategories()
    {
        $tree_categories = Category::getRootCategory()->recurseLiteCategTree(0);

        if (!isset($tree_categories['children']) || !is_array($tree_categories['children'])) {
            return [];
        }

        // We do not display root category
        $list = self::categoryPath('', $tree_categories['children']);

        return $list;
    }

    public static function categoryPath($parent_path, $tree_children)
    {
        $list_categories = [];

        foreach ($tree_children as $category) {
            $path = ($parent_path ? $parent_path . '/' : '') . $category['name'];

            $list_categories[] = [
                'id' => $category['id'],
                'path' => $path,
            ];

            if (isset($category['children']) && is_array($category['children']) && count($category['children'])) {
                $list_categories = array_merge($list_categories, self::categoryPath($path, $category['children']));
            }
        }

        return $list_categories;
    }

    public function getDashboardData($year)
    {
        $list_sales = [];
        $list_nb_orders = [];

        // Init data
        for ($i = 1; $i <= 12; ++$i) {
            $list_sales[$i] = 0;
            $list_nb_orders[$i] = 0;
        }

        $p_year = pSQL($year);
        $req_date_valid_o = self::reqDateValid('o');
        $req_location_valid_o = self::reqLocationValid('o');
        $where_shop_o = self::whereShop('o');
        $where_profile_country_a = self::getWhereProfileCountrie('a');

        $data = Db::getInstance()->executeS('
            SELECT date_valid, month, SUM(nb_order) AS nb_order, SUM(total_products_tax_excl) AS total_products_tax_excl,
                SUM(total_shipping_tax_excl) AS total_shipping_tax_excl,
                SUM(total_discounts_tax_excl) AS total_discounts_tax_excl,
                SUM(total_slip_tax_excl) AS total_slip_tax_excl
            FROM (
                SELECT DATE_FORMAT(' . $req_date_valid_o . ', "%Y-%m") AS date_valid,
                    DATE_FORMAT(' . $req_date_valid_o . ', "%c") AS month, COUNT(o.`id_order`) AS nb_order,
                    IFNULL(SUM(IFNULL(o.`total_products`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_products_tax_excl,
                    IFNULL(SUM(IFNULL(o.`total_shipping_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_shipping_tax_excl,
                    IFNULL(SUM(IFNULL(o.`total_discounts_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_discounts_tax_excl,
                    0 AS total_slip_tax_excl
                FROM `' . _DB_PREFIX_ . 'orders` o
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_location_valid_o . '
                WHERE ' . $req_date_valid_o . ' BETWEEN "' . $p_year . '-01-01 00:00:00" AND "' . $p_year . '-12-31 23:59:59"
                AND o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                GROUP BY date_valid

                UNION

                SELECT DATE_FORMAT(os.`date_add`, "%Y-%m") AS date_valid,
                    DATE_FORMAT(os.`date_add`, "%c") AS month, 0 AS nb_order,
                    0 AS total_products_tax_excl,
                    0 AS total_shipping_tax_excl,
                    0 AS total_discounts_tax_excl,
                    IFNULL(SUM((IFNULL(os.`total_products_tax_excl`, 0) + IFNULL(os.`total_shipping_tax_excl`, 0))/IFNULL(os.`conversion_rate`, 1)), 0) AS total_slip_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_slip` os
                JOIN `' . _DB_PREFIX_ . 'orders` o ON os.`id_order` = o.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_location_valid_o . '
                WHERE os.`date_add` BETWEEN "' . $p_year . '-01-01 00:00:00" AND "' . $p_year . '-12-31 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                GROUP BY date_valid
            ) t
            GROUP BY date_valid
            ORDER BY date_valid DESC
        ');

        foreach ($data as $dt) {
            $sales = $dt['total_products_tax_excl'] - $dt['total_slip_tax_excl'] - $dt['total_discounts_tax_excl']; // Only products, no shipping

            $list_sales[$dt['month']] = round($sales, 2);
            $list_nb_orders[$dt['month']] = $dt['nb_order'];
        }

        return [
            'list_sales' => $list_sales,
            'list_nb_orders' => $list_nb_orders,
        ];
    }

    public function getTotalSales($from, $to, $id_group, $for_export = false)
    {
        $list = [];

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $req_valid_o = self::reqDateValid('o');
        $req_location_valid_o = self::reqLocationValid('o');
        $where_shop_o = self::whereShop('o');
        $where_profile_country_a = self::getWhereProfileCountrie('a');
        $p_id_group = self::protectIntArraySQL($id_group);

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_total_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_total_sales`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_total_sales` (
                `id_order`          int(10)         UNSIGNED    NOT NULL,
                `cost`              decimal(20,6)               NOT NULL,
                PRIMARY KEY (`id_order`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_product_total_sales` (
                `product_id`        int(10)         UNSIGNED    NOT NULL,
                `cost`              decimal(20,6)               NOT NULL,
                PRIMARY KEY (`product_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_product_total_sales` (`product_id`, `cost`)
            SELECT t.`product_id`, t.`cost`
            FROM (
                    SELECT od.`product_id`, (
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN od.`purchase_supplier_price`
                        ELSE od.`original_wholesale_price`
                        END
                    ) AS cost
                    FROM `' . _DB_PREFIX_ . 'order_detail` od
                    JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
                    JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_location_valid_o . '
                    WHERE ' . $req_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                    AND o.`valid` = 1
                    ' . $where_shop_o . '
                    ' . $where_profile_country_a . '
                    ' . (($id_group) ? ' AND o.`id_customer` IN(
                        SELECT cg.`id_customer`
                        FROM `' . _DB_PREFIX_ . 'customer_group` cg
                        WHERE cg.`id_group` IN (' . $p_id_group . ')
                        )
                    ' : '') . '
                    HAVING cost > 0
                    ORDER BY o.`date_add` ASC
            ) t
            GROUP BY t.`product_id`
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_total_sales` (`id_order`, `cost`)
            SELECT od.`id_order`,
                SUM(
                    IFNULL(
                        (
                            SELECT
                            CASE od.`original_wholesale_price`
                            WHEN 0
                            THEN
                                CASE od.`purchase_supplier_price`
                                WHEN 0
                                THEN (
                                    SELECT IFNULL(cpts.`cost`, 0)
                                    FROM `nts_cost_product_total_sales` cpts
                                    WHERE od.`product_id` = cpts.`product_id`
                                )
                                ELSE od.`purchase_supplier_price`
                                END
                            ELSE od.`original_wholesale_price`
                            END
                        ), 0
                    ) * IFNULL(od.`product_quantity`, 0)
                )
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_location_valid_o . '
            WHERE ' . $req_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            AND o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            GROUP BY od.`id_order`
        ');

        $data = Db::getInstance()->executeS('
            SELECT date_valid, SUM(nb_order) AS nb_order, SUM(cost) AS cost,
                SUM(total_products_tax_excl) AS total_products_tax_excl,
                SUM(total_products_tax_incl) AS total_products_tax_incl,
                SUM(total_shipping_tax_excl) AS total_shipping_tax_excl,
                SUM(total_shipping_tax_incl) AS total_shipping_tax_incl,
                SUM(total_discounts_tax_excl) AS total_discounts_tax_excl,
                SUM(total_discounts_tax_incl) AS total_discounts_tax_incl,
                SUM(total_shipping_slip_tax_excl) AS total_shipping_slip_tax_excl,
                SUM(total_shipping_slip_tax_incl) AS total_shipping_slip_tax_incl,
                SUM(total_product_slip_tax_excl) AS total_product_slip_tax_excl,
                SUM(total_product_slip_tax_incl) AS total_product_slip_tax_incl
            FROM (
                SELECT DATE_FORMAT(' . $req_valid_o . ', "%Y-%m-%d") AS date_valid, COUNT(o.`id_order`) AS nb_order, SUM(nt.`cost`) AS cost,
                    IFNULL(SUM(IFNULL(o.`total_products`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_products_tax_excl,
                    IFNULL(SUM(IFNULL(o.`total_products_wt`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_products_tax_incl,
                    IFNULL(SUM(IFNULL(o.`total_shipping_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_shipping_tax_excl,
                    IFNULL(SUM(IFNULL(o.`total_shipping_tax_incl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_shipping_tax_incl,
                    IFNULL(SUM(IFNULL(o.`total_discounts_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_discounts_tax_excl,
                    IFNULL(SUM(IFNULL(o.`total_discounts_tax_incl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_discounts_tax_incl,
                    0 AS total_shipping_slip_tax_excl, 0 AS total_shipping_slip_tax_incl,
                    0 AS total_product_slip_tax_excl, 0 AS total_product_slip_tax_incl
                FROM `' . _DB_PREFIX_ . 'orders` o
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_location_valid_o . '
                LEFT JOIN `nts_cost_total_sales` nt ON nt.`id_order` = o.`id_order`
                WHERE ' . $req_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                AND o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY date_valid

                UNION

                SELECT DATE_FORMAT(os.`date_add`, "%Y-%m-%d") AS date_valid, 0 AS nb_order, 0 AS cost,
                    0 AS total_products_tax_excl, 0 AS total_products_tax_incl,
                    0 AS total_shipping_tax_excl, 0 AS total_shipping_tax_incl,
                    0 AS total_discounts_tax_excl, 0 AS total_discounts_tax_incl,
                    IFNULL(SUM(IFNULL(os.`total_shipping_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_shipping_slip_tax_excl,
                    IFNULL(SUM(IFNULL(os.`total_shipping_tax_incl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_shipping_slip_tax_incl,
                    IFNULL(SUM(IFNULL(os.`total_products_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_product_slip_tax_excl,
                    IFNULL(SUM(IFNULL(os.`total_products_tax_incl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_product_slip_tax_incl
                FROM `' . _DB_PREFIX_ . 'order_slip` os
                JOIN `' . _DB_PREFIX_ . 'orders` o ON os.`id_order` = o.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_location_valid_o . '
                WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY date_valid

                ORDER BY date_valid DESC
            ) t
            GROUP BY date_valid
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_total_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_total_sales`;
        ');

        $total_nb_order = 0;
        $total_products_tax_excl = 0;
        $total_products_tax_incl = 0;
        $total_shipping_tax_excl = 0;
        $total_shipping_tax_incl = 0;
        $total_shipping_slip_tax_excl = 0;
        $total_shipping_slip_tax_incl = 0;
        $total_product_slip_tax_excl = 0;
        $total_product_slip_tax_incl = 0;
        $total_discounts_tax_excl = 0;
        $total_discounts_tax_incl = 0;
        $total_average_cart = 0;
        $total_cost = 0;
        $total_margin = 0;

        foreach ($data as $dt) {
            if ($dt['total_products_tax_excl'] > 0) {
                $average_cart = ($dt['total_products_tax_excl'] - $dt['total_discounts_tax_excl']) / (($dt['nb_order'] > 0) ? $dt['nb_order'] : 1);
            } else {
                $average_cart = 0;
            }

            $sales = $dt['total_products_tax_excl'] - $dt['total_product_slip_tax_excl'] - $dt['total_discounts_tax_excl']; // Only products, no shipping
            $margin = $sales - $dt['cost'];

            if ($sales > 0) {
                $margin_per = ($margin / $sales) * 100;
            } else {
                $margin_per = 0;
            }

            $total_nb_order += $dt['nb_order'];
            $total_products_tax_excl += $dt['total_products_tax_excl'];
            $total_products_tax_incl += $dt['total_products_tax_incl'];
            $total_shipping_tax_excl += $dt['total_shipping_tax_excl'];
            $total_shipping_tax_incl += $dt['total_shipping_tax_incl'];
            $total_shipping_slip_tax_excl += $dt['total_shipping_slip_tax_excl'];
            $total_shipping_slip_tax_incl += $dt['total_shipping_slip_tax_incl'];
            $total_product_slip_tax_excl += $dt['total_product_slip_tax_excl'];
            $total_product_slip_tax_incl += $dt['total_product_slip_tax_incl'];
            $total_discounts_tax_excl += $dt['total_discounts_tax_excl'];
            $total_discounts_tax_incl += $dt['total_discounts_tax_incl'];
            $total_cost += $dt['cost'];
            $total_margin += $margin;

            if ($from == '0000-00-00' || $dt['date_valid'] < $from) {
                $from = $dt['date_valid'];
            }

            $products_vat = $dt['total_products_tax_incl'] - $dt['total_products_tax_excl'];
            $shipping_vat = $dt['total_shipping_tax_incl'] - $dt['total_shipping_tax_excl'];
            $shipping_slip_vat = $dt['total_shipping_slip_tax_incl'] - $dt['total_shipping_slip_tax_excl'];
            $product_slip_vat = $dt['total_product_slip_tax_incl'] - $dt['total_product_slip_tax_excl'];
            $discounts_vat = $dt['total_discounts_tax_incl'] - $dt['total_discounts_tax_excl'];

            $list[] = [
                'date_valid' => $dt['date_valid'],
                'nb_orders' => $dt['nb_order'],
                'total_products_tax_excl' => (($for_export) ? round($dt['total_products_tax_excl'], 2) : self::displayPrice($dt['total_products_tax_excl'])),
                'total_products_vat' => (($for_export) ? round($products_vat, 2) : self::displayPrice($products_vat)),
                'total_shipping_tax_excl' => (($for_export) ? round($dt['total_shipping_tax_excl'], 2) : self::displayPrice($dt['total_shipping_tax_excl'])),
                'total_shipping_vat' => (($for_export) ? round($shipping_vat, 2) : self::displayPrice($shipping_vat)),
                'total_shipping_slip_tax_excl' => (($for_export) ? round($dt['total_shipping_slip_tax_excl'], 2) : self::displayPrice($dt['total_shipping_slip_tax_excl'])),
                'total_shipping_slip_vat' => (($for_export) ? round($shipping_slip_vat, 2) : self::displayPrice($shipping_slip_vat)),
                'total_product_slip_tax_excl' => (($for_export) ? round($dt['total_product_slip_tax_excl'], 2) : self::displayPrice($dt['total_product_slip_tax_excl'])),
                'total_product_slip_vat' => (($for_export) ? round($product_slip_vat, 2) : self::displayPrice($product_slip_vat)),
                'total_discounts_tax_excl' => (($for_export) ? round($dt['total_discounts_tax_excl'], 2) : self::displayPrice($dt['total_discounts_tax_excl'])),
                'total_discounts_vat' => (($for_export) ? round($discounts_vat, 2) : self::displayPrice($discounts_vat)),
                'cost' => (($for_export) ? round($dt['cost'], 2) : self::displayPrice($dt['cost'])),
                'margin' => (($for_export) ? round($margin, 2) : self::displayPrice($margin)),
                'margin_per' => round($margin_per, 2),
                'sales' => (($for_export) ? round($sales, 2) : self::displayPrice($sales)),
                'average_cart' => (($for_export) ? round($average_cart, 2) : self::displayPrice($average_cart)),
            ];
        }

        if ($total_products_tax_excl > 0) {
            $total_average_cart = ($total_products_tax_excl - $total_discounts_tax_excl) / (($total_nb_order > 0) ? $total_nb_order : 1);
        }

        $total_sales = $total_products_tax_excl - $total_product_slip_tax_excl - $total_discounts_tax_excl;

        if ($total_sales > 0) {
            $total_margin_per = ($total_margin / $total_sales) * 100;
        } else {
            $total_margin_per = 0;
        }
        $total_products_vat = $total_products_tax_incl - $total_products_tax_excl;
        $total_shipping_vat = $total_shipping_tax_incl - $total_shipping_tax_excl;
        $total_shipping_slip_vat = $total_shipping_slip_tax_incl - $total_shipping_slip_tax_excl;
        $total_product_slip_vat = $total_product_slip_tax_incl - $total_product_slip_tax_excl;
        $total_discounts_vat = $total_discounts_tax_incl - $total_discounts_tax_excl;

        $list[] = [
            'date_valid' => $this->l('Total'),
            'nb_orders' => $total_nb_order,
            'total_products_tax_excl' => (($for_export) ? round($total_products_tax_excl, 2) : self::displayPrice($total_products_tax_excl)),
            'total_products_vat' => (($for_export) ? round($total_products_vat, 2) : self::displayPrice($total_products_vat)),
            'total_shipping_tax_excl' => (($for_export) ? round($total_shipping_tax_excl, 2) : self::displayPrice($total_shipping_tax_excl)),
            'total_shipping_vat' => (($for_export) ? round($total_shipping_vat, 2) : self::displayPrice($total_shipping_vat)),
            'total_shipping_slip_tax_excl' => (($for_export) ? round($total_shipping_slip_tax_excl, 2) : self::displayPrice($total_shipping_slip_tax_excl)),
            'total_shipping_slip_vat' => (($for_export) ? round($total_shipping_slip_vat, 2) : self::displayPrice($total_shipping_slip_vat)),
            'total_product_slip_tax_excl' => (($for_export) ? round($total_product_slip_tax_excl, 2) : self::displayPrice($total_product_slip_tax_excl)),
            'total_product_slip_vat' => (($for_export) ? round($total_product_slip_vat, 2) : self::displayPrice($total_product_slip_vat)),
            'total_discounts_tax_excl' => (($for_export) ? round($total_discounts_tax_excl, 2) : self::displayPrice($total_discounts_tax_excl)),
            'total_discounts_vat' => (($for_export) ? round($total_discounts_vat, 2) : self::displayPrice($total_discounts_vat)),
            'cost' => (($for_export) ? round($total_cost, 2) : self::displayPrice($total_cost)),
            'margin' => (($for_export) ? round($total_margin, 2) : self::displayPrice($total_margin)),
            'margin_per' => round($total_margin_per, 2),
            'sales' => (($for_export) ? round($total_sales, 2) : self::displayPrice($total_sales)),
            'average_cart' => (($for_export) ? round($total_average_cart, 2) : self::displayPrice($total_average_cart)),
        ];

        $start = new DateTime($from);
        $end = new DateTime($to);

        $nb_days_total = $end->diff($start)->format('%a');

        if ($nb_days_total == 0) {
            $av_nb_order = 0;
            $av_products_tax_excl = 0;
            $av_products_vat = 0;
            $av_shipping_tax_excl = 0;
            $av_shipping_vat = 0;
            $av_shipping_slip_tax_excl = 0;
            $av_shipping_slip_vat = 0;
            $av_product_slip_tax_excl = 0;
            $av_product_slip_vat = 0;
            $av_discounts_tax_excl = 0;
            $av_discounts_vat = 0;
            $av_cost = 0;
            $av_margin = 0;
            $av_sales = 0;
            $av_average_cart = 0;
        } else {
            $av_nb_order = $total_nb_order / $nb_days_total;
            $av_products_tax_excl = $total_products_tax_excl / $nb_days_total;
            $av_products_vat = $total_products_vat / $nb_days_total;
            $av_shipping_tax_excl = $total_shipping_tax_excl / $nb_days_total;
            $av_shipping_vat = $total_shipping_vat / $nb_days_total;
            $av_shipping_slip_tax_excl = $total_shipping_slip_tax_excl / $nb_days_total;
            $av_shipping_slip_vat = $total_shipping_slip_vat / $nb_days_total;
            $av_product_slip_tax_excl = $total_product_slip_tax_excl / $nb_days_total;
            $av_product_slip_vat = $total_product_slip_vat / $nb_days_total;
            $av_discounts_tax_excl = $total_discounts_tax_excl / $nb_days_total;
            $av_discounts_vat = $total_discounts_tax_excl / $nb_days_total;
            $av_cost = $total_cost / $nb_days_total;
            $av_margin = $total_margin / $nb_days_total;
            $av_sales = $total_sales / $nb_days_total;
            $av_average_cart = $total_average_cart / $nb_days_total;
        }

        $av_margin_per = 0;

        if ($av_sales > 0) {
            $av_margin_per = ($av_margin / $av_sales) * 100;
        } else {
            $av_margin_per = 0;
        }

        $list[] = [
            'date_valid' => $this->l('Average'),
            'nb_orders' => round($av_nb_order, 2),
            'total_products_tax_excl' => (($for_export) ? round($av_products_tax_excl, 2) : self::displayPrice($av_products_tax_excl)),
            'total_products_vat' => (($for_export) ? round($av_products_vat, 2) : self::displayPrice($av_products_vat)),
            'total_shipping_tax_excl' => (($for_export) ? round($av_shipping_tax_excl, 2) : self::displayPrice($av_shipping_tax_excl)),
            'total_shipping_vat' => (($for_export) ? round($av_shipping_vat, 2) : self::displayPrice($av_shipping_vat)),
            'total_shipping_slip_tax_excl' => (($for_export) ? round($av_shipping_slip_tax_excl, 2) : self::displayPrice($av_shipping_slip_tax_excl)),
            'total_shipping_slip_vat' => (($for_export) ? round($av_shipping_slip_vat, 2) : self::displayPrice($av_shipping_slip_vat)),
            'total_product_slip_tax_excl' => (($for_export) ? round($av_product_slip_tax_excl, 2) : self::displayPrice($av_product_slip_tax_excl)),
            'total_product_slip_vat' => (($for_export) ? round($av_product_slip_vat, 2) : self::displayPrice($av_product_slip_vat)),
            'total_discounts_tax_excl' => (($for_export) ? round($av_discounts_tax_excl, 2) : self::displayPrice($av_discounts_tax_excl)),
            'total_discounts_vat' => (($for_export) ? round($av_discounts_vat, 2) : self::displayPrice($av_discounts_vat)),
            'cost' => (($for_export) ? round($av_cost, 2) : self::displayPrice($av_cost)),
            'margin' => (($for_export) ? round($av_margin, 2) : self::displayPrice($av_margin)),
            'margin_per' => round($av_margin_per, 2),
            'sales' => (($for_export) ? round($av_sales, 2) : self::displayPrice($av_sales)),
            'average_cart' => (($for_export) ? round($av_average_cart, 2) : self::displayPrice($av_average_cart)),
        ];

        return $list;
    }

    public static function getCompareTotalSales($from, $to, $id_group, $for_export = false)
    {
        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $p_req_loc_valid_o = self::reqLocationValid('o');
        $p_req_date_valid_o = self::reqDateValid('o');
        $p_where_shop_o = self::whereShop('o');
        $p_where_profile_country_a = self::getWhereProfileCountrie('a');
        $p_id_group = self::protectIntArraySQL($id_group);

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_cmp_total_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_cmp_total_sales`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_cmp_total_sales` (
                `id_order_detail`   int(10)         UNSIGNED    NOT NULL,
                `id_order`          int(10)         UNSIGNED    NOT NULL,
                `cost`              decimal(20,6)               NOT NULL,
                PRIMARY KEY (`id_order_detail`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_product_cmp_total_sales` (
                `product_id`        int(10)         UNSIGNED    NOT NULL,
                `cost`              decimal(20,6)               NOT NULL,
                PRIMARY KEY (`product_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_product_cmp_total_sales` (`product_id`, `cost`)
            SELECT t.`product_id`, t.`cost`
            FROM (
                    SELECT od.`product_id`, (
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN od.`purchase_supplier_price`
                        ELSE od.`original_wholesale_price`
                        END
                    ) AS cost
                    FROM `' . _DB_PREFIX_ . 'order_detail` od
                    JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
                    JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $p_req_loc_valid_o . '
                    WHERE ' . $p_req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                    AND o.`valid` = 1
                    ' . $p_where_shop_o . '
                    ' . $p_where_profile_country_a . '
                    ' . (($id_group) ? ' AND o.`id_customer` IN(
                        SELECT cg.`id_customer`
                        FROM `' . _DB_PREFIX_ . 'customer_group` cg
                        WHERE cg.`id_group` IN (' . $p_id_group . ')
                        )
                    ' : '') . '
                    HAVING cost > 0
                    ORDER BY o.`date_add` ASC
            ) t
            GROUP BY t.`product_id`
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_cmp_total_sales` (`id_order_detail`, `id_order`, `cost`)
            SELECT od.`id_order_detail`, od.`id_order`,
            (
                IFNULL(
                    (
                        SELECT
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN
                            CASE od.`purchase_supplier_price`
                            WHEN 0
                            THEN (
                                SELECT IFNULL(cpts.`cost`, 0)
                                FROM `nts_cost_product_cmp_total_sales` cpts
                                WHERE od.`product_id` = cpts.`product_id`
                            )
                            ELSE od.`purchase_supplier_price`
                            END
                        ELSE od.`original_wholesale_price`
                        END
                    ), 0
                ) * IFNULL(od.`product_quantity`, 0)
            )
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $p_req_loc_valid_o . '
            WHERE ' . $p_req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            AND o.`valid` = 1
            ' . $p_where_shop_o . '
            ' . $p_where_profile_country_a . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
        ');

        $data = Db::getInstance()->getRow('
            SELECT SUM(nb_order) AS nb_order, SUM(total_products_tax_excl) AS total_products_tax_excl,
                SUM(total_shipping_tax_excl) AS total_shipping_tax_excl,
                SUM(total_discounts_tax_excl) AS total_discounts_tax_excl,
                SUM(total_shipping_slip_tax_excl) AS total_shipping_slip_tax_excl,
                SUM(total_product_slip_tax_excl) AS total_product_slip_tax_excl, SUM(cost) AS cost
            FROM (
                SELECT COUNT(o.`id_order`) AS nb_order,
                    IFNULL(SUM(IFNULL(o.`total_products`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_products_tax_excl,
                    IFNULL(SUM(IFNULL(o.`total_shipping_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_shipping_tax_excl,
                    IFNULL(SUM(IFNULL(o.`total_discounts_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_discounts_tax_excl,
                    0 AS total_shipping_slip_tax_excl,
                    0 AS total_product_slip_tax_excl,
                    (
                        SELECT IFNULL(SUM(nt4.`cost`), 0)
                        FROM `nts_cost_cmp_total_sales` nt4
                        JOIN `' . _DB_PREFIX_ . 'orders` o4 ON nt4.`id_order` = o4.`id_order`
                    ) AS cost
                FROM `' . _DB_PREFIX_ . 'orders` o
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $p_req_loc_valid_o . '
                WHERE ' . $p_req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                AND o.`valid` = 1
                ' . $p_where_shop_o . '
                ' . $p_where_profile_country_a . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '

                UNION

                SELECT 0 AS nb_order,
                    0 AS total_products_tax_excl,
                    0 AS total_shipping_tax_excl,
                    0 AS total_discounts_tax_excl,
                    IFNULL(SUM(IFNULL(os.`total_shipping_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_shipping_slip_tax_excl,
                    IFNULL(SUM(IFNULL(os.`total_products_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_product_slip_tax_excl,
                    0 AS cost
                FROM `' . _DB_PREFIX_ . 'order_slip` os
                JOIN `' . _DB_PREFIX_ . 'orders` o ON os.`id_order` = o.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $p_req_loc_valid_o . '
                WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $p_where_shop_o . '
                ' . $p_where_profile_country_a . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
            ) t
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_cmp_total_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_cmp_total_sales`;
        ');

        if ($data['total_products_tax_excl'] > 0) {
            $average_cart = ($data['total_products_tax_excl'] - $data['total_discounts_tax_excl']) / (($data['nb_order'] > 0) ? $data['nb_order'] : 1);
        } else {
            $average_cart = 0;
        }

        $sales = $data['total_products_tax_excl'] - $data['total_discounts_tax_excl'] - $data['total_product_slip_tax_excl'];
        $margin = $sales - $data['cost'];

        $list = [
            'from' => $from,
            'to' => $to,
            'nb_order' => $data['nb_order'],
            'total_products_tax_excl' => (($for_export) ? round($data['total_products_tax_excl'], 2) : self::displayPrice($data['total_products_tax_excl'])),
            'total_shipping_tax_excl' => (($for_export) ? round($data['total_shipping_tax_excl'], 2) : self::displayPrice($data['total_shipping_tax_excl'])),
            'total_shipping_slip_tax_excl' => (($for_export) ? round($data['total_shipping_slip_tax_excl'], 2) : self::displayPrice($data['total_shipping_slip_tax_excl'])),
            'total_product_slip_tax_excl' => (($for_export) ? round($data['total_product_slip_tax_excl'], 2) : self::displayPrice($data['total_product_slip_tax_excl'])),
            'total_discounts_tax_excl' => (($for_export) ? round($data['total_discounts_tax_excl'], 2) : self::displayPrice($data['total_discounts_tax_excl'])),
            'cost' => (($for_export) ? round($data['cost'], 2) : self::displayPrice($data['cost'])),
            'margin' => (($for_export) ? round($margin, 2) : self::displayPrice($margin)),
            'sales' => (($for_export) ? round($sales, 2) : self::displayPrice($sales)),
            'average_cart' => (($for_export) ? round($average_cart, 2) : self::displayPrice($average_cart)),
        ];

        return $list;
    }

    public function getTotalCategoriesSales($from, $to, $id_category, $id_group, $for_export = false)
    {
        $list = [];

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $categories = [];

        if ($id_category) {
            foreach ($id_category as $id_cat) {
                $o_category = new Category($id_cat);
                $children = $o_category->getAllChildren();
                $categories[] = $id_cat;

                foreach ($children as $child) {
                    $categories[] = (int) $child->id;
                }
            }
        }

        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $req_loc_valid_o = self::reqLocationValid('o');
        $req_date_valid_o = self::reqDateValid('o');
        $where_shop_o = self::whereShop('o');
        $where_profile_country_a = self::getWhereProfileCountrie('a');
        $p_id_categories = self::protectIntArraySQL($categories);
        $p_id_group = self::protectIntArraySQL($id_group);
        $req_date_valid_o5 = self::reqDateValid('o5');
        $req_date_valid_o6 = self::reqDateValid('o6');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_total_categories_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_total_categories_sales`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_total_categories_sales` (
                `id_order_detail`   int(10)         UNSIGNED    NOT NULL,
                `cost`              decimal(20,6)               NOT NULL,
                PRIMARY KEY (`id_order_detail`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_product_total_categories_sales` (
                `product_id`        int(10)         UNSIGNED    NOT NULL,
                `cost`              decimal(20,6)               NOT NULL,
                PRIMARY KEY (`product_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_product_total_categories_sales` (`product_id`, `cost`)
            SELECT t.`product_id`, t.`cost`
            FROM (
                    SELECT od.`product_id`, (
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN od.`purchase_supplier_price`
                        ELSE od.`original_wholesale_price`
                        END
                    ) AS cost
                    FROM `' . _DB_PREFIX_ . 'order_detail` od
                    JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
                    JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                    WHERE ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                    AND o.`valid` = 1
                    ' . $where_shop_o . '
                    ' . $where_profile_country_a . '
                    ' . (($id_category) ? ' AND od.`product_id` IN(
                        SELECT cp2.`id_product`
                        FROM `' . _DB_PREFIX_ . 'category_product` cp2
                        WHERE cp2.`id_category` IN (' . $p_id_categories . ')
                        )
                    ' : '') . '
                    ' . (($id_group) ? ' AND o.`id_customer` IN(
                        SELECT cg.`id_customer`
                        FROM `' . _DB_PREFIX_ . 'customer_group` cg
                        WHERE cg.`id_group` IN (' . $p_id_group . ')
                        )
                    ' : '') . '
                    HAVING cost > 0
                    ORDER BY o.`date_add` ASC
            ) t
            GROUP BY t.`product_id`
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_total_categories_sales` (`id_order_detail`, `cost`)
            SELECT od.`id_order_detail`,
                (
                    IFNULL(
                        (
                            SELECT
                            CASE od.`original_wholesale_price`
                            WHEN 0
                            THEN
                                CASE od.`purchase_supplier_price`
                                WHEN 0
                                THEN (
                                    SELECT cpts.`cost`
                                    FROM `nts_cost_product_total_categories_sales` cpts
                                    WHERE od.`product_id` = cpts.`product_id`
                                )
                                ELSE od.`purchase_supplier_price`
                                END
                            ELSE od.`original_wholesale_price`
                            END
                        ), 0
                    ) * IFNULL(od.`product_quantity`, 0)
                )
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            WHERE ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59" /* ex: Y-m = 2021-11 but from = 2021-11-15 we do not want all of 2021-11 */
            AND o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            ' . (($id_category) ? ' AND od.`product_id` IN(
                SELECT cp.`id_product`
                FROM `' . _DB_PREFIX_ . 'category_product` cp
                WHERE cp.`id_category` IN (' . $p_id_categories . ')
                )
            ' : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
        ');

        $data = Db::getInstance()->executeS('
            SELECT month, SUM(product_quantity) AS product_quantity, SUM(total_price_tax_excl) AS total_price_tax_excl,
                SUM(cost) AS cost, SUM(discount_tax_excl) AS discount_tax_excl, SUM(quantity_return) AS quantity_return,
                SUM(total_refund_tax_excl) AS total_refund_tax_excl
            FROM (
                SELECT SUBSTR(' . $req_date_valid_o . ', 1, 7) AS month,
                    IFNULL(SUM(IFNULL(od.`product_quantity`, 0)), 0) AS product_quantity,
                    IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_price_tax_excl,
                    SUM(nt.`cost`) AS cost,
                    (
                        SELECT SUM(IFNULL(o5.`total_discounts_tax_excl`, 0) / IFNULL(o5.`conversion_rate`, 1))
                        FROM `' . _DB_PREFIX_ . 'orders` o5
                        JOIN `' . _DB_PREFIX_ . 'address` a5 ON a5.`id_address` = ' . self::reqLocationValid('o5') . '
                        WHERE SUBSTR(' . $req_date_valid_o5 . ', 1, 7) = month
                        AND ' . $req_date_valid_o5 . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59" /* ex: Y-m = 2021-11 but from = 2021-11-15 we do not want all of 2021-11 */
                        AND o5.`valid` = 1
                        ' . self::whereShop('o5') . '
                        ' . self::getWhereProfileCountrie('a5') . '
                        ' . (($id_category) ? ' AND o5.`id_order` IN(
                            SELECT o6.`id_order`
                            FROM `' . _DB_PREFIX_ . 'orders` o6
                            JOIN `' . _DB_PREFIX_ . 'address` a6 ON a6.`id_address` = ' . self::reqLocationValid('o6') . '
                            JOIN `' . _DB_PREFIX_ . 'order_detail` od6 ON od6.`id_order` = o6.`id_order`
                            JOIN `' . _DB_PREFIX_ . 'category_product` cp6 ON od6.`product_id` = cp6.`id_product`
                            WHERE SUBSTR(' . $req_date_valid_o6 . ', 1, 7) = month
                            AND ' . $req_date_valid_o6 . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59" /* ex: Y-m = 2021-11 but from = 2021-11-15 we do not want all of 2021-11 */
                            AND o6.`valid` = 1
                            ' . self::whereShop('o6') . '
                            ' . self::getWhereProfileCountrie('a6') . '
                            AND cp6.`id_category` IN (' . $p_id_categories . ')
                            )
                        ' : '') . '
                        ' . (($id_group) ? ' AND o5.`id_customer` IN(
                            SELECT cg5.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg5
                            WHERE cg5.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                    ) AS discount_tax_excl, 0 AS quantity_return, 0 AS total_refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'orders` o
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON od.`id_order` = o.`id_order`
                JOIN `nts_cost_total_categories_sales` nt ON nt.`id_order_detail` = od.`id_order_detail`
                WHERE ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                AND o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_id_categories . ')
                    )
                ' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY month

                UNION

                SELECT SUBSTR(ore.`date_add`, 1, 7) AS month, 0 AS product_quantity, 0 AS total_price_tax_excl, 0 AS cost,
                    0 AS discount_tax_excl, IFNULL(SUM(IFNULL(ord.`product_quantity`, 0)), 0) AS quantity_return,
                    0 AS total_refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_return_detail` ord
                JOIN `' . _DB_PREFIX_ . 'order_return` ore ON ord.`id_order_return` = ore.`id_order_return`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON od.`id_order_detail` = ord.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = ore.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE ore.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . self::reqReturnValid('ore') . '
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_id_categories . ')
                    )
                ' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY month

                UNION

                SELECT SUBSTR(os.`date_add`, 1, 7) AS month, 0 AS product_quantity, 0 AS total_price_tax_excl, 0 AS cost,
                    0 AS discount_tax_excl, 0 AS quantity_return,
                    IFNULL(SUM(IFNULL(osd.`total_price_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_slip_detail` osd
                JOIN `' . _DB_PREFIX_ . 'order_slip` os ON os.`id_order_slip` = osd.`id_order_slip`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON od.`id_order_detail` = osd.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = os.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_id_categories . ')
                    )
                ' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY month

                ORDER BY month DESC
            ) t
            GROUP BY month
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_total_categories_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_total_categories_sales`;
        ');

        $total_product_quantity = 0;
        $total_price_tax_excl = 0;
        $total_cost = 0;
        $total_margin = 0;
        $total_discount_tax_excl = 0;
        $total_quantity_return = 0;
        $total_refund_tax_excl = 0;
        $total_quantity_return_per = 0;
        $total_refund_tax_excl_per = 0;

        foreach ($data as $dt) {
            if ($dt['product_quantity'] > 0) {
                $dt['quantity_return_per'] = $dt['quantity_return'] / $dt['product_quantity'] * 100;
            } else {
                $dt['quantity_return_per'] = 0;
            }

            if ($dt['total_price_tax_excl'] > 0) {
                $dt['total_refund_tax_excl_per'] = $dt['total_refund_tax_excl'] / $dt['total_price_tax_excl'] * 100;
            } else {
                $dt['total_refund_tax_excl_per'] = 0;
            }

            if ($dt['cost'] > 0) {
                $total_price_product = $dt['total_price_tax_excl'] - $dt['total_refund_tax_excl'];
                $dt['margin'] = $total_price_product - $dt['discount_tax_excl'] - $dt['cost'];
            } else {
                $dt['margin'] = 0;
            }

            $total_product_quantity += $dt['product_quantity'];
            $total_price_tax_excl += $dt['total_price_tax_excl'];
            $total_cost += $dt['cost'];
            $total_margin += $dt['margin'];
            $total_discount_tax_excl += $dt['discount_tax_excl'];
            $total_quantity_return += $dt['quantity_return'];
            $total_refund_tax_excl += $dt['total_refund_tax_excl'];

            $list[] = [
                'month' => $dt['month'],
                'product_quantity' => $dt['product_quantity'],
                'total_price_tax_excl' => (($for_export) ? round($dt['total_price_tax_excl'], 2) : self::displayPrice($dt['total_price_tax_excl'])),
                'cost' => (($for_export) ? round($dt['cost'], 2) : self::displayPrice($dt['cost'])),
                'discount_tax_excl' => (($for_export) ? round($dt['discount_tax_excl'], 2) : self::displayPrice($dt['discount_tax_excl'])),
                'quantity_return' => $dt['quantity_return'],
                'total_refund_tax_excl' => (($for_export) ? round($dt['total_refund_tax_excl'], 2) : self::displayPrice($dt['total_refund_tax_excl'])),
                'margin' => (($for_export) ? round($dt['margin'], 2) : self::displayPrice($dt['margin'])),
                'quantity_return_per' => round($dt['quantity_return_per'], 2),
                'total_refund_tax_excl_per' => round($dt['total_refund_tax_excl_per'], 2),
            ];
        }

        if ($total_product_quantity > 0) {
            $total_quantity_return_per = $total_quantity_return / $total_product_quantity * 100;
        }

        if ($total_price_tax_excl > 0) {
            $total_refund_tax_excl_per = $total_refund_tax_excl / $total_price_tax_excl * 100;
        }

        $list[] = [
            'month' => $this->l('Total'),
            'product_quantity' => $total_product_quantity,
            'total_price_tax_excl' => (($for_export) ? round($total_price_tax_excl, 2) : self::displayPrice($total_price_tax_excl)),
            'cost' => (($for_export) ? round($total_cost, 2) : self::displayPrice($total_cost)),
            'discount_tax_excl' => (($for_export) ? round($total_discount_tax_excl, 2) : self::displayPrice($total_discount_tax_excl)),
            'quantity_return' => $total_quantity_return,
            'total_refund_tax_excl' => (($for_export) ? round($total_refund_tax_excl, 2) : self::displayPrice($total_refund_tax_excl)),
            'margin' => (($for_export) ? round($total_margin, 2) : self::displayPrice($total_margin)),
            'quantity_return_per' => round($total_quantity_return_per, 2),
            'total_refund_tax_excl_per' => round($total_refund_tax_excl_per, 2),
        ];

        return $list;
    }

    public static function getCompareTotalCategoriesSales($from, $to, $id_category, $id_group, $for_export = false)
    {
        $categories = [];

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        if ($id_category) {
            foreach ($id_category as $id_cat) {
                $o_category = new Category($id_cat);
                $children = $o_category->getAllChildren();
                $categories[] = $id_cat;

                foreach ($children as $child) {
                    $categories[] = (int) $child->id;
                }
            }
        }

        $req_loc_valid_o = self::reqLocationValid('o');
        $where_shop_o = self::whereShop('o');
        $where_profile_country = self::getWhereProfileCountrie('a');
        $req_date_valid = self::reqDateValid('o');
        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $p_id_group = self::protectIntArraySQL($id_group);
        $p_id_categorie = self::protectIntArraySQL($categories);

        Db::getInstance()->execute('
            DROP VIEW IF EXISTS discount_prorata_cmp_categories_sales
        ');

        Db::getInstance()->execute('
            CREATE OR REPLACE VIEW discount_prorata_cmp_categories_sales AS
            SELECT product_id, id_order, id_order_detail,
                    SUM(total_discounts_tax_excl) AS total_discounts_tax_excl,
                    SUM(total_products) AS total_products,
                    SUM(total_price_tax_excl) AS total_price_tax_excl,
                    SUM(total_refund_tax_excl) AS total_refund_tax_excl,
                    SUM(order_total_refund_tax_excl) AS order_total_refund_tax_excl
            FROM(
                SELECT od.`product_id`, od.`id_order`, od.`id_order_detail`,
                    (IFNULL(o.`total_discounts_tax_excl`, 0) / IFNULL(o.`conversion_rate`, 1)) AS total_discounts_tax_excl,
                    (IFNULL(o.`total_products`, 0) / IFNULL(o.`conversion_rate`, 1)) AS total_products,
                    IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_price_tax_excl,
                    0 AS total_refund_tax_excl,
                    0 AS order_total_refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country . '
                AND ' . $req_date_valid . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_id_categorie . ')
                    )
                ' : '') . '
                GROUP BY od.`id_order`, od.`id_order_detail`

                UNION

                SELECT od.`product_id`, od.`id_order`, od.`id_order_detail`,
                    0 AS total_discounts_tax_excl,
                    0 AS total_products,
                    0 AS total_price_tax_excl,
                    IFNULL(SUM(IFNULL(osd.`total_price_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_refund_tax_excl,
                    IFNULL(IFNULL(os.`total_products_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1), 0) AS order_total_refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_slip_detail` osd
                JOIN `' . _DB_PREFIX_ . 'order_slip` os ON osd.`id_order_slip` = os.`id_order_slip`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON osd.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_id_categorie . ')
                    )
                ' : '') . '
                GROUP BY od.`id_order`, od.`id_order_detail`
            )t
            GROUP BY id_order, id_order_detail
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_cmp_categories_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_cmp_categories_sales`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_cmp_categories_sales` (
                `id_order_detail`   int(10)         UNSIGNED    NOT NULL,
                `cost`              decimal(20,6)               NOT NULL,
                PRIMARY KEY (`id_order_detail`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_product_cmp_categories_sales` (
                `product_id`    int(10)         UNSIGNED    NOT NULL,
                `cost`          decimal(20,6)               NOT NULL,
                PRIMARY KEY (`product_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_product_cmp_categories_sales` (`product_id`, `cost`)
            SELECT t.`product_id`, t.`cost`
            FROM (
                    SELECT od.`product_id`, (
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN od.`purchase_supplier_price`
                        ELSE od.`original_wholesale_price`
                        END
                    ) AS cost
                    FROM `' . _DB_PREFIX_ . 'order_detail` od
                    JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
                    JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                    WHERE ' . $req_date_valid . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                    AND o.`valid` = 1
                    ' . $where_shop_o . '
                    ' . $where_profile_country . '
                    ' . (($id_category) ? ' AND od.`product_id` IN(
                        SELECT cp.`id_product`
                        FROM `' . _DB_PREFIX_ . 'category_product` cp
                        WHERE cp.`id_category` IN (' . $p_id_categorie . ')
                        )
                    ' : '') . '
                    ' . (($id_group) ? ' AND o.`id_customer` IN(
                        SELECT cg.`id_customer`
                        FROM `' . _DB_PREFIX_ . 'customer_group` cg
                        WHERE cg.`id_group` IN (' . $p_id_group . ')
                        )
                    ' : '') . '
                    HAVING cost > 0
                    ORDER BY o.`date_add` ASC
            ) t
            GROUP BY t.`product_id`
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_cmp_categories_sales` (`id_order_detail`, `cost`)
            SELECT od.`id_order_detail`,
            (
                IFNULL(
                    (
                        SELECT
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN
                            CASE od.`purchase_supplier_price`
                            WHEN 0
                            THEN (
                                SELECT IFNULL(cpts.`cost`, 0)
                                FROM `nts_cost_product_cmp_categories_sales` cpts
                                WHERE od.`product_id` = cpts.`product_id`
                            )
                            ELSE od.`purchase_supplier_price`
                            END
                        ELSE od.`original_wholesale_price`
                        END
                    ), 0
                ) * IFNULL(od.`product_quantity`, 0)
            )
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            WHERE ' . $req_date_valid . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            AND o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country . '
            ' . (($id_category) ? ' AND od.`product_id` IN(
                SELECT cp.`id_product`
                FROM `' . _DB_PREFIX_ . 'category_product` cp
                WHERE cp.`id_category` IN (' . $p_id_categorie . ')
                )
            ' : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
        ');

        $data = Db::getInstance()->getRow('
            SELECT SUM(product_quantity) AS product_quantity, SUM(total_price_tax_excl) AS total_price_tax_excl,
                SUM(quantity_return) AS quantity_return, SUM(total_refund_tax_excl) AS total_refund_tax_excl,
                SUM(cost) AS cost, SUM(discount_prorata) AS discount_prorata
            FROM (
                SELECT
                    IFNULL(SUM(IFNULL(od.`product_quantity`, 0)), 0) AS product_quantity,
                    IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_price_tax_excl,
                    0 AS quantity_return,
                    0 AS total_refund_tax_excl,
                    IFNULL(SUM(nt.`cost`), 0) AS cost,
                    IFNULL(
                        (SELECT SUM((dp5.`total_discounts_tax_excl` * (dp5.`total_price_tax_excl` - dp5.`total_refund_tax_excl`)) /(dp5.`total_products` - dp5.`order_total_refund_tax_excl`))
                        FROM `discount_prorata_cmp_categories_sales` dp5
                        JOIN `' . _DB_PREFIX_ . 'orders` o5 ON dp5.`id_order` = o5.`id_order`
                        JOIN `' . _DB_PREFIX_ . 'address` a5 ON a5.`id_address` = ' . self::reqLocationValid('o5') . '
                        WHERE ' . self::reqDateValid('o5') . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        AND o5.`valid` = 1
                        ' . self::whereShop('o5') . '
                        ' . self::getWhereProfileCountrie('a5') . '
                        ' . (($id_category) ? ' AND dp5.`product_id` IN(
                            SELECT cp5.`id_product`
                            FROM `' . _DB_PREFIX_ . 'category_product` cp5
                            WHERE cp5.`id_category` IN (' . $p_id_categorie . ')
                            )
                        ' : '') . '
                        ' . (($id_group) ? ' AND o5.`id_customer` IN(
                            SELECT cg5.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg5
                            WHERE cg5.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                    ), 0) AS discount_prorata
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                JOIN `nts_cost_cmp_categories_sales` nt ON od.`id_order_detail` = nt.`id_order_detail`
                WHERE ' . $req_date_valid . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                AND o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_id_categorie . ')
                    )
                ' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '

                UNION

                SELECT
                    0 AS product_quantity,
                    0 AS total_price_tax_excl,
                    IFNULL(SUM(IFNULL(ord.`product_quantity`, 0)), 0) AS quantity_return,
                    0 AS total_refund_tax_excl,
                    0 AS cost,
                    0 AS discount_prorata
                FROM `' . _DB_PREFIX_ . 'order_return_detail` ord
                JOIN `' . _DB_PREFIX_ . 'order_return` ore ON ord.`id_order_return` = ore.`id_order_return`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON ord.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE ore.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . self::reqReturnValid('ore') . '
                ' . $where_shop_o . '
                ' . $where_profile_country . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_id_categorie . ')
                    )
                ' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '

                UNION

                SELECT
                    0 AS product_quantity,
                    0 AS total_price_tax_excl,
                    0 AS quantity_return,
                    IFNULL(SUM(IFNULL(osd.`total_price_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_refund_tax_excl,
                    0 AS cost,
                    0 AS discount_prorata
                FROM `' . _DB_PREFIX_ . 'order_slip_detail` osd
                JOIN `' . _DB_PREFIX_ . 'order_slip` os ON osd.`id_order_slip` = os.`id_order_slip`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON osd.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_id_categorie . ')
                    )
                ' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
            ) t
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_cmp_categories_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_cmp_categories_sales`;
        ');

        Db::getInstance()->execute('
            DROP VIEW IF EXISTS discount_prorata_cmp_categories_sales
        ');

        if ($data['product_quantity'] > 0) {
            $data['quantity_return_per'] = $data['quantity_return'] / $data['product_quantity'] * 100;
        } else {
            $data['quantity_return_per'] = 0;
        }

        if ($data['total_price_tax_excl'] > 0) {
            $data['total_refund_tax_excl_per'] = $data['total_refund_tax_excl'] / $data['total_price_tax_excl'] * 100;
        } else {
            $data['total_refund_tax_excl_per'] = 0;
        }

        $margin = $data['total_price_tax_excl'] - $data['total_refund_tax_excl'] - $data['discount_prorata'] - $data['cost'];

        $list = [
            'from' => $from,
            'to' => $to,
            'product_quantity' => $data['product_quantity'],
            'total_price_tax_excl' => (($for_export) ? round($data['total_price_tax_excl'], 2) : self::displayPrice($data['total_price_tax_excl'])),
            'cost' => (($for_export) ? round($data['cost'], 2) : self::displayPrice($data['cost'])),
            'discount_prorata' => (($for_export) ? round($data['discount_prorata'], 2) : self::displayPrice($data['discount_prorata'])),
            'quantity_return' => $data['quantity_return'],
            'total_refund_tax_excl' => (($for_export) ? round($data['total_refund_tax_excl'], 2) : self::displayPrice($data['total_refund_tax_excl'])),
            'margin' => (($for_export) ? round($margin, 2) : self::displayPrice($margin)),
            'quantity_return_per' => round($data['quantity_return_per'], 2),
            'total_refund_tax_excl_per' => round($data['total_refund_tax_excl_per'], 2),
        ];

        return $list;
    }

    public function getTotalProductsSales(
        $from,
        $to,
        $id_category,
        $id_manufacturer,
        $id_country_invoice,
        $id_product,
        $id_group,
        $id_feature,
        $id_feature_value,
        $product_simple,
        $for_export = false
    ) {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;
        $a_config = self::getConfig();

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $categories = [];

        if ($id_category) {
            foreach ($id_category as $id_cat) {
                $o_category = new Category($id_cat);
                $children = $o_category->getAllChildren();
                $categories[] = $id_cat;

                foreach ($children as $child) {
                    $categories[] = (int) $child->id;
                }
            }
        }

        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $p_id_product = self::protectIntArraySQL($id_product);
        $p_id_group = self::protectIntArraySQL($id_group);
        $p_id_category = self::protectIntArraySQL($categories);
        $p_id_manufacturer = self::protectIntArraySQL($id_manufacturer);
        $p_id_country_invoice = self::protectIntArraySQL($id_country_invoice);
        $p_id_feature = self::protectIntArraySQL($id_feature);
        $p_id_feature_value = self::protectIntArraySQL($id_feature_value);
        $req_loc_valid_o = self::reqLocationValid('o');
        $req_loc_valid_o2 = self::reqLocationValid('o2');
        $where_shop_o = self::whereShop('o');
        $where_shop_o2 = self::whereShop('o2');
        $where_shop_p1 = self::whereShop('pl', false);
        $where_profile_country_a = self::getWhereProfileCountrie('a');
        $where_profile_country_a2 = self::getWhereProfileCountrie('a2');
        $req_loc_valid_o3 = self::reqLocationValid('o3');
        $req_date_valid_o = self::reqDateValid('o');
        $req_date_valid_o2 = self::reqDateValid('o2');

        $req_total = '
            SELECT IFNULL(SUM(IFNULL(od.`product_quantity`, 0)), 0) AS product_quantity,
                IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_price_tax_excl,
                IFNULL(
                    (
                        SELECT COUNT(DISTINCT o2.`id_customer`)
                        FROM `' . _DB_PREFIX_ . 'orders` o2
                        JOIN `' . _DB_PREFIX_ . 'order_detail` od2 ON o2.`id_order` = od2.`id_order`
                        JOIN `' . _DB_PREFIX_ . 'address` a2 ON a2.`id_address` = ' . $req_loc_valid_o2 . '
                        WHERE o2.`valid` = 1
                        ' . $where_shop_o2 . '
                        ' . $where_profile_country_a2 . '
                        AND ' . $req_date_valid_o2 . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_product) ? ' AND od2.`product_id` IN (' . $p_id_product . ')' : '') . '
                        ' . (($id_group) ? ' AND o2.`id_customer` IN(
                            SELECT cg2.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg2
                            WHERE cg2.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                        ' . (($id_category) ? ' AND od2.`product_id` IN(
                            SELECT cp2.`id_product`
                            FROM `' . _DB_PREFIX_ . 'category_product` cp2
                            WHERE cp2.`id_category` IN (' . $p_id_category . ')
                            )
                        ' : '') . '
                        ' . (($id_manufacturer) ? ' AND od2.`product_id` IN(
                            SELECT p2.`id_product`
                            FROM `' . _DB_PREFIX_ . 'product` p2
                            WHERE p2.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                            )
                        ' : '') . '
                        ' . (($id_country_invoice) ? ' AND od2.`id_order` IN(
                            SELECT o3.`id_order`
                            FROM `' . _DB_PREFIX_ . 'orders` o3
                            JOIN `' . _DB_PREFIX_ . 'address` a3 ON a3.`id_address` = ' . $req_loc_valid_o3 . '
                            WHERE a3.`id_country` IN (' . $p_id_country_invoice . ')
                            )
                        ' : '') . '
                        ' . (($id_feature) ? ' AND od2.`product_id` IN(
                            SELECT fp2.`id_product`
                            FROM `' . _DB_PREFIX_ . 'feature_product` fp2
                            WHERE fp2.`id_feature` IN (' . $p_id_feature . ')
                            )
                        ' : '') . '
                        ' . (($id_feature_value) ? ' AND od2.`product_id` IN(
                            SELECT fp3.`id_product`
                            FROM `' . _DB_PREFIX_ . 'feature_product` fp3
                            WHERE fp3.`id_feature_value` IN (' . $p_id_feature_value . ')
                            )
                        ' : '') . '
                    )
                , 0) AS nb_customer
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.`id_product` = od.`product_id`
                AND pl.`id_lang` = ' . $id_lang . ' ' . $where_shop_p1 . '
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            ' . (($id_category) ? ' AND od.`product_id` IN(
                SELECT cp.`id_product`
                FROM `' . _DB_PREFIX_ . 'category_product` cp
                WHERE cp.`id_category` IN (' . $p_id_category . ')
                )
            ' : '') . '
            ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                SELECT p3.`id_product`
                FROM `' . _DB_PREFIX_ . 'product` p3
                WHERE p3.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                )
            ' : '') . '
            ' . (($id_country_invoice) ? ' AND od.`id_order` IN(
                SELECT o3.`id_order`
                FROM `' . _DB_PREFIX_ . 'orders` o3
                JOIN `' . _DB_PREFIX_ . 'address` a3 ON a3.`id_address` = ' . $req_loc_valid_o3 . '
                WHERE a3.`id_country` IN (' . $p_id_country_invoice . ')
                )
            ' : '') . '
            ' . (($id_feature) ? ' AND od.`product_id` IN(
                SELECT fp.`id_product`
                FROM `' . _DB_PREFIX_ . 'feature_product` fp
                WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                )
            ' : '') . '
            ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                SELECT fp1.`id_product`
                FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                )
            ' : '') . '
        ';

        $total_product = Db::getInstance()->getRow($req_total);

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_total_products_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_total_products_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_nb_customer_total_products_sales`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_total_products_sales` (
                `id_order_detail`   int(10)         UNSIGNED    NOT NULL,
                `cost`              decimal(20,6)               NOT NULL,
                `reference`         TEXT,
                PRIMARY KEY (`id_order_detail`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_product_total_products_sales` (
                `product_id`    int(10)         UNSIGNED    NOT NULL,
                `cost`          decimal(20,6)               NOT NULL,
                PRIMARY KEY (`product_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_nb_customer_total_products_sales` (
                `product_id`   int(10)         UNSIGNED    NOT NULL,
                `nb_customer`   int(10)         UNSIGNED    NOT NULL,
                PRIMARY KEY (`product_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_product_total_products_sales` (`product_id`, `cost`)
            SELECT t.`product_id`, t.`cost`
            FROM (
                    SELECT od.`product_id`, (
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN od.`purchase_supplier_price`
                        ELSE od.`original_wholesale_price`
                        END
                    ) AS cost
                    FROM `' . _DB_PREFIX_ . 'order_detail` od
                    JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
                    JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                    WHERE ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                    AND o.`valid` = 1
                    ' . $where_shop_o . '
                    ' . $where_profile_country_a . '
                    ' . (($id_group) ? ' AND o.`id_customer` IN(
                        SELECT cg.`id_customer`
                        FROM `' . _DB_PREFIX_ . 'customer_group` cg
                        WHERE cg.`id_group` IN (' . $p_id_group . ')
                        )
                    ' : '') . '
                    HAVING cost > 0
                    ORDER BY o.`date_add` ASC
            ) t
            GROUP BY t.`product_id`
        ');

        $referense_select = 'od.`product_reference`';
        $reference_join = '';

        if ($a_config['group_product_reference']) {
            $referense_select = 'IFNULL(p.`reference`, od.`product_reference`)';
            $reference_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`';
        }

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_total_products_sales` (`id_order_detail`, `cost`, `reference`)
            SELECT od.`id_order_detail`,
                (
                    IFNULL(
                        (
                            SELECT
                            CASE od.`original_wholesale_price`
                            WHEN 0
                            THEN
                                CASE od.`purchase_supplier_price`
                                WHEN 0
                                THEN (
                                    SELECT IFNULL(cpts.`cost`, 0)
                                    FROM `nts_cost_product_total_products_sales` cpts
                                    WHERE od.`product_id` = cpts.`product_id`
                                )
                                ELSE od.`purchase_supplier_price`
                                END
                            ELSE od.`original_wholesale_price`
                            END
                        ), 0
                    ) * IFNULL(od.`product_quantity`, 0)
                ), IFNULL (' . $referense_select . ', "")
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            ' . $reference_join . '
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_nb_customer_total_products_sales` (`product_id`, `nb_customer`)
            SELECT od.`product_id`, COUNT(DISTINCT o.`id_customer`)
            FROM `' . _DB_PREFIX_ . 'orders` o
            JOIN `' . _DB_PREFIX_ . 'order_detail` od ON o.`id_order` = od.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            GROUP BY od.`product_id`
        ');

        $details_data = Db::getInstance()->executeS('
            SELECT product_reference, product_id, id_order, id_order_detail,
                SUM(quantity) AS quantity,
                SUM(product_quantity) AS product_quantity,
                SUM(total_discounts_tax_excl) AS total_discounts_tax_excl,
                SUM(total_products) AS total_products,
                SUM(total_price_tax_excl) AS total_price_tax_excl,
                IFNULL(
                    (
                        SELECT pl.`name`
                        FROM `' . _DB_PREFIX_ . 'product_lang` pl
                        WHERE pl.`id_product` = product_id
                        AND pl.`id_lang` = ' . $id_lang . ' ' . $where_shop_p1 . '
                        GROUP BY pl.`id_lang`
                    )
                , "-") AS name,
                SUM(quantity_return) AS quantity_return,
                SUM(total_refund_tax_excl) AS total_refund_tax_excl,
                SUM(order_total_refund_tax_excl) AS order_total_refund_tax_excl,
                SUM(nb_customer) AS nb_customer,
                SUM(cost) AS cost
            FROM (
                SELECT nt.`reference` AS product_reference, od.`product_id`, od.`id_order`, od.`id_order_detail`,
                    IFNULL(sa.`quantity`, 0) AS quantity,
                    IFNULL(od.`product_quantity`, 0) AS product_quantity,
                    (IFNULL(o.`total_discounts_tax_excl`, 0) / IFNULL(o.`conversion_rate`, 1)) AS total_discounts_tax_excl,
                    (IFNULL(o.`total_products`, 0) / IFNULL(o.`conversion_rate`, 1)) AS total_products,
                    IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_price_tax_excl,
                    0 AS quantity_return,
                    0 AS total_refund_tax_excl,
                    0 AS order_total_refund_tax_excl,
                    IFNULL(ctp.`nb_customer`, 0) AS nb_customer,
                    SUM(nt.`cost`) AS cost
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                JOIN `nts_cost_total_products_sales` nt ON nt.`id_order_detail` = od.`id_order_detail`
                JOIN `nts_nb_customer_total_products_sales` ctp ON ctp.`product_id` = od.`product_id`
                LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
                LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON sa.`id_product` = p.`id_product`
                    ' . self::whereShopStockAvailable('sa') . ' AND sa.`id_product_attribute` = 0
                WHERE o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                ' . (($product_simple) ? ' AND od.`product_attribute_id` = 0' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_id_category . ')
                    )
                ' : '') . '
                ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                    SELECT p7.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product` p7
                    WHERE p7.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                    )
                ' : '') . '
                ' . (($id_country_invoice) ? ' AND od.`id_order` IN(
                    SELECT o3.`id_order`
                    FROM `' . _DB_PREFIX_ . 'orders` o3
                    JOIN `' . _DB_PREFIX_ . 'address` a3 ON a3.`id_address` = ' . $req_loc_valid_o3 . '
                    WHERE a3.`id_country` IN (' . $p_id_country_invoice . ')
                    )
                ' : '') . '
                ' . (($id_feature) ? ' AND od.`product_id` IN(
                    SELECT fp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp
                    WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                    )
                ' : '') . '
                ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                    SELECT fp1.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                    WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                    )
                ' : '') . '
                GROUP BY od.`product_id`, od.`id_order_detail` ' . (($a_config['group_product_reference']) ? '' : ', od.`product_reference`') . '

                UNION

                SELECT IFNULL (' . $referense_select . ', "") AS product_reference, od.`product_id`, od.`id_order`, od.`id_order_detail`,
                    0 AS quantity,
                    0 AS product_quantity,
                    0 AS total_discounts_tax_excl,
                    0 AS total_products,
                    0 AS total_price_tax_excl,
                    IFNULL(SUM(IFNULL(ord.`product_quantity`, 0)), 0) AS quantity_return,
                    0 AS total_refund_tax_excl,
                    0 AS order_total_refund_tax_excl,
                    0 AS nb_customer,
                    0 AS cost
                FROM `' . _DB_PREFIX_ . 'order_return_detail` ord
                JOIN `' . _DB_PREFIX_ . 'order_return` ore ON ore.`id_order_return` = ord.`id_order_return`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON ord.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = ore.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                ' . $reference_join . '
                WHERE ore.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . self::reqReturnValid('ore') . '
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                ' . (($product_simple) ? ' AND od.`product_attribute_id` = 0' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_id_category . ')
                    )
                ' : '') . '
                ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                    SELECT p7.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product` p7
                    WHERE p7.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                    )
                ' : '') . '
                ' . (($id_country_invoice) ? ' AND od.`id_order` IN(
                    SELECT o3.`id_order`
                    FROM `' . _DB_PREFIX_ . 'orders` o3
                    JOIN `' . _DB_PREFIX_ . 'address` a3 ON a3.`id_address` = ' . $req_loc_valid_o3 . '
                    WHERE a3.`id_country` IN (' . $p_id_country_invoice . ')
                    )
                ' : '') . '
                ' . (($id_feature) ? ' AND od.`product_id` IN(
                    SELECT fp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp
                    WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                    )
                ' : '') . '
                ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                    SELECT fp1.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                    WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                    )
                ' : '') . '
                GROUP BY od.`product_id`, od.`id_order_detail` ' . (($a_config['group_product_reference']) ? '' : ', od.`product_reference`') . '

                UNION

                SELECT IFNULL (' . $referense_select . ', "") AS product_reference, od.`product_id`, od.`id_order`, od.`id_order_detail`,
                    0 AS quantity,
                    0 AS product_quantity,
                    0 AS total_discounts_tax_excl,
                    0 AS total_products,
                    0 AS total_price_tax_excl,
                    0 AS quantity_return,
                    IFNULL(SUM(IFNULL(osd.`total_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_refund_tax_excl,
                    IFNULL(IFNULL(os.`total_products_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1), 0) AS order_total_refund_tax_excl,
                    0 AS nb_customer,
                    0 AS cost
                FROM `' . _DB_PREFIX_ . 'order_slip_detail` osd
                JOIN `' . _DB_PREFIX_ . 'order_slip` os ON osd.`id_order_slip` = os.`id_order_slip`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON osd.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = os.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                ' . $reference_join . '
                WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                ' . (($product_simple) ? ' AND od.`product_attribute_id` = 0' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_id_category . ')
                    )
                ' : '') . '
                ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                    SELECT p7.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product` p7
                    WHERE p7.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                    )
                ' : '') . '
                ' . (($id_country_invoice) ? ' AND od.`id_order` IN(
                    SELECT o3.`id_order`
                    FROM `' . _DB_PREFIX_ . 'orders` o3
                    JOIN `' . _DB_PREFIX_ . 'address` a3 ON a3.`id_address` = ' . $req_loc_valid_o3 . '
                    WHERE a3.`id_country` IN (' . $p_id_country_invoice . ')
                    )
                ' : '') . '
                ' . (($id_feature) ? ' AND od.`product_id` IN(
                    SELECT fp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp
                    WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                    )
                ' : '') . '
                ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                    SELECT fp1.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                    WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                    )
                ' : '') . '
                GROUP BY od.`product_id`, od.`id_order_detail` ' . (($a_config['group_product_reference']) ? '' : ', od.`product_reference`') . '
            ) t
            GROUP BY product_id, id_order_detail ' . (($a_config['group_product_reference']) ? '' : ', product_reference') . '
            ORDER BY product_reference, name
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_total_products_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_total_products_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_nb_customer_total_products_sales`;
        ');

        $products_infos = [];

        foreach ($details_data as &$tmp_dt) {
            $tmp_dt['discount_prorata'] = 0;
            $tmp_dt['margin'] = 0;
            $tmp_dt['unit_margin'] = 0;
            $tmp_dt['margin_per'] = 0;

            if ($tmp_dt['cost'] > 0) {
                $total_price_product = $tmp_dt['total_price_tax_excl'] - $tmp_dt['total_refund_tax_excl'];

                if (($tmp_dt['total_products'] - $tmp_dt['order_total_refund_tax_excl']) > 0) {
                    $tmp_dt['discount_prorata'] = ($tmp_dt['total_discounts_tax_excl'] * $total_price_product) / ($tmp_dt['total_products'] - $tmp_dt['order_total_refund_tax_excl']);
                } else {
                    $tmp_dt['discount_prorata'] = 0;
                }
            }

            $k = $tmp_dt['product_id'] . (($a_config['group_product_reference']) ? '' : '_' . $tmp_dt['product_reference']);

            if (!isset($products_infos[$k])) {
                $products_infos[$k] = $tmp_dt;
            } else {
                $products_infos[$k]['product_quantity'] += $tmp_dt['product_quantity'];
                $products_infos[$k]['total_price_tax_excl'] += $tmp_dt['total_price_tax_excl'];
                $products_infos[$k]['quantity_return'] += $tmp_dt['quantity_return'];
                $products_infos[$k]['total_refund_tax_excl'] += $tmp_dt['total_refund_tax_excl'];
                $products_infos[$k]['cost'] += $tmp_dt['cost'];
                $products_infos[$k]['discount_prorata'] += $tmp_dt['discount_prorata'];
            }
        }

        /*dump($data);
        dump($products_infos);
        die();*/

        $total_quantity = 0;
        $total_product_quantity = 0;
        $total_price_tax_excl = 0;
        $total_quantity_return = 0;
        $total_refund_tax_excl = 0;
        $total_quantity_return_per = 0;
        $total_refund_tax_excl_per = 0;
        $total_product_quantity_per = 0;
        $total_cost = 0;
        $total_margin = 0;
        $total_discount_prorata = 0;
        $total_stock_duration = 0;

        $total_cost_possible = true;

        $data = $products_infos;

        $datetime1 = new DateTime($from);
        $datetime2 = new DateTime($to);
        $difference = $datetime1->diff($datetime2);
        $nb_days_period = $difference->days + 1; // ex: 2023-01-01 - 2023-01-01 should be 1 not 0

        foreach ($data as $dt) {
            // To force natural sorting by keys
            $key = Tools::strtolower(Tools::replaceAccentedChars($dt['product_reference'] . '_' . $dt['name']));

            if ($dt['product_quantity'] > 0) {
                $dt['quantity_return_per'] = $dt['quantity_return'] / $dt['product_quantity'] * 100;
            } else {
                $dt['quantity_return_per'] = 0;
            }

            if ($dt['total_price_tax_excl'] > 0) {
                $dt['total_refund_tax_excl_per'] = $dt['total_refund_tax_excl'] / $dt['total_price_tax_excl'] * 100;
            } else {
                $dt['total_refund_tax_excl_per'] = 0;
            }

            if ($total_product['product_quantity'] > 0) {
                $dt['product_quantity_per'] = $dt['product_quantity'] / $total_product['product_quantity'] * 100;
            } else {
                $dt['product_quantity_per'] = 0;
            }

            if ($total_product['nb_customer'] > 0) {
                $dt['nb_customer_per'] = $dt['nb_customer'] / $total_product['nb_customer'] * 100;
            } else {
                $dt['nb_customer_per'] = 0;
            }

            $dt['unit_margin'] = 0;
            $dt['margin_per'] = 0;
            $dt['margin'] = 0;

            if ($dt['cost'] <= 0) {
                $total_cost_possible = false;
            } else {
                $total_price_product = $dt['total_price_tax_excl'] - $dt['total_refund_tax_excl'];

                if ($total_price_product > 0) {
                    $nb_products_refunded = number_format($dt['total_refund_tax_excl'] / $total_price_product);
                } else {
                    $nb_products_refunded = 0;
                }

                $quantity_calc = $dt['product_quantity'] - $nb_products_refunded;

                if ($quantity_calc > 0) {
                    $dt['unit_margin'] = $dt['margin'] / $quantity_calc;
                } else {
                    $dt['unit_margin'] = 0;
                }

                $dt['margin'] = $total_price_product - $dt['discount_prorata'] - $dt['cost'];

                if (($total_price_product - $dt['discount_prorata']) > 0) {
                    $dt['margin_per'] = ($dt['margin'] / ($total_price_product - $dt['discount_prorata'])) * 100;
                } else {
                    $dt['margin_per'] = 0;
                }
            }

            $total_quantity += $dt['quantity'];
            $total_product_quantity += $dt['product_quantity'];
            $total_price_tax_excl += $dt['total_price_tax_excl'];
            $total_quantity_return += $dt['quantity_return'];
            $total_refund_tax_excl += $dt['total_refund_tax_excl'];
            $total_cost += $dt['cost'];
            $total_margin += $dt['margin'];
            $total_discount_prorata += $dt['discount_prorata'];

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export && $dt['name'] != '-') {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminProducts',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminProducts')],
                    false
                );

                $dt['product_reference'] = '<a href="' . $admin_link . '&id_product=' . $dt['product_id']
                    . '&updateproduct" target="_blank">' . $dt['product_reference'] . '</a>';
            }

            if (!$for_export) {
                $dt['name'] = wordwrap($dt['name'], 50, '<br />');
            }

            if ($dt['product_quantity'] > 0) {
                $stock_duration = ($dt['quantity'] * $nb_days_period) / $dt['product_quantity'];
            } else {
                $stock_duration = 0;
            }

            if (($dt['product_quantity'] + $dt['quantity']) > 0) {
                $sellout = ($dt['product_quantity'] / ($dt['product_quantity'] + $dt['quantity']));
            } else {
                $sellout = 0;
            }

            // To force natural sorting by keys
            $list[$key] = [
                'reference' => $dt['product_reference'],
                'name' => $dt['name'],
                'product_quantity' => $dt['product_quantity'],
                'quantity' => $dt['quantity'],
                'need' => ($dt['quantity'] - $dt['product_quantity']) * -1,
                'stock_duration' => round($stock_duration),
                'sellout' => round($sellout * 100, 2),
                'product_quantity_per' => round($dt['product_quantity_per'], 2),
                'total_price_tax_excl' => (($for_export) ? round($dt['total_price_tax_excl'], 2) : self::displayPrice($dt['total_price_tax_excl'])),
                'quantity_return' => $dt['quantity_return'],
                'nb_customer' => $dt['nb_customer'],
                'nb_customer_per' => round($dt['nb_customer_per'], 2),
                'total_refund_tax_excl' => (($for_export) ? round($dt['total_refund_tax_excl'], 2) : self::displayPrice($dt['total_refund_tax_excl'])),
                'quantity_return_per' => round($dt['quantity_return_per'], 2),
                'total_refund_tax_excl_per' => round($dt['total_refund_tax_excl_per'], 2),
                'cost' => ($dt['cost'] > 0) ? (($for_export) ? round($dt['cost'], 2) : self::displayPrice($dt['cost'])) : '-',
                'discount_prorata' => (($for_export) ? round($dt['discount_prorata'], 2) : self::displayPrice($dt['discount_prorata'])),
                'unit_margin' => ($dt['unit_margin'] > 0) ? (($for_export) ? round($dt['unit_margin'], 2) : self::displayPrice($dt['unit_margin'])) : '-',
                'margin' => ($dt['margin'] > 0) ? (($for_export) ? round($dt['margin'], 2) : self::displayPrice($dt['margin'])) : '-',
                'margin_per' => ($dt['margin_per'] > 0) ? round($dt['margin_per'], 2) : '-',
            ];
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        $clean_list = array_values($list);

        if ($total_product_quantity > 0) {
            $total_quantity_return_per = $total_quantity_return / $total_product_quantity * 100;
            $total_stock_duration = ($total_quantity * $nb_days_period) / $total_product_quantity;
        }

        if ($total_price_tax_excl > 0) {
            $total_refund_tax_excl_per = $total_refund_tax_excl / $total_price_tax_excl * 100;
        }

        if ($total_product['product_quantity'] > 0) {
            $total_product_quantity_per = $total_product_quantity / $total_product['product_quantity'] * 100;
        }

        $clean_list[] = [
            'reference' => $this->l('Total'),
            'name' => '',
            'product_quantity' => $total_product_quantity,
            'quantity' => $total_quantity,
            'need' => ($total_quantity - $total_product_quantity) * -1,
            'stock_duration' => round($total_stock_duration),
            'sellout' => '-',
            'product_quantity_per' => round($total_product_quantity_per, 2),
            'total_price_tax_excl' => (($for_export) ? round($total_price_tax_excl, 2) : self::displayPrice($total_price_tax_excl)),
            'quantity_return' => $total_quantity_return,
            'nb_customer' => $total_product['nb_customer'],
            'nb_customer_per' => '100',
            'total_refund_tax_excl' => (($for_export) ? round($total_refund_tax_excl, 2) : self::displayPrice($total_refund_tax_excl)),
            'quantity_return_per' => round($total_quantity_return_per, 2),
            'total_refund_tax_excl_per' => round($total_refund_tax_excl_per, 2),
            'cost' => ($total_cost_possible) ? (($for_export) ? round($total_cost, 2) : self::displayPrice($total_cost)) : '-',
            'discount_prorata' => (($for_export) ? round($total_discount_prorata, 2) : self::displayPrice($total_discount_prorata)),
            'unit_margin' => '-',
            'margin' => ($total_cost_possible) ? (($for_export) ? round($total_margin, 2) : self::displayPrice($total_margin)) : '-',
            'margin_per' => ($total_cost_possible && (($total_price_tax_excl - $total_refund_tax_excl) - $total_discount_prorata) > 0) ? round(($total_margin / (($total_price_tax_excl - $total_refund_tax_excl) - $total_discount_prorata)) * 100, 2) : '-',
        ];

        return $clean_list;
    }

    public function getTotalManufacturersSales($from, $to, $id_manufacturer, $id_group, $for_export = false)
    {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $req_date_valid_o = self::reqDateValid('o');
        $req_date_valid_o3 = self::reqDateValid('o3');
        $req_loc_valid_o = self::reqLocationValid('o');
        $req_loc_valid_o2 = self::reqLocationValid('o2');
        $req_loc_valid_o3 = self::reqLocationValid('o3');
        $req_return_valid_ore = self::reqReturnValid('ore');
        $where_shop_o = self::whereShop('o');
        $where_shop_o2 = self::whereShop('o2');
        $where_shop_o3 = self::whereShop('o3');
        $where_profile_country_a = self::getWhereProfileCountrie('a');
        $where_profile_country_a2 = self::getWhereProfileCountrie('a2');
        $where_profile_country_a3 = self::getWhereProfileCountrie('a3');
        $p_id_manufacturer = self::protectIntArraySQL($id_manufacturer);
        $p_id_group = self::protectIntArraySQL($id_group);

        $total_manufacturer = Db::getInstance()->getRow('
            SELECT IFNULL(SUM(IFNULL(od.`product_quantity`, 0)), 0) AS product_quantity,
                IFNULL(
                    IFNULL(
                        (
                            SELECT IFNULL(SUM(IFNULL(ord.`product_quantity`, 0)), 0)
                            FROM `' . _DB_PREFIX_ . 'order_return_detail` ord
                            JOIN `' . _DB_PREFIX_ . 'order_return` ore ON ord.`id_order_return` = ore.`id_order_return`
                            JOIN `' . _DB_PREFIX_ . 'order_detail` od2 ON ord.`id_order_detail` = od2.`id_order_detail`
                            JOIN `' . _DB_PREFIX_ . 'orders` o2 ON o2.`id_order` = od2.`id_order`
                            JOIN `' . _DB_PREFIX_ . 'address` a2 ON a2.`id_address` = ' . $req_loc_valid_o2 . '
                            LEFT JOIN `' . _DB_PREFIX_ . 'product` p2 ON p2.`id_product` = od2.`product_id`
                            LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m2 ON p2.`id_manufacturer` = m2.`id_manufacturer`
                            WHERE ore.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                            ' . $req_return_valid_ore . '
                            ' . $where_shop_o2 . '
                            ' . $where_profile_country_a2 . '
                            ' . (($id_manufacturer) ? ' AND m2.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
                            ' . (($id_group) ? ' AND o2.`id_customer` IN(
                                SELECT cg2.`id_customer`
                                FROM `' . _DB_PREFIX_ . 'customer_group` cg2
                                WHERE cg2.`id_group` IN (' . $p_id_group . ')
                                )
                            ' : '') . '
                        )
                    , 0)
                , 0) AS quantity_return,
                IFNULL(
                    (
                        SELECT COUNT(DISTINCT o3.`id_customer`)
                        FROM `' . _DB_PREFIX_ . 'order_detail` od3
                        JOIN `' . _DB_PREFIX_ . 'orders` o3 ON o3.`id_order` = od3.`id_order`
                        JOIN `' . _DB_PREFIX_ . 'address` a3 ON a3.`id_address` = ' . $req_loc_valid_o3 . '
                        LEFT JOIN `' . _DB_PREFIX_ . 'product` p3 ON p3.`id_product` = od3.`product_id`
                        LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m3 ON p3.`id_manufacturer` = m3.`id_manufacturer`
                        WHERE o3.`valid` = 1
                        ' . $where_shop_o3 . '
                        ' . $where_profile_country_a3 . '
                        AND ' . $req_date_valid_o3 . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_manufacturer) ? ' AND m3.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
                        ' . (($id_group) ? ' AND o3.`id_customer` IN(
                            SELECT cg3.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg3
                            WHERE cg3.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                    )
                , 0) AS nb_customer
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
            LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON p.`id_manufacturer` = m.`id_manufacturer`
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_manufacturer) ? ' AND m.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
        ');

        Db::getInstance()->execute('
            DROP VIEW IF EXISTS discount_prorata_manufacturers_sales
        ');

        Db::getInstance()->execute('
            CREATE OR REPLACE VIEW discount_prorata_manufacturers_sales AS
            SELECT month, product_id, id_order, id_order_detail,
                SUM(total_discounts_tax_excl) AS total_discounts_tax_excl, SUM(total_products) AS total_products,
                SUM(total_price_tax_excl) AS total_price_tax_excl, SUM(total_refund_tax_excl) AS total_refund_tax_excl,
                SUM(order_total_refund_tax_excl) AS order_total_refund_tax_excl
            FROM (
                SELECT DATE_FORMAT(' . $req_date_valid_o . ', "%Y-%m") AS month, od.`product_id`, od.`id_order`, od.`id_order_detail`,
                    (IFNULL(o.`total_discounts_tax_excl`, 0) / IFNULL(o.`conversion_rate`, 1)) AS total_discounts_tax_excl,
                    (IFNULL(o.`total_products`, 0) / IFNULL(o.`conversion_rate`, 1)) AS total_products,
                    IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_price_tax_excl,
                    0 AS total_refund_tax_excl,
                    0 AS order_total_refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON p.`id_manufacturer` = m.`id_manufacturer`
                WHERE o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . (($id_manufacturer) ? ' AND m.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY od.`id_order`, od.`id_order_detail`

                UNION

                SELECT DATE_FORMAT(' . $req_date_valid_o . ', "%Y-%m") AS month, od.`product_id`, od.`id_order`, od.`id_order_detail`,
                    0 AS total_discounts_tax_excl,
                    0 AS total_products,
                    0 AS total_price_tax_excl,
                    IFNULL(SUM(IFNULL(osd.`total_price_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_refund_tax_excl,
                    IFNULL(IFNULL(os.`total_products_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1), 0) AS order_total_refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_slip_detail` osd
                JOIN `' . _DB_PREFIX_ . 'order_slip` os ON osd.`id_order_slip` = os.`id_order_slip`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON osd.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON p.`id_manufacturer` = m.`id_manufacturer`
                WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_manufacturer) ? ' AND m.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY od.`id_order`, od.`id_order_detail`
            ) t
            GROUP BY id_order, id_order_detail
            ORDER BY month, id_order_detail
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_total_manufactures_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_total_manufactures_sales`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_total_manufactures_sales` (
                `id_order_detail`   int(10)         UNSIGNED    NOT NULL,
                `cost`              decimal(20,6)               NOT NULL,
                PRIMARY KEY (`id_order_detail`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_product_total_manufactures_sales` (
                `product_id`    int(10)         UNSIGNED    NOT NULL,
                `cost`          decimal(20,6)               NOT NULL,
                PRIMARY KEY (`product_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_product_total_manufactures_sales` (`product_id`, `cost`)
            SELECT t.`product_id`, t.`cost`
            FROM (
                    SELECT od.`product_id`, (
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN od.`purchase_supplier_price`
                        ELSE od.`original_wholesale_price`
                        END
                    ) AS cost
                    FROM `' . _DB_PREFIX_ . 'order_detail` od
                    JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
                    JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                    LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
                    LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON p.`id_manufacturer` = m.`id_manufacturer`
                    WHERE o.`valid` = 1
                    AND od.`product_id` = od.`product_id`
                    ' . $where_shop_o . '
                    ' . $where_profile_country_a . '
                    AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                    ' . (($id_manufacturer) ? ' AND m.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
                    ' . (($id_group) ? ' AND o.`id_customer` IN(
                        SELECT cg.`id_customer`
                        FROM `' . _DB_PREFIX_ . 'customer_group` cg
                        WHERE cg.`id_group` IN (' . $p_id_group . ')
                        )
                    ' : '') . '
                    HAVING cost > 0
                    ORDER BY o.`date_add` ASC
            ) t
            GROUP BY t.`product_id`
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_total_manufactures_sales` (`id_order_detail`, `cost`)
            SELECT od.`id_order_detail`,
            (
                IFNULL(
                    (
                        SELECT
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN
                            CASE od.`purchase_supplier_price`
                            WHEN 0
                            THEN (
                                SELECT IFNULL(cpts.`cost`, 0)
                                FROM `nts_cost_product_total_manufactures_sales` cpts
                                WHERE od.`product_id` = cpts.`product_id`
                            )
                            ELSE od.`purchase_supplier_price`
                            END
                        ELSE od.`original_wholesale_price`
                        END
                    ), 0
                ) * IFNULL(od.`product_quantity`, 0)
            )
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
            LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON p.`id_manufacturer` = m.`id_manufacturer`
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_manufacturer) ? ' AND m.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
        ');

        $data = Db::getInstance()->executeS('
            SELECT id_manufacturer, name, SUM(manufacturer_quantity) AS manufacturer_quantity,
                SUM(total_price_tax_excl) AS total_price_tax_excl, SUM(quantity_return) AS quantity_return,
                SUM(total_refund_tax_excl) AS total_refund_tax_excl, SUM(nb_customer) AS nb_customer,
                SUM(discount_prorata) AS discount_prorata, SUM(cost) AS cost
            FROM (
                SELECT IFNULL(m.`id_manufacturer`, 0) AS id_manufacturer, IFNULL(m.`name`, "-") AS name,
                    IFNULL(SUM(IFNULL(od.`product_quantity`, 0)), 0) AS manufacturer_quantity,
                    IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_price_tax_excl,
                    0 AS quantity_return,
                    0 AS total_refund_tax_excl,
                    IFNULL(
                        (
                            SELECT COUNT(DISTINCT o4.`id_customer`)
                            FROM `' . _DB_PREFIX_ . 'order_detail` od4
                            JOIN `' . _DB_PREFIX_ . 'orders` o4 ON o4.`id_order` = od4.`id_order`
                            JOIN `' . _DB_PREFIX_ . 'address` a4 ON a4.`id_address` = ' . self::reqLocationValid('o4') . '
                            LEFT JOIN `' . _DB_PREFIX_ . 'product` p4 ON p4.`id_product` = od4.`product_id`
                            LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m4 ON p4.`id_manufacturer` = m4.`id_manufacturer`
                            WHERE o4.`valid` = 1
                            ' . self::whereShop('o4') . '
                            ' . self::getWhereProfileCountrie('a4') . '
                            AND ' . self::reqDateValid('o4') . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                            ' . (($id_group) ? ' AND o4.`id_customer` IN(
                                SELECT cg4.`id_customer`
                                FROM `' . _DB_PREFIX_ . 'customer_group` cg4
                                WHERE cg4.`id_group` IN (' . $p_id_group . ')
                                )
                            ' : '') . '
                            AND IFNULL(m4.`id_manufacturer`, 0) = IFNULL(m.`id_manufacturer`, 0)
                        )
                    , 0) AS nb_customer,
                    IFNULL (
                    (
                        SELECT SUM((dp5.`total_discounts_tax_excl` * (dp5.`total_price_tax_excl` - dp5.`total_refund_tax_excl`)) /(dp5.`total_products` - dp5.`order_total_refund_tax_excl`))
                        FROM `discount_prorata_manufacturers_sales` dp5
                        JOIN `' . _DB_PREFIX_ . 'orders` o5 ON dp5.`id_order` = o5.`id_order`
                        JOIN `' . _DB_PREFIX_ . 'address` a5 ON a5.`id_address` = ' . self::reqLocationValid('o5') . '
                        LEFT JOIN `' . _DB_PREFIX_ . 'product` p5 ON p5.`id_product` = dp5.`product_id`
                        LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m5 ON p5.`id_manufacturer` = m5.`id_manufacturer`
                        WHERE o5.`valid` = 1
                        ' . self::whereShop('o5') . '
                        ' . self::getWhereProfileCountrie('a5') . '
                        AND ' . self::reqDateValid('o5') . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_group) ? ' AND o5.`id_customer` IN(
                            SELECT cg5.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg5
                            WHERE cg5.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                        AND IFNULL(m5.`id_manufacturer`, 0) = IFNULL(m.`id_manufacturer`, 0)
                    ), 0) AS discount_prorata,
                    IFNULL(SUM(nt.`cost`), 0) AS cost
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                JOIN `nts_cost_total_manufactures_sales` nt ON nt.`id_order_detail` = od.`id_order_detail`
                LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON p.`id_manufacturer` = m.`id_manufacturer`
                WHERE o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . (($id_manufacturer) ? ' AND m.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY m.`id_manufacturer`

                UNION

                SELECT IFNULL(m.`id_manufacturer`, 0) AS id_manufacturer, IFNULL(m.`name`, "-") AS name,
                    0 AS manufacturer_quantity,
                    0 AS total_price_tax_excl,
                    IFNULL(SUM(IFNULL(ord.`product_quantity`, 0)), 0) AS quantity_return,
                    0 AS total_refund_tax_excl,
                    0 AS nb_customer,
                    0 AS discount_prorata,
                    0 AS cost
                FROM `' . _DB_PREFIX_ . 'order_return_detail` ord
                JOIN `' . _DB_PREFIX_ . 'order_return` ore ON ord.`id_order_return` = ore.`id_order_return`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON ord.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON p.`id_manufacturer` = m.`id_manufacturer`
                WHERE ore.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $req_return_valid_ore . '
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_manufacturer) ? ' AND m.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY m.`id_manufacturer`

                UNION

                SELECT IFNULL(m.`id_manufacturer`, 0) AS id_manufacturer, IFNULL(m.`name`, "-") AS name,
                    0 AS manufacturer_quantity,
                    0 AS total_price_tax_excl,
                    0 AS quantity_return,
                    IFNULL(SUM(IFNULL(osd.`total_price_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_refund_tax_excl,
                    0 AS nb_customer,
                    0 AS discount_prorata,
                    0 AS cost
                FROM `' . _DB_PREFIX_ . 'order_slip_detail` osd
                JOIN `' . _DB_PREFIX_ . 'order_slip` os ON osd.`id_order_slip` = os.`id_order_slip`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON osd.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON p.`id_manufacturer` = m.`id_manufacturer`
                WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_manufacturer) ? ' AND m.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY m.`id_manufacturer`
            ) t
            GROUP BY id_manufacturer
            ORDER BY name
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_total_manufactures_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_total_manufactures_sales`;
        ');

        Db::getInstance()->execute('
            DROP VIEW IF EXISTS discount_prorata_manufacturers_sales
        ');

        $total_price_tax_excl = 0;
        $total_quantity_return = 0;
        $total_refund_tax_excl = 0;
        $total_discount_prorata = 0;
        $total_cost = 0;
        $total_margin = 0;
        $total_quantity_return_per = 0;
        $total_refund_tax_excl_per = 0;

        foreach ($data as $dt) {
            // To force natural sorting by keys
            $key = Tools::strtolower(Tools::replaceAccentedChars($dt['name']));

            if ($dt['manufacturer_quantity'] > 0) {
                $dt['quantity_return_per'] = $dt['quantity_return'] / $dt['manufacturer_quantity'] * 100;
            } else {
                $dt['quantity_return_per'] = 0;
            }

            if ($dt['total_price_tax_excl'] > 0) {
                $dt['total_refund_tax_excl_per'] = $dt['total_refund_tax_excl'] / $dt['total_price_tax_excl'] * 100;
            } else {
                $dt['total_refund_tax_excl_per'] = 0;
            }

            if ($total_manufacturer['product_quantity'] > 0) {
                $dt['manufacturer_quantity_per'] = $dt['manufacturer_quantity'] / $total_manufacturer['product_quantity'] * 100;
            } else {
                $dt['manufacturer_quantity_per'] = 0;
            }

            if ($total_manufacturer['quantity_return'] > 0) {
                $dt['manufacturer_return_per'] = $dt['quantity_return'] / $total_manufacturer['quantity_return'] * 100;
            } else {
                $dt['manufacturer_return_per'] = 0;
            }

            if ($total_manufacturer['nb_customer'] > 0) {
                $dt['nb_customer_per'] = $dt['nb_customer'] / $total_manufacturer['nb_customer'] * 100;
            } else {
                $dt['nb_customer_per'] = 0;
            }

            $margin = $dt['total_price_tax_excl'] - $dt['total_refund_tax_excl'] - $dt['discount_prorata'] - $dt['cost'];

            $total_price_tax_excl += $dt['total_price_tax_excl'];
            $total_quantity_return += $dt['quantity_return'];
            $total_refund_tax_excl += $dt['total_refund_tax_excl'];
            $total_discount_prorata += $dt['discount_prorata'];
            $total_cost += $dt['cost'];
            $total_margin += $margin;

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export && $dt['name'] != '-') {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminManufacturers',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminManufacturers')],
                    false
                );

                $dt['name'] = '<a href="' . $admin_link . '&id_manufacturer=' . $dt['id_manufacturer']
                    . '&updatemanufacturer" target="_blank">' . $dt['name'] . '</a>';
            }

            // To force natural sorting by keys
            $list[$key] = [
                'name' => $dt['name'],
                'manufacturer_quantity' => $dt['manufacturer_quantity'],
                'manufacturer_quantity_per' => round($dt['manufacturer_quantity_per'], 2),
                'nb_customer' => $dt['nb_customer'],
                'nb_customer_per' => round($dt['nb_customer_per'], 2),
                'total_price_tax_excl' => (($for_export) ? round($dt['total_price_tax_excl'], 2) : self::displayPrice($dt['total_price_tax_excl'])),
                'quantity_return' => $dt['quantity_return'],
                'total_refund_tax_excl' => (($for_export) ? round($dt['total_refund_tax_excl'], 2) : self::displayPrice($dt['total_refund_tax_excl'])),
                'discount_prorata' => (($for_export) ? round($dt['discount_prorata'], 2) : self::displayPrice($dt['discount_prorata'])),
                'cost' => (($for_export) ? round($dt['cost'], 2) : self::displayPrice($dt['cost'])),
                'margin' => (($for_export) ? round($margin, 2) : self::displayPrice($margin)),
                'quantity_return_per' => round($dt['quantity_return_per'], 2),
                'total_refund_tax_excl_per' => round($dt['total_refund_tax_excl_per'], 2),
            ];
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        $clean_list = array_values($list);

        if ($total_manufacturer['product_quantity'] > 0) {
            $total_quantity_return_per = $total_quantity_return / $total_manufacturer['product_quantity'] * 100;
        }

        if ($total_price_tax_excl > 0) {
            $total_refund_tax_excl_per = $total_refund_tax_excl / $total_price_tax_excl * 100;
        }

        $clean_list[] = [
            'name' => $this->l('Total'),
            'manufacturer_quantity' => $total_manufacturer['product_quantity'],
            'manufacturer_quantity_per' => '100',
            'nb_customer' => $total_manufacturer['nb_customer'],
            'nb_customer_per' => '100',
            'total_price_tax_excl' => (($for_export) ? round($total_price_tax_excl, 2) : self::displayPrice($total_price_tax_excl)),
            'quantity_return' => $total_quantity_return,
            'total_refund_tax_excl' => (($for_export) ? round($total_refund_tax_excl, 2) : self::displayPrice($total_refund_tax_excl)),
            'discount_prorata' => (($for_export) ? round($total_discount_prorata, 2) : self::displayPrice($total_discount_prorata)),
            'cost' => (($for_export) ? round($total_cost, 2) : self::displayPrice($total_cost)),
            'margin' => (($for_export) ? round($total_margin, 2) : self::displayPrice($total_margin)),
            'quantity_return_per' => round($total_quantity_return_per, 2),
            'total_refund_tax_excl_per' => round($total_refund_tax_excl_per, 2),
        ];

        return $clean_list;
    }

    public function getTotalPaymentMethodsSales($from, $to, $payment_method, $id_group, $for_export = false)
    {
        $list = [];

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $p_payment_method = self::protectStringArraySQL($payment_method);
        $p_id_group = self::protectIntArraySQL($id_group);
        $req_loc_valid_o = self::reqLocationValid('o');
        $where_shop_o = self::whereShop('o');
        $where_profile_country_a = self::getWhereProfileCountrie('a');

        $total_payment_method = Db::getInstance()->getRow('
            SELECT IFNULL(COUNT(DISTINCT o.`id_customer`), 0) AS nb_customer
            FROM `' . _DB_PREFIX_ . 'order_payment` op
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`reference` = op.`order_reference`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND op.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($payment_method) ? ' AND op.`payment_method` IN ("' . $p_payment_method . '")' : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
        ');

        $collation_charset = Db::getInstance()->getRow('
            SELECT COLLATION_NAME, CHARACTER_SET_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = "' . _DB_NAME_ . '"
            AND TABLE_NAME = "' . _DB_PREFIX_ . 'order_payment"
            AND COLUMN_NAME = "payment_method";
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_payment_method_name`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_payment_method_name` (
                `payment_method`    TEXT,
                `displayed_name`    TEXT,
                `nb_customer`       int(10)         UNSIGNED    NOT NULL,
                PRIMARY KEY (`payment_method`(20))
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=' . $collation_charset['CHARACTER_SET_NAME'] . ' COLLATE=' . $collation_charset['COLLATION_NAME'] . ';
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_payment_method_name` (`payment_method`, `displayed_name`, `nb_customer`)
            SELECT op.`payment_method`, IFNULL(ntcpm.`display_name`, op.`payment_method`), COUNT(DISTINCT o.`id_customer`)
            FROM `' . _DB_PREFIX_ . 'order_payment` op
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`reference` = op.`order_reference`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            LEFT JOIN `' . _DB_PREFIX_ . 'nts_config_payment_method` ntcpm ON op.`payment_method` = ntcpm.`payment_method`
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND op.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            ' . (($payment_method) ? ' AND (op.`payment_method` IN ("' . $p_payment_method . '") OR ntcpm.`display_name` IN ("' . $p_payment_method . '"))' : '') . '
            GROUP BY op.`payment_method`
        ');

        $data = Db::getInstance()->executeS('
            SELECT pmn.`displayed_name`, IFNULL(pmn.`nb_customer`, 0) AS nb_customer,
                IFNULL(SUM(IFNULL(op.`amount`, 0)/IFNULL(op.`conversion_rate`, 1)), 0) AS amount_tax_incl
            FROM `' . _DB_PREFIX_ . 'order_payment` op
            JOIN `nts_payment_method_name` pmn ON pmn.`payment_method` = op.`payment_method`
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`reference` = op.`order_reference`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND op.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            ' . (($payment_method) ? ' AND pmn.`payment_method` IN ("' . $p_payment_method . '")' : '') . '
            GROUP BY pmn.`displayed_name`
            ORDER BY pmn.`displayed_name`
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_payment_method_name`;
        ');

        $total_amount_tax_incl = 0;

        foreach ($data as $dt) {
            // To force natural sorting by keys
            $key = Tools::strtolower(Tools::replaceAccentedChars($dt['displayed_name']));

            if ($total_payment_method['nb_customer'] > 0) {
                $dt['nb_customer_per'] = $dt['nb_customer'] / $total_payment_method['nb_customer'] * 100;
            } else {
                $dt['nb_customer_per'] = 0;
            }

            $total_amount_tax_incl += $dt['amount_tax_incl'];

            // To force natural sorting by keys
            $list[$key] = [
                'payment_method' => $dt['displayed_name'],
                'nb_customer' => $dt['nb_customer'],
                'nb_customer_per' => round($dt['nb_customer_per'], 2),
                'amount_tax_incl' => (($for_export) ? round($dt['amount_tax_incl'], 2) : self::displayPrice($dt['amount_tax_incl'])),
            ];
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        $clean_list = array_values($list);

        $clean_list[] = [
            'payment_method' => $this->l('Total'),
            'nb_customer' => $total_payment_method['nb_customer'],
            'nb_customer_per' => '100',
            'amount_tax_incl' => (($for_export) ? round($total_amount_tax_incl, 2) : self::displayPrice($total_amount_tax_incl)),
        ];

        return $clean_list;
    }

    public function getOrders($from, $to, $id_product, $id_group, $id_category, $payment_method, $id_cart_rule, $for_export = false)
    {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $categories = [];

        if ($id_category) {
            foreach ($id_category as $id_cat) {
                $o_category = new Category($id_cat);
                $children = $o_category->getAllChildren();
                $categories[] = $id_cat;

                foreach ($children as $child) {
                    $categories[] = (int) $child->id;
                }
            }
        }

        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $p_payment_method = self::protectStringArraySQL($payment_method);
        $p_cart_rule = self::protectIntArraySQL($id_cart_rule);
        $p_categories = self::protectIntArraySQL($categories);
        $p_id_group = self::protectIntArraySQL($id_group);
        $p_id_product = self::protectIntArraySQL($id_product);
        $req_date_valid_o = self::reqDateValid('o');

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT o.`id_order`, o.`reference`, CONCAT(c.`firstname`, " ", c.`lastname`) AS customer,
                osl.`name` AS order_state, os.`color` AS order_color_state, ' . $req_date_valid_o . ' AS date_valid,
                (IFNULL(o.`total_paid_tax_incl`, 0)/IFNULL(o.`conversion_rate`, 1)) AS total_paid_tax_incl,
                (IFNULL(o.`total_paid_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) AS total_paid_tax_excl,
                (IFNULL(o.`total_discounts_tax_incl`, 0)/IFNULL(o.`conversion_rate`, 1)) AS total_discounts_tax_incl,
                (IFNULL(o.`total_discounts_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) AS total_discounts_tax_excl,
                (IFNULL(o.`total_shipping_tax_incl`, 0)/IFNULL(o.`conversion_rate`, 1)) AS total_shipping_tax_incl,
                (IFNULL(o.`total_shipping_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) AS total_shipping_tax_excl,
                (IFNULL(o.`total_wrapping_tax_incl`, 0)/IFNULL(o.`conversion_rate`, 1)) AS total_wrapping_tax_incl,
                (IFNULL(o.`total_wrapping_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) AS total_wrapping_tax_excl,
                (IFNULL(o.`total_products_wt`, 0)/IFNULL(o.`conversion_rate`, 1)) AS total_products_tax_incl,
                (IFNULL(o.`total_products`, 0)/IFNULL(o.`conversion_rate`, 1)) AS total_products_tax_excl,
                IFNULL(a.`postcode`, "") AS postcode, IFNULL(a.`city`, "") AS city,
                (
                    IFNULL((
                        SELECT SUM(od2.`ecotax`)
                        FROM `' . _DB_PREFIX_ . 'order_detail` od2
                        WHERE od2.`id_order` = o.`id_order`
                    ), 0)/IFNULL(o.`conversion_rate`, 1)
                ) AS total_ecotax_tax_excl,
                (
                    IFNULL((
                        SELECT SUM(od3.`ecotax` + (od3.`ecotax` * (od3.`ecotax_tax_rate` / 100)))
                        FROM `' . _DB_PREFIX_ . 'order_detail` od3
                        WHERE od3.`id_order` = o.`id_order`
                    ), 0)/IFNULL(o.`conversion_rate`, 1)
                ) AS total_ecotax_tax_incl,
                IFNULL((
                    SELECT cl.`name`
                    FROM `' . _DB_PREFIX_ . 'country_lang` cl
                    WHERE cl.`id_country` = a.`id_country` AND cl.`id_lang` = ' . $id_lang . '
                ), "") AS country,
                IFNULL(
                    (
                        SELECT MAX(ocr.`free_shipping`)
                        FROM `' . _DB_PREFIX_ . 'order_cart_rule` ocr
                        WHERE ocr.`id_order` = o.`id_order`
                        GROUP BY ocr.`id_order`
                    )
                , "-") AS free_shipping,
                IFNULL(
                    (
                        SELECT GROUP_CONCAT(op.`date_add` SEPARATOR ", ")
                        FROM `' . _DB_PREFIX_ . 'order_payment` op
                        WHERE op.`order_reference` = o.`reference`
                        GROUP BY op.`order_reference`
                    )
                , "-") AS payment_date,
                IFNULL(
                    (
                        SELECT GROUP_CONCAT(IFNULL(ntcpm.`display_name`, op2.`payment_method`) SEPARATOR ", ")
                        FROM `' . _DB_PREFIX_ . 'order_payment` op2
                        LEFT JOIN `' . _DB_PREFIX_ . 'nts_config_payment_method` ntcpm ON op2.`payment_method` = ntcpm.`payment_method`
                        WHERE op2.`order_reference` = o.`reference`
                    )
                , "-") AS payment_method,
                IFNULL(
                    (
                        SELECT GROUP_CONCAT(ocr.`name` SEPARATOR ", ")
                        FROM `' . _DB_PREFIX_ . 'order_cart_rule` ocr
                        WHERE ocr.`id_order` = o.`id_order`
                        GROUP BY ocr.`id_order`
                    )
                , "-") AS discount_name,
                IFNULL(
                    (
                        SELECT GROUP_CONCAT(oi.`id_order_invoice` SEPARATOR ",")
                        FROM `' . _DB_PREFIX_ . 'order_invoice` oi
                        WHERE oi.`id_order` = o.`id_order`
                        GROUP BY oi.`id_order`
                    )
                , "-") AS id_order_invoice,
                (
                    SELECT SUM(
                        IFNULL(
                            (
                                SELECT
                                CASE od3.`original_wholesale_price`
                                WHEN 0
                                THEN
                                    CASE od3.`purchase_supplier_price`
                                    WHEN 0
                                    THEN (
                                        SELECT(
                                            CASE od4.`original_wholesale_price`
                                            WHEN 0
                                            THEN od4.`purchase_supplier_price`
                                            ELSE od4.`original_wholesale_price`
                                            END
                                        ) AS cost2
                                        FROM `' . _DB_PREFIX_ . 'order_detail` od4
                                        JOIN `' . _DB_PREFIX_ . 'orders` o4 ON od4.`id_order` = o4.`id_order`
                                        JOIN `' . _DB_PREFIX_ . 'address` a4 ON a4.`id_address` = ' . self::reqLocationValid('o4') . '
                                        WHERE od4.`id_order_detail` = od.`id_order_detail`
                                        AND o4.`valid` = 1
                                        ' . self::whereShop('o4') . '
                                        ' . self::getWhereProfileCountrie('a4') . '
                                        AND ' . self::reqDateValid('o4') . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                                        ' . (($id_product) ? ' AND od4.`product_id` IN (' . $p_id_product . ')' : '') . '
                                        ' . (($id_group) ? ' AND o4.`id_customer` IN(
                                            SELECT cg4.`id_customer`
                                            FROM `' . _DB_PREFIX_ . 'customer_group` cg4
                                            WHERE cg4.`id_group` IN (' . $p_id_group . ')
                                            )
                                        ' : '') . '
                                        ' . (($id_category) ? ' AND od4.`product_id` IN(
                                            SELECT cp4.`id_product`
                                            FROM `' . _DB_PREFIX_ . 'category_product` cp4
                                            WHERE cp4.`id_category` IN (' . $p_categories . ')
                                            )
                                        ' : '') . '
                                        ' . (($id_cart_rule) ? ' AND o4.`id_order` IN(
                                            SELECT ocr4.`id_order`
                                            FROM `' . _DB_PREFIX_ . 'order_cart_rule` ocr4
                                            WHERE ocr4.`id_cart_rule` IN (' . $p_cart_rule . ')
                                            )
                                        ' : '') . '
                                        ' . (($payment_method) ? ' AND o4.`reference` IN(
                                                SELECT op5.`order_reference`
                                                FROM `' . _DB_PREFIX_ . 'order_payment` op5
                                                LEFT JOIN `' . _DB_PREFIX_ . 'nts_config_payment_method` ntcpm5 ON op5.`payment_method` = ntcpm5.`payment_method`
                                                WHERE op5.`payment_method` IN ("' . $p_payment_method . '")
                                                OR ntcpm5.`display_name` IN ("' . $p_payment_method . '")
                                            )
                                        ' : '') . '
                                        AND od4.`id_order_detail` = od3.`id_order_detail`
                                        HAVING cost2 > 0
                                        ORDER BY o4.`date_add` ASC
                                        LIMIT 1
                                    )
                                    ELSE od3.`purchase_supplier_price`
                                    END
                                ELSE od3.`original_wholesale_price`
                                END
                            ), 0
                        ) * IFNULL(od3.`product_quantity`, 0)
                    )
                    FROM `' . _DB_PREFIX_ . 'order_detail` od3
                    JOIN `' . _DB_PREFIX_ . 'orders` o3 ON od3.`id_order` = o3.`id_order`
                    JOIN `' . _DB_PREFIX_ . 'address` a3 ON a3.`id_address` = ' . self::reqLocationValid('o3') . '
                    WHERE o3.`id_order` = o.`id_order`
                    AND o3.`valid` = 1
                    ' . self::whereShop('o3') . '
                    ' . self::getWhereProfileCountrie('a3') . '
                    AND ' . self::reqDateValid('o3') . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                    ' . (($id_product) ? ' AND od3.`product_id` IN (' . $p_id_product . ')' : '') . '
                    ' . (($id_group) ? ' AND o3.`id_customer` IN(
                        SELECT cg3.`id_customer`
                        FROM `' . _DB_PREFIX_ . 'customer_group` cg3
                        WHERE cg3.`id_group` IN (' . $p_id_group . ')
                        )
                    ' : '') . '
                    ' . (($id_category) ? ' AND od3.`product_id` IN(
                        SELECT cp3.`id_product`
                        FROM `' . _DB_PREFIX_ . 'category_product` cp3
                        WHERE cp3.`id_category` IN (' . $p_categories . ')
                        )
                    ' : '') . '
                    ' . (($id_cart_rule) ? ' AND o3.`id_order` IN(
                        SELECT ocr3.`id_order`
                        FROM `' . _DB_PREFIX_ . 'order_cart_rule` ocr3
                        WHERE ocr3.`id_cart_rule` IN (' . $p_cart_rule . ')
                        )
                    ' : '') . '
                    ' . (($payment_method) ? ' AND o3.`reference` IN(
                            SELECT op5.`order_reference`
                            FROM `' . _DB_PREFIX_ . 'order_payment` op5
                            LEFT JOIN `' . _DB_PREFIX_ . 'nts_config_payment_method` ntcpm5 ON op5.`payment_method` = ntcpm5.`payment_method`
                            WHERE op5.`payment_method` IN ("' . $p_payment_method . '")
                            OR ntcpm5.`display_name` IN ("' . $p_payment_method . '")
                        )
                    ' : '') . '
                ) AS cost
            FROM `' . _DB_PREFIX_ . 'orders` o
            JOIN `' . _DB_PREFIX_ . 'order_detail` od ON o.`id_order` = od.`id_order`
            JOIN `' . _DB_PREFIX_ . 'customer` c ON o.`id_customer` = c.`id_customer`
            JOIN `' . _DB_PREFIX_ . 'order_state` os ON o.`current_state` = os.`id_order_state`
            JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON o.`current_state` = osl.`id_order_state`
                AND osl.`id_lang` = ' . $id_lang . '
            JOIN `' . _DB_PREFIX_ . 'address` a ON ' . self::reqLocationValid('o') . ' = a.`id_address`
            WHERE o.`valid` = 1
            ' . self::whereShop('o') . '
            ' . self::getWhereProfileCountrie('a') . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            ' . (($id_category) ? ' AND od.`product_id` IN(
                SELECT cp.`id_product`
                FROM `' . _DB_PREFIX_ . 'category_product` cp
                WHERE cp.`id_category` IN (' . $p_categories . ')
                )
            ' : '') . '
            ' . (($id_cart_rule) ? ' AND o.`id_order` IN(
                SELECT ocr.`id_order`
                FROM `' . _DB_PREFIX_ . 'order_cart_rule` ocr
                WHERE ocr.`id_cart_rule` IN (' . $p_cart_rule . ')
                )
            ' : '') . '
            ' . (($payment_method) ? ' AND o.`reference` IN(
                    SELECT op3.`order_reference`
                    FROM `' . _DB_PREFIX_ . 'order_payment` op3
                    LEFT JOIN `' . _DB_PREFIX_ . 'nts_config_payment_method` ntcpm3 ON op3.`payment_method` = ntcpm3.`payment_method`
                    WHERE op3.`payment_method` IN ("' . $p_payment_method . '")
                    OR ntcpm3.`display_name` IN ("' . $p_payment_method . '")
                )
            ' : '') . '
            ORDER BY o.`id_order`
        ');

        $total_paid_tax_excl = 0;
        $total_paid_tax_incl = 0;
        $total_discounts_tax_excl = 0;
        $total_discounts_tax_incl = 0;
        $total_shipping_tax_excl = 0;
        $total_shipping_tax_incl = 0;
        $total_wrapping_tax_excl = 0;
        $total_wrapping_tax_incl = 0;
        $total_products_tax_excl = 0;
        $total_products_tax_incl = 0;
        $total_ecotax_tax_excl = 0;
        $total_ecotax_tax_incl = 0;
        $total_cost = 0;
        $total_gross_profit = 0;
        $total_net_profit = 0;
        $gross_margin = 0;
        $total_gross_margin = 0;
        $net_margin = 0;
        $total_net_margin = 0;

        foreach ($data as $dt) {
            // To force natural sorting by keys
            $key = $dt['id_order'];

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true
                && !$for_export
                && $dt['reference'] != ''
                && $dt['id_order']
            ) {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminOrders',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminOrders')],
                    false
                );

                $dt['reference'] = '<a href="' . $admin_link . '&id_order=' . $dt['id_order']
                    . '&vieworder" target="_blank">' . $dt['reference'] . '</a>';

                $dt['id_order'] = '<a href="' . $admin_link . '&id_order=' . $dt['id_order']
                    . '&vieworder" target="_blank">' . $dt['id_order'] . '</a>';
            }

            $span = 'span';

            if (!$for_export) {
                $dt['order_state'] = '<' . $span . ' class="order_state" style="border-color: ' . $dt['order_color_state'] . ';">' . $dt['order_state'] . '</' . $span . '>';
            }

            $list_id_order_invoice = explode(',', $dt['id_order_invoice']);
            $list_order_invoice_number = [];

            foreach ($list_id_order_invoice as $id_order_invoice) {
                $o_order_invoice = new OrderInvoice($id_order_invoice);
                $list_order_invoice_number[] = $o_order_invoice->getInvoiceNumberFormatted($id_lang);
            }

            $total_paid_tax_excl += $dt['total_paid_tax_excl'];
            $total_paid_tax_incl += $dt['total_paid_tax_incl'];

            $dt['total_vat'] = $dt['total_paid_tax_incl'] - $dt['total_paid_tax_excl'];

            $total_discounts_tax_excl += $dt['total_discounts_tax_excl'];
            $total_discounts_tax_incl += $dt['total_discounts_tax_incl'];

            $dt['discounts_vat'] = $dt['total_discounts_tax_incl'] - $dt['total_discounts_tax_excl'];

            $total_shipping_tax_excl += $dt['total_shipping_tax_excl'];
            $total_shipping_tax_incl += $dt['total_shipping_tax_incl'];

            $dt['shipping_vat'] = $dt['total_shipping_tax_incl'] - $dt['total_shipping_tax_excl'];

            $total_wrapping_tax_excl += $dt['total_wrapping_tax_excl'];
            $total_wrapping_tax_incl += $dt['total_wrapping_tax_incl'];

            $dt['wrapping_vat'] = $dt['total_wrapping_tax_incl'] - $dt['total_wrapping_tax_excl'];

            $total_products_tax_excl += $dt['total_products_tax_excl'];
            $total_products_tax_incl += $dt['total_products_tax_incl'];

            $dt['products_vat'] = $dt['total_products_tax_incl'] - $dt['total_products_tax_excl'];

            $total_ecotax_tax_excl += $dt['total_ecotax_tax_excl'];
            $total_ecotax_tax_incl += $dt['total_ecotax_tax_incl'];

            $dt['ecotax_vat'] = $dt['total_ecotax_tax_incl'] - $dt['total_ecotax_tax_excl'];

            $total_cost += $dt['cost'];

            $gross_profit = $dt['total_products_tax_excl'] - $dt['cost'];
            $total_gross_profit = $total_products_tax_excl - $total_cost;

            $net_profit = $dt['total_products_tax_excl'] - $dt['total_discounts_tax_excl'] - $dt['cost'];
            $total_net_profit = $total_products_tax_excl - $total_discounts_tax_excl - $total_cost;

            if ($dt['total_products_tax_excl'] > 0) {
                $gross_margin = $gross_profit / $dt['total_products_tax_excl'] * 100;
            } else {
                $gross_margin = 0;
            }

            if ($total_products_tax_excl > 0) {
                $total_gross_margin = $total_gross_profit / $total_products_tax_excl * 100;
            } else {
                $total_gross_margin = 0;
            }

            if (($dt['total_products_tax_excl'] - $dt['total_discounts_tax_excl'] + $dt['total_wrapping_tax_excl']) > 0) {
                $net_margin = ($net_profit + $dt['total_wrapping_tax_excl']) / ($dt['total_products_tax_excl'] - $dt['total_discounts_tax_excl'] + $dt['total_wrapping_tax_excl']) * 100;
            } else {
                $net_margin = 0;
            }

            if (($total_products_tax_excl - $total_discounts_tax_excl + $total_wrapping_tax_excl) > 0) {
                $total_net_margin = ($total_net_profit + $total_wrapping_tax_excl) / ($total_products_tax_excl - $total_discounts_tax_excl + $total_wrapping_tax_excl) * 100;
            } else {
                $total_net_margin = 0;
            }

            // To force natural sorting by keys
            $list[$key] = [
                'id_order' => $dt['id_order'],
                'reference' => $dt['reference'],
                'total_paid_tax_excl' => (($for_export) ? round($dt['total_paid_tax_excl'], 2) : self::displayPrice($dt['total_paid_tax_excl'])),
                'total_paid_tax_incl' => (($for_export) ? round($dt['total_paid_tax_incl'], 2) : self::displayPrice($dt['total_paid_tax_incl'])),
                'total_vat' => (($for_export) ? round($dt['total_vat'], 2) : self::displayPrice($dt['total_vat'])),
                'total_products_tax_excl' => (($for_export) ? round($dt['total_products_tax_excl'], 2) : self::displayPrice($dt['total_products_tax_excl'])),
                'total_products_tax_incl' => (($for_export) ? round($dt['total_products_tax_incl'], 2) : self::displayPrice($dt['total_products_tax_incl'])),
                'total_products_vat' => (($for_export) ? round($dt['products_vat'], 2) : self::displayPrice($dt['products_vat'])),
                'total_discounts_tax_excl' => (($for_export) ? round($dt['total_discounts_tax_excl'], 2) : self::displayPrice($dt['total_discounts_tax_excl'])),
                'total_discounts_tax_incl' => (($for_export) ? round($dt['total_discounts_tax_incl'], 2) : self::displayPrice($dt['total_discounts_tax_incl'])),
                'total_discounts_vat' => (($for_export) ? round($dt['discounts_vat'], 2) : self::displayPrice($dt['discounts_vat'])),
                'total_shipping_tax_excl' => (($for_export) ? round($dt['total_shipping_tax_excl'], 2) : self::displayPrice($dt['total_shipping_tax_excl'])),
                'total_shipping_tax_incl' => (($for_export) ? round($dt['total_shipping_tax_incl'], 2) : self::displayPrice($dt['total_shipping_tax_incl'])),
                'total_shipping_vat' => (($for_export) ? round($dt['shipping_vat'], 2) : self::displayPrice($dt['shipping_vat'])),
                'total_wrapping_tax_excl' => (($for_export) ? round($dt['total_wrapping_tax_excl'], 2) : self::displayPrice($dt['total_wrapping_tax_excl'])),
                'total_wrapping_tax_incl' => (($for_export) ? round($dt['total_wrapping_tax_incl'], 2) : self::displayPrice($dt['total_wrapping_tax_incl'])),
                'total_wrapping_vat' => (($for_export) ? round($dt['wrapping_vat'], 2) : self::displayPrice($dt['wrapping_vat'])),
                'total_ecotax_tax_excl' => (($for_export) ? round($dt['total_ecotax_tax_excl'], 2) : self::displayPrice($dt['total_ecotax_tax_excl'])),
                'total_ecotax_tax_incl' => (($for_export) ? round($dt['total_ecotax_tax_incl'], 2) : self::displayPrice($dt['total_ecotax_tax_incl'])),
                'total_ecotax_vat' => (($for_export) ? round($dt['ecotax_vat'], 2) : self::displayPrice($dt['ecotax_vat'])),
                'cost' => (($for_export) ? round($dt['cost'], 2) : self::displayPrice($dt['cost'])),
                'gross_profit' => (($for_export) ? round($gross_profit, 2) : self::displayPrice($gross_profit)),
                'net_profit' => (($for_export) ? round($net_profit, 2) : self::displayPrice($net_profit)),
                'gross_margin' => round($gross_margin, 2),
                'net_margin' => round($net_margin, 2),
                'discount_name' => $dt['discount_name'],
                'free_shipping' => (($dt['free_shipping']) ? (($for_export) ? '1' : '<i class="fas fa-check"></i>') : (($for_export) ? '0' : '<i class="fas fa-times"></i>')),
                'invoice_number' => implode(', ', $list_order_invoice_number),
                'date_valid' => $dt['date_valid'],
                'payment_date' => $dt['payment_date'],
                'payment_method' => $dt['payment_method'],
                'customer' => $dt['customer'],
                'postcode' => $dt['postcode'],
                'city' => $dt['city'],
                'country' => $dt['country'],
                'order_state' => $dt['order_state'],
            ];
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        $clean_list = array_values($list);

        $total_vat = $total_paid_tax_incl - $total_paid_tax_excl;
        $total_discounts_vat = $total_discounts_tax_incl - $total_discounts_tax_excl;
        $total_shipping_vat = $total_shipping_tax_incl - $total_shipping_tax_excl;
        $total_wrapping_vat = $total_wrapping_tax_incl - $total_wrapping_tax_excl;
        $total_products_vat = $total_products_tax_incl - $total_products_tax_excl;
        $total_ecotax_vat = $total_ecotax_tax_incl - $total_ecotax_tax_excl;

        $clean_list[] = [
            'id_order' => $this->l('Total'),
            'reference' => '',
            'total_paid_tax_excl' => (($for_export) ? round($total_paid_tax_excl, 2) : self::displayPrice($total_paid_tax_excl)),
            'total_paid_tax_incl' => (($for_export) ? round($total_paid_tax_incl, 2) : self::displayPrice($total_paid_tax_incl)),
            'total_vat' => (($for_export) ? round($total_vat, 2) : self::displayPrice($total_vat)),
            'total_products_tax_excl' => (($for_export) ? round($total_products_tax_excl, 2) : self::displayPrice($total_products_tax_excl)),
            'total_products_tax_incl' => (($for_export) ? round($total_products_tax_incl, 2) : self::displayPrice($total_products_tax_incl)),
            'total_products_vat' => (($for_export) ? round($total_products_vat, 2) : self::displayPrice($total_products_vat)),
            'total_discounts_tax_excl' => (($for_export) ? round($total_discounts_tax_excl, 2) : self::displayPrice($total_discounts_tax_excl)),
            'total_discounts_tax_incl' => (($for_export) ? round($total_discounts_tax_incl, 2) : self::displayPrice($total_discounts_tax_incl)),
            'total_discounts_vat' => (($for_export) ? round($total_discounts_vat, 2) : self::displayPrice($total_discounts_vat)),
            'total_shipping_tax_excl' => (($for_export) ? round($total_shipping_tax_excl, 2) : self::displayPrice($total_shipping_tax_excl)),
            'total_shipping_tax_incl' => (($for_export) ? round($total_shipping_tax_incl, 2) : self::displayPrice($total_shipping_tax_incl)),
            'total_shipping_vat' => (($for_export) ? round($total_shipping_vat, 2) : self::displayPrice($total_shipping_vat)),
            'total_wrapping_tax_excl' => (($for_export) ? round($total_wrapping_tax_excl, 2) : self::displayPrice($total_wrapping_tax_excl)),
            'total_wrapping_tax_incl' => (($for_export) ? round($total_wrapping_tax_incl, 2) : self::displayPrice($total_wrapping_tax_incl)),
            'total_wrapping_vat' => (($for_export) ? round($total_wrapping_vat, 2) : self::displayPrice($total_wrapping_vat)),
            'total_ecotax_tax_excl' => (($for_export) ? round($total_ecotax_tax_excl, 2) : self::displayPrice($total_ecotax_tax_excl)),
            'total_ecotax_tax_incl' => (($for_export) ? round($total_ecotax_tax_incl, 2) : self::displayPrice($total_ecotax_tax_incl)),
            'total_ecotax_vat' => (($for_export) ? round($total_ecotax_vat, 2) : self::displayPrice($total_ecotax_vat)),
            'cost' => (($for_export) ? round($total_cost, 2) : self::displayPrice($total_cost)),
            'gross_profit' => (($for_export) ? round($total_gross_profit, 2) : self::displayPrice($total_gross_profit)),
            'net_profit' => (($for_export) ? round($total_net_profit, 2) : self::displayPrice($total_net_profit)),
            'gross_margin' => round($total_gross_margin, 2),
            'net_margin' => round($total_net_margin, 2),
            'discount_name' => '',
            'free_shipping' => '',
            'invoice_number' => '',
            'date_valid' => '',
            'payment_date' => '',
            'payment_method' => '',
            'customer' => '',
            'postcode' => '',
            'city' => '',
            'country' => '',
            'order_state' => '',
        ];

        return $clean_list;
    }

    public function getCategories($from, $to, $id_category, $id_group, $for_export = false)
    {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $categories = [];

        if ($id_category) {
            foreach ($id_category as $id_cat) {
                $o_category = new Category($id_cat);
                $children = $o_category->getAllChildren();
                $categories[] = $id_cat;

                foreach ($children as $child) {
                    $categories[] = (int) $child->id;
                }
            }
        }

        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $p_id_group = self::protectIntArraySQL($id_group);
        $p_categories = self::protectIntArraySQL($categories);
        $req_date_valid_o = self::reqDateValid('o');
        $req_date_valid_o2 = self::reqDateValid('o2');
        $req_loc_valid_o = self::reqLocationValid('o');
        $req_loc_valid_o2 = self::reqLocationValid('o2');
        $where_shop_o = self::whereShop('o');
        $where_shop_o2 = self::whereShop('o2');
        $where_profile_country_a = self::getWhereProfileCountrie('a');
        $where_profile_country_a2 = self::getWhereProfileCountrie('a2');

        Db::getInstance()->execute('
            DROP VIEW IF EXISTS discount_prorata_categories
        ');

        Db::getInstance()->execute('
            CREATE OR REPLACE VIEW discount_prorata_categories AS
            SELECT product_id, id_order, id_order_detail,
                    SUM(total_discounts_tax_excl) AS total_discounts_tax_excl,
                    SUM(total_products) AS total_products,
                    SUM(total_price_tax_excl) AS total_price_tax_excl,
                    SUM(total_refund_tax_excl) AS total_refund_tax_excl,
                    SUM(order_total_refund_tax_excl) AS order_total_refund_tax_excl
            FROM (
                SELECT od.`product_id`, od.`id_order`, od.`id_order_detail`,
                    (IFNULL(o.`total_discounts_tax_excl`, 0) / IFNULL(o.`conversion_rate`, 1)) AS total_discounts_tax_excl,
                    (IFNULL(o.`total_products`, 0) / IFNULL(o.`conversion_rate`, 1)) AS total_products,
                    IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_price_tax_excl,
                    0 AS total_refund_tax_excl,
                    0 AS order_total_refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_categories . ')
                    )
                ' : '') . '
                GROUP BY od.`id_order`, od.`id_order_detail`

                UNION

                SELECT od.`product_id`, od.`id_order`, od.`id_order_detail`,
                    0 AS total_discounts_tax_excl,
                    0 AS total_products,
                    0 AS total_price_tax_excl,
                    IFNULL(SUM(IFNULL(osd.`total_price_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_refund_tax_excl,
                    IFNULL(IFNULL(os.`total_products_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1), 0) AS order_total_refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_slip_detail` osd
                JOIN `' . _DB_PREFIX_ . 'order_slip` os ON os.`id_order_slip` = osd.`id_order_slip`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON osd.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_categories . ')
                    )
                ' : '') . '
                GROUP BY od.`id_order`, od.`id_order_detail`
            ) t
            GROUP BY id_order, id_order_detail
        ');

        $total = Db::getInstance()->getRow('
            SELECT
                IFNULL(
                    (
                        SELECT COUNT(DISTINCT o2.`id_customer`)
                        FROM `' . _DB_PREFIX_ . 'customer` c2
                        JOIN `' . _DB_PREFIX_ . 'orders` o2 ON o2.`id_customer` = c2.`id_customer`
                        JOIN `' . _DB_PREFIX_ . 'address` a2 ON a2.`id_address` = ' . $req_loc_valid_o2 . '
                        WHERE o2.`valid` = 1
                        ' . $where_shop_o2 . '
                        ' . $where_profile_country_a2 . '
                        AND ' . $req_date_valid_o2 . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_group) ? ' AND o2.`id_customer` IN(
                            SELECT cg2.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg2
                            WHERE cg2.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                    )
                , 0) AS total_customer,
                IFNULL(
                    (
                        SELECT IFNULL(SUM(IFNULL(od3.`product_quantity`, 0)), 0)
                        FROM `' . _DB_PREFIX_ . 'order_detail` od3
                        JOIN `' . _DB_PREFIX_ . 'orders` o3 ON o3.`id_order` = od3.`id_order`
                        JOIN `' . _DB_PREFIX_ . 'address` a3 ON a3.`id_address` = ' . self::reqLocationValid('o3') . '
                        WHERE o3.`valid` = 1
                        ' . self::whereShop('o3') . '
                        ' . self::getWhereProfileCountrie('a3') . '
                        AND ' . self::reqDateValid('o3') . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_group) ? ' AND o3.`id_customer` IN(
                            SELECT cg3.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg3
                            WHERE cg3.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                    )
                , 0) AS total_product,
                IFNULL(
                    (
                        SELECT IFNULL(SUM(IFNULL(o4.`total_products`, 0)/IFNULL(o4.`conversion_rate`, 1)), 0)
                        FROM `' . _DB_PREFIX_ . 'orders` o4
                        JOIN `' . _DB_PREFIX_ . 'address` a4 ON a4.`id_address` = ' . self::reqLocationValid('o4') . '
                        WHERE o4.`valid` = 1
                        ' . self::whereShop('o4') . '
                        ' . self::getWhereProfileCountrie('a4') . '
                        AND ' . self::reqDateValid('o4') . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_group) ? ' AND o4.`id_customer` IN(
                            SELECT cg4.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg4
                            WHERE cg4.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                    )
                , 0) AS total_products_tax_excl
            FROM `' . _DB_PREFIX_ . 'orders`
        ');

        $category_lang_shop = self::whereShop('cl', false);

        if (!$category_lang_shop) {
            $category_lang_shop = 'cl.`id_shop` = ' . (int) Configuration::get('PS_SHOP_DEFAULT');
        }

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_category_customer`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_category_refund`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_category_cost`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_category_discount_prorata`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_category_order_detail`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_category_customer` (
                `id_category`   int(10)  UNSIGNED    NOT NULL,
                `nb_customer`   int(10) UNSIGNED    NOT NULL,
                PRIMARY KEY (`id_category`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_category_refund` (
                `id_category`           int(10)         UNSIGNED    NOT NULL,
                `total_refund_tax_excl` decimal(20,6)               NOT NULL,
                PRIMARY KEY (`id_category`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_category_cost` (
                `id_category`   int(10)         UNSIGNED    NOT NULL,
                `cost`          decimal(20,6)               NOT NULL,
                PRIMARY KEY (`id_category`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_category_discount_prorata` (
                `id_category`       int(10)         UNSIGNED    NOT NULL,
                `discount_prorata`  decimal(20,6)               NOT NULL,
                PRIMARY KEY (`id_category`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_category_order_detail` (
                `id_category`           int(10)         UNSIGNED    NOT NULL,
                `product_quantity`      int(10)         UNSIGNED    NOT NULL,
                `total_price_tax_excl`  decimal(20,6)               NOT NULL,
                PRIMARY KEY (`id_category`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_category_customer` (`id_category`, `nb_customer`)
            SELECT c.`id_category`, COUNT(DISTINCT o.`id_customer`)
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            JOIN `' . _DB_PREFIX_ . 'category_product` cp ON od.`product_id` = cp.`id_product`
            JOIN `' . _DB_PREFIX_ . 'category` c ON cp.`id_category` = c.`id_category`
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_category) ? ' AND c.`id_category` IN (' . $p_categories . ')' : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            GROUP BY c.`id_category`
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_category_refund` (`id_category`, `total_refund_tax_excl`)
            SELECT c.`id_category`, IFNULL(SUM(IFNULL(osd.`total_price_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0)
            FROM `' . _DB_PREFIX_ . 'order_slip_detail` osd
            JOIN `' . _DB_PREFIX_ . 'order_slip` os ON osd.`id_order_slip` = os.`id_order_slip`
            JOIN `' . _DB_PREFIX_ . 'order_detail` od ON osd.`id_order_detail` = od.`id_order_detail`
            JOIN `' . _DB_PREFIX_ . 'category_product` cp ON od.`product_id` = cp.`id_product`
            JOIN `' . _DB_PREFIX_ . 'category` c ON cp.`id_category` = c.`id_category`
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            ' . (($id_category) ? ' AND c.`id_category` IN (' . $p_categories . ')' : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            GROUP BY c.`id_category`
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_category_cost` (`id_category`, `cost`)
            SELECT c.`id_category`, SUM(
                IFNULL(
                    (
                        SELECT
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN
                            CASE od.`purchase_supplier_price`
                            WHEN 0
                            THEN (
                                SELECT(
                                    CASE od2.`original_wholesale_price`
                                    WHEN 0
                                    THEN od2.`purchase_supplier_price`
                                    ELSE od2.`original_wholesale_price`
                                    END
                                ) AS cost2
                                FROM `' . _DB_PREFIX_ . 'order_detail` od2
                                JOIN `' . _DB_PREFIX_ . 'orders` o2 ON od2.`id_order` = o2.`id_order`
                                JOIN `' . _DB_PREFIX_ . 'address` a2 ON a2.`id_address` = ' . $req_loc_valid_o2 . '
                                JOIN `' . _DB_PREFIX_ . 'category_product` cp2 ON od2.`product_id` = cp2.`id_product`
                                JOIN `' . _DB_PREFIX_ . 'category` c2 ON cp2.`id_category` = c2.`id_category`
                                WHERE o2.`valid` = 1
                                ' . $where_shop_o2 . '
                                ' . $where_profile_country_a2 . '
                                AND ' . $req_date_valid_o2 . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                                ' . (($id_group) ? ' AND o2.`id_customer` IN(
                                    SELECT cg2.`id_customer`
                                    FROM `' . _DB_PREFIX_ . 'customer_group` cg2
                                    WHERE cg2.`id_group` IN (' . $p_id_group . ')
                                    )
                                ' : '') . '
                                AND od2.`id_order_detail` = od.`id_order_detail`
                                HAVING cost2 > 0
                                ORDER BY o2.`date_add` ASC
                                LIMIT 1
                            )
                            ELSE od.`purchase_supplier_price`
                            END
                        ELSE od.`original_wholesale_price`
                        END
                    ), 0
                ) * IFNULL(od.`product_quantity`, 0)
            )
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            JOIN `' . _DB_PREFIX_ . 'category_product` cp ON od.`product_id` = cp.`id_product`
            JOIN `' . _DB_PREFIX_ . 'category` c ON cp.`id_category` = c.`id_category`
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_category) ? ' AND c.`id_category` IN (' . $p_categories . ')' : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            GROUP BY c.`id_category`
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_category_discount_prorata` (`id_category`, `discount_prorata`)
            SELECT c.`id_category`,
                SUM((dp.`total_discounts_tax_excl` * (dp.`total_price_tax_excl` - dp.`total_refund_tax_excl`)) /(dp.`total_products` - dp.`order_total_refund_tax_excl`))
            FROM `discount_prorata_categories` dp
            JOIN `' . _DB_PREFIX_ . 'orders` o ON dp.`id_order` = o.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            JOIN `' . _DB_PREFIX_ . 'order_detail` od ON od.`id_order_detail` = dp.`id_order_detail`
            JOIN `' . _DB_PREFIX_ . 'category_product` cp ON od.`product_id` = cp.`id_product`
            JOIN `' . _DB_PREFIX_ . 'category` c ON cp.`id_category` = c.`id_category`
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_category) ? ' AND c.`id_category` IN (' . $p_categories . ')' : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            GROUP BY c.`id_category`
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_category_order_detail` (`id_category`, `product_quantity`, `total_price_tax_excl`)
            SELECT cp.`id_category`, IFNULL(SUM(IFNULL(od.`product_quantity`, 0)), 0),
                IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0)
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            JOIN `' . _DB_PREFIX_ . 'category_product` cp ON od.`product_id` = cp.`id_product`
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_category) ? ' AND c.`id_category` IN (' . $p_categories . ')' : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            GROUP BY cp.`id_category`
        ');

        $data = Db::getInstance()->executeS('
            SELECT IFNULL(cl.`name`, "-") AS name, c.`id_category`,
                IFNULL(ntcod.`product_quantity`, 0) AS product_quantity,
                IFNULL(ntcc.`nb_customer`, 0) AS nb_customer,
                IFNULL(ntcod.`total_price_tax_excl`, 0) AS total_price_tax_excl,
                IFNULL (ntcr.`total_refund_tax_excl`, 0) AS total_refund_tax_excl,
                IFNULL(ntco.`cost`, 0) AS cost,
                IFNULL(ntcd.`discount_prorata`, 0) AS discount_prorata
            FROM `' . _DB_PREFIX_ . 'category` c
            JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON cl.`id_category` = c.`id_category`
                AND cl.`id_lang` = ' . $id_lang . ' ' . $category_lang_shop . '
            JOIN `' . _DB_PREFIX_ . 'category_product` cp ON cp.`id_category` = c.`id_category`
            JOIN `nts_category_order_detail` ntcod ON ntcod.`id_category` = c.`id_category`
            LEFT JOIN `nts_category_customer` ntcc ON ntcc.`id_category` = c.`id_category`
            LEFT JOIN `nts_category_refund` ntcr ON ntcr.`id_category` = c.`id_category`
            LEFT JOIN `nts_category_cost` ntco ON ntco.`id_category` = c.`id_category`
            LEFT JOIN `nts_category_discount_prorata` ntcd ON ntcd.`id_category` = c.`id_category`
            GROUP BY c.`id_category`
            ORDER BY total_price_tax_excl
        ');

        Db::getInstance()->execute('
            DROP VIEW IF EXISTS discount_prorata_categories
        ');

        foreach ($data as $dt) {
            // To force natural sorting by keys
            $key = Tools::strtolower(Tools::replaceAccentedChars($dt['total_price_tax_excl'] . '_' . $dt['id_category']));

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export && $dt['name'] != '') {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminCategories',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminCategories')],
                    false
                );

                $dt['name'] = '<a href="' . $admin_link . '&id_category=' . $dt['id_category']
                    . '&viewcategory" target="_blank">' . $dt['name'] . '</a>';
            }

            if ($dt['product_quantity'] > 0) {
                $average_amount = $dt['total_price_tax_excl'] / $dt['product_quantity'];
            } else {
                $average_amount = 0;
            }

            if ($total['total_product'] > 0) {
                $product_quantity_per = round($dt['product_quantity'] / $total['total_product'] * 100, 2);
            } else {
                $product_quantity_per = 0;
            }

            if ($total['total_customer'] > 0) {
                $nb_customer_per = round($dt['nb_customer'] / $total['total_customer'] * 100, 2);
            } else {
                $nb_customer_per = 0;
            }

            if ($dt['cost'] > 0) {
                $total_price_product = $dt['total_price_tax_excl'] - $dt['total_refund_tax_excl'];
                $dt['margin'] = $total_price_product - $dt['discount_prorata'] - $dt['cost'];
            } else {
                $dt['margin'] = 0;
            }

            // To force natural sorting by keys
            $list[$key] = [
                'name' => $dt['name'],
                'product_quantity' => $dt['product_quantity'],
                'product_quantity_per' => $product_quantity_per,
                'nb_customer' => $dt['nb_customer'],
                'nb_customer_per' => $nb_customer_per,
                'total_paid_tax_excl' => (($for_export) ? round($dt['total_price_tax_excl'], 2) : self::displayPrice($dt['total_price_tax_excl'])),
                'total_refund_tax_excl' => (($for_export) ? round($dt['total_refund_tax_excl'], 2) : self::displayPrice($dt['total_refund_tax_excl'])),
                'cost' => (($for_export) ? round($dt['cost'], 2) : self::displayPrice($dt['cost'])),
                'discount_prorata' => (($for_export) ? round($dt['discount_prorata'], 2) : self::displayPrice($dt['discount_prorata'])),
                'margin' => (($for_export) ? round($dt['margin'], 2) : self::displayPrice($dt['margin'])),
                'average_mount' => (($for_export) ? round($average_amount, 2) : self::displayPrice($average_amount)),
            ];
        }

        array_multisort(array_keys($list), SORT_NATURAL, SORT_ASC, $list);

        $clean_list = array_values($list);

        return $clean_list;
    }

    public function getDurationStatuses($from, $to, $for_export = false)
    {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $data = Db::getInstance()->executeS('
            SELECT h.`id_order_state`, COUNT(h.`id_order`) AS nb_orders, IFNULL(sl.`name`, "") AS name,
                IFNULL(s.`color`, "#fff") AS color,  SUM(IFNULL(
                (
                    SELECT SUM(
                        datediff(h1.`date_add`,
                            (
                                SELECT MAX(h2.`date_add`)
                                FROM `' . _DB_PREFIX_ . 'order_history` h2
                                WHERE h2.`id_order` = h.`id_order`
                                AND h2.`id_order_state` = h.`id_order_state`
                                AND h2.`date_add` < h1.`date_add`
                            )
                        )
                    )
                    FROM `' . _DB_PREFIX_ . 'order_history` h1
                    WHERE h1.`id_order` = h.`id_order`
                )
            , 0)) AS duration_status
            FROM `' . _DB_PREFIX_ . 'order_history` h
            JOIN `' . _DB_PREFIX_ . 'orders` o ON h.`id_order` = o.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . self::reqLocationValid('o') . '
            JOIN `' . _DB_PREFIX_ . 'order_state_lang` sl ON sl.`id_order_state` = h.`id_order_state` AND sl.`id_lang` = ' . $id_lang . '
            JOIN `' . _DB_PREFIX_ . 'order_state` s ON s.`id_order_state` = h.`id_order_state`
            WHERE o.`valid` = 1
            ' . self::whereShop('o') . '
            ' . self::getWhereProfileCountrie('a') . '
            AND ' . self::reqDateValid('o') . ' BETWEEN "' . pSQL($from) . ' 00:00:00" AND "' . pSQL($to) . ' 23:59:59"
            AND h.`id_order_history` <> (
                SELECT MAX(h3.`id_order_history`)
                FROM `' . _DB_PREFIX_ . 'order_history` h3
                WHERE h3.`id_order` = h.`id_order`
            )
            GROUP BY h.`id_order_state`
        ');

        foreach ($data as $dt) {
            // To force natural sorting by keys
            $key = Tools::strtolower(Tools::replaceAccentedChars($dt['name']));

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export && $dt['name'] != '') {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminStatuses',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminStatuses')],
                    false
                );

                $dt['name'] = '<a href="' . $admin_link . '&id_order_state=' . $dt['id_order_state']
                    . '&updateorder_state" target="_blank">' . $dt['name'] . '</a>';
            }

            $span = 'span';

            if (!$for_export) {
                $dt['name'] = '<' . $span . ' class="order_state" style="border-color: ' . $dt['color'] . ';">' . $dt['name'] . '</' . $span . '>';
            }

            if ($dt['nb_orders'] > 0) {
                $average_duration_status = $dt['duration_status'] / $dt['nb_orders'];
            } else {
                $average_duration_status = 0;
            }

            // To force natural sorting by keys
            $list[$key] = [
                'name' => $dt['name'],
                'nb_orders' => $dt['nb_orders'],
                'average_duration_status' => round($average_duration_status, 2),
            ];
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        $clean_list = array_values($list);

        return $clean_list;
    }

    public function getCustomers($from, $to, $id_group, $for_export = false)
    {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        // Protected variables for SQL usage
        $p_id_group = self::protectIntArraySQL($id_group);
        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $req_date_valid_o = self::reqDateValid('o');
        $req_date_valid_o2 = self::reqDateValid('o2');
        $req_date_valid_o4 = self::reqDateValid('o4');
        $req_loc_valid_o = self::reqLocationValid('o');
        $req_loc_valid_o2 = self::reqLocationValid('o2');
        $where_profile_country_a = self::getWhereProfileCountrie('a');
        $where_profile_country_a2 = self::getWhereProfileCountrie('a2');
        $where_shop_o = self::whereShop('o');
        $where_shop_o2 = self::whereShop('o2');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_customer_cost`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_customer_cost` (
                `id_customer`   int(10)         UNSIGNED    NOT NULL,
                `cost`          decimal(20,6)               NOT NULL,
                PRIMARY KEY (`id_customer`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_customer_cost` (`id_customer`, `cost`)
            SELECT o.`id_customer`, SUM(
                IFNULL(
                    (
                        SELECT
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN
                            CASE od.`purchase_supplier_price`
                            WHEN 0
                            THEN (
                                SELECT(
                                    CASE od2.`original_wholesale_price`
                                    WHEN 0
                                    THEN od2.`purchase_supplier_price`
                                    ELSE od2.`original_wholesale_price`
                                    END
                                ) AS cost2
                                FROM `' . _DB_PREFIX_ . 'order_detail` od2
                                JOIN `' . _DB_PREFIX_ . 'orders` o2 ON od2.`id_order` = o2.`id_order`
                                JOIN `' . _DB_PREFIX_ . 'address` a2 ON a2.`id_address` = ' . $req_loc_valid_o2 . '
                                WHERE o2.`valid` = 1
                                ' . $where_shop_o2 . '
                                ' . $where_profile_country_a2 . '
                                AND ' . $req_date_valid_o2 . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                                ' . (($id_group) ? ' AND o2.`id_customer` IN(
                                    SELECT cg2.`id_customer`
                                    FROM `' . _DB_PREFIX_ . 'customer_group` cg2
                                    WHERE cg2.`id_group` IN (' . $p_id_group . ')
                                    )
                                ' : '') . '
                                AND od2.`id_order_detail` = od.`id_order_detail`
                                HAVING cost2 > 0
                                ORDER BY o2.`date_add` ASC
                                LIMIT 1
                            )
                            ELSE od.`purchase_supplier_price`
                            END
                        ELSE od.`original_wholesale_price`
                        END
                    ), 0
                ) * IFNULL(od.`product_quantity`, 0)
            )
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            GROUP BY o.`id_customer`
        ');

        $data = Db::getInstance()->executeS('
            SELECT c.`id_customer`, CONCAT(c.`lastname`," ", c.`firstname`, " (", c.`email`, ")") AS customer,
                IFNULL(
                    (
                        SELECT count(o2.`id_order`)
                        FROM `' . _DB_PREFIX_ . 'orders` o2
                        JOIN `' . _DB_PREFIX_ . 'address` a2 ON a2.`id_address` = ' . $req_loc_valid_o2 . '
                        WHERE o2.`id_customer` = c.`id_customer`
                        AND o2.`valid` = 1
                        ' . $where_shop_o2 . '
                        ' . $where_profile_country_a2 . '
                        AND ' . $req_date_valid_o2 . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_group) ? ' AND o2.`id_customer` IN(
                            SELECT cg2.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg2
                            WHERE cg2.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                    )
                , 0) AS nb_orders,
                IFNULL(
                    (
                        SELECT count(o2.`id_order`)
                        FROM `' . _DB_PREFIX_ . 'orders` o2
                        JOIN `' . _DB_PREFIX_ . 'address` a2 ON a2.`id_address` = ' . $req_loc_valid_o2 . '
                        WHERE o2.`id_customer` = c.`id_customer`
                        AND o2.`valid` = 0
                        ' . $where_shop_o2 . '
                        ' . $where_profile_country_a2 . '
                        AND ' . $req_date_valid_o2 . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_group) ? ' AND o2.`id_customer` IN(
                            SELECT cg2.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg2
                            WHERE cg2.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                    )
                , 0) AS nb_invalid_orders,
                IFNULL(SUM(IFNULL(od.`product_quantity`, 0)), 0) AS product_quantity,
                IFNULL(
                    (
                        SELECT SUM(IFNULL(o3.`total_paid_tax_excl`, 0)/IFNULL(o3.`conversion_rate`, 1))
                        FROM `' . _DB_PREFIX_ . 'orders` o3
                        JOIN `' . _DB_PREFIX_ . 'address` a3 ON a3.`id_address` = ' . self::reqLocationValid('o3') . '
                        WHERE o3.`id_customer` = c.`id_customer`
                        AND o3.`valid` = 1
                        ' . self::whereShop('o3') . '
                        ' . self::getWhereProfileCountrie('a3') . '
                        AND ' . self::reqDateValid('o3') . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_group) ? ' AND o3.`id_customer` IN(
                            SELECT cg3.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg3
                            WHERE cg3.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                    )
                , 0) AS total_paid_tax_excl,
                IFNULL(
                    (
                        SELECT SUM(
                            datediff(' . $req_date_valid_o4 . ',
                                (
                                    SELECT MAX(' . self::reqDateValid('o5') . ')
                                    FROM `' . _DB_PREFIX_ . 'orders` o5
                                    JOIN `' . _DB_PREFIX_ . 'address` a5 ON a5.`id_address` = ' . self::reqLocationValid('o5') . '
                                    WHERE o5.`id_customer` = c.`id_customer`
                                    AND o5.`valid` = 1
                                    ' . self::whereShop('o5') . '
                                    ' . self::getWhereProfileCountrie('a5') . '
                                    AND ' . self::reqDateValid('o5') . ' < ' . $req_date_valid_o4 . '
                                    ' . (($id_group) ? ' AND o5.`id_customer` IN(
                                        SELECT cg5.`id_customer`
                                        FROM `' . _DB_PREFIX_ . 'customer_group` cg5
                                        WHERE cg5.`id_group` IN (' . $p_id_group . ')
                                        )
                                    ' : '') . '
                                )
                            )
                        )
                        FROM `' . _DB_PREFIX_ . 'orders` o4
                        JOIN `' . _DB_PREFIX_ . 'address` a4 ON a4.`id_address` = ' . self::reqLocationValid('o4') . '
                        WHERE o4.`id_customer` = c.`id_customer`
                        AND o4.`valid` = 1
                        ' . self::whereShop('o4') . '
                        ' . self::getWhereProfileCountrie('a4') . '
                        AND ' . $req_date_valid_o4 . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_group) ? ' AND o4.`id_customer` IN(
                            SELECT cg4.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg4
                            WHERE cg4.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                    )
                , 0) AS days_btw_order,
                IFNULL(
                    (
                        SELECT SUM(IFNULL(o6.`total_discounts_tax_excl`, 0)/IFNULL(o6.`conversion_rate`, 1))
                        FROM `' . _DB_PREFIX_ . 'orders` o6
                        JOIN `' . _DB_PREFIX_ . 'address` a6 ON a6.`id_address` = ' . self::reqLocationValid('o6') . '
                        WHERE o6.`id_customer` = c.`id_customer`
                        AND o6.`valid` = 1
                        ' . self::whereShop('o6') . '
                        ' . self::getWhereProfileCountrie('a6') . '
                        AND ' . self::reqDateValid('o6') . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_group) ? ' AND o6.`id_customer` IN(
                            SELECT cg6.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg6
                            WHERE cg6.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                    )
                , 0) AS total_discounts_tax_excl,
                IFNULL(
                    (
                        SELECT SUM(IFNULL(o7.`total_products`, 0)/IFNULL(o7.`conversion_rate`, 1))
                        FROM `' . _DB_PREFIX_ . 'orders` o7
                        JOIN `' . _DB_PREFIX_ . 'address` a7 ON a7.`id_address` = ' . self::reqLocationValid('o7') . '
                        WHERE o7.`id_customer` = c.`id_customer`
                        AND o7.`valid` = 1
                        ' . self::whereShop('o7') . '
                        ' . self::getWhereProfileCountrie('a7') . '
                        AND ' . self::reqDateValid('o7') . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_group) ? ' AND o7.`id_customer` IN(
                            SELECT cg7.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg7
                            WHERE cg7.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                    )
                , 0) AS total_products_tax_excl,
                IFNULL(ntco.`cost`, 0) AS cost
            FROM `' . _DB_PREFIX_ . 'orders` o
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            JOIN `' . _DB_PREFIX_ . 'customer` c ON o.`id_customer` = c.`id_customer`
            JOIN `' . _DB_PREFIX_ . 'order_detail` od ON od.`id_order` = o.`id_order`
            LEFT JOIN `nts_customer_cost` ntco ON ntco.`id_customer` = o.`id_customer`
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            GROUP BY o.`id_customer`
        ');

        foreach ($data as $dt) {
            // To force natural sorting by keys
            $key = Tools::strtolower(Tools::replaceAccentedChars($dt['customer']));

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export && $dt['customer'] != '') {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminCustomers',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminCustomers')],
                    false
                );

                $dt['customer'] = '<a href="' . $admin_link . '&id_customer=' . $dt['id_customer']
                    . '&viewcustomer" target="_blank">' . $dt['customer'] . '</a>';
            }

            if ($dt['nb_orders'] > 0) {
                $average_order_tax_excl = $dt['total_paid_tax_excl'] / $dt['nb_orders'];
            } else {
                $average_order_tax_excl = 0;
            }

            if ($dt['nb_orders'] > 0) {
                $average_nb_products = $dt['product_quantity'] / $dt['nb_orders'];
            } else {
                $average_nb_products = 0;
            }

            if ($dt['nb_orders'] > 0) {
                $average_days_btw_order = $dt['days_btw_order'] / $dt['nb_orders'];
            } else {
                $average_days_btw_order = 0;
            }

            $net_profit = $dt['total_products_tax_excl'] - $dt['total_discounts_tax_excl'] - $dt['cost'];

            // To force natural sorting by keys
            $list[$key] = [
                'customer' => $dt['customer'],
                'id_customer' => $dt['id_customer'],
                'nb_orders' => $dt['nb_orders'],
                'nb_invalid_orders' => $dt['nb_invalid_orders'],
                'nb_products' => $dt['product_quantity'],
                'total_order_tax_excl' => (($for_export) ? round($dt['total_paid_tax_excl'], 2) : self::displayPrice($dt['total_paid_tax_excl'])),
                'average_order_tax_excl' => round($average_order_tax_excl, 2),
                'average_nb_products' => round($average_nb_products, 2),
                'average_days_btw_orders' => round($average_days_btw_order, 2),
                'net_profit' => (($for_export) ? round($net_profit, 2) : self::displayPrice($net_profit)),
            ];
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        $clean_list = array_values($list);

        return $clean_list;
    }

    public function getCustomersOrdersDetails(
        $from,
        $to,
        $id_group,
        $sort_by,
        $srt_direction,
        $min_valid_order,
        $max_valid_order,
        $min_total_tax_excl,
        $max_total_tax_excl,
        $min_nb_total_products,
        $max_nb_total_products,
        $min_nb_products,
        $max_nb_products,
        $for_export = false
    ) {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;
        $sort_direction = strtoupper($srt_direction);

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $p_id_group = self::protectIntArraySQL($id_group);
        $req_loc_valid_o = self::reqLocationValid('o');
        $req_loc_valid_o2 = self::reqLocationValid('o2');
        $where_shop_o = self::whereShop('o');
        $where_shop_o2 = self::whereShop('o2');
        $where_profile_country_a = self::getWhereProfileCountrie('a');
        $where_profile_country_a2 = self::getWhereProfileCountrie('a2');
        $req_date_valid_o = self::reqDateValid('o');
        $req_date_valid_o2 = self::reqDateValid('o2');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_order_customer`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_order_customer` (
                `id_customer`               int(10)         UNSIGNED    NOT NULL,
                `total_paid_tax_incl`       decimal(20,6)               NOT NULL,
                `total_paid_tax_excl`       decimal(20,6)               NOT NULL,
                `nb_orders`                 int(10)         UNSIGNED    NOT NULL,
                PRIMARY KEY (`id_customer`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_order_customer` (`id_customer`, `total_paid_tax_incl`, `total_paid_tax_excl`, `nb_orders`)
            SELECT o.`id_customer`, SUM(IFNULL(o.`total_paid_tax_incl`, 0)/IFNULL(o.`conversion_rate`, 1)),
                SUM(IFNULL(o.`total_paid_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), count(o.`id_order`)
            FROM `' . _DB_PREFIX_ . 'orders` o
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            GROUP BY o.`id_customer`
        ');

        $data = Db::getInstance()->executeS('
            SELECT c.`id_customer`, CONCAT(c.`lastname`," ", c.`firstname`, " - ID ", c.`id_customer`, " (", c.`email`, ")") AS customer,
                o.`reference`, o.`id_order`, osl.`name` AS order_state, od.`id_order_detail`, od.`product_id`,
                os.`color` AS order_color_state, ' . $req_date_valid_o . ' AS date_valid,
                IFNULL(od.`product_name`, "") AS product_name,
                IFNULL(od.`product_reference`, "") AS product_reference,
                (IFNULL(o.`total_paid_tax_incl`, 0)/IFNULL(o.`conversion_rate`, 1)) AS order_tax_incl,
                (IFNULL(o.`total_paid_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) AS order_tax_excl,
                IFNULL(
                    (
                        SELECT GROUP_CONCAT(op.`date_add` SEPARATOR ", ")
                        FROM `' . _DB_PREFIX_ . 'order_payment` op
                        WHERE op.`order_reference` = o.`reference`
                        GROUP BY op.`order_reference`
                    )
                , "-") AS payment_date,
                IFNULL(
                    (
                        SELECT GROUP_CONCAT(IFNULL(ntcpm.`display_name`, op2.`payment_method`) SEPARATOR ", ")
                        FROM `' . _DB_PREFIX_ . 'order_payment` op2
                        LEFT JOIN `' . _DB_PREFIX_ . 'nts_config_payment_method` ntcpm ON op2.`payment_method` = ntcpm.`payment_method`
                        WHERE op2.`order_reference` = o.`reference`
                    )
                , "-") AS payment_method,
                IFNULL(ntoc.`total_paid_tax_incl`, 0) AS total_paid_tax_incl,
                IFNULL(ntoc.`total_paid_tax_excl`, 0) AS total_paid_tax_excl,
                IFNULL(ntoc.`nb_orders`, 0) AS nb_orders,
                IFNULL(
                    (
                        SELECT count(o2.`id_order`)
                        FROM `' . _DB_PREFIX_ . 'orders` o2
                        JOIN `' . _DB_PREFIX_ . 'address` a2 ON a2.`id_address` = ' . $req_loc_valid_o2 . '
                        WHERE o2.`id_customer` = c.`id_customer`
                        AND o2.`valid` = 0
                        ' . $where_shop_o2 . '
                        ' . $where_profile_country_a2 . '
                        AND ' . $req_date_valid_o2 . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_group) ? ' AND o2.`id_customer` IN(
                            SELECT cg2.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg2
                            WHERE cg2.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                    )
                , 0) AS nb_invalid_orders,
                IFNULL(
                    (
                        SELECT IFNULL(SUM(IFNULL(od2.`product_quantity`, 0)), 0)
                        FROM `' . _DB_PREFIX_ . 'orders` o2
                        JOIN `' . _DB_PREFIX_ . 'address` a2 ON a2.`id_address` = ' . $req_loc_valid_o2 . '
                        JOIN `' . _DB_PREFIX_ . 'order_detail` od2 ON od2.`id_order` = o2.`id_order`
                        WHERE o2.`id_customer` = c.`id_customer`
                        AND o2.`valid` = 1
                        ' . $where_shop_o2 . '
                        ' . $where_profile_country_a2 . '
                        AND ' . $req_date_valid_o2 . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_group) ? ' AND o2.`id_customer` IN(
                            SELECT cg2.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg2
                            WHERE cg2.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                    )
                , 0) AS total_product_quantity,
                IFNULL(
                    (
                        SELECT IFNULL(SUM(IFNULL(od2.`product_quantity`, 0)), 0)
                        FROM `' . _DB_PREFIX_ . 'orders` o2
                        JOIN `' . _DB_PREFIX_ . 'address` a2 ON a2.`id_address` = ' . $req_loc_valid_o2 . '
                        JOIN `' . _DB_PREFIX_ . 'order_detail` od2 ON od2.`id_order` = o2.`id_order`
                        WHERE o2.`id_order` = o.`id_order`
                        AND o2.`valid` = 1
                        ' . $where_shop_o2 . '
                        ' . $where_profile_country_a2 . '
                        AND ' . $req_date_valid_o2 . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_group) ? ' AND o2.`id_customer` IN(
                            SELECT cg2.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg2
                            WHERE cg2.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                    )
                , 0) AS order_product_quantity,
                IFNULL(od.`product_quantity`, 0) AS product_quantity,
                IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_product_price_tax_excl
            FROM `' . _DB_PREFIX_ . 'orders` o
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            JOIN `' . _DB_PREFIX_ . 'order_detail` od ON od.`id_order` = o.`id_order`
            JOIN `' . _DB_PREFIX_ . 'customer` c ON o.`id_customer` = c.`id_customer`
            JOIN `nts_order_customer` ntoc ON ntoc.`id_customer` = c.`id_customer`
            JOIN `' . _DB_PREFIX_ . 'order_state` os ON o.`current_state` = os.`id_order_state`
            JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON o.`current_state` = osl.`id_order_state`
                AND osl.`id_lang` = ' . $id_lang . '
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            GROUP BY od.`id_order_detail`
            HAVING 1=1
            ' . ((is_numeric($min_valid_order)) ? ' AND nb_orders >= ' . (int) $min_valid_order : '') . '
            ' . ((is_numeric($max_valid_order)) ? ' AND nb_orders <= ' . (int) $max_valid_order : '') . '
            ' . ((is_numeric($min_total_tax_excl)) ? ' AND total_paid_tax_excl >= ' . (float) $min_total_tax_excl : '') . '
            ' . ((is_numeric($max_total_tax_excl)) ? ' AND total_paid_tax_excl <= ' . (float) $max_total_tax_excl : '') . '
            ' . ((is_numeric($min_nb_total_products)) ? ' AND total_product_quantity >= ' . (int) $min_nb_total_products : '') . '
            ' . ((is_numeric($max_nb_total_products)) ? ' AND total_product_quantity <= ' . (int) $max_nb_total_products : '') . '
            ' . ((is_numeric($min_nb_products)) ? ' AND order_product_quantity >= ' . (int) $min_nb_products : '') . '
            ' . ((is_numeric($max_nb_products)) ? ' AND order_product_quantity <= ' . (int) $max_nb_products : '') . '
            ORDER BY ' . $sort_by . ' ' . $sort_direction . ', `id_order` ASC, `id_order_detail` ASC
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_order_customer`;
        ');

        $customers_done = [];
        $orders_done = [];

        foreach ($data as $dt) {
            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export && $dt['customer'] != '') {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminCustomers',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminCustomers')],
                    false
                );

                $dt['customer'] = '<a href="' . $admin_link . '&id_customer=' . $dt['id_customer']
                    . '&viewcustomer" target="_blank">' . $dt['customer'] . '</a>';
            }

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export && $dt['reference'] != '') {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminOrders',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminOrders')],
                    false
                );

                $dt['reference'] = '<a href="' . $admin_link . '&id_order=' . $dt['id_order']
                    . '&vieworder" target="_blank">' . $dt['reference'] . '</a>';
            }

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export) {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminProducts',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminProducts')],
                    false
                );

                $dt['product_reference'] = '<a href="' . $admin_link . '&id_product=' . $dt['product_id']
                    . '&updateproduct" target="_blank">' . $dt['product_reference'] . '</a>';
            }

            if (!$for_export) {
                $dt['product_name'] = wordwrap($dt['product_name'], 50, '<br />');
            }

            $span = 'span';

            if (!$for_export) {
                $dt['order_state'] = '<' . $span . ' class="order_state" style="border-color: ' . $dt['order_color_state'] . ';">' . $dt['order_state'] . '</' . $span . '>';
            }

            if (!in_array($dt['id_customer'], $customers_done) || $dt['customer'] == '') {
                $customers_done[] = $dt['id_customer'];

                $list[] = [
                    'customer' => $dt['customer'],
                    'nb_orders' => $dt['nb_orders'],
                    'nb_invalid_orders' => $dt['nb_invalid_orders'],
                    'total_paid_tax_excl' => self::displayPrice($dt['total_paid_tax_excl']),
                    'total_paid_tax_incl' => self::displayPrice($dt['total_paid_tax_incl']),
                    'nb_total_products' => $dt['total_product_quantity'],
                    'reference' => '',
                    'order_tax_excl' => '',
                    'order_tax_incl' => '',
                    'date_valid' => '',
                    'payment_date' => '',
                    'payment_method' => '',
                    'order_state' => '',
                    'nb_products' => '',
                    'product_reference' => '',
                    'product_name' => '',
                    'product_quantity' => '',
                    'total_product_price_tax_excl' => '',
                ];
            }

            $dt['total_product_price_tax_excl'] = self::displayPrice($dt['total_product_price_tax_excl']);

            if (!in_array($dt['id_order'], $orders_done)) {
                $orders_done[] = $dt['id_order'];

                $list[] = [
                    'customer' => '',
                    'nb_orders' => '',
                    'nb_invalid_orders' => '',
                    'total_paid_tax_excl' => '',
                    'total_paid_tax_incl' => '',
                    'nb_total_products' => '',
                    'reference' => $dt['reference'],
                    'order_tax_excl' => self::displayPrice($dt['order_tax_excl']),
                    'order_tax_incl' => self::displayPrice($dt['order_tax_incl']),
                    'date_valid' => $dt['date_valid'],
                    'payment_date' => $dt['payment_date'],
                    'payment_method' => $dt['payment_method'],
                    'order_state' => $dt['order_state'],
                    'nb_products' => $dt['order_product_quantity'],
                    'product_reference' => '',
                    'product_name' => '',
                    'product_quantity' => '',
                    'total_product_price_tax_excl' => '',
                ];
            }

            $list[] = [
                'customer' => '',
                'nb_orders' => '',
                'nb_invalid_orders' => '',
                'total_paid_tax_excl' => '',
                'total_paid_tax_incl' => '',
                'nb_total_products' => '',
                'reference' => '',
                'order_tax_excl' => '',
                'order_tax_incl' => '',
                'date_valid' => '',
                'payment_date' => '',
                'payment_method' => '',
                'order_state' => '',
                'nb_products' => '',
                'product_reference' => $dt['product_reference'],
                'product_name' => $dt['product_name'],
                'product_quantity' => $dt['product_quantity'],
                'total_product_price_tax_excl' => $dt['total_product_price_tax_excl'],
            ];
        }

        return $list;
    }

    public function getCustomersProducts($from, $to, $id_group, $id_manufacturer, $id_feature, $id_feature_value, $id_category, $id_product, $id_combination, $for_export = false)
    {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $categories = [];

        if ($id_category) {
            foreach ($id_category as $id_cat) {
                $o_category = new Category($id_cat);
                $children = $o_category->getAllChildren();
                $categories[] = $id_cat;

                foreach ($children as $child) {
                    $categories[] = (int) $child->id;
                }
            }
        }

        $req_date_valid_o = self::reqDateValid('o');
        $req_date_valid_o2 = self::reqDateValid('o2');
        $req_location_valid_o = self::reqLocationValid('o');
        $req_location_valid_o2 = self::reqLocationValid('o2');
        $where_shop_o = self::whereShop('o');
        $where_shop_o2 = self::whereShop('o2');
        $where_profile_country_a = self::getWhereProfileCountrie('a');
        $where_profile_country_a2 = self::getWhereProfileCountrie('a2');
        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $p_id_group = self::protectIntArraySQL($id_group);
        $p_id_manufacturer = self::protectIntArraySQL($id_manufacturer);
        $p_id_feature = self::protectIntArraySQL($id_feature);
        $p_id_feature_value = self::protectIntArraySQL($id_feature_value);
        $p_categories = self::protectIntArraySQL($categories);
        $p_id_product = self::protectIntArraySQL($id_product);
        $p_id_combination = self::protectIntArraySQL($id_combination);

        $data = Db::getInstance()->executeS('
            SELECT c.`id_customer`, CONCAT(c.`lastname`," ", c.`firstname`, " (", c.`email`, ")") AS customer,
                IFNULL(SUM(IFNULL(od.`product_quantity`, 0)), 0) AS product_quantity, IFNULL(gl.`name`, "--") AS gender,
                (YEAR(CURRENT_DATE)-YEAR(c.`birthday`)) - (RIGHT(CURRENT_DATE, 5)<RIGHT(c.`birthday`, 5)) AS age,
                IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_price_tax_excl,
                IFNULL(
                    (
                        SELECT a1.`city`
                        FROM `' . _DB_PREFIX_ . 'customer` c1
                        JOIN `' . _DB_PREFIX_ . 'orders` o1 ON o1.`id_customer` = c1.`id_customer`
                        JOIN `' . _DB_PREFIX_ . 'address` a1 ON a1.`id_address` = o1.`id_address_delivery`
                        JOIN `' . _DB_PREFIX_ . 'country_lang` cl1 ON cl1.`id_country` = a1.`id_country` AND cl1.`id_lang` = ' . $id_lang . '
                        WHERE c1.`id_customer` = c.`id_customer`
                        GROUP BY a1.`id_address`
                        ORDER BY count(a1.`id_address`) DESC
                        LIMIT 1
                    )
                , "") AS city_delivery_address,
                IFNULL(
                    (
                        SELECT cl1.`name`
                        FROM `' . _DB_PREFIX_ . 'customer` c1
                        JOIN `' . _DB_PREFIX_ . 'orders` o1 ON o1.`id_customer` = c1.`id_customer`
                        JOIN `' . _DB_PREFIX_ . 'address` a1 ON a1.`id_address` = o1.`id_address_delivery`
                        JOIN `' . _DB_PREFIX_ . 'country_lang` cl1 ON cl1.`id_country` = a1.`id_country` AND cl1.`id_lang` = ' . $id_lang . '
                        WHERE c1.`id_customer` = c.`id_customer`
                        GROUP BY a1.`id_address`
                        ORDER BY count(a1.`id_address`) DESC
                        LIMIT 1
                    )
                , "") AS country_delivery_address,
                IFNULL(
                    (
                        SELECT a1.`city`
                        FROM `' . _DB_PREFIX_ . 'customer` c1
                        JOIN `' . _DB_PREFIX_ . 'orders` o1 ON o1.`id_customer` = c1.`id_customer`
                        JOIN `' . _DB_PREFIX_ . 'address` a1 ON a1.`id_address` = o1.`id_address_invoice`
                        JOIN `' . _DB_PREFIX_ . 'country_lang` cl1 ON cl1.`id_country` = a1.`id_country` AND cl1.`id_lang` = ' . $id_lang . '
                        WHERE c1.`id_customer` = c.`id_customer`
                        GROUP BY a1.`id_address`
                        ORDER BY count(a1.`id_address`) DESC
                        LIMIT 1
                    )
                , "") AS city_invoice_address,
                IFNULL(
                    (
                        SELECT cl1.`name`
                        FROM `' . _DB_PREFIX_ . 'customer` c1
                        JOIN `' . _DB_PREFIX_ . 'orders` o1 ON o1.`id_customer` = c1.`id_customer`
                        JOIN `' . _DB_PREFIX_ . 'address` a1 ON a1.`id_address` = o1.`id_address_invoice`
                        JOIN `' . _DB_PREFIX_ . 'country_lang` cl1 ON cl1.`id_country` = a1.`id_country` AND cl1.`id_lang` = ' . $id_lang . '
                        WHERE c1.`id_customer` = c.`id_customer`
                        GROUP BY a1.`id_address`
                        ORDER BY count(a1.`id_address`) DESC
                        LIMIT 1
                    )
                , "") AS country_invoice_address,
                IFNULL(
                    (
                        SELECT MAX(' . $req_date_valid_o2 . ')
                        FROM `' . _DB_PREFIX_ . 'orders` o2
                        JOIN `' . _DB_PREFIX_ . 'address` a2 ON a2.`id_address` = ' . $req_location_valid_o2 . '
                        JOIN `' . _DB_PREFIX_ . 'order_detail` od2 ON o2.`id_order` = od2.`id_order`
                        WHERE o2.`id_customer` = c.`id_customer`
                        AND o2.`valid` = 1
                        ' . $where_shop_o2 . '
                        ' . $where_profile_country_a2 . '
                        AND ' . $req_date_valid_o2 . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_group) ? ' AND o2.`id_customer` IN(
                            SELECT cg2.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg2
                            WHERE cg2.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                        ' . (($id_manufacturer) ? ' AND od2.`product_id` IN(
                            SELECT p2.`id_product`
                            FROM `' . _DB_PREFIX_ . 'product` p2
                            WHERE p2.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                            )
                        ' : '') . '
                        ' . (($id_feature) ? ' AND od2.`product_id` IN(
                            SELECT fp2.`id_product`
                            FROM `' . _DB_PREFIX_ . 'feature_product` fp2
                            WHERE fp2.`id_feature` IN (' . $p_id_feature . ')
                            )
                        ' : '') . '
                        ' . (($id_feature_value) ? ' AND od2.`product_id` IN(
                            SELECT fp3.`id_product`
                            FROM `' . _DB_PREFIX_ . 'feature_product` fp3
                            WHERE fp3.`id_feature_value` IN (' . $p_id_feature_value . ')
                            )
                        ' : '') . '
                        ' . (($id_category) ? ' AND od2.`product_id` IN(
                            SELECT cp2.`id_product`
                            FROM `' . _DB_PREFIX_ . 'category_product` cp2
                            WHERE cp2.`id_category` IN (' . $p_categories . ')
                            )
                        ' : '') . '
                        ' . (($id_product) ? ' AND od2.`product_id` IN (' . $p_id_product . ')' : '') . '
                        ' . (($id_combination) ? ' AND od2.`product_attribute_id` IN (' . $p_id_combination . ')' : '') . '
                    )
                , 0) AS last_date
            FROM `' . _DB_PREFIX_ . 'orders` o
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_location_valid_o . '
            JOIN `' . _DB_PREFIX_ . 'customer` c ON o.`id_customer` = c.`id_customer`
            LEFT JOIN `' . _DB_PREFIX_ . 'gender_lang` gl ON gl.`id_gender` = c.`id_gender` AND gl.`id_lang` = ' . $id_lang . '
            JOIN `' . _DB_PREFIX_ . 'order_detail` od ON od.`id_order` = o.`id_order`
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                SELECT p.`id_product`
                FROM `' . _DB_PREFIX_ . 'product` p
                WHERE p.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                )
            ' : '') . '
            ' . (($id_feature) ? ' AND od.`product_id` IN(
                SELECT fp.`id_product`
                FROM `' . _DB_PREFIX_ . 'feature_product` fp
                WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                )
            ' : '') . '
            ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                SELECT fp1.`id_product`
                FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                )
            ' : '') . '
            ' . (($id_category) ? ' AND od.`product_id` IN(
                SELECT cp.`id_product`
                FROM `' . _DB_PREFIX_ . 'category_product` cp
                WHERE cp.`id_category` IN (' . $p_categories . ')
                )
            ' : '') . '
            ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
            ' . (($id_combination) ? ' AND od.`product_attribute_id` IN (' . $p_id_combination . ')' : '') . '
            GROUP BY o.`id_customer`
        ');

        foreach ($data as $dt) {
            // To force natural sorting by keys
            $key = Tools::strtolower(Tools::replaceAccentedChars($dt['customer']));

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export && $dt['customer'] != '') {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminCustomers',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminCustomers')],
                    false
                );

                $dt['customer'] = '<a href="' . $admin_link . '&id_customer=' . $dt['id_customer']
                    . '&viewcustomer" target="_blank">' . $dt['customer'] . '</a>';
            }

            // To force natural sorting by keys
            $list[$key] = [
                'customer' => $dt['customer'],
                'id_customer' => $dt['id_customer'],
                'nb_products' => $dt['product_quantity'],
                'total_price_tax_excl' => (($for_export) ? round($dt['total_price_tax_excl'], 2) : self::displayPrice($dt['total_price_tax_excl'])),
                'last_date' => $dt['last_date'],
                'gender' => $dt['gender'],
                'age' => (isset($dt['age']) && $dt['age'] != date('Y') ? $dt['age'] : '--'),
                'city_delivery_address' => $dt['city_delivery_address'],
                'country_delivery_address' => $dt['country_delivery_address'],
                'city_invoice_address' => $dt['city_invoice_address'],
                'country_invoice_address' => $dt['country_invoice_address'],
            ];
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        $clean_list = array_values($list);

        return $clean_list;
    }

    public function getCustomersProductsDetails($from, $to, $id_group, $id_manufacturer, $id_feature, $id_feature_value, $id_category, $id_product, $id_combination, $for_export = false)
    {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $categories = [];

        if ($id_category) {
            foreach ($id_category as $id_cat) {
                $o_category = new Category($id_cat);
                $children = $o_category->getAllChildren();
                $categories[] = $id_cat;

                foreach ($children as $child) {
                    $categories[] = (int) $child->id;
                }
            }
        }

        $req_date_valid_o = self::reqDateValid('o');
        $req_location_valid_o = self::reqLocationValid('o');
        $where_shop_o = self::whereShop('o');
        $where_profile_country_a = self::getWhereProfileCountrie('a');
        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $p_id_group = self::protectIntArraySQL($id_group);
        $p_id_manufacturer = self::protectIntArraySQL($id_manufacturer);
        $p_id_feature = self::protectIntArraySQL($id_feature);
        $p_id_feature_value = self::protectIntArraySQL($id_feature_value);
        $p_categories = self::protectIntArraySQL($categories);
        $p_id_product = self::protectIntArraySQL($id_product);
        $p_id_combination = self::protectIntArraySQL($id_combination);

        $data = Db::getInstance()->executeS('
            SELECT c.`id_customer`, CONCAT(c.`lastname`," ", c.`firstname`, " (", c.`email`, ")") AS customer, IFNULL(gl.`name`, "--") AS gender,
                (YEAR(CURRENT_DATE)-YEAR(c.`birthday`)) - (RIGHT(CURRENT_DATE, 5)<RIGHT(c.`birthday`, 5)) AS age,
                o.`reference`, o.`id_order`, osl.`name` AS order_state, od.`id_order_detail`, od.`product_id`,
                os.`color` AS order_color_state, ' . $req_date_valid_o . ' AS date_valid,
                IFNULL(od.`product_name`, "") AS product_name,
                IFNULL(od.`product_reference`, "") AS product_reference,
                (IFNULL(o.`total_paid_tax_incl`, 0)/IFNULL(o.`conversion_rate`, 1)) AS order_tax_incl,
                (IFNULL(o.`total_paid_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) AS order_tax_excl,
                IFNULL(SUM(IFNULL(od.`product_quantity`, 0)), 0) AS product_quantity,
                IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS product_price_tax_excl,
                IFNULL(SUM((IFNULL(od.`unit_price_tax_incl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS product_price_tax_incl,
                IFNULL(ad.`city`, "") AS city_delivery_address, IFNULL(cld.`name`, "") AS country_delivery_address,
                IFNULL(ai.`city`, "") AS city_invoice_address, IFNULL(cli.`name`, "") AS country_invoice_address,
                IFNULL(
                    (
                        SELECT GROUP_CONCAT(op.`date_add` SEPARATOR ", ")
                        FROM `' . _DB_PREFIX_ . 'order_payment` op
                        WHERE op.`order_reference` = o.`reference`
                        GROUP BY op.`order_reference`
                    )
                , "-") AS payment_date,
                IFNULL(
                    (
                        SELECT GROUP_CONCAT(IFNULL(ntcpm.`display_name`, op2.`payment_method`) SEPARATOR ", ")
                        FROM `' . _DB_PREFIX_ . 'order_payment` op2
                        LEFT JOIN `' . _DB_PREFIX_ . 'nts_config_payment_method` ntcpm ON op2.`payment_method` = ntcpm.`payment_method`
                        WHERE op2.`order_reference` = o.`reference`
                    )
                , "-") AS payment_method
            FROM `' . _DB_PREFIX_ . 'orders` o
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_location_valid_o . '
            JOIN `' . _DB_PREFIX_ . 'customer` c ON o.`id_customer` = c.`id_customer`
            JOIN `' . _DB_PREFIX_ . 'address` ad ON ad.`id_address` = o.`id_address_delivery`
            JOIN `' . _DB_PREFIX_ . 'country_lang` cld ON cld.`id_country` = ad.`id_country` AND cld.`id_lang` = ' . $id_lang . '
            JOIN `' . _DB_PREFIX_ . 'address` ai ON ai.`id_address` = o.`id_address_invoice`
            JOIN `' . _DB_PREFIX_ . 'country_lang` cli ON cli.`id_country` = ai.`id_country` AND cli.`id_lang` = ' . $id_lang . '
            LEFT JOIN `' . _DB_PREFIX_ . 'gender_lang` gl ON gl.`id_gender` = c.`id_gender` AND gl.`id_lang` = ' . $id_lang . '
            JOIN `' . _DB_PREFIX_ . 'order_detail` od ON od.`id_order` = o.`id_order`
            JOIN `' . _DB_PREFIX_ . 'order_state` os ON o.`current_state` = os.`id_order_state`
            JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON o.`current_state` = osl.`id_order_state` AND osl.`id_lang` = ' . $id_lang . '
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                SELECT p.`id_product`
                FROM `' . _DB_PREFIX_ . 'product` p
                WHERE p.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                )
            ' : '') . '
            ' . (($id_feature) ? ' AND od.`product_id` IN(
                SELECT fp.`id_product`
                FROM `' . _DB_PREFIX_ . 'feature_product` fp
                WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                )
            ' : '') . '
            ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                SELECT fp1.`id_product`
                FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                )
            ' : '') . '
            ' . (($id_category) ? ' AND od.`product_id` IN(
                SELECT cp.`id_product`
                FROM `' . _DB_PREFIX_ . 'category_product` cp
                WHERE cp.`id_category` IN (' . $p_categories . ')
                )
            ' : '') . '
            ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
            ' . (($id_combination) ? ' AND od.`product_attribute_id` IN (' . $p_id_combination . ')' : '') . '
            GROUP BY od.`id_order_detail`
        ');

        foreach ($data as $dt) {
            // To force natural sorting by keys
            $key = Tools::strtolower(Tools::replaceAccentedChars($dt['customer'].'_'.$dt['id_order_detail']));

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export && $dt['customer'] != '') {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminCustomers',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminCustomers')],
                    false
                );

                $dt['customer'] = '<a href="' . $admin_link . '&id_customer=' . $dt['id_customer']
                    . '&viewcustomer" target="_blank">' . $dt['customer'] . '</a>';
            }

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export && $dt['reference'] != '') {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminOrders',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminOrders')],
                    false
                );

                $dt['reference'] = '<a href="' . $admin_link . '&id_order=' . $dt['id_order']
                    . '&vieworder" target="_blank">' . $dt['reference'] . '</a>';
            }

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export) {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminProducts',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminProducts')],
                    false
                );

                $dt['product_reference'] = '<a href="' . $admin_link . '&id_product=' . $dt['product_id']
                    . '&updateproduct" target="_blank">' . $dt['product_reference'] . '</a>';
            }

            if (!$for_export) {
                $dt['product_name'] = wordwrap($dt['product_name'], 50, '<br />');
            }

            $span = 'span';

            if (!$for_export) {
                $dt['order_state'] = '<' . $span . ' class="order_state" style="border-color: ' . $dt['order_color_state'] . ';">' . $dt['order_state'] . '</' . $span . '>';
            }

            $order_vat = $dt['order_tax_incl'] - $dt['order_tax_excl'];
            $product_vat = $dt['product_price_tax_incl'] - $dt['product_price_tax_excl'];

            // To force natural sorting by keys
            $list[$key] = [
                'customer' => $dt['customer'],
                'id_customer' => $dt['id_customer'],
                'gender' => $dt['gender'],
                'age' => (isset($dt['age']) && $dt['age'] != date('Y') ? $dt['age'] : '--'),
                'order_reference' => $dt['reference'],
                'order_tax_excl' => (($for_export) ? round($dt['order_tax_excl'], 2) : self::displayPrice($dt['order_tax_excl'])),
                'order_vat' => (($for_export) ? round($order_vat, 2) : self::displayPrice($order_vat)),
                'order_tax_incl' => (($for_export) ? round($dt['order_tax_incl'], 2) : self::displayPrice($dt['order_tax_incl'])),
                'city_delivery_address' => $dt['city_delivery_address'],
                'country_delivery_address' => $dt['country_delivery_address'],
                'city_invoice_address' => $dt['city_invoice_address'],
                'country_invoice_address' => $dt['country_invoice_address'],
                'date_valid' => $dt['date_valid'],
                'payment_date' => $dt['payment_date'],
                'payment_method' => $dt['payment_method'],
                'order_state' => $dt['order_state'],
                'product_reference' => $dt['product_reference'],
                'product_name' => $dt['product_name'],
                'product_quantity' => $dt['product_quantity'],
                'product_price_tax_excl' => (($for_export) ? round($dt['product_price_tax_excl'], 2) : self::displayPrice($dt['product_price_tax_excl'])),
                'product_vat' => (($for_export) ? round($product_vat, 2) : self::displayPrice($product_vat)),
                'product_price_tax_incl' => (($for_export) ? round($dt['product_price_tax_incl'], 2) : self::displayPrice($dt['product_price_tax_incl'])),
            ];
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        $clean_list = array_values($list);

        return $clean_list;
    }

    public static function getCompareTotalProductsSales($from, $to, $id_product, $id_group, $id_category, $id_manufacturer, $id_feature, $id_feature_value, $product_simple, $for_export = false)
    {
        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $categories = [];

        if ($id_category) {
            foreach ($id_category as $id_cat) {
                $o_category = new Category($id_cat);
                $children = $o_category->getAllChildren();
                $categories[] = $id_cat;

                foreach ($children as $child) {
                    $categories[] = (int) $child->id;
                }
            }
        }

        $req_loc_valid_o = self::reqLocationValid('o');
        $where_shop_o = self::whereShop('o');
        $where_profile_country_a = self::getWhereProfileCountrie('a');
        $req_date_valid_o = self::reqDateValid('o');
        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $p_id_product = self::protectIntArraySQL($id_product);
        $p_id_group = self::protectIntArraySQL($id_group);
        $p_categories = self::protectIntArraySQL($categories);
        $p_id_manufacturer = self::protectIntArraySQL($id_manufacturer);
        $p_id_feature = self::protectIntArraySQL($id_feature);
        $p_id_feature_value = self::protectIntArraySQL($id_feature_value);

        Db::getInstance()->execute('
            DROP VIEW IF EXISTS discount_prorata_cmp_products_sales
        ');

        Db::getInstance()->execute('
            CREATE OR REPLACE VIEW discount_prorata_cmp_products_sales AS
            SELECT product_id, id_order, id_order_detail, product_attribute_id,
                SUM(total_discounts_tax_excl) AS total_discounts_tax_excl, SUM(total_products) AS total_products,
                SUM(total_price_tax_excl) AS total_price_tax_excl, SUM(total_refund_tax_excl) AS total_refund_tax_excl,
                SUM(order_total_refund_tax_excl) AS order_total_refund_tax_excl
            FROM (
                SELECT od.`product_id`, od.`id_order`, od.`id_order_detail`, od.`product_attribute_id`,
                    (IFNULL(o.`total_discounts_tax_excl`, 0) / IFNULL(o.`conversion_rate`, 1)) AS total_discounts_tax_excl,
                    (IFNULL(o.`total_products`, 0) / IFNULL(o.`conversion_rate`, 1)) AS total_products,
                    IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_price_tax_excl,
                    0 AS total_refund_tax_excl,
                    0 AS order_total_refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                ' . (($product_simple) ? ' AND od.`product_attribute_id` = 0' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_categories . ')
                    )
                ' : '') . '
                ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                    SELECT p.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product` p
                    WHERE p.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                    )
                ' : '') . '
                ' . (($id_feature) ? ' AND od.`product_id` IN(
                    SELECT fp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp
                    WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                    )
                ' : '') . '
                ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                    SELECT fp1.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                    WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                    )
                ' : '') . '
                GROUP BY od.`id_order`, od.`id_order_detail`

                UNION

                SELECT od.`product_id`, od.`id_order`, od.`id_order_detail`, od.`product_attribute_id`,
                    0 AS total_discounts_tax_excl,
                    0 AS total_products,
                    0 AS total_price_tax_excl,
                    IFNULL(SUM(IFNULL(osd.`total_price_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_refund_tax_excl,
                    IFNULL(IFNULL(os.`total_products_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1), 0) AS order_total_refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_slip_detail` osd
                JOIN `' . _DB_PREFIX_ . 'order_slip` os ON osd.`id_order_slip` = os.`id_order_slip`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON osd.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                ' . (($product_simple) ? ' AND od.`product_attribute_id` = 0' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_categories . ')
                    )
                ' : '') . '
                ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                    SELECT p.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product` p
                    WHERE p.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                    )
                ' : '') . '
                ' . (($id_feature) ? ' AND od.`product_id` IN(
                    SELECT fp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp
                    WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                    )
                ' : '') . '
                ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                    SELECT fp1.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                    WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                    )
                ' : '') . '
                GROUP BY od.`id_order`, od.`id_order_detail`
            ) t
            GROUP BY id_order, id_order_detail
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_cmp_products_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_cmp_products_sales`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_cmp_products_sales` (
                `id_order_detail`   int(10)         UNSIGNED    NOT NULL,
                `cost`              decimal(20,6)               NOT NULL,
                PRIMARY KEY (`id_order_detail`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_product_cmp_products_sales` (
                `product_id`    int(10)         UNSIGNED    NOT NULL,
                `cost`          decimal(20,6)               NOT NULL,
                PRIMARY KEY (`product_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_product_cmp_products_sales` (`product_id`, `cost`)
            SELECT t.`product_id`, t.`cost`
            FROM (
                    SELECT od.`product_id`, (
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN od.`purchase_supplier_price`
                        ELSE od.`original_wholesale_price`
                        END
                    ) AS cost
                    FROM `' . _DB_PREFIX_ . 'order_detail` od
                    JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
                    JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                    WHERE o.`valid` = 1
                    ' . $where_shop_o . '
                    ' . $where_profile_country_a . '
                    AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                    ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                    ' . (($product_simple) ? ' AND od.`product_attribute_id` = 0' : '') . '
                    ' . (($id_group) ? ' AND o.`id_customer` IN(
                        SELECT cg.`id_customer`
                        FROM `' . _DB_PREFIX_ . 'customer_group` cg
                        WHERE cg.`id_group` IN (' . $p_id_group . ')
                        )
                    ' : '') . '
                    ' . (($id_category) ? ' AND od.`product_id` IN(
                        SELECT cp.`id_product`
                        FROM `' . _DB_PREFIX_ . 'category_product` cp
                        WHERE cp.`id_category` IN (' . $p_categories . ')
                        )
                    ' : '') . '
                    ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                        SELECT p.`id_product`
                        FROM `' . _DB_PREFIX_ . 'product` p
                        WHERE p.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                        )
                    ' : '') . '
                    ' . (($id_feature) ? ' AND od.`product_id` IN(
                        SELECT fp.`id_product`
                        FROM `' . _DB_PREFIX_ . 'feature_product` fp
                        WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                        )
                    ' : '') . '
                    ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                        SELECT fp2.`id_product`
                        FROM `' . _DB_PREFIX_ . 'feature_product` fp2
                        WHERE fp2.`id_feature_value` IN (' . $p_id_feature_value . ')
                        )
                    ' : '') . '
                    HAVING cost > 0
                    ORDER BY o.`date_add` ASC
            ) t
            GROUP BY t.`product_id`
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_cmp_products_sales` (`id_order_detail`, `cost`)
            SELECT od.`id_order_detail`,
            (
                IFNULL(
                    (
                        SELECT
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN
                            CASE od.`purchase_supplier_price`
                            WHEN 0
                            THEN (
                                SELECT IFNULL(cpts.`cost`, 0)
                                FROM `nts_cost_product_cmp_products_sales` cpts
                                WHERE od.`product_id` = cpts.`product_id`
                            )
                            ELSE od.`purchase_supplier_price`
                            END
                        ELSE od.`original_wholesale_price`
                        END
                    ), 0
                ) * IFNULL(od.`product_quantity`, 0)
            )
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
            ' . (($product_simple) ? ' AND od.`product_attribute_id` = 0' : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            ' . (($id_category) ? ' AND od.`product_id` IN(
                SELECT cp.`id_product`
                FROM `' . _DB_PREFIX_ . 'category_product` cp
                WHERE cp.`id_category` IN (' . $p_categories . ')
                )
            ' : '') . '
            ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                SELECT p.`id_product`
                FROM `' . _DB_PREFIX_ . 'product` p
                WHERE p.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                )
            ' : '') . '
            ' . (($id_feature) ? ' AND od.`product_id` IN(
                SELECT fp3.`id_product`
                FROM `' . _DB_PREFIX_ . 'feature_product` fp3
                WHERE fp3.`id_feature` IN (' . $p_id_feature . ')
                )
            ' : '') . '
            ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                SELECT fp4.`id_product`
                FROM `' . _DB_PREFIX_ . 'feature_product` fp4
                WHERE fp4.`id_feature_value` IN (' . $p_id_feature_value . ')
                )
            ' : '') . '
        ');

        $data = Db::getInstance()->getRow('
            SELECT SUM(product_quantity) AS product_quantity, SUM(total_price_tax_excl) AS total_price_tax_excl,
                SUM(quantity_return) AS quantity_return, SUM(total_refund_tax_excl) AS total_refund_tax_excl,
                SUM(cost) AS cost, SUM(discount_prorata) AS discount_prorata
            FROM (
                SELECT
                    IFNULL(SUM(IFNULL(od.`product_quantity`, 0)), 0) AS product_quantity,
                    IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_price_tax_excl,
                    0 AS quantity_return,
                    0 AS total_refund_tax_excl,
                    IFNULL(SUM(nt.`cost`), 0) AS cost,
                    IFNULL(
                        (SELECT SUM((dp5.`total_discounts_tax_excl` * (dp5.`total_price_tax_excl` - dp5.`total_refund_tax_excl`)) /(dp5.`total_products` - dp5.`order_total_refund_tax_excl`))
                        FROM `discount_prorata_cmp_products_sales` dp5
                        JOIN `' . _DB_PREFIX_ . 'orders` o5 ON dp5.`id_order` = o5.`id_order`
                        JOIN `' . _DB_PREFIX_ . 'address` a5 ON a5.`id_address` = ' . self::reqLocationValid('o5') . '
                        WHERE o5.`valid` = 1
                        ' . self::whereShop('o5') . '
                        ' . self::getWhereProfileCountrie('a5') . '
                        AND ' . self::reqDateValid('o5') . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_product) ? ' AND dp5.`product_id` IN (' . $p_id_product . ')' : '') . '
                        ' . (($product_simple) ? ' AND dp5.`product_attribute_id` = 0' : '') . '
                        ' . (($id_group) ? ' AND o5.`id_customer` IN(
                            SELECT cg5.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg5
                            WHERE cg5.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                        ' . (($id_category) ? ' AND dp5.`product_id` IN(
                            SELECT cp5.`id_product`
                            FROM `' . _DB_PREFIX_ . 'category_product` cp5
                            WHERE cp5.`id_category` IN (' . $p_categories . ')
                            )
                        ' : '') . '
                        ' . (($id_manufacturer) ? ' AND dp5.`product_id` IN(
                            SELECT p5.`id_product`
                            FROM `' . _DB_PREFIX_ . 'product` p5
                            WHERE p5.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                            )
                        ' : '') . '
                        ' . (($id_feature) ? ' AND dp5.`product_id` IN(
                            SELECT fp5.`id_product`
                            FROM `' . _DB_PREFIX_ . 'feature_product` fp5
                            WHERE fp5.`id_feature` IN (' . $p_id_feature . ')
                            )
                        ' : '') . '
                        ' . (($id_feature_value) ? ' AND dp5.`product_id` IN(
                            SELECT fp6.`id_product`
                            FROM `' . _DB_PREFIX_ . 'feature_product` fp6
                            WHERE fp6.`id_feature_value` IN (' . $p_id_feature_value . ')
                            )
                        ' : '') . '
                    ), 0) AS discount_prorata
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                JOIN `nts_cost_cmp_products_sales` nt ON nt.`id_order_detail` = od.`id_order_detail`
                WHERE o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                ' . (($product_simple) ? ' AND od.`product_attribute_id` = 0' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_categories . ')
                    )
                ' : '') . '
                ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                    SELECT p.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product` p
                    WHERE p.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                    )
                ' : '') . '
                ' . (($id_feature) ? ' AND od.`product_id` IN(
                    SELECT fp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp
                    WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                    )
                ' : '') . '
                ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                    SELECT fp1.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                    WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                    )
                ' : '') . '

                UNION

                SELECT
                    0 AS product_quantity,
                    0 AS total_price_tax_excl,
                    IFNULL(SUM(IFNULL(ord.`product_quantity`, 0)), 0) AS quantity_return,
                    0 AS total_refund_tax_excl,
                    0 AS cost,
                    0 AS discount_prorata
                FROM `' . _DB_PREFIX_ . 'order_return_detail` ord
                JOIN `' . _DB_PREFIX_ . 'order_return` ore ON ord.`id_order_return` = ore.`id_order_return`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON ord.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE ore.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . self::reqReturnValid('ore') . '
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                ' . (($product_simple) ? ' AND od.`product_attribute_id` = 0' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_categories . ')
                    )
                ' : '') . '
                ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                    SELECT p.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product` p
                    WHERE p.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                    )
                ' : '') . '
                ' . (($id_feature) ? ' AND od.`product_id` IN(
                    SELECT fp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp
                    WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                    )
                ' : '') . '
                ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                    SELECT fp1.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                    WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                    )
                ' : '') . '

                UNION

                SELECT
                    0 AS product_quantity,
                    0 AS total_price_tax_excl,
                    0 AS quantity_return,
                    IFNULL(SUM(IFNULL(osd.`total_price_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_refund_tax_excl,
                    0 AS cost,
                    0 AS discount_prorata
                FROM `' . _DB_PREFIX_ . 'order_slip_detail` osd
                JOIN `' . _DB_PREFIX_ . 'order_slip` os ON osd.`id_order_slip` = os.`id_order_slip`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON osd.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                ' . (($product_simple) ? ' AND od.`product_attribute_id` = 0' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_categories . ')
                    )
                ' : '') . '
                ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                    SELECT p.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product` p
                    WHERE p.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                    )
                ' : '') . '
                ' . (($id_feature) ? ' AND od.`product_id` IN(
                    SELECT fp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp
                    WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                    )
                ' : '') . '
                ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                    SELECT fp1.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                    WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                    )
                ' : '') . '
            ) t
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_cmp_products_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_cmp_products_sales`;
        ');

        Db::getInstance()->execute('
            DROP VIEW IF EXISTS discount_prorata_cmp_products_sales
        ');

        if ($data['product_quantity'] > 0) {
            $data['quantity_return_per'] = $data['quantity_return'] / $data['product_quantity'] * 100;
        } else {
            $data['quantity_return_per'] = 0;
        }

        if ($data['total_price_tax_excl'] > 0) {
            $data['total_refund_tax_excl_per'] = $data['total_refund_tax_excl'] / $data['total_price_tax_excl'] * 100;
        } else {
            $data['total_refund_tax_excl_per'] = 0;
        }

        $margin = $data['total_price_tax_excl'] - $data['total_refund_tax_excl'] - $data['cost'] - $data['discount_prorata'];

        $list = [
            'from' => $from,
            'to' => $to,
            'product_quantity' => $data['product_quantity'],
            'total_price_tax_excl' => (($for_export) ? round($data['total_price_tax_excl'], 2) : self::displayPrice($data['total_price_tax_excl'])),
            'quantity_return' => $data['quantity_return'],
            'total_refund_tax_excl' => (($for_export) ? round($data['total_refund_tax_excl'], 2) : self::displayPrice($data['total_refund_tax_excl'])),
            'cost' => (($for_export) ? round($data['cost'], 2) : self::displayPrice($data['cost'])),
            'discount_prorata' => (($for_export) ? round($data['discount_prorata'], 2) : self::displayPrice($data['discount_prorata'])),
            'margin' => (($for_export) ? round($margin, 2) : self::displayPrice($margin)),
            'quantity_return_per' => round($data['quantity_return_per'], 2),
            'total_refund_tax_excl_per' => round($data['total_refund_tax_excl_per'], 2),
        ];

        return $list;
    }

    public static function getCompareTotalManufacturersSales($from, $to, $id_manufacturer, $id_group, $for_export = false)
    {
        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $p_id_manufacturer = self::protectIntArraySQL($id_manufacturer);
        $p_id_group = self::protectIntArraySQL($id_group);
        $req_loc_valid_o = self::reqLocationValid('o');
        $req_date_valid_o = self::reqDateValid('o');
        $where_shop_o = self::whereShop('o');
        $where_profile_country_a = self::getWhereProfileCountrie('a');

        Db::getInstance()->execute('
            DROP VIEW IF EXISTS discount_prorata_cmp_manufacturers_sales
        ');

        Db::getInstance()->execute('
            CREATE OR REPLACE VIEW discount_prorata_cmp_manufacturers_sales AS
            SELECT product_id, id_order, id_order_detail, SUM(total_discounts_tax_excl) AS total_discounts_tax_excl,
                SUM(total_products) AS total_products,
                SUM(total_price_tax_excl) AS total_price_tax_excl,
                SUM(total_refund_tax_excl) AS total_refund_tax_excl,
                SUM(order_total_refund_tax_excl) AS order_total_refund_tax_excl
            FROM (
                SELECT od.`product_id`, od.`id_order`, od.`id_order_detail`,
                    (IFNULL(o.`total_discounts_tax_excl`, 0) / IFNULL(o.`conversion_rate`, 1)) AS total_discounts_tax_excl,
                    (IFNULL(o.`total_products`, 0) / IFNULL(o.`conversion_rate`, 1)) AS total_products,
                    IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_price_tax_excl,
                    0 AS total_refund_tax_excl,
                    0 AS order_total_refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON p.`id_manufacturer` = m.`id_manufacturer`
                WHERE o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . (($id_manufacturer) ? ' AND m.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY od.`id_order`, od.`id_order_detail`

                UNION

                SELECT od.`product_id`, od.`id_order`, od.`id_order_detail`,
                    0 AS total_discounts_tax_excl,
                    0 AS total_products,
                    0 AS total_price_tax_excl,
                    IFNULL(SUM(IFNULL(osd.`total_price_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_refund_tax_excl,
                    IFNULL(IFNULL(os.`total_products_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1), 0) AS order_total_refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_slip_detail` osd
                JOIN `' . _DB_PREFIX_ . 'order_slip` os ON osd.`id_order_slip` = os.`id_order_slip`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON osd.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON p.`id_manufacturer` = m.`id_manufacturer`
                WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_manufacturer) ? ' AND m.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY od.`id_order`, od.`id_order_detail`
            )t
            GROUP BY id_order, id_order_detail
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_cmp_manufacturers_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_cmp_manufacturers_sales`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_cmp_manufacturers_sales` (
                `id_order_detail`   int(10)         UNSIGNED    NOT NULL,
                `cost`              decimal(20,6)               NOT NULL,
                PRIMARY KEY (`id_order_detail`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_product_cmp_manufacturers_sales` (
                `product_id`    int(10)         UNSIGNED    NOT NULL,
                `cost`          decimal(20,6)               NOT NULL,
                PRIMARY KEY (`product_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_product_cmp_manufacturers_sales` (`product_id`, `cost`)
            SELECT t.`product_id`, t.`cost`
            FROM (
                    SELECT od.`product_id`, (
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN od.`purchase_supplier_price`
                        ELSE od.`original_wholesale_price`
                        END
                    ) AS cost
                    FROM `' . _DB_PREFIX_ . 'order_detail` od
                    JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
                    JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                    LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
                    LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON p.`id_manufacturer` = m.`id_manufacturer`
                    WHERE o.`valid` = 1
                    ' . $where_shop_o . '
                    ' . $where_profile_country_a . '
                    AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                    ' . (($id_manufacturer) ? ' AND m.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
                    ' . (($id_group) ? ' AND o.`id_customer` IN(
                        SELECT cg.`id_customer`
                        FROM `' . _DB_PREFIX_ . 'customer_group` cg
                        WHERE cg.`id_group` IN (' . $p_id_group . ')
                        )
                    ' : '') . '
                    HAVING cost > 0
                    ORDER BY o.`date_add` ASC
            ) t
            GROUP BY t.`product_id`
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_cmp_manufacturers_sales` (`id_order_detail`, `cost`)
            SELECT od.`id_order_detail`,
            (
                IFNULL(
                    (
                        SELECT
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN
                            CASE od.`purchase_supplier_price`
                            WHEN 0
                            THEN (
                                SELECT IFNULL(cpts.`cost`, 0)
                                FROM `nts_cost_product_cmp_manufacturers_sales` cpts
                                WHERE od.`product_id` = cpts.`product_id`
                            )
                            ELSE od.`purchase_supplier_price`
                            END
                        ELSE od.`original_wholesale_price`
                        END
                    ), 0
                ) * IFNULL(od.`product_quantity`, 0)
            )
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
            LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON p.`id_manufacturer` = m.`id_manufacturer`
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_manufacturer) ? ' AND m.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
        ');

        $data = Db::getInstance()->getRow('
            SELECT SUM(manufacturer_quantity) AS manufacturer_quantity,
                SUM(total_price_tax_excl) AS total_price_tax_excl, SUM(quantity_return) AS quantity_return,
                SUM(total_refund_tax_excl) AS total_refund_tax_excl, SUM(discount_prorata) AS discount_prorata,
                SUM(cost) AS cost
            FROM (
                SELECT
                    IFNULL(SUM(IFNULL(od.`product_quantity`, 0)), 0) AS manufacturer_quantity,
                    IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_price_tax_excl,
                    0 AS quantity_return,
                    0 AS total_refund_tax_excl,
                    (
                        SELECT IFNULL(SUM((dp5.`total_discounts_tax_excl` * (dp5.`total_price_tax_excl` - dp5.`total_refund_tax_excl`)) /(dp5.`total_products` - dp5.`order_total_refund_tax_excl`)), 0)
                        FROM `discount_prorata_cmp_manufacturers_sales` dp5
                        JOIN `' . _DB_PREFIX_ . 'orders` o5 ON dp5.`id_order` = o5.`id_order`
                        JOIN `' . _DB_PREFIX_ . 'address` a5 ON a5.`id_address` = ' . self::reqLocationValid('o5') . '
                        LEFT JOIN `' . _DB_PREFIX_ . 'product` p5 ON p5.`id_product` = dp5.`product_id`
                        LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m5 ON p5.`id_manufacturer` = m5.`id_manufacturer`
                        WHERE o5.`valid` = 1
                        ' . self::whereShop('o5') . '
                        ' . self::getWhereProfileCountrie('a5') . '
                        AND ' . self::reqDateValid('o5') . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_manufacturer) ? ' AND m5.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
                        ' . (($id_group) ? ' AND o5.`id_customer` IN(
                            SELECT cg5.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg5
                            WHERE cg5.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                    ) AS discount_prorata,
                    IFNULL(SUM(nt.`cost`), 0) AS cost
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                JOIN `nts_cost_cmp_manufacturers_sales` nt ON nt.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
                JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON p.`id_manufacturer` = m.`id_manufacturer`
                WHERE o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . (($id_manufacturer) ? ' AND m.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '

                UNION

                SELECT
                    0 AS manufacturer_quantity,
                    0 AS total_price_tax_excl,
                    IFNULL(SUM(IFNULL(ord.`product_quantity`, 0)), 0) AS quantity_return,
                    0 AS total_refund_tax_excl,
                    0 AS discount_prorata,
                    0 AS cost
                FROM `' . _DB_PREFIX_ . 'order_return_detail` ord
                JOIN `' . _DB_PREFIX_ . 'order_return` ore ON ord.`id_order_return` = ore.`id_order_return`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON ord.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
                JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON p.`id_manufacturer` = m.`id_manufacturer`
                WHERE ore.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . self::reqReturnValid('ore') . '
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_manufacturer) ? ' AND m.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '

                UNION

                SELECT
                    0 AS manufacturer_quantity,
                    0 AS total_price_tax_excl,
                    0 AS quantity_return,
                    IFNULL(SUM(IFNULL(osd.`total_price_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_refund_tax_excl,
                    0 AS discount_prorata,
                    0 AS cost
                FROM `' . _DB_PREFIX_ . 'order_slip_detail` osd
                JOIN `' . _DB_PREFIX_ . 'order_slip` os ON osd.`id_order_slip` = os.`id_order_slip`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON osd.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
                JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON p.`id_manufacturer` = m.`id_manufacturer`
                WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_manufacturer) ? ' AND m.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
            ) t
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_cmp_manufacturers_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_cmp_manufacturers_sales`;
        ');

        Db::getInstance()->execute('
            DROP VIEW IF EXISTS discount_prorata_cmp_manufacturers_sales
        ');

        if ($data['manufacturer_quantity'] > 0) {
            $data['quantity_return_per'] = $data['quantity_return'] / $data['manufacturer_quantity'] * 100;
        } else {
            $data['quantity_return_per'] = 0;
        }

        if ($data['total_price_tax_excl'] > 0) {
            $data['total_refund_tax_excl_per'] = $data['total_refund_tax_excl'] / $data['total_price_tax_excl'] * 100;
        } else {
            $data['total_refund_tax_excl_per'] = 0;
        }

        $margin = $data['total_price_tax_excl'] - $data['total_refund_tax_excl'] - $data['discount_prorata'] - $data['cost'];

        $list = [
            'from' => $from,
            'to' => $to,
            'manufacturer_quantity' => $data['manufacturer_quantity'],
            'total_price_tax_excl' => (($for_export) ? round($data['total_price_tax_excl'], 2) : self::displayPrice($data['total_price_tax_excl'])),
            'quantity_return' => $data['quantity_return'],
            'total_refund_tax_excl' => (($for_export) ? round($data['total_refund_tax_excl'], 2) : self::displayPrice($data['total_refund_tax_excl'])),
            'discount_prorata' => (($for_export) ? round($data['discount_prorata'], 2) : self::displayPrice($data['discount_prorata'])),
            'cost' => (($for_export) ? round($data['cost'], 2) : self::displayPrice($data['cost'])),
            'margin' => (($for_export) ? round($margin, 2) : self::displayPrice($margin)),
            'quantity_return_per' => round($data['quantity_return_per'], 2),
            'total_refund_tax_excl_per' => round($data['total_refund_tax_excl_per'], 2),
        ];

        return $list;
    }

    public static function getCompareTotalPaymentMethodsSales($from, $to, $payment_method, $id_group, $for_export = false)
    {
        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $p_payment_method = self::protectStringArraySQL($payment_method);

        $data = Db::getInstance()->getRow('
            SELECT IFNULL(SUM(IFNULL(op.`amount`, 0)/IFNULL(op.`conversion_rate`, 1)), 0) AS amount_tax_incl
            FROM `' . _DB_PREFIX_ . 'order_payment` op
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`reference` = op.`order_reference`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . self::reqLocationValid('o') . '
            LEFT JOIN `' . _DB_PREFIX_ . 'nts_config_payment_method` ntcpm ON op.`payment_method` = ntcpm.`payment_method`
            WHERE o.`valid` = 1
            ' . self::whereShop('o') . '
            ' . self::getWhereProfileCountrie('a') . '
            AND op.`date_add` BETWEEN "' . pSQL($from) . ' 00:00:00" AND "' . pSQL($to) . ' 23:59:59"
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . self::protectIntArraySQL($id_group) . ')
                )
            ' : '') . '
            ' . (($payment_method) ? ' AND (op.`payment_method` IN ("' . $p_payment_method . '") OR ntcpm.`display_name` IN ("' . $p_payment_method . '"))' : '') . '
        ');

        $list = [
            'from' => $from,
            'to' => $to,
            'amount_tax_incl' => (($for_export) ? round($data['amount_tax_incl'], 2) : self::displayPrice($data['amount_tax_incl'])),
        ];

        return $list;
    }

    public function getTotalCombinationsSales(
        $from,
        $to,
        $id_category,
        $id_manufacturer,
        $id_country_invoice,
        $id_product,
        $id_combination,
        $id_group,
        $id_feature,
        $id_feature_value,
        $simple,
        $for_export = false
    ) {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;
        $a_config = self::getConfig();

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $categories = [];

        if ($id_category) {
            foreach ($id_category as $id_cat) {
                $o_category = new Category($id_cat);
                $children = $o_category->getAllChildren();
                $categories[] = $id_cat;

                foreach ($children as $child) {
                    $categories[] = (int) $child->id;
                }
            }
        }

        $req_loc_valid_o = self::reqLocationValid('o');
        $req_loc_valid_o3 = self::reqLocationValid('o3');
        $req_date_valid_o = self::reqDateValid('o');
        $where_shop_o = self::whereShop('o');
        $where_profile_country_a = self::getWhereProfileCountrie('a');
        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $p_id_group = self::protectIntArraySQL($id_group);
        $p_id_product = self::protectIntArraySQL($id_product);
        $p_id_combination = self::protectIntArraySQL($id_combination);
        $p_categories = self::protectIntArraySQL($categories);
        $p_id_manufacturer = self::protectIntArraySQL($id_manufacturer);
        $p_id_country_invoice = self::protectIntArraySQL($id_country_invoice);
        $p_id_feature_value = self::protectIntArraySQL($id_feature_value);
        $p_id_feature = self::protectIntArraySQL($id_feature);

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_total_combinations_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_total_combinations_sales`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_total_combinations_sales` (
                `id_order_detail`   int(10)         UNSIGNED    NOT NULL,
                `cost`              decimal(20,6)               NOT NULL,
                `reference`         TEXT,
                PRIMARY KEY (`id_order_detail`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_product_total_combinations_sales` (
                `product_id`            int(10)         UNSIGNED    NOT NULL,
                `product_attribute_id`  int(10)         UNSIGNED    NOT NULL,
                `cost`                  decimal(20,6)               NOT NULL,
                PRIMARY KEY (`product_attribute_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_product_total_combinations_sales` (`product_id`, `product_attribute_id`, `cost`)
            SELECT t.`product_id`, t.`product_attribute_id`, t.`cost`
            FROM
            (
                SELECT od.`product_attribute_id`, od.`product_id`, (
                    CASE od.`original_wholesale_price`
                    WHEN 0
                    THEN od.`purchase_supplier_price`
                    ELSE od.`original_wholesale_price`
                    END
                ) AS cost
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                HAVING cost > 0
                ORDER BY o.`date_add` ASC
            ) t
            GROUP BY t.`product_attribute_id`
        ');

        $referense_select = 'od.`product_reference`';
        $reference_join = '';

        if ($a_config['group_product_reference']) {
            $referense_select = 'IFNULL(pa.`reference`, od.`product_reference`)';
            $reference_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa ON od.`product_id` = pa.`id_product` AND od.`product_attribute_id` = pa.`id_product_attribute`';
        }

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_total_combinations_sales` (`id_order_detail`, `cost`, `reference`)
            SELECT od.`id_order_detail`,
                (
                    IFNULL(
                        (
                            SELECT
                            CASE od.`original_wholesale_price`
                            WHEN 0
                            THEN
                                CASE od.`purchase_supplier_price`
                                WHEN 0
                                THEN (
                                    SELECT IFNULL(cpts.`cost`, 0)
                                    FROM `nts_cost_product_total_combinations_sales` cpts
                                    WHERE od.`product_id` = cpts.`product_id` AND od.`product_attribute_id` = cpts.`product_attribute_id`
                                )
                                ELSE od.`purchase_supplier_price`
                                END
                            ELSE od.`original_wholesale_price`
                            END
                        ), 0
                    ) * IFNULL(od.`product_quantity`, 0)
                ), IFNULL (' . $referense_select . ', "")
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                ' . $reference_join . '
                WHERE o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
        ');

        $data = Db::getInstance()->executeS('
            SELECT product_attribute_id, product_id, name,
                    SUM(quantity) AS quantity,
                    product_reference,
                    combination_reference,
                    SUM(product_quantity) AS product_quantity,
                    SUM(total_discounts_tax_excl) AS total_discounts_tax_excl,
                    SUM(total_products) AS total_products,
                    SUM(total_price_tax_excl) AS total_price_tax_excl,
                    SUM(quantity_return) AS quantity_return,
                    SUM(total_refund_tax_excl) AS total_refund_tax_excl,
                    SUM(order_total_refund_tax_excl) AS order_total_refund_tax_excl,
                    SUM(cost) AS cost
            FROM (
                SELECT DISTINCT od.`product_attribute_id`, od.`product_id`, IFNULL(od.`product_name`, "-") AS name,
                    IFNULL(sa.`quantity`, 0) AS quantity,
                    p.`reference` AS product_reference,
                    nt.`reference` AS combination_reference,
                    IFNULL(SUM(IFNULL(od.`product_quantity`, 0)), 0) AS product_quantity,
                    (IFNULL(o.`total_discounts_tax_excl`, 0) / IFNULL(o.`conversion_rate`, 1)) AS total_discounts_tax_excl,
                    (IFNULL(o.`total_products`, 0) / IFNULL(o.`conversion_rate`, 1)) AS total_products,
                    IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_price_tax_excl,
                    0 AS quantity_return,
                    0 AS total_refund_tax_excl,
                    0 AS order_total_refund_tax_excl,
                    IFNULL(SUM(nt.`cost`), 0) AS cost
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                JOIN `nts_cost_total_combinations_sales` nt ON nt.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
                LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON sa.`id_product` = od.`product_id`
                    ' . self::whereShopStockAvailable('sa') . ' AND sa.`id_product_attribute` = od.`product_attribute_id`
                WHERE o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                ' . (($id_combination) ? ' AND od.`product_attribute_id` IN (' . $p_id_combination . ')' : '') . '
                ' . ((!$simple) ? ' AND od.`product_attribute_id` <> 0' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_categories . ')
                    )
                ' : '') . '
                ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                    SELECT p7.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product` p7
                    WHERE p7.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                    )
                ' : '') . '
                ' . (($id_country_invoice) ? ' AND od.`id_order` IN(
                    SELECT o3.`id_order`
                    FROM `' . _DB_PREFIX_ . 'orders` o3
                    JOIN `' . _DB_PREFIX_ . 'address` a3 ON a3.`id_address` = ' . $req_loc_valid_o3 . '
                    WHERE a3.`id_country` IN (' . $p_id_country_invoice . ')
                    )
                ' : '') . '
                ' . (($id_feature) ? ' AND od.`product_id` IN(
                    SELECT fp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp
                    WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                    )
                ' : '') . '
                ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                    SELECT fp1.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                    WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                    )
                ' : '') . '
                GROUP BY od.`product_id`, od.`product_attribute_id`' . (($a_config['group_product_reference']) ? '' : ', od.`product_reference`') . '

                UNION

                SELECT DISTINCT od.`product_attribute_id`, od.`product_id`, IFNULL(od.`product_name`, "-") AS name,
                    0 AS quantity,
                    p.`reference` AS product_reference,
                    IFNULL (' . $referense_select . ', "") AS combination_reference,
                    0 AS product_quantity,
                    0 AS total_discounts_tax_excl,
                    0 AS total_products,
                    0 AS total_price_tax_excl,
                    IFNULL(SUM(IFNULL(ord.`product_quantity`, 0)), 0) AS quantity_return,
                    0 AS total_refund_tax_excl,
                    0 AS order_total_refund_tax_excl,
                    0 AS cost
                FROM `' . _DB_PREFIX_ . 'order_return_detail` ord
                JOIN `' . _DB_PREFIX_ . 'order_return` ore ON ord.`id_order_return` = ore.`id_order_return`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON ord.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = ore.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
                ' . $reference_join . '
                WHERE ore.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . self::reqReturnValid('ore') . '
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                ' . (($id_combination) ? ' AND od.`product_attribute_id` IN (' . $p_id_combination . ')' : '') . '
                ' . ((!$simple) ? ' AND od.`product_attribute_id` <> 0' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_categories . ')
                    )
                ' : '') . '
                ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                    SELECT p7.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product` p7
                    WHERE p7.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                    )
                ' : '') . '
                ' . (($id_country_invoice) ? ' AND od.`id_order` IN(
                    SELECT o3.`id_order`
                    FROM `' . _DB_PREFIX_ . 'orders` o3
                    JOIN `' . _DB_PREFIX_ . 'address` a3 ON a3.`id_address` = ' . $req_loc_valid_o3 . '
                    WHERE a3.`id_country` IN (' . $p_id_country_invoice . ')
                    )
                ' : '') . '
                ' . (($id_feature) ? ' AND od.`product_id` IN(
                    SELECT fp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp
                    WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                    )
                ' : '') . '
                ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                    SELECT fp1.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                    WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                    )
                ' : '') . '
                GROUP BY od.`product_id`, od.`product_attribute_id`' . (($a_config['group_product_reference']) ? '' : ', od.`product_reference`') . '

                UNION

                SELECT DISTINCT od.`product_attribute_id`, od.`product_id`, IFNULL(od.`product_name`, "-") AS name,
                    0 AS quantity,
                    p.`reference` AS product_reference,
                    IFNULL (' . $referense_select . ', "") AS combination_reference,
                    0 AS product_quantity,
                    0 AS total_discounts_tax_excl,
                    0 AS total_products,
                    0 AS total_price_tax_excl,
                    0 AS quantity_return,
                    IFNULL(SUM(IFNULL(osd.`total_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_refund_tax_excl,
                    IFNULL((IFNULL(os.`total_products_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS order_total_refund_tax_excl,
                    0 AS cost
                FROM `' . _DB_PREFIX_ . 'order_slip_detail` osd
                JOIN `' . _DB_PREFIX_ . 'order_slip` os ON osd.`id_order_slip` = os.`id_order_slip`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON osd.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = os.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = od.`product_id`
                ' . $reference_join . '
                WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                ' . (($id_combination) ? ' AND od.`product_attribute_id` IN (' . $p_id_combination . ')' : '') . '
                ' . ((!$simple) ? ' AND od.`product_attribute_id` <> 0' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_categories . ')
                    )
                ' : '') . '
                ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                    SELECT p7.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product` p7
                    WHERE p7.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                    )
                ' : '') . '
                ' . (($id_country_invoice) ? ' AND od.`id_order` IN(
                    SELECT o3.`id_order`
                    FROM `' . _DB_PREFIX_ . 'orders` o3
                    JOIN `' . _DB_PREFIX_ . 'address` a3 ON a3.`id_address` = ' . $req_loc_valid_o3 . '
                    WHERE a3.`id_country` IN (' . $p_id_country_invoice . ')
                    )
                ' : '') . '
                ' . (($id_feature) ? ' AND od.`product_id` IN(
                    SELECT fp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp
                    WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                    )
                ' : '') . '
                ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                    SELECT fp1.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                    WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                    )
                ' : '') . '
                GROUP BY od.`product_id`, od.`product_attribute_id`' . (($a_config['group_product_reference']) ? '' : ', od.`product_reference`') . '
            ) t
            GROUP BY product_id, product_attribute_id' . (($a_config['group_product_reference']) ? '' : ', combination_reference') . '
            ORDER BY combination_reference, name
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_total_combinations_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_total_combinations_sales`;
        ');

        $total_product_quantity = 0;
        $total_quantity = 0;
        $total_price_tax_excl = 0;
        $total_quantity_return = 0;
        $total_refund_tax_excl = 0;
        $total_quantity_return_per = 0;
        $total_refund_tax_excl_per = 0;
        $total_cost = 0;
        $total_margin = 0;
        $total_discount_prorata = 0;

        $total_cost_possible = true;

        foreach ($data as $dt) {
            // To force natural sorting by keys
            $key = Tools::strtolower(Tools::replaceAccentedChars($dt['combination_reference'] . '_' . $dt['name'] . '_' . $dt['product_id'] . '_' . $dt['product_attribute_id']));

            if ($dt['product_quantity'] > 0) {
                $dt['quantity_return_per'] = $dt['quantity_return'] / $dt['product_quantity'] * 100;
            } else {
                $dt['quantity_return_per'] = 0;
            }

            if ($dt['total_price_tax_excl'] > 0) {
                $dt['total_refund_tax_excl_per'] = $dt['total_refund_tax_excl'] / $dt['total_price_tax_excl'] * 100;
            } else {
                $dt['total_refund_tax_excl_per'] = 0;
            }

            $dt['margin'] = 0;
            $dt['unit_margin'] = 0;
            $dt['discount_prorata'] = 0;
            $dt['margin_per'] = 0;

            if ($dt['cost'] <= 0) {
                $total_cost_possible = false;
            } else {
                if ($dt['product_quantity'] > 0) {
                    $unit_cost = $dt['cost'] / $dt['product_quantity'];
                } else {
                    $unit_cost = 0;
                }

                $total_price_product = $dt['total_price_tax_excl'] - $dt['total_refund_tax_excl'];

                if ($total_price_product > 0) {
                    $nb_products_refunded = number_format($dt['total_refund_tax_excl'] / $total_price_product);
                } else {
                    $nb_products_refunded = 0;
                }

                $quantity_calc = $dt['product_quantity'] - $nb_products_refunded;

                if (($dt['total_products'] - $dt['order_total_refund_tax_excl']) > 0) {
                    $dt['discount_prorata'] = ($dt['total_discounts_tax_excl'] * $total_price_product) / ($dt['total_products'] - $dt['order_total_refund_tax_excl']);
                } else {
                    $dt['discount_prorata'] = 0;
                }

                $dt['margin'] = $total_price_product - $dt['discount_prorata'] - $dt['cost'];

                if ($quantity_calc > 0) {
                    $dt['unit_margin'] = $dt['margin'] / $quantity_calc;
                } else {
                    $dt['unit_margin'] = 0;
                }

                if (($total_price_product - $dt['discount_prorata']) > 0) {
                    $dt['margin_per'] = ($dt['margin'] / ($total_price_product - $dt['discount_prorata'])) * 100;
                }
            }

            $total_product_quantity += $dt['product_quantity'];
            $total_quantity += $dt['quantity'];
            $total_price_tax_excl += $dt['total_price_tax_excl'];
            $total_quantity_return += $dt['quantity_return'];
            $total_refund_tax_excl += $dt['total_refund_tax_excl'];
            $total_cost += $dt['cost'];
            $total_margin += $dt['margin'];
            $total_discount_prorata += $dt['discount_prorata'];

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export && $dt['name'] != '-') {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminProducts',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminProducts')],
                    false
                );

                $dt['combination_reference'] = '<a href="' . $admin_link . '&id_product=' . $dt['product_id']
                    . '&updateproduct" target="_blank">' . $dt['combination_reference'] . '</a>';
            }

            if (!$for_export) {
                $dt['name'] = wordwrap($dt['name'], 50, '<br />');
            }

            $list[$key] = [
                'reference' => $dt['combination_reference'],
                'product_reference' => $dt['product_reference'],
                'name' => $dt['name'],
                'product_quantity' => $dt['product_quantity'],
                'quantity' => $dt['quantity'],
                'need' => ($dt['quantity'] - $dt['product_quantity']) * -1,
                'total_price_tax_excl' => (($for_export) ? round($dt['total_price_tax_excl'], 2) : self::displayPrice($dt['total_price_tax_excl'])),
                'quantity_return' => $dt['quantity_return'],
                'total_refund_tax_excl' => (($for_export) ? round($dt['total_refund_tax_excl'], 2) : self::displayPrice($dt['total_refund_tax_excl'])),
                'quantity_return_per' => round($dt['quantity_return_per'], 2),
                'total_refund_tax_excl_per' => round($dt['total_refund_tax_excl_per'], 2),
                'cost' => ($dt['cost'] > 0) ? (($for_export) ? round($dt['cost'], 2) : self::displayPrice($dt['cost'])) : '-',
                'discount_prorata' => (($for_export) ? round($dt['discount_prorata'], 2) : self::displayPrice($dt['discount_prorata'])),
                'unit_margin' => ($dt['unit_margin'] > 0) ? (($for_export) ? round($dt['unit_margin'], 2) : self::displayPrice($dt['unit_margin'])) : '-',
                'margin' => ($dt['margin'] > 0) ? (($for_export) ? round($dt['margin'], 2) : self::displayPrice($dt['margin'])) : '-',
                'margin_per' => ($dt['margin_per'] > 0) ? round($dt['margin_per'], 2) : '-',
            ];
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        $clean_list = array_values($list);

        if ($total_product_quantity > 0) {
            $total_quantity_return_per = $total_quantity_return / $total_product_quantity * 100;
        }

        if ($total_price_tax_excl > 0) {
            $total_refund_tax_excl_per = $total_refund_tax_excl / $total_price_tax_excl * 100;
        }

        $clean_list[] = [
            'reference' => $this->l('Total'),
            'product_reference' => '',
            'name' => '',
            'product_quantity' => $total_product_quantity,
            'quantity' => $total_quantity,
            'need' => ($total_quantity - $total_product_quantity) * -1,
            'total_price_tax_excl' => (($for_export) ? round($total_price_tax_excl, 2) : self::displayPrice($total_price_tax_excl)),
            'quantity_return' => $total_quantity_return,
            'total_refund_tax_excl' => (($for_export) ? round($total_refund_tax_excl, 2) : self::displayPrice($total_refund_tax_excl)),
            'quantity_return_per' => round($total_quantity_return_per, 2),
            'total_refund_tax_excl_per' => round($total_refund_tax_excl_per, 2),
            'cost' => ($total_cost_possible) ? (($for_export) ? round($total_cost, 2) : self::displayPrice($total_cost)) : '-',
            'discount_prorata' => (($for_export) ? round($total_discount_prorata, 2) : self::displayPrice($total_discount_prorata)),
            'unit_margin' => '-',
            'margin' => ($total_cost_possible) ? (($for_export) ? round($total_margin, 2) : self::displayPrice($total_margin)) : '-',
            'margin_per' => ($total_cost_possible && (($total_price_tax_excl - $total_refund_tax_excl) - $total_discount_prorata) > 0) ? round(($total_margin / (($total_price_tax_excl - $total_refund_tax_excl) - $total_discount_prorata)) * 100, 2) : '-',
        ];

        return $clean_list;
    }

    public static function getCompareTotalCombinationsSales($from, $to, $id_product, $id_combination, $id_group, $id_category, $id_manufacturer, $id_feature, $id_feature_value, $simple, $for_export = false)
    {
        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $categories = [];

        if ($id_category) {
            foreach ($id_category as $id_cat) {
                $o_category = new Category($id_cat);
                $children = $o_category->getAllChildren();
                $categories[] = $id_cat;

                foreach ($children as $child) {
                    $categories[] = (int) $child->id;
                }
            }
        }

        $req_loc_valid_o = self::reqLocationValid('o');
        $req_date_valid_o = self::reqDateValid('o');
        $where_shop_o = self::whereShop('o');
        $where_profile_country_a = self::getWhereProfileCountrie('a');
        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $p_id_product = self::protectIntArraySQL($id_product);
        $p_id_combination = self::protectIntArraySQL($id_combination);
        $p_id_group = self::protectIntArraySQL($id_group);
        $p_categories = self::protectIntArraySQL($categories);
        $p_id_manufacturer = self::protectIntArraySQL($id_manufacturer);
        $p_id_feature = self::protectIntArraySQL($id_feature);
        $p_id_feature_value = self::protectIntArraySQL($id_feature_value);

        Db::getInstance()->execute('
            DROP VIEW IF EXISTS discount_prorata_cmp_combinations_sales
        ');

        Db::getInstance()->execute('
            CREATE OR REPLACE VIEW discount_prorata_cmp_combinations_sales AS
            SELECT product_id, id_order, id_order_detail, product_attribute_id,
                SUM(total_discounts_tax_excl) AS total_discounts_tax_excl,
                SUM(total_products) AS total_products,
                SUM(total_price_tax_excl) AS total_price_tax_excl,
                SUM(total_refund_tax_excl) AS total_refund_tax_excl,
                SUM(order_total_refund_tax_excl) AS order_total_refund_tax_excl
            FROM (
                SELECT od.`product_id`, od.`id_order`, od.`id_order_detail`, od.`product_attribute_id`,
                    (IFNULL(o.`total_discounts_tax_excl`, 0) / IFNULL(o.`conversion_rate`, 1)) AS total_discounts_tax_excl,
                    (IFNULL(o.`total_products`, 0) / IFNULL(o.`conversion_rate`, 1)) AS total_products,
                    IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_price_tax_excl,
                    0 AS total_refund_tax_excl,
                    0 AS order_total_refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                ' . (($id_combination) ? ' AND od.`product_attribute_id` IN (' . $p_id_combination . ')' : '') . '
                ' . ((!$simple) ? ' AND od.`product_attribute_id` <> 0' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_categories . ')
                    )
                ' : '') . '
                ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                    SELECT p.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product` p
                    WHERE p.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                    )
                ' : '') . '
                ' . (($id_feature) ? ' AND od.`product_id` IN(
                    SELECT fp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp
                    WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                    )
                ' : '') . '
                ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                    SELECT fp1.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                    WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                    )
                ' : '') . '
                GROUP BY od.`id_order`, od.`id_order_detail`

                UNION

                SELECT od.`product_id`, od.`id_order`, od.`id_order_detail`, od.`product_attribute_id`,
                    0 AS total_discounts_tax_excl,
                    0 AS total_products,
                    0 AS total_price_tax_excl,
                    IFNULL(SUM(IFNULL(osd.`total_price_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_refund_tax_excl,
                    IFNULL(IFNULL(os.`total_products_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1), 0) AS order_total_refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_slip_detail` osd
                JOIN `' . _DB_PREFIX_ . 'order_slip` os ON osd.`id_order_slip` = os.`id_order_slip`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON osd.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                ' . (($id_combination) ? ' AND od.`product_attribute_id` IN (' . $p_id_combination . ')' : '') . '
                ' . ((!$simple) ? ' AND od.`product_attribute_id` <> 0' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_categories . ')
                    )
                ' : '') . '
                ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                    SELECT p.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product` p
                    WHERE p.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                    )
                ' : '') . '
                ' . (($id_feature) ? ' AND od.`product_id` IN(
                    SELECT fp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp
                    WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                    )
                ' : '') . '
                ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                    SELECT fp1.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                    WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                    )
                ' : '') . '
                GROUP BY od.`id_order`, od.`id_order_detail`
            ) t
            GROUP BY id_order, id_order_detail
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_cmp_combinations_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_cmp_combinations_sales`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_cmp_combinations_sales` (
                `id_order_detail`   int(10)         UNSIGNED    NOT NULL,
                `cost`              decimal(20,6)               NOT NULL,
                PRIMARY KEY (`id_order_detail`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_product_cmp_combinations_sales` (
                `product_id`            int(10)         UNSIGNED    NOT NULL,
                `product_attribute_id`  int(10)         UNSIGNED    NOT NULL,
                `cost`                  decimal(20,6)               NOT NULL,
                PRIMARY KEY (`product_id`, `product_attribute_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_product_cmp_combinations_sales` (`product_id`, `product_attribute_id`, `cost`)
            SELECT t.`product_id`, t.`product_attribute_id`, t.`cost`
            FROM (
                    SELECT od.`product_attribute_id`, od.`product_id`, (
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN od.`purchase_supplier_price`
                        ELSE od.`original_wholesale_price`
                        END
                    ) AS cost
                    FROM `' . _DB_PREFIX_ . 'order_detail` od
                    JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
                    JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                    WHERE o.`valid` = 1
                    ' . $where_shop_o . '
                    ' . $where_profile_country_a . '
                    AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                    ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                    ' . (($id_combination) ? ' AND od.`product_attribute_id` IN (' . $p_id_combination . ')' : '') . '
                    ' . ((!$simple) ? ' AND od.`product_attribute_id` <> 0' : '') . '
                    ' . (($id_group) ? ' AND o.`id_customer` IN(
                        SELECT cg.`id_customer`
                        FROM `' . _DB_PREFIX_ . 'customer_group` cg
                        WHERE cg.`id_group` IN (' . $p_id_group . ')
                        )
                    ' : '') . '
                    ' . (($id_category) ? ' AND od.`product_id` IN(
                        SELECT cp.`id_product`
                        FROM `' . _DB_PREFIX_ . 'category_product` cp
                        WHERE cp.`id_category` IN (' . $p_categories . ')
                        )
                    ' : '') . '
                    ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                        SELECT p.`id_product`
                        FROM `' . _DB_PREFIX_ . 'product` p
                        WHERE p.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                        )
                    ' : '') . '
                    ' . (($id_feature) ? ' AND od.`product_id` IN(
                        SELECT fp.`id_product`
                        FROM `' . _DB_PREFIX_ . 'feature_product` fp
                        WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                        )
                    ' : '') . '
                    ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                        SELECT fp2.`id_product`
                        FROM `' . _DB_PREFIX_ . 'feature_product` fp2
                        WHERE fp2.`id_feature_value` IN (' . $p_id_feature_value . ')
                        )
                    ' : '') . '
                    HAVING cost > 0
                    ORDER BY o.`date_add` ASC
            ) t
            GROUP BY t.`product_id`
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_cmp_combinations_sales` (`id_order_detail`, `cost`)
            SELECT od.`id_order_detail`,
            (
                IFNULL(
                    (
                        SELECT
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN
                            CASE od.`purchase_supplier_price`
                            WHEN 0
                            THEN (
                                SELECT IFNULL(cpts.`cost`, 0)
                                FROM `nts_cost_product_cmp_combinations_sales` cpts
                                WHERE od.`product_id` = cpts.`product_id` AND od.`product_attribute_id` = cpts.`product_attribute_id`
                            )
                            ELSE od.`purchase_supplier_price`
                            END
                        ELSE od.`original_wholesale_price`
                        END
                    ), 0
                ) * IFNULL(od.`product_quantity`, 0)
            )
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            WHERE o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
            ' . (($id_combination) ? ' AND od.`product_attribute_id` IN (' . $p_id_combination . ')' : '') . '
            ' . ((!$simple) ? ' AND od.`product_attribute_id` <> 0' : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            ' . (($id_category) ? ' AND od.`product_id` IN(
                SELECT cp.`id_product`
                FROM `' . _DB_PREFIX_ . 'category_product` cp
                WHERE cp.`id_category` IN (' . $p_categories . ')
                )
            ' : '') . '
            ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                SELECT p.`id_product`
                FROM `' . _DB_PREFIX_ . 'product` p
                WHERE p.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                )
            ' : '') . '
            ' . (($id_feature) ? ' AND od.`product_id` IN(
                SELECT fp.`id_product`
                FROM `' . _DB_PREFIX_ . 'feature_product` fp
                WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                )
            ' : '') . '
            ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                SELECT fp2.`id_product`
                FROM `' . _DB_PREFIX_ . 'feature_product` fp2
                WHERE fp2.`id_feature_value` IN (' . $p_id_feature_value . ')
                )
            ' : '') . '
        ');

        $data = Db::getInstance()->getRow('
            SELECT SUM(product_quantity) AS product_quantity, SUM(total_price_tax_excl) AS total_price_tax_excl,
                SUM(quantity_return) AS quantity_return, SUM(total_refund_tax_excl) AS total_refund_tax_excl,
                SUM(discount_prorata) AS discount_prorata, SUM(cost) AS cost
            FROM (
                SELECT
                    IFNULL(SUM(IFNULL(od.`product_quantity`, 0)), 0) AS product_quantity,
                    IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_price_tax_excl,
                    0 AS quantity_return,
                    0 AS total_refund_tax_excl,
                    IFNULL(
                        (SELECT SUM((dp5.`total_discounts_tax_excl` * (dp5.`total_price_tax_excl` - dp5.`total_refund_tax_excl`)) /(dp5.`total_products` - dp5.`order_total_refund_tax_excl`))
                        FROM `discount_prorata_cmp_combinations_sales` dp5
                        JOIN `' . _DB_PREFIX_ . 'orders` o5 ON dp5.`id_order` = o5.`id_order`
                        JOIN `' . _DB_PREFIX_ . 'address` a5 ON a5.`id_address` = ' . self::reqLocationValid('o5') . '
                        WHERE o5.`valid` = 1
                        ' . self::whereShop('o5') . '
                        ' . self::getWhereProfileCountrie('a5') . '
                        AND ' . self::reqDateValid('o5') . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                        ' . (($id_product) ? ' AND dp5.`product_id` IN (' . $p_id_product . ')' : '') . '
                        ' . (($id_combination) ? ' AND dp5.`product_attribute_id` IN (' . $p_id_combination . ')' : '') . '
                        ' . ((!$simple) ? ' AND dp5.`product_attribute_id` <> 0' : '') . '
                        ' . (($id_group) ? ' AND o5.`id_customer` IN(
                            SELECT cg5.`id_customer`
                            FROM `' . _DB_PREFIX_ . 'customer_group` cg5
                            WHERE cg5.`id_group` IN (' . $p_id_group . ')
                            )
                        ' : '') . '
                        ' . (($id_category) ? ' AND dp5.`product_id` IN(
                            SELECT cp5.`id_product`
                            FROM `' . _DB_PREFIX_ . 'category_product` cp5
                            WHERE cp5.`id_category` IN (' . $p_categories . ')
                            )
                        ' : '') . '
                        ' . (($id_manufacturer) ? ' AND dp5.`product_id` IN(
                            SELECT p5.`id_product`
                            FROM `' . _DB_PREFIX_ . 'product` p5
                            WHERE p5.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                            )
                        ' : '') . '
                        ' . (($id_feature) ? ' AND dp5.`product_id` IN(
                            SELECT fp5.`id_product`
                            FROM `' . _DB_PREFIX_ . 'feature_product` fp5
                            WHERE fp5.`id_feature` IN (' . $p_id_feature . ')
                            )
                        ' : '') . '
                        ' . (($id_feature_value) ? ' AND dp5.`product_id` IN(
                            SELECT fp6.`id_product`
                            FROM `' . _DB_PREFIX_ . 'feature_product` fp6
                            WHERE fp6.`id_feature_value` IN (' . $p_id_feature_value . ')
                            )
                        ' : '') . '
                    ), 0) AS discount_prorata,
                    IFNULL(SUM(nt.`cost`), 0) AS cost
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                JOIN `nts_cost_cmp_combinations_sales` nt ON nt.`id_order_detail` = od.`id_order_detail`
                WHERE o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                AND ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                ' . (($id_combination) ? ' AND od.`product_attribute_id` IN (' . $p_id_combination . ')' : '') . '
                ' . ((!$simple) ? ' AND od.`product_attribute_id` <> 0' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_categories . ')
                    )
                ' : '') . '
                ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                    SELECT p.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product` p
                    WHERE p.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                    )
                ' : '') . '
                ' . (($id_feature) ? ' AND od.`product_id` IN(
                    SELECT fp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp
                    WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                    )
                ' : '') . '
                ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                    SELECT fp1.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                    WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                    )
                ' : '') . '

                UNION

                SELECT
                    0 AS product_quantity,
                    0 AS total_price_tax_excl,
                    IFNULL(SUM(IFNULL(ord.`product_quantity`, 0)), 0) AS quantity_return,
                    0 AS total_refund_tax_excl,
                    0 AS discount_prorata,
                    0 AS cost
                FROM `' . _DB_PREFIX_ . 'order_return_detail` ord
                JOIN `' . _DB_PREFIX_ . 'order_return` ore ON ore.`id_order_return` = ord.`id_order_return`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON od.`id_order_detail` = ord.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE ore.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . self::reqReturnValid('ore') . '
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                ' . (($id_combination) ? ' AND od.`product_attribute_id` IN (' . $p_id_combination . ')' : '') . '
                ' . ((!$simple) ? ' AND od.`product_attribute_id` <> 0' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_categories . ')
                    )
                ' : '') . '
                ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                    SELECT p.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product` p
                    WHERE p.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                    )
                ' : '') . '
                ' . (($id_feature) ? ' AND od.`product_id` IN(
                    SELECT fp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp
                    WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                    )
                ' : '') . '
                ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                    SELECT fp1.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                    WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                    )
                ' : '') . '

                UNION

                SELECT
                    0 AS product_quantity,
                    0 AS total_price_tax_excl,
                    0 AS quantity_return,
                    IFNULL(SUM(IFNULL(osd.`total_price_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_refund_tax_excl,
                    0 AS discount_prorata,
                    0 AS cost
                FROM `' . _DB_PREFIX_ . 'order_slip_detail` osd
                JOIN `' . _DB_PREFIX_ . 'order_slip` os ON os.`id_order_slip` = osd.`id_order_slip`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON od.`id_order_detail` = osd.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_product) ? ' AND od.`product_id` IN (' . $p_id_product . ')' : '') . '
                ' . (($id_combination) ? ' AND od.`product_attribute_id` IN (' . $p_id_combination . ')' : '') . '
                ' . ((!$simple) ? ' AND od.`product_attribute_id` <> 0' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                ' . (($id_category) ? ' AND od.`product_id` IN(
                    SELECT cp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'category_product` cp
                    WHERE cp.`id_category` IN (' . $p_categories . ')
                    )
                ' : '') . '
                ' . (($id_manufacturer) ? ' AND od.`product_id` IN(
                    SELECT p.`id_product`
                    FROM `' . _DB_PREFIX_ . 'product` p
                    WHERE p.`id_manufacturer` IN (' . $p_id_manufacturer . ')
                    )
                ' : '') . '
                ' . (($id_feature) ? ' AND od.`product_id` IN(
                    SELECT fp.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp
                    WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                    )
                ' : '') . '
                ' . (($id_feature_value) ? ' AND od.`product_id` IN(
                    SELECT fp1.`id_product`
                    FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                    WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                    )
                ' : '') . '
            ) t
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_cmp_combinations_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_cmp_combinations_sales`;
        ');

        Db::getInstance()->execute('
            DROP VIEW IF EXISTS discount_prorata_cmp_combinations_sales
        ');

        if ($data['product_quantity'] > 0) {
            $data['quantity_return_per'] = $data['quantity_return'] / $data['product_quantity'] * 100;
        } else {
            $data['quantity_return_per'] = 0;
        }

        if ($data['total_price_tax_excl'] > 0) {
            $data['total_refund_tax_excl_per'] = $data['total_refund_tax_excl'] / $data['total_price_tax_excl'] * 100;
        } else {
            $data['total_refund_tax_excl_per'] = 0;
        }

        $margin = $data['total_price_tax_excl'] - $data['total_refund_tax_excl'] - $data['cost'] - $data['discount_prorata'];

        $list = [
            'from' => $from,
            'to' => $to,
            'product_quantity' => $data['product_quantity'],
            'total_price_tax_excl' => (($for_export) ? round($data['total_price_tax_excl'], 2) : self::displayPrice($data['total_price_tax_excl'])),
            'quantity_return' => $data['quantity_return'],
            'total_refund_tax_excl' => (($for_export) ? round($data['total_refund_tax_excl'], 2) : self::displayPrice($data['total_refund_tax_excl'])),
            'quantity_return_per' => round($data['quantity_return_per'], 2),
            'total_refund_tax_excl_per' => round($data['total_refund_tax_excl_per'], 2),
            'cost' => (($for_export) ? round($data['cost'], 2) : self::displayPrice($data['cost'])),
            'discount_prorata' => (($for_export) ? round($data['discount_prorata'], 2) : self::displayPrice($data['discount_prorata'])),
            'margin' => (($for_export) ? round($margin, 2) : self::displayPrice($margin)),
        ];

        return $list;
    }

    public function getTotalCountriesSales($from, $to, $id_country, $id_group, $for_export = false)
    {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $req_loc_valid_o = self::reqLocationValid('o');
        $req_date_valid_o = self::reqDateValid('o');
        $where_shop_o = self::whereShop('o');
        $where_profile_country_a = self::getWhereProfileCountrie('a');
        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $p_id_country = self::protectIntArraySQL($id_country);
        $p_id_group = self::protectIntArraySQL($id_group);

        // SUM on id_order to force a result and prevent a false result
        $total_country = Db::getInstance()->getRow('
            SELECT IFNULL(SUM(IFNULL(od.`product_quantity`, 0)), 0) AS product_quantity,
                COUNT(DISTINCT o.`id_customer`) AS nb_customer
            FROM `' . _DB_PREFIX_ . 'orders` o
            JOIN `' . _DB_PREFIX_ . 'order_detail` od ON o.`id_order` = od.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            JOIN `' . _DB_PREFIX_ . 'country` c ON c.`id_country` = a.`id_country`
            JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON cl.`id_country` = c.`id_country`
                AND cl.`id_lang` = ' . $id_lang . '
            WHERE ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            AND o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            ' . (($id_country) ? ' AND a.`id_country` IN (' . $p_id_country . ')' : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_total_countries_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_total_countries_sales`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_total_countries_sales` (
                `id_order_detail`   int(10)         UNSIGNED    NOT NULL,
                `id_order`          int(10)         UNSIGNED    NOT NULL,
                `cost`              decimal(20,6)               NOT NULL,
                PRIMARY KEY (`id_order_detail`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_product_total_countries_sales` (
                `product_id`    int(10)         UNSIGNED    NOT NULL,
                `cost`          decimal(20,6)               NOT NULL,
                PRIMARY KEY (`product_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_product_total_countries_sales` (`product_id`, `cost`)
            SELECT t.`product_id`, t.`cost`
            FROM (
                    SELECT od.`product_id`, (
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN od.`purchase_supplier_price`
                        ELSE od.`original_wholesale_price`
                        END
                    ) AS cost
                    FROM `' . _DB_PREFIX_ . 'order_detail` od
                    JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
                    JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                    WHERE ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                    AND o.`valid` = 1
                    ' . $where_shop_o . '
                    ' . $where_profile_country_a . '
                    ' . (($id_group) ? ' AND o.`id_customer` IN(
                        SELECT cg.`id_customer`
                        FROM `' . _DB_PREFIX_ . 'customer_group` cg
                        WHERE cg.`id_group` IN (' . $p_id_group . ')
                        )
                    ' : '') . '
                    HAVING cost > 0
                    ORDER BY o.`date_add` ASC
            ) t
            GROUP BY t.`product_id`
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_total_countries_sales` (`id_order_detail`, `id_order`, `cost`)
            SELECT od.`id_order_detail`, od.`id_order`,
                (
                    IFNULL(
                        (
                            SELECT
                            CASE od.`original_wholesale_price`
                            WHEN 0
                            THEN
                                CASE od.`purchase_supplier_price`
                                WHEN 0
                                THEN (
                                    SELECT IFNULL(cpts.`cost`, 0)
                                    FROM `nts_cost_product_total_countries_sales` cpts
                                    WHERE od.`product_id` = cpts.`product_id`
                                )
                                ELSE od.`purchase_supplier_price`
                                END
                            ELSE od.`original_wholesale_price`
                            END
                        ), 0
                    ) * IFNULL(od.`product_quantity`, 0)
                )
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            WHERE ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            AND o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
        ');

        $data = Db::getInstance()->executeS('
            SELECT country, id_country, SUM(nb_order) AS nb_order, SUM(total_products_tax_excl) AS total_products_tax_excl,
                SUM(total_products_tax_incl) AS total_products_tax_incl,
                SUM(total_shipping_tax_excl) AS total_shipping_tax_excl,
                SUM(total_discounts_tax_excl) AS total_discounts_tax_excl,
                SUM(total_discounts_tax_incl) AS total_discounts_tax_incl,
                SUM(total_shipping_refund_tax_excl) AS total_shipping_refund_tax_excl,
                SUM(total_product_refund_tax_excl) AS total_product_refund_tax_excl,
                SUM(total_product_refund_tax_incl) AS total_product_refund_tax_incl,
                SUM(quantity_sold) AS quantity_sold, SUM(quantity_return) AS quantity_return,
                SUM(nb_customer) AS nb_customer, SUM(cost) AS cost
            FROM (
                SELECT cl.`name` AS country, c.`id_country` AS id_country,
                    count(o.`id_order`) AS nb_order,
                    IFNULL(SUM(IFNULL(o.`total_products`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_products_tax_excl,
                    IFNULL(SUM(IFNULL(o.`total_products_wt`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_products_tax_incl,
                    IFNULL(SUM(IFNULL(o.`total_shipping_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_shipping_tax_excl,
                    IFNULL(SUM(IFNULL(o.`total_discounts_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_discounts_tax_excl,
                    IFNULL(SUM(IFNULL(o.`total_discounts_tax_incl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_discounts_tax_incl,
                    0 AS total_shipping_refund_tax_excl,
                    0 AS total_product_refund_tax_excl,
                    0 AS total_product_refund_tax_incl,
                    IFNULL(
                        (
                            SELECT IFNULL(SUM(IFNULL(od4.`product_quantity`, 0)), 0)
                            FROM `' . _DB_PREFIX_ . 'order_detail` od4
                            JOIN `' . _DB_PREFIX_ . 'orders` o4 ON o4.`id_order` = od4.`id_order`
                            JOIN `' . _DB_PREFIX_ . 'address` a4 ON a4.`id_address` = ' . self::reqLocationValid('o4') . '
                            WHERE ' . self::reqDateValid('o4') . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                            AND o4.`valid` = 1
                            ' . self::whereShop('o4') . '
                            ' . self::getWhereProfileCountrie('a4') . '
                            ' . (($id_group) ? ' AND o4.`id_customer` IN(
                                SELECT cg4.`id_customer`
                                FROM `' . _DB_PREFIX_ . 'customer_group` cg4
                                WHERE cg4.`id_group` IN (' . $p_id_group . ')
                                )
                            ' : '') . '
                            AND a4.`id_country` = c.`id_country`
                        )
                    , 0) AS quantity_sold,
                    0 AS quantity_return,
                    IFNULL(
                        (
                            SELECT COUNT(DISTINCT o9.`id_customer`)
                            FROM `' . _DB_PREFIX_ . 'orders` o9
                            JOIN `' . _DB_PREFIX_ . 'address` a9 ON a9.`id_address` = ' . self::reqLocationValid('o9') . '
                            JOIN `' . _DB_PREFIX_ . 'country` c9 ON c9.`id_country` = a9.`id_country`
                            JOIN `' . _DB_PREFIX_ . 'country_lang` cl9 ON cl9.`id_country` = c9.`id_country`
                                AND cl9.`id_lang` = ' . $id_lang . '
                            WHERE ' . self::reqDateValid('o9') . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                            AND o9.`valid` = 1
                            ' . self::whereShop('o9') . '
                            ' . self::getWhereProfileCountrie('a9') . '
                            ' . (($id_group) ? ' AND o9.`id_customer` IN(
                                SELECT cg9.`id_customer`
                                FROM `' . _DB_PREFIX_ . 'customer_group` cg9
                                WHERE cg9.`id_group` IN (' . $p_id_group . ')
                                )
                            ' : '') . '
                            AND a9.`id_country` = c.`id_country`
                        )
                    , 0) AS nb_customer,
                    (
                        SELECT SUM(nt.`cost`)
                        FROM `nts_cost_total_countries_sales` nt
                        JOIN `' . _DB_PREFIX_ . 'orders` o10 ON o10.`id_order` = nt.`id_order`
                        JOIN `' . _DB_PREFIX_ . 'address` a10 ON a10.`id_address` = ' . self::reqLocationValid('o10') . '
                        WHERE a10.`id_country` = c.`id_country`
                    ) AS cost
                FROM `' . _DB_PREFIX_ . 'orders` o
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                JOIN `' . _DB_PREFIX_ . 'country` c ON c.`id_country` = a.`id_country`
                JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON cl.`id_country` = c.`id_country`
                    AND cl.`id_lang` = ' . $id_lang . '
                WHERE ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                AND o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_country) ? ' AND a.`id_country` IN (' . $p_id_country . ')' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY a.`id_country`

                UNION

                SELECT cl.`name` AS country, c.`id_country` AS id_country,
                    0 AS nb_order,
                    0 AS total_products_tax_excl,
                    0 AS total_products_tax_incl,
                    0 AS total_shipping_tax_excl,
                    0 AS total_discounts_tax_excl,
                    0 AS total_discounts_tax_incl,
                    IFNULL(SUM((IFNULL(os.`total_shipping_tax_excl`, 0))/IFNULL(os.`conversion_rate`, 1)), 0) AS total_shipping_refund_tax_excl,
                    IFNULL(SUM((IFNULL(os.`total_products_tax_excl`, 0))/IFNULL(os.`conversion_rate`, 1)), 0) AS total_product_refund_tax_excl,
                    IFNULL(SUM((IFNULL(os.`total_products_tax_incl`, 0))/IFNULL(os.`conversion_rate`, 1)), 0) AS total_product_refund_tax_incl,
                    0 AS quantity_sold,
                    0 AS quantity_return,
                    0 AS nb_customer,
                    0 AS cost
                FROM `' . _DB_PREFIX_ . 'order_slip` os
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = os.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                JOIN `' . _DB_PREFIX_ . 'country` c ON c.`id_country` = a.`id_country`
                JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON cl.`id_country` = c.`id_country`
                    AND cl.`id_lang` = ' . $id_lang . '
                WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_country) ? ' AND a.`id_country` IN (' . $p_id_country . ')' : '') . '
                ' . (($id_group) ? ' AND os.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY a.`id_country`

                UNION

                SELECT cl.`name` AS country, c.`id_country` AS id_country,
                    0 AS nb_order,
                    0 AS total_products_tax_excl,
                    0 AS total_products_tax_incl,
                    0 AS total_shipping_tax_excl,
                    0 AS total_discounts_tax_excl,
                    0 AS total_discounts_tax_incl,
                    0 AS total_shipping_refund_tax_excl,
                    0 AS total_product_refund_tax_excl,
                    0 AS total_product_refund_tax_incl,
                    0 AS quantity_sold,
                    IFNULL(SUM(IFNULL(ord.`product_quantity`, 0)), 0) AS quantity_return,
                    0 AS nb_customer,
                    0 AS cost
                FROM `' . _DB_PREFIX_ . 'order_return_detail` ord
                JOIN `' . _DB_PREFIX_ . 'order_return` ore ON ord.`id_order_return` = ore.`id_order_return`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON ord.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                JOIN `' . _DB_PREFIX_ . 'country` c ON c.`id_country` = a.`id_country`
                JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON cl.`id_country` = c.`id_country`
                    AND cl.`id_lang` = ' . $id_lang . '
                WHERE ore.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . self::reqReturnValid('ore') . '
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_country) ? ' AND a.`id_country` IN (' . $p_id_country . ')' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY a.`id_country`
            ) t
            GROUP BY id_country
            ORDER BY country
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_total_countries_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_total_countries_sales`;
        ');

        $total_nb_order = 0;
        $total_products_tax_excl = 0;
        $total_shipping_tax_excl = 0;
        $total_shipping_refund_tax_excl = 0;
        $total_product_refund_tax_excl = 0;
        $total_discounts_tax_excl = 0;
        $total_cost = 0;
        $total_margin = 0;
        $total_average_cart = 0;
        $total_quantity_return = 0;
        $total_quantity_return_per = 0;
        $total_taxes = 0;

        foreach ($data as $dt) {
            if ($dt['nb_order'] > 0) {
                $average_cart = ($dt['total_products_tax_excl'] - $dt['total_discounts_tax_excl']) / $dt['nb_order'];
            } else {
                $average_cart = 0;
            }

            if ($dt['quantity_sold'] > 0) {
                $dt['quantity_return_per'] = $dt['quantity_return'] / $dt['quantity_sold'] * 100;
            } else {
                $dt['quantity_return_per'] = 0;
            }

            if ($total_country['product_quantity'] > 0) {
                $dt['quantity_sold_per'] = $dt['quantity_sold'] / $total_country['product_quantity'] * 100;
            } else {
                $dt['quantity_sold_per'] = 0;
            }

            if ($total_country['nb_customer'] > 0) {
                $dt['nb_customer_per'] = ($dt['nb_customer'] / $total_country['nb_customer']) * 100;
            } else {
                $dt['nb_customer_per'] = 0;
            }

            $sales = $dt['total_products_tax_excl'] - $dt['total_product_refund_tax_excl'] - $dt['total_discounts_tax_excl'];
            $sale_ti = $dt['total_products_tax_incl'] - $dt['total_product_refund_tax_incl'] - $dt['total_discounts_tax_incl'];
            $taxes = ($sale_ti - $sales);

            if ($dt['cost'] > 0) {
                $dt['margin'] = $sales - $dt['cost'];
            } else {
                $dt['margin'] = 0;
            }

            $total_nb_order += $dt['nb_order'];
            $total_products_tax_excl += $dt['total_products_tax_excl'];
            $total_shipping_tax_excl += $dt['total_shipping_tax_excl'];
            $total_shipping_refund_tax_excl += $dt['total_shipping_refund_tax_excl'];
            $total_product_refund_tax_excl += $dt['total_product_refund_tax_excl'];
            $total_discounts_tax_excl += $dt['total_discounts_tax_excl'];
            $total_cost += $dt['cost'];
            $total_margin += $dt['margin'];
            $total_quantity_return += $dt['quantity_return'];
            $total_quantity_return_per += $dt['quantity_return_per'];
            $total_taxes += $taxes;

            $list[] = [
                'country' => $dt['country'],
                'nb_order' => $dt['nb_order'],
                'quantity_sold' => $dt['quantity_sold'],
                'quantity_sold_per' => round($dt['quantity_sold_per'], 2),
                'quantity_return' => $dt['quantity_return'],
                'quantity_return_per' => round($dt['quantity_return_per'], 2),
                'nb_customer' => $dt['nb_customer'],
                'nb_customer_per' => round($dt['nb_customer_per'], 2),
                'total_products_tax_excl' => (($for_export) ? round($dt['total_products_tax_excl'], 2) : self::displayPrice($dt['total_products_tax_excl'])),
                'total_shipping_tax_excl' => (($for_export) ? round($dt['total_shipping_tax_excl'], 2) : self::displayPrice($dt['total_shipping_tax_excl'])),
                'total_shipping_refund_tax_excl' => (($for_export) ? round($dt['total_shipping_refund_tax_excl'], 2) : self::displayPrice($dt['total_shipping_refund_tax_excl'])),
                'total_product_refund_tax_excl' => (($for_export) ? round($dt['total_product_refund_tax_excl'], 2) : self::displayPrice($dt['total_product_refund_tax_excl'])),
                'total_discounts_tax_excl' => (($for_export) ? round($dt['total_discounts_tax_excl'], 2) : self::displayPrice($dt['total_discounts_tax_excl'])),
                'cost' => (($for_export) ? round($dt['cost'], 2) : self::displayPrice($dt['cost'])),
                'margin' => (($for_export) ? round($dt['margin'], 2) : self::displayPrice($dt['margin'])),
                'sales' => (($for_export) ? round($sales, 2) : self::displayPrice($sales)),
                'taxes' => (($for_export) ? round($taxes, 2) : self::displayPrice($taxes)),
                'average_cart' => (($for_export) ? round($average_cart, 2) : self::displayPrice($average_cart)),
            ];
        }

        if ($total_nb_order > 0) {
            $total_average_cart = ($total_products_tax_excl - $total_discounts_tax_excl) / $total_nb_order;
        }

        if ($total_country['product_quantity'] > 0) {
            $total_quantity_return_per = $total_quantity_return / $total_country['product_quantity'] * 100;
        } else {
            $total_quantity_return_per = 0;
        }

        $total_sales = $total_products_tax_excl - $total_product_refund_tax_excl - $total_discounts_tax_excl;

        $list[] = [
            'country' => $this->l('Total'),
            'nb_order' => $total_nb_order,
            'quantity_sold' => $total_country['product_quantity'],
            'quantity_sold_per' => '100',
            'quantity_return' => $total_quantity_return,
            'quantity_return_per' => round($total_quantity_return_per, 2),
            'nb_customer' => $total_country['nb_customer'],
            'nb_customer_per' => '100',
            'total_products_tax_excl' => (($for_export) ? round($total_products_tax_excl, 2) : self::displayPrice($total_products_tax_excl)),
            'total_shipping_tax_excl' => (($for_export) ? round($total_shipping_tax_excl, 2) : self::displayPrice($total_shipping_tax_excl)),
            'total_shipping_refund_tax_excl' => (($for_export) ? round($total_shipping_refund_tax_excl, 2) : self::displayPrice($total_shipping_refund_tax_excl)),
            'total_product_refund_tax_excl' => (($for_export) ? round($total_product_refund_tax_excl, 2) : self::displayPrice($total_product_refund_tax_excl)),
            'total_discounts_tax_excl' => (($for_export) ? round($total_discounts_tax_excl, 2) : self::displayPrice($total_discounts_tax_excl)),
            'cost' => (($for_export) ? round($total_cost, 2) : self::displayPrice($total_cost)),
            'margin' => (($for_export) ? round($total_margin, 2) : self::displayPrice($total_margin)),
            'sales' => (($for_export) ? round($total_sales, 2) : self::displayPrice($total_sales)),
            'taxes' => (($for_export) ? round($total_taxes, 2) : self::displayPrice($total_taxes)),
            'average_cart' => (($for_export) ? round($total_average_cart, 2) : self::displayPrice($total_average_cart)),
        ];

        return $list;
    }

    public static function getCompareTotalCountriesSales($from, $to, $id_country, $id_group, $for_export = false)
    {
        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $req_date_valid_o = self::reqDateValid('o');
        $req_loc_valid_o = self::reqLocationValid('o');
        $where_shop_o = self::whereShop('o');
        $where_profile_country_a = self::getWhereProfileCountrie('a');
        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $p_id_group = self::protectIntArraySQL($id_group);
        $p_id_country = self::protectIntArraySQL($id_country);

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_cmp_total_countries_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_cmp_total_countries_sales`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_cmp_total_countries_sales` (
                `id_order_detail`   int(10)         UNSIGNED    NOT NULL,
                `id_order`          int(10)         UNSIGNED    NOT NULL,
                `cost`              decimal(20,6)               NOT NULL,
                PRIMARY KEY (`id_order_detail`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_cost_product_cmp_total_countries_sales` (
                `product_id`    int(10)         UNSIGNED    NOT NULL,
                `cost`          decimal(20,6)               NOT NULL,
                PRIMARY KEY (`product_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_product_cmp_total_countries_sales` (`product_id`, `cost`)
            SELECT t.`product_id`, t.`cost`
            FROM (
                    SELECT od.`product_id`, (
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN od.`purchase_supplier_price`
                        ELSE od.`original_wholesale_price`
                        END
                    ) AS cost
                    FROM `' . _DB_PREFIX_ . 'order_detail` od
                    JOIN `' . _DB_PREFIX_ . 'orders` o ON od.`id_order` = o.`id_order`
                    JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                    WHERE ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                    AND o.`valid` = 1
                    ' . $where_shop_o . '
                    ' . $where_profile_country_a . '
                    ' . (($id_group) ? ' AND o.`id_customer` IN(
                        SELECT cg.`id_customer`
                        FROM `' . _DB_PREFIX_ . 'customer_group` cg
                        WHERE cg.`id_group` IN (' . $p_id_group . ')
                        )
                    ' : '') . '
                    HAVING cost > 0
                    ORDER BY o.`date_add` ASC
            ) t
            GROUP BY t.`product_id`
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_cost_cmp_total_countries_sales` (`id_order_detail`, `id_order`, `cost`)
            SELECT od.`id_order_detail`, od.`id_order`,
            (
                IFNULL(
                    (
                        SELECT
                        CASE od.`original_wholesale_price`
                        WHEN 0
                        THEN
                            CASE od.`purchase_supplier_price`
                            WHEN 0
                            THEN (
                                SELECT IFNULL(cpts.`cost`, 0)
                                FROM `nts_cost_product_cmp_total_countries_sales` cpts
                                WHERE od.`product_id` = cpts.`product_id`
                            )
                            ELSE od.`purchase_supplier_price`
                            END
                        ELSE od.`original_wholesale_price`
                        END
                    ), 0
                ) * IFNULL(od.`product_quantity`, 0)
            )
            FROM `' . _DB_PREFIX_ . 'order_detail` od
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            WHERE ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            AND o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            ' . (($id_country) ? ' AND a.`id_country` IN (' . $p_id_country . ')' : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
        ');

        $data = Db::getInstance()->getRow('
            SELECT SUM(nb_order) AS nb_order, SUM(total_products_tax_excl) AS total_products_tax_excl,
                SUM(total_products_tax_incl) AS total_products_tax_incl,
                SUM(total_shipping_tax_excl) AS total_shipping_tax_excl,
                SUM(total_discounts_tax_excl) AS total_discounts_tax_excl,
                SUM(total_discounts_tax_incl) AS total_discounts_tax_incl,
                SUM(total_shipping_refund_tax_excl) AS total_shipping_refund_tax_excl,
                SUM(total_product_refund_tax_excl) AS total_product_refund_tax_excl,
                SUM(total_product_refund_tax_incl) AS total_product_refund_tax_incl,
                SUM(cost) AS cost
            FROM (
                SELECT
                    count(o.`id_order`) AS nb_order,
                    IFNULL(SUM(IFNULL(o.`total_products`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_products_tax_excl,
                    IFNULL(SUM(IFNULL(o.`total_products_wt`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_products_tax_incl,
                    IFNULL(SUM(IFNULL(o.`total_shipping_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_shipping_tax_excl,
                    IFNULL(SUM(IFNULL(o.`total_discounts_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_discounts_tax_excl,
                    IFNULL(SUM(IFNULL(o.`total_discounts_tax_incl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_discounts_tax_incl,
                    0 AS total_shipping_refund_tax_excl,
                    0 AS total_product_refund_tax_excl,
                    0 AS total_product_refund_tax_incl,
                    (
                        SELECT IFNULL(SUM(nt4.`cost`), 0)
                        FROM `nts_cost_cmp_total_countries_sales` nt4
                        JOIN `' . _DB_PREFIX_ . 'orders` o4 ON o4.`id_order` = nt4.`id_order`
                    ) AS cost
                FROM `' . _DB_PREFIX_ . 'orders` o
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                JOIN `' . _DB_PREFIX_ . 'country` c ON c.`id_country` = a.`id_country`
                WHERE ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                AND o.`valid` = 1
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_country) ? ' AND a.`id_country` IN (' . $p_id_country . ')' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '

                UNION

                SELECT
                    0 AS nb_order,
                    0 AS total_products_tax_excl,
                    0 AS total_products_tax_incl,
                    0 AS total_shipping_tax_excl,
                    0 AS total_discounts_tax_excl,
                    0 AS total_discounts_tax_incl,
                    IFNULL(SUM(IFNULL(os.`total_shipping_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_shipping_refund_tax_excl,
                    IFNULL(SUM(IFNULL(os.`total_products_tax_excl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_product_refund_tax_excl,
                    IFNULL(SUM(IFNULL(os.`total_products_tax_incl`, 0)/IFNULL(os.`conversion_rate`, 1)), 0) AS total_product_refund_tax_incl,
                    0 AS cost
                FROM `' . _DB_PREFIX_ . 'order_slip` os
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = os.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                JOIN `' . _DB_PREFIX_ . 'country` c ON c.`id_country` = a.`id_country`
                WHERE os.`date_add` BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
                ' . $where_shop_o . '
                ' . $where_profile_country_a . '
                ' . (($id_country) ? ' AND a.`id_country` IN (' . $p_id_country . ')' : '') . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
            ) t
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_cmp_total_countries_sales`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_cost_product_cmp_total_countries_sales`;
        ');

        if ($data['nb_order'] > 0) {
            $average_cart = ($data['total_products_tax_excl'] - $data['total_discounts_tax_excl']) / $data['nb_order'];
        } else {
            $average_cart = 0;
        }

        $sales = $data['total_products_tax_excl'] - $data['total_product_refund_tax_excl'] - $data['total_discounts_tax_excl'];
        $sales_ti = $data['total_products_tax_incl'] - $data['total_product_refund_tax_incl'] - $data['total_discounts_tax_incl'];
        $taxes = $sales_ti - $sales;
        $margin = $sales - $data['cost'];

        $list = [
            'from' => $from,
            'to' => $to,
            'nb_order' => $data['nb_order'],
            'total_products_tax_excl' => (($for_export) ? round($data['total_products_tax_excl'], 2) : self::displayPrice($data['total_products_tax_excl'])),
            'total_shipping_tax_excl' => (($for_export) ? round($data['total_shipping_tax_excl'], 2) : self::displayPrice($data['total_shipping_tax_excl'])),
            'total_shipping_refund_tax_excl' => (($for_export) ? round($data['total_shipping_refund_tax_excl'], 2) : self::displayPrice($data['total_shipping_refund_tax_excl'])),
            'total_product_refund_tax_excl' => (($for_export) ? round($data['total_product_refund_tax_excl'], 2) : self::displayPrice($data['total_product_refund_tax_excl'])),
            'total_discounts_tax_excl' => (($for_export) ? round($data['total_discounts_tax_excl'], 2) : self::displayPrice($data['total_discounts_tax_excl'])),
            'cost' => (($for_export) ? round($data['cost'], 2) : self::displayPrice($data['cost'])),
            'margin' => (($for_export) ? round($margin, 2) : self::displayPrice($margin)),
            'sales' => (($for_export) ? round($sales, 2) : self::displayPrice($sales)),
            'taxes' => (($for_export) ? round($taxes, 2) : self::displayPrice($taxes)),
            'average_cart' => (($for_export) ? round($average_cart, 2) : self::displayPrice($average_cart)),
        ];

        return $list;
    }

    public function getProducts(
        $already_sold,
        $with_stock,
        $with_stock_mvt,
        $with_combination,
        $with_out_stock_combination,
        $with_image,
        $with_cover_image,
        $active,
        $with_ean13,
        $id_group,
        $id_category,
        $id_manufacturer,
        $id_feature,
        $id_feature_value,
        $for_export = false
    ) {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;
        $where = '';

        $categories = [];

        if ($id_category) {
            foreach ($id_category as $id_cat) {
                $o_category = new Category($id_cat);
                $children = $o_category->getAllChildren();
                $categories[] = $id_cat;

                foreach ($children as $child) {
                    $categories[] = (int) $child->id;
                }
            }
        }

        $req_loc_valid_o = self::reqLocationValid('o');
        $where_shop_od = self::whereShop('od', false);
        $where_profile_country_a = self::getWhereProfileCountrie('a');
        $p_id_group = self::protectIntArraySQL($id_group);
        $p_id_manufacturer = self::protectIntArraySQL($id_manufacturer);
        $p_id_feature = self::protectIntArraySQL($id_feature);
        $p_id_feature_value = self::protectIntArraySQL($id_feature_value);
        $p_categories = self::protectIntArraySQL($categories);

        if ($with_stock_mvt > -1) {
            $where .= '
                AND ' . (($with_stock_mvt) ? 'EXISTS' : 'NOT EXISTS') . '(
                    SELECT 1
                    FROM `' . _DB_PREFIX_ . 'stock_available` sawsm
                    JOIN `' . _DB_PREFIX_ . 'stock_mvt` smwsm ON smwsm.`id_stock` = sawsm.`id_stock_available`
                    WHERE sawsm.`id_product` = p.`id_product`
                )
            ';
        }

        if ($with_image > -1) {
            $where .= '
                AND ' . (($with_image) ? 'EXISTS' : 'NOT EXISTS') . '(
                    SELECT 1
                    FROM `' . _DB_PREFIX_ . 'image` i
                    WHERE i.`id_product` = p.`id_product`
                )
            ';
        }

        if ($with_cover_image > -1) {
            $where .= '
                AND ' . (($with_cover_image) ? 'EXISTS' : 'NOT EXISTS') . '(
                    SELECT 1
                    FROM `' . _DB_PREFIX_ . 'image` i
                    WHERE i.`id_product` = p.`id_product`
                    AND i.`cover` = 1
                )
            ';
        }

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_product_quantity`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_abandoned_cart`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_product_quantity` (
                `product_id`        int(10)         UNSIGNED    NOT NULL,
                `quantity`          int(10)         UNSIGNED    NOT NULL,
                `quantity_return`   int(10)         UNSIGNED    NOT NULL,
                `refund_tax_excl`   decimal(20,6)               NOT NULL,
                PRIMARY KEY (`product_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_abandoned_cart` (
                `product_id`    int(10)         UNSIGNED    NOT NULL,
                `nb`            int(10)         UNSIGNED    NOT NULL,
                PRIMARY KEY (`product_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_product_quantity` (`product_id`, `quantity`, `quantity_return`, `refund_tax_excl`)
            SELECT product_id, SUM(quantity) AS quantity, SUM(quantity_return) AS quantity_return,
                SUM(refund_tax_excl) AS refund_tax_excl
            FROM (
                SELECT od.`product_id`, IFNULL(SUM(IFNULL(od.`product_quantity`, 0)), 0) AS quantity,
                    0 AS quantity_return,
                    0 AS refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE o.`valid` = 1
                ' . $where_shop_od . '
                ' . $where_profile_country_a . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY od.`product_id`

                UNION

                SELECT od.`product_id`, 0 AS quantity,
                    IFNULL(SUM(IFNULL(ord.`product_quantity`, 0)), 0) AS quantity_return,
                    0 AS refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_return_detail` ord
                JOIN `' . _DB_PREFIX_ . 'order_return` ore ON ord.`id_order_return` = ore.`id_order_return`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON ord.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE 1 = 1
                ' . self::reqReturnValid('ore') . '
                ' . $where_shop_od . '
                ' . $where_profile_country_a . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY od.`product_id`

                UNION

                SELECT od.`product_id`, 0 AS quantity,
                    0 AS quantity_return,
                    IFNULL(SUM(IFNULL(osd.`total_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS refund_tax_excl
                FROM `' . _DB_PREFIX_ . 'order_slip_detail` osd
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON osd.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE 1 = 1
                ' . $where_shop_od . '
                ' . $where_profile_country_a . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY od.`product_id`
            ) t
            GROUP BY product_id
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_abandoned_cart` (`product_id`, `nb`)
            SELECT cp.`id_product`, COUNT(c.`id_cart`)
            FROM `' . _DB_PREFIX_ . 'cart` c
            JOIN `' . _DB_PREFIX_ . 'cart_product` cp ON cp.`id_cart` = c.`id_cart`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . self::reqLocationValid('c') . '
            WHERE c.`id_cart` NOT IN(
                SELECT o.`id_cart`
                FROM `' . _DB_PREFIX_ . 'orders` o
            )
            ' . self::whereShop('c') . '
            ' . $where_profile_country_a . '
            ' . (($id_group) ? ' AND c.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            AND c.`date_upd` < "' . date('Y-m-d H:i:s', mktime(date('H') - 1)) . '"
            GROUP BY cp.`id_product`
        ');

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT p.`reference`, p.`id_product`, ps.`date_add`, IFNULL(pl.`name`, "") AS name,
                IFNULL(sa.`quantity`, 0) AS quantity,
                IFNULL(ps.`price`, 0) AS unit_price_tax_excl,
                IFNULL(ps.`wholesale_price`, 0) AS unit_wholesale_price_tax_excl,
                IFNULL(ntpq.`quantity`, 0) AS quantity_sold,
                IFNULL(ntpq.`quantity_return`, 0) AS quantity_return,
                IFNULL(ntpq.`refund_tax_excl`, 0) AS total_refund_tax_excl,
                IFNULL(p.`ean13`, "") AS ean13, p.`active`,
                (
                    SELECT count(pa.`id_product_attribute`)
                    FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                    WHERE pa.`id_product` = p.`id_product`
                ) AS nb_combinations,
                (
                    SELECT count(sa.`id_product_attribute`)
                    FROM `' . _DB_PREFIX_ . 'stock_available` sa
                    WHERE sa.`id_product` = p.`id_product`
                    AND sa.`id_product_attribute` <> 0
                    AND sa.`quantity` <= 0
                ) AS nb_out_stock_combinations,
                IFNULL (ntac.`nb`, 0) AS abandoned_cart
            FROM `' . _DB_PREFIX_ . 'product` p
            JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON ps.`id_product` = p.`id_product`
                ' . self::whereShop('ps', false) . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.`id_product` = p.`id_product`
                AND pl.`id_lang` = ' . $id_lang . ' ' . self::whereShop('pl', false) . '
            LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON sa.`id_product` = p.`id_product`
                ' . self::whereShopStockAvailable('sa') . ' AND sa.`id_product_attribute` = 0
            LEFT JOIN `nts_product_quantity` ntpq ON ntpq.`product_id` = p.`id_product`
            LEFT JOIN `nts_abandoned_cart` ntac ON ntac.`product_id` = p.`id_product`
            WHERE 1 = 1
            ' . (($active > -1) ? ' AND p.`active` = ' . (int) $active : '') . '
            ' . (($id_manufacturer) ? ' AND p.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
            ' . (($id_feature) ? ' AND p.`id_product` IN(
                SELECT fp.`id_product`
                FROM `' . _DB_PREFIX_ . 'feature_product` fp
                WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                )
            ' : '') . '
            ' . (($id_feature_value) ? ' AND p.`id_product` IN(
                SELECT fp1.`id_product`
                FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                )
            ' : '') . '
            ' . $where . '
            GROUP BY p.`id_product`
            HAVING 1 = 1
            ' . (($id_group) ? (' AND quantity_return < quantity_sold') : '') . '
            ' . (($already_sold > -1) ? (' AND quantity_return ' . (($already_sold) ? '<' : '>=') . ' quantity_sold') : '') . '
            ' . (($with_ean13 > -1) ? (' AND ean13 ' . (($with_ean13) ? '<>' : '=') . ' ""') : '') . '
            ' . (($with_stock > -1) ? (' AND quantity ' . (($with_stock) ? '>' : '<=') . ' 0') : '') . '
            ' . (($with_out_stock_combination > -1) ? (' AND nb_combinations > 0 AND nb_out_stock_combinations ' . (($with_out_stock_combination) ? '>' : '<=') . ' 0') : '') . '
            ' . (($with_combination > -1) ? (' AND nb_combinations  ' . (($with_combination) ? '>' : '<=') . ' 0') : '') . '
            ' . (($id_category) ? ' AND p.`id_product` IN(
                SELECT cp.`id_product`
                FROM `' . _DB_PREFIX_ . 'category_product` cp
                WHERE cp.`id_category` IN (' . $p_categories . ')
                )
            ' : '') . '
            ORDER BY p.`reference`
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_product_quantity`;
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_abandoned_cart`;
        ');

        $total_nb_out_stock_combinations = 0;
        $total_nb_combinations = 0;
        $total_quantity = 0;
        $total_stock_purchase_value_tax_excl = 0;
        $total_stock_value_tax_excl = 0;
        $total_stock_margin_tax_excl = 0;
        $total_quantity_sold = 0;
        $total_quantity_return = 0;
        $total_refund_tax_excl = 0;
        $total_abandoned_cart = 0;

        foreach ($data as $dt) {
            // To force natural sorting by keys
            $key = Tools::strtolower(Tools::replaceAccentedChars($dt['reference'] . '_' . $dt['name']));

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export) {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminProducts',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminProducts')],
                    false
                );

                $dt['reference'] = '<a href="' . $admin_link . '&id_product=' . $dt['id_product']
                    . '&updateproduct" target="_blank">' . $dt['reference'] . '</a>';
            }

            if (!$for_export) {
                $dt['name'] = wordwrap($dt['name'], 50, '<br />');
            }

            $unit_margin_tax_excl = $dt['unit_price_tax_excl'] - $dt['unit_wholesale_price_tax_excl'];
            $stock_purchase_value_tax_excl = $dt['unit_wholesale_price_tax_excl'] * $dt['quantity'];
            $stock_value_tax_excl = $dt['unit_price_tax_excl'] * $dt['quantity'];
            $stock_margin_tax_excl = ($dt['unit_price_tax_excl'] - $dt['unit_wholesale_price_tax_excl']) * $dt['quantity'];

            $total_nb_out_stock_combinations += $dt['nb_out_stock_combinations'];
            $total_nb_combinations += $dt['nb_combinations'];
            $total_quantity += $dt['quantity'];
            $total_stock_purchase_value_tax_excl += $stock_purchase_value_tax_excl;
            $total_stock_value_tax_excl += $stock_value_tax_excl;
            $total_stock_margin_tax_excl += $stock_margin_tax_excl;
            $total_quantity_sold += $dt['quantity_sold'];
            $total_quantity_return += $dt['quantity_return'];
            $total_refund_tax_excl += $dt['total_refund_tax_excl'];
            $total_abandoned_cart += $dt['abandoned_cart'];

            $list[$key] = [
                'id_product' => $dt['id_product'],
                'reference' => $dt['reference'],
                'name' => $dt['name'],
                'nb_out_stock_combinations' => $dt['nb_out_stock_combinations'],
                'nb_combinations' => $dt['nb_combinations'],
                'unit_wholesale_price_tax_excl' => (($for_export) ? round($dt['unit_wholesale_price_tax_excl'], 2) : self::displayPrice($dt['unit_wholesale_price_tax_excl'])),
                'unit_price_tax_excl' => (($for_export) ? round($dt['unit_price_tax_excl'], 2) : self::displayPrice($dt['unit_price_tax_excl'])),
                'unit_margin_tax_excl' => (($for_export) ? round($unit_margin_tax_excl, 2) : self::displayPrice($unit_margin_tax_excl)),
                'quantity' => $dt['quantity'],
                'stock_purchase_value_tax_excl' => (($for_export) ? round($stock_purchase_value_tax_excl, 2) : self::displayPrice($stock_purchase_value_tax_excl)),
                'stock_value_tax_excl' => (($for_export) ? round($stock_value_tax_excl, 2) : self::displayPrice($stock_value_tax_excl)),
                'stock_margin_tax_excl' => (($for_export) ? round($stock_margin_tax_excl, 2) : self::displayPrice($stock_margin_tax_excl)),
                'quantity_sold' => $dt['quantity_sold'],
                'quantity_return' => $dt['quantity_return'],
                'total_refund_tax_excl' => (($for_export) ? round($dt['total_refund_tax_excl'], 2) : self::displayPrice($dt['total_refund_tax_excl'])),
                'ean13' => $dt['ean13'],
                'active' => (($dt['active']) ? (($for_export) ? '1' : '<i class="fas fa-check"></i>') : (($for_export) ? '0' : '<i class="fas fa-times"></i>')),
                'abandoned_cart' => $dt['abandoned_cart'],
                'date_add' => $dt['date_add'],
            ];
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        $clean_list = array_values($list);

        $clean_list[] = [
            'id_product' => $this->l('Total'),
            'reference' => '-',
            'name' => '-',
            'nb_out_stock_combinations' => $total_nb_out_stock_combinations,
            'nb_combinations' => $total_nb_combinations,
            'unit_wholesale_price_tax_excl' => '-',
            'unit_price_tax_excl' => '-',
            'unit_margin_tax_excl' => '-',
            'quantity' => $total_quantity,
            'stock_purchase_value_tax_excl' => (($for_export) ? round($total_stock_purchase_value_tax_excl, 2) : self::displayPrice($total_stock_purchase_value_tax_excl)),
            'stock_value_tax_excl' => (($for_export) ? round($total_stock_value_tax_excl, 2) : self::displayPrice($total_stock_value_tax_excl)),
            'stock_margin_tax_excl' => (($for_export) ? round($total_stock_margin_tax_excl, 2) : self::displayPrice($total_stock_margin_tax_excl)),
            'quantity_sold' => $total_quantity_sold,
            'quantity_return' => $total_quantity_return,
            'total_refund_tax_excl' => (($for_export) ? round($total_refund_tax_excl, 2) : self::displayPrice($total_refund_tax_excl)),
            'ean13' => '-',
            'active' => '-',
            'abandoned_cart' => $total_abandoned_cart,
            'date_add' => '-',
        ];

        return $clean_list;
    }

    public static function getProductsWithOutStockCombinations($active, $for_export = false)
    {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT p.`reference`, p.`id_product`, IFNULL(pl.`name`, "") AS name, p.`active`,
                (
                    SELECT count(pa.`id_product_attribute`)
                    FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                    WHERE pa.`id_product` = p.`id_product`
                ) AS nb_combinations,
                (
                    SELECT count(sa.`id_product_attribute`)
                    FROM `' . _DB_PREFIX_ . 'stock_available` sa
                    WHERE sa.`id_product` = p.`id_product`
                    AND sa.`id_product_attribute` <> 0
                    AND sa.`quantity` <= 0
                ) AS nb_out_stock_combinations
            FROM `' . _DB_PREFIX_ . 'product` p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.`id_product` = p.`id_product`
                AND pl.`id_lang` = ' . $id_lang . ' ' . self::whereShop('pl', false) . '
            WHERE 1 = 1
            ' . (($active > -1) ? ' AND p.`active` = ' . (int) $active : '') . '
            GROUP BY p.`id_product`
            HAVING nb_combinations > 0 AND nb_out_stock_combinations > 0
            ORDER BY p.`reference`
        ');

        foreach ($data as $dt) {
            // To force natural sorting by keys
            $key = Tools::strtolower(Tools::replaceAccentedChars($dt['reference'] . '_' . $dt['name']));

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export) {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminProducts',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminProducts')],
                    false
                );

                $dt['reference'] = '<a href="' . $admin_link . '&id_product=' . $dt['id_product']
                    . '&updateproduct" target="_blank">' . $dt['reference'] . '</a>';
            }

            $list[$key] = [
                'reference' => $dt['reference'],
                'name' => $dt['name'],
                'active' => (($dt['active']) ? (($for_export) ? '1' : '<i class="fas fa-check"></i>') : (($for_export) ? '0' : '<i class="fas fa-times"></i>')),
            ];
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        return array_values($list);
    }

    public static function getProductsWithCombinationsWithoutEnoughStock($nb_combinations_min_without_stock, $active, $for_export = false)
    {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT p.`reference`, p.`id_product`, IFNULL(pl.`name`, "") AS name,
                IFNULL(sa.`quantity`, 0) AS quantity,
                (
                    SELECT count(pa.`id_product_attribute`)
                    FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                    WHERE pa.`id_product` = p.`id_product`
                ) AS nb_combinations,
                (
                    SELECT count(sa.`id_product_attribute`)
                    FROM `' . _DB_PREFIX_ . 'stock_available` sa
                    WHERE sa.`id_product` = p.`id_product`
                    AND sa.`id_product_attribute` <> 0
                    AND sa.`quantity` > 0
                    ' . self::whereShopStockAvailable('sa') . '
                ) AS nb_combinations_with_stock
            FROM `' . _DB_PREFIX_ . 'product` p
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.`id_product` = p.`id_product`
                AND pl.`id_lang` = ' . $id_lang . ' ' . self::whereShop('pl', false) . '
            LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON sa.`id_product` = p.`id_product`
                ' . self::whereShopStockAvailable('sa') . ' AND sa.`id_product_attribute` = 0
            WHERE 1 = 1
            ' . (($active > -1) ? ' AND p.`active` = ' . (int) $active : '') . '
            GROUP BY p.`id_product`
            HAVING nb_combinations > 0 AND nb_combinations_with_stock <= ' . (int) $nb_combinations_min_without_stock . '
            ORDER BY p.`reference`
        ');

        foreach ($data as $dt) {
            // To force natural sorting by keys
            $key = Tools::strtolower(Tools::replaceAccentedChars($dt['reference'] . '_' . $dt['name']));

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export) {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminProducts',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminProducts')],
                    false
                );

                $dt['reference'] = '<a href="' . $admin_link . '&id_product=' . $dt['id_product']
                    . '&updateproduct" target="_blank">' . $dt['reference'] . '</a>';
            }

            $list[$key] = [
                'reference' => $dt['reference'],
                'name' => $dt['name'],
                'quantity' => $dt['quantity'],
                'nb_combinations_with_stock' => $dt['nb_combinations_with_stock'],
                'nb_combinations' => $dt['nb_combinations'],
            ];
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        return array_values($list);
    }

    public static function getCombinations(
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
        $for_export = 0
    ) {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;

        $categories = [];

        if ($id_category) {
            foreach ($id_category as $id_cat) {
                $o_category = new Category($id_cat);
                $children = $o_category->getAllChildren();
                $categories[] = $id_cat;

                foreach ($children as $child) {
                    $categories[] = (int) $child->id;
                }
            }
        }

        $req_loc_valid_o = self::reqLocationValid('o');
        $where_shop_od = self::whereShop('od', false);
        $where_profile_country_a = self::getWhereProfileCountrie('a');
        $p_id_group = self::protectIntArraySQL($id_group);
        $p_id_manufacturer = self::protectIntArraySQL($id_manufacturer);
        $p_id_feature = self::protectIntArraySQL($id_feature);
        $p_id_feature_value = self::protectIntArraySQL($id_feature_value);
        $p_id_product = self::protectIntArraySQL($id_product);
        $p_categories = self::protectIntArraySQL($categories);

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_product_attribute_quantity`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_product_attribute_quantity` (
                `product_id`            int(10)         UNSIGNED    NOT NULL,
                `product_attribute_id`  int(10)         UNSIGNED    NOT NULL,
                `quantity`              int(10)         UNSIGNED    NOT NULL,
                `quantity_return`       int(10)         UNSIGNED    NOT NULL,
                PRIMARY KEY (`product_id`, `product_attribute_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_product_attribute_quantity` (`product_id`, `product_attribute_id`, `quantity`, `quantity_return`)
            SELECT product_id, id_product_attr, SUM(product_quantity) AS product_quantity,
                SUM(product_quantity_return) AS product_quantity_return
            FROM (
                SELECT od.`product_id`, IFNULL(od.`product_attribute_id`, 0) AS id_product_attr, IFNULL(SUM(IFNULL(od.`product_quantity`, 0)), 0) AS product_quantity,
                    0 AS product_quantity_return
                FROM `' . _DB_PREFIX_ . 'order_detail` od
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE o.`valid` = 1
                ' . $where_shop_od . '
                ' . $where_profile_country_a . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY od.`product_id`, id_product_attr

                UNION

                SELECT od.`product_id`, IFNULL(od.`product_attribute_id`, 0) AS id_product_attr, 0 AS product_quantity,
                    IFNULL(SUM(IFNULL(ord.`product_quantity`, 0)), 0) AS product_quantity_return
                FROM `' . _DB_PREFIX_ . 'order_return_detail` ord
                JOIN `' . _DB_PREFIX_ . 'order_return` ore ON ord.`id_order_return` = ore.`id_order_return`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od ON ord.`id_order_detail` = od.`id_order_detail`
                JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
                JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
                WHERE 1 = 1
                ' . self::reqReturnValid('ore') . '
                ' . $where_shop_od . '
                ' . $where_profile_country_a . '
                ' . (($id_group) ? ' AND o.`id_customer` IN(
                    SELECT cg.`id_customer`
                    FROM `' . _DB_PREFIX_ . 'customer_group` cg
                    WHERE cg.`id_group` IN (' . $p_id_group . ')
                    )
                ' : '') . '
                GROUP BY od.`product_id`, id_product_attr
            ) t
            GROUP BY product_id, id_product_attr
        ');

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT pa.`id_product_attribute`, p.`id_product`, IFNULL(pl.`name`, "") AS name,
                IF(pa.`reference` IS NULL OR pa.`reference` = "", p.`reference`, pa.`reference`) AS reference,
                IFNULL(pa.`ean13`, "") AS ean13, IFNULL(sa.`quantity`, 0) AS quantity, p.`active`,
                GROUP_CONCAT(DISTINCT CONCAT(IFNULL(agl.`name`, ""), " - ", IFNULL(al.`name`, "")) ORDER BY agl.`name`,
                al.`name` SEPARATOR ", ") AS combination,
                IFNULL((IFNULL(pas.`price`, 0) + IFNULL(ps.`price`, 0)) , 0) AS unit_price_tax_excl,
                IFNULL((IFNULL(pas.`wholesale_price`, 0) + IFNULL(ps.`wholesale_price`, 0)) , 0) AS unit_wholesale_price_tax_excl,
                IFNULL(ntpa.`quantity`, 0) AS quantity_sold,
                IFNULL(ntpa.`quantity_return`, 0) AS quantity_return
            FROM `' . _DB_PREFIX_ . 'product_attribute` pa
            JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` pas ON pas.`id_product_attribute` = pa.`id_product_attribute`
                ' . self::whereShop('pas', false) . '
            JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac
                ON pac.`id_product_attribute` = pa.`id_product_attribute`
            JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
            JOIN `' . _DB_PREFIX_ . 'attribute_shop` ash ON ash.`id_attribute` = a.`id_attribute`
                ' . self::whereShop('ash', false) . '
            JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON al.`id_attribute` = a.`id_attribute`
                AND al.`id_lang` = ' . $id_lang . '
            JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON agl.`id_attribute_group` = a.`id_attribute_group`
                AND agl.`id_lang` = ' . $id_lang . '
            JOIN `' . _DB_PREFIX_ . 'attribute_group_shop` ags ON ags.`id_attribute_group` = a.`id_attribute_group`
                ' . self::whereShop('ags', false) . '
            JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = pa.`id_product`
            JOIN `' . _DB_PREFIX_ . 'product_shop` ps ON ps.`id_product` = pa.`id_product`
                ' . self::whereShop('ps', false) . '
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.`id_product` = pa.`id_product`
                AND pl.`id_lang` = ' . $id_lang . ' ' . self::whereShop('pl', false) . '
            LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON sa.`id_product_attribute` = pa.`id_product_attribute`
                ' . self::whereShopStockAvailable('sa') . '
            LEFT JOIN `nts_product_attribute_quantity` ntpa ON ntpa.`product_attribute_id` = pa.`id_product_attribute` AND  ntpa.`product_id` = p.`id_product`
            WHERE 1 = 1
            ' . (($active > -1) ? ' AND p.`active` = ' . (int) $active : '') . '
            ' . (($id_manufacturer) ? ' AND p.`id_manufacturer` IN (' . $p_id_manufacturer . ')' : '') . '
            ' . (($id_feature) ? ' AND p.`id_product` IN(
                SELECT fp.`id_product`
                FROM `' . _DB_PREFIX_ . 'feature_product` fp
                WHERE fp.`id_feature` IN (' . $p_id_feature . ')
                )
            ' : '') . '
            ' . (($id_feature_value) ? ' AND p.`id_product` IN(
                SELECT fp1.`id_product`
                FROM `' . _DB_PREFIX_ . 'feature_product` fp1
                WHERE fp1.`id_feature_value` IN (' . $p_id_feature_value . ')
                )
            ' : '') . '
            ' . (($id_product) ? ' AND pa.`id_product` IN (' . $p_id_product . ')' : '') . '
            GROUP BY pa.`id_product_attribute`
            HAVING 1 = 1
            ' . (($id_group) ? ' AND quantity_return < quantity_sold' : '') . '
            ' . (($already_sold > -1) ? (' AND quantity_return ' . (($already_sold) ? '<' : '>=') . ' quantity_sold') : '') . '
            ' . (($with_stock > -1) ? (' AND quantity ' . (($with_stock) ? '>' : '<=') . ' 0') : '') . '
            ' . ((is_numeric($min_quantity)) ? (' AND quantity >= ' . (int) $min_quantity) : '') . '
            ' . ((is_numeric($max_quantity)) ? (' AND quantity <= ' . (int) $max_quantity) : '') . '
            ' . (($id_category) ? ' AND p.`id_product` IN(
                SELECT cp.`id_product`
                FROM `' . _DB_PREFIX_ . 'category_product` cp
                WHERE cp.`id_category` IN (' . $p_categories . ')
                )
            ' : '') . '
            ORDER BY reference, combination
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_product_attribute_quantity`;
        ');

        foreach ($data as $dt) {
            // To force natural sorting by keys
            $key = Tools::strtolower(Tools::replaceAccentedChars($dt['reference'] . '_' . $dt['combination'] . '_' . $dt['id_product_attribute']));

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export) {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminProducts',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminProducts')],
                    false
                );

                $dt['reference'] = '<a href="' . $admin_link . '&id_product=' . $dt['id_product']
                    . '&updateproduct" target="_blank">' . $dt['reference'] . '</a>';
            }

            if (!$for_export) {
                $dt['name'] = wordwrap($dt['name'], 50, '<br />');
                $dt['combination'] = wordwrap($dt['combination'], 50, '<br />');
            }

            $unit_margin_tax_excl = $dt['unit_price_tax_excl'] - $dt['unit_wholesale_price_tax_excl'];
            $stock_purchase_value_tax_excl = $dt['unit_wholesale_price_tax_excl'] * $dt['quantity'];
            $stock_value_tax_excl = $dt['unit_price_tax_excl'] * $dt['quantity'];
            $stock_margin_tax_excl = ($dt['unit_price_tax_excl'] - $dt['unit_wholesale_price_tax_excl']) * $dt['quantity'];

            $list[$key] = [
                'reference' => $dt['reference'],
                'name' => $dt['name'],
                'combination' => $dt['combination'],
                'unit_wholesale_price_tax_excl' => (($for_export) ? round($dt['unit_wholesale_price_tax_excl'], 2) : self::displayPrice($dt['unit_wholesale_price_tax_excl'])),
                'unit_price_tax_excl' => (($for_export) ? round($dt['unit_price_tax_excl'], 2) : self::displayPrice($dt['unit_price_tax_excl'])),
                'unit_margin_tax_excl' => (($for_export) ? round($unit_margin_tax_excl, 2) : self::displayPrice($unit_margin_tax_excl)),
                'quantity' => $dt['quantity'],
                'stock_purchase_value_tax_excl' => (($for_export) ? round($stock_purchase_value_tax_excl, 2) : self::displayPrice($stock_purchase_value_tax_excl)),
                'stock_value_tax_excl' => (($for_export) ? round($stock_value_tax_excl, 2) : self::displayPrice($stock_value_tax_excl)),
                'stock_margin_tax_excl' => (($for_export) ? round($stock_margin_tax_excl, 2) : self::displayPrice($stock_margin_tax_excl)),
                'quantity_sold' => $dt['quantity_sold'],
                'quantity_return' => $dt['quantity_return'],
                'ean13' => $dt['ean13'],
                'active' => (($dt['active']) ? (($for_export) ? '1' : '<i class="fas fa-check"></i>') : (($for_export) ? '0' : '<i class="fas fa-times"></i>')),
            ];
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        return array_values($list);
    }

    public static function getCombinationsUnsoldWithStock($for_export = false)
    {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;

        $data = Db::getInstance()->executeS('
            SELECT DISTINCT pa.`id_product_attribute`, p.`id_product`, IFNULL(pl.`name`, "") AS name,
                IF(pa.`reference` IS NULL OR pa.`reference` = "", p.`reference`, pa.`reference`) AS reference,
                IFNULL(pa.`ean13`, "") AS ean13, IFNULL(sa.`quantity`, 0) AS quantity,
                GROUP_CONCAT(DISTINCT CONCAT(IFNULL(agl.`name`, ""), " - ", IFNULL(al.`name`, "")) ORDER BY agl.`name`,
                al.`name` SEPARATOR ", ") AS combination,
                IFNULL(a2.quantity_sold, 0) AS quantity_sold, (IFNULL(a2.quantity_return, 0) + IFNULL(r2.quantity_return, 0)) AS quantity_return
            FROM `' . _DB_PREFIX_ . 'product_attribute` pa
            JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` pas ON pas.`id_product_attribute` = pa.`id_product_attribute` ' . self::whereShop('pas', false) . '
            JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
            JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
            JOIN `' . _DB_PREFIX_ . 'attribute_shop` ash ON ash.`id_attribute` = a.`id_attribute` ' . self::whereShop('ash', false) . '
            JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON al.`id_attribute` = a.`id_attribute` AND al.`id_lang` = ' . $id_lang . '
            JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON agl.`id_attribute_group` = a.`id_attribute_group` AND agl.`id_lang` = ' . $id_lang . '
            JOIN `' . _DB_PREFIX_ . 'attribute_group_shop` ags ON ags.`id_attribute_group` = a.`id_attribute_group` ' . self::whereShop('ags', false) . '
            JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_product` = pa.`id_product`
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON pl.`id_product` = pa.`id_product` AND pl.`id_lang` = ' . $id_lang . ' ' . self::whereShop('pl', false) . '
            LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sa ON sa.`id_product_attribute` = pa.`id_product_attribute` ' . self::whereShopStockAvailable('sa') . '
            LEFT JOIN (
                SELECT od1.`product_attribute_id`, SUM(IFNULL(od1.`product_quantity`, 0)) AS quantity_sold,
                    SUM(IFNULL(od1.`product_quantity_return`, 0)) AS quantity_return
                FROM `' . _DB_PREFIX_ . 'order_detail` od1
                WHERE 1 = 1
                ' . self::whereShop('od1', false) . '
                GROUP BY od1.`product_attribute_id`
            ) AS a2 ON (a2.`product_attribute_id` = pa.`id_product_attribute`)
            LEFT JOIN (
                SELECT od2.`product_attribute_id`, ord.`id_order_detail`,
                    SUM(IFNULL(ord.`product_quantity`, 0)) AS quantity_return
                FROM `' . _DB_PREFIX_ . 'order_return_detail` ord
                JOIN `' . _DB_PREFIX_ . 'order_return` ore ON ord.`id_order_return` = ore.`id_order_return`
                JOIN `' . _DB_PREFIX_ . 'order_detail` od2 ON ord.`id_order_detail` = od2.`id_order_detail`
                WHERE 1 = 1
                ' . self::reqReturnValid('ore') . '
                ' . self::whereShop('od2', false) . '
                GROUP BY od2.`product_attribute_id`
            ) AS r2 ON (r2.`product_attribute_id` = pa.`id_product_attribute`)
            GROUP BY pa.`id_product_attribute`
            HAVING quantity_return >= quantity_sold
            AND quantity > 0
            ORDER BY reference, combination
        ');

        foreach ($data as $dt) {
            // To force natural sorting by keys
            $key = Tools::strtolower(Tools::replaceAccentedChars($dt['reference'] . '_' . $dt['combination'] . '_' . $dt['id_product_attribute']));

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export) {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminProducts',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminProducts')],
                    false
                );

                $dt['reference'] = '<a href="' . $admin_link . '&id_product=' . $dt['id_product']
                    . '&updateproduct" target="_blank">' . $dt['reference'] . '</a>';
            }

            if (!$for_export) {
                $dt['name'] = wordwrap($dt['name'], 50, '<br />');
                $dt['combination'] = wordwrap($dt['combination'], 50, '<br />');
            }

            $list[$key] = [
                'reference' => $dt['reference'],
                'name' => $dt['name'],
                'combination' => $dt['combination'],
                'quantity' => $dt['quantity'],
                'ean13' => $dt['ean13'],
            ];
        }

        array_multisort(array_keys($list), SORT_NATURAL, $list);

        return array_values($list);
    }

    public static function getCarriers($from, $to, $id_carrier, $id_group, $for_export = false)
    {
        $list = [];

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $default_name = Configuration::get('PS_SHOP_NAME');

        $req_date_valid_o = self::reqDateValid('o');
        $where_shop_o = self::whereShop('o');
        $where_profile_country_a = self::getWhereProfileCountrie('a');

        $data = Db::getInstance()->executeS('
            SELECT DATE_FORMAT(' . $req_date_valid_o . ', "%Y-%m") AS month,
                IFNULL(
                    (
                        SELECT c2.`name`
                        FROM `' . _DB_PREFIX_ . 'carrier` c2
                        WHERE c2.`deleted` = 0
                        AND c2.`id_reference` = c.`id_reference`
                    )
                , (
                        IFNULL(c.`name`, 0)
                    )
                ) AS name,
                COUNT(o.`id_order`) AS nb_order,
                IFNULL(SUM(IFNULL(o.`total_shipping_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_shipping_tax_excl
            FROM `' . _DB_PREFIX_ . 'carrier` c
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_carrier` = c.`id_carrier` ' . $where_shop_o . '
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . self::reqLocationValid('o') . ' ' . $where_profile_country_a . '
            WHERE ' . $req_date_valid_o . ' BETWEEN "' . pSQL($from) . ' 00:00:00" AND "' . pSQL($to) . ' 23:59:59"
            AND o.`valid` = 1
            ' . $where_shop_o . '
            ' . $where_profile_country_a . '
            ' . (($id_carrier) ? (' AND c.`id_carrier` IN (' . self::protectIntArraySQL($id_carrier) . ')') : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . self::protectIntArraySQL($id_group) . ')
                )
            ' : '') . '
            GROUP BY month, c.`id_reference`
            ORDER BY month DESC , name
        ');

        $total_order = [];
        $total_amount = [];

        foreach ($data as $d) {
            if (!isset($total_order[$d['month']])) {
                $total_order[$d['month']] = 0;
            }

            if (!isset($total_amount[$d['month']])) {
                $total_amount[$d['month']] = 0;
            }

            $total_order[$d['month']] += $d['nb_order'];
            $total_amount[$d['month']] += $d['total_shipping_tax_excl'];
        }

        foreach ($data as $dt) {
            if ($dt['nb_order'] > 0 && $total_order[$dt['month']] > 0) {
                $dt['nb_order_per'] = $dt['nb_order'] / $total_order[$dt['month']] * 100;
            } else {
                $dt['nb_order_per'] = 0;
            }

            if ($dt['total_shipping_tax_excl'] > 0 && $total_amount[$dt['month']] > 0) {
                $dt['total_shipping_tax_excl_per'] = $dt['total_shipping_tax_excl'] / $total_amount[$dt['month']] * 100;
            } else {
                $dt['total_shipping_tax_excl_per'] = 0;
            }

            if ($dt['name'] == '0') {
                $dt['name'] = $default_name;
            }

            $list[] = [
                'month' => $dt['month'],
                'name' => $dt['name'],
                'nb_order' => $dt['nb_order'],
                'total_shipping_tax_excl' => (($for_export) ? round($dt['total_shipping_tax_excl'], 2) : self::displayPrice($dt['total_shipping_tax_excl'])),
                'nb_order_per' => round($dt['nb_order_per'], 2),
                'total_shipping_tax_excl_per' => round($dt['total_shipping_tax_excl_per'], 2),
            ];
        }

        return $list;
    }

    public static function getManufacturers($from, $to, $id_manufacturer, $id_group, $for_export = false)
    {
        $list = [];

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $req_date_valid_o = self::reqDateValid('o');
        $req_loc_valid_o = self::reqLocationValid('o');
        $where_shop_od = self::whereShop('od', false);
        $where_profile_country_a = self::getWhereProfileCountrie('a');
        $p_from = pSQL($from);
        $p_to = pSQL($to);
        $p_id_manufacturer = self::protectIntArraySQL($id_manufacturer);
        $p_id_group = self::protectIntArraySQL($id_group);

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_order_manufacturer`;
        ');

        Db::getInstance()->execute('
            CREATE TEMPORARY TABLE `nts_order_manufacturer` (
                `id_manufacturer`   int(10) UNSIGNED    NOT NULL,
                `nb_order`          int(10) UNSIGNED    NOT NULL,
                `month`             varchar(7)          NOT NULL,
                PRIMARY KEY (`id_manufacturer`, `month`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
        ');

        Db::getInstance()->execute('
            INSERT INTO `nts_order_manufacturer` (`id_manufacturer`, `nb_order`, `month`)
            SELECT m.`id_manufacturer`, COUNT(DISTINCT od.`id_order`), DATE_FORMAT(' . $req_date_valid_o . ', "%Y-%m") as month
            FROM `' . _DB_PREFIX_ . 'manufacturer` m
            JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_manufacturer` = m.`id_manufacturer`
            JOIN `' . _DB_PREFIX_ . 'order_detail` od ON od.`product_id` = p.`id_product` ' . $where_shop_od . '
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            WHERE ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59" /* ex: Y-m = 2021-11 but from = 2021-11-15 we do not want all of 2021-11 */
            AND o.`valid` = 1
            ' . $where_profile_country_a . '
            ' . (($id_manufacturer) ? (' AND m.`id_manufacturer` IN (' . $p_id_manufacturer . ')') : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            GROUP BY m.`id_manufacturer`, month
        ');

        $data = Db::getInstance()->executeS('
            SELECT DATE_FORMAT(' . $req_date_valid_o . ', "%Y-%m") AS month,
                IFNULL(
                    (
                        SELECT m2.`name`
                        FROM `' . _DB_PREFIX_ . 'manufacturer` m2
                        WHERE m2.`id_manufacturer` = m.`id_manufacturer`
                    )
                , "") AS name,
                IFNULL(ntom.`nb_order`, 0) AS nb_order,
                IFNULL(SUM((IFNULL(od.`unit_price_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)) * IFNULL(od.`product_quantity`, 1)), 0) AS total_price_tax_excl
            FROM `' . _DB_PREFIX_ . 'manufacturer` m
            JOIN `' . _DB_PREFIX_ . 'product` p ON p.`id_manufacturer` = m.`id_manufacturer`
            JOIN `' . _DB_PREFIX_ . 'order_detail` od ON od.`product_id` = p.`id_product` ' . $where_shop_od . '
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = od.`id_order`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . $req_loc_valid_o . '
            JOIN `nts_order_manufacturer` ntom ON ntom.`id_manufacturer` = m.`id_manufacturer` AND ntom.`month` = DATE_FORMAT(' . $req_date_valid_o . ', "%Y-%m")
            WHERE ' . $req_date_valid_o . ' BETWEEN "' . $p_from . ' 00:00:00" AND "' . $p_to . ' 23:59:59"
            AND o.`valid` = 1
            ' . $where_profile_country_a . '
            ' . (($id_manufacturer) ? (' AND m.`id_manufacturer` IN (' . $p_id_manufacturer . ')') : '') . '
            ' . (($id_group) ? ' AND o.`id_customer` IN(
                SELECT cg.`id_customer`
                FROM `' . _DB_PREFIX_ . 'customer_group` cg
                WHERE cg.`id_group` IN (' . $p_id_group . ')
                )
            ' : '') . '
            GROUP BY month, m.`id_manufacturer`
            ORDER BY month DESC , name
        ');

        Db::getInstance()->execute('
            DROP TABLE IF EXISTS `nts_order_manufacturer`;
        ');

        $total_order = [];
        $total_amount = [];

        foreach ($data as $d) {
            if (!isset($total_order[$d['month']])) {
                $total_order[$d['month']] = 0;
            }

            if (!isset($total_amount[$d['month']])) {
                $total_amount[$d['month']] = 0;
            }

            $total_order[$d['month']] += $d['nb_order'];
            $total_amount[$d['month']] += $d['total_price_tax_excl'];
        }

        foreach ($data as $dt) {
            if ($dt['nb_order'] > 0 && $total_order[$dt['month']] > 0) {
                $dt['nb_order_per'] = $dt['nb_order'] / $total_order[$dt['month']] * 100;
            } else {
                $dt['nb_order_per'] = 0;
            }

            if ($dt['total_price_tax_excl'] > 0 && $total_amount[$dt['month']] > 0) {
                $dt['total_price_tax_excl_per'] = $dt['total_price_tax_excl'] / $total_amount[$dt['month']] * 100;
            } else {
                $dt['total_price_tax_excl_per'] = 0;
            }

            $list[] = [
                'month' => $dt['month'],
                'name' => $dt['name'],
                'nb_order' => $dt['nb_order'],
                'total_price_tax_excl' => (($for_export) ? round($dt['total_price_tax_excl'], 2) : self::displayPrice($dt['total_price_tax_excl'])),
                'nb_order_per' => round($dt['nb_order_per'], 2),
                'total_price_tax_excl_per' => round($dt['total_price_tax_excl_per'], 2),
            ];
        }

        return $list;
    }

    public static function getCustomerSingleOrderAmount($amount_customer_min_one_order, $for_export = false)
    {
        $list = [];
        $id_lang = Context::getContext()->language->id;

        $data = Db::getInstance()->executeS('
            SELECT c.`id_customer`, c.`email`, c.`firstname`, c.`lastname`,
                (IFNULL(MAX(o.`total_paid_real`), 0)/IFNULL(o.`conversion_rate`, 1)) AS max_amount
            FROM `' . _DB_PREFIX_ . 'customer` c
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_customer` = c.`id_customer`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . self::reqLocationValid('o') . '
            WHERE o.`valid` = 1
            ' . self::whereShop('o') . '
            ' . self::getWhereProfileCountrie('a') . '
            GROUP BY c.`id_customer`
            HAVING max_amount > ' . (float) $amount_customer_min_one_order . '
            ORDER BY max_amount DESC
        ');

        foreach ($data as $dt) {
            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export) {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminCustomers',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminCustomers')],
                    false
                );

                $dt['email'] = '<a href="' . $admin_link . '&id_customer=' . $dt['id_customer']
                    . '&viewcustomer" target="_blank">' . $dt['email'] . '</a>';
            }

            $list[] = [
                'email' => $dt['email'],
                'firstname' => $dt['firstname'],
                'lastname' => $dt['lastname'],
                'id_customer' => $dt['id_customer'],
                'max_amount' => (($for_export) ? round($dt['max_amount'], 2) : self::displayPrice($dt['max_amount'])),
            ];
        }

        return $list;
    }

    public static function getCustomerOrdersAmount($amount_customer_min_one_order, $for_export = false)
    {
        $list = [];
        $id_lang = Context::getContext()->language->id;

        $data = Db::getInstance()->executeS('
            SELECT c.`id_customer`, c.`email`, c.`firstname`, c.`lastname`, COUNT(o.`id_order`) AS nb_order,
                IFNULL(SUM(IFNULL(o.`total_paid_real`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS total_amount
            FROM `' . _DB_PREFIX_ . 'customer` c
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_customer` = c.`id_customer`
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . self::reqLocationValid('o') . '
            WHERE o.`valid` = 1
            ' . self::whereShop('o') . '
            ' . self::getWhereProfileCountrie('a') . '
            GROUP BY c.`id_customer`
            HAVING nb_order > 1 AND total_amount > ' . (float) $amount_customer_min_one_order . '
            ORDER BY nb_order DESC, total_amount DESC
        ');

        foreach ($data as $dt) {
            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true && !$for_export) {
                $admin_link = Dispatcher::getInstance()->createUrl(
                    'AdminCustomers',
                    $id_lang,
                    ['token' => Tools::getAdminTokenLite('AdminCustomers')],
                    false
                );

                $dt['email'] = '<a href="' . $admin_link . '&id_customer=' . $dt['id_customer']
                    . '&viewcustomer" target="_blank">' . $dt['email'] . '</a>';
            }

            $average_amount = (($dt['nb_order'] > 0) ? ($dt['total_amount'] / $dt['nb_order']) : 0);

            $list[] = [
                'email' => $dt['email'],
                'firstname' => $dt['firstname'],
                'lastname' => $dt['lastname'],
                'id_customer' => $dt['id_customer'],
                'nb_order' => $dt['nb_order'],
                'total_amount' => (($for_export) ? round($dt['total_amount'], 2) : self::displayPrice($dt['total_amount'])),
                'average_amount' => (($for_export) ? round($average_amount, 2) : self::displayPrice($average_amount)),
            ];
        }

        return $list;
    }

    public static function getCartrules($from, $to, $for_export = false)
    {
        $list = [];
        $id_lang = (int) Context::getContext()->language->id;

        if ($to == '0000-00-00') {
            $to = date('Y-m-d');
        }

        $data = Db::getInstance()->executeS('
            SELECT DATE_FORMAT(' . self::reqDateValid('o') . ', "%Y-%m") AS month, IFNULL(crl.`name`, "") AS name,
                COUNT(ocr.`id_cart_rule`) AS nb_cart_rules, IFNULL(SUM(IFNULL(ocr.`value`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS amount_tax_incl,
                IFNULL(SUM(IFNULL(ocr.`value_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS amount_tax_excl, cr.`code`, ocr.`free_shipping`,
                IFNULL(SUM(IFNULL(o.`total_paid_tax_excl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS order_tax_excl,
                IFNULL(SUM(IFNULL(o.`total_paid_tax_incl`, 0)/IFNULL(o.`conversion_rate`, 1)), 0) AS order_tax_incl
            FROM `' . _DB_PREFIX_ . 'order_cart_rule` ocr
            JOIN `' . _DB_PREFIX_ . 'cart_rule_lang` crl ON crl.`id_cart_rule` = ocr.`id_cart_rule`
                AND crl.`id_lang` = ' . $id_lang . '
            JOIN `' . _DB_PREFIX_ . 'cart_rule` cr ON cr.`id_cart_rule` = ocr.`id_cart_rule`
            JOIN `' . _DB_PREFIX_ . 'orders` o ON o.`id_order` = ocr.`id_order` ' . self::whereShop('o') . '
            JOIN `' . _DB_PREFIX_ . 'address` a ON a.`id_address` = ' . self::reqLocationValid('o') . '
            WHERE ' . self::reqDateValid('o') . ' BETWEEN "' . pSQL($from) . ' 00:00:00" AND "' . pSQL($to) . ' 23:59:59"
            AND o.`valid` = 1
            ' . self::whereShop('o') . '
            ' . self::getWhereProfileCountrie('a') . '
            GROUP BY month, ocr.`id_cart_rule`
            ORDER BY month DESC , ocr.`id_cart_rule`
        ');

        foreach ($data as $dt) {
            $list[] = [
                'month' => $dt['month'],
                'name' => $dt['name'],
                'nb_cart_rules' => $dt['nb_cart_rules'],
                'amount_tax_excl' => (($for_export) ? round($dt['amount_tax_excl'], 2) : self::displayPrice($dt['amount_tax_excl'])),
                'amount_tax_incl' => (($for_export) ? round($dt['amount_tax_incl'], 2) : self::displayPrice($dt['amount_tax_incl'])),
                'code' => $dt['code'],
                'free_shipping' => (($dt['free_shipping']) ? (($for_export) ? '1' : '<i class="fas fa-check"></i>') : (($for_export) ? '0' : '<i class="fas fa-times"></i>')),
                'order_tax_excl' => (($for_export) ? round($dt['order_tax_excl'], 2) : self::displayPrice($dt['order_tax_excl'])),
                'order_tax_incl' => (($for_export) ? round($dt['amount_tax_incl'], 2) : self::displayPrice($dt['order_tax_incl'])),
            ];
        }

        return $list;
    }

    public static function displayPrice($value)
    {
        $context = Context::getContext();

        if (version_compare(_PS_VERSION_, '1.7.7', '>=') === true) {
            return Tools::getContextLocale($context)->formatPrice($value, $context->currency->iso_code);
        } else {
            return Tools::displayPrice($value);
        }
    }

    public static function protectIntArraySQL($i_array)
    {
        return (is_array($i_array)) ? implode(',', array_map('intval', $i_array)) : (int) $i_array;
    }

    public static function protectStringArraySQL($s_array)
    {
        return (is_array($s_array)) ? implode('","', array_map('pSQL', $s_array)) : pSQL($s_array);
    }
}
