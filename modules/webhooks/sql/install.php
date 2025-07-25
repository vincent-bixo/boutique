<?php
/**
 * 2007-2022 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2024 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

$sql = [];

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'webhooks` (
    `id_webhook` int(11) NOT NULL AUTO_INCREMENT,
    `url` VARCHAR(1500) NULL,
    `hook` VARCHAR(500) NULL,
    `real_time` INT(1) NOT NULL DEFAULT 1,
    `retries` INT(3) NOT NULL DEFAULT 5,
    `active` INT(1) NOT NULL DEFAULT 1,
    `date_add` DATETIME NULL,
    PRIMARY KEY  (`id_webhook`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'webhooks_log` (
    `id_log` INT(11) NOT NULL AUTO_INCREMENT,
    `id_webhook` INT(11) NULL,
    `real_time` INT(1) NOT NULL DEFAULT 1,
    `url` VARCHAR(1500) NULL,
    `payload` TEXT NULL,
    `response` TEXT NULL,
    `status_code` INT(3) NOT NULL DEFAULT 200,
    `date_add` DATETIME NULL,
    PRIMARY KEY  (`id_log`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'webhooks_queue` (
    `id_queue` INT(11) NOT NULL AUTO_INCREMENT,
    `id_webhook` INT(11) NULL,
    `executed` INT(1) NOT NULL DEFAULT 0,
    `retry` INT(3) NOT NULL DEFAULT 0,
    `url` VARCHAR(1500) NULL,
    `payload` TEXT NULL,
    `date_add` DATETIME NULL,
    PRIMARY KEY  (`id_queue`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
