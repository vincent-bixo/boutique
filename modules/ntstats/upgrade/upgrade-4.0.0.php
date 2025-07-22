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

function upgrade_module_4_0_0($module)
{
    if (!$module->registerHook('dashboardZoneOne')) {
        PrestaShopLogger::addLog('Could not register to dashboard zone 1.', 3);

        return false;
    }
    if (!$module->registerHook('actionAdminControllerSetMedia')) {
        PrestaShopLogger::addLog('Could not register to add media to dashboard.', 3);

        return false;
    }

    $add_table = Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'nts_tables_config` (
            `id_nts_tables_config`  int(10)         unsigned    NOT NULL    auto_increment,
            `name`                  TEXT                        NOT NULL,
            `config`                TEXT                        NOT NULL,
            `date_add`              datetime,
            `date_upd`              datetime,
            PRIMARY KEY (`id_nts_tables_config`)
        ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
    ');

    if (!$add_table) {
        PrestaShopLogger::addLog('Could not add column order table. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    $automation_2nt_hours = (int) mt_rand(2, 5); // Rand hour between 2 and 5
    $automation_2nt_minutes = (int) mt_rand(1, 59); // Rand minutes between 1 and 59
    $last_shop_url = Tools::getCurrentUrlProtocolPrefix() . Tools::getHttpHost() . __PS_BASE_URI__;

    $update_table1 = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'nts_config`
        ADD `automation_2nt_ip` int(10) unsigned NOT NULL DEFAULT "0" AFTER `mail_version`,
        ADD `last_shop_url` TEXT NOT NULL AFTER `automation_2nt_ip`,
        ADD `automation_2nt` tinyint(1) unsigned NOT NULL DEFAULT "0" AFTER `last_shop_url`,
        ADD `automation_2nt_hours` int(10) unsigned NOT NULL DEFAULT "0" AFTER `automation_2nt`,
        ADD `automation_2nt_minutes` int(10) unsigned NOT NULL DEFAULT "0" AFTER `automation_2nt_hours`,
        ADD `mail_stock_alert` TEXT NOT NULL AFTER `automation_2nt_minutes`,
        ADD `email_alert_threshold` int(10) unsigned NOT NULL DEFAULT "3" AFTER `mail_stock_alert`,
        ADD `email_alert_type` int(10) unsigned NOT NULL DEFAULT "0" AFTER `email_alert_threshold`,
        ADD `email_alert_active` int(10) NOT NULL DEFAULT "1" AFTER `email_alert_type`,
        ADD `email_alert_send_empty` tinyint(1) unsigned NOT NULL DEFAULT "0" AFTER `email_alert_active`,
        ADD `dashboard_sales` tinyint(1) unsigned NOT NULL DEFAULT "1" AFTER `email_alert_send_empty`,
        ADD `dashboard_nb_orders` tinyint(1) unsigned NOT NULL DEFAULT "1" AFTER `dashboard_sales`;
    ');

    $update_table2 = Db::getInstance()->execute('
        UPDATE `' . _DB_PREFIX_ . 'nts_config` SET `last_shop_url` = "' . pSQL($last_shop_url) . '";
    ');

    $update_table3 = Db::getInstance()->execute('
        UPDATE `' . _DB_PREFIX_ . 'nts_config` SET `automation_2nt_hours` = "' . $automation_2nt_hours . '";
    ');

    $update_table4 = Db::getInstance()->execute('
        UPDATE `' . _DB_PREFIX_ . 'nts_config` SET `automation_2nt_minutes` = "' . $automation_2nt_minutes . '";
    ');

    if (!$update_table1 || !$update_table2 || !$update_table3 || !$update_table4) {
        PrestaShopLogger::addLog('Could not upgrade config table. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    return $module;
}
