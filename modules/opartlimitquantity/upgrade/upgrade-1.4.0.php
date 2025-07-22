<?php
/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2021 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

function upgrade_module_1_4_0($module)
{
    $sql = [];
    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'product` CHANGE `opart_max_qty` `opart_max_qty` INT(10) NULL DEFAULT \'0\';';
    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'product` CHANGE `opart_min_qty` `opart_min_qty` INT(10) NULL DEFAULT \'0\';';
    $sql[] = 'UPDATE `' . _DB_PREFIX_ . 'product` SET `opart_max_qty` = 0 WHERE `opart_max_qty` IS NULL;';
    $sql[] = 'UPDATE `' . _DB_PREFIX_ . 'product` SET `opart_min_qty` = 0 WHERE `opart_min_qty` IS NULL;';
    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'product_attribute` ADD (`opart_max_qty` int(10) NOT NULL DEFAULT \'0\',`opart_min_qty` int(10) NOT NULL DEFAULT \'0\')';
    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'opartlimitquantity_product_batch` (
  `id_product` int(11) UNSIGNED NOT NULL,
  `batch_type` varchar(32) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_product`,`batch_type`,`quantity`),
  KEY `id_product` (`id_product`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'opartlimitquantity_product_attribute_batch` (
  `id_product` int(11) UNSIGNED NOT NULL,
  `id_product_attribute` int(11) UNSIGNED NOT NULL,
  `batch_type` varchar(32) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_product`,`id_product_attribute`,`batch_type`,`quantity`),
  KEY `id_product` (`id_product`),
  KEY `id_product_attribute` (`id_product_attribute`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
    foreach ($sql as $s) {
        if (!Db::getInstance()->execute($s)) {
            return false;
        }
    }

    return $module->registerHook('displayHeader') && $module->registerHook('displayBackOfficeHeader') && $module->registerHook('actionAdminControllerSetMedia');
}
