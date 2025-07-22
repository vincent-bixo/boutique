<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
class StockMvt extends StockMvtCore
{
    /*
    * module: wkwarehouses
    * date: 2024-12-07 01:51:56
    * version: 1.85.40
    */
    public static function getNegativeStockMvts($id_order, $id_product, $id_product_attribute, $quantity, $id_warehouse = null)
    {
        return array();
    }
}
