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

function upgrade_module_4_10_0($module)
{
    $add_table = Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'nts_config_profil_countries` (
            `id_nts_config_profil_countries`    int(10)         unsigned    NOT NULL    auto_increment,
            `id_nts_config`                     int(10)         unsigned    NOT NULL,
            `id_profil`                         int(10)         unsigned    NOT NULL,
            `id_countries`                      TEXT                        NOT NULL,
            `date_add`                          datetime,
            `date_upd`                          datetime,
            PRIMARY KEY (`id_nts_config_profil_countries`)
        ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8;
    ');

    if (!$add_table) {
        PrestaShopLogger::addLog('Could not add profil countries config table. ' . Db::getInstance()->getMsgError(), 3);

        return false;
    }

    return $module;
}
