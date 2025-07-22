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

function upgrade_module_2_6_0($module)
{
    $update_table1 = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'nts_config` ADD `receive_email_version` tinyint(1) NOT NULL DEFAULT "0" AFTER `amount_customer_min_orders`;
    ');

    $update_table2 = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'nts_config` ADD `mail_version` TEXT NOT NULL AFTER `receive_email_version`;
    ');

    $update_table3 = Db::getInstance()->execute('
        UPDATE `' . _DB_PREFIX_ . 'nts_config` SET `mail_version` = "' . Configuration::get('PS_SHOP_EMAIL') . '";
    ');

    if (!$update_table1 || !$update_table2 || !$update_table3) {
        PrestaShopLogger::addLog('Could not upgrade config table. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    return $module;
}
