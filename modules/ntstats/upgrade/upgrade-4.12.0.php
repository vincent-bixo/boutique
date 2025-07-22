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

function upgrade_module_4_12_0($module)
{
    $add_order_type_date = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'nts_config` ADD `order_type_date` INT NOT NULL DEFAULT 1 AFTER `server_memory_value`;
    ');

    if (!$add_order_type_date) {
        PrestaShopLogger::addLog('Could not add order type date to config table. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    $update_order_type_date = Db::getInstance()->execute('
        UPDATE `' . _DB_PREFIX_ . 'nts_config` SET `order_type_date` = "0";
    ');

    if (!$update_order_type_date) {
        PrestaShopLogger::addLog('Could not initialize order type date in config table. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    $add_order_date_state = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'nts_config` ADD `order_date_state` INT NOT NULL DEFAULT 0 AFTER `order_type_date`;
    ');

    if (!$add_order_date_state) {
        PrestaShopLogger::addLog('Could not add order date state to config table. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    $add_return_valid_states = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'nts_config` ADD `return_valid_states` TEXT NOT NULL AFTER `order_date_state`;
    ');

    if (!$add_return_valid_states) {
        PrestaShopLogger::addLog('Could not add return valid states to config table. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    if (!Configuration::updateValue('NTSTATS_NEW_VERSION_MSG', 1)) {
        return false;
    }

    return $module;
}
