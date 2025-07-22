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

class Order extends OrderCore
{
    public function getProductsDetail()
    {
        if (!Module::isEnabled('wkwarehouses') || !Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            return parent::getProductsDetail();
        }

        $order = new Order((int)$this->id);
        /*
        * If Order confirmation page, display always all splitted orders products in one page
        * But, in the other pages (order detail in FO, BO, etc.), display only the real ordered products
        */
        if ((self::isOrderMultiWarehouses($order) || self::isOrderMultiCarriers($order)) &&
            Tools::getValue('controller') != 'orderconfirmation') {
            return parent::getProductsDetail();
        }
    
        $orders_ids = array((int)$this->id);
        foreach ($order->getBrother() as $suborder) {
            $orders_ids[] = (int)$suborder->id;
        }
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT *
             FROM `'._DB_PREFIX_.'order_detail` od
             LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.id_product = od.product_id)
             LEFT JOIN `'._DB_PREFIX_.'product_shop` ps ON (
                ps.id_product = p.id_product AND ps.id_shop = od.id_shop
             )
             WHERE od.`id_order` IN ('.implode(',', $orders_ids).')'
        );
    }

    // Get warehouses list of all splitted orders
    public static function isOrderMultiWarehouses($order)
    {
        $warehouses_list = array();
        $orders_collection = $order->getBrother();
        if (count($orders_collection)) {
            $warehouses_list = $order->getWarehouseList();
            foreach ($orders_collection as $suborder) {/* Loop the other orders */
                foreach ($suborder->getWarehouseList() as $id_warehouse) {
                    array_push($warehouses_list, (int)$id_warehouse);
                }
            }
            $warehouses_list = array_unique(array_filter($warehouses_list));
        }
        return (!empty($warehouses_list) && count($warehouses_list) > 1  ? true : false);
    }

    // Get carriers list of all splitted orders
    public static function isOrderMultiCarriers($order)
    {
        $carriers_list = array();
        $orders_collection = $order->getBrother();
        if (count($orders_collection)) {
            $carriers_list[] = (int)$order->id_carrier;
            foreach ($orders_collection as $suborder) {/* Loop the other orders */
                array_push($carriers_list, (int)$suborder->id_carrier);
            }
            $carriers_list = array_unique(array_filter($carriers_list));
        }
        return (!empty($carriers_list) && count($carriers_list) > 1 ? true : false);
    }

    public function fixOrderPayment()
    {
        if ($this->id) {
            $query = new DbQuery();
            $query->select('op.id_order_payment, op.amount');
            $query->from('order_payment', 'op');
            $query->innerJoin('order_invoice_payment', 'oip', 'op.id_order_payment = oip.id_order_payment');
            $query->innerJoin('orders', 'o', 'oip.id_order = o.id_order');
            $query->where('oip.id_order = '.(int)$this->id);
            $rowPaid = Db::getInstance()->getRow($query->build());
            if ($rowPaid) {
                if ((float)$rowPaid['amount'] != (float)$this->total_paid_tax_incl && $rowPaid['id_order_payment']) {
                    Db::getInstance()->execute(
                        'UPDATE `'._DB_PREFIX_.'order_payment`
                         SET amount = '.(float)$this->total_paid_tax_incl.'
                         WHERE `id_order_payment` = '.(int)$rowPaid['id_order_payment']
                    );
                }
            }
        }
    }
}
