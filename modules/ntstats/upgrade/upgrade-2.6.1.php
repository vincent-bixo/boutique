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

function upgrade_module_2_6_1($module)
{
    $update_table1 = Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'nts_config` ADD`id_shop_group` int(10) unsigned NOT NULL AFTER `id_shop`;
    ');

    $update_table2 = Db::getInstance()->execute('
        UPDATE `' . _DB_PREFIX_ . 'nts_config` SET `id_shop_group` = "' . (int) Context::getContext()->shop->id_shop_group . '";
    ');

    if (!$update_table1 || !$update_table2) {
        PrestaShopLogger::addLog('Could not upgrade config table. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    return $module;
}
