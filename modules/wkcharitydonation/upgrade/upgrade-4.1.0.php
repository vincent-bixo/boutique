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

function upgrade_module_4_1_0()
{
    $wkQueries = [
        'ALTER TABLE `' . _DB_PREFIX_ . 'wk_donation_info_lang` ADD  `id_shop` int(10) unsigned NOT NULL',
        'ALTER TABLE `' . _DB_PREFIX_ . 'wk_donation_display_places` ADD `id_shop` int(10) unsigned NOT NULL',
        'ALTER TABLE `' . _DB_PREFIX_ . 'wk_donation_stats` ADD `id_shop` int(10) unsigned NOT NULL',
        'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'wk_donation_info_shop` (
            `id_donation_info` int(11) unsigned NOT NULL,
            `id_shop` int(10) unsigned NOT NULL,
            primary key (`id_donation_info`, `id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8',
    ];

    $dbInstance = Db::getInstance();
    $success = true;
    foreach ($wkQueries as $query) {
        $success &= $dbInstance->execute(trim($query));
    }

    if ($success) {
        $shopIds = Shop::getContextListShopID();
        $wk_donations_info = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'wk_donation_info');
        if ($wk_donations_info) {
            foreach ($wk_donations_info as $wk_donation_info) {
                foreach ($shopIds as $idShop) {
                    $wkSql_1 = 'INSERT INTO `' . _DB_PREFIX_ . 'wk_donation_info_shop` (`id_donation_info`,`id_shop`)
                        VALUES (' . (int) $wk_donation_info['id_donation_info'] . ', ' . (int) $idShop . ')';

                    $wkSql_2 = 'INSERT INTO `' . _DB_PREFIX_ . 'wk_donation_info_lang` (`id_donation_info`,`id_shop`)
                        VALUES (' . (int) $wk_donation_info['id_donation_info'] . ', ' . (int) $idShop . ')';

                    $wkSql_3 = 'INSERT INTO `' . _DB_PREFIX_ . 'wk_donation_display_places` (`id_donation_info`,`id_shop`)
                        VALUES (' . (int) $wk_donation_info['id_donation_info'] . ', ' . (int) $idShop . ')';

                    $wkSql_4 = 'INSERT INTO `' . _DB_PREFIX_ . 'wk_donation_stats` (`id_donation_info`,`id_shop`)
                        VALUES (' . (int) $wk_donation_info['id_donation_info'] . ', ' . (int) $idShop . ')';

                    $dbInstance->execute($wkSql_1);
                    $dbInstance->execute($wkSql_2);
                    $dbInstance->execute($wkSql_3);
                    $dbInstance->execute($wkSql_4);
                }
            }
        }
    }

    return true;
}
