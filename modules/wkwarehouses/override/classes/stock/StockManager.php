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

class StockManager extends StockManagerCore implements StockManagerInterface
{
    public function getProductRealQuantities($id_product, $id_product_attribute, $ids_warehouse = null, $usable = false)
    {
        if (!Module::isEnabled('wkwarehouses') || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            return parent::getProductRealQuantities($id_product, $id_product_attribute, $ids_warehouse, $usable);
        }
        if (!class_exists('WorkshopAsm')) {
            require_once(dirname(__FILE__).'/../../../modules/wkwarehouses/classes/WorkshopAsm.php');
        }
		$physical_quantity = (int)WorkshopAsm::getProductPhysicalQuantities(
			$id_product,
			$id_product_attribute,
			$ids_warehouse
		);
		$reserved_quantity = (int)WorkshopAsm::getReservedQuantityByProductAndWarehouse(
			$id_product,
			$id_product_attribute,
			$ids_warehouse
		);
        return $physical_quantity - $reserved_quantity;
    }
}
