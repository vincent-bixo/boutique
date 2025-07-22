<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
class StockManager extends StockManagerCore implements StockManagerInterface
{
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
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
