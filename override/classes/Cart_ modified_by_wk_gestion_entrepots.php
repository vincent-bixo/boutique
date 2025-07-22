<?php

class Cart extends CartCore
{
    /*
    * module: wkwarehouses
    * date: 2020-09-13 20:29:01
    * version: 1.7.60
    */
    public function checkQuantities($returnProductOnFailure = false)
    {
        if (Configuration::isCatalogMode() && !defined('_PS_ADMIN_DIR_')) {
            return false;
        }
        foreach ($this->getProducts() as $product) {
            if (!$this->allow_seperated_package &&
                !$product['allow_oosp'] &&
                $product['advanced_stock_management'] &&
                (bool)Context::getContext()->customer->isLogged() &&
                ($delivery = $this->getDeliveryOption()) && !empty($delivery)) {
                $product['stock_quantity'] = Cart::getWarehouseStockByCarrier(
                    (int)$product['id_product'],
                    (int)$product['id_product_attribute'],
                    $delivery
                );
            }
            if (!$product['active'] ||
                !$product['available_for_order'] ||
                (!$product['allow_oosp'] && $product['stock_quantity'] < $product['cart_quantity'])) {
                return $returnProductOnFailure ? $product : false;
            }
            if (!$product['allow_oosp'] && version_compare(_PS_VERSION_, '1.7.3.2', '>=') === true) {
                $productQuantity = Product::getQuantity(
                    $product['id_product'],
                    $product['id_product_attribute'],
                    null,
                    $this,
                    $product['id_customization']
                );
                if ($productQuantity < 0) {
                    return $returnProductOnFailure ? $product : false;
                }
            }
        }
        return true;
    }
    
    /*
    * module: wkwarehouses
    * date: 2020-09-13 20:29:01
    * version: 1.7.60
    */
    public static function getWarehouseStockByCarrier($id_product = 0, $id_product_attribute = 0, $delivery_option = null)
    {
        if (!(int)$id_product || !is_array($delivery_option) || !is_int($id_product_attribute)) {
            return false;
        }
        if (!class_exists('StoreHouse')) {
            require_once(dirname(__FILE__).'/../../modules/wkwarehouses/classes/Warehouse.php');
        }
        $results = StoreHouse::getWarehousesByProductId($id_product, $id_product_attribute);
        $stock_quantity = 0;
        foreach ($results as $result) {
            if (isset($result['id_warehouse']) && (int)$result['id_warehouse']) {
                $id_warehouse = (int)$result['id_warehouse'];
                $ws = new StoreHouse($id_warehouse);
                $carriers = $ws->getWsCarriers();
                if (is_array($carriers) && !empty($carriers)) {
                    $stock_quantity += Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        'SELECT SUM(s.`physical_quantity`) as quantity
                         FROM '._DB_PREFIX_.'stock s
                         LEFT JOIN '._DB_PREFIX_.'warehouse_carrier wc ON wc.`id_warehouse` = s.`id_warehouse`
                         LEFT JOIN '._DB_PREFIX_.'carrier c ON wc.`id_carrier` = c.`id_reference`
                         WHERE s.`id_product` = '.(int)$id_product.' AND 
                         s.`id_product_attribute` = '.(int)$id_product_attribute.' AND 
                         s.`id_warehouse` = '.$id_warehouse.' AND 
                         c.`id_carrier` IN ('.rtrim($delivery_option[(int)Context::getContext()->cart->id_address_delivery], ',').') 
                         GROUP BY s.`id_product`'
                    );
                } else {
                    $stock_quantity += Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        'SELECT SUM(s.`physical_quantity`) as quantity
                         FROM '._DB_PREFIX_.'stock s
                         WHERE s.`id_product` = '.(int)$id_product.' AND 
                         s.`id_product_attribute` = '.(int)$id_product_attribute.' AND 
                         s.`id_warehouse` = '.$id_warehouse.' 
                         GROUP BY s.`id_product`'
                    );
                }
            }
        }
        return $stock_quantity;
    }
    /*
    * module: shippingedit
    * date: 2020-10-03 18:18:06
    * version: 1.7.6
    */
    public function getPackageShippingCost(
        $id_carrier = null,
        $use_tax = true,
        Country $default_country = null,
        $product_list = null,
        $id_zone = null
    ) {
        $total = 0;
        $return = false;
        
        Hook::exec('actionCartGetPackageShippingCost', array(
            'object' => &$this,
            'id_carrier' => &$id_carrier,
            'use_tax' => &$use_tax,
            'default_country' => &$default_country,
            'product_list' => &$product_list,
            'id_zone' => &$id_zone,
            'total' => &$total,
            'return' => &$return
        ));
        
        if ($return) {
            return (float) Tools::ps_round((float) $total, 2);
        }
        
        return parent::getPackageShippingCost(
            $id_carrier,
            $use_tax,
            $default_country,
            $product_list,
            $id_zone
        ) + (float) Tools::ps_round((float) $total, 2);
    }
}
