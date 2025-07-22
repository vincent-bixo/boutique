<?php
/**
* NOTICE OF LICENSE
*
* This file is part of the 'Wk Warehouses Management' module feature.
* Developped by Khoufi Wissem (2018).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/
if (!defined('_PS_VERSION_')) {
    exit;
}

Db::getInstance()->Execute(
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'warehouse` (
        `id_warehouse` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `id_currency` int(11) unsigned NOT NULL,
        `id_address` int(11) unsigned NOT NULL,
        `id_employee` int(11) unsigned NOT NULL,
        `reference` varchar(32) DEFAULT NULL,
        `name` varchar(45) NOT NULL,
        `management_type` enum(\'WA\',\'FIFO\',\'LIFO\') NOT NULL DEFAULT \'WA\',
        `deleted` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
        `active` tinyint(1) unsigned NOT NULL DEFAULT \'1\',
        PRIMARY KEY (`id_warehouse`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'
);
Db::getInstance()->Execute(
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'warehouse_lang` (
        `id_warehouse` int(11) unsigned NOT NULL,
        `id_lang` int(11) unsigned NOT NULL,
        `name` varchar(128) DEFAULT NULL,
        `delivery_time` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id_warehouse`,`id_lang`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'
);
Db::getInstance()->Execute(
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'warehouse_carrier` (
        `id_carrier` int(11) unsigned NOT NULL,
        `id_warehouse` int(11) unsigned NOT NULL,
        PRIMARY KEY (`id_warehouse`,`id_carrier`),
        KEY `id_warehouse` (`id_warehouse`),
        KEY `id_carrier` (`id_carrier`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'
);
Db::getInstance()->Execute(
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'warehouse_product_location` (
        `id_warehouse_product_location` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `id_product` int(11) unsigned NOT NULL,
        `id_product_attribute` int(11) unsigned NOT NULL,
        `id_warehouse` int(11) unsigned NOT NULL,
        `location` varchar(64) DEFAULT NULL,
        PRIMARY KEY (`id_warehouse_product_location`),
        UNIQUE KEY `id_product` (`id_product`,`id_product_attribute`,`id_warehouse`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'
);
Db::getInstance()->Execute(
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'warehouse_cart_product` (
        `id_cart` int(10) unsigned NOT NULL,
        `id_product` int(10) unsigned NOT NULL,
        `id_product_attribute` int(10) unsigned NOT NULL DEFAULT \'0\',
        `id_warehouse` int(10) unsigned NOT NULL,
        `date_add` datetime NOT NULL,
        PRIMARY KEY (`id_cart`,`id_product`,`id_product_attribute`),
        KEY `id_product_attribute` (`id_product_attribute`),
        KEY `id_cart_order` (`id_cart`,`id_product`,`id_product_attribute`) USING BTREE
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'
);
Db::getInstance()->Execute(
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'warehouse_shop` (
        `id_shop` int(11) unsigned NOT NULL,
        `id_warehouse` int(11) unsigned NOT NULL,
        PRIMARY KEY (`id_warehouse`,`id_shop`),
        KEY `id_warehouse` (`id_warehouse`),
        KEY `id_shop` (`id_shop`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'
);
Db::getInstance()->Execute(
    'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'stock` (
        `id_stock` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `id_warehouse` int(11) unsigned NOT NULL,
        `id_product` int(11) unsigned NOT NULL,
        `id_product_attribute` int(11) unsigned NOT NULL,
        `reference` varchar(32) NOT NULL,
        `ean13` varchar(13) DEFAULT NULL,
        `isbn` varchar(32) DEFAULT NULL,
        `upc` varchar(12) DEFAULT NULL,
        `physical_quantity` int(11) NOT NULL,
        `usable_quantity` int(11) NOT NULL,
        `price_te` decimal(20,6) DEFAULT \'0.000000\',
        PRIMARY KEY (`id_stock`),
        KEY `id_warehouse` (`id_warehouse`),
        KEY `id_product` (`id_product`),
        KEY `id_product_attribute` (`id_product_attribute`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8'
);
// Create warehouse stocks movements table
WarehouseStockMvt::createWarehouseStockMvtsTable();

// Turn off enabled depends_on_stock to avoid stock troubles
Db::getInstance()->Execute(
    'UPDATE `'._DB_PREFIX_.'stock_available`
     SET depends_on_stock  = 0
     WHERE depends_on_stock = 1'
);

if (Db::getInstance()->executeS('SHOW COLUMNS FROM `'._DB_PREFIX_.'warehouse` LIKE "active"') == false) {
	Db::getInstance()->Execute(
		'ALTER TABLE `' . _DB_PREFIX_ . 'warehouse` ADD `active` tinyint(1) NOT NULL default 1'
	);
}

// Change warehouses stock table
Db::getInstance()->Execute(
    'ALTER TABLE `'._DB_PREFIX_.'stock` CHANGE `physical_quantity` `physical_quantity` INT(11) NULL DEFAULT NULL'
);
Db::getInstance()->Execute(
    'ALTER TABLE `'._DB_PREFIX_.'stock` CHANGE `usable_quantity` `usable_quantity` INT(11) NULL DEFAULT NULL'
);
