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

class WkCharityDonationDb
{
    public function getModuleSql()
    {
        return [
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . "wk_donation_info` (
                `id_donation_info` int(11) unsigned NOT NULL auto_increment,
                `id_product` int(10) unsigned NOT NULL,
                `price_type` tinyint(1) unsigned NOT NULL,
                `price` decimal(20, 6) NOT NULL DEFAULT '0.000000',
                `expiry_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `product_visibility` tinyint(1) unsigned NOT NULL,
                `show_at_checkout` tinyint(1) unsigned NOT NULL,
                `advertise` tinyint(1) unsigned NOT NULL,
                `show_donate_button` tinyint(1) unsigned,
                `adv_title_color` varchar(32),
                `adv_desc_color` varchar(32),
                `button_text_color` varchar(32),
                `button_border_color` varchar(32),
                `position` varchar(10) DEFAULT NULL,
                `is_global` tinyint(1) unsigned NOT NULL DEFAULT '0',
                `active` tinyint(1) unsigned DEFAULT '0',
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                primary key (`id_donation_info`)
            ) ENGINE=" . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'wk_donation_info_lang` (
                `id_donation_info` int(11) unsigned NOT NULL,
                `id_lang` int(10) unsigned NOT NULL,
                `id_shop` int(10) unsigned NOT NULL,
                `name` varchar(128) NOT NULL,
                `description` text NOT NULL,
                `advertisement_title` varchar(128) NOT NULL,
                `advertisement_description` text NOT NULL,
                `donate_button_text` varchar(128),
                primary key (`id_donation_info`, `id_lang`,`id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'wk_donation_info_shop` (
                `id_donation_info` int(11) unsigned NOT NULL,
                `id_shop` int(10) unsigned NOT NULL,
                primary key (`id_donation_info`, `id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'wk_donation_display_places` (
                `id_donation_info` int(11) unsigned NOT NULL,
                `id_page` int(11) NOT NULL,
                `id_hook` int(11) unsigned NOT NULL,
                `id_shop` int(10) unsigned NOT NULL,
                `date_add`  datetime NOT NULL
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8',
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . "wk_donation_stats` (
                `id_donation_stats` int(10) unsigned NOT NULL auto_increment,
                `id_order` int(10) unsigned NOT NULL,
                `id_product` int(10) unsigned NOT NULL,
                `id_donation_info` int(11) unsigned NOT NULL,
                `id_customer` int(10) unsigned NOT NULL DEFAULT '0',
                `id_shop` int(10) unsigned NOT NULL,
                `name` varchar(128) NOT NULL,
                `date_add` datetime NOT NULL,
                primary key (`id_donation_stats`)
            ) ENGINE=" . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8',
        ];
    }

    public function createTables()
    {
        if ($sql = $this->getModuleSql()) {
            $objDb = Db::getInstance();
            foreach ($sql as $query) {
                if (!$objDb->execute(trim($query))) {
                    return false;
                }
            }
        }

        return true;
    }

    public function deleteTables()
    {
        return Db::getInstance()->execute(
            'DROP TABLE IF EXISTS
            `' . _DB_PREFIX_ . 'wk_donation_info`,
            `' . _DB_PREFIX_ . 'wk_donation_info_lang`,
            `' . _DB_PREFIX_ . 'wk_donation_info_shop`,
            `' . _DB_PREFIX_ . 'wk_donation_display_places`,
            `' . _DB_PREFIX_ . 'wk_donation_stats`'
        );
    }
}
